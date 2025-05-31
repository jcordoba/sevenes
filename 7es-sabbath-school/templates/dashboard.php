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
$calendar = '<div class="ss-dashboard-calendar-block"><span class="ss-dashboard-calendar-placeholder">📅 Calendario de Actividades<br><small>(Aquí irá el calendario de actividades)</small></span></div>';

ob_start();
?>
<main class="ss-main-content">
    <div class="ss-dashboard-cards-wrapper">
        <?php foreach($cards as $c): ?>
            <div class="ss-dashboard-card ss-dashboard-card-<?php echo strtolower($c['title']); ?>">
                <i class="fa-solid <?php echo $c['icon']; ?> ss-dashboard-card-icon"></i>
                <div class="ss-dashboard-card-label"><?php echo $c['title']; ?></div>
                <div class="ss-dashboard-card-value"><?php echo $c['value']; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php echo $calendar; ?>
</main>
<?php
$content = ob_get_clean();
$title = 'Dashboard | 7es Sabbath School';
$active = 'dashboard';
include SABBATH_SCHOOL_PLUGIN_PATH.'templates/layout.php';
