<?php
if (!function_exists('fya_upload_dir')) {
    function fya_upload_dir() {
        static $dir = null;
        if ($dir === null) {
            $dir = rtrim(dirname(__DIR__), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
        return $dir;
    }

    function fya_upload_path($filename) {
        $safeName = basename((string) $filename);
        return fya_upload_dir() . $safeName;
    }

    function fya_upload_url($filename) {
        $safeName = basename((string) $filename);
        return '../uploads/' . rawurlencode($safeName);
    }

    function fya_image_exists($filename) {
        return !empty($filename) && is_file(fya_upload_path($filename));
    }

    function fya_image_src($filename) {
        return fya_image_exists($filename) ? fya_upload_url($filename) : '';
    }
}
