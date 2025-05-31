<?php
/**
 * Listado y búsqueda de miembros (mobile-first)
 * @package 7es-sabbath-school
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="ss-members-wrapper">
    <form id="ss-member-search-form" class="ss-form ss-form-inline" autocomplete="off">
        <input type="text" name="search" id="ss-member-search" placeholder="<?php _e('Buscar miembro...','sabbathschool'); ?>" />
        <button type="submit" class="ss-btn ss-btn-primary"><?php _e('Buscar','sabbathschool'); ?></button>
        <button type="button" id="ss-member-add" class="ss-btn ss-btn-success"><?php _e('Nuevo','sabbathschool'); ?></button>
    </form>
    <div id="ss-member-feedback" class="ss-feedback"></div>
    <div id="ss-members-list" class="ss-members-list"></div>
    <div id="ss-member-form-modal" class="ss-modal" style="display:none;"></div>
</div>
