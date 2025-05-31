<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Si ya está logueado, redirige al dashboard
if ( is_user_logged_in() ) {
    wp_safe_redirect( home_url('/sevenes-dashboard/') );
    exit;
}

// Procesar login propio
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['ss_login_nonce']) && wp_verify_nonce($_POST['ss_login_nonce'],'ss_login')) {
    $creds = [
        'user_login'    => sanitize_user($_POST['log']),
        'user_password' => $_POST['pwd'],
        'remember'      => !empty($_POST['rememberme'])
    ];
    $user = wp_signon($creds, false);
    if ( is_wp_error($user) ) {
        $error = $user->get_error_message();
    } else {
        wp_safe_redirect( home_url('/sevenes-dashboard/') );
        exit;
    }
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Iniciar sesión | 7es Sabbath School</title>
    <link rel="stylesheet" href="<?php echo SABBATH_SCHOOL_PLUGIN_URL.'assets/css/login.css'; ?>">
</head>
<body class="ss-login-bg">
    <div class="ss-login-container">
        <h1>7es Sabbath School</h1>
        <form method="post" class="ss-login-form">
            <?php wp_nonce_field('ss_login','ss_login_nonce'); ?>
            <div class="ss-form-group">
                <label>Usuario</label>
                <input type="text" name="log" required autofocus>
            </div>
            <div class="ss-form-group">
                <label>Contraseña</label>
                <input type="password" name="pwd" required>
            </div>
            <div class="ss-form-group">
                <label><input type="checkbox" name="rememberme"> Recordarme</label>
            </div>
            <button type="submit" class="ss-btn ss-btn-primary">Entrar</button>
            <?php if($error): ?><div class="ss-feedback-error"><?php echo esc_html($error); ?></div><?php endif; ?>
        </form>
        <div class="ss-login-brand">&copy; <?php echo date('Y'); ?> Windsurf</div>
    </div>
</body>
</html>
