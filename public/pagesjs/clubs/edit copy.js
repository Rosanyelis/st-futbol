$(document).ready(function() {
    // Mostrar/ocultar campos según hospedaje
    const hasAccommodation = document.getElementById('has_accommodation');
    const rowsToShow = [
        'teachers_quantity', 'teacher_price',
        'companions_quantity', 'companion_price',
        'drivers_quantity', 'driver_price'
    ];

    function toggleAccommodationFields() {
        const value = hasAccommodation.value;
        rowsToShow.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.closest('tr').style.display = (value === '1') ? '' : 'none';
            }
        });
    }
    hasAccommodation.addEventListener('change', toggleAccommodationFields);
    toggleAccommodationFields();

    function toggleSupplierField() {
        if ($('#has_accommodation').val() === '1') {
            $('#supplierField').show();
        } else {
            $('#supplierField').hide();
        }
    }
    $('#has_accommodation').on('change', toggleSupplierField);
    toggleSupplierField();

    // Cálculo de totales
    function calculateTotal(quantityId, priceId, totalId) {
        // Quita las comas antes de convertir a número
        const quantity = parseFloat(document.getElementById(quantityId).value.replace(/,/g, '')) || 0;
        const price = parseFloat(document.getElementById(priceId).value.replace(/,/g, '')) || 0;
        const total = quantity * price;
        // Formatea el total con separador de miles
        document.getElementById(totalId).value = formatNumberWithThousandsSeparator(total.toFixed(0));
        return total;
    }

    function updateAllTotals() {
        const totalPlayers = calculateTotal('players_quantity', 'player_price', 'totalPlayers');
        const totalTeachers = calculateTotal('teachers_quantity', 'teacher_price', 'totalTeachers');
        const totalCompanions = calculateTotal('companions_quantity', 'companion_price', 'totalCompanions');
        const totalDrivers = calculateTotal('drivers_quantity', 'driver_price', 'totalDrivers');
        const totalLiberated = calculateTotal('liberated_quantity', 'liberated_price', 'totalLiberated');

        // Calcular total de personas (quita comas)
        const totalPeople = 
            (parseFloat(document.getElementById('players_quantity').value.replace(/,/g, '')) || 0) +
            (parseFloat(document.getElementById('teachers_quantity').value.replace(/,/g, '')) || 0) +
            (parseFloat(document.getElementById('companions_quantity').value.replace(/,/g, '')) || 0) +
            (parseFloat(document.getElementById('drivers_quantity').value.replace(/,/g, '')) || 0) +
            (parseFloat(document.getElementById('liberated_quantity').value.replace(/,/g, '')) || 0);

        document.getElementById('totalPeople').value = formatNumberWithThousandsSeparator(totalPeople.toString());

        // Calcular total general
        const grandTotal = totalPlayers + totalTeachers + totalCompanions + totalDrivers + totalLiberated;
        document.getElementById('grandTotal').value = formatNumberWithThousandsSeparator(grandTotal.toFixed(0));
    }

    // Agregar event listeners para todos los campos de cantidad y precio
    [
        'players_quantity', 'player_price',
        'teachers_quantity', 'teacher_price',
        'companions_quantity', 'companion_price',
        'drivers_quantity', 'driver_price',
        'liberated_quantity', 'liberated_price'
    ].forEach(id => {
        document.getElementById(id).addEventListener('input', updateAllTotals);
    });

    // Inicializar cálculos
    updateAllTotals();

    // --- Carga dinámica de provincias y ciudades ---
    const countrySelect = document.getElementById('country');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');

    countrySelect.addEventListener('change', function() {
        const countryId = this.value;
        provinceSelect.innerHTML = '<option value="">Seleccione una provincia</option>';
        citySelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
        if (!countryId) return;
        fetch('/clubs/get-provinces', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ country_id: countryId })
        })
        .then(response => response.json())
        .then(data => {
            data.forEach(province => {
                const option = document.createElement('option');
                option.value = province.id;
                option.textContent = province.name;
                provinceSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar provincias:', error);
        });
    });

    provinceSelect.addEventListener('change', function() {
        const provinceId = this.value;
        citySelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
        if (!provinceId) return;
        fetch('/clubs/get-cities', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ province_id: provinceId })
        })
        .then(response => response.json())
        .then(data => {
            data.forEach(city => {
                const option = document.createElement('option');
                option.value = city.id;
                option.textContent = city.name;
                citySelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar ciudades:', error);
        });
    });

    // Provincias por país
    $('#country').on('change', function() {
        var countryId = $(this).val();
        $('#province').html('<option value="">Seleccione una provincia</option>');
        $('#city').html('<option value="">Seleccione una ciudad</option>');
        if (!countryId) return;

        $.ajax({
            url: '/clubs/get-provinces',
            type: 'POST',
            data: {
                country_id: countryId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                $.each(data, function(i, province) {
                    $('#province').append($('<option>', {
                        value: province.id,
                        text: province.name
                    }));
                });
            },
            error: function(xhr) {
                console.error('Error al cargar provincias');
            }
        });
    });

    // Ciudades por provincia
    $('#province').on('change', function() {
        var provinceId = $(this).val();
        $('#city').html('<option value="">Seleccione una ciudad</option>');
        if (!provinceId) return;

        $.ajax({
            url: '/clubs/get-cities',
            type: 'POST',
            data: {
                province_id: provinceId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                $.each(data, function(i, city) {
                    $('#city').append($('<option>', {
                        value: city.id,
                        text: city.name
                    }));
                });
            },
            error: function(xhr) {
                console.error('Error al cargar ciudades');
            }
        });
    });

    // Preview de imagen
    $('#upload').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#uploadedLogo').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Reset de imagen
    $('.account-image-reset').on('click', function() {
        $('#uploadedLogo').attr('src', "{{ asset('assets/img/avatars/2.png') }}");
        $('#upload').val('');
    });

    // Manejo del formulario
    $('#formClub').on('submit', function(e) {
        e.preventDefault();

        // Cambiar el texto del botón y deshabilitarlo
        let $btn = $(this).find('button[type=submit]');
        let originalText = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
        $btn.prop('disabled', true);

        // Enviar el formulario
        this.submit();
    });



// Evento de cambio de evento usando jQuery
$('#event_id').on('change', function() {
    loadSuppliersByEvent($(this).val());
});

// Cargar proveedores por evento
function loadSuppliersByEvent(eventId) {
    // Limpiar el select de proveedores
    $('#supplier_id').html('<option value="">Seleccione un proveedor</option>');
    if (!eventId) return;

    $.ajax({
        url: '/clubs/get-suppliers',
        type: 'POST',
        data: {
            event_id: eventId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            $.each(data, function(i, supplier) {
                $('#supplier_id').append(
                    $('<option>', {
                        value: supplier.id,
                        text: supplier.name
                    })
                );
            });
        },
        error: function(xhr) {
            console.error('Error al cargar proveedores:', xhr);
        }
    });
}

// Formateo de números con separador de miles
function formatNumberWithThousandsSeparator(value) {
    // Elimina todo lo que no sea dígito
    value = value.replace(/\D/g, '');
    // Formatea con puntos como separador de miles
    return value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Aplica el formateo en tiempo real a los campos de cantidad y precio
[
    'players_quantity', 'player_price',
    'teachers_quantity', 'teacher_price',
    'companions_quantity', 'companion_price',
    'drivers_quantity', 'driver_price',
    'liberated_quantity', 'liberated_price'
].forEach(function(id) {
    $('#' + id).on('input', function() {
        // Guarda la posición del cursor
        var cursorPos = this.selectionStart;
        var originalLength = this.value.length;

        // Formatea el valor
        var formatted = formatNumberWithThousandsSeparator(this.value);
        this.value = formatted;

        // Ajusta la posición del cursor
        var newLength = formatted.length;
        this.selectionEnd = this.selectionStart = cursorPos + (newLength - originalLength);

        // Actualiza los totales (si tienes lógica de cálculo)
        updateAllTotals();
    });
});

// Cargar provincias y ciudades seleccionadas al editar
function cargarProvinciasYSeleccionar(countryId, selectedProvinceId, selectedCityId) {
    if (!countryId) return;
    // Cargar provincias
    $.ajax({
        url: '/clubs/get-provinces',
        type: 'POST',
        data: {
            country_id: countryId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(provinces) {
            $('#province').html('<option value="">Seleccione una provincia</option>');
            $.each(provinces, function(i, province) {
                $('#province').append(
                    $('<option>', {
                        value: province.id,
                        text: province.name,
                        selected: province.id == selectedProvinceId
                    })
                );
            });
            // Si hay provincia seleccionada, cargar ciudades
            if (selectedProvinceId) {
                cargarCiudadesYSeleccionar(selectedProvinceId, selectedCityId);
            }
        }
    });
}

function cargarCiudadesYSeleccionar(provinceId, selectedCityId) {
    if (!provinceId) return;
    $.ajax({
        url: '/clubs/get-cities',
        type: 'POST',
        data: {
            province_id: provinceId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(cities) {
            $('#city').html('<option value="">Seleccione una ciudad</option>');
            $.each(cities, function(i, city) {
                $('#city').append(
                    $('<option>', {
                        value: city.id,
                        text: city.name,
                        selected: city.id == selectedCityId
                    })
                );
            });
        }
    });
}

// Al cargar la página, si hay valores seleccionados, los cargamos
let selectedCountryId = $('#country').val();
let selectedProvinceId = $('#province').data('selected'); // Usa data-selected en el select
let selectedCityId = $('#city').data('selected');         // Usa data-selected en el select

if (selectedCountryId && selectedProvinceId) {
    cargarProvinciasYSeleccionar(selectedCountryId, selectedProvinceId, selectedCityId);
} else if (selectedCountryId) {
    cargarProvinciasYSeleccionar(selectedCountryId, null, null);
}
});
