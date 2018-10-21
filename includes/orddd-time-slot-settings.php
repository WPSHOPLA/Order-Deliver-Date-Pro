<?php
/**
 * Order Delivery Time Slots Settings
 *
 * @author Tyche Softwares
 * @package Order-Delivery-Date-Pro-for-WooCommerce/Admin/Settings/General
 * @since 2.4
 * @category Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class orddd_time_slot_settings {
    
    /**
     * Callback for adding Time slot tab settings
     */
    public static function orddd_time_slot_admin_settings_callback() { }
    
    /**
     * Callback for adding Enable time slot setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.4
     */
    
    public static function orddd_time_slot_enable_callback( $args ) {
        $enable_time_slot = '';
        if ( get_option( 'orddd_enable_time_slot' ) == 'on' ) {
            $enable_time_slot = 'checked';
        }
        
        echo '<input type="checkbox" name="orddd_enable_time_slot" id="orddd_enable_time_slot" class="day-checkbox" ' . $enable_time_slot . '/>';
        
        $html = '<label for="orddd_enable_time_slot"> ' . $args[0] . '</label>';
        echo $html;
    }
    
    
    /**
     * Callback for adding Time slot field mandatory setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.4
     */
    
    public static function orddd_time_slot_mandatory_callback( $args ) {
        echo '<input type="checkbox" name="orddd_time_slot_mandatory" id="orddd_time_slot_mandatory" class="timeslot-checkbox" value="checked" ' . get_option( 'orddd_time_slot_mandatory' ) . ' />';
        $html = '<label for="orddd_time_slot_mandatory"> ' . $args[0] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for adding As soon as possible option in time slot dropdown on checkout page
     *
     * @param array $args Extra arguments containing label & class for the field.
     * @since 7.9
     */

    public static function orddd_time_slot_asap_callback( $args ) {
        echo '<input type="checkbox" name="orddd_time_slot_asap" id="orddd_time_slot_asap" class="timeslot-checkbox" value="checked" ' . get_option( 'orddd_time_slot_asap' ) . ' />';
        $html = '<label for="orddd_time_slot_asap"> ' . $args[0] . '</label>';
        echo $html;
    }

    /**
     * Callback for adding Global lockout for Time slot setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.4
     */
    
    public static function orddd_global_lockout_time_slots_callback( $args ) {
       
        echo '<input type="text" name="orddd_global_lockout_time_slots" id="orddd_global_lockout_time_slots" value="' . get_option( 'orddd_global_lockout_time_slots' ) . '"/>';
    
        $html = '<label for="orddd_global_lockout_time_slots"> ' . $args[0] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for adding Show first available Time slot setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.4
     */
    
    public static function orddd_show_first_available_time_slot_callback( $args ) {
        $orddd_show_select = "";
        if ( get_option( 'orddd_auto_populate_first_available_time_slot' ) == "on" ) {
            $orddd_show_select = "checked";
        } 
        
        echo "<input type='checkbox' name='orddd_auto_populate_first_available_time_slot' id='orddd_auto_populate_first_available_time_slot' value='on'" . $orddd_show_select . ">";
        
        $html = '<label for="orddd_auto_populate_first_available_time_slot"> ' . $args[0] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for adding Time slot settings Extra arguments containing label & class for the field
     */
    
    public static function orddd_add_time_slot_admin_settings_callback() { }
    
    /**
     * Callback to add time slots for weekday or specific dates
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.4
     */
    public static function orddd_time_slot_for_delivery_days_callback( $args ) {
        global $orddd_weekdays;
        $orddd_time_slot_for_weekdays = 'checked';
        $orddd_time_slot_for_specific_dates = '';
        if ( get_option( 'orddd_time_slot_for_delivery_days' ) == "weekdays" ) {
            $orddd_time_slot_for_weekdays = "checked";
            $orddd_time_slot_for_specific_dates = '';
        } else if ( get_option( 'orddd_time_slot_for_delivery_days' ) == "specific_dates" ) {
            $orddd_time_slot_for_specific_dates = "checked";
            $orddd_time_slot_for_weekdays = '';
        }
        
        ?>
        <p><label><input type="radio" name="orddd_time_slot_for_delivery_days" id="orddd_time_slot_for_delivery_days" value="weekdays"<?php echo $orddd_time_slot_for_weekdays; ?>/><?php _e( 'Weekdays', 'order-delivery-date' ) ;?></label>
        <label><input type="radio" name="orddd_time_slot_for_delivery_days" id="orddd_time_slot_for_delivery_days" value="specific_dates"<?php echo $orddd_time_slot_for_specific_dates; ?>/><?php _e( 'Specific Dates', 'order-delivery-date' ) ;?></label></p>
        <script type="text/javascript" language="javascript">
        <?php 
        if ( get_option( 'orddd_enable_specific_delivery_dates' ) != 'on' ) {
            ?>
		    jQuery( document ).ready( function() {
		    	jQuery( "input[type=radio][id=\"orddd_time_slot_for_delivery_days\"][value=\"specific_dates\"]" ).attr( "disabled", "disabled" );
		    });
            <?php
		} 
		$alldays = array();
		foreach ( $orddd_weekdays as $n => $day_name ) {
		    $alldays[ $n ] = get_option( $n );
		}
		 
		$alldayskeys = array_keys( $alldays );
		$checked = "No";
		foreach( $alldayskeys as $key ) {
		    if ( $alldays[ $key ] == 'checked' ) {
		        $checked = "Yes";
		    }
		}
		if ( $checked == 'No' ) {
		    
		}
		?> </script> <?php
        $html = '<label for="orddd_time_slot_for_delivery_days"> ' . $args[0] . '</label>';
        echo $html;?>
        <script type='text/javascript'>
            jQuery( document ).ready( function(){
            	if ( jQuery( "input[type=radio][id=\"orddd_time_slot_for_delivery_days\"][value=\"weekdays\"]" ).is(":checked") ) {
            		jQuery( '.time_slot_options' ).slideUp();
            		jQuery( '.time_slot_for_weekdays' ).slideDown();
            	} else {
            		jQuery( '.time_slot_options' ).slideDown();
         		    jQuery( '.time_slot_for_weekdays' ).slideUp();
            	}
                jQuery( '.orddd_time_slot_for_weekdays' ).select2();
                jQuery( '.orddd_time_slot_for_weekdays' ).css({'width': '300px' });
                jQuery( "input[type=radio][id=\"orddd_time_slot_for_delivery_days\"]" ).on( 'change', function() {
        			if ( jQuery( this ).is(':checked') ) {
        				var value = jQuery( this ).val();
        				jQuery( '.time_slot_options' ).slideUp();
        				jQuery( '.time_slot_for_' + value ).slideDown();
        			}
        		})
            });
        </script>
        <?php      
    }
    
    /**
     * Callback for adding Weekdays for Time slot setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.4
     */
    
    public static function orddd_time_slot_for_weekdays_callback( $args ) {
        global $orddd_weekdays;
        foreach ( $orddd_weekdays as $n => $day_name ) {
            $alldays[ $n ] = get_option( $n );
        }
        $alldayskeys = array_keys( $alldays );
        $checked = "No";
        foreach( $alldayskeys as $key ) {
            if ( $alldays[ $key ] == 'checked' ) {
                $checked = "Yes";
            }
        }
        
        printf( 
            '<div class="time_slot_options time_slot_for_weekdays">
             <select class="orddd_time_slot_for_weekdays" id="orddd_time_slot_for_weekdays" name="orddd_time_slot_for_weekdays[]" multiple="multiple" placeholder="Select Weekdays">
                <option name="all" value="all">All</option>'
        );
        $weekdays_arr = array();
	    foreach ( $orddd_weekdays as $n => $day_name ) {
            if ( get_option( $n ) == 'checked' ) {
                $weekdays[ $n ] = $day_name;
                printf( '<option name="' . $n . '" value="' . $n . '">' .  $weekdays[ $n ] . '</option>' );
            }
	    }

        if ( $checked == 'No' ) {
            foreach ( $orddd_weekdays as $n => $day_name ) {
                $weekdays[ $n ] = $day_name;
                printf( '<option name="' . $n . '" value="' . $n . '">' .  $weekdays[ $n ] . '</option>' );
            }
        }   
		print( '</select></div>' );
		
		$html = '<label for="orddd_time_slot_for_weekdays"> ' . $args[0] . '</label>';
		//echo $html;
		
		if ( get_option( 'orddd_enable_specific_delivery_dates' ) != 'on' ) {
		?>
		  <script type="text/javascript" language="javascript">
		    jQuery( document ).ready( function() {
		    	  jQuery( '#orddd_select_delivery_dates' ).attr( "disabled", "disabled" );
		    } );
		  </script>
		<?php
		} 
		        
		printf( '<div class="time_slot_options time_slot_for_specific_dates">
            <select class="orddd_time_slot_for_weekdays" id="orddd_select_delivery_dates" name="orddd_select_delivery_dates[]" multiple="multiple" placeholder="Select Specific Delivery Dates" >' 
		);
		        
		$delivery_arr = array();
		$delivery_dates_select = get_option( 'orddd_delivery_dates' );
		if ( $delivery_dates_select != '' && $delivery_dates_select != '{}' && $delivery_dates_select != '[]' && $delivery_dates_select != 'null' ) {
            $delivery_arr = json_decode( $delivery_dates_select );
		}
		foreach( $delivery_arr as $key => $value ) {
            foreach ( $value as $k => $v ) {
                if( $k == 'date' ) {
                    $date = explode( "-", $v );
                    $date_to_display = date( "m-d-Y", gmmktime( 0, 0, 0, $date[0], $date[1], $date[2] ) );
                    $temp_arr[ $k ] = $date_to_display;
                } else { 
                    $temp_arr[ $k ] = $v;
                }
            }
            printf( 
              "<option value=" . $temp_arr[ 'date' ] . ">" . $temp_arr[ 'date' ] . "</option>\n"
            );
		}					
		printf( '</select></div>');
		
		$html = '<label for="orddd_time_slot_for_weekdays"> ' . $args[0] . '</label>';
		echo $html;
    }
    
    /**
     * Callback for adding From hours for Time slot setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.4
     */
    
    public static function orddd_time_from_hours_callback( $args ) {
        echo '<select name="orddd_time_from_hours" id="orddd_time_from_hours" size="1">' ;
        // time options
        $delivery_from_hours = get_option( 'orddd_delivery_from_hours' );
        $delivery_to_hours = get_option( 'orddd_delivery_to_hours' );
        for ( $i = 0 ; $i <= 23 ; $i++ ) {
            printf( "<option value='%s'>%s</option>\n",
                esc_attr( $i ),
                $i
            );
        }
		echo '</select>&nbsp;' . __( 'Hours', 'order-delivery-date' ) . '&nbsp&nbsp&nbsp;<select name="orddd_time_from_minutes" id="orddd_time_from_minutes" size="1">';
		for ( $i = 0 ; $i <= 59 ; $i++ ) {
	   	   if ( $i < 10 ) {
		       $i = '0' . $i;
		   }
		   printf( "<option  value='%s'>%s</option>\n",
			  esc_attr( $i ),
	          $i
		   );
		}
		echo '</select>' . __( 'Minutes', 'order-delivery-date' );
        $html = '<label for="orddd_time_from_hours"> ' . $args[0] . '</label>';
        echo $html;
    }

    /**
     * Callback for adding To hours for Time slot setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.4
     */
    
    public static function orddd_time_to_hours_callback( $args ) {
        echo '<select name="orddd_time_to_hours" id="orddd_time_to_hours" size="1">';
		// time options
		$delivery_from_hours = get_option( 'orddd_delivery_from_hours' );
		$delivery_to_hours = get_option( 'orddd_delivery_to_hours' );
		for ( $i = 0 ; $i <= 23 ; $i++ ) {
            printf( "<option value='%s'>%s</option>\n",
                esc_attr( $i ),
                $i
            );
	    }
		echo '</select>&nbsp;' . __( 'Hours', 'order-delivery-date' ) . '&nbsp&nbsp&nbsp;<select name="orddd_time_to_minutes" id="orddd_time_to_minutes" size="1">';
		for ( $i = 0 ; $i <= 59 ; $i++ ) {
            if ( $i < 10 ) {
                $i = '0' . $i;
            }
            printf( "<option value='%s'>%s</option>\n",
                esc_attr( $i ),
                $i
            );
	    }
	    echo '</select>' . __( 'Minutes', 'order-delivery-date' );
        $html = '<label for="orddd_time_to_hours"> ' . $args[0] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for adding Lockout Time slot after X orders setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.4
     */
    
    public static function orddd_time_slot_lockout_callback( $args ) {
        echo '<input type="text" name="orddd_time_slot_lockout" id="orddd_time_slot_lockout"/>';
        
        $html = '<label for="orddd_time_slot_lockout"> ' . $args[0] . '</label>';
        echo $html;
    }
    
    /**
     * Callback to add additional charges for a time slot 
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.4
     */
    public static function orddd_time_slot_additional_charges_callback( $args ) {
        echo '<input type="text" name="orddd_time_slot_additional_charges" id="orddd_time_slot_additional_charges" placeholder="Charges"/>';
        echo '<input type="text" name="orddd_time_slot_additional_charges_label" id="orddd_time_slot_additional_charges_label" placeholder="Time slot Charges Label" />';
    
        $html = '<label for="orddd_time_slot_additional_charges"> ' . $args[0] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for saving time slots
     * 
     * @return string
     * @since 2.4
     */
    
    public static function orddd_delivery_time_slot_callback() {
        $timeslot = get_option( 'orddd_delivery_time_slot_log' );
        $timeslot_new_arr = array();
        if ( $timeslot == 'null' || $timeslot == '' || $timeslot == '{}' || $timeslot == '[]' ) {
            $timeslot_arr = array();
        } else {
            $timeslot_arr = json_decode( $timeslot );
        }
        
        if ( isset( $timeslot_arr ) && is_array( $timeslot_arr ) && count( $timeslot_arr ) > 0 ) {
            foreach ( $timeslot_arr as $k => $v ) {
                $timeslot_new_arr[] = array( 'tv' => $v->tv, 'dd' => $v->dd, 'lockout' => $v->lockout, 'additional_charges' => $v->additional_charges, 'additional_charges_label'  => $v->additional_charges_label, 'fh' => $v->fh, 'fm' => $v->fm, 'th' => $v->th, 'tm' => $v->tm );
            }
        }
        
        if( ( !isset( $_POST['orddd_time_slot_for_weekdays'] ) && !isset( $_POST[ 'orddd_select_delivery_dates' ] ) ) && isset( $_POST[ 'orddd_time_from_hours' ] ) && $_POST[ 'orddd_time_from_hours' ] != 0 && isset( $_POST[ 'orddd_time_to_hours' ] ) && $_POST[ 'orddd_time_to_hours' ] != 0 ) {
            add_settings_error( 'orddd_delivery_time_slot_log_error', 'time_slot_save_error', 'Please Select Delivery Days/Dates for the Time slot', 'error' );
        } else {
            $devel_dates = '';
            if( isset( $_POST[ 'orddd_time_slot_for_delivery_days' ] ) ) {
                $time_slot_value = $_POST[ 'orddd_time_slot_for_delivery_days' ];
                if( $time_slot_value == 'weekdays' ) {
                    if ( isset( $_POST['orddd_time_slot_for_weekdays'] ) ) {
                        $devel_dates = json_encode( $_POST['orddd_time_slot_for_weekdays'] );
                    }
                } else if( $time_slot_value == 'specific_dates' ) {
                    if ( isset( $_POST[ 'orddd_select_delivery_dates' ] ) ) {
                        $devel_dates_arr = $_POST[ 'orddd_select_delivery_dates' ];
                        $dates_arr = array();
                        foreach( $devel_dates_arr as $key => $value ) {
                            $date = explode( "-", $value );
                            $date_to_store = date( "n-j-Y", gmmktime( 0, 0, 0, $date[0], $date[1], $date[2] ) );
                            $dates_arr[ $key ] = $date_to_store;
                        }
                        $devel_dates = json_encode( $dates_arr );
                    }
                }
            } else {
                $time_slot_value = '';
            }
            
            $from_hour = 0;
            $from_minute = 0;   
            $to_hour = 0;   
            $to_minute = 0;   
            $lockouttime = '';   
            $additional_charges = '';
            $additional_charges_label = '';

            if( isset( $_POST[ 'orddd_time_from_hours' ] ) ) { 
                $from_hour = $_POST[ 'orddd_time_from_hours' ];
            }
            
            if( isset( $_POST[ 'orddd_time_from_minutes' ] ) ) {
                $from_minute = $_POST[ 'orddd_time_from_minutes' ];
            }
            
            if( isset( $_POST[ 'orddd_time_to_hours' ] ) ) {
                $to_hour = $_POST[ 'orddd_time_to_hours' ];
            }
            
            if( isset( $_POST[ 'orddd_time_to_minutes' ] ) ) {
                $to_minute = $_POST[ 'orddd_time_to_minutes' ];
            }
            
            if( isset( $_POST[ 'orddd_time_slot_lockout' ] ) ) { 
                $lockouttime = $_POST[ 'orddd_time_slot_lockout' ];
            }
            
            if( isset( $_POST[ 'orddd_time_slot_additional_charges' ] ) ) {
                $additional_charges = $_POST[ 'orddd_time_slot_additional_charges' ];
            }
            
            if( isset( $_POST[ 'orddd_time_slot_additional_charges_label' ] ) ) {
                $additional_charges_label = $_POST[ 'orddd_time_slot_additional_charges_label' ];
            }
            
            $from_hour_new = date( "G" , gmmktime( $from_hour, $from_minute, 0, date( "m" ), date( "d" ), date( "Y" ) ) );
            $from_minute_new = date( "i " , gmmktime( $from_hour, $from_minute, 0, date( "m" ), date( "d" ), date( "Y" ) ) );
            $to_hour_new = date( "G" , gmmktime( $to_hour, $to_minute, 0, date( "m" ), date( "d" ), date( "Y" ) ) );
            $to_minute_new = date( "i " , gmmktime( $to_hour, $to_minute, 0, date( "m" ), date( "d" ), date( "Y" ) ) );
            
            if ( $from_hour_new != $to_hour_new || $from_minute_new != $to_minute_new ) {
                $timeslot_new_arr[] = array(
                    'tv'      => $time_slot_value,
                    'dd'	  => $devel_dates,
                    'lockout' => $lockouttime,
                    'additional_charges' => $additional_charges,
                    'additional_charges_label'  => $additional_charges_label,
                    'fh'	  => $from_hour_new,
                    'fm'	  => $from_minute_new,
                    'th'	  => $to_hour_new,
                    'tm'	  => $to_minute_new,
                );
            }
            
        }
        $timeslot_jarr = json_encode( $timeslot_new_arr );
        return $timeslot_jarr;
    }
}