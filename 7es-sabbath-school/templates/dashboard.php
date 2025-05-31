// dashboard.php
<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url('/sevenes-login/') );
    exit;
}
$user = wp_get_current_user();

// Dummy data para cards (reemplazar por queries reales luego)
$cards = [
    [
        'icon' => 'fa-users',
        'color' => 'ss-blue',
        'title' => 'Miembros',
        'value' => '27+',
    ],
    [
        'icon' => 'fa-chalkboard-user',
        'color' => 'ss-green',
        'title' => 'Clases',
        'value' => '7',
    ],
    [
        'icon' => 'fa-calendar-check',
        'color' => 'ss-orange',
        'title' => 'Asistencia',
        'value' => '98%',
    ],
    [
        'icon' => 'fa-chart-pie',
        'color' => 'ss-red',
        'title' => 'Reportes',
        'value' => '15',
    ],
];

// Breadcrumbs no necesarios en dashboard
$breadcrumbs = '';

// Calendar dummy (puedes reemplazar por integración real luego)
$calendar = '<div class="ss-calendar-block"><h3 style="margin-top:0;font-size:1.1em;color:#2d72d9;font-weight:600;"><i class="fa-solid fa-calendar"></i> Calendario de Actividades</h3><div style="color:#888;font-size:1em;">[Aquí irá el calendario de actividades]</div></div>';

ob_start();
?>
<div class="ss-cards-row">
    <?php foreach($cards as $c): ?>
        <div class="ss-card <?php echo $c['color']; ?>">
            <span class="ss-card-icon"><i class="fa-solid <?php echo $c['icon']; ?>"></i></span>
            <span class="ss-card-title"><?php echo $c['title']; ?></span>
            <span class="ss-card-value"><?php echo $c['value']; ?></span>
        </div>
    <?php endforeach; ?>
</div>
<?php echo $calendar; ?>
<?php
$content = ob_get_clean();
$title = 'Dashboard | 7es Sabbath School';
$active = 'dashboard';
include SABBATH_SCHOOL_PLUGIN_PATH.'templates/layout.php';
