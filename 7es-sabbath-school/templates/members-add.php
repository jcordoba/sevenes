<?php
/**
 * Alta de nuevo miembro - 7es Sabbath School
 * URL: /sevenes-dashboard/members/add/
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url('/sevenes-login/') );
    exit;
}
if ( ! current_user_can('manage_sabbath_members') ) {
    // Ensure this check is done before any potential output from form processing
    // If we place form processing logic above, this check might be too late if an error occurs during processing
    // For now, keeping it here, but will be mindful if feedback needs to be displayed before this check.
    echo '<div class="ss-feedback-error">No tienes permisos para acceder a este módulo.</div>';
    // It's better to include the layout for a consistent error page
    // For simplicity now, just exiting. Consider integrating with layout.php for errors.
    exit;
}

// Initialize feedback and form value variables
$feedback_message = '';
$feedback_type = '';

// Variables to hold form values for re-population or clearing
$v_first_name = '';
$v_last_name = '';
$v_identification = '';
$v_gender = '';
$v_birthdate = ''; // Will store YYYY-MM-DD for <input type="date">
$v_phone = '';
$v_mobile = '';
$v_email = '';
$v_role = '';
$v_marital_status = ''; // New field
$v_class_name_form = ''; // For the 'class' text input from form
$v_is_new_convert = 0; // Checkbox, 0 by default
$v_baptism_date_form = ''; // Will store YYYY-MM-DD for <input type="date">
$v_unit_entry_form = ''; // For the 'unit_entry' text input from form

// --- Handle Form Submission --- 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify nonce
    if (!isset($_POST['ss_add_member_nonce']) || !wp_verify_nonce($_POST['ss_add_member_nonce'], 'ss_add_member_action')) {
        $feedback_message = 'Error de seguridad. Por favor, intenta de nuevo.';
        $feedback_type = 'error';
    } else {
        // Nonce is valid, proceed with data sanitization and validation
        global $wpdb;
        // Assuming table name, adjust if different. Example: $wpdb->prefix . 's_s_members' or similar
        $table_name = $wpdb->prefix . 'sapp_members'; // Updated table name 

        // Sanitize and populate form value variables from POST data
        $v_first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $v_last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $v_identification = isset($_POST['identification']) ? sanitize_text_field($_POST['identification']) : '';
        $v_gender = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '';
        $v_birthdate = isset($_POST['birthdate']) ? sanitize_text_field($_POST['birthdate']) : ''; // Keep as string for form value
        $v_phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $v_mobile = isset($_POST['mobile']) ? sanitize_text_field($_POST['mobile']) : '';
        $v_email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $v_address = isset($_POST['address']) ? sanitize_text_field(stripslashes($_POST['address'])) : ''; // Añadido para dirección
        $v_role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
        $v_marital_status = isset($_POST['marital_status']) ? sanitize_text_field($_POST['marital_status']) : '';
        $v_class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0; // Nuevo: obtener class_id
        $v_is_new_convert = isset($_POST['is_new']) ? 1 : 0;
        $v_baptism_date_form = isset($_POST['baptism_date']) ? sanitize_text_field($_POST['baptism_date']) : ''; // Keep as string for form value
        
        // For DB insertion, convert empty date strings to null
        $db_birthdate = !empty($v_birthdate) ? date('Y-m-d', strtotime($v_birthdate)) : null;
        $db_baptism_date = !empty($v_baptism_date_form) ? date('Y-m-d', strtotime($v_baptism_date_form)) : null;

        // Basic validation (example: required fields)
        if (empty($v_first_name) || empty($v_last_name) || empty($v_identification) || empty($v_gender) || empty($v_role) || empty($v_marital_status) || empty($v_class_id)) { // Añadido chequeo para class_id
            $feedback_message = 'Por favor, completa todos los campos obligatorios (*).';
            $feedback_type = 'error';
            if (empty($v_class_id)) $field_errors['class_id'] = 'La clase es obligatoria.';
        } else {
            // Advanced Validations (Unique identification and email)
            $id_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE identification = %s", $v_identification));
            if ($id_exists > 0) {
                $feedback_message = 'El número de identificación ya existe en la base de datos.';
                $feedback_type = 'error';
            }

            if (!empty($v_email) && $feedback_type !== 'error') { // Only check email if it's provided and no previous error
                $email_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE email = %s", $v_email));
                if ($email_exists > 0) {
                    $feedback_message = 'La dirección de correo electrónico ya existe en la base de datos.';
                    $feedback_type = 'error';
                }
            }

            // Further validations: field formats and lengths
            if ($feedback_type !== 'error') {
                // Email format validation (if provided)
                if (!empty($v_email) && !filter_var($v_email, FILTER_VALIDATE_EMAIL)) {
                    $feedback_message = 'El formato del correo electrónico no es válido.';
                    $feedback_type = 'error';
                }
            }

            if ($feedback_type !== 'error') {
                // Phone number validation (if provided)
                if (!empty($v_phone)) {
                    if (!preg_match('/^[0-9\s\+\-\(\)]*$/', $v_phone)) {
                        $feedback_message = 'El formato del teléfono no es válido. Use números y opcionalmente +, -, (, ), espacios.';
                        $feedback_type = 'error';
                    } else {
                        $digits_only_phone = preg_replace('/[^0-9]/', '', $v_phone);
                        if (strlen($digits_only_phone) < 10) {
                            $feedback_message = 'El teléfono debe contener al menos 10 dígitos.';
                            $feedback_type = 'error';
                        }
                    }
                }
            }

            if ($feedback_type !== 'error') {
                // Mobile number validation (if provided)
                if (!empty($v_mobile)) {
                    if (!preg_match('/^[0-9\s\+\-\(\)]*$/', $v_mobile)) {
                        $feedback_message = 'El formato del celular no es válido. Use números y opcionalmente +, -, (, ), espacios.';
                        $feedback_type = 'error';
                    } else {
                        $digits_only_mobile = preg_replace('/[^0-9]/', '', $v_mobile);
                        if (strlen($digits_only_mobile) < 10) {
                            $feedback_message = 'El celular debe contener al menos 10 dígitos.';
                            $feedback_type = 'error';
                        }
                    }
                }
            }

            // Length validations
            if ($feedback_type !== 'error') {
                if (strlen($v_identification) > 50) {
                    $feedback_message = 'El identificador no puede tener más de 50 caracteres.';
                    $feedback_type = 'error';
                } elseif (strlen($v_identification) < 3) { // Example min length
                    $feedback_message = 'El identificador debe tener al menos 3 caracteres.';
                    $feedback_type = 'error';
                }
            }
            if ($feedback_type !== 'error') {
                if (strlen($v_first_name) > 100) {
                    $feedback_message = 'El nombre no puede tener más de 100 caracteres.';
                    $feedback_type = 'error';
                }
            }
            if ($feedback_type !== 'error') {
                if (strlen($v_last_name) > 100) {
                    $feedback_message = 'El apellido no puede tener más de 100 caracteres.';
                    $feedback_type = 'error';
                }
            }
            if ($feedback_type !== 'error') {
                if (!empty($v_phone) && strlen($v_phone) > 30) { // Max length for phone
                    $feedback_message = 'El teléfono no puede tener más de 30 caracteres.';
                    $feedback_type = 'error';
                }
            }
            if ($feedback_type !== 'error') {
                if (!empty($v_mobile) && strlen($v_mobile) > 30) { // Max length for mobile
                    $feedback_message = 'El celular no puede tener más de 30 caracteres.';
                    $feedback_type = 'error';
                }
            }

            // Proceed with insertion only if no errors so far
            if ($feedback_type !== 'error') {
            // All basic validation passed, attempt to insert into DB
            // Convert form gender to DB ENUM (M/F)
            $db_gender = '';
            if ($v_gender === 'Masculino') $db_gender = 'M';
            if ($v_gender === 'Femenino') $db_gender = 'F';
            // Add 'Otro' if your form supports it and it maps to 'Otro' in ENUM

            // Convert form role to DB ENUM (lowercase)
            $db_role = strtolower($v_role);

            $data_to_insert = [
                'first_name' => $v_first_name,
                'last_name' => $v_last_name,
                'identification' => $v_identification,
                'gender' => $db_gender, // Converted from $v_gender
                'birth_date' => $db_birthdate, // Null if empty, from $v_birthdate
                'phone' => $v_phone,
                'mobile' => $v_mobile,
                'email' => $v_email,
                'address' => $v_address, // Campo de dirección añadido
                'role' => $db_role, // Converted from $v_role
                'marital_status' => $v_marital_status,
                'baptism_date' => $db_baptism_date, // Null if empty, from $v_baptism_date_form
                'is_new_convert' => $v_is_new_convert,
                'is_active' => 1, // Nuevo campo, por defecto activo
                'class_id' => $v_class_id, // Nuevo: guardar class_id
                // 'joined_at' => null, // Opcional: si tienes un campo específico para esto en el formulario
                // 'status_id' => 1, // Si quieres setear un status_id específico
                'created_at' => current_time('mysql', 1), // Nuevo campo
                'updated_at' => current_time('mysql', 1) // También establecer updated_at en la creación
            ];
            // El campo 'status' de la tabla original fue reemplazado/mejorado por 'status_id' y 'is_active'
            // El campo 'member_since' fue reemplazado por 'joined_at' o se puede manejar con 'created_at'

            
            // Filter out null values for optional date fields to avoid DB errors if columns don't allow NULL and have no default
            $data_to_insert = array_filter($data_to_insert, function($value) { return $value !== null; });

            $inserted = $wpdb->insert($table_name, $data_to_insert);

            if ($inserted === false) {
                $feedback_message = 'Error al guardar el miembro: ' . $wpdb->last_error;
                $feedback_type = 'error';
            } else {
                $feedback_message = '¡Miembro guardado exitosamente!';
                $feedback_type = 'success';
                // Clear form field values for next render
                $v_first_name = ''; $v_last_name = ''; $v_identification = ''; $v_gender = '';
                $v_birthdate = ''; $v_phone = ''; $v_mobile = ''; $v_email = ''; $v_address = ''; $v_role = '';
                $v_marital_status = ''; $v_class_id = 0; $v_is_new_convert = 0; // Limpiar class_id
                $v_baptism_date_form = '';
            }
          } // End of if ($feedback_type !== 'error') for advanced validation
        }
    }
}
// --- End Handle Form Submission ---

if ( ! current_user_can('manage_sabbath_members') ) {
    echo '<div class="ss-feedback-error">No tienes permisos para acceder a este módulo.</div>';
    exit;
}

// Breadcrumbs
$breadcrumbs = '<nav class="ss-breadcrumbs"><a href="'.home_url('/sevenes-dashboard/').'">Dashboard</a> <span>&gt;</span> <a href="'.home_url('/sevenes-dashboard/members/').'">Miembros</a> <span>&gt;</span> Nuevo</nav>';
$title = 'Nuevo Miembro | 7es Sabbath School';
$active = 'members';

ob_start();
?>
<div class="ss-members-wrapper">
    <div class="ss-card ss-card-form">
        <h2 class="ss-title">Nuevo Miembro</h2>
        <form id="ss-member-add-form" class="ss-form ss-form-vertical" autocomplete="off" method="post">
            <?php wp_nonce_field('ss_add_member_action', 'ss_add_member_nonce'); ?>
            <div class="ss-form-group">
                <label>Nombre(s)*</label>
                <input type="text" name="first_name" required maxlength="100" value="<?php echo esc_attr($v_first_name); ?>" />
            </div>
            <div class="ss-form-group">
                <label>Apellido(s)*</label>
                <input type="text" name="last_name" required maxlength="100" value="<?php echo esc_attr($v_last_name); ?>" />
            </div>
            <div class="ss-form-group">
                <label>Identificador*</label>
                <input type="text" name="identification" required maxlength="50" value="<?php echo esc_attr($v_identification); ?>" />
            </div>
            <div class="ss-form-group">
                <label>Género*</label>
                <select name="gender" required>
                    <option value="">--</option>
                    <option value="Masculino" <?php selected($v_gender, "Masculino"); ?>>Masculino</option>
                    <option value="Femenino" <?php selected($v_gender, "Femenino"); ?>>Femenino</option>
                </select>
            </div>
            <div class="ss-form-group">
                <label>Fecha de nacimiento</label>
                <input type="date" name="birthdate" value="<?php echo esc_attr($v_birthdate); ?>" />
            </div>
            <div class="ss-form-group">
                <label>Estado Civil*</label>
                <select name="marital_status" required>
                    <option value="">--</option>
                    <?php
                    $marital_status_options = ["Soltero/a", "Casado/a", "Viudo/a", "Divorciado/a", "Unión Libre"];
                    foreach ($marital_status_options as $option) {
                        echo '<option value="' . esc_attr($option) . '" ' . selected($v_marital_status, $option, false) . '>' . esc_html($option) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="ss-form-group">
                <label>Teléfono</label>
                <input type="text" name="phone" maxlength="30" value="<?php echo esc_attr($v_phone); ?>" />
            </div>
            <div class="ss-form-group">
                <label>Celular</label>
                <input type="text" name="mobile" maxlength="30" value="<?php echo esc_attr($v_mobile); ?>" />
            </div>
            <div class="ss-form-group">
                <label>Email</label>
                <input type="email" name="email" maxlength="100" value="<?php echo esc_attr($v_email); ?>" />
            </div>
            <div class="ss-form-group">
                <label>Dirección</label>
                <input type="text" name="address" maxlength="255" value="<?php echo esc_attr($v_address); ?>" />
            </div>
            <div class="ss-form-group">
                <label>Rol*</label>
                <select name="role" required>
                    <option value="">--</option>
                    <option value="Alumno" <?php selected($v_role, "Alumno"); ?>>Alumno</option>
                    <option value="Maestro" <?php selected($v_role, "Maestro"); ?>>Maestro</option>
                    <option value="Secretario" <?php selected($v_role, "Secretario"); ?>>Secretario</option>
                    <option value="Director" <?php selected($v_role, "Director"); ?>>Director</option>
                </select>
            </div>
            <div class="ss-form-group">
                <label for="class_id">Clase/Unidad actual <span class="ss-required">*</span></label>
                <select id="class_id" name="class_id" required>
                    <option value="">-- Selecciona una clase --</option>
                    <?php
                    global $wpdb;
                    $classes_table = $wpdb->prefix . 'sapp_classes';
                    $classes = $wpdb->get_results("SELECT id, class_name FROM $classes_table ORDER BY class_name ASC");
                    if ($classes) {
                        foreach ($classes as $class) {
                            echo '<option value="' . esc_attr($class->id) . '"' . selected($v_class_id, $class->id, false) . '>' . esc_html($class->class_name) . '</option>';
                        }
                    }
                    ?>
                </select>
                <?php if (isset($field_errors['class_id'])): ?><span class="ss-error-message"><?php echo $field_errors['class_id']; ?></span><?php endif; ?>
            </div>
            <div class="ss-form-group">
                <label><input type="checkbox" name="is_new" value="1" <?php checked($v_is_new_convert, 1); ?> /> ¿Es nuevo converso?</label>
            </div>
            <div class="ss-form-group">
                <label>Año de conversión</label>
                <input type="date" name="baptism_date" value="<?php echo esc_attr($v_baptism_date_form); ?>" />
            </div>
            <div class="ss-form-actions">
                <button type="submit" class="ss-btn ss-btn-primary">Guardar</button>
                <a href="/sevenes-dashboard/members/" class="ss-btn ss-btn-cancel">Cancelar</a>
            </div>
        </form>
        <div id="ss-member-add-feedback" class="ss-feedback <?php if (!empty($feedback_message)) echo 'ss-feedback-' . esc_attr($feedback_type); ?>">
            <?php if (!empty($feedback_message)) echo esc_html($feedback_message); ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$extra_css = '<link rel="stylesheet" href="'.SABBATH_SCHOOL_PLUGIN_URL.'assets/css/members.css">';
include SABBATH_SCHOOL_PLUGIN_PATH.'templates/layout.php';
