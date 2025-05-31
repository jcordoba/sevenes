// layout.php
<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo isset($title) ? esc_html($title) : '7es Sabbath School'; ?></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SABBATH_SCHOOL_PLUGIN_URL.'assets/css/layout.css'; ?>">
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body class="ss-layout-bg">
    <div class="ss-overlay" id="ssOverlay"></div>
    <aside class="ss-sidebar" id="ssSidebar">
        <div class="ss-logo">
            <i class="fa-solid fa-leaf ss-logo-icon"></i> <span>7es Sabbath School</span>
        </div>
        <nav class="ss-nav">
            <a href="<?php echo home_url('/sevenes-dashboard/'); ?>" class="ss-nav-link<?php if($active==='dashboard') echo ' active'; ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="<?php echo home_url('/sevenes-dashboard/members/'); ?>" class="ss-nav-link<?php if($active==='members') echo ' active'; ?>"><i class="fa-solid fa-users"></i> Miembros</a>
            <a href="<?php echo home_url('/sevenes-dashboard/classes/'); ?>" class="ss-nav-link<?php if($active==='classes') echo ' active'; ?>"><i class="fa-solid fa-chalkboard-user"></i> Clases</a>
            <a href="<?php echo home_url('/sevenes-dashboard/attendance/'); ?>" class="ss-nav-link<?php if($active==='attendance') echo ' active'; ?>"><i class="fa-solid fa-calendar-check"></i> Asistencia</a>
            <a href="<?php echo home_url('/sevenes-dashboard/reports/'); ?>" class="ss-nav-link<?php if($active==='reports') echo ' active'; ?>"><i class="fa-solid fa-chart-pie"></i> Reportes</a>
            <a href="<?php echo home_url('/sevenes-logout/'); ?>" class="ss-nav-link"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
        </nav>
    </aside>
    <div class="ss-main">
        <header class="ss-header" role="banner">
            <button class="ss-sidebar-toggle" id="ssSidebarToggle" aria-label="Abrir menú">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="ss-header-center">
                <?php if (isset($breadcrumbs) && $breadcrumbs) {
                    echo $breadcrumbs;
                } elseif (isset($title)) {
                    echo '<span class=\"ss-header-title\">'.esc_html($title).'</span>';
                } ?>
            </div>
            <div class="ss-header-actions"></div>
        </header>
        <main class="ss-main-content">
            <?php if (isset($content)) echo $content; ?>
        </main>
    </div>
    <script>
    // Mobile sidebar toggle + overlay global
    (function() {
      var sidebar = document.getElementById('ssSidebar');
      var overlay = document.getElementById('ssOverlay');
      var toggle = document.getElementById('ssSidebarToggle');
      function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.remove('hide');
        overlay.style.display = 'block';
        document.body.classList.add('ss-menu-open');
      }
      function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.add('hide');
        setTimeout(function(){ overlay.style.display = 'none'; }, 180);
        document.body.classList.remove('ss-menu-open');
      }
      toggle.onclick = function(e) {
        if (!sidebar.classList.contains('open')) openSidebar();
        else closeSidebar();
      };
      overlay.onclick = function(e) { closeSidebar(); };
      window.addEventListener('resize', function() {
        if(window.innerWidth > 700) {
          closeSidebar();
        }
      });
    })();
    </script>
</body>
</html>
