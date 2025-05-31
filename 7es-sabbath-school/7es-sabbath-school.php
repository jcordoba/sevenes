<?php
/*
Plugin Name: 7es Sabbath School
Description: Gestión de clases, miembros y reportes para Escuela Sabática/Unidades Misioneras.
Version: 0.1.0
Author: Equipo Javier Córdoba - Windsurf - ChatGPT
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
// --- SISTEMA DE RUTAS CUSTOM SEVENES ---

// 1. Añadir rewrite rules al activar/desactivar
register_activation_hook(__FILE__, function(){
    SabbathSchool_Install::activate();
    add_sevenes_rewrite_rules();
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, function(){
    flush_rewrite_rules();
});
function add_sevenes_rewrite_rules() {
    add_rewrite_rule('^sevenes-login/?$', 'index.php?sevenes_route=login', 'top');
    add_rewrite_rule('^sevenes-logout/?$', 'index.php?sevenes_route=logout', 'top');
    add_rewrite_rule('^sevenes-dashboard/?$', 'index.php?sevenes_route=dashboard', 'top');
    add_rewrite_rule('^sevenes-dashboard/members/?$', 'index.php?sevenes_route=members', 'top');
    add_rewrite_rule('^sevenes-dashboard/classes/?$', 'index.php?sevenes_route=classes', 'top');
}
add_action('init', 'add_sevenes_rewrite_rules');

// 2. Añadir query var custom
add_filter('query_vars', function($vars){ $vars[] = 'sevenes_route'; return $vars; });

// 3. Interceptar template para rutas custom
add_filter('template_include', function($template){
    $route = get_query_var('sevenes_route');
    if(!$route) return $template;
    switch($route) {
        case 'login':
            include SABBATH_SCHOOL_PLUGIN_PATH.'templates/login.php'; exit;
        case 'logout':
            wp_logout();
            wp_safe_redirect( home_url('/sevenes-login/') ); exit;
        case 'dashboard':
            include SABBATH_SCHOOL_PLUGIN_PATH.'templates/dashboard.php'; exit;
        case 'members':
            include SABBATH_SCHOOL_PLUGIN_PATH.'templates/members.php'; exit;
        case 'classes':
            include SABBATH_SCHOOL_PLUGIN_PATH.'templates/classes.php'; exit;
    }
    return $template;
});

// --- FIN SISTEMA DE RUTAS CUSTOM ---
// (Aquí se cargarán hooks, shortcodes, endpoints, etc.)
