<?php

/**
 * Funciones helpers globales para la aplicación
 */

if (!function_exists('replace_spaces_with_hyphens')) {
    /**
     * Reemplaza espacios por guiones en una cadena de texto
     *
     * @param string $text El texto original
     * @param bool $lowercase Si se debe convertir a minúsculas (por defecto true)
     * @return string El texto con espacios reemplazados por guiones
     */
    function replace_spaces_with_hyphens(string $text, bool $lowercase = true): string
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
}

if (!function_exists('simple_replace_spaces')) {
    /**
     * Función simple que solo reemplaza espacios por guiones
     *
     * @param string $text El texto original
     * @return string El texto con espacios reemplazados por guiones
     */
    function simple_replace_spaces(string $text): string
    {
        return str_replace(' ', '-', trim($text));
    }
}

if (!function_exists('slugify')) {
    /**
     * Crea un slug URL-friendly a partir de un texto
     *
     * @param string $text El texto original
     * @return string El slug generado
     */
    function slugify(string $text): string
    {
        return replace_spaces_with_hyphens($text);
    }
}

if (!function_exists('save_image')) {
    /**
     * Guarda una imagen en el storage
     *
     * @param \Illuminate\Http\UploadedFile $file El archivo subido
     * @param string $folder La carpeta donde guardar
     * @param string|null $oldFileName Nombre del archivo anterior para eliminarlo
     * @param array $options Opciones adicionales
     * @return string|false El nombre del archivo guardado o false si falla
     */
    function save_image($file, string $folder, ?string $oldFileName = null, array $options = []): string|false
    {
        return \App\Helpers\FileHelper::saveImage($file, $folder, $oldFileName, $options);
    }
}

if (!function_exists('save_multiple_images')) {
    /**
     * Guarda múltiples imágenes en el storage
     *
     * @param array $files Array de archivos UploadedFile
     * @param string $folder La carpeta donde guardar
     * @param array $options Opciones adicionales
     * @return array Array con los nombres de archivos guardados
     */
    function save_multiple_images(array $files, string $folder, array $options = []): array
    {
        return \App\Helpers\FileHelper::saveMultipleImages($files, $folder, $options);
    }
}

if (!function_exists('delete_image')) {
    /**
     * Elimina una imagen del storage
     *
     * @param string $fileName Nombre del archivo
     * @param string $folder Carpeta donde está el archivo
     * @param string $disk Disco de storage
     * @return bool True si se eliminó correctamente
     */
    function delete_image(string $fileName, string $folder, string $disk = 'public'): bool
    {
        return \App\Helpers\FileHelper::deleteImage($fileName, $folder, $disk);
    }
}

if (!function_exists('get_image_url')) {
    /**
     * Obtiene la URL pública de una imagen
     *
     * @param string $fileName Nombre del archivo
     * @param string $folder Carpeta donde está el archivo
     * @param string $disk Disco de storage
     * @return string|null URL de la imagen o null si no existe
     */
    function get_image_url(string $fileName, string $folder, string $disk = 'public'): ?string
    {
        return \App\Helpers\FileHelper::getImageUrl($fileName, $folder, $disk);
    }
}

if (!function_exists('get_file_info')) {
    /**
     * Obtiene información del archivo
     *
     * @param string $fileName Nombre del archivo
     * @param string $folder Carpeta donde está el archivo
     * @param string $disk Disco de storage
     * @return array|null Información del archivo o null si no existe
     */
    function get_file_info(string $fileName, string $folder, string $disk = 'public'): ?array
    {
        return \App\Helpers\FileHelper::getFileInfo($fileName, $folder, $disk);
    }
}
