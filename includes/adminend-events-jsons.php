<?php
/**
 * Order Delivery Date Pro for WooCommerce
 *
 * Handles the JSON output for Delivery events to be displayed in Delivery Calendar for admin.
 *
 * @author   Tyche Softwares
 * @package  Order-Delivery-Date-Pro-for-WooCommerce/Delivery-Calendar
 * @since    2.8.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class for Delivery Calendar.
 *
 * @since 1.0
 */

class orddd_adminevent_jsons {

    /**
     * Default Constructor
     *
     * @since 8.1
     */
    public function __construct() {
        //Delivery Calendar in Admin
        add_action( 'admin_init', array( &$this, 'orddd_adminevent_event_jsons' ) );  
    }

    /**
     * This function is used for handling deliveries in the Calendar View. 
     *
     * @since 1.0
     */
    public static function orddd_adminevent_event_jsons() {   
        if( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'orddd-adminend-events-jsons' ) {
            global $wpdb;
            if( isset( $_GET[ 'orderType' ] ) && ( $_GET[ 'orderType'] != '' ) ) {
                $order_status1 = $_GET[ 'orderType' ];
                $order_status = explode( ',', $order_status1 );
            } else {
                $all_order_status = wc_get_order_statuses();
                $order_status = array();
                foreach ( $all_order_status as $order_status_key => $order_status_name ) {
                    if ( $order_status_key == 'wc-pending' || $order_status_key == 'wc-processing' || $order_status_key == 'wc-on-hold' || $order_status_key == 'wc-completed' ) {
                        $order_status[] = $order_status_key; 
                    } else if( $order_status_key != 'wc-cancelled' && $order_status_key != 'wc-refunded' && $order_status_key != 'wc-failed' ) {
                        $order_status[] = $order_status_key; 
                    }
                }
            }
            $event_start = $event_start_timestamp = $event_end = $event_end_timestamp = '';
            if( isset( $_GET[ 'start' ] ) ) {
                $event_start = $_GET[ 'start' ];
                $event_start_timestamp = strtotime( $_GET[ 'start' ] );
            }

            if( isset( $_GET[ 'end' ] ) ) {
                $event_end = $_GET[ 'end' ];
                $event_end_timestamp = strtotime( $_GET[ 'end' ] );
            }

            $date_str = orddd_common::str_to_date_format();

            $orddd_query = "SELECT ID, post_status FROM `" . $wpdb->prefix . "posts` WHERE post_type = 'shop_order' AND post_status NOT IN ('wc-cancelled', 'wc-refunded', 'trash', 'wc-failed') AND ID IN ( SELECT post_id FROM `" . $wpdb->prefix . "postmeta` WHERE ( meta_key = '_orddd_timestamp' AND meta_value >= '" . $event_start_timestamp . "' AND meta_value <= '" . $event_end_timestamp . "' ) OR ( meta_key = '" . get_option( 'orddd_delivery_date_field_label' ) . "' AND STR_TO_DATE( meta_value, '" . $date_str . "' ) >= '" . $event_start . "' AND STR_TO_DATE( meta_value, '" . $date_str . "' ) <= '" . $event_end . "' ) OR ( meta_key = '" . ORDDD_DELIVERY_DATE_FIELD_LABEL . "' AND STR_TO_DATE( meta_value, '" . $date_str . "' ) >= '" . $event_start . "' AND STR_TO_DATE( meta_value, '" . $date_str . "' ) <= '" . $event_end . "' ) )";
            $results = $wpdb->get_results( $orddd_query );
            $data = array();

            foreach ( $results as $key => $value ) {
                $order = new WC_Order( $value->ID );
                $order_items = $order->get_items();
                $order_id = $order->get_id();
                $delivery_date_timestamp = '';
                $delivery_date_formatted = orddd_common::orddd_get_order_delivery_date( $value->ID );
                $orddd_timestamp = get_post_meta( $value->ID, '_orddd_timestamp', true );
                $delivery_date_for_label = get_post_meta( $value->ID, get_option( 'orddd_delivery_date_field_label' ), true );
                $time_slot_field_label = get_option( 'orddd_delivery_timeslot_field_label' );
                if( $time_slot_field_label == '' ) {
                	$time_slot_field_label = 'Time slot';
                }

                $time_slot = get_post_meta( $value->ID, $time_slot_field_label , true );
                if ( $orddd_timestamp != '' && $orddd_timestamp != 'null' && $orddd_timestamp != '{}' && $orddd_timestamp != '[]' ) {
                    $delivery_date_timestamp = $orddd_timestamp;
                } else {
                    if ( $delivery_date_for_label != '' && $delivery_date_for_label != 'null' && $delivery_date_for_label != '{}' && $delivery_date_for_label != '[]' ) {
                        $delivery_date_timestamp = strtotime( str_replace( ",", " ", $delivery_date_for_label ) );
                    } else {
                        $delivery_date_for_default_label = get_post_meta( $value->ID, ORDDD_DELIVERY_DATE_FIELD_LABEL, true );
                        if ( $delivery_date_for_default_label != '' && $delivery_date_for_default_label != 'null' && $delivery_date_for_default_label != '{}' && $delivery_date_for_default_label != '[]' ) {
                            $delivery_date_timestamp = strtotime( str_replace( ",", " ", $delivery_date_for_default_label ) );
                        }
                    }
                }
                $time_settings = '';
                if( isset( $delivery_date_timestamp ) && $delivery_date_timestamp != '' ) {
                    $time_settings = date( "H:i", $delivery_date_timestamp );
                }

                $end_date = '';
                if ( has_filter( 'orddd_to_add_end_date' ) ) {
                    $end_date = apply_filters( 'orddd_to_add_end_date', $order_id );
                }
                
                $post_status = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">="  ) ) ? get_post_status( $order_id ) : $order->post_status;
                if( in_array( $post_status, $order_status ) ) {
                    if ( ( isset( $_GET[ 'eventType' ] )  && ( $_GET[ 'eventType' ] == '' || $_GET[ 'eventType' ] == 'product' ) ) || !isset( $_GET[ 'eventType' ] ) ) {
                        foreach ( $order_items as $item ) {
                            $product_name = html_entity_decode( $item[ 'name' ], ENT_COMPAT, 'UTF-8' );
                            if( $delivery_date_timestamp != '' && $delivery_date_formatted != '' && $delivery_date_timestamp >= $event_start_timestamp && $delivery_date_timestamp <= $event_end_timestamp ) {
                                if( isset( $time_slot ) && ( $time_slot != '' && $time_slot != 'null' && false == strpos( $time_slot, "Possible" ) ) ) {
                                    $time_arr = explode( '-', $time_slot );
                                    $from_time = $time_arr[ 0 ];
                                    $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                    $delivery_date .= " ".$from_time;
                                    $post_from_timestamp = strtotime( $delivery_date );
                                    $from_date = date ( 'Y-m-d H:i:s', $post_from_timestamp );    
                                    
                                    if( isset( $from_time ) && $from_time != '' ) {
                                        if ( isset( $time_arr[ 1 ] ) && $time_arr[ 1 ] != '' ) {
                                            $to_time = $time_arr[ 1 ];
                                            $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                            $delivery_date .= " " . $to_time;
                                            $post_to_timestamp = strtotime( $delivery_date );
                                            if ( '' != $end_date ) {
                                                $to_date = $end_date;
                                            } else {
                                                $to_date = date ( 'Y-m-d H:i:s', $post_to_timestamp );
                                            }
                                            array_push( $data, array(
                                                'id'       => $value->ID,
                                                'title'    => $product_name . " x" . $item[ 'quantity' ],
                                                'start'    => $from_date,
                                                'end'      => $to_date,
                                                'timeslot' => $time_slot,
                                                'eventtype'=> 'product',
                                                'value'    => $value,
                                                'delivery_date' => "",
                                                'event_product_id' => $item[ 'product_id' ],
                                                'event_product_qty' => $item[ 'quantity' ],
                                            ) );
                                        } else {
                                            $to_time = date( "H:i", strtotime( '+30 minutes', $post_from_timestamp ) );
                                            $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                            $delivery_date .= " " . $to_time;
                                            $post_to_timestamp = strtotime( $delivery_date );
                                            if ( '' != $end_date ) {
                                                $to_date = $end_date;
                                            } else {
                                                $to_date = date ( 'Y-m-d H:i:s', $post_to_timestamp );
                                            }
                                            array_push( $data, array(
                                                'id'       => $value->ID,
                                                'title'    => $product_name . " x" . $item[ 'quantity' ],
                                                'start'    => $from_date,
                                                'end'      => $to_date,
                                                'timeslot' => $time_slot,
                                                'eventtype'=> 'product',
                                                'value'    => $value,
                                                'delivery_date' => "",
                                                'event_product_id' => $item[ 'product_id' ],
                                                'event_product_qty' => $item[ 'quantity' ],
                                            ) );
                                        }
                                    }
                                } else if ( $time_settings != '00:01' && $time_settings != '' && $time_settings != '00:00' ) {
                                    $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                    $from_date = date( 'Y-m-d H:i:s', $delivery_date_timestamp );
                                    if ( '' != $end_date ) {
                                        $to_date = $end_date;
                                    } else {
                                        $to_date = date( "Y-m-d H:i:s", strtotime( '+30 minutes', $delivery_date_timestamp ) );
                                    }
                                    array_push( $data, array(
                                        'id'    => $value->ID,
                                        'title' => $product_name . " x" . $item[ 'quantity' ],
                                        'start' => $from_date,
                                        'end'   => $to_date,
                                        'eventtype'=> 'product',
                                        'value' => $value,
                                        'delivery_date' => "",
                                        'event_product_id' => $item[ 'product_id' ],
                                        'event_product_qty' => $item[ 'quantity' ],
                                    ) );
                                } else {
                                    if ( '' != $end_date ) {
                                        $delivery_date_formatted = $end_date;
                                    } else {
                                        $delivery_date_formatted = date( 'Y-m-d', $delivery_date_timestamp );
                                    }
                                    array_push( $data, array(
                                        'id'    => $value->ID,
                                        'title' => $product_name . " x" . $item[ 'quantity' ],
                                        'start' => $delivery_date_formatted,
                                        'end'   => $delivery_date_formatted,
                                        'eventtype'=> 'product',
                                        'value' => $value,
                                        'delivery_date' => "",
                                        'event_product_id' => $item[ 'product_id' ],
                                        'event_product_qty' => $item[ 'quantity' ],
                                    ) );
                                }
                            }
                        }
                        
                    } else if ( isset( $_GET[ 'eventType' ] ) && $_GET[ 'eventType' ] == 'order' ) {
                        $billing_first_name = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">="  ) ) ? $order->get_billing_first_name() : $order->billing_first_name;
                        $billing_last_name = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">="  ) ) ? $order->get_billing_last_name() : $order->billing_last_name;

                        $customer_name = $billing_first_name . " " . $billing_last_name;
                        if( $delivery_date_timestamp != '' && $delivery_date_formatted != '' && $delivery_date_timestamp >= $event_start_timestamp && $delivery_date_timestamp <= $event_end_timestamp ) {
                            if( isset( $time_slot ) && ( $time_slot != '' && $time_slot != 'null' && false == strpos( $time_slot, "Possible" ) ) ) {
                                $time_arr = explode( '-', $time_slot );
                                $from_time = $time_arr[ 0 ];
                                $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                $delivery_date .= " ".$from_time;
                                $post_from_timestamp = strtotime( $delivery_date );
                                $from_date = date ( 'Y-m-d H:i:s', $post_from_timestamp );
                                if( isset( $from_time ) && $from_time != '' ) {
                                    if ( isset( $time_arr[ 1 ] ) && $time_arr[ 1 ] != '' ) {
                                        $to_time = $time_arr[ 1 ];
                                        $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                        $delivery_date .= " " . $to_time;
                                        $post_to_timestamp = strtotime( $delivery_date );
                                        if ( '' != $end_date ) {
                                            $to_date = $end_date;
                                        } else {
                                            $to_date = date ( 'Y-m-d H:i:s', $post_to_timestamp );
                                        }
                                        array_push( $data, array(
                                            'id'       => $value->ID,
                                            'title'    => 'Order Number: ' . $order->get_order_number(),
                                            'start'    => $from_date,
                                            'end'      => $to_date,
                                            'timeslot' => $time_slot,
                                            'eventtype'=> 'order',
                                            'value'    => $value,
                                            'delivery_date' => "",
                                            'event_product_id' => "",
                                            'event_product_qty' => ''
                                        ) );
                                    } else {
                                        $to_time = date( "H:i", strtotime( '+30 minutes', $post_from_timestamp ) );
                                        $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                        $delivery_date .= " " . $to_time;
                                        $post_to_timestamp = strtotime( $delivery_date );
                                        if ( '' != $end_date ) {
                                            $to_date = $end_date;
                                        } else {
                                            $to_date = date ( 'Y-m-d H:i:s', $post_to_timestamp );
                                        }
                                        
                                        array_push( $data, array(
                                            'id'       => $value->ID,
                                            'title'    => 'Order Number: ' . $order->get_order_number(),
                                            'start'    => $from_date,
                                            'end'      => $to_date,
                                            'timeslot' => $time_slot,
                                            'eventtype'=> 'order',
                                            'value'    => $value,
                                            'delivery_date' => "",
                                            'event_product_id' => "",
                                            'event_product_qty' => ''
                                        ) );
                                    }
                                }
                            } else if ( $time_settings != '00:01' && $time_settings != '' && $time_settings != '00:00' ) {
                                $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                $from_date = date( 'Y-m-d H:i:s', $delivery_date_timestamp );
                                if ( '' != $end_date ) {
                                    $to_date = $end_date;
                                } else {
                                    $to_date = date( "Y-m-d H:i:s", strtotime( '+30 minutes', $delivery_date_timestamp ) );
                                }
                                array_push( $data, array(
                                    'id'    => $value->ID,
                                    'title' => 'Order Number: ' . $order->get_order_number(),
                                    'start' => $from_date,
                                    'end'   => $to_date,
                                    'eventtype'=> 'order',
                                    'value' => $value,
                                    'delivery_date' => "",
                                    'event_product_id' => "",
                                    'event_product_qty' => ''
                                ) );
                
                            } else {
                                if ( '' != $end_date ) {
                                    $delivery_date_formatted = $end_date;
                                } else {
                                    $delivery_date_formatted = date( 'Y-m-d', $delivery_date_timestamp );
                                }
                                array_push( $data, array(
                                    'id'    => $value->ID,
                                    'title' => 'Order Number: ' . $order->get_order_number(),
                                    'start' => $delivery_date_formatted,
                                    'end'   => $delivery_date_formatted,
                                    'eventtype'=> 'order',
                                    'value' => $value,
                                    'delivery_date' => "",
                                    'event_product_id' => "",
                                    'event_product_qty' => ''
                                ) );
                            }
                        }
                    }
                }
            }

            $orddd_query = "SELECT ID FROM `" . $wpdb->prefix . "posts` WHERE post_type = 'shop_order' AND post_status NOT IN ('wc-cancelled', 'wc-refunded', 'trash', 'wc-failed') AND ID IN ( SELECT post_id FROM `" . $wpdb->prefix . "postmeta` WHERE meta_key LIKE '%_orddd_shipping_multiple_addresss_timestamp_%' AND meta_value >= '" . $event_start_timestamp . "' AND meta_value <= '" . $event_end_timestamp . "' ) ";
            $results = $wpdb->get_results( $orddd_query );
            if( is_array( $results ) && count( $results ) > 0 ) {
                foreach ( $results as $key => $value ) {
                    $order = new WC_Order( $value->ID );
                    $order_items = $order->get_items();
                    $post_status = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">="  ) ) ? get_post_status( $value->ID ) : $order->post_status;
                    if( in_array( $post_status, $order_status ) ) {
                        $shipping_packages = get_post_meta( $value->ID, '_shipping_packages', true );
                        $query = "SELECT meta_key, meta_value FROM `" . $wpdb->prefix . "postmeta` WHERE post_id='" . $value->ID . "' AND meta_key LIKE '%_orddd_shipping_multiple_addresss_%'";
                        $results_array = $wpdb->get_results( $query );
                        $delivery_dates = array();
                        foreach( $results_array as $r_key => $r_value ) {
                            $delivery_dates[ $r_value->meta_key ] = $r_value->meta_value;
                        }
                        foreach( $delivery_dates as $d_key => $d_value ) {
                            if( preg_match( '/_orddd_shipping_multiple_addresss_e_deliverydate/', $d_key ) ) {
                                $date_to_display = $d_value;
                                $key_explode = explode( "_", $d_key );
                                $timestamp_key = '_orddd_shipping_multiple_addresss_timestamp_' . $key_explode[ 7 ] . "_" . $key_explode[ 8 ] . "_" . $key_explode[ 9 ];
                                $time_slot_key = '_orddd_shipping_multiple_addresss_time_slot_' . $key_explode[ 7 ] . "_" . $key_explode[ 8 ] . "_" . $key_explode[ 9 ];
                                if( isset( $delivery_dates[ $timestamp_key ] ) ) {
                                    $delivery_date_timestamp = $delivery_dates[ $timestamp_key ];
                                } else {
                                    $delivery_date_timestamp = "";
                                }
                            
                                if( isset( $delivery_dates[ $time_slot_key ] ) ) {
                                    $time_slot = $delivery_dates[ $time_slot_key ];
                                } else {
                                    $time_slot = "";
                                }
                                if( isset( $delivery_date_timestamp ) && $delivery_date_timestamp != '' ) {
                                    $time_settings_arr = explode( " ", $d_value );
                                    array_pop( $time_settings_arr );
                                    $time_settings = date( "H:i", strtotime( end( $time_settings_arr ) ) );
                                } else {
                                    $time_settings = '';
                                }
                                
                                if ( ( isset( $_GET[ 'eventType' ] )  && ( $_GET[ 'eventType' ] == '' || $_GET[ 'eventType' ] == 'product' ) ) || !isset( $_GET[ 'eventType' ] ) ) {
                                    foreach ( $order_items as $item_key => $item ) {
                                        if( $item[ 'product_id' ] == $key_explode[ 8 ] ) {
                                            $product_name = html_entity_decode( $item[ 'name' ], ENT_COMPAT, 'UTF-8' );
                                            if( isset( $time_slot ) && $time_slot != '' && $delivery_date_timestamp != "" && false == strpos( $time_slot, "Possible" ) ) {
                                                $time_arr = explode( '-', $time_slot );
                                                $from_time = $time_arr[ 0 ];
                                                $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                                $delivery_date .= " ".$from_time;
                                                $post_from_timestamp = strtotime( $delivery_date );
                                                $from_date = date ( 'Y-m-d H:i:s', $post_from_timestamp );
                                                if( isset( $from_time ) && $from_time != '' ) {
                                                    if ( isset( $time_arr[ 1 ] ) && $time_arr[ 1 ] != '' ) {
                                                        $to_time = $time_arr[ 1 ];
                                                        $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                                        $delivery_date_to_pass = $delivery_date;
                                                        $delivery_date .= " " . $to_time;
                                                        $post_to_timestamp = strtotime( $delivery_date );
                                                        if ( '' != $end_date ) {
                                                            $to_date = $end_date;
                                                        } else {
                                                            $to_date = date ( 'Y-m-d H:i:s', $post_to_timestamp );
                                                        }
                                                        array_push( $data, array(
                                                            'id'       => $value->ID,
                                                            'title'    => $product_name . " x" . $item[ 'quantity' ],
                                                            'start'    => $from_date,
                                                            'end'      => $to_date,
                                                            'timeslot' => $time_slot,
                                                            'eventtype'=> 'product',
                                                            'value'    => $value,
                                                            'delivery_date' => $date_to_display,
                                                            'time_slot' => $time_slot,
                                                            'event_product_id' => $item[ 'product_id' ],
                                                            'event_product_qty' => $item[ 'quantity' ],

                                                        ) );
                                                    } else {
                                                        $to_time = date( "H:i", strtotime( '+30 minutes', $post_from_timestamp ) );
                                                        $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                                        $delivery_date_to_pass = $delivery_date;
                                                        $delivery_date .= " " . $to_time;
                                                        $post_to_timestamp = strtotime( $delivery_date );
                                                        if ( '' != $end_date ) {
                                                            $to_date = $end_date;
                                                        } else {
                                                            $to_date = date ( 'Y-m-d H:i:s', $post_to_timestamp );
                                                        }                                            
                                                        array_push( $data, array(
                                                            'id'       => $value->ID,
                                                            'title'    => $product_name . " x" . $item[ 'quantity' ],
                                                            'start'    => $from_date,
                                                            'end'      => $to_date,
                                                            'timeslot' => $time_slot,
                                                            'eventtype'=> 'product',
                                                            'value'    => $value,
                                                            'delivery_date' => $date_to_display,
                                                            'time_slot' => $time_slot,
                                                            'event_product_id' => $item[ 'product_id' ],
                                                            'event_product_qty' => $item[ 'quantity' ],
                                                        ) );
                                                    }
                                                } 
                                            } else if ( $time_settings != '00:01' && $time_settings != '' && $time_settings != '00:00' && $delivery_date_timestamp != "" ) {
                                                $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                                $from_date = date( 'Y-m-d H:i:s', $delivery_date_timestamp );
                                                if ( '' != $end_date ) {
                                                    $to_date = $end_date;
                                                } else {
                                                    $to_date  = date( "Y-m-d H:i:s", strtotime( '+30 minutes', $delivery_date_timestamp ) );
                                                }
                                                array_push( $data, array(
                                                    'id'    => $value->ID,
                                                    'title' => $product_name . " x" . $item[ 'quantity' ],
                                                    'start' => $from_date,
                                                    'end'   => $to_date,
                                                    'eventtype'=> 'product',
                                                    'value' => $value,
                                                    'delivery_date' => $date_to_display,
                                                    'time_slot' => $time_slot,
                                                    'event_product_id' => $item[ 'product_id' ],
                                                    'event_product_qty' => $item[ 'quantity' ],
                                                ) );
                                            } else if( $delivery_date_timestamp != "" ) {
                                                if ( '' != $end_date ) {
                                                    $delivery_date_formatted = $end_date;
                                                } else {
                                                    $delivery_date_formatted = date( 'Y-m-d', $delivery_date_timestamp );
                                                }
                                                array_push( $data, array(
                                                    'id'    => $value->ID,
                                                    'title' => $product_name . " x" . $item[ 'quantity' ],
                                                    'start' => $delivery_date_formatted,
                                                    'end'   => $delivery_date_formatted,
                                                    'eventtype'=> 'product',
                                                    'value' => $value,
                                                    'delivery_date' => $date_to_display,
                                                    'time_slot' => $time_slot,
                                                    'event_product_id' => $item[ 'product_id' ],
                                                    'event_product_qty' => $item[ 'quantity' ],
                                                ) );    
                                            }
                                        }
                                    }
                                } else if ( isset( $_GET[ 'eventType' ] ) && $_GET[ 'eventType' ] == 'order' ) {
                                    $billing_first_name = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">="  ) ) ? $order->get_billing_first_name() : $order->billing_first_name;
                                    $billing_last_name = ( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">="  ) ) ? $order->get_billing_last_name() : $order->billing_last_name;

                                    $customer_name = $billing_first_name . " " . $billing_last_name;
                                    if( $delivery_date_timestamp != '' && $delivery_date_formatted != '' && $delivery_date_timestamp >= $event_start_timestamp && $delivery_date_timestamp <= $event_end_timestamp ) {
                                        if( isset( $time_slot ) && ( $time_slot != '' && $time_slot != 'null' && false == strpos( $time_slot, "Possible" ) ) ) {
                                            $time_arr = explode( '-', $time_slot );
                                            $from_time = $time_arr[ 0 ];
                                            $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                            $delivery_date .= " ".$from_time;
                                            $post_from_timestamp = strtotime( $delivery_date );
                                            $from_date = date ( 'Y-m-d H:i:s', $post_from_timestamp );
                                            if( isset( $from_time ) && $from_time != '' ) {
                                                if ( isset( $time_arr[ 1 ] ) && $time_arr[ 1 ] != '' ) {
                                                    $to_time = $time_arr[ 1 ];
                                                    $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                                    $delivery_date .= " " . $to_time;
                                                    $post_to_timestamp = strtotime( $delivery_date );
                                                    $to_date = date ( 'Y-m-d H:i:s', $post_to_timestamp );
                                                    array_push( $data, array(
                                                        'id'       => $value->ID,
                                                        'title'    => 'Order Number: ' . $order->get_order_number(),
                                                        'start'    => $from_date,
                                                        'end'      => $to_date,
                                                        'timeslot' => $time_slot,
                                                        'eventtype'=> 'order',
                                                        'value'    => $value,
                                                        'delivery_date' => $date_to_display,
                                                        'time_slot' => $time_slot,
                                                        'event_product_id' => $key_explode[ 8 ],
                                                        'event_product_qty' => ''
                                                    ) );
                                                } else {
                                                    $to_time = date( "H:i", strtotime( '+30 minutes', $post_from_timestamp ) );
                                                    $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                                    $delivery_date .= " " . $to_time;
                                                    $post_to_timestamp = strtotime( $delivery_date );
                                                    $to_date = date ( 'Y-m-d H:i:s', $post_to_timestamp );
                                                    array_push( $data, array(
                                                        'id'       => $value->ID,
                                                        'title'    => 'Order Number: ' . $order->get_order_number(),
                                                        'start'    => $from_date,
                                                        'end'      => $to_date,
                                                        'timeslot' => $time_slot,
                                                        'eventtype'=> 'order',
                                                        'value'    => $value,
                                                        'delivery_date' => $date_to_display,
                                                        'time_slot' => $time_slot,
                                                        'event_product_id' => $key_explode[ 8 ],
                                                        'event_product_qty' => ''
                                                    ) );
                                                }
                                            }
                                        } else if ( $time_settings != '00:01' && $time_settings != '' && $time_settings != '00:00' ) {
                                            $delivery_date = date( "Y-m-d", $delivery_date_timestamp );
                                            $from_date = date( 'Y-m-d H:i:s', $delivery_date_timestamp );
                                            $to_date  = date( "Y-m-d H:i:s", strtotime( '+30 minutes', $delivery_date_timestamp ) );
                                            array_push( $data, array(
                                                'id'    => $value->ID,
                                                'title' => 'Order Number: ' . $order->get_order_number(),
                                                'start' => $from_date,
                                                'end'   => $to_date,
                                                'eventtype'=> 'order',
                                                'value' => $value,
                                                'delivery_date' => $date_to_display,
                                                'time_slot' => $time_slot,
                                                'event_product_id' => $key_explode[ 8 ],
                                                'event_product_qty' => ''
                                            ) );
                                        } else {
                                            $delivery_date_formatted = date( 'Y-m-d', $delivery_date_timestamp );
                                            array_push( $data, array(
                                                'id'    => $value->ID,
                                                'title' => 'Order Number: ' . $order->get_order_number(),
                                                'start' => $delivery_date_formatted,
                                                'end'   => $delivery_date_formatted,
                                                'eventtype'=> 'order',
                                                'value' => $value,
                                                'delivery_date' => $date_to_display,
                                                'time_slot' => $time_slot,
                                                'event_product_id' => $key_explode[ 8 ],
                                                'event_product_qty' => ''
                                            ) );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            echo json_encode( $data );
        }
    }
}
$orddd_adminevent_jsons = new orddd_adminevent_jsons();
