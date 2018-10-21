<?php
/**
 * General Settings for Order Delivery Date
 *
 * @author Tyche Softwares
 * @package Order-Delivery-Date-Pro-for-WooCommerce/Admin/Settings/General
 * @since 2.8.3
 * @category Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class orddd_date_settings {
    
    /**
     * Callback for adding Date Settings tab settings
     */
    public static function orddd_delivery_date_setting() { }
    
    /**
     * Callback for adding Enable Delivery Date setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_enable_delivery_date_callback( $args ) {
        $enable_delivery_date = "";
        if ( get_option( 'orddd_enable_delivery_date' ) == 'on' ) {
            $enable_delivery_date = "checked";
        }
       
        echo '<input type="checkbox" name="orddd_enable_delivery_date" id="orddd_enable_delivery_date" class="day-checkbox" value="on" ' . $enable_delivery_date . ' />';
        
        $html = '<label for="orddd_enable_delivery_date"> ' . $args[0] . '</label>';
        echo $html;      
    }
    
    /**
     * Callback for delivery checkout option to select Calendar or Text Block
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */

    public static function orddd_delivery_checkout_options_callback( $args ) {
        global $orddd_weekdays;
        $orddd_delivery_checkout_options_delivery_calendar = 'checked';
        $orddd_delivery_checkout_options_text_block = '';
        if ( get_option( 'orddd_delivery_checkout_options' ) == "text_block" ) {
            $orddd_delivery_checkout_options_text_block = "checked";
            $orddd_delivery_checkout_options_delivery_calendar = '';
        } else if ( get_option( 'orddd_delivery_checkout_options' ) == "delivery_calendar" ) {
            $orddd_delivery_checkout_options_delivery_calendar = "checked";
            $orddd_delivery_checkout_options_text_block = '';
        }
        
        ?>
        <p><label><input type="radio" name="orddd_delivery_checkout_options" id="orddd_delivery_checkout_options" value="delivery_calendar"<?php echo $orddd_delivery_checkout_options_delivery_calendar; ?>/><?php _e( 'Calendar', 'order-delivery-date' ) ;?></label>
        <label><input type="radio" name="orddd_delivery_checkout_options" id="orddd_delivery_checkout_options" value="text_block"<?php echo $orddd_delivery_checkout_options_text_block; ?>/><?php _e( 'Text block', 'order-delivery-date' ) ;?></label></p>
        <?php
        $html = '<label for="orddd_delivery_checkout_options"> ' . $args[0] . '</label>';
        echo $html;
        ?>
        <script type='text/javascript'>
            jQuery( document ).ready( function(){
                if ( jQuery( "input[type=radio][id=\"orddd_delivery_checkout_options\"][value=\"delivery_calendar\"]" ).is(":checked") ) {
                    i = 0;
                    jQuery( ".form-table"  ).each( function() {
                        if( i == 0 ) {
                            k = 0;
                            var row = jQuery( this ).find( "tr" );
                            if( row.length == 10 ) {
                                jQuery.each( row , function() {
                                    if( k == 9 ) {
                                        jQuery( this ).fadeOut();
                                    } else {
                                        jQuery( this ).fadeIn();    
                                    }
                                    k++ 
                                });
                            } else {
                                jQuery.each( row , function() {
                                    if( k == 10 ) {
                                        jQuery( this ).fadeOut();
                                    } else {
                                        jQuery( this ).fadeIn();    
                                    }
                                    k++ 
                                });
                            }
                        } else {
                            jQuery( this ).fadeIn();
                        } 
                        i++;
                    }); 
                } else if ( jQuery( "input[type=radio][id=\"orddd_delivery_checkout_options\"][value=\"text_block\"]" ).is(":checked") ) {
                    i = 0;
                    jQuery(".form-table").each( function() {
                        if( i == 0 ) {
                            k = 0;
                            var row = jQuery( this ).find( "tr" );
                            if( row.length == 10 ) {
                                jQuery.each( row , function() {

                                    if( k == 1 || k == 0 || k == 4 ) {
                                        // the field needs to be shown so we do nothing
                                    } else if( k == 9 ) {
                                        jQuery( this ).fadeIn();    
                                    } else {
                                        jQuery( this ).fadeOut();
                                    }
                                    k++ 
                                });
                            } else {
                                jQuery.each( row , function() {

                                    if( k == 1 || k == 0 || k == 5 || k == 2 ) {
                                        // the field needs to be shown so we do nothing
                                    } else if( k == 10 ) {
                                        jQuery( this ).fadeIn();    
                                    } else {
                                        jQuery( this ).fadeOut();
                                    }
                                    k++ 
                                });
                            }
                        }
                        i++;
                    });
                }

                 jQuery( "input[type=radio][id=\"orddd_delivery_checkout_options\"]" ).on( 'change', function() {
                    if ( jQuery( this ).is(':checked') ) {
                        var value = jQuery( this ).val();
                        if( value == 'delivery_calendar' ) {
                            i = 0;
                            jQuery( ".form-table"  ).each( function() {
                                if( i == 0 ) {
                                    k = 0;
                                    var row = jQuery( this ).find( "tr" );
                                    if( row.length == 10 ) {
                                        jQuery.each( row , function() {
                                            if( k == 9 ) {
                                                jQuery( this ).fadeOut();
                                            } else {
                                                jQuery( this ).fadeIn();    
                                            }
                                            k++ 
                                        });
                                    } else {
                                        jQuery.each( row , function() {
                                            if( k == 10 ) {
                                                jQuery( this ).fadeOut();
                                            } else {
                                                jQuery( this ).fadeIn();    
                                            }
                                            k++;
                                        });
                                    }
                                } else {
                                    jQuery( this ).fadeIn();
                                } 
                                i++;
                            }); 
                        } else if( value == 'text_block' ) {
                            i = 0;
                            jQuery(".form-table").each( function() {
                                if( i == 0 ) {
                                    k = 0;
                                    var row = jQuery( this ).find( "tr" );
                                    if( row.length == 10 ) {
                                        jQuery.each( row , function() {

                                            if( k == 1 || k == 0 || k == 4 ) {
                                                // the field needs to be shown so we do nothing
                                            } else if( k == 9 ) {
                                                jQuery( this ).fadeIn();    
                                            } else {
                                                jQuery( this ).fadeOut();
                                            }
                                            k++ 
                                        });
                                    } else {
                                        jQuery.each( row , function() {
                                            if( k == 1 || k == 0 || k == 5 || k == 2) {
                                                // the field needs to be shown so we do nothing
                                            } else if( k == 10 ) {
                                                jQuery( this ).fadeIn();    
                                            } else {
                                                jQuery( this ).fadeOut();
                                            }
                                            k++ 
                                        });
                                    }
                                }
                                i++;
                            });
                        }
                    }
                } );
            });
        </script>
        <?php
    }

    /**
     * Callback to add the Delivery Range field for the text block
     * 
     * @param array @args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    public static function orddd_text_block_between_days_callback( $args ) {
        $orddd_min_between_days = get_option( 'orddd_min_between_days' );
        if( '' == $orddd_min_between_days ) {
            $orddd_min_between_days = 1;
        }

        $orddd_max_between_days = get_option( 'orddd_max_between_days' );
        if( '' == $orddd_max_between_days ) {
            $orddd_max_between_days = 3;
        }
        ?>
        <label for="orddd_text_block_between_days">Between 
            <input id="orddd_min_between_days" name="orddd_min_between_days" type="number" value="<?php echo $orddd_min_between_days ?>" style="width:50px;" min="1" step="1"> 
            and 
            <input id="orddd_max_between_days" name="orddd_max_between_days" type="number" value="<?php echo $orddd_max_between_days ?>" style="width:50px;" min="1" step="1"> 
            days.
        </label>
        <?php
        $html = '<label for="orddd_text_block_between_days"> ' . $args[0] . '</label>';
        echo $html;
    }

    /**
     * Callback for adding Delivery Weekdays setting
     *
     * @param string $input Value of the weekday setting 
     * 
     * @return string $input
     * 
     * @todo Unused Function. Need to check and remove it. 
     * @since 2.8.3
     */
    
    public static function orddd_weekday_0_save( $input ) {
        $input = orddd_date_settings::return_orddd_weekday_input( 'orddd_weekday_0' );
        return $input;
    }

    /**
     * Callback for adding Delivery Weekdays setting
     *
     * @param string $input Value of the weekday setting 
     * 
     * @return string $input
     * 
     * @todo Unused Function. Need to check and remove it. 
     * @since 2.8.3
     */
    public static function orddd_weekday_1_save( $input ) {
        $input = orddd_date_settings::return_orddd_weekday_input( 'orddd_weekday_1' );
        return $input;
    }

    /**
     * Callback for adding Delivery Weekdays setting
     *
     * @param string $input Value of the weekday setting 
     * 
     * @return string $input
     * 
     * @todo Unused Function. Need to check and remove it. 
     * @since 2.8.3
     */

    public static function orddd_weekday_2_save( $input ) {
        $input = orddd_date_settings::return_orddd_weekday_input( 'orddd_weekday_2' );
        return $input;
    }

    /**
     * Callback for adding Delivery Weekdays setting
     *
     * @param string $input Value of the weekday setting 
     * 
     * @return string $input
     * 
     * @todo Unused Function. Need to check and remove it. 
     * @since 2.8.3
     */
    public static function orddd_weekday_3_save( $input ) {
        $input = orddd_date_settings::return_orddd_weekday_input( 'orddd_weekday_3' );
        return $input;
    }

    /**
     * Callback for adding Delivery Weekdays setting
     *
     * @param string $input Value of the weekday setting 
     * 
     * @return string $input
     * 
     * @todo Unused Function. Need to check and remove it. 
     * @since 2.8.3
     */
    public static function orddd_weekday_4_save( $input ) {
        $input = orddd_date_settings::return_orddd_weekday_input( 'orddd_weekday_4' );
        return $input;
    }

    /**
     * Callback for adding Delivery Weekdays setting
     *
     * @param string $input Value of the weekday setting 
     * 
     * @return string $input
     * 
     * @todo Unused Function. Need to check and remove it. 
     * @since 2.8.3
     */
    public static function orddd_weekday_5_save( $input ) {
        $input = orddd_date_settings::return_orddd_weekday_input( 'orddd_weekday_5' );
        return $input;
    }

    /**
     * Callback for adding Delivery Weekdays setting
     *
     * @param string $input Value of the weekday setting 
     * 
     * @return string $input
     * 
     * @todo Unused Function. Need to check and remove it. 
     * @since 2.8.3
     */

    public static function orddd_weekday_6_save( $input ) {
        $input = orddd_date_settings::return_orddd_weekday_input( 'orddd_weekday_6' );
        return $input;
    }
    
    /**
     * Return the selected weekdays
     * 
     * @todo Unused Function. Need to check and remove it. 
     * @param string $weekday 
     * @return string $input 
     * @since 2.8.3
     */
    public static function return_orddd_weekday_input( $weekday ) {
        global $orddd_weekdays;
        $input = '';
        if( isset( $_POST[ 'orddd_weekdays' ] ) ) {
            $weekdays = $_POST[ 'orddd_weekdays' ];
            if( in_array( $weekday, $weekdays ) ) {
                $input = 'checked';
            }
        }
        return $input;
    }

    /**
     * Callback function to select weekdays for deliveries
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    public static function orddd_delivery_days_callback( $args ) {
        global $orddd_weekdays;
        $currency_symbol = get_woocommerce_currency_symbol();
        
        echo '<select class="orddd_weekdays" id="orddd_weekdays" name="orddd_weekdays[]" placeholder="Select Weekdays" multiple="multiple">';
                foreach ( $orddd_weekdays as $n => $day_name ) {
                    if( "checked" == get_option( $n ) ) {
                        print( '<option name="' . $n . '" value="' . $n . '" selected>' .  $day_name . '</option>' );
                    } else {
                        print( '<option name="' . $n . '" value="' . $n . '">' .  $day_name . '</option>' );
                    }
                    
                }
        echo '</select>';
        echo '<script>
            jQuery( ".orddd_weekdays" ).select2();
        </script>';
    
        $html = '<label for="orddd_delivery_days"> ' . $args[ 0 ] . '</label>';
        echo $html;   
    }
    
    /**
     * Callback to add Weekday Settings field
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    public static function orddd_enable_day_wise_settings_callback( $args ) {
        $enable_day_wise_settings = "";
        if ( get_option( 'orddd_enable_day_wise_settings' ) == 'on' ) {
            $enable_day_wise_settings = "checked";
        }
       
        echo '<input type="checkbox" name="orddd_enable_day_wise_settings" id="orddd_enable_day_wise_settings" class="day-checkbox" value="on" ' . $enable_day_wise_settings . ' />';
        
        $html = '<label for="orddd_enable_day_wise_settings"> ' . $args[0] . '</label>';
        echo $html;      
    }

    /**
     * Callback for adding Minimum Delivery Time(in hours) setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_minimum_delivery_time_callback( $args ) {
        echo '<input type="number" min="0" name="orddd_minimumOrderDays" id="orddd_minimumOrderDays" value="' . get_option( 'orddd_minimumOrderDays' ) . '" step="0.25"/>';
        
        $html = '<label for="orddd_minimumOrderDays"> ' . $args[ 0 ] . '</label>';
        echo $html;
    }

    /**
     * Callback for adding Number of dates to choose setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_number_of_dates_callback( $args ) {
        echo '<input type="text" name="orddd_number_of_dates" id="orddd_number_of_dates" value="' . get_option( 'orddd_number_of_dates' ) . '"/>';
        
        $html = '<label for="orddd_number_of_dates"> ' . $args[ 0 ] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for adding Show Delivery Date in customer notification email setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_show_delivery_date_in_customer_notification_email_callback( $args ) {
        $plugin_path = plugins_url();
        $show_delivery_date_in_customer_email = '';
        if ( get_option( 'orddd_show_delivery_date_in_customer_email' ) == 'on' ) {
        	$show_delivery_date_in_customer_email = "checked";
        }
        
        echo '<input type="checkbox" name="orddd_show_delivery_date_in_customer_email" id="orddd_show_delivery_date_in_customer_email" class="day-checkbox" ' . $show_delivery_date_in_customer_email . '/>';
        
        $html = '<label for="orddd_show_delivery_date_in_customer_email"> ' . $args[ 0 ] . '</label>';
        echo $html;
    }

    /**
     * Callback for adding Delivery Date field mandatory setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_date_field_mandatory_callback( $args ) {
        echo '<input type="checkbox" name="orddd_date_field_mandatory" id="orddd_date_field_mandatory" class="day-checkbox" value="checked" ' . get_option( 'orddd_date_field_mandatory' ) . '/>';
        
        $html = '<label for="orddd_date_field_mandatory"> ' . $args[ 0 ] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for adding Lockout date after X orders setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_lockout_date_after_orders_callback( $args ) {
        echo '<input type="text" name="orddd_lockout_date_after_orders" id="orddd_lockout_date_after_orders" value="' . get_option( 'orddd_lockout_date_after_orders' ) . '"/>';
        
        $html = '<label for="orddd_lockout_date_after_orders"> ' . $args[ 0 ] . '</label>';
        echo $html;
    }
    
    /**
     * Callback to add the Maximum Deliveries based on per product quantity setting
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_lockout_date_quantity_based_callback( $args ) {
        $orddd_lockout_date_quantity_based = "";
        if ( get_option( 'orddd_lockout_date_quantity_based' ) == 'on' ) {
            $orddd_lockout_date_quantity_based = "checked";
        }
        
        echo '<input type="checkbox" name="orddd_lockout_date_quantity_based" id="orddd_lockout_date_quantity_based" value="on" ' . $orddd_lockout_date_quantity_based . '/>';
        
        $html = '<label for="orddd_lockout_date_quantity_based"> ' . $args[ 0 ] . '</label>';
        echo $html;
    }
}