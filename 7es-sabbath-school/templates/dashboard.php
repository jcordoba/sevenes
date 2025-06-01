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
    <div class="ss-cards-row">
  <div class="ss-card ss-blue">
    <div class="ss-card-icon"><i class="fa-solid fa-users"></i></div>
    <div class="ss-card-title">Miembros</div>
    <div class="ss-card-value">27+</div>
  </div>
  <div class="ss-card ss-green">
    <div class="ss-card-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
    <div class="ss-card-title">Clases</div>
    <div class="ss-card-value">7</div>
  </div>
  <div class="ss-card ss-orange">
    <div class="ss-card-icon"><i class="fa-solid fa-calendar-check"></i></div>
    <div class="ss-card-title">Asistencia</div>
    <div class="ss-card-value">98%</div>
  </div>
  <div class="ss-card ss-red">
    <div class="ss-card-icon"><i class="fa-solid fa-chart-pie"></i></div>
    <div class="ss-card-title">Reportes</div>
    <div class="ss-card-value">15</div>
  </div>
</div>
    <?php echo $calendar; ?>
</main>
<?php
$content = ob_get_clean();
$title = 'Dashboard | 7es Sabbath School';
$active = 'dashboard';
include SABBATH_SCHOOL_PLUGIN_PATH.'templates/layout.php';
