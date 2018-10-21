<?php 

/**
 * ORDDD Holiday Settings
 *
 * @author Tyche Softwares
 * @package Order-Delivery-Date-Pro-for-WooCommerce/Admin/Settings/General
 * @since 2.8.4
 * @category Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class orddd_holidays_settings {
	
    /**
     * Callback for adding Holidays tab settings
     */
	public static function orddd_holidays_admin_setting_callback() {}
	
	/**
	 * Callback for adding Holiday name setting
	 *
	 * @param array $args  Extra arguments containing label & class for the field
     * @since 2.8.4
     */
	
	public static function orddd_holidays_name_callback() {
	   echo '<input type="text" name="orddd_holiday_name" id="orddd_holiday_name" class="orddd_holiday_name" ' . stripslashes( get_option( 'orddd_holiday_name' ) ) . '/>';
	}
	
	/**
	 * Callback for adding Holiday start date setting
	 *
	 * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.4
	 */
	
    public static function orddd_holidays_from_date_callback() {
        $current_language = get_option( 'orddd_language_selected' );
        print( '<script type="text/javascript">
			     jQuery( document ).ready( function() {
				    jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "en-GB" ] );
					var formats = [ "mm-dd-yy", "d.m.y", "d M, yy","MM d, yy" ];
					jQuery( "#orddd_holiday_from_date" ).val( "" ).datepicker( {
						constrainInput: true,
						dateFormat: formats[0],
						onSelect: function( selectedDate,inst ) {
                            var monthValue = inst.selectedMonth+1;
						    var dayValue = inst.selectedDay;
						    var yearValue = inst.selectedYear;
                            var current_dt = dayValue + "-" + monthValue + "-" + yearValue;
                            var to_date = jQuery("#orddd_holiday_to_date").val();
                            if ( to_date == "") {    
                                var split = current_dt.split("-");
								split[1] = split[1] - 1;		
								var minDate = new Date(split[2],split[1],split[0]);
                                jQuery("#orddd_holiday_to_date").datepicker("setDate",minDate);
                            }
						}
					} );
				} );
	   </script>' );
         
        echo '<input type="text" name="orddd_holiday_from_date" id="orddd_holiday_from_date" class="orddd_holiday_from_date" ' . get_option( 'orddd_holiday_from_date' ) . '/>';       
    }
    
    /**
     * Callback for adding Holiday end date setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.4
     */
    
    public static function orddd_holidays_to_date_callback( $args ) {
        $current_language = get_option( 'orddd_language_selected' );
        print( '<script type="text/javascript">
			     jQuery( document ).ready( function() {
				    jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "en-GB" ] );
					var formats = [ "mm-dd-yy", "d.m.y", "d M, yy","MM d, yy" ];
					jQuery( "#orddd_holiday_to_date" ).val( "" ).datepicker( {
						constrainInput: true,
						dateFormat: formats[0],
					} );
				} );
        </script>' );
         
        echo '<input type="text" name="orddd_holiday_to_date" id="orddd_holiday_to_date" class="orddd_holiday_to_date" ' . get_option( 'orddd_holiday_to_date' ) . '/>';
        
        $html = '<label for="orddd_holiday_to_date"> ' . $args[0] . '</label>';
        echo $html;
    } 
    
    /**
     * Callback for adding Allow Recurring Holidays settings
     * 
     * @since 8.0
     */

    public static function orddd_allow_recurring_holiday_callback( $args ) { 
        echo '<input type="checkbox" name="orddd_allow_recurring_holiday" id="orddd_allow_recurring_holiday" class="day-checkbox" />';
        
        $html = '<label for="orddd_allow_recurring_holiday"> ' . $args[0] . '</label>';
        echo $html;             
    }

    /**
     * Callback for saving the Holidays
     *
     * @param array $input
     * @since 2.8.4
     */
    
    public static function orddd_delivery_date_holidays_callback( $input ){
        $holidays = get_option( 'orddd_delivery_date_holidays' );
        $holiday_dates_arr = $holidays_new_arr = array();
        
        $orddd_allow_recurring_holiday = '""';
        $holiday_name = '';

        if ( $holidays == '' || $holidays == '{}' || $holidays == '[]' || $holidays == 'null' ) {
            $holidays_arr = array();
        } else {
            $holidays_arr = json_decode( $holidays );
        }

        foreach ( $holidays_arr as $k => $v ) {
            if( isset( $v->r_type ) ) {
                $holidays_new_arr[] = array( 'n' => $v->n, 'd' => $v->d, 'r_type' => $v->r_type );    
            } else {
                $holidays_new_arr[] = array( 'n' => $v->n, 'd' => $v->d, 'r_type' => "" );
            }
            
            $holiday_dates_arr[] = $v->d;
        }

        if( isset( $_POST[ 'orddd_holiday_name' ] ) ) {
            $holiday_name = str_replace( "\'", "'", $_POST[ 'orddd_holiday_name' ] );
            $holiday_name = str_replace( '\"', '"', $holiday_name );
        }

        if( isset( $_POST[ 'orddd_allow_recurring_holiday' ] ) ) {
            $orddd_allow_recurring_holiday = $_POST[ 'orddd_allow_recurring_holiday' ];
        }

        if ( isset( $_POST[ 'orddd_holiday_from_date' ] ) && $_POST[ 'orddd_holiday_from_date' ] != '' && isset( $_POST[ 'orddd_holiday_to_date' ] ) && $_POST[ 'orddd_holiday_to_date' ] != '' ) {
            $date_from_arr = explode( "-", $_POST[ 'orddd_holiday_from_date' ] );
            $date_to_arr = explode( "-", $_POST[ 'orddd_holiday_to_date' ] );
            $tstmp_from = date( 'd-n-Y', gmmktime( 0, 0, 0, $date_from_arr[ 0 ], $date_from_arr[ 1 ], $date_from_arr[ 2 ] ) );
            $tstmp_to = date( 'd-n-Y', gmmktime( 0, 0, 0, $date_to_arr[ 0 ], $date_to_arr[ 1 ], $date_to_arr[ 2 ] ) ); 
            $holiday_dates = orddd_common::orddd_get_betweendays( $tstmp_from, $tstmp_to );
            $holiday_date = '';
            $output = array();
            foreach( $holiday_dates as $k => $v ) {
                $v1 = date( ORDDD_HOLIDAY_DATE_FORMAT, strtotime( $v ) );
                if( !in_array( $v1, $holiday_dates_arr ) ) {
                    $holidays_new_arr[] = array( 'n' => $holiday_name,
                        'd' => $v1, 'r_type' => $orddd_allow_recurring_holiday );
                }
            }
        } 

        $holidays_jarr = json_encode( $holidays_new_arr );
        $output = $holidays_jarr;
        return $output;
    }

    /**
     * Text to display on the Block Time Slots page
     * 
     * @since 2.8.4
     */
    
    public static function orddd_disable_time_slot_callback() {
        echo 'Use this if you want to hide or block a Time Slot temporarily.';
    }

    /**
     * Callback to add setting to block time slots
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.4
     */
        
    public static function orddd_disable_time_slot_for_delivery_days_callback( $args ) {
        global $orddd_weekdays;
        $orddd_disable_time_slot_for_weekdays = '';
        $orddd_disable_time_slot_for_dates = 'checked';
        if ( get_option( 'orddd_disable_time_slot_for_delivery_days' ) == "weekdays" ) {
            $orddd_disable_time_slot_for_weekdays = "checked";
            $orddd_disable_time_slot_for_dates = '';
        } else if ( get_option( 'orddd_disable_time_slot_for_delivery_days' ) == "dates" ) {
            $orddd_disable_time_slot_for_dates = "checked";
            $orddd_disable_time_slot_for_weekdays = '';
        }
        
        ?>
        <p><label><input type="radio" name="orddd_disable_time_slot_for_delivery_days" id="orddd_disable_time_slot_for_delivery_days" value="dates"<?php echo $orddd_disable_time_slot_for_dates; ?>/><?php _e( 'Dates', 'order-delivery-date' ) ;?></label>
        <label><input type="radio" name="orddd_disable_time_slot_for_delivery_days" id="orddd_disable_time_slot_for_delivery_days" value="weekdays"<?php echo $orddd_disable_time_slot_for_weekdays; ?>/><?php _e( 'Weekdays', 'order-delivery-date' ) ;?></label></p>
        <?php
        $html = '<label for="orddd_disable_time_slot_for_delivery_days"> ' . $args[0] . '</label>';
        echo $html;?>
        <script type='text/javascript'>
            jQuery( document ).ready( function(){
            	if ( jQuery( "input[type=radio][id=\"orddd_disable_time_slot_for_delivery_days\"][value=\"weekdays\"]" ).is(":checked") ) {
            		jQuery( '.disable_time_slot_options' ).slideUp();
            		jQuery( '.disable_time_slot_for_weekdays' ).slideDown();
            	} else {
            		jQuery( '.disable_time_slot_options' ).slideDown();
         		    jQuery( '.disable_time_slot_for_weekdays' ).slideUp();
            	}
                jQuery( '.orddd_disable_time_slot_for_weekdays' ).select2();
                jQuery( '.orddd_disable_time_slot_for_weekdays' ).css({'width': '300px' });
                jQuery( "input[type=radio][id=\"orddd_disable_time_slot_for_delivery_days\"]" ).on( 'change', function() {
        			if ( jQuery( this ).is(':checked') ) {
        				var value = jQuery( this ).val();
        				jQuery( '.disable_time_slot_options' ).slideUp();
        				jQuery( '.disable_time_slot_for_' + value ).slideDown();
        			}
        		})
            });
        </script>
        <?php       
    }


    /**
     * Callback to add the setting for disabling time slots for weekdays
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.4
     */
    public static function orddd_disable_time_slot_for_weekdays_callback( $args ) {
        global $orddd_weekdays;
        printf( 
            '<div class="disable_time_slot_options disable_time_slot_for_weekdays">
            <select class="orddd_disable_time_slot_for_weekdays" id="orddd_disable_time_slot_for_weekdays" name="orddd_disable_time_slot_for_weekdays[]" multiple="multiple" placeholder="Select Weekdays">
             <option name="all" value="all">All</option>'
        );
        $weekdays_arr = array();
	    foreach ( $orddd_weekdays as $n => $day_name ) {
            $weekdays[ $n ] = $day_name;
            printf( '<option name="' . $n . '" value="' . $n . '">' .  $weekdays[ $n ] . '</option>' );
	    }
		print( '</select></div>' );
		
		$html = '<label for="orddd_disable_time_slot_for_weekdays"> ' . $args[0] . '</label>';
		
		printf( '<div class="disable_time_slot_options disable_time_slot_for_dates">
            <textarea rows="4" cols="40" name="disable_time_slot_for_dates" id="disable_time_slot_for_dates" placeholder="Select Dates"></textarea>' 
		);
		        
		$delivery_arr = array();
		$current_language = get_option( 'orddd_language_selected' );
		print( '<script type="text/javascript">
            jQuery(document).ready(function() {
                var formats = [ "mm-dd-yy", "d.m.y", "d M, yy","MM d, yy" ];
                jQuery( "#disable_time_slot_for_dates" ).datepick({dateFormat: formats[0], multiSelect: 999, monthsToShow: 1, showTrigger: "#calImg"});
            });
        </script></div>' );
		
		$html = '<label for="orddd_disable_time_slot_for_weekdays"> ' . $args[0] . '</label>';
		echo $html;
    }
    
    /**
     * Callback to add the setting to select time slots to disable
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.4
     */
    public static function orddd_selected_time_slots_to_be_disabled_callback( $args ) {

        printf( '<select class="orddd_selected_time_slots_to_be_disabled" id="orddd_selected_time_slots_to_be_disabled" name="orddd_selected_time_slots_to_be_disabled[]" multiple="multiple" placeholder="Select Time slots">' );
        
        $time_slot_key_arr = orddd_holidays_settings::get_all_timeslots();
        
        if( isset( $time_slot_key_arr ) && is_array( $time_slot_key_arr ) && count( $time_slot_key_arr ) > 0 ) {
            foreach ( $time_slot_key_arr as $ts_key => $ts_value ) {
                echo "<option value='" . $ts_value . "'>" . $ts_value . "</option>\n";
            }
        }
        echo '</select>';
        $html = '<label for="orddd_selected_time_slots_to_be_disabled"> ' . $args[0] . '</label>';
        echo $html;?>
        <script type='text/javascript'>
            jQuery( document ).ready( function(){
            	jQuery( '.orddd_selected_time_slots_to_be_disabled' ).select2();
                jQuery( '.orddd_selected_time_slots_to_be_disabled' ).css({'width': '300px' });
            });
        </script>
        <?php       
    }

    /**
     * Get all the saved time slots
     * 
     * @param string $format_requested
     * @return array
     * @since 2.8.4
     */
    public static function get_all_timeslots( $format_requested = '' ) {
        
        global $orddd_weekdays, $wpdb;
        
        $time_slot_arr = array();
        $time_slot_key_arr = array();
        if( 'on' == get_option( 'orddd_enable_time_slot' ) ) {
            $time_slot_select = get_option( 'orddd_delivery_time_slot_log' );
            
            if ( $time_slot_select != '' && $time_slot_select != '{}' && $time_slot_select != '[]' && $time_slot_select != 'null' ) {
                $time_slot_arr = json_decode( $time_slot_select );
            }
            if ( is_array( $time_slot_arr ) && count( $time_slot_arr ) > 0 ) {
                if( $time_slot_arr == 'null' ) {
                    $time_slot_arr = array();
                }
                foreach ( $time_slot_arr as $k => $v ) {
                    $from_time = $v->fh . ":" . trim( $v->fm );
                    // Send in format as requested
                    if( $format_requested != '' ) {
                        $from_time = date( $format_requested, strtotime( $from_time ) );
                    }
                    if ( $v->th != 00 ){
                        $to_time = $v->th . ":" . trim( $v->tm );
                        
                        if( $format_requested != '' ) {
                            $to_time = date( $format_requested, strtotime( $to_time ) );
                        }
                        
                        $time_slot_key = $from_time . " - " . $to_time;
                    } else {
                        $time_slot_key = $from_time;
                    }
                    $time_slot_key_arr[] = $time_slot_key;
                }
            }
        }

        if( 'on' == get_option( 'orddd_enable_shipping_based_delivery' ) ) {
            $shipping_based_settings_query = "SELECT option_value, option_name FROM `" . $wpdb->prefix . "options` WHERE option_name LIKE 'orddd_shipping_based_settings_%' AND option_name != 'orddd_shipping_based_settings_option_key' ORDER BY option_id DESC";
            $results = $wpdb->get_results( $shipping_based_settings_query );
            
            foreach ( $results as $key => $value ) {
                $shipping_settings = get_option( $value->option_name );
                $time_slots_settings = '';
                if( isset( $shipping_settings[ 'time_slots' ] ) && $shipping_settings[ 'time_slots' ] != '' ) {
                    $timeslot_settings = explode( '},', $shipping_settings[ 'time_slots' ] );
                    $time_slot_str = '';
                    foreach( $timeslot_settings as $hk => $hv ) {
                        $specific_dates = '';
                        if( $hv != '' ) {
                            $time_format = get_option( 'orddd_delivery_time_format' );
                            if( $format_requested != '' ) {
                                $time_format_to_show = $format_requested;
                            } else {
                                if ( $time_format == '1' ) {
                                    $time_format_to_show = 'h:i A';
                                } else {
                                    $time_format_to_show = 'H:i';
                                }
                            }
                            $hv_str = str_replace( '}', '', $hv );
                            $hv_str = str_replace( '{', '', $hv_str );
            
                            $time_slot_charges_lable_str = strrchr( $hv_str, ":" );
                            $time_slot_charges_lable_str_length = strlen( $time_slot_charges_lable_str );
                            $additional_charges_label = substr( $time_slot_charges_lable_str, 1, $time_slot_charges_lable_str_length );
            
                            $time_slot_charges_string = substr( $hv_str, 0, -( $time_slot_charges_lable_str_length ) );
                            $time_slot_charges_str = strrchr( $time_slot_charges_string, ":" );
                            $time_slot_charges_str_length = strlen( $time_slot_charges_str );
                            $additional_charges = substr( $time_slot_charges_str, 1, $time_slot_charges_str_length );
            
                            $lockout_string = substr( $time_slot_charges_string, 0, -( $time_slot_charges_str_length ) );
                            $lockout_str = strrchr( $lockout_string, ":" );
                            $lockout_str_length = strlen( $lockout_str );
                            $lockout = substr( $lockout_str, 1, $lockout_str_length );
            
                            $allpos = array();
                            $offset = 0;
                            $time_slot_str = substr( $lockout_string, 0, -( $lockout_str_length ) );
                            while ( ( $pos = strpos( $time_slot_str, ":", $offset ) ) !== FALSE ) {
                                $offset   = $pos + 1;
                                $allpos[] = $pos;
                            }
                            $time_slot_pos = $allpos[ 1 ];
                            $time_slot = substr( $time_slot_str, ( $time_slot_pos ) + 1 );
            
                            $time_slot_arr = explode( " - ", $time_slot );
                            $from_time = date( $time_format_to_show, strtotime( $time_slot_arr[ 0 ] ) );
                            if( isset( $time_slot_arr[ 1 ] ) ) {
                                $to_time = date( $time_format_to_show, strtotime( $time_slot_arr[ 1 ] ) );
                                $custom_time_slot = $from_time . " - " . $to_time;
                            } else {
                                $custom_time_slot = $from_time;
                            }
                            if ( !in_array( $custom_time_slot, $time_slot_key_arr ) ) {
                                $time_slot_key_arr[] = $custom_time_slot;
                            }
            
                        }
                    }
                }
            }
        }
        
        return $time_slot_key_arr;
    }

    /**
     * Callback to disable the selected time slots
     * 
     * @return string $timeslot_jarr JSON Encoded values for selected time slots 
     * @since 2.8.4
     */
    public static function orddd_disable_time_slots_callback() {
        $disable_timeslot = get_option( 'orddd_disable_time_slot_log' );
        $disable_devel_dates = array();
        
        if( isset( $_POST[ 'orddd_disable_time_slot_for_delivery_days' ] ) ) {
            $disable_time_slot_value = $_POST[ 'orddd_disable_time_slot_for_delivery_days' ];
            if( $disable_time_slot_value == 'weekdays' ) {
                if ( isset( $_POST['orddd_disable_time_slot_for_weekdays'] ) ) {
                    $disable_devel_dates = $_POST['orddd_disable_time_slot_for_weekdays'];
                }
            } else if( $disable_time_slot_value == 'dates' ) {
                if ( isset( $_POST[ 'disable_time_slot_for_dates' ] ) ) {
                    $disable_devel_dates = explode( ",", $_POST[ 'disable_time_slot_for_dates' ] );
                }
            }
        } else {
            $disable_time_slot_value = '';
        }
        
        if( isset( $_POST[ 'orddd_selected_time_slots_to_be_disabled' ] ) ) {
            $selected_time_slot = json_encode( $_POST[ 'orddd_selected_time_slots_to_be_disabled' ] );
        } else {
            $selected_time_slot = '';
        }
        
        $disable_timeslot_new_arr = array();
        if ( $disable_timeslot == 'null' || $disable_timeslot == '' || $disable_timeslot == '{}' || $disable_timeslot == '[]' ) {
            $timeslot_arr = array();
        } else {
            $timeslot_arr = json_decode( $disable_timeslot );
        }
        
        if ( isset( $timeslot_arr ) && is_array( $timeslot_arr ) && count( $timeslot_arr ) > 0 ) {
            foreach ( $timeslot_arr as $k => $v ) {
                $disable_timeslot_new_arr[] = array( 'dtv' => $v->dtv, 'dd' => $v->dd, 'ts' => $v->ts );
            }
        }
        
        if ( is_array( $disable_devel_dates ) && count( $disable_devel_dates ) > 0 && $selected_time_slot != '' ) {
            foreach( $disable_devel_dates as $key => $value ) {
                if( $disable_time_slot_value == 'dates' ) {
                    $disable_date = explode( "-", $value );
                    $delivery_disable_date = date( "n-j-Y", gmmktime( 0, 0, 0, $disable_date[0], $disable_date[1], $disable_date[2] ) );
                } else {
                    $delivery_disable_date = $value;
                }
                $disable_timeslot_new_arr[] = array(
                    'dtv'     => $disable_time_slot_value,
                    'dd'	  => $delivery_disable_date,
                    'ts'      => $selected_time_slot,
                );
            }
        }
        $timeslot_jarr = json_encode( $disable_timeslot_new_arr );
        return $timeslot_jarr;
    }    
}