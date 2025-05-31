// class-form.php
<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$class = isset($class) ? $class : null;
$editing = !!$class;
?>
<form id="ss-class-form" class="ss-form ss-form-vertical" autocomplete="off">
    <?php wp_nonce_field('sabbathschool_class'); ?>
    <input type="hidden" name="id" value="<?php echo $editing ? esc_attr($class->id) : ''; ?>">
    <div class="ss-form-group">
        <label><?php _e('Nombre de la clase/unidad','sabbathschool'); ?>*</label>
        <input type="text" name="name" value="<?php echo $editing ? esc_attr($class->name) : ''; ?>" required maxlength="100">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Maestro (ID miembro)','sabbathschool'); ?>*</label>
        <input type="number" name="teacher_id" value="<?php echo $editing ? esc_attr($class->teacher_id) : ''; ?>" required min="1">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Asistente (ID miembro)','sabbathschool'); ?></label>
        <input type="number" name="assistant_id" value="<?php echo $editing ? esc_attr($class->assistant_id) : ''; ?>" min="1">
    </div>
    <div class="ss-form-group">
        <label><?php _e('Descripción','sabbathschool'); ?></label>
        <input type="text" name="description" value="<?php echo $editing ? esc_attr($class->description) : ''; ?>" maxlength="255">
    </div>
    <div class="ss-form-group">
        <button type="submit" class="ss-btn ss-btn-primary"><?php _e('Guardar','sabbathschool'); ?></button>
        <button type="button" class="ss-btn ss-btn-cancel" id="ss-class-cancel"><?php _e('Cancelar','sabbathschool'); ?></button>
    </div>
</form>
