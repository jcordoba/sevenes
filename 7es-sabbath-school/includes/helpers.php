<?php
/**
 * Helpers generales y para AJAX (nonces, respuestas JSON)
 * @package 7es-sabbath-school
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class SabbathSchool_AJAX_Helpers {
    /**
     * Verifica nonce para AJAX o termina la ejecución
     */
    public static function verify_nonce_or_exit($action) {
        $nonce = isset($_POST['_ajax_nonce']) ? $_POST['_ajax_nonce'] : '';
        if (!wp_verify_nonce($nonce, $action)) {
            wp_send_json_error(['message' => __('Nonce de seguridad inválido.', 'sabbathschool')]);
            exit;
        }
    }

    /**
     * Devuelve respuesta JSON estándar
     * @param mixed $result
     * @param bool $list Si es listado, siempre success
     */
    public static function json_response($result, $list = false) {
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        } elseif ($list) {
            wp_send_json_success(['results' => $result]);
        } else {
            wp_send_json_success(['result' => $result]);
        }
        exit;
    }
}
