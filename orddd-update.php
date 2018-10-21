<?php
/**
 * Order Delivery Date Pro for WooCommerce
 *
 * Update the necessary options when plugin is updated. 
 *
 * @author      Tyche Softwares
 * @package     Order-Delivery-Date-Pro-for-WooCommerce/Update
 * @since       8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( is_admin() ) {
    include_once( 'orddd-common.php' ); 
}

/**
 * orddd_update Class
 *
 * @class orddd_update
 */

class orddd_update {

	/**
	 * Default Constructor
	 *
	 * @since 8.1
	 */
	public function __construct() {
		//Update plugin
	    add_action( 'admin_init', array( &$this, 'orddd_update_db_check' ) );
	}

	/**
	 * Executed when the plugin is updated using the Automatic Updater. 
	 * 
	 * @hook admin_init
	 * @globals int $orddd_plugin_version Current plugin version
	 * @globals int $orddd_version Current plugin version
	 * @since 1.0
	 */
	public function orddd_update_db_check() {
		global $orddd_plugin_version, $orddd_version;
		$orddd_plugin_version = $orddd_version;
		if ( $orddd_plugin_version == "8.7" ) {
			self::orddd_update_install();
		}
	}

	/**
	 * Update the settings if required when the plugin is updated using the Automatic Updater.
	 *
	 * @globals resource $wpdb WordPress object
	 * @globals array $orddd_weekdays Weekdays Array
	 *
	 * @since 1.0
	 */
	public static function orddd_update_install() {
	    global $wpdb, $orddd_weekdays;
	    
	    //code to set the option to on as default
	    $orddd_plugin_version = get_option( 'orddd_db_version' );
	    if ( $orddd_plugin_version != order_delivery_date::get_orddd_version() ) {
	        //Update Database version
            update_option( 'orddd_db_version', '8.7' );
	        
	        self::orddd_update_default_time_slot_weekday();

	        self::orddd_update_timeslot_for_value();
	        
	        self::orddd_update_minimum_delivery_time();

	        self::orddd_update_weekday_value();
			
	        self::orddd_update_default_value_product_cat();

	        self::orddd_update_placeholder();

	        self::orddd_update_default_custom_settings_type();
	        
	        self::orddd_update_time_slot_for_value();
	        
	        self::orddd_update_default_sorting_value();

	        self::orddd_update_tax_cal();

	        self::orddd_update_default_field_placement();
	        
	       	self::orddd_update_timeslot_charges_label();
	        
	       	self::orddd_update_time_format();

	       	self::orddd_update_auto_fil();

	       	self::orddd_update_advance_settings();

	       	self::orddd_update_delivery_checkout_options();

	       	self::orddd_update_holiday_type();

            self::orddd_update_location_label();
        }
	    
	    //Update the shipping method with legacy prefix with the WooCommerce 2.6 update
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, "2.6.0", '>=' ) ) {
	        if( get_option( 'orddd_update_shipping_method_id_delete' ) != 'yes' ) {
	            delete_option( 'orddd_update_shipping_method_id' );
	            update_option( 'orddd_update_shipping_method_id_delete', 'yes' );
	        }
	
	        if( get_option( 'orddd_update_shipping_method_id' ) != 'yes' ) {
	            $results = orddd_common::orddd_get_shipping_settings();
	            if( is_array( $results ) && count( $results ) > 0 ) {
	                foreach ( $results as $key => $value ) {
	                    $shipping_settings = get_option( $value->option_name );
	                    if ( isset( $shipping_settings[ 'delivery_settings_based_on' ] ) && $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] == 'shipping_methods' ) {
	                        $shipping_methods = $shipping_settings[ 'shipping_methods' ];
	                        foreach( $shipping_methods as $shipping_key => $shipping_value ) {
	                            if ( $shipping_value == 'flat_rate' ) {
	                                $shipping_settings[ 'shipping_methods' ][ $shipping_key ] = 'legacy_flat_rate';
	                            }
	
	                            if ( $shipping_value == 'free_shipping' ) {
	                                $shipping_settings[ 'shipping_methods' ][ $shipping_key ] = 'legacy_free_shipping';
	                            }
	
	                            if ( $shipping_value == 'international_delivery' ) {
	                                $shipping_settings[ 'shipping_methods' ][ $shipping_key ] = 'legacy_international_delivery';
	                            }
	
	                            if ( $shipping_value == 'local_pickup' ) {
	                                $shipping_settings[ 'shipping_methods' ][ $shipping_key ] = 'legacy_local_pickup';
	                            }
	
	                            if ( $shipping_value == 'local_delivery' ) {
	                                $shipping_settings[ 'shipping_methods' ][ $shipping_key ] = 'legacy_local_delivery';
	                            }
	                        }
	                    }
	                    update_option( $value->option_name, $shipping_settings );
	                }
	            }
	            update_option( 'orddd_update_shipping_method_id', 'yes' );
	        }
	    }
	}

	/**
	 * Function to update the default weekday to All for the existing time slots.
	 *
	 * @since 8.1
	 */
	public static function orddd_update_default_time_slot_weekday() {
        $existing_timeslots_str = get_option( 'orddd_delivery_time_slot_log' );
        $existing_timeslots_arr = json_decode( $existing_timeslots_str );
        if( $existing_timeslots_arr == 'null' || $existing_timeslots_arr == '' || $existing_timeslots_arr == '{}' || $existing_timeslots_arr == '[]' ) {
            $existing_timeslots_arr = array();
        }
        
        if( is_array( $existing_timeslots_arr ) && count( $existing_timeslots_arr ) > 0 ) {
            foreach ( $existing_timeslots_arr as $k => $v ) {
                if( $v->dd == '' ) {
                    $v->dd = 'all';
                    $existing_timeslots_arr [ $k ] = $v;
                }
            }
            $timeslot_update_arr = json_encode( $existing_timeslots_arr  );
            update_option( 'orddd_delivery_time_slot_log', $timeslot_update_arr );
        }
	}

	/** 
	 * Function to update the tv value to either weekdays or specific_days for existing time slot.
	 *
	 * @since 8.1
	 */
	public static function orddd_update_timeslot_for_value() {
        $orddd_time_slot_log_for_tv = get_option( 'update_time_slot_log_for_tv' );
        if ( $orddd_time_slot_log_for_tv != 'yes' ) {
            $existing_timeslots_str = get_option( 'orddd_delivery_time_slot_log' );
            $existing_timeslots_arr = json_decode( $existing_timeslots_str );
            if( $existing_timeslots_arr == 'null' || $existing_timeslots_arr == '' || $existing_timeslots_arr == '{}' || $existing_timeslots_arr == '[]' ) {
                $existing_timeslots_arr = array();
            }

            if( is_array( $existing_timeslots_arr ) && count( $existing_timeslots_arr ) > 0 ) {
                foreach ( $existing_timeslots_arr as $k => $v ) {
                    if ( !isset( $v->tv ) ) {
                        if ( gettype( json_decode( $v->dd )) == 'array' && count( json_decode( $v->dd ) ) > 0 ) {
                            $v->tv = 'specific_dates';
                            $existing_timeslots_arr [ $k ] = $v;
                        } else {
                            $v->tv = 'weekdays';
                            $existing_timeslots_arr [ $k ] = $v;
                        }
                    }
                }
                $timeslot_update_arr = json_encode( $existing_timeslots_arr  );
                update_option( 'orddd_delivery_time_slot_log', $timeslot_update_arr );
            }
            update_option( 'update_time_slot_log_for_tv', 'yes' );
        }

	}

	/** 
	 * Function to convert the Minimum delivery time(in days) to Minimum delivery time(in hours)
	 *
	 * @since 8.1
	 */
	public static function orddd_update_minimum_delivery_time() {
        $orddd_abp_hrs = get_option( 'orddd_abp_hrs' );
        if ( $orddd_abp_hrs != 'HOURS' ) {
            // Convert the Minimum Delivery time in days to hours
            if ( get_option( 'orddd_minimumOrderDays' ) > 0 ) {
                $advance_period_hrs = get_option( 'orddd_minimumOrderDays' ) * 24;
                update_option( 'orddd_minimumOrderDays', $advance_period_hrs );
            }
            update_option( 'orddd_abp_hrs', 'HOURS' );
        }
	}

	/**
	 * Function to disable the weekdays while updating if specific delivery date is enabled in version 2.7.8 and below.
	 * 
	 * @since 8.1
	 */
	public static function orddd_update_weekday_value() {
        global $orddd_weekdays;
        if( get_option( 'update_weekdays_value' ) != 'yes' ) {
            $specific_date_enable = get_option( 'orddd_enable_specific_delivery_dates' );
            if( $specific_date_enable == 'on' ) {
                foreach ( $orddd_weekdays as $n => $day_name ) {
                    update_option( $n, '' );
                }
            }
            update_option( 'update_weekdays_value', 'yes' );
        }
    }
	        
    /**
     * By default enable the Enable Delivery Date checkbox for all the product categories
     *
     * @since 8.1
     */
    public static function orddd_update_default_value_product_cat() {
        global $wpdb;
        if( get_option( 'update_delivery_product_category' ) != 'yes' ) {
            $terms = $wpdb->get_results( 'SELECT term_id FROM ' . $wpdb->prefix . 'term_taxonomy WHERE taxonomy="product_cat"' );
            foreach( $terms as $term_key => $term_value ) {
                foreach( $term_value as $key => $v ) {
                    if( $key == 'term_id') {
                        $category_id = $term_value->term_id;
                        update_woocommerce_term_meta( $category_id, 'orddd_delivery_date_for_product_category', 'on' );
                    }
                }
            }
            update_option( 'update_delivery_product_category', 'yes');
        }
    }

    /**
     * Set the default value for the delivery date field placeholder
     *
     * @since 8.1
     */
    public static function orddd_update_placeholder() {
        if ( get_option( 'update_placeholder_value' ) != 'yes' ) {
            update_option( 'orddd_delivery_date_field_placeholder', ORDDD_DELIVERY_DATE_FIELD_PLACEHOLDER );
            update_option( 'update_placeholder_value', 'yes' );
        }
    }

    /**
     * Update the default value for Custom Settings Based on value to Shipping Methods.
     *
     * @since 8.1
     */
    public static function orddd_update_default_custom_settings_type() {
        if( get_option( 'orddd_update_shipping_delivery_settings_based' ) != 'yes' ) {
            $results = orddd_common::orddd_get_shipping_settings();
            foreach ( $results as $key => $value ) {
                $shipping_settings = get_option( $value->option_name );
                if( !isset( $shipping_settings[ 'delivery_settings_based_on' ] ) ) {
                    $shipping_settings[ 'delivery_settings_based_on' ][ 0 ] = 'shipping_methods';
                }
                update_option( $value->option_name, $shipping_settings );
            }
            update_option( 'orddd_update_shipping_delivery_settings_based', 'yes' );
        }
    }

    /**
     * Function to update the time slot value whether it is 'Weekdays' or 'Specific dates'
     *
     * @since 8.1
     */
    public static function orddd_update_time_slot_for_value() {
        if ( get_option( 'orddd_update_time_slot_for_shipping_delivery' ) != 'yes' ) {
            $results = orddd_common::orddd_get_shipping_settings();
            foreach ( $results as $key => $value ) {
                $specific_date_delivery = $weekday_delivery = '';
                $shipping_settings = get_option( $value->option_name );
                $time_slot_value = '';
                if( isset( $shipping_settings[ 'time_slots' ] ) ) {
                    $time_slots = explode( ',', $shipping_settings[ 'time_slots' ] );
                    foreach( $time_slots as $tk => $tv ) {
                        if( $tv != '' ) {
                            $timeslot_values = orddd_common::get_timeslot_values( $tv );
                            if( isset( $shipping_settings[ 'delivery_type' ] ) ) {
                                $delivery_type = $shipping_settings[ 'delivery_type' ];
                                if( isset( $delivery_type[ 'weekdays' ] ) && $delivery_type[ 'weekdays' ] == 'on' ) {
                                    $weekday_delivery = 'checked';
                                }
                                if( isset( $delivery_type[ 'specific_dates' ] ) && $delivery_type[ 'specific_dates' ] == 'on' ) {
                                    $specific_date_delivery = 'checked';
                                }
                            }
                            
                            if( $weekday_delivery == "checked" && $specific_date_delivery == "checked" ) {
                                if ( isset( $shipping_settings[ 'weekdays' ] ) && $shipping_settings[ 'weekdays' ] != '{}' && $shipping_settings[ 'weekdays' ] != '[]' && $shipping_settings[ 'weekdays' ] != 'null' ) {
                                    $delivery_day_selected = "weekdays";
                                    $weekday = 'all';
                                    $time_slot_value .= "{" . $delivery_day_selected . ":" . $weekday . ":" . $timeslot_values[ 'time_slot' ] . ":" . $timeslot_values[ 'lockout' ] . "},";
                                }
                                if ( isset( $shipping_settings[ 'specific_dates' ] ) && $shipping_settings[ 'specific_dates' ] != '{}' && $shipping_settings[ 'specific_dates' ] != '[]' && $shipping_settings[ 'specific_dates' ] != 'null' ) {
                                    $delivery_day_selected = "specific_dates";
                                    $specific_dates = '';
                                    $specific_days_settings = explode( ',', $shipping_settings[ 'specific_dates' ] );
                                    foreach( $specific_days_settings as $sk => $sv ) {
                                        if( $sv != '' ) {
                                            $specific_date_str = str_replace( '}', '', $sv );
                                            $specific_date_str = str_replace( '{', '', $specific_date_str );
                                            $specific_date_arr = explode( ':', $specific_date_str );
                                            $specific_dates .= $specific_date_arr[ 0 ] . ",";
                                        }
                                    }
                                    $specific_dates = substr( $specific_dates, 0, strlen( $specific_dates )-1 );
                                    $time_slot_value .= "{" . $delivery_day_selected . ":" . $specific_dates .":". $timeslot_values[ 'time_slot' ] . ":" . $timeslot_values[ 'lockout' ] . "},";
                                }
                            } else if( $weekday_delivery == "checked" ) {
                                if ( isset( $shipping_settings[ 'weekdays' ] ) && $shipping_settings[ 'weekdays' ] != '{}' && $shipping_settings[ 'weekdays' ] != '[]' && $shipping_settings[ 'weekdays' ] != 'null' ) {
                                    $delivery_day_selected = "weekdays";
                                    $weekday = 'all';
                                    $time_slot_value .= "{" . $delivery_day_selected . ":" . $weekday . ":" . $timeslot_values[ 'time_slot' ] . ":" . $timeslot_values[ 'lockout' ] . "},";
                                }
                            } else if( $specific_date_delivery == "checked" ) {
                                if ( isset( $shipping_settings[ 'specific_dates' ] ) && $shipping_settings[ 'specific_dates' ] != '{}' && $shipping_settings[ 'specific_dates' ] != '[]' && $shipping_settings[ 'specific_dates' ] != 'null' ) {
                                    $delivery_day_selected = "specific_dates";
                                    $specific_dates = '';
                                    $specific_days_settings = explode( ',', $shipping_settings[ 'specific_dates' ] );
                                    foreach( $specific_days_settings as $sk => $sv ) {
                                        if( $sv != '' ) {
                                            $specific_date_str = str_replace( '}', '', $sv );
                                            $specific_date_str = str_replace( '{', '', $specific_date_str );
                                            $specific_date_arr = explode( ':', $specific_date_str );
                                            $specific_dates .= $specific_date_arr[ 0 ] . ",";
                                        }
                                    }
                                    $specific_dates = substr( $specific_dates, 0, strlen( $specific_dates )-1 );
                                    $time_slot_value .= "{" . $delivery_day_selected . ":" . $specific_dates . ":" . $timeslot_values[ 'time_slot' ] . ":" . $timeslot_values[ 'lockout' ] . "},";
                                }
                            }
                        }
                    }
                }
                $shipping_settings[ 'time_slots' ] = $time_slot_value;
                update_option( $value->option_name, $shipping_settings );
            }
            update_option( 'orddd_update_time_slot_for_shipping_delivery', 'yes' );
        }
    }

    /**
     * Default sorting is enabled when plugin is updated.
     *
     * @since 8.1
     */
    public static function orddd_update_default_sorting_value() {
        if( get_option( 'orddd_default_sorting' ) != 'yes' ) {
            update_option( 'orddd_enable_default_sorting_of_column', 'on' );
            update_option( 'orddd_default_sorting', 'yes' );
        }
    }
	
	/**
	 * Set the tax calculation checkbox enabled when charges are added for any of the day or time slot
	 *
	 * @since 8.1
	 */
	public static function orddd_update_tax_cal() {
        global $orddd_weekdays;
        if( get_option( 'orddd_tax_calculation_enabled' ) != 'yes' ) {
            $delivery_charges = 'no';
            foreach ( $orddd_weekdays as $n => $day_name ) {
                $fee = get_option( 'additional_charges_' . $n );
                if( $fee > 0 && $fee != '' ) {
                    $delivery_charges = 'yes';
                    break;
                }
            }
            
            if( $delivery_charges == 'no' && get_option( 'orddd_same_day_additional_charges' ) != '' && get_option( 'orddd_same_day_additional_charges' ) > 0 ) {
                $delivery_charges = 'yes';
            }

            if( $delivery_charges == 'no' && get_option( 'orddd_next_day_additional_charges' ) != '' && get_option( 'orddd_next_day_additional_charges' ) > 0 ) {
                $delivery_charges = 'yes';
            }

            if( $delivery_charges == 'no' && ( ( get_option( 'additional_charges_1' ) != '' && get_option( 'additional_charges_1' ) > 0 )
                || ( get_option( 'additional_charges_2' ) != '' && get_option( 'additional_charges_2' ) > 0 )
                || ( get_option( 'additional_charges_3' ) != '' && get_option( 'additional_charges_3' ) > 0 ) ) ) {
                $delivery_charges = 'yes';
            }

            if( $delivery_charges == 'no' && get_option( 'orddd_enable_shipping_based_delivery' ) == 'on' ) {
                $results = orddd_common::orddd_get_shipping_settings();
                foreach ( $results as $key => $value ) {
                    $shipping_settings = get_option( $value->option_name );
                    $row_id = substr( $value->option_name, strrpos( $value->option_name, "_" ) + 1 );
                    $return_shipping_settings[ $row_id ] = new stdClass();
                    $shipping_method_str = $shipping_days = '';
                    if( isset( $shipping_settings[ 'delivery_type' ] ) ) {
                        $delivery_type = $shipping_settings[ 'delivery_type' ];
                    } else {
                        $delivery_type = '';
                    }

                    if( isset( $delivery_type[ 'weekdays' ] ) && $delivery_type[ 'weekdays' ] == 'on' ) {
                        if( isset( $shipping_settings[ 'weekdays' ] ) ) {
                            $weekdays_settings = $shipping_settings[ 'weekdays' ];
                        } else {
                            $weekdays_settings = array();
                        }
                        if( is_array( $weekdays_settings ) && count( $weekdays_settings ) > 0 ) {
                            foreach( $orddd_weekdays as $wk => $wv ) {
                                $weekday = $weekdays_settings[ $wk ];
                                if( isset( $weekday[ 'enable' ] ) && $weekday[ 'enable' ] == 'checked' ) {
                                    if( isset( $weekday[ 'additional_charges' ] ) && $weekday[ 'additional_charges' ] != '' && $weekday[ 'additional_charges' ] > 0 ) {
                                        $delivery_charges = 'yes';
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    if( isset( $delivery_type[ 'specific_dates' ] ) && $delivery_type[ 'specific_dates' ] == 'on' ) {
                        $specific_days_settings = explode( ',', $shipping_settings[ 'specific_dates' ] );
                        foreach( $specific_days_settings as $sk => $sv ) {
                            $sv_str = str_replace('}', '', $sv);
                            $sv_str = str_replace('{', '', $sv_str);
                            $specific_date_arr = explode( ':', $sv_str );
                            if( isset( $specific_date_arr[ 0 ] ) && $specific_date_arr[ 0 ] != '' ) {
                                if( isset( $specific_date_arr[ 1 ] ) && $specific_date_arr[ 1 ] != '' &&  $specific_date_arr[ 1 ] > 0 ) {
                                    $delivery_charges = 'yes';
                                    break 2;
                                }
                            }
                        }
                    }

                    if( isset( $shipping_settings[ 'same_day' ] ) ) {
                        $same_day = $shipping_settings[ 'same_day' ];
                        if( isset( $same_day[ 'after_hours' ] ) && $same_day[ 'after_hours' ] != 0 ) {
                            if( isset( $same_day[ 'additional_charges' ] ) && $same_day[ 'additional_charges' ] != '' && $same_day[ 'additional_charges' ] > 0 ) {
                                $delivery_charges = 'yes';
                                break;
                            }
                        }
                    }

                    if( isset( $shipping_settings[ 'next_day' ] ) ) {
                        $next_day = $shipping_settings[ 'next_day' ];
                        if( isset( $next_day[ 'after_hours' ] ) && $next_day[ 'after_hours' ] != 0 ) {
                            if( isset( $next_day[ 'additional_charges' ] ) && $next_day[ 'additional_charges' ] != '' && $next_day[ 'additional_charges' ] > 0 ) {
                                $delivery_charges = 'yes';
                                break;
                            }
                        }
                    }
                }
            }
            
            if( $delivery_charges == 'yes' ) {
                update_option( 'orddd_enable_tax_calculation_for_delivery_charges', 'on' );
            }
            update_option( "orddd_tax_calculation_enabled", 'yes' );
        }
    }	  

    /**
     * Default value for placement of fields on the checkout page
     *
     * @since 8.1
     */
    public static function orddd_update_default_field_placement() {
        if ( get_option( 'orddd_delivery_date_on_checkout_page_enabled' ) != 'yes' ) {
            if ( get_option( 'orddd_date_in_shipping' ) == 'on' ) {
                update_option( 'orddd_delivery_date_fields_on_checkout_page', 'shipping_section' );
                delete_option( 'orddd_date_in_shipping' );
            } else {
                update_option( 'orddd_delivery_date_fields_on_checkout_page', 'billing_section' );
                delete_option( 'orddd_date_in_shipping' );
            }
            update_option( 'orddd_delivery_date_on_checkout_page_enabled', 'yes' );
        }      
    }


    /**
     * Default values for time slot charges and checkout label
     *
     * @since 8.1
     */
    public static function orddd_update_timeslot_charges_label() {
        if( get_option( 'orddd_update_additional_charges_records' ) != 'yes' ) {
			$results = orddd_common::orddd_get_shipping_settings();
            foreach ( $results as $key => $value ) {
                $shipping_settings = get_option( $value->option_name );
                if( isset( $shipping_settings[ 'time_slots' ] ) ) {
                    $time_slots = explode( ',', $shipping_settings[ 'time_slots' ] );
                    $time_slot_value = '';
                    foreach( $time_slots as $tk => $tv ) {
                        if( $tv != '' ) {
                            $tv_str = str_replace( '}', '', $tv );
                            $tv_str = str_replace( '{', '', $tv_str );
                            $tv_explode = explode( ":", $tv_str );
                            $time_slot_value .= "{" . $tv_explode[ 0 ] .":". $tv_explode[ 1 ] .":". $tv_explode[ 2 ] . ":" . $tv_explode[ 3 ]. ":" . $tv_explode[ 4 ] . ":". $tv_explode[ 5 ] . "::},";
                        }
                    }
                }
                $shipping_settings[ 'time_slots' ] = $time_slot_value;
                update_option( $value->option_name, $shipping_settings );
            }

            $timeslots = get_option( 'orddd_delivery_time_slot_log' );
            $timeslot_new_arr = $timeslot_arr = array();
            if ( $timeslots != 'null' && $timeslots != '' && $timeslots != '{}' && $timeslots != '[]' ) {
                $timeslot_arr = json_decode( $timeslots );
            }
	            
            if ( isset( $timeslot_arr ) && is_array( $timeslot_arr ) && count( $timeslot_arr ) > 0 ) {
                foreach ( $timeslot_arr as $k => $v ) {
                    $timeslot_new_arr[] = array( 'tv' => $v->tv, 'dd' => $v->dd, 'lockout' => $v->lockout, 'additional_charges' => "", 'additional_charges_label'  => "", 'fh' => $v->fh, 'fm' => $v->fm, 'th' => $v->th, 'tm' => $v->tm );
                }
            }

            $timeslot_jarr = json_encode( $timeslot_new_arr );
            update_option( 'orddd_delivery_time_slot_log', $timeslot_jarr );
            update_option( 'orddd_update_additional_charges_records', 'yes' );
        }
    }
	       
	/**
	 * Update time format for the time slot format
	 *
	 * @since 8.1
	 */
	public static function orddd_update_time_format() {
        if( get_option( 'orddd_update_time_format' ) != 'yes' ) {
            if( get_option( 'orddd_enable_time_slot' ) == 'on' && get_option( 'orddd_enable_delivery_time' ) != 'on' ) {
                $timeslot_format = get_option( 'orddd_delivery_timeslot_format' );
                update_option( 'orddd_delivery_time_format', $timeslot_format );
            } else if( get_option( 'orddd_enable_time_slot' ) != 'on' && get_option( 'orddd_enable_delivery_time' ) == 'on' ) {
                $timeslot_format = get_option( 'orddd_delivery_time_format' );
                update_option( 'orddd_delivery_time_format', $timeslot_format );
            } else {
                $timeslot_format = get_option( 'orddd_delivery_timeslot_format' );
                update_option( 'orddd_delivery_time_format', $timeslot_format );
            }
            update_option( 'orddd_update_time_format', 'yes' );
        }
    }
	        
    /**
     * Default value for Auto populate of the delivery date field
     *
     * @since 8.1
     */
    public static function orddd_update_auto_fil() {
        if( get_option( 'orddd_update_auto_populate_first_available_time_slot' ) != 'yes' ) {
            if( get_option( 'orddd_show_first_available_time_slot_as_selected' ) == 'first_time_slot' ) {
                update_option( 'orddd_auto_populate_first_available_time_slot', 'on' );
            }
            update_option( 'orddd_update_auto_populate_first_available_time_slot', 'yes' );
        }
    }

    /**
     * Update Weekdays Settings with existing delivery charges
     *
     * @since 8.1
     */

	public static function orddd_update_advance_settings() {
        global $orddd_weekdays;
        if( get_option( 'orddd_update_advance_settings' ) != 'yes' ) {
            $advance_settings = array();
            $i = 0;
            foreach( $orddd_weekdays as $key => $value ) {
                $fee_var = "additional_charges_" . $key;
                $fee_label_var = "delivery_charges_label_" . $key;
                if( '' != get_option( $fee_var ) ) {
                    $charges_array = array(
                        'row_id'       => $i,
                        'additional_charges'  => get_option( $fee_var ),
                        'delivery_charges_label' => get_option( $fee_label_var ),
                        'orddd_weekdays'         => $key,
                        'orddd_disable_same_day_delivery_after_hours' => '',
                        'orddd_disable_next_day_delivery_after_hours' => '',
                        'orddd_minimumOrderDays' => '',
                        'orddd_before_cutoff_weekday'    => '',
                        'orddd_after_cutoff_weekday'    => '',
                    );
                    update_option( 'orddd_enable_day_wise_settings', 'on' );
                    $advance_settings[ $i ]  = $charges_array;
                }
                $i++;
            }
            update_option( 'orddd_advance_settings', $advance_settings );
            update_option( 'orddd_update_advance_settings', 'yes' );
        }
    }

    /**
     * Update default delivery checkout option
     *
     * @since 8.1
     */
    public static function orddd_update_delivery_checkout_options() {
        if( get_option( 'orddd_update_delivery_checkout_options' ) != 'yes' ) {
        	//Global Option
        	update_option( 'orddd_delivery_checkout_options', 'delivery_calendar' );

        	//Custom Delivery Settings options
        	$results = orddd_common::orddd_get_shipping_settings();
            if( is_array( $results ) && count( $results ) > 0 ) {
                foreach ( $results as $key => $value ) {
                    $shipping_settings = get_option( $value->option_name );
                    $shipping_settings[ 'orddd_delivery_checkout_options' ] = 'delivery_calendar';
                    update_option( $value->option_name, $shipping_settings );
                }
            }
        	update_option( 'orddd_update_delivery_checkout_options', 'yes' );
        }
    }

    /**
     * Update type of the holidays. Recurring or for current year. 
     *
     * @since 8.1
     */
    public static function orddd_update_holiday_type() {
	    if( 'yes' != get_option( 'orddd_update_holiday_type' ) ) {
	    	$holidays = get_option( 'orddd_delivery_date_holidays' );
	        $holidays_new_arr = array();

	        if ( $holidays == '' || $holidays == '{}' || $holidays == '[]' || $holidays == 'null' ) {
	            $holidays_arr = array();
	        } else {
	            $holidays_arr = json_decode( $holidays );
	        }

	        foreach ( $holidays_arr as $k => $v ) {
	            $holidays_new_arr[] = array( 'n' => $v->n, 'd' => $v->d, 'r_type' => 'on' );
	        }

	        $results = orddd_common::orddd_get_shipping_settings();
            foreach ( $results as $key => $value ) {
            	$shipping_settings = get_option( $value->option_name );
            	if( isset( $shipping_settings[ 'holidays' ] ) && $shipping_settings[ 'holidays' ] != '' ) {
                    $holiday_settings = explode( ',', $shipping_settings[ 'holidays' ] );
                    $holiday_settings_new_arr = array();
                    foreach( $holiday_settings as $hk => $hv ) {
                    	if( $hv != "" ) {
                    		$holiday_arr = explode( ":", $hv );
                    		if( is_array( $holiday_arr ) && count( $holiday_arr ) < 3 ) {
                    			$holiday_value = str_replace( '}', '', $hv );
	                        	$holiday_value .= ':}';
                    		} else {
                    			$holiday_value = $hv;
                    		}
                    	} else {
                			$holiday_value = $hv;
                		}                    		

                    	$holiday_settings_new_arr[] = $holiday_value;	
                    }

                    $holiday_string =  implode( ',', $holiday_settings_new_arr );
                    if( substr( $holiday_string, -1 ) == '}' ) {
                    	$holiday_string .= ",";
                    }

                    $shipping_settings[ 'holidays' ] = $holiday_string;
                }
                update_option( $value->option_name, $shipping_settings );
            }
	        update_option( 'orddd_update_holiday_type', 'yes' );
	    }
	}

    /**
     * Update Delivery Locations label for the checkout page. 
     *
     * @since 8.4
     */
    public static function orddd_update_location_label() {
         if ( get_option( 'orddd_update_location_label' ) != 'yes' ) {
            update_option( 'orddd_location_field_label', 'Pickup Location' );
            update_option( 'orddd_update_location_label', 'yes' );
        }
    }
}

$orddd_update = new orddd_update();