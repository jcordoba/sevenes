// classes.php
<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url('/sevenes-login/') );
    exit;
}
if ( ! current_user_can('manage_sabbath_classes') ) {
    echo '<div class="ss-feedback-error">No tienes permisos para acceder a este módulo.</div>';
    exit;
}

// Breadcrumbs
$breadcrumbs = '<nav class="ss-breadcrumbs"><a href="'.home_url('/sevenes-dashboard/').'">Dashboard</a> <span>&gt;</span> Clases</nav>';
$title = 'Clases/Unidades | 7es Sabbath School';
$active = 'classes';

ob_start();
?>
<div class="ss-classes-wrapper">
    <form id="ss-class-search-form" class="ss-form ss-form-inline" autocomplete="off">
        <input type="text" name="search" id="ss-class-search" placeholder="<?php _e('Buscar clase...','sabbathschool'); ?>" />
        <button type="submit" class="ss-btn ss-btn-primary"><?php _e('Buscar','sabbathschool'); ?></button>
        <button type="button" id="ss-class-add" class="ss-btn ss-btn-success"><?php _e('Nueva','sabbathschool'); ?></button>
    </form>
    <div id="ss-class-feedback" class="ss-feedback"></div>
    <div id="ss-classes-list" class="ss-classes-list"></div>
    <div id="ss-class-form-modal" class="ss-modal" style="display:none;"></div>
</div>
<?php
$content = ob_get_clean();
$extra_css = '<link rel="stylesheet" href="'.SABBATH_SCHOOL_PLUGIN_URL.'assets/css/classes.css">';

// Scripts para el módulo
add_action('wp_footer', function() {
    ?>
    <script src="<?php echo includes_url('js/jquery/jquery.js'); ?>"></script>
    <script src="<?php echo SABBATH_SCHOOL_PLUGIN_URL.'assets/js/classes.js'; ?>"></script>
    <script>var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>'; var ssClasses = { nonce: '<?php echo wp_create_nonce('sabbathschool_class'); ?>' };</script>
    <?php
}, 100);

include SABBATH_SCHOOL_PLUGIN_PATH.'templates/layout.php';
