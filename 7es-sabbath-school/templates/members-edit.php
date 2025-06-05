<?php
/**
 * Plantilla para editar un miembro existente.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Salir si se accede directamente

// Verificar si el usuario está logueado y tiene permisos
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url('/sevenes-login/') );
    exit;
}
if ( ! current_user_can('manage_sabbath_members') ) {
    echo '<div class="ss-feedback-error">No tienes permisos para acceder a este módulo.</div>';
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'sapp_members';
$feedback_message = '';
$feedback_type = ''; // 'success' o 'error'

// Obtener el ID del miembro de la URL
$member_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : 0;

if ( ! $member_id ) {
    echo '<div class="ss-feedback-error">No se especificó un ID de miembro válido.</div>';
    // Podrías redirigir a la lista de miembros aquí
    // wp_safe_redirect(home_url('/sevenes-dashboard/members/'));
    // exit;
    // Por ahora, solo mostramos el error y detenemos la carga del formulario.
    // Necesitamos incluir el layout.php para que el error se muestre dentro del diseño.
    $title = 'Error | 7es Sabbath School';
    $active = 'members';
    $breadcrumbs = '<nav class="ss-breadcrumbs"><a href="'.home_url('/sevenes-dashboard/').'">Dashboard</a> <span>&gt;</span> <a href="'.home_url('/sevenes-dashboard/members/').'">Miembros</a> <span>&gt;</span> Error</nav>';
    ob_start();
    echo '<div class="ss-container"><h2>Error</h2><p>No se especificó un ID de miembro válido para editar.</p></div>';
    $content = ob_get_clean();
    include SABBATH_SCHOOL_PLUGIN_PATH.'templates/layout.php';
    exit;
}

// Cargar datos del miembro para edición
$member_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $member_id), ARRAY_A);

if ( ! $member_data ) {
    echo '<div class="ss-feedback-error">No se encontró el miembro con el ID especificado.</div>';
    $title = 'Error | 7es Sabbath School';
    $active = 'members';
    $breadcrumbs = '<nav class="ss-breadcrumbs"><a href="'.home_url('/sevenes-dashboard/').'">Dashboard</a> <span>&gt;</span> <a href="'.home_url('/sevenes-dashboard/members/').'">Miembros</a> <span>&gt;</span> Error</nav>';
    ob_start();
    echo '<div class="ss-container"><h2>Error</h2><p>No se encontró un miembro con el ID especificado.</p></div>';
    $content = ob_get_clean();
    include SABBATH_SCHOOL_PLUGIN_PATH.'templates/layout.php';
    exit;
}

// Inicializar variables del formulario con los datos del miembro
$v_identification = $member_data['identification'];
$v_first_name = $member_data['first_name'];
$v_last_name = $member_data['last_name'];
$v_birth_date = $member_data['birth_date'];
$v_phone = $member_data['phone'];
$v_mobile = $member_data['mobile'];
$v_email = $member_data['email'];
$v_address = $member_data['address'];
$v_baptism_date_form = isset($member_data['baptism_date']) && $member_data['baptism_date'] != '0000-00-00' ? date('d/m/Y', strtotime($member_data['baptism_date'])) : '';
$v_gender = $member_data['gender']; // 'M' o 'F'
$v_marital_status = $member_data['marital_status'];
$v_role = $member_data['role'];
// Campos que no se editan directamente aquí o tienen valores por defecto:
// class_id, unit_id, ministry_id, is_active, status, member_since, etc.

// Procesamiento del formulario cuando se envía
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_member_nonce']) ) {
    if ( ! wp_verify_nonce($_POST['update_member_nonce'], 'update_member_action') ) {
        $feedback_message = 'Error de seguridad. Inténtalo de nuevo.';
        $feedback_type = 'error';
    } else {
        // Sanitizar y obtener datos del POST
        $v_identification = isset($_POST['identification']) ? sanitize_text_field($_POST['identification']) : '';
        $v_first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $v_last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $v_birth_date = isset($_POST['birth_date']) ? sanitize_text_field($_POST['birth_date']) : ''; // Validar formato fecha después
        $v_phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $v_mobile = isset($_POST['mobile']) ? sanitize_text_field($_POST['mobile']) : '';
        $v_email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $v_address = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : ''; // Nuevo: obtener dirección
        $v_baptism_date_form = isset($_POST['baptism_date']) ? sanitize_text_field($_POST['baptism_date']) : '';
        $v_gender = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : ''; // 'M' o 'F'
        $v_marital_status = isset($_POST['marital_status']) ? sanitize_text_field($_POST['marital_status']) : '';
        $v_role = isset($_POST['role']) ? sanitize_key($_POST['role']) : 'alumno'; // 'alumno' o 'maestro'
        $v_class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : $v_class_id; // Repopular class_id o mantener el cargado
        $v_is_new_convert = isset($_POST['is_new']) ? 1 : 0;

        // Validaciones (similares a members-add.php, adaptadas para edición)
        // Identificación (obligatorio, longitud)
        if (empty($v_identification)) {
            $feedback_message = 'El número de identificación es obligatorio.';
            $feedback_type = 'error';
        } elseif (strlen($v_identification) < 3 || strlen($v_identification) > 50) {
            $feedback_message = 'La identificación debe tener entre 3 y 50 caracteres.';
            $feedback_type = 'error';
        }

        // Nombre y Apellido (obligatorio, longitud)
        if ($feedback_type !== 'error') {
            if (empty($v_first_name) || empty($v_last_name)) {
                $feedback_message = 'El nombre y el apellido son obligatorios.';
                $feedback_type = 'error';
            } elseif (strlen($v_first_name) > 100 || strlen($v_last_name) > 100) {
                $feedback_message = 'El nombre y el apellido no deben exceder los 100 caracteres.';
                $feedback_type = 'error';
            }
        }
        
        // Email (formato y unicidad, si se proporciona y es diferente al actual)
        if ($feedback_type !== 'error' && !empty($v_email)) {
            if (!filter_var($v_email, FILTER_VALIDATE_EMAIL)) {
                $feedback_message = 'El formato del email no es válido.';
                $feedback_type = 'error';
            } else {
                // Verificar unicidad solo si el email ha cambiado
                if (strtolower($v_email) !== strtolower($member_data['email'])) {
                    $email_exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE email = %s AND id != %d", $v_email, $member_id));
                    if ($email_exists) {
                        $feedback_message = 'Este email ya está registrado para otro miembro.';
                        $feedback_type = 'error';
                    }
                }
            }
        }

        // Identificación (unicidad, si ha cambiado)
        if ($feedback_type !== 'error') {
            if (strtolower($v_identification) !== strtolower($member_data['identification'])) {
                $id_exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE identification = %s AND id != %d", $v_identification, $member_id));
                if ($id_exists) {
                    $feedback_message = 'Este número de identificación ya está registrado para otro miembro.';
                    $feedback_type = 'error';
                }
            }
        }

        // Phone number validation (if provided)
        if ($feedback_type !== 'error' && !empty($v_phone)) {
            if (!preg_match('/^[0-9\s\+\-\(\)]*$/', $v_phone)) {
                $feedback_message = 'El formato del teléfono no es válido. Use números y opcionalmente +, -, (, ), espacios.';
                $feedback_type = 'error';
            } elseif (strlen($v_phone) > 30) {
                $feedback_message = 'El teléfono no debe exceder los 30 caracteres.';
                $feedback_type = 'error';
            } else {
                $digits_only_phone = preg_replace('/[^0-9]/', '', $v_phone);
                if (strlen($digits_only_phone) < 10) {
                    $feedback_message = 'El teléfono debe contener al menos 10 dígitos.';
                    $feedback_type = 'error';
                }
            }
        }

        // Mobile number validation (if provided)
        if ($feedback_type !== 'error' && !empty($v_mobile)) {
            if (!preg_match('/^[0-9\s\+\-\(\)]*$/', $v_mobile)) {
                $feedback_message = 'El formato del celular no es válido. Use números y opcionalmente +, -, (, ), espacios.';
                $feedback_type = 'error';
            } elseif (strlen($v_mobile) > 30) {
                $feedback_message = 'El celular no debe exceder los 30 caracteres.';
                $feedback_type = 'error';
            } else {
                $digits_only_mobile = preg_replace('/[^0-9]/', '', $v_mobile);
                if (strlen($digits_only_mobile) < 10) {
                    $feedback_message = 'El celular debe contener al menos 10 dígitos.';
                    $feedback_type = 'error';
                }
            }
        }

        // Marital Status (obligatorio)
        if ($feedback_type !== 'error' && empty($v_marital_status)) {
            $feedback_message = 'El estado civil es obligatorio.';
            $feedback_type = 'error';
        }

        // Class/Unit (obligatorio)
        if ($feedback_type !== 'error' && empty($v_class_id)) {
            $feedback_message = 'La clase/unidad es obligatoria.';
            $feedback_type = 'error';
        }

        // Si no hay errores, actualizar en la base de datos
        if ($feedback_type !== 'error') {
            $data_to_update = [
                'identification' => $v_identification,
                'first_name' => $v_first_name,
                'last_name' => $v_last_name,
                'birth_date' => !empty($v_birth_date) ? date('Y-m-d', strtotime($v_birth_date)) : null,
                'phone' => $v_phone,
                'mobile' => $v_mobile,
                'email' => $v_email,
                'address' => $v_address,
                'baptism_date' => !empty($v_baptism_date_form) ? date('Y-m-d', strtotime(str_replace('/', '-', $v_baptism_date_form))) : null,
                'gender' => $v_gender,
                'marital_status' => $v_marital_status,
                'class_id' => $v_class_id, // Nuevo: añadir class_id a la actualización
                'is_new_convert' => $v_is_new_convert,
                'role' => $v_role,
                'updated_at' => current_time('mysql', 1) // GMT
            ];

            // --- Registro de Auditoría ---
            $current_user_id = get_current_user_id();
            $audit_table_name = $wpdb->prefix . 'sapp_member_audit_log';

            // Campos a auditar (claves de $data_to_update, excluyendo 'updated_at')
            $fields_to_audit = [
                'identification' => 'Identificación',
                'first_name' => 'Nombres',
                'last_name' => 'Apellidos',
                'birth_date' => 'Fecha de Nacimiento',
                'phone' => 'Teléfono Fijo',
                'mobile' => 'Celular',
                'email' => 'Email',
                'address' => 'Dirección', // Nuevo: auditar dirección
                'baptism_date' => 'Fecha de Bautismo', // Nuevo: auditar fecha de bautismo
                'class_id' => 'Clase/Unidad', // Nuevo: auditar class_id
                'gender' => 'Género',
                'marital_status' => 'Estado Civil', // Nuevo: auditar estado civil
                'role' => 'Rol'
            ];

            foreach ($fields_to_audit as $field_key => $field_label) {
                $old_value = isset($member_data[$field_key]) ? $member_data[$field_key] : null;
                $new_value = isset($data_to_update[$field_key]) ? $data_to_update[$field_key] : null;

                // Comparar. Tratar null y string vacío como diferentes si uno es null y el otro no.
                // O si ambos no son null, comparar sus valores.
                $has_changed = false;
                if (is_null($old_value) !== is_null($new_value)) {
                    $has_changed = true;
                } elseif (!is_null($old_value) && $old_value != $new_value) { // != para comparación laxa (e.g. '0' != 0 es false)
                    $has_changed = true;
                }

                if ($has_changed) {
                    $wpdb->insert(
                        $audit_table_name,
                        [
                            'member_id' => $member_id,
                            'user_id' => $current_user_id,
                            'field_name' => $field_key, // Usar $field_label para un nombre más descriptivo si se prefiere
                            'old_value' => is_null($old_value) ? null : (string)$old_value,
                            'new_value' => is_null($new_value) ? null : (string)$new_value,
                            'changed_at' => current_time('mysql', 1)
                        ],
                        [
                            '%d', // member_id
                            '%d', // user_id
                            '%s', // field_name
                            '%s', // old_value
                            '%s', // new_value
                            '%s'  // changed_at
                        ]
                    );
                }
            }
            // --- Fin Registro de Auditoría ---

            $result = $wpdb->update($table_name, $data_to_update, ['id' => $member_id]);

            if ($result !== false) {
                $feedback_message = 'Miembro actualizado correctamente.';
                $feedback_type = 'success';
                // Recargar datos del miembro para mostrar los actualizados en el formulario
                $member_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $member_id), ARRAY_A);
                // Actualizar variables del formulario
                $v_identification = $member_data['identification'];
                $v_first_name = $member_data['first_name'];
                $v_last_name = $member_data['last_name'];
                $v_birth_date = $member_data['birth_date'];
                $v_phone = $member_data['phone'];
                $v_mobile = $member_data['mobile'];
                $v_email = $member_data['email'];
                $v_address = $member_data['address'];
                $v_baptism_date_form = isset($member_data['baptism_date']) && $member_data['baptism_date'] != '0000-00-00' ? date('d/m/Y', strtotime($member_data['baptism_date'])) : '';
                $v_gender = $member_data['gender'];
                $v_marital_status = $member_data['marital_status'];
                $v_class_id = $member_data['class_id'];
                $v_role = $member_data['role'];

            } else {
                $feedback_message = 'Hubo un error al actualizar el miembro o no se realizaron cambios.';
                $feedback_type = 'error';
            }
        }
    }
}

// Configuración para el layout
$title = 'Editar Miembro | 7es Sabbath School';
$active = 'members'; // Para resaltar en el menú
$breadcrumbs = '<nav class="ss-breadcrumbs"><a href="'.home_url('/sevenes-dashboard/').'">Dashboard</a> <span>&gt;</span> <a href="'.home_url('/sevenes-dashboard/members/').'">Miembros</a> <span>&gt;</span> Editar Miembro</nav>';

// Iniciar buffer para capturar el contenido del formulario
ob_start();
?>
<div class="ss-container">
    <h2>Editar Miembro: <?php echo esc_html($member_data['first_name'] . ' ' . $member_data['last_name']); ?></h2>

    <?php if ($feedback_message): ?>
        <div class="ss-feedback-<?php echo esc_attr($feedback_type); ?>">
            <?php echo esc_html($feedback_message); ?>
        </div>
    <?php endif; ?>

    <form id="edit-member-form" method="POST" action="" class="ss-form">
        <?php wp_nonce_field('update_member_action', 'update_member_nonce'); ?>
        <?php // El action="" hace que el formulario se envíe a la URL actual. ?>
        <?php // Se eliminó el input hidden name="action" ya que no se usa admin-post.php aquí. ?>
        <input type="hidden" name="member_id" value="<?php echo esc_attr($member_id); ?>">

        <div class="ss-form-row">
            <div class="ss-form-group ss-form-group-half">
                <label for="identification">Identificación <span class="ss-required">*</span></label>
                <input type="text" id="identification" name="identification" value="<?php echo esc_attr($v_identification); ?>" required maxlength="50">
            </div>
            <div class="ss-form-group ss-form-group-half">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo esc_attr($v_email); ?>" maxlength="100">
            </div>
        </div>

        <div class="ss-form-row">
            <div class="ss-form-group ss-form-group-half">
                <label for="first_name">Nombres <span class="ss-required">*</span></label>
                <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($v_first_name); ?>" required maxlength="100">
            </div>
            <div class="ss-form-group ss-form-group-half">
                <label for="last_name">Apellidos <span class="ss-required">*</span></label>
                <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($v_last_name); ?>" required maxlength="100">
            </div>
        </div>
        
        <div class="ss-form-row">
            <div class="ss-form-group ss-form-group-half">
                <label for="birth_date">Fecha de Nacimiento</label>
                <input type="text" id="birth_date" name="birth_date" value="<?php echo esc_attr($v_birth_date); ?>" placeholder="dd/mm/yyyy">
            </div>
            <div class="ss-form-group ss-form-group-half">
                <label for="gender">Género <span class="ss-required">*</span></label>
                <select id="gender" name="gender" required>
                    <option value="M" <?php selected($v_gender, 'M'); ?>>Masculino</option>
                    <option value="F" <?php selected($v_gender, 'F'); ?>>Femenino</option>
                </select>
            </div>
        </div>

        <div class="ss-form-row">
            <div class="ss-form-group ss-form-group-half">
                <label for="phone">Teléfono Fijo</label>
                <input type="tel" id="phone" name="phone" value="<?php echo esc_attr($v_phone); ?>" maxlength="30">
            </div>
            <div class="ss-form-group ss-form-group-half">
                <label for="mobile">Celular</label>
                <input type="tel" id="mobile" name="mobile" value="<?php echo esc_attr($v_mobile); ?>" maxlength="30">
            </div>
        </div>

        <div class="ss-form-row">
            <div class="ss-form-group ss-form-group-half">
                <label for="marital_status">Estado Civil <span class="ss-required">*</span></label>
                <select id="marital_status" name="marital_status" required>
                    <option value="" <?php selected($v_marital_status, ''); ?>>Seleccione...</option>
                    <option value="Soltero/a" <?php selected($v_marital_status, 'Soltero/a'); ?>>Soltero/a</option>
                    <option value="Casado/a" <?php selected($v_marital_status, 'Casado/a'); ?>>Casado/a</option>
                    <option value="Viudo/a" <?php selected($v_marital_status, 'Viudo/a'); ?>>Viudo/a</option>
                    <option value="Divorciado/a" <?php selected($v_marital_status, 'Divorciado/a'); ?>>Divorciado/a</option>
                    <option value="Unión Libre" <?php selected($v_marital_status, 'Unión Libre'); ?>>Unión Libre</option>
                </select>
            </div>
            <div class="ss-form-group ss-form-group-half">
                <label for="baptism_date">Fecha de Bautismo</label>
                <input type="text" id="baptism_date" name="baptism_date" value="<?php echo esc_attr($v_baptism_date_form); ?>" placeholder="dd/mm/yyyy">
            </div>
        </div>

        <div class="ss-form-group">
            <label for="address">Dirección</label>
            <input type="text" id="address" name="address" value="<?php echo esc_attr($v_address); ?>" maxlength="255">
        </div>

        <div class="ss-form-group">
             <label for="role">Rol en la Escuela Sabática <span class="ss-required">*</span></label>
            <select id="role" name="role" required>
                <option value="alumno" <?php selected($v_role, 'alumno'); ?>>Alumno</option>
                <option value="maestro" <?php selected($v_role, 'maestro'); ?>>Maestro</option>
            </select>
        </div>

        <div class="ss-form-actions">
            <button type="submit" class="ss-btn ss-btn-primary">Actualizar Miembro</button>
            <a href="<?php echo esc_url(home_url('/sevenes-dashboard/members/')); ?>" class="ss-btn ss-btn-cancel">Cancelar</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean(); // Capturar el contenido del formulario
include SABBATH_SCHOOL_PLUGIN_PATH.'templates/layout.php'; // Incluir el layout general
?>
