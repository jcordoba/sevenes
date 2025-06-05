<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url('/sevenes-login/') );
    exit;
}
if ( ! current_user_can('manage_sabbath_members') ) {
    echo '<div class="ss-feedback-error">No tienes permisos para acceder a este módulo.</div>';
    exit;
}

// Breadcrumbs
$breadcrumbs = '<nav class="ss-breadcrumbs"><a href="'.home_url('/sevenes-dashboard/').'">Dashboard</a> <span>&gt;</span> Miembros</nav>';
$title = 'Miembros | 7es Sabbath School';
$active = 'members';

ob_start();
include SABBATH_SCHOOL_PLUGIN_PATH . 'templates/members-list.php';
$content = ob_get_clean();
// $extra_css = '<link rel="stylesheet" href="'.SABBATH_SCHOOL_PLUGIN_URL.'assets/css/members.css">'; // Will be enqueued properly

// Scripts y estilos para el módulo
add_action('wp_enqueue_scripts', function() {
    // Solo encolar en la página de miembros
    // Necesitamos una forma de verificar si estamos en la página de miembros aquí.
    // Por ahora, asumiremos que este archivo (members.php) solo se carga para la ruta de miembros.
    // Si members.php es un template cargado por template_include, esta condición es implícita.

    $members_css_path = SABBATH_SCHOOL_PLUGIN_PATH . 'assets/css/members.css';
    $members_css_url = SABBATH_SCHOOL_PLUGIN_URL . 'assets/css/members.css';
    if (file_exists($members_css_path)) {
        wp_enqueue_style('sabbathschool-members-style', $members_css_url, [], filemtime($members_css_path));
    }

    wp_enqueue_script('jquery'); // Enqueue WordPress's jQuery

    $members_js_path = SABBATH_SCHOOL_PLUGIN_PATH . 'assets/js/members.js';
    $members_js_url = SABBATH_SCHOOL_PLUGIN_URL . 'assets/js/members.js';
    if (file_exists($members_js_path)) {
        wp_enqueue_script('sabbathschool-members-script', $members_js_url, ['jquery'], filemtime($members_js_path), true);
    }
    
    // Localize script para pasar nonce y ajaxurl de forma segura
    wp_localize_script('sabbathschool-members-script', 'ssMembers', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('sabbathschool_member') // Considerar un nonce más específico si es necesario
    ]);

}, 100); // La prioridad 100 es bastante tardía, podría ser antes si no hay dependencias de otros scripts encolados en wp_footer

// Si $extra_css se usaba en layout.php, asegúrate de que layout.php no lo necesite o ajústalo.
// Por ahora, asumimos que el layout principal no depende de $extra_css para esta página específica.

include SABBATH_SCHOOL_PLUGIN_PATH.'templates/layout.php';
