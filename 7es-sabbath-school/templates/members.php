// members.php
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
$extra_css = '<link rel="stylesheet" href="'.SABBATH_SCHOOL_PLUGIN_URL.'assets/css/members.css">';

// Scripts para el módulo
add_action('wp_footer', function() {
    ?>
    <script src="<?php echo includes_url('js/jquery/jquery.js'); ?>"></script>
    <script src="<?php echo SABBATH_SCHOOL_PLUGIN_URL.'assets/js/members.js'; ?>"></script>
    <script>var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>'; var ssMembers = { nonce: '<?php echo wp_create_nonce('sabbathschool_member'); ?>' };</script>
    <?php
}, 100);

include SABBATH_SCHOOL_PLUGIN_PATH.'templates/layout.php';
