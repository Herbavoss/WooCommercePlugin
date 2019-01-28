<?php

/*
Plugin Name: Accept CryptoCurrency Payments on WooCommerce - GRAFT
Plugin URI:
Description: Accept CryptoCurrency payments on your WooCommerce store with GRAFT payments plugin.
Version: 1.0.0
Author: GRAFT Network
Author URI: https://graft.network
License: MIT License
License URI: https: http://opensource.org/licenses/MIT
Github Plugin URI: 
*/

if (!defined('ABSPATH')) {
    die('Access denied.');
}

add_action('plugins_loaded', 'graft_init');

define('GRAFT_VERSION', '1.0.0');

function graft_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    };

    require_once(__DIR__ . '/includes/client/init.php');

    class WC_Gateway_Graft extends WC_Payment_Gateway
    {

        private $graftClient;

        public function __construct()
        {
            $this->id = 'graft';
            $this->has_fields = false;
            $this->order_button_text = __('Pay with GRAFT', 'woocommerce');
            $this->method_title = __('GRAFT', 'woocommerce');

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->api_key = $this->get_option('api_key');
            $this->api_secret = $this->get_option('api_secret');
            $this->test_mode = ('yes' === $this->get_option('api_test_mode', 'no'));
            //$this->order_statuses = $this->get_option('order_statuses');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_statuses'));
            add_action('woocommerce_api_wc_gateway_graft', array($this, 'callback'));
        }

        public function admin_options()
        {
            $graft_api_status = get_option("graft_api_status");
            $graft_api_show = get_option("graft_api_status_show");

            echo '<h3>' . __('GRAFT', 'woothemes') . '</h3>';
            echo '<p>' . __('GRAFT helps you accept CryptoCurrency payments on your WooCommerce online store. For any inquiries or questions regarding GRAFT plugin please email us at <a href="mailto:support@graft.io">support@graft.io</a>', 'graft') . '</p>';
            if ($graft_api_status && $graft_api_show) {
                echo '<p><b>' . __('Successfully connected GRAFT API.', 'graft') . '</b></p>';
            } elseif ($graft_api_show) {
                echo '<p><b>' . __('Could not connect GRAFT API.', 'graft') . '</b></p>';
            }
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }

        public function process_admin_options()
        {
            if (!parent::process_admin_options())
                return false;

            $api_key = $this->get_option('api_key');
            $api_secret = $this->get_option('api_secret');
            $env = ('yes' === $this->get_option('api_test_mode', 'no'));

            if ($api_key && $api_secret) {
                $graftClient = new \GraftClient\GraftClient(
                    array(
                        'api_key' => $api_key,
                        'api_secret' => $api_secret,
                        'env' => ($env ? 'test' : 'live'),
                        'user_agent' => 'GRAFT/Payment Plugin: ' . GRAFT_VERSION . '/' . 'WooCommerce: ' . WOOCOMMERCE_VERSION
                    )
                );

                try {
                    $test_api = $graftClient->testApi();
                    if (isset($test_api['test']) && $test_api['test']['status']) {
                        update_option("graft_api_status", true);
                        update_option("graft_api_status_show", true);
                    } else {
                        update_option("graft_api_status", false);
                        update_option("graft_api_status_show", true);
                    }
                } catch (\Exception $e) {
                    update_option("graft_api_status", false);
                    update_option("graft_api_status_show", true);
                    return false;
                }
            } else {
                update_option("graft_api_status", false);
            }
        }

        public function get_icon()
        {
            $payment_icon = plugins_url('assets/images/graft-logo.png', __FILE__);
            $payment_icon_html = '<img src="' . esc_attr($payment_icon) . '" alt="' . esc_attr__('GRAFT', 'woocommerce') . '" />';
            return apply_filters('woocommerce_gateway_icon', $payment_icon_html, $this->id);
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable GRAFT', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                    'default' => __('Crypto Currency', 'woocommerce'),
                    'desc_tip' => true
                ),
                'description' => array(
                    'title' => __('Description', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                    'default' => __('Pay with Crypto Currency via GRAFT', 'woocommerce'),
                    'desc_tip' => true
                ),
                'api_details' => array(
                    'title' => __('API credentials', 'woocommerce'),
                    'type' => 'title',
                    'description' => sprintf(__('Enter your GRAFT Payment Gateway API credentials.', 'graft')),
                ),
                'api_key' => array(
                    'title' => __('API Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter your GRAFT Payment Gateway API Key.', 'graft'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true
                ),
                'api_secret' => array(
                    'title' => __('API Secret', 'woocommerce'),
                    'type' => 'text',
                    'default' => '',
                    'description' => __('Please enter your GRAFT Payment Gateway API Secret.', 'graft'),
                    'desc_tip' => true
                ),
                // 'api_test_mode' => array(
                //     'title' => __('Test Mode', 'woocommerce'),
                //     'type' => 'checkbox',
                //     'label' => __('Enable Test Mode', 'woocommerce'),
                //     'default' => 'no',
                //     'description' => __('You can use GRAFT Payment Gateway test API. Find out more about the <a href="https://app.graft.io/docs/api/api-testing" target="_blank">GRAFT test API</a>.', 'graft'),
                // ),
                // 'order_statuses' => array(
                //     'type' => 'order_statuses'
                // ),
            );
        }

        public function process_payment($order_id)
        {
            global $woocommerce;

            $order = new WC_Order($order_id);
            $address = $order->billing_email;
            $total = $order->get_total();
            $currency = $order->get_order_currency();

            $graftClient = new \GraftClient\GraftClient(
                array(
                    'api_key' => $this->api_key,
                    'api_secret' => $this->api_secret,
                    'env' => ($this->test_mode ? 'test' : 'live'),
                    'user_agent' => 'GRAFT/Payment Plugin: ' . GRAFT_VERSION . '/' . 'WooCommerce: ' . WOOCOMMERCE_VERSION
                )
            );

            $payment = $graftClient->addPayment(
                array(
                    'ExternalOrderId' => $order->get_id(),
                    'PayCurrency' => 'BTC',
                    'SaleCurrency' => $currency,
                    'SaleAmount' => $this->formatCurrency($total),
                    'CallbackUrl' => trailingslashit(get_bloginfo('wpurl')) . '?wc-api=wc_gateway_graft',
                    'CompleteUrl' => esc_url_raw($this->get_return_url($order)),
                    'CancelUrl' => esc_url_raw($order->get_cancel_order_url_raw())
                )
            );
            if ($payment && isset($payment['paymentUrl']) && !empty($payment['paymentUrl']) ) {
                return array(
                    'result' => 'success',
                    'redirect' => $payment['paymentUrl'],
                );
            } else {
                return array(
                    'result' => 'fail',
                );
            }
        }

        public function callback()
        {
            if ($this->enabled != 'yes') {
                return;
            }

            $request = $_REQUEST;
            if (true === empty($request)) {
                error_log('[Error] Invalid Request Data' . $request);
                wp_die('Invalid Request data');
            }

            // if (false === isset($request['key'])) {
            //     error_log('[***********] No key');
            //     wp_die('No Payment ID');
            // }
            // else {
            //     error_log('[**********] Key: ' .$request['key']);
            // }


            if (false === isset($request['id'])) {
                error_log('[Error] No Payment ID');
                wp_die('No Payment ID');
            }

            if (false === $request['order_id']) {
                error_log('[Error] No Order ID');
                wp_die('No Order ID');
            }

            $order_id = $request['order_id'];
            $order_id = apply_filters('woocommerce_order_id_from_number', $order_id);
            $order = wc_get_order($order_id);

            if (false === $order || 'WC_Order' !== get_class($order)) {
                error_log('[Error] Could not retrieve the order details for order_id ' . $order_id);
                throw new \Exception('Could not retrieve the order details for order_id ' . $order_id);
            }

            // $key = get_post_meta($order_id, 'graft_order_key', true);

            // if (false === isset($key) || empty($key) || $_GET['key'] != $key) {
            //     error_log('[Error] Order Key Not Found' . $key);
            //     throw new Exception('Order Key Not Found');
            // }

            $graftClient = new \GraftClient\GraftClient(
                array(
                    'api_key' => $this->api_key,
                    'api_secret' => $this->api_secret,
                    'user_agent' => 'GRAFT/Payment Plugin: ' . GRAFT_VERSION . '/' . 'WooCommerce: ' . WOOCOMMERCE_VERSION
                )
            );

            try {
                $payment = $graftClient->getPayment($request['id']);
                if (false === isset($payment) || !$payment || empty($payment)) {
                    error_log('[Error] API - Payment Not Found, Paymend ID:' . $request['id']);
                    wp_die('Payment Not Found');
                }
            } catch (\Exception $e) {
                wp_die($e->getMessage());
            }

            $payment_status = $payment['status'];
            if (false === isset($payment_status) && true === empty($payment_status)) {
                error_log('[Error] Could not obtain the current status from the payment #' . $payment['paymentId']);
                throw new \Exception('Could not obtain the current status from the payment.');
            } else {
                error_log('[Info] The current payment status for this payment #' . $payment['paymentId'] . ' is ' . $payment_status);
            }

            // $order_statuses = $this->get_option('order_statuses');
            // $woocomerce_order_status = $order_statuses[$payment['data']['payment_status']];
            // $completed_status = $order_statuses['completed'];
            // $confirmed_status = $order_statuses['confirmed'];
            // error_log('[************] 444');

            $order_status = $order->get_status();
            if (false === isset($order_status) && true === empty($order_status)) {
                error_log('[Error] Could not obtain the current status from the order #' . $order_id);
                throw new \Exception('Could not obtain the current status from the order #' . $order_id);
            } else {
                error_log('[Info] The current order status for this order #' . $order_id . ' is ' . $order_status);
            }

            if ($payment_status >= 3){
                if ($order_status !== 'completed'){
                    $order->payment_complete();
                    $order->add_order_note(__('GRAFT: Payment completed.', 'graft'));
                    $order->update_status('completed');
                }
            } else if ($payment_status < 0) {
                if ($order_status !== 'failed'){
                    $order->add_order_note(__('GRAFT: Payment is invalid for this order.', 'graft'));
                    $order->update_status('failed');
                }
            }

            // switch ($payment['data']['payment_status']) {
            //     case 'completed':
            //         if ($order_status == $completed_status || 'wc_' . $order_status == $completed_status) {
            //             error_log('[Warning] Order # ' . $order_id . ' has status: ' . $order_status);
            //         } else {
            //             $order->update_status($woocomerce_order_status);
            //             $order->add_order_note(__('GRAFT: Payment completed.', 'graft'));
            //         }
            //         break;
            //     case 'confirmed':
            //         if ($order_status == $confirmed_status || 'wc_' . $order_status == $confirmed_status) {
            //             error_log('[Warning] Order # ' . $order_id . ' has status: ' . $order_status);
            //         } else {
            //             $order->payment_complete();
            //             $order->add_order_note(__('GRAFT: Payment confirmed. Awaiting network confirmation and payment completed status.', 'graft'));
            //             $order->update_status($woocomerce_order_status);
            //         }
            //         break;
            //     case 'underpayment':
            //     error_log('[Info] Order #' . $order_id . 'Update Status - Underpayment');
            //         $order->add_order_note(__('GRAFT: Payment has been underpaid.', 'graft'));
            //         break;
            //     case 'refunded':
            //     error_log('[Info] Order #' . $order_id . 'Update Status - Refunded');
            //         $order->update_status($woocomerce_order_status);
            //         $order->add_order_note(__('GRAFT: Payment was refunded.', 'graft'));
            //         break;
            //     case 'invalid':
            //     error_log('[Info] Order #' . $order_id . 'Update Status - Invalid');
            //         $order->update_status($woocomerce_order_status);
            //         $order->add_order_note(__('GRAFT: Payment is invalid for this order.', 'graft'));
            //         break;
            //     case 'expired':
            //     error_log('[Info] Order #' . $order_id . 'Update Status - Expired');
            //         $order->update_status($woocomerce_order_status);
            //         $order->add_order_note(__('GRAFT: Payment has expired.', 'graft'));
            //         break;
            //     case 'canceled':
            //     error_log('[Info] Order #' . $order_id . 'Update Status - Canceled');
            //         $order->update_status($woocomerce_order_status);
            //         $order->add_order_note(__('GRAFT: Payment was canceled.', 'graft'));
            //         break;
            // }
        }

        // public function validate_order_statuses_field()
        // {
        //     $order_statuses = $this->get_option('order_statuses');
        //     if (isset($_POST[$this->plugin_id . $this->id . '_order_statuses'])) {
        //         $order_statuses = $_POST[$this->plugin_id . $this->id . '_order_statuses'];
        //     }
        //     return $order_statuses;
        // }

        public function save_statuses()
        {
            // $graft_statuses = $this->graft_statuses();
            // $woocommerce_statuses = wc_get_order_statuses();
            // if (isset($_POST['woocommerce_graft_order_statuses']) === true) {
            //     $settings = get_option('woocommerce_graft_settings');
            //     $order_statuses = $settings['order_statuses'];
            //     foreach ($graft_statuses as $graft_status_name => $graft_status_title) {
            //         if (isset($_POST['woocommerce_graft_order_statuses'][$graft_status_name]) === false) {
            //             continue;
            //         }
            //         $woocommerce_status_name = $_POST['woocommerce_graft_order_statuses'][$graft_status_name];
            //         if (array_key_exists($woocommerce_status_name, $woocommerce_statuses) === true) {
            //             $order_statuses[$graft_status_name] = $woocommerce_status_name;
            //         }
            //     }
            //     $settings['order_statuses'] = $order_statuses;
            //     update_option('woocommerce_graft_settings', $settings);
            // }
        }

        // private function graft_statuses()
        // {
        //     return array(
        //         'completed' => 'Completed',
        //         'confirmed' => 'Confirmed',
        //         'underpayment' => 'Underpayment',
        //         'refunded' => 'Refunded',
        //         'expired' => 'Expired',
        //         'canceled' => 'Canceled',
        //         'invalid' => 'Invalid'
        //     );
        // }

        private function order_items($order)
        {
            $items = array();
            $calculated_total = 0;

            foreach ($order->get_items(array('line_item', 'fee')) as $item) {
                if ('fee' === $item['type']) {
                    $item_line_total = $this->formatCurrency($item['line_total']);
                    $line_item = $this->add_order_item($item->get_name(), 1, $item_line_total);
                    $calculated_total += $item_line_total;
                } else {
                    $product = $item->get_product();
                    $sku = $product ? $product->get_sku() : '';
                    $item_line_total = $this->formatCurrency($order->get_item_subtotal($item, false));
                    $line_item = $this->add_order_item($this->get_item_name($order, $item), $item->get_quantity(), $item_line_total, $sku);
                    $calculated_total += $item_line_total * $item->get_quantity();
                }
                if ($line_item) {
                    $items[] = $line_item;
                }
            }

            if ($calculated_total + $order->get_total_tax() + $order->get_shipping_total() - $order->get_total_discount() != $order->get_total()) {
                return false;
            } else {
                return $items;
            }
        }

        protected function add_order_item($item_name, $quantity = 1, $amount = 0.0, $item_number = '')
        {

            $item = array(
                'name' => $this->limit_length(html_entity_decode(wc_trim_string($item_name ? $item_name : __('Item', 'woocommerce'), 127), ENT_NOQUOTES, 'UTF-8'), 127),
                'qty' => (int)$quantity,
                'amount' => wc_float_to_string((float)$amount),
                'item_number' => $item_number,
            );
            return $item;
        }

        protected function get_item_name($order, $item)
        {
            $item_name = $item->get_name();
            $item_meta = strip_tags(wc_display_item_meta($item, array(
                'before' => "",
                'separator' => ", ",
                'after' => "",
                'echo' => false,
                'autop' => false,
            )));

            if ($item_meta) {
                $item_name .= ' (' . $item_meta . ')';
            }
            return $item_name;
        }

        protected function limit_length($string, $limit = 127)
        {
            if (strlen($string) > $limit) {
                $string = substr($string, 0, $limit - 3) . '...';
            }
            return $string;
        }

        private function log($message)
        {
            if (false === isset($this->logger) || true === empty($this->logger)) {
                $this->logger = new WC_Logger();
            }
            $this->logger->add('graft', $message);
        }

        private function formatCurrency($amount)
        {
            return number_format($amount, 6, '.', '');
        }
    }

    function graft_gateway_class($methods)
    {
        $methods[] = 'WC_Gateway_Graft';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'graft_gateway_class');
}

