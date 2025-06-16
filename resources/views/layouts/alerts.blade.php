
@if (session('success'))
            Swal.fire({
                position: 'top-center',
                icon: 'success',
                title: '{{ session('success') }}',
                customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                buttonsStyling: false

            });
            @endif

            @if (session('error'))
            Swal.fire({
                position: 'top-center',
                icon: 'error',
                title: '{{ session('error') }}',
                customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                buttonsStyling: false

            });
            @endif

            @if (session('warning'))
            Swal.fire({
                position: 'top-center',
                icon: 'warning',
                title: '{{ session('warning') }}',
                customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                buttonsStyling: false

            });
            @endif
