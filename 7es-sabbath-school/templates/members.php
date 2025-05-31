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
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Miembros | 7es Sabbath School</title>
    <link rel="stylesheet" href="<?php echo SABBATH_SCHOOL_PLUGIN_URL.'assets/css/dashboard.css'; ?>">
    <link rel="stylesheet" href="<?php echo SABBATH_SCHOOL_PLUGIN_URL.'assets/css/members.css'; ?>">
</head>
<body>
    <div class="ss-dashboard-wrapper">
        <header>
            <h1>Gestión de Miembros</h1>
            <nav>
                <a href="<?php echo home_url('/sevenes-dashboard/'); ?>">Dashboard</a> |
                <a href="<?php echo home_url('/sevenes-dashboard/classes/'); ?>">Clases</a> |
                <a href="<?php echo home_url('/sevenes-logout/'); ?>">Salir</a>
            </nav>
        </header>
        <main>
            <?php include SABBATH_SCHOOL_PLUGIN_PATH . 'templates/members-list.php'; ?>
        </main>
    </div>
    <script src="<?php echo includes_url('js/jquery/jquery.js'); ?>"></script>
    <script src="<?php echo SABBATH_SCHOOL_PLUGIN_URL.'assets/js/members.js'; ?>"></script>
    <script>var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>'; var ssMembers = { nonce: '<?php echo wp_create_nonce('sabbathschool_member'); ?>' };</script>
</body>
</html>
