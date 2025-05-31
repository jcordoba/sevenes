<?php
/**
 * Clase para gestión de miembros (CRUD, transferencias, roles, baja lógica, historial)
 * @package 7es-sabbath-school
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SabbathSchool_Members {
    /**
     * Crea un nuevo miembro
     * @param array $data Datos del miembro
     * @return int|WP_Error ID del miembro creado o error
     */
    public static function create($data) {
        if ( ! current_user_can('manage_sabbath_members') ) {
            return new WP_Error('forbidden', __('No tienes permisos para crear miembros.', 'sabbathschool'));
        }
        global $wpdb;
        $prefix = $wpdb->prefix;

        // Sanitización y validación estricta
        $identification = isset($data['identification']) ? sanitize_text_field($data['identification']) : '';
        $first_name     = isset($data['first_name']) ? sanitize_text_field($data['first_name']) : '';
        $last_name      = isset($data['last_name']) ? sanitize_text_field($data['last_name']) : '';
        $gender         = isset($data['gender']) && in_array($data['gender'], ['M','F','Otro']) ? $data['gender'] : '';
        $birth_date     = isset($data['birth_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['birth_date']) ? $data['birth_date'] : null;
        $phone          = isset($data['phone']) ? sanitize_text_field($data['phone']) : '';
        $mobile         = isset($data['mobile']) ? sanitize_text_field($data['mobile']) : '';
        $email          = isset($data['email']) ? sanitize_email($data['email']) : '';
        $class_id       = isset($data['class_id']) ? intval($data['class_id']) : null;
        $role           = isset($data['role']) && in_array($data['role'], ['alumno','maestro','practicante','asistente','visitante']) ? $data['role'] : 'alumno';
        $is_new_convert = !empty($data['is_new_convert']) ? 1 : 0;
        $conversion_year= isset($data['conversion_year']) && preg_match('/^\d{4}$/', $data['conversion_year']) ? $data['conversion_year'] : null;
        $joined_at      = isset($data['joined_at']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['joined_at']) ? $data['joined_at'] : current_time('Y-m-d');
        $ministry_unit_id = isset($data['ministry_unit_id']) ? intval($data['ministry_unit_id']) : null;

        if (empty($identification) || empty($first_name) || empty($last_name) || empty($gender)) {
            return new WP_Error('missing_fields', __('Faltan campos obligatorios.', 'sabbathschool'));
        }

        // Validación de unicidad
        if (self::exists_identification($identification)) {
            return new WP_Error('duplicate_identification', __('Ya existe un miembro con esa identificación.', 'sabbathschool'));
        }
        if (!empty($email) && self::exists_email($email)) {
            return new WP_Error('duplicate_email', __('Ya existe un miembro con ese email.', 'sabbathschool'));
        }

        $insert = [
            'identification'     => $identification,
            'first_name'         => $first_name,
            'last_name'          => $last_name,
            'gender'             => $gender,
            'birth_date'         => $birth_date,
            'phone'              => $phone,
            'mobile'             => $mobile,
            'email'              => $email,
            'class_id'           => $class_id,
            'role'               => $role,
            'status_id'          => 1, // Activo por defecto
            'is_new_convert'     => $is_new_convert,
            'conversion_year'    => $conversion_year,
            'joined_at'          => $joined_at,
            'ministry_unit_id'   => $ministry_unit_id,
            'updated_at'         => current_time('mysql', 1),
        ];
        $result = $wpdb->insert($prefix.'sapp_members', $insert);
        $member_id = $wpdb->insert_id;

        if ($result && $member_id) {
            // Registrar en historial
            self::add_history([
                'member_id'  => $member_id,
                'status'     => 'Activo',
                'role'       => $role,
                'class_id'   => $class_id,
                'start_date' => $joined_at,
                'observations' => __('Alta de miembro', 'sabbathschool'),
                'user_id'    => get_current_user_id(),
            ]);
            return $member_id;
        }
        return new WP_Error('db_insert_error', __('No se pudo crear el miembro.', 'sabbathschool'));
    }

    /**
     * Edita miembro existente
     * @param int $id ID del miembro
     * @param array $data Datos a actualizar
     * @return bool|WP_Error
     */
    public static function update($id, $data) {
        if ( ! current_user_can('manage_sabbath_members') ) {
            return new WP_Error('forbidden', __('No tienes permisos para editar miembros.', 'sabbathschool'));
        }
        global $wpdb;
        $prefix = $wpdb->prefix;
        $miembro = self::get($id);
        if (!$miembro) return new WP_Error('not_found', __('Miembro no encontrado.', 'sabbathschool'));

        // Sanitización y validación
        $update = [];
        if (isset($data['identification'])) {
            $identification = sanitize_text_field($data['identification']);
            if ($identification !== $miembro->identification && self::exists_identification($identification)) {
                return new WP_Error('duplicate_identification', __('Ya existe un miembro con esa identificación.', 'sabbathschool'));
            }
            $update['identification'] = $identification;
        }
        if (isset($data['email'])) {
            $email = sanitize_email($data['email']);
            if ($email && $email !== $miembro->email && self::exists_email($email)) {
                return new WP_Error('duplicate_email', __('Ya existe un miembro con ese email.', 'sabbathschool'));
            }
            $update['email'] = $email;
        }
        foreach ([
            'first_name', 'last_name', 'phone', 'mobile', 'ministry_unit_id'
        ] as $field) {
            if (isset($data[$field])) $update[$field] = sanitize_text_field($data[$field]);
        }
        if (isset($data['gender']) && in_array($data['gender'], ['M','F','Otro'])) {
            $update['gender'] = $data['gender'];
        }
        if (isset($data['birth_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['birth_date'])) {
            $update['birth_date'] = $data['birth_date'];
        }
        if (isset($data['class_id'])) {
            $update['class_id'] = intval($data['class_id']);
        }
        if (isset($data['role']) && in_array($data['role'], ['alumno','maestro','practicante','asistente','visitante'])) {
            $update['role'] = $data['role'];
        }
        if (isset($data['is_new_convert'])) {
            $update['is_new_convert'] = !empty($data['is_new_convert']) ? 1 : 0;
        }
        if (isset($data['conversion_year']) && preg_match('/^\d{4}$/', $data['conversion_year'])) {
            $update['conversion_year'] = $data['conversion_year'];
        }
        if (isset($data['joined_at']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['joined_at'])) {
            $update['joined_at'] = $data['joined_at'];
        }
        if (empty($update)) return false;
        $update['updated_at'] = current_time('mysql', 1);

        $res = $wpdb->update($prefix.'sapp_members', $update, ['id' => $id]);
        if ($res !== false) {
            // Registrar en historial si cambió clase, rol o status
            if (
                (isset($update['class_id']) && $update['class_id'] != $miembro->class_id) ||
                (isset($update['role']) && $update['role'] != $miembro->role)
            ) {
                self::add_history([
                    'member_id'  => $id,
                    'status'     => $miembro->status_id == 1 ? 'Activo' : 'Otro',
                    'role'       => $update['role'] ?? $miembro->role,
                    'class_id'   => $update['class_id'] ?? $miembro->class_id,
                    'start_date' => current_time('Y-m-d'),
                    'observations' => __('Actualización de miembro', 'sabbathschool'),
                    'user_id'    => get_current_user_id(),
                ]);
            }
            return true;
        }
        return new WP_Error('db_update_error', __('No se pudo actualizar el miembro.', 'sabbathschool'));
    }

    /**
     * Baja lógica (cambia status y deja historial)
     */
    public static function deactivate($id, $reason = 'Inactivo') {
        if ( ! current_user_can('manage_sabbath_members') ) {
            return new WP_Error('forbidden', __('No tienes permisos para dar de baja miembros.', 'sabbathschool'));
        }
        global $wpdb;
        $prefix = $wpdb->prefix;
        $miembro = self::get($id);
        if (!$miembro) return new WP_Error('not_found', __('Miembro no encontrado.', 'sabbathschool'));
        $wpdb->update($prefix.'sapp_members', [
            'status_id' => 2, // Inactivo
            'updated_at' => current_time('mysql', 1)
        ], ['id' => $id]);
        self::add_history([
            'member_id'  => $id,
            'status'     => sanitize_text_field($reason),
            'role'       => $miembro->role,
            'class_id'   => $miembro->class_id,
            'start_date' => current_time('Y-m-d'),
            'observations' => __('Baja lógica', 'sabbathschool'),
            'user_id'    => get_current_user_id(),
        ]);
        return true;
    }

    /**
     * Transferencia de clase (actualiza class_id y deja historial)
     */
    public static function transfer_class($id, $new_class_id) {
        if ( ! current_user_can('manage_sabbath_members') ) {
            return new WP_Error('forbidden', __('No tienes permisos para transferir miembros.', 'sabbathschool'));
        }
        $miembro = self::get($id);
        if (!$miembro) return new WP_Error('not_found', __('Miembro no encontrado.', 'sabbathschool'));
        $new_class_id = intval($new_class_id);
        if ($new_class_id <= 0) {
            return new WP_Error('invalid_class', __('Clase destino no válida.', 'sabbathschool'));
        }
        return self::update($id, [
            'class_id' => $new_class_id
        ]);
    }

    /**
     * Cambio de rol (actualiza role y deja historial)
     */
    public static function change_role($id, $new_role) {
        if ( ! current_user_can('manage_sabbath_members') ) {
            return new WP_Error('forbidden', __('No tienes permisos para cambiar el rol de miembros.', 'sabbathschool'));
        }
        $miembro = self::get($id);
        if (!$miembro) return new WP_Error('not_found', __('Miembro no encontrado.', 'sabbathschool'));
        $roles = ['alumno','maestro','practicante','asistente','visitante'];
        if (!in_array($new_role, $roles)) {
            return new WP_Error('invalid_role', __('Rol no válido.', 'sabbathschool'));
        }
        return self::update($id, [
            'role' => $new_role
        ]);
    }

    /**
     * Obtiene un miembro por ID
     */
    public static function get($id) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$prefix}sapp_members WHERE id=%d", $id));
    }

    /**
     * Verifica si existe identificación
     */
    public static function exists_identification($identification) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        return (bool) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$prefix}sapp_members WHERE identification=%s", $identification));
    }

    /**
     * Verifica si existe email
     */
    public static function exists_email($email) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        return (bool) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$prefix}sapp_members WHERE email=%s", $email));
    }

    /**
     * Agrega registro al historial de estados
     * @param array $data
     */
    public static function add_history($data) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $insert = [
            'member_id'    => $data['member_id'],
            'status'       => $data['status'],
            'role'         => $data['role'],
            'class_id'     => $data['class_id'],
            'start_date'   => $data['start_date'],
            'end_date'     => $data['end_date'] ?? null,
            'observations' => $data['observations'] ?? '',
            'user_id'      => $data['user_id'],
            'created_at'   => current_time('mysql', 1),
        ];
        $wpdb->insert($prefix.'sapp_member_status_history', $insert);
    }

    /**
     * Carga condicional de assets para miembros
     */
    public static function enqueue_assets() {
        if (!self::is_members_page()) return;
        wp_enqueue_style('sabbathschool-members', SABBATH_SCHOOL_PLUGIN_URL . 'assets/css/members.css', [], '1.0');
        wp_enqueue_script('sabbathschool-members', SABBATH_SCHOOL_PLUGIN_URL . 'assets/js/members.js', ['jquery'], '1.0', true);
    }

    /**
     * Búsqueda/listado de miembros (mobile-first)
     * @param string $search
     * @return array
     */
    public static function search($search = '') {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $where = '1=1';
        $params = [];
        if ($search) {
            $where .= " AND (first_name LIKE %s OR last_name LIKE %s OR identification LIKE %s OR email LIKE %s)";
            $like = '%' . $wpdb->esc_like($search) . '%';
            $params = array_fill(0, 4, $like);
        }
        $sql = "SELECT * FROM {$prefix}sapp_members WHERE $where ORDER BY last_name, first_name LIMIT 100";
        return $wpdb->get_results($wpdb->prepare($sql, ...$params));
    }

    /**
     * Helper: ¿Estamos en la página de miembros?
     */
    public static function is_members_page() {
        // Aquí puedes personalizar la lógica según el slug/shortcode/ruta
        return isset($_GET['sabbathschool']) && $_GET['sabbathschool'] === 'members';
    }
}

// Hook para cargar assets sólo en módulo miembros
add_action('wp_enqueue_scripts', ['SabbathSchool_Members', 'enqueue_assets']);
