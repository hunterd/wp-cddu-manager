<?php
namespace CDDU_Manager;

class Autoloader {
    public static function init(): void {
        spl_autoload_register([__CLASS__, 'autoload']);
    }
    public static function autoload(string $class): void {
        if (strpos($class, __NAMESPACE__ . '\\') !== 0) { return; }
        $path = __DIR__ . '/' . str_replace('CDDU_Manager\\', '', $class) . '.php';
        $path = str_replace('\\', '/', $path);
        if (file_exists($path)) { require_once $path; }
    }
}
