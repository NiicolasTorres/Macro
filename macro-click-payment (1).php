<?php
/**
 * Plugin Name: MacroClick Payment Gateway
 * Description: Integración de pago MacroClick para WooCommerce.
 * Version: 1.1
 * Author: fixiecommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'init_macroclick_gateway_class');

function init_macroclick_gateway_class() {
    if (!class_exists('WC_Payment_Gateway')) return;

    class WC_MacroClick_Gateway extends WC_Payment_Gateway {

        public function __construct() {
            $this->id = 'macroclick';
            $this->icon = '';
            $this->has_fields = true;
            $this->method_title = 'Macro Click de Pago';
            $this->method_description = 'Integración con Macro Click de Pago para WooCommerce.';

            $this->init_form_fields(); 
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->guid = $this->get_option('guid');
            $this->frase = $this->get_option('frase');
            $this->caja_codigo = $this->get_option('caja_codigo');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable Macro Click de Pago',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no',
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'Title that users see during checkout.',
                    'default'     => 'Macro Click de Pago',
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'Description that users see during checkout.',
                    'default'     => 'Pay securely using Macro Click de Pago.',
                ),
                'guid' => array(
                    'title'       => 'GUID',
                    'type'        => 'text',
                    'description' => 'GUID proporcionado por Macro Click de Pago.',
                    'default'     => '',
                ),
                'secret_key' => array(
                    'title'       => 'Secret Key',
                    'type'        => 'password',
                    'description' => 'Clave secreta proporcionada por Macro Click de Pago.',
                    'default'     => '',
                ),
                'frase' => array(
                    'title'       => 'Frase Secreta',
                    'type'        => 'password',
                    'description' => 'Frase secreta proporcionada por Macro Click de Pago.',
                    'default'     => '',
                ),
            );
        }

        public function payment_fields() {
            ?>
            <fieldset>
                <p class="form-row form-row-wide">
                    <label for="macroclick-card-number"><?php _e('Número de tarjeta', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input id="macroclick-card-number" name="macroclick_card_number" type="text" class="input-text" autocomplete="off" placeholder="<?php _e('Ingresa el número de tu tarjeta', 'woocommerce'); ?>" />
                </p>
                <p class="form-row form-row-first">
                    <label for="macroclick-expiry-date"><?php _e('Fecha de vencimiento (MM/YY)', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input id="macroclick-expiry-date" name="macroclick_expiry_date" type="text" class="input-text" autocomplete="off" placeholder="<?php _e('MM/YY', 'woocommerce'); ?>" />
                </p>
                <p class="form-row form-row-last">
                    <label for="macroclick-cvc"><?php _e('Código de seguridad (CVC)', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input id="macroclick-cvc" name="macroclick_cvc" type="text" class="input-text" autocomplete="off" placeholder="<?php _e('Ingresa el código de seguridad', 'woocommerce'); ?>" />
                </p>
                <p class="form-row form-row-wide">
                    <label for="macroclick-documento-titular"><?php _e('Documento del titular', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input id="macroclick-documento-titular" name="macroclick_documento_titular" type="text" class="input-text" autocomplete="off" placeholder="<?php _e('Número de documento del titular', 'woocommerce'); ?>" />
                </p>
                <p class="form-row form-row-wide">
                    <label for="macroclick-email"><?php _e('Email del titular', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input id="macroclick-email" name="macroclick_email" type="email" class="input-text" autocomplete="off" placeholder="<?php _e('Email del titular', 'woocommerce'); ?>" />
                </p>
                <p class="form-row form-row-wide">
                    <label for="macroclick-fecha-nacimiento"><?php _e('Fecha de nacimiento del titular', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input id="macroclick-fecha-nacimiento" name="macroclick_fecha_nacimiento" type="text" class="input-text" autocomplete="off" placeholder="<?php _e('DD/MM/AAAA', 'woocommerce'); ?>" />
                </p>
                <p class="form-row form-row-wide">
                    <label for="macroclick-tipo-documento"><?php _e('Tipo de documento', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input id="macroclick-tipo-documento" name="macroclick_tipo_documento" type="text" class="input-text" autocomplete="off" placeholder="<?php _e('Tipo de documento del titular', 'woocommerce'); ?>" />
                </p>
                <p class="form-row form-row-wide">
                    <label for="macroclick-titular-tarjeta"><?php _e('Titular de la tarjeta', 'woocommerce'); ?> <span class="required">*</span></label>
                    <input id="macroclick-titular-tarjeta" name="macroclick_titular_tarjeta" type="text" class="input-text" autocomplete="off" placeholder="<?php _e('Nombre completo del titular de la tarjeta', 'woocommerce'); ?>" />
                </p>
                <p class="form-row form-row-wide">
                    <label for="macroclick-cantidad-cuotas"><?php _e('Cantidad de Cuotas', 'woocommerce'); ?></label>
                    <select name="macroclick_cantidad_cuotas" id="macroclick-cantidad-cuotas" class="woocommerce-select">
                        <option value="1">1 cuota</option>
                        <option value="3">3 cuotas</option>
                        <option value="6">6 cuotas</option>
                        <option value="12">12 cuotas</option>
                    </select>
                </p>
                <p class="form-row form-row-wide">
                    <label for="macroclick-medios-de-pago"><?php _e('Medio de Pago', 'woocommerce'); ?> <span class="required">*</span></label>
                    <select name="macroclick_medios_de_pago" id="macroclick-medios-de-pago" class="woocommerce-select">
                        <option value="8"><?php _e('Crédito', 'woocommerce'); ?></option>
                        <option value="9"><?php _e('Débito', 'woocommerce'); ?></option>
                        <option value="11"><?php _e('Crédito en cuotas', 'woocommerce'); ?></option>
                    </select>
                </p>
            </fieldset>
            <?php
        }


        private function complete_payment($token, $order_id) {
            $auth_token = $this->get_auth_token();
        
            if (!$auth_token) {
                return [
                    'status' => 'error',
                    'message' => 'No se pudo obtener el token de autenticación',
                ];
            }
        
            $payment_url = 'https://botonpp.macroclickpago.com.ar:8082/v1/payment';
            $cantidad_cuotas = isset($_POST['macroclick_cantidad_cuotas']) ? intval($_POST['macroclick_cantidad_cuotas']) : 1;

            $medio_pago_id = isset($_POST['macroclick_medios_de_pago']) ? intval($_POST['macroclick_medios_de_pago']) : 8;

            if ($medio_pago_id === 8 && $cantidad_cuotas > 1) {
                $medio_pago_id = 11; 
            } elseif ($medio_pago_id === 9 && $cantidad_cuotas > 1) {
                $medio_pago_id = 11; 
            }

            $data = [
                'DatosTarjeta' => [
                    'NumeroTarjeta' => isset($_POST['macroclick_card_number']) ? sanitize_text_field($_POST['macroclick_card_number']) : '',
                    'AñoVencimiento' => isset($_POST['macroclick_expiry_date']) ? substr(sanitize_text_field($_POST['macroclick_expiry_date']), -2) : '', 
                    'MesVencimiento' => isset($_POST['macroclick_expiry_date']) ? substr(sanitize_text_field($_POST['macroclick_expiry_date']), 0, 2) : '', 
                    'CodigoTarjeta' => isset($_POST['macroclick_cvc']) ? sanitize_text_field($_POST['macroclick_cvc']) : '',
                    'DocumentoTitular' => isset($_POST['macroclick_documento_titular']) ? sanitize_text_field($_POST['macroclick_documento_titular']) : '',
                    'Email' => isset($_POST['macroclick_email']) ? sanitize_email($_POST['macroclick_email']) : '',
                    'FechaNacimientoTitular' => isset($_POST['macroclick_fecha_nacimiento']) ? sanitize_text_field($_POST['macroclick_fecha_nacimiento']) : '',
                    'NumeroPuertaResumen' => '20', 
                    'TipoDocumento' => isset($_POST['macroclick_tipo_documento']) ? sanitize_text_field($_POST['macroclick_tipo_documento']) : '',
                    'TitularTarjeta' => isset($_POST['macroclick_titular_tarjeta']) ? sanitize_text_field($_POST['macroclick_titular_tarjeta']) : '',
                ],
                'AceptaHabeasData' => false,  
                'AceptTerminosyCondiciones' => true,
                'CantidadCuotas' => $cantidad_cuotas,
                'IPCliente' => $this->get_client_ip(),
                'MedioPagoId' => $medio_pago_id
            ];

            $response = wp_remote_post($payment_url, [
                'method'    => 'POST',
                'body'      => json_encode($data),
                'headers'   => [
                    'Content-Type'  => 'application/json',
                    'X-Token'       => $auth_token,
                ],
                'sslverify' => true,
            ]);
        
            if (is_wp_error($response)) {
                return [
                    'status' => 'error',
                    'message' => $response->get_error_message(),
                ];
            }
        
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);
        
            if (isset($result['status']) && $result['status'] === 'success') {
                return [
                    'status' => 'success',
                    'message' => 'Pago completado exitosamente',
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => isset($result['message']) ? $result['message'] : 'Error en el pago',
                ];
            }
        }



        private function get_payment_token($data) {
            $auth_token = $this->get_auth_token();
        
            if (!$auth_token) {
                return [
                    'status' => 'error',
                    'message' => 'No se pudo obtener el token de autenticación',
                ];
            }
        
            $token_url = 'https://botonpp.macroclickpago.com.ar:8082/v1/tokens';
            $response = wp_remote_post($token_url, [
                'method'    => 'POST',
                'body'      => json_encode($data),
                'headers'   => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $auth_token,
                ],
                'sslverify' => true,
            ]);
        
            if (is_wp_error($response)) {
                return [
                    'status' => 'error',
                    'message' => $response->get_error_message(),
                ];
            }
        
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);
        
            if (isset($result['token'])) {
                return [
                    'status' => 'success',
                    'token' => $result['token'],
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => isset($result['message']) ? $result['message'] : 'Error al obtener el token de pago',
                ];
            }
        }


        private function get_auth_token() {
            $guid = $this->guid;
            $secret_key = $this->secret_key;
            $frase = $this->frase;
        
            $auth_url = 'https://botonpp.macroclickpago.com.ar:8082/v1/sesion';
            $response = wp_remote_post($auth_url, [
                'method'    => 'POST',
                'body'      => json_encode([
                    'guid'       => $guid,
                    'secret_key' => $secret_key,
                    'frase'      => $frase,
                ]),
                'headers'   => [
                    'Content-Type' => 'application/json',
                ],
                'sslverify' => true,
            ]);
     
            if (is_wp_error($response)) {
                return [
                    'code' => 500,
                    'status' => false,
                    'message' => 'Error en la solicitud.',
                    'data' => null,
                ];
            }

            $status_code = wp_remote_retrieve_response_code($response);

            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);

            if ($status_code == 200 && isset($result['token'])) {
                return [
                    'code' => $status_code,
                    'status' => true,
                    'message' => 'Identificación del comercio correcta.',
                    'data' => $result['token'],
                ];
            } else {
                return [
                    'code' => $status_code,
                    'status' => false,
                    'message' => isset($result['message']) ? $result['message'] : 'No se pudo identificar al comercio.',
                    'data' => null,
                ];
            }
        }


        private function get_client_ip() {
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                return $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                return $_SERVER['REMOTE_ADDR'];
            }
        }
    }

    function add_macroclick_gateway($methods) {
        $methods[] = 'WC_MacroClick_Gateway';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'add_macroclick_gateway');
}
?>