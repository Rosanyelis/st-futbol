<?php

// Ejemplo de uso de las funciones StringHelper

require_once 'StringHelper.php';

use App\Helpers\StringHelper;

// Ejemplos de uso de la función principal
echo "=== Ejemplos de replaceSpacesWithHyphens ===\n";

$text1 = "Hola Mundo";
echo "Original: '$text1'\n";
echo "Resultado: '" . StringHelper::replaceSpacesWithHyphens($text1) . "'\n\n";

$text2 = "  Mi   Nombre   Es   Juan  ";
echo "Original: '$text2'\n";
echo "Resultado: '" . StringHelper::replaceSpacesWithHyphens($text2) . "'\n\n";

$text3 = "Producto & Categoría (2024)";
echo "Original: '$text3'\n";
echo "Resultado: '" . StringHelper::replaceSpacesWithHyphens($text3) . "'\n\n";

$text4 = "TÍTULO EN MAYÚSCULAS";
echo "Original: '$text4'\n";
echo "Resultado: '" . StringHelper::replaceSpacesWithHyphens($text4) . "'\n\n";

// Ejemplo con mayúsculas preservadas
echo "=== Ejemplo preservando mayúsculas ===\n";
$text5 = "Mi Título Importante";
echo "Original: '$text5'\n";
echo "Resultado: '" . StringHelper::replaceSpacesWithHyphens($text5, false) . "'\n\n";

// Ejemplos de la función simple
echo "=== Ejemplos de simpleReplaceSpaces ===\n";
$text6 = "Texto Simple";
echo "Original: '$text6'\n";
echo "Resultado: '" . StringHelper::simpleReplaceSpaces($text6) . "'\n\n";

$text7 = "  Texto   con   espacios   múltiples  ";
echo "Original: '$text7'\n";
echo "Resultado: '" . StringHelper::simpleReplaceSpaces($text7) . "'\n\n";
