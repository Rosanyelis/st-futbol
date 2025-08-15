<?php

namespace App\Helpers;

class StringHelper
{
    /**
     * Reemplaza espacios por guiones en una cadena de texto
     *
     * @param string $text El texto original
     * @param bool $lowercase Si se debe convertir a minúsculas (por defecto true)
     * @return string El texto con espacios reemplazados por guiones
     */
    public static function replaceSpacesWithHyphens(string $text, bool $lowercase = true): string
    {
        // Eliminar espacios múltiples y reemplazar por un solo guión
        $text = preg_replace('/\s+/', '-', trim($text));
        
        // Eliminar caracteres especiales excepto guiones
        $text = preg_replace('/[^a-zA-Z0-9\-]/', '', $text);
        
        // Eliminar guiones múltiples consecutivos
        $text = preg_replace('/-+/', '-', $text);
        
        // Eliminar guiones al inicio y final
        $text = trim($text, '-');
        
        // Convertir a minúsculas si se especifica
        if ($lowercase) {
            $text = strtolower($text);
        }
        
        return $text;
    }
    
    /**
     * Función alternativa más simple que solo reemplaza espacios por guiones
     *
     * @param string $text El texto original
     * @return string El texto con espacios reemplazados por guiones
     */
    public static function simpleReplaceSpaces(string $text): string
    {
        return str_replace(' ', '-', trim($text));
    }
}
