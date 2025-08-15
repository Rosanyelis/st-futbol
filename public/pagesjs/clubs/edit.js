$(document).ready(function() {
    
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
