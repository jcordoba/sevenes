<?php
/**
 * Handlers AJAX para miembros y clases/unidades
 * @package 7es-sabbath-school
 */
if ( ! defined( 'ABSPATH' ) ) exit;

require_once SABBATH_SCHOOL_PLUGIN_PATH . 'includes/class-members.php';
require_once SABBATH_SCHOOL_PLUGIN_PATH . 'includes/class-classes.php';
require_once SABBATH_SCHOOL_PLUGIN_PATH . 'includes/helpers.php';

// --- MIEMBROS ---
add_action('wp_ajax_sabbathschool_member_create', function() {
    SabbathSchool_AJAX_Helpers::verify_nonce_or_exit('sabbathschool_member');
    $result = SabbathSchool_Members::create($_POST);
    SabbathSchool_AJAX_Helpers::json_response($result);
});

add_action('wp_ajax_sabbathschool_member_update', function() {
    SabbathSchool_AJAX_Helpers::verify_nonce_or_exit('sabbathschool_member');
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $result = SabbathSchool_Members::update($id, $_POST);
    SabbathSchool_AJAX_Helpers::json_response($result);
});

add_action('wp_ajax_sabbathschool_member_deactivate', function() {
    SabbathSchool_AJAX_Helpers::verify_nonce_or_exit('sabbathschool_member');
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : '';
    $result = SabbathSchool_Members::deactivate($id, $reason);
    SabbathSchool_AJAX_Helpers::json_response($result);
});

add_action('wp_ajax_sabbathschool_member_list', function() {
    SabbathSchool_AJAX_Helpers::verify_nonce_or_exit('sabbathschool_member');
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $result = SabbathSchool_Members::search($search);
    SabbathSchool_AJAX_Helpers::json_response($result, true);
});

// --- CLASES/UNIDADES ---
add_action('wp_ajax_sabbathschool_class_create', function() {
    SabbathSchool_AJAX_Helpers::verify_nonce_or_exit('sabbathschool_class');
    $result = SabbathSchool_Classes::create($_POST);
    SabbathSchool_AJAX_Helpers::json_response($result);
});

add_action('wp_ajax_sabbathschool_class_update', function() {
    SabbathSchool_AJAX_Helpers::verify_nonce_or_exit('sabbathschool_class');
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $result = SabbathSchool_Classes::update($id, $_POST);
    SabbathSchool_AJAX_Helpers::json_response($result);
});

add_action('wp_ajax_sabbathschool_class_deactivate', function() {
    SabbathSchool_AJAX_Helpers::verify_nonce_or_exit('sabbathschool_class');
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : '';
    $result = SabbathSchool_Classes::deactivate($id, $reason);
    SabbathSchool_AJAX_Helpers::json_response($result);
});

add_action('wp_ajax_sabbathschool_class_list', function() {
    SabbathSchool_AJAX_Helpers::verify_nonce_or_exit('sabbathschool_class');
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $result = SabbathSchool_Classes::search($search);
    SabbathSchool_AJAX_Helpers::json_response($result, true);
});
