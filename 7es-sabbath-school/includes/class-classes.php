<?php
/**
 * Clase para gestión de clases/unidades misioneras (CRUD, baja lógica, historial)
 * @package 7es-sabbath-school
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SabbathSchool_Classes {
    /**
     * Crea una nueva clase/unidad
     * @param array $data Datos de la clase
     * @return int|WP_Error ID de la clase creada o error
     */
    public static function create($data) {
        if ( ! current_user_can('manage_sabbath_classes') ) {
            return new WP_Error('forbidden', __('No tienes permisos para crear clases/unidades.', 'sabbathschool'));
        }
        global $wpdb;
        $prefix = $wpdb->prefix;

        // Sanitización y validación
        $name = isset($data['name']) ? sanitize_text_field($data['name']) : '';
        $church_id = isset($data['church_id']) ? intval($data['church_id']) : null;
        $district_id = isset($data['district_id']) ? intval($data['district_id']) : null;
        $teacher_id = isset($data['teacher_id']) ? intval($data['teacher_id']) : null;
        $assistant_id = isset($data['assistant_id']) ? intval($data['assistant_id']) : null;
        $status_id = isset($data['status_id']) ? intval($data['status_id']) : 1;
        $created_at = isset($data['created_at']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['created_at']) ? $data['created_at'] : current_time('Y-m-d');
        $updated_at = current_time('mysql', 1);

        if (empty($name)) {
            return new WP_Error('missing_fields', __('El nombre es obligatorio.', 'sabbathschool'));
        }
        if ($teacher_id && !self::member_exists($teacher_id)) {
            return new WP_Error('invalid_teacher', __('El maestro asignado no existe.', 'sabbathschool'));
        }
        if ($assistant_id && !self::member_exists($assistant_id)) {
            return new WP_Error('invalid_assistant', __('El asistente asignado no existe.', 'sabbathschool'));
        }

        $insert = [
            'name' => $name,
            'church_id' => $church_id,
            'district_id' => $district_id,
            'teacher_id' => $teacher_id,
            'assistant_id' => $assistant_id,
            'status_id' => $status_id,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];
        $result = $wpdb->insert($prefix.'sapp_classes', $insert);
        $class_id = $wpdb->insert_id;

        if ($result && $class_id) {
            self::add_history([
                'class_id' => $class_id,
                'status' => $status_id,
                'teacher_id' => $teacher_id,
                'assistant_id' => $assistant_id,
                'action' => __('Alta de clase/unidad', 'sabbathschool'),
                'user_id' => get_current_user_id(),
            ]);
            return $class_id;
        }
        return new WP_Error('db_insert_error', __('No se pudo crear la clase/unidad.', 'sabbathschool'));
    }

    /**
     * Edita una clase/unidad existente
     */
    public static function update($id, $data) {
        if ( ! current_user_can('manage_sabbath_classes') ) {
            return new WP_Error('forbidden', __('No tienes permisos para editar clases/unidades.', 'sabbathschool'));
        }
        global $wpdb;
        $prefix = $wpdb->prefix;
        $clase = self::get($id);
        if (!$clase) return new WP_Error('not_found', __('Clase/unidad no encontrada.', 'sabbathschool'));

        $update = [];
        if (isset($data['name'])) $update['name'] = sanitize_text_field($data['name']);
        if (isset($data['church_id'])) $update['church_id'] = intval($data['church_id']);
        if (isset($data['district_id'])) $update['district_id'] = intval($data['district_id']);
        if (isset($data['teacher_id'])) {
            $teacher_id = intval($data['teacher_id']);
            if ($teacher_id && !self::member_exists($teacher_id)) {
                return new WP_Error('invalid_teacher', __('El maestro asignado no existe.', 'sabbathschool'));
            }
            $update['teacher_id'] = $teacher_id;
        }
        if (isset($data['assistant_id'])) {
            $assistant_id = intval($data['assistant_id']);
            if ($assistant_id && !self::member_exists($assistant_id)) {
                return new WP_Error('invalid_assistant', __('El asistente asignado no existe.', 'sabbathschool'));
            }
            $update['assistant_id'] = $assistant_id;
        }
        if (isset($data['status_id'])) $update['status_id'] = intval($data['status_id']);
        $update['updated_at'] = current_time('mysql', 1);

        if (empty($update)) return false;
        $res = $wpdb->update($prefix.'sapp_classes', $update, ['id' => $id]);
        if ($res !== false) {
            self::add_history([
                'class_id' => $id,
                'status' => $update['status_id'] ?? $clase->status_id,
                'teacher_id' => $update['teacher_id'] ?? $clase->teacher_id,
                'assistant_id' => $update['assistant_id'] ?? $clase->assistant_id,
                'action' => __('Actualización de clase/unidad', 'sabbathschool'),
                'user_id' => get_current_user_id(),
            ]);
            return true;
        }
        return new WP_Error('db_update_error', __('No se pudo actualizar la clase/unidad.', 'sabbathschool'));
    }

    /**
     * Baja lógica de clase/unidad (status = 2, historial)
     */
    public static function deactivate($id, $reason = 'Inactiva') {
        if ( ! current_user_can('manage_sabbath_classes') ) {
            return new WP_Error('forbidden', __('No tienes permisos para dar de baja clases/unidades.', 'sabbathschool'));
        }
        global $wpdb;
        $prefix = $wpdb->prefix;
        $clase = self::get($id);
        if (!$clase) return new WP_Error('not_found', __('Clase/unidad no encontrada.', 'sabbathschool'));
        $wpdb->update($prefix.'sapp_classes', [
            'status_id' => 2, // Inactiva
            'updated_at' => current_time('mysql', 1)
        ], ['id' => $id]);
        self::add_history([
            'class_id' => $id,
            'status' => 2,
            'teacher_id' => $clase->teacher_id,
            'assistant_id' => $clase->assistant_id,
            'action' => sanitize_text_field($reason),
            'user_id' => get_current_user_id(),
        ]);
        return true;
    }

    /**
     * Obtiene una clase/unidad por ID
     */
    public static function get($id) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$prefix}sapp_classes WHERE id=%d", $id));
    }

    /**
     * Verifica si existe un miembro (por id)
     */
    public static function member_exists($member_id) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        return (bool) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$prefix}sapp_members WHERE id=%d", $member_id));
    }

    /**
     * Agrega registro al historial de clases/unidades
     * @param array $data
     */
    public static function add_history($data) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $insert = [
            'class_id'      => $data['class_id'],
            'status'        => $data['status'],
            'teacher_id'    => $data['teacher_id'],
            'assistant_id'  => $data['assistant_id'],
            'action'        => $data['action'],
            'user_id'       => $data['user_id'],
            'created_at'    => current_time('mysql', 1),
        ];
        $wpdb->insert($prefix.'sapp_classes_history', $insert);
    }

    /**
     * Carga condicional de assets para clases/unidades
     */
    public static function enqueue_assets() {
        if (!self::is_classes_page()) return;
        wp_enqueue_style('sabbathschool-classes', SABBATH_SCHOOL_PLUGIN_URL . 'assets/css/classes.css', [], '1.0');
        wp_enqueue_script('sabbathschool-classes', SABBATH_SCHOOL_PLUGIN_URL . 'assets/js/classes.js', ['jquery'], '1.0', true);
    }

    /**
     * Helper: ¿Estamos en la página de clases/unidades?
     */
    public static function is_classes_page() {
        return isset($_GET['sabbathschool']) && $_GET['sabbathschool'] === 'classes';
    }
}

add_action('wp_enqueue_scripts', ['SabbathSchool_Classes', 'enqueue_assets']);
