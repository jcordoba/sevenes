<?php
/**
 * Formulario de alta/edición de miembro (mobile-first)
 * @package 7es-sabbath-school
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$member = isset($member) ? $member : null;
$editing = !!$member;
?>
<form id="ss-member-form" class="ss-form ss-form-vertical" autocomplete="off">
    <?php wp_nonce_field('sabbathschool_member'); ?>
    <input type="hidden" name="id" value="<?php echo $editing ? esc_attr($member->id) : ''; ?>">
    <div class="ss-form-group">
        <label><?php _e('Identificación','sabbathschool'); ?>*</label>
        <input type="text" name="identification" value="<?php echo $editing ? esc_attr($member->identification) : ''; ?>" required maxlength="50">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Nombres','sabbathschool'); ?>*</label>
        <input type="text" name="first_name" value="<?php echo $editing ? esc_attr($member->first_name) : ''; ?>" required maxlength="100">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Apellidos','sabbathschool'); ?>*</label>
        <input type="text" name="last_name" value="<?php echo $editing ? esc_attr($member->last_name) : ''; ?>" required maxlength="100">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Género','sabbathschool'); ?>*</label>
        <select name="gender" required>
            <option value="">--</option>
            <option value="M" <?php if($editing && $member->gender==='M') echo 'selected'; ?>><?php _e('Masculino','sabbathschool'); ?></option>
            <option value="F" <?php if($editing && $member->gender==='F') echo 'selected'; ?>><?php _e('Femenino','sabbathschool'); ?></option>
            <option value="Otro" <?php if($editing && $member->gender==='Otro') echo 'selected'; ?>><?php _e('Otro','sabbathschool'); ?></option>
        </select>
    </div>
    <div class="ss-form-group">
        <label><?php _e('Fecha de nacimiento','sabbathschool'); ?></label>
        <input type="date" name="birth_date" value="<?php echo $editing ? esc_attr($member->birth_date) : ''; ?>">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Teléfono','sabbathschool'); ?></label>
        <input type="text" name="phone" value="<?php echo $editing ? esc_attr($member->phone) : ''; ?>" maxlength="30">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Celular','sabbathschool'); ?></label>
        <input type="text" name="mobile" value="<?php echo $editing ? esc_attr($member->mobile) : ''; ?>" maxlength="30">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Email','sabbathschool'); ?></label>
        <input type="email" name="email" value="<?php echo $editing ? esc_attr($member->email) : ''; ?>" maxlength="100">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Rol','sabbathschool'); ?>*</label>
        <select name="role" required>
            <option value="alumno" <?php if($editing && $member->role==='alumno') echo 'selected'; ?>><?php _e('Alumno','sabbathschool'); ?></option>
            <option value="maestro" <?php if($editing && $member->role==='maestro') echo 'selected'; ?>><?php _e('Maestro','sabbathschool'); ?></option>
            <option value="practicante" <?php if($editing && $member->role==='practicante') echo 'selected'; ?>><?php _e('Practicante','sabbathschool'); ?></option>
            <option value="asistente" <?php if($editing && $member->role==='asistente') echo 'selected'; ?>><?php _e('Asistente','sabbathschool'); ?></option>
            <option value="visitante" <?php if($editing && $member->role==='visitante') echo 'selected'; ?>><?php _e('Visitante','sabbathschool'); ?></option>
        </select>
    </div>
    <div class="ss-form-group">
        <label><?php _e('Clase/Unidad actual','sabbathschool'); ?></label>
        <input type="number" name="class_id" value="<?php echo $editing ? esc_attr($member->class_id) : ''; ?>" min="1">
    </div>
    <div class="ss-form-group">
        <label><?php _e('¿Es nuevo converso?','sabbathschool'); ?></label>
        <input type="checkbox" name="is_new_convert" value="1" <?php if($editing && $member->is_new_convert) echo 'checked'; ?>>
    </div>
    <div class="ss-form-group">
        <label><?php _e('Año de conversión','sabbathschool'); ?></label>
        <input type="number" name="conversion_year" value="<?php echo $editing ? esc_attr($member->conversion_year) : ''; ?>" min="1900" max="2100">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Fecha de ingreso','sabbathschool'); ?></label>
        <input type="date" name="joined_at" value="<?php echo $editing ? esc_attr($member->joined_at) : ''; ?>">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Unidad misionera anterior','sabbathschool'); ?></label>
        <input type="number" name="ministry_unit_id" value="<?php echo $editing ? esc_attr($member->ministry_unit_id) : ''; ?>" min="1">
    </div>
    <div class="ss-form-group">
        <button type="submit" class="ss-btn ss-btn-primary"><?php _e('Guardar','sabbathschool'); ?></button>
        <button type="button" class="ss-btn ss-btn-cancel" id="ss-member-cancel"><?php _e('Cancelar','sabbathschool'); ?></button>
    </div>
</form>
