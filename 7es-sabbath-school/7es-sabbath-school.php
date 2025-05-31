<?php
/*
Plugin Name: 7es Sabbath School
Description: Gestión de clases, miembros y reportes para Escuela Sabática/Unidades Misioneras. Mobile-first, arquitectura centralizada y auditoría total.
Version: 0.1.0
Author: Equipo Windsurf
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Seguridad: no acceso directo

define('SABBATH_SCHOOL_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SABBATH_SCHOOL_PLUGIN_URL', plugin_dir_url(__FILE__));

// Carga automática de clases
spl_autoload_register(function ($class) {
    if (strpos($class, 'SabbathSchool_') === 0) {
        $file = SABBATH_SCHOOL_PLUGIN_PATH . 'includes/class-' . strtolower(str_replace('SabbathSchool_', '', $class)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

// Activación: crear/migrar tablas
register_activation_hook(__FILE__, ['SabbathSchool_Install', 'activate']);
// (Opcional) Actualización futura
add_action('plugins_loaded', ['SabbathSchool_Install', 'maybe_update']);

// (Aquí se cargarán hooks, shortcodes, endpoints, etc.)
