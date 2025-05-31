<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url('/sevenes-login/') );
    exit;
}
$user = wp_get_current_user();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard | 7es Sabbath School</title>
    <link rel="stylesheet" href="<?php echo SABBATH_SCHOOL_PLUGIN_URL.'assets/css/dashboard.css'; ?>">
</head>
<body>
    <div class="ss-dashboard-wrapper">
        <header>
            <h1>Bienvenido, <?php echo esc_html($user->display_name); ?></h1>
            <nav>
                <a href="<?php echo home_url('/sevenes-dashboard/members/'); ?>">Miembros</a> |
                <a href="<?php echo home_url('/sevenes-dashboard/classes/'); ?>">Clases</a> |
                <a href="<?php echo home_url('/sevenes-logout/'); ?>">Salir</a>
            </nav>
        </header>
        <main>
            <h2>Panel principal</h2>
            <p>Usa el menú para navegar por los módulos.</p>
        </main>
    </div>
</body>
</html>
