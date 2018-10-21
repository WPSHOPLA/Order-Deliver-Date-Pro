<?php
/**
 * Order Delivery Date Pro for WooCommerce
 *
 * Availability Widget added to show the available delivery dates in the calendar on the frontend. 
 *
 * @author      Tyche Softwares
 * @package     Order-Delivery-Date-Pro-for-WooCommerce/Frontend/Widgets
 * @since       8.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class for adding availability widget on the frontend
 *
 * @class orddd_widget
 */

class orddd_widget {
    
    /**
     * Default Constructor
     * 
     * @since 8.6
     */
    public function __construct() {
        // Register and load the widget
        add_action( 'widgets_init', array( &$this, 'orddd_load_widget' ) );
        add_action( 'wp_ajax_nopriv_orddd_show_availability_calendar', array( &$this, 'orddd_show_availability_calendar' ), 10, 1 );
        add_action( 'wp_ajax_orddd_show_availability_calendar', array( &$this, 'orddd_show_availability_calendar' ), 10, 1 );
    }
    
    /**
     * Registers the Availability Widget
     * 
     * @hook orddd_load_widget
     * @since 8.6
     */
    public function orddd_load_widget() {
        register_widget( 'orddd_availability_widget' );
    }

    /**
     * Updates the availability of the dates based on the postcode when the Show availability button is clicked.
     *
     * @hook wp_ajax_nopriv_orddd_show_availability_calendar
     * @hook wp_ajax_orddd_show_availability_calendar
     * @since 8.6 
     */
    public function orddd_show_availability_calendar() {
        $zone_details = explode( "-", orddd_common::orddd_get_zone_id( '', false ) );
        $shipping_method = $zone_details[ 1 ];
        $partially_booked_dates_str = self::get_partially_booked_dates( $shipping_method );
        echo $shipping_method . "&" . $partially_booked_dates_str;
        die();
    }
    
    /**
     * Returns the availability of the dates. 
     *
     * @since 8.6
     */
    public static function get_partially_booked_dates( $shipping_method, $shipping_settings = array() ) {
        global $wpdb;     
        
        $gmt = false;
        if( has_filter( 'orddd_gmt_calculations' ) ) {
            $gmt = apply_filters( 'orddd_gmt_calculations', '' );
        }
        $current_time = current_time( 'timestamp', $gmt );

        $time_format_to_show = orddd_common::orddd_get_time_format(); 
        $available_deliveries = '';   
		$partially_lockout_dates = '';
        $shipping_settings_to_check = array();
        $is_custom_enabled = 'no';
        if( get_option( 'orddd_enable_shipping_based_delivery' ) == 'on' ) {
            if( '' != $shipping_method ) {
                $shipping_based_settings_query = "SELECT option_value, option_name FROM `".$wpdb->prefix."options` WHERE option_name LIKE 'orddd_shipping_based_settings_%' AND option_name != 'orddd_shipping_based_settings_option_key' ORDER BY option_id DESC";
                $results = $wpdb->get_results( $shipping_based_settings_query );
                if( is_array( $results ) && count( $results ) > 0 && $shipping_method != '' ) {
                    foreach ( $results as $key => $value ) {
                        $shipping_methods = array();
                        $shipping_settings = get_option( $value->option_name );
                        if( isset( $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] ) &&
                            $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] == 'shipping_methods' ) {
                            if( in_array( $shipping_method, $shipping_settings[ 'shipping_methods' ] ) ) {
                                $shipping_settings_to_check = $shipping_settings;
                            }
                        }
                    }
                }
            } else if( is_array( $shipping_settings ) && count( $shipping_settings ) > 0 ) {
                $shipping_settings_to_check = $shipping_settings;
            }

            if( is_array( $shipping_settings_to_check ) && count( $shipping_settings_to_check ) > 0 ) {
                if( isset( $shipping_settings_to_check[ 'time_slots' ] ) && $shipping_settings_to_check[ 'time_slots' ] != '' ) {                                
                    $lockout_arr             = array();
                    $lockout_time_arr        = array();
                    $date                    = array();
                    $previous_orders         = 0;
                    $specific_dates          = array();
                    $delivery_days           = array();
    
                    $time_slots = explode( '},', $shipping_settings_to_check[ 'time_slots' ] );
                    // Sort the multidimensional array
                    usort( $time_slots, array( 'orddd_common', 'orddd_custom_sort' ) );
                    foreach( $time_slots as $tk => $tv ) {
                        if( $tv != '' ) {
                            $timeslot_values = orddd_common::get_timeslot_values( $tv );
                            if( is_array( $timeslot_values[ 'selected_days' ] ) ) {
                                $time_slot_arr = explode( ' - ',  $timeslot_values[ 'time_slot' ] );
                                $from_time = date( $time_format_to_show, strtotime( trim( $time_slot_arr[ 0 ] ) ) );
                                if( isset( $time_slot_arr[ 1 ] ) ) {
                                    $to_time = date( $time_format_to_show, strtotime( trim( $time_slot_arr[ 1 ] ) ) );
                                    $time_slot = $from_time . " - " . $to_time;    
                                } else {
                                    $time_slot = $from_time;
                                }
                                
                                if ( $timeslot_values[ 'delivery_days_selected' ] == 'weekdays' ) {
                                    foreach( $timeslot_values[ 'selected_days' ] as $dkey => $dval ) {
                                        if( $timeslot_values[ 'lockout' ] != "" && $timeslot_values[ 'lockout' ] != "0" ) {
                                            $delivery_days[ $dval ][ $time_slot ] = $timeslot_values[ 'lockout' ];
                                        } else if ( get_option( 'orddd_global_lockout_time_slots' ) != '0' && get_option( 'orddd_global_lockout_time_slots' ) != '' ) {
                                            $delivery_days[ $dval ][ $time_slot ] = get_option( 'orddd_global_lockout_time_slots' );
                                        } else {
                                            $delivery_days[ $dval ][ $time_slot ] = 0;
                                        }
                                    }
                                } else if ( $timeslot_values[ 'delivery_days_selected' ] == 'specific_dates' ) {
                                    foreach( $timeslot_values[ 'selected_days' ] as $dkey => $dval ) {
                                        if( $timeslot_values[ 'lockout' ] != "" && $timeslot_values[ 'lockout' ] != "0" ) {
                                            $specific_dates[ $dval ][ $time_slot ] = $timeslot_values[ 'lockout' ];
                                        } else if ( get_option( 'orddd_global_lockout_time_slots' ) != '0' && get_option( 'orddd_global_lockout_time_slots' ) != '' ) {
                                            $specific_dates[ $dval ][ $time_slot ] = get_option( 'orddd_global_lockout_time_slots' );
                                        } else {
                                            if( isset( $specific_dates[ $dval ][ $time_slot ] ) ) {
                                                $specific_dates[ $dval ][ $time_slot ] = 0;
                                            }
                                        }
                                    }
                                }
                                
                                if( $timeslot_values[ 'lockout' ] != "" && $timeslot_values[ 'lockout' ] != "0" ) {
                                    if( isset( $shipping_settings_to_check[ 'orddd_lockout_time_slot' ] ) ) {
                                        $lockout_time = $shipping_settings_to_check[ 'orddd_lockout_time_slot' ];
                                        if ( $lockout_time == '' || $lockout_time == '{}' || $lockout_time == '[]' || $lockout_time == 'null' ) {
                                            $lockout_time_arr = array();
                                        } else {
                                            $lockout_time_arr = json_decode( $lockout_time );
                                        }
                                    }
                                } else if( get_option( 'orddd_global_lockout_time_slots' ) != '0' && get_option( 'orddd_global_lockout_time_slots' ) != '' ) {
                                    $lockout_time = get_option( 'orddd_lockout_time_slot' );
                                    if ( $lockout_time == '' || $lockout_time == '{}' || $lockout_time == '[]' || $lockout_time == 'null' ) {
                                        $lockout_time_arr = array();
                                    } else {
                                        $lockout_time_arr = json_decode( $lockout_time );
                                    }
                                }
                            }
                        }
                    }  
    
                    foreach ( $lockout_time_arr as $k => $v ) {
                        $lockout_time_slot = explode( " - ", $v->t );
                        $lockout_from_time = date( "G:i", strtotime( $lockout_time_slot[ 0 ] ) );
                        if( isset( $lockout_time_slot[1] ) )  {
                            $lockout_to_time = date( "G:i", strtotime( $lockout_time_slot[ 1 ] ) );
                            $lockout_time_str = $lockout_from_time . " - " . $lockout_to_time;
                        } else {
                            $lockout_time_str = $lockout_from_time;
                        }
    
                        $weekday = date( 'w', strtotime( $k ) );
                        $date_str = date( 'j-n-Y', strtotime( $v->d ) );
                        if ( array_key_exists( $date_str, $date ) ) {
                            $previous_orders =  $date[ $date_str ][ $v->t ] + $v->o;
                            $date[ $date_str ][ $v->t ] = $previous_orders;
    
                        } else {
                            $date[ $date_str ][ $v->t ] = $v->o;
                        }
                    }
                    
                    $partially_lockout_dates .= "'available_slots>" . __( "Available Delivery Slots", "order-delivery-date" ) . "nl'," ;
                    foreach( $date as $dk => $dv ) {
                        $available_timeslot_deliveries = '';
                        $lockout_date_arr = explode( "-", $dk );
                        $date_lockout_time = strtotime( $dk );
                        if( $date_lockout_time > $current_time ) {
                            $lockout_date = $lockout_date_arr[1] . "-" . $lockout_date_arr[ 0 ] . "-" . $lockout_date_arr[2];
                            if ( is_array( $delivery_days ) && count( $delivery_days ) > 0 ) {
                                $weekday = date( 'w', strtotime( $dk ) );
                                if ( isset( $delivery_days[ 'orddd_weekday_' . $weekday ] ) && '0' != array_sum( $delivery_days[ 'orddd_weekday_' . $weekday ] ) ) {
                                    $time_slots = $delivery_days[ 'orddd_weekday_' . $weekday ];
                                } else if( isset( $delivery_days[ 'all' ] ) && '0' != array_sum( $delivery_days[ 'all' ] ) ) {
                                    $time_slots = $delivery_days[ 'all' ];
                                }
    
                                foreach( $time_slots as $tk => $tv ) {
                                    if( array_key_exists( $tk , $dv ) && $tv >= $dv[ $tk ] ) {
                                        if( ( $tv - $dv[ $tk ] ) > 0 ) {
                                            $available_timeslot_deliveries .= $tk . ": " . ( $tv - $dv[ $tk ] ). "nl";    
                                        }
                                    } else {
                                        $available_timeslot_deliveries .= $tk . ": " . $tv . "nl";
                                    }
                                }
                                $partially_lockout_dates .= "'" . $lockout_date . ">" . $available_timeslot_deliveries . "',";
                            }
                        }
                    }
    
                    $available_deliveries = "'available_slots>" . __( "Available Delivery Slots", "order-delivery-date" ) . "'," ;
                    foreach( $delivery_days as $del_days_key => $del_days_val ) {
                        if( $del_days_key == 'all' ) {
                            $time_slots = $delivery_days[ 'all' ];
                            for( $i = 0; $i < 7; $i++ ) {
                                foreach( $time_slots as $tk => $tv ) {
                                    if( $tv == 0 ) {
                                        $available_deliveries .= "'orddd_weekday_" . $i . ">" . $tk . ": " . __( 'Unlimited', 'order-delivery-date' ) . "',";            
                                    } else {
                                        $available_deliveries .= "'orddd_weekday_" . $i . ">" . $tk . ": " . $tv . "',";    
                                    }
                                }
                            }
                        } else {
                            $time_slots = $delivery_days[ $del_days_key ];
                            foreach( $time_slots as $tk => $tv ) {
                                if( $tv == 0 ) {
                                    $available_deliveries .= "'" . $del_days_key . ">" . $tk . ": " . __( 'Unlimited', 'order-delivery-date' ) . "',";            
                                } else {
                                    $available_deliveries .= "'" . $del_days_key . ">" . $tk . ": " . $tv . "',";
                                }    
                            }
                        }
                    }
                } else {
                    if( isset( $shipping_settings_to_check[ 'date_lockout' ] ) && $shipping_settings_to_check[ 'date_lockout' ] != '' && $shipping_settings_to_check[ 'date_lockout' ] != '0' ) {
                        $date_lockout = $shipping_settings_to_check[ 'date_lockout' ];
                        if( isset( $shipping_settings_to_check[ 'orddd_lockout_date' ] ) ) {
                            $lockout_date_array = $shipping_settings_to_check[ 'orddd_lockout_date' ];
                            if ( $lockout_date_array == '' || 
                                 $lockout_date_array == '{}' || 
                                 $lockout_date_array == '[]' || 
                                 $lockout_date_array == 'null' ) {
                                $lockout_date_arr = array();
                            } else {
                                $lockout_date_arr = (array) json_decode( $lockout_date_array );
                            }
                        } else {
                            $lockout_date_arr = array();
                        }
    					
                        foreach ( $lockout_date_arr as $k => $v ) {
                            $partially_lockout_dates .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . ( $date_lockout - $v->o ) . "',";
                        }
                        $available_deliveries = __( 'Available Deliveries: ', 'order-delivery-date' ) . $date_lockout;
                    }
                }
                $is_custom_enabled = 'yes';
            }
        } 
        
        // change the condition for the global settings
        if( 'no' == $is_custom_enabled ) {
            $date_lockout = get_option( 'orddd_lockout_date_after_orders' );
            if ( get_option( 'orddd_enable_time_slot' ) == 'on' ) {
                $date = array();
                $lockout_timeslots_arr = array();
                $previous_orders = 0;

                $lockout_timeslots_days = get_option( 'orddd_lockout_time_slot' );
                if ( $lockout_timeslots_days != ''   && 
                    $lockout_timeslots_days  != '{}' && 
                    $lockout_timeslots_days  != '[]' && 
                    $lockout_timeslots_days  != 'null' ) {
                    $lockout_timeslots_arr = json_decode( get_option( 'orddd_lockout_time_slot' ) );
                }

                foreach ( $lockout_timeslots_arr as $k => $v ) {
                    $date_str = date( 'j-n-Y', strtotime( $v->d ) );
                    if( isset( $date[ $date_str ][ $v->t ] ) ) {
                        $previous_orders =  $date[ $date_str ][ $v->t ] + $v->o;
                        $date[ $date_str ][ $v->t ] = $previous_orders;
                    } else {
                        $date[ $date_str ][ $v->t ] = $v->o;
                    }
                }

                $specific_dates = array();
                $delivery_days = array();
                $previous_lockout = 0;

                $existing_timeslots_arr = json_decode( get_option( 'orddd_delivery_time_slot_log' ) );
                // Sort the multidimensional array
                usort( $existing_timeslots_arr, array( 'orddd_common', 'orddd_custom_sort' ) );
                foreach( $existing_timeslots_arr as $k => $v ) {
                    $from_time = date( $time_format_to_show, strtotime( $v->fh . ":" . trim( $v->fm, ' ' ) ) );
                    $to_time = date( $time_format_to_show, strtotime( $v->th . ":" . trim( $v->tm, ' ' ) ) );
                    $time_slot = $from_time . " - " . $to_time;
                    $dd = json_decode( $v->dd );
                    if ( is_array( $dd ) &&  count( $dd ) > 0 ) {
                        foreach ( $dd as $dkey => $dval ) {
                            if( $v->lockout != "" && $v->lockout != "0" ) {
                                if( $v->tv == 'weekdays' ) {
                                    $delivery_days[ $dval ][ $time_slot ] = $v->lockout;
                                } else {
                                    $specific_dates[ $dval ][ $time_slot ] = $v->lockout;
                                }
                            }
                        }
                    }
                }

                $partially_lockout_dates .= "'available_slots>" . __( "Available Delivery Slots", "order-delivery-date" ) . "nl'," ;
                foreach( $date as $dk => $dv ) {
                    $available_timeslot_deliveries = '';
                    $lockout_date_arr = explode( "-", $dk );
                    $date_lockout_time = strtotime( $dk );
                    if( $date_lockout_time > $current_time ) {
                        $lockout_date = $lockout_date_arr[1] . "-" . $lockout_date_arr[ 0 ] . "-" . $lockout_date_arr[2];
                        if ( is_array( $delivery_days ) && count( $delivery_days ) > 0 ) {
                            $weekday = date( 'w', strtotime( $dk ) );
                            if ( isset( $delivery_days[ 'orddd_weekday_' . $weekday ] ) && '0' != array_sum( $delivery_days[ 'orddd_weekday_' . $weekday ] ) ) {
                                $time_slots = $delivery_days[ 'orddd_weekday_' . $weekday ];
                            } else if( isset( $delivery_days[ 'all' ] ) && '0' != array_sum( $delivery_days[ 'all' ] ) ) {
                                $time_slots = $delivery_days[ 'all' ];
                            }

                            foreach( $time_slots as $tk => $tv ) {
                                if( array_key_exists( $tk , $dv ) && $tv >= $dv[ $tk ] ) {
                                    if( ( $tv - $dv[ $tk ] ) > 0 ) {
                                        $available_timeslot_deliveries .= $tk . ": " . ( $tv - $dv[ $tk ] ). "nl";    
                                    }
                                } else {
                                    $available_timeslot_deliveries .= $tk . ": " . $tv . "nl";
                                }
                            }
                            $partially_lockout_dates .= "'" . $lockout_date . ">" . $available_timeslot_deliveries . "',";
                        }
                    }
                }

                $available_deliveries = "'available_slots>" . __( "Available Delivery Slots", "order-delivery-date" ) . "'," ;
                foreach( $delivery_days as $del_days_key => $del_days_val ) {
                    if( $del_days_key != 'all' ) {
                        $time_slots = $delivery_days[ $del_days_key ];
                        foreach( $time_slots as $tk => $tv ) {
                            $available_deliveries .= "'" . $del_days_key . ">" . $tk . ": " . $tv . "',";    
                        }
                    } else {
                        $time_slots = $delivery_days[ 'all' ];
                        for( $i = 0; $i < 7; $i++ ) {
                            foreach( $time_slots as $tk => $tv ) {
                                $available_deliveries .= "'orddd_weekday_" . $i . ">" . $tk . ": " . $tv . "',";    
                            }
                        }
                    }
                }
            } else {
                if ( $date_lockout > 0 && $date_lockout != '' ) {
                    $lockout_days_arr = array();
                    $lockout_days = get_option( 'orddd_lockout_days' );
                    if ( $lockout_days != '' && $lockout_days != '{}' && $lockout_days != '[]' && $lockout_days != "null" ) {
                        $lockout_days_arr = json_decode( get_option( 'orddd_lockout_days' ) );
                    }
                    foreach ( $lockout_days_arr as $k => $v ) {
                        $partially_lockout_dates .= "'" . $v->d . ">" . __( 'Available Deliveries: ', 'order-delivery-date' ) . ( $date_lockout - $v->o ) . "',";
                    }    
                    $partially_lockout_dates = trim( $partially_lockout_dates, "," );
                    $available_deliveries = __( 'Available Deliveries: ', 'order-delivery-date' ) . $date_lockout;
                }
            }
        }

        $partially_lockout_dates .= "&" . $available_deliveries;        
        return $partially_lockout_dates;
    }
}
$orddd_widget = new orddd_widget();