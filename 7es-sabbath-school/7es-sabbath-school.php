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

// Shortcode para vista de miembros
add_shortcode('ss_members', function(){
    ob_start();
    include SABBATH_SCHOOL_PLUGIN_PATH . 'templates/members-list.php';
    return ob_get_clean();
});

// AJAX: formulario de alta/edición (modal)
add_action('wp_ajax_sabbathschool_member_form', function(){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $member = $id ? SabbathSchool_Members::get($id) : null;
    include SABBATH_SCHOOL_PLUGIN_PATH . 'templates/member-form.php';
    exit;
});

// Carga condicional de assets para miembros
add_action('wp_enqueue_scripts', function(){
    if(!is_singular() && !is_page()) return;
    global $post;
    if(isset($post->post_content) && strpos($post->post_content,'[ss_members')!==false){
        wp_enqueue_style('ss-members', SABBATH_SCHOOL_PLUGIN_URL.'assets/css/members.css',[], '1.0');
        wp_enqueue_script('ss-members', SABBATH_SCHOOL_PLUGIN_URL.'assets/js/members.js',['jquery'], '1.0', true);
        wp_localize_script('ss-members','ssMembers',[ 'nonce'=> wp_create_nonce('sabbathschool_member') ]);
    }
});
// (Aquí se cargarán hooks, shortcodes, endpoints, etc.)
