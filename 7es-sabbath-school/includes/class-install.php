<?php
/**
 * Clase de instalación y migración de tablas para 7es Sabbath School
 * @package 7es-sabbath-school
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SabbathSchool_Install {
    /**
     * Ejecuta la creación/migración de tablas en activación
     */
    public static function activate() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix;

        // Registrar capacidades personalizadas para el sistema
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('manage_sabbath_members');
            $role->add_cap('manage_sabbath_classes');
        }
        
        // Tabla de miembros
        $sql_members = "CREATE TABLE {$prefix}sapp_members (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            identification VARCHAR(50) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            gender ENUM('M','F','Otro') NOT NULL,
            birth_date DATE NULL,
            phone VARCHAR(30) NULL,
            mobile VARCHAR(30) NULL,
            email VARCHAR(100) NULL,
            class_id INT UNSIGNED NULL,
            role ENUM('alumno','maestro','practicante','asistente','visitante') NOT NULL DEFAULT 'alumno',
            marital_status VARCHAR(50) NULL,
            address VARCHAR(255) NULL,
            baptism_date DATE NULL,
            status_id INT NOT NULL DEFAULT 1, -- Para estados más detallados (transferido, etc.)
            is_active TINYINT(1) NOT NULL DEFAULT 1, -- Para activo/inactivo simple
            is_new_convert TINYINT(1) NOT NULL DEFAULT 0,
            conversion_year YEAR NULL,
            joined_at DATE NULL, -- Fecha en que se unió a la iglesia/escuela sabática
            ministry_unit_id INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Tabla de clases/unidades
        $sql_classes = "CREATE TABLE {$prefix}sapp_classes (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            church_id INT UNSIGNED NULL,
            district_id INT UNSIGNED NULL,
            teacher_id INT UNSIGNED NULL,
            assistant_id INT UNSIGNED NULL,
            status_id INT NOT NULL DEFAULT 1,
            created_at DATE NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Tabla de iglesias
        $sql_church = "CREATE TABLE {$prefix}sapp_church (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            district VARCHAR(100) NULL,
            address VARCHAR(200) NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Tabla de asistencia
        $sql_attendance = "CREATE TABLE {$prefix}sapp_member_attendance (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id INT UNSIGNED NOT NULL,
            class_id INT UNSIGNED NOT NULL,
            week_number TINYINT NOT NULL,
            date DATE NOT NULL,
            is_present TINYINT(1) NOT NULL DEFAULT 1,
            role ENUM('alumno','maestro','practicante') NOT NULL DEFAULT 'alumno',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Tabla de reportes estadísticos
        $sql_stat_reports = "CREATE TABLE {$prefix}sapp_statistical_reports (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            class_id INT UNSIGNED NOT NULL,
            sabbath_date DATE NOT NULL,
            week_number TINYINT NOT NULL,
            members_present INT NOT NULL DEFAULT 0,
            study_daily INT NOT NULL DEFAULT 0,
            offerings DECIMAL(10,2) NOT NULL DEFAULT 0,
            visits INT NOT NULL DEFAULT 0,
            branches INT NOT NULL DEFAULT 0,
            souls INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Tabla de reportes misioneros
        $sql_missionary_reports = "CREATE TABLE {$prefix}sapp_missionary_reports (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            class_id INT UNSIGNED NOT NULL,
            sabbath_date DATE NOT NULL,
            week_number TINYINT NOT NULL,
            converts INT NOT NULL DEFAULT 0,
            teacher_visits INT NOT NULL DEFAULT 0,
            discipleship INT NOT NULL DEFAULT 0,
            fellowship INT NOT NULL DEFAULT 0,
            mission_project VARCHAR(255) NULL,
            branch_members INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Tabla de evaluaciones
        $sql_evaluations = "CREATE TABLE {$prefix}sapp_evaluations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id INT UNSIGNED NOT NULL,
            class_id INT UNSIGNED NOT NULL,
            criterion VARCHAR(100) NOT NULL,
            score INT NOT NULL,
            period_id INT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Tabla de historial de estados
        $sql_status_history = "CREATE TABLE {$prefix}sapp_member_status_history (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id INT UNSIGNED NOT NULL,
            status ENUM('Nuevo converso','Egresado','Transferido','Activo','Inactivo','Otro') NOT NULL,
            role ENUM('alumno','maestro','practicante','asistente','visitante') NOT NULL,
            class_id INT UNSIGNED NULL,
            start_date DATE NOT NULL,
            end_date DATE NULL,
            observations TEXT NULL,
            user_id INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Ejecutar todas las queries
        dbDelta($sql_members);
        dbDelta($sql_classes);
        dbDelta($sql_church);
        dbDelta($sql_attendance);
        dbDelta($sql_stat_reports);
        dbDelta($sql_missionary_reports);
        dbDelta($sql_evaluations);
        dbDelta($sql_status_history);

        // Tabla de registro de auditoría para miembros
        $sql_member_audit_log = "CREATE TABLE {$prefix}sapp_member_audit_log (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id INT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            field_name VARCHAR(100) NOT NULL,
            old_value TEXT NULL,
            new_value TEXT NULL,
            PRIMARY KEY (id),
            KEY member_id (member_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql_member_audit_log);
    }

    /**
     * Hook para futuras migraciones/actualizaciones
     */
    public static function maybe_update() {
        // Aquí se podría manejar lógica de migraciones futuras
    }
}
