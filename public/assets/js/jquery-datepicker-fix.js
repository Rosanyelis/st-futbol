// Fix para jQuery Datepicker - Implementación básica
(function($) {
    'use strict';
    
    // Verificar si jQuery está disponible
    if (typeof $ === 'undefined') {
        console.error('jQuery no está disponible para el datepicker fix');
        return;
    }
    
    // Implementación básica de datepicker si no existe
    if (typeof $.fn.datepicker === 'undefined') {
        $.fn.datepicker = function(options) {
            var defaults = {
                format: 'mm/dd/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'es'
            };
            
            var settings = $.extend({}, defaults, options);
            
            return this.each(function() {
                var $input = $(this);
                
                // Crear un input de tipo date nativo como fallback
                if ($input.attr('type') !== 'date') {
                    $input.attr('type', 'date');
                }
                
                // Agregar clases CSS para styling
                $input.addClass('form-control datepicker-input');
                
                // Configurar el formato de fecha
                if (settings.format) {
                    $input.attr('data-date-format', settings.format);
                }
                
                // Agregar placeholder si no existe
                if (!$input.attr('placeholder')) {
                    $input.attr('placeholder', 'Seleccione una fecha');
                }
            });
        };
        
        console.log('jQuery Datepicker fix aplicado');
    }
    
})(jQuery);
