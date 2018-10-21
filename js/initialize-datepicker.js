/**
 * Allows to initiliaze/load the settings in the calendar.
 *
 * @namespace orddd_initialize
 * @since 1.0
 */
jQuery( document ).ready(function() {    
    //Select Woo class for time alot and pickup locations field. 
    var local_storage_postcode = localStorage.getItem( "orddd_availability_postcode" );
    if( local_storage_postcode != '' && local_storage_postcode != 'undefined' && local_storage_postcode != null ) {
        jQuery( '#billing_postcode' ).val( local_storage_postcode );    
    }
    
    var woo_version = jsL10n.wooVersion;
    if ( woo_version >= '3.2.0' ) {
        if ( jQuery().selectWoo ) {
            jQuery( '#time_slot' ).selectWoo();
            jQuery( '#orddd_locations' ).selectWoo();
        }
    }

    //Hide pickup location field if the shipping method is not selected. 
    var shipping_method = orddd_get_selected_shipping_method();
    if( shipping_method.indexOf( 'local_pickup' ) === -1 ) {
        jQuery( "#orddd_locations_field" ).hide();
        jQuery( "#orddd_locations" ).val( "select_location" ).trigger( "change" );    
    }

    
    jQuery( "#orddd_unique_custom_settings" ).val( "" );

    //Validate the time field if set to mandatory
    parent =  jQuery( '#time_slot' ).closest( '.form-row' );
    validated = true;
    jQuery( 'form.checkout' ).on( 'input validate change','#time_slot', function( e ){
        if ( 'validate' === e.type || 'change' === e.type ) {
            if( jQuery('#time_slot').val() == 'select' && jQuery( '#orddd_timeslot_field_mandatory' ).val() == 'checked' ) {
                parent.removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid woocommerce-invalid-required-field' );
                validated = false;
            }
        }
        
        if( validated ) {
            parent.removeClass( 'woocommerce-invalid woocommerce-invalid-required-field' ).addClass( 'woocommerce-validated' );
        }
    });

    //Clear local storage for the selected delivery date in next 2 hours. 
    var orddd_last_check_date = localStorage.getItem( "orddd_storage_next_time" );
    var current_date = jQuery( "#orddd_current_day" ).val();
    var split_current_date = current_date.split( '-' );
    var ordd_next_date = new Date( split_current_date[ 2 ], ( split_current_date[ 1 ] - 1 ), split_current_date[ 0 ], jQuery( "#orddd_current_hour" ).val(), jQuery( "#orddd_current_minute" ).val() );
    if ( null != orddd_last_check_date ) {
        if ( ordd_next_date.getTime() > orddd_last_check_date ) {
            localStorage.removeItem( "orddd_storage_next_time" );
            localStorage.removeItem( "e_deliverydate_session" );
            localStorage.removeItem( "h_deliverydate_session" );
            localStorage.removeItem( "time_slot" );
            localStorage.removeItem( "orddd_availability_postcode" );
        }
    }

    jQuery(document).on( "ajaxComplete", function( event, xhr, options ) {
        if( options.url.indexOf( "wc-ajax=checkout" ) !== -1 ) {
            if( xhr.statusText != "abort" ) {
                localStorage.removeItem( "orddd_storage_next_time" );
                localStorage.removeItem( "e_deliverydate_session" );
                localStorage.removeItem( "h_deliverydate_session" );
                localStorage.removeItem( "time_slot" );
                localStorage.removeItem( "orddd_availability_postcode" );
            }
        }
    });

    
    var startDaysDisabled = [];

    //Assign options to delivery date on checkout page. 
    var option_str = get_datepicker_options();
    var show = jQuery( "#orddd_show_datepicker" ).val();
    if( show == 'datetimepicker' ) {
        jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).val( "" ).datetimepicker( option_str ).focus( function ( event ) {
            jQuery(this).trigger( "blur" );
            jQuery.datepicker.afterShow( event );
        });    
    } else {
        jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).val( "" ).datepicker( option_str ).focus( function ( event ) {
            jQuery(this).trigger( "blur" );
            jQuery.datepicker.afterShow( event );
        });    
    }
    
    var orddd_available_dates_color = jQuery( "#orddd_available_dates_color" ).val() + '59';
    var orddd_booked_dates_color    = jQuery( "#orddd_booked_dates_color" ).val() + '59';

    jQuery( ".partially-booked" ).children().attr( 'style', 'background: linear-gradient(to bottom right, ' + orddd_booked_dates_color + ' 0%, ' + orddd_booked_dates_color + ' 50%, ' + orddd_available_dates_color + ' 50%, ' + orddd_available_dates_color + ' 100%);' );
    jQuery( ".available-deliveries" ).children().attr( 'style', 'background: ' + orddd_available_dates_color + ' !important;' );

    jQuery( document ).on( "change", "#time_slot", function() {
        var shipping_method = orddd_get_selected_shipping_method();
        jQuery( "#hidden_e_deliverydate" ).val( jQuery( "#e_deliverydate" ).val() );
        jQuery( "#hidden_h_deliverydate" ).val( jQuery( "#h_deliverydate" ).val() );
        jQuery( "#hidden_timeslot" ).val( jQuery(this).find(":selected").val() );
        jQuery( "#hidden_shipping_method" ).val( shipping_method );
        jQuery( "#hidden_shipping_class" ).val( jQuery( "#orddd_shipping_class_settings_to_load" ).val() );

        if( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
            var selected_val = jQuery(this).val();
            jQuery(this).find('option[value="'+ selected_val + '"]').prop( 'selected', true );
            if( jQuery( "#orddd_delivery_date_on_cart_page" ).val() == 'on' ) {
                localStorage.setItem( "e_deliverydate_session", jQuery( "#e_deliverydate" ).val() );
                localStorage.setItem( "h_deliverydate_session", jQuery( "#h_deliverydate" ).val() );
                localStorage.setItem( "time_slot", selected_val );

                var current_date = jQuery( "#orddd_current_day" ).val();
                var split_current_date = current_date.split( '-' );
                var ordd_next_date = new Date( split_current_date[ 2 ], ( split_current_date[ 1 ] - 1 ), split_current_date[ 0 ], jQuery( "#orddd_current_hour" ).val(), jQuery( "#orddd_current_minute" ).val() );

                ordd_next_date.setHours( ordd_next_date.getHours() + 2 );
                localStorage.setItem( "orddd_storage_next_time", ordd_next_date.getTime() );
            }
        }

        jQuery( "body" ).trigger( "update_checkout" );
        if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
            jQuery( "body" ).trigger( "wc_update_cart" );
        }
    });

    if ( jQuery( "#orddd_field_note_text" ).val() != '' ) {
        jQuery( "#e_deliverydate_field" ).append( "<br><small class='orddd_field_note'>" + jQuery( "#orddd_field_note_text" ).val() + "</small>" );
    }

    
    jQuery(document).on( "change", "select[name=\"orddd_locations\"]", function() {
        if ( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == 'on' ) {
            var update_settings = load_delivery_date();
            if( update_settings == 'yes' && jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == 'on' ) {
                orddd_autofil_date_time();
            }
        }
        localStorage.setItem( "orddd_location_session", jQuery(this).val() );
    });
    
       
    jQuery(document).on( "change", "input[name=\"shipping_method[0]\"]", function() {
        var shipping_method = orddd_get_selected_shipping_method();
        var shipping_method_to_check = shipping_method;

        if( shipping_method.indexOf( 'local_pickup' ) === -1 ) {
            jQuery( "#orddd_locations_field" ).hide();
            jQuery( "#orddd_locations" ).val( "select_location" ).trigger( "change" );    
        } else {
            jQuery( "#orddd_locations_field" ).show();    
        }

        if ( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == 'on' && "yes" == jQuery( "#orddd_shipping_method_based_settings" ).val() ) {
            var data = {
                shipping_method: shipping_method_to_check,
                action: "orddd_update_delivery_session"
            };
            
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                var response_arr = response.split( "/" );
                jQuery( "#orddd_common_delivery_days_for_product_category" ).val( response_arr[ 0 ] );
                jQuery( "#orddd_common_delivery_dates_for_product_category" ).val( response_arr[ 1 ] );
                jQuery( "#orddd_holidays_for_product_category" ).val( response_arr[ 2 ] );
                jQuery( "#orddd_common_locked_days" ).val( response_arr[ 3 ] );
                jQuery( "#orddd_is_days_common" ).val( response_arr[ 4 ] ); 
                jQuery( "#orddd_categories_settings_common" ).val( response_arr[ 5 ] );   
                if( typeof response_arr[6] !== 'undefined' ) {
                    var availability = response_arr[6].split( '&' );                    
                    jQuery( "#orddd_partially_booked_dates" ).val( availability[ 0 ] );
                    jQuery( "#orddd_available_deliveries" ).val( availability[ 1 ] );    
                }
                var update_settings = load_delivery_date();
                if( update_settings == 'yes' && jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == 'on' ) {
                    orddd_autofil_date_time();
                }
            });
        }
    });
        
    jQuery(document).on( "change", "select[name=\"shipping_method[0]\"]", function() {
        var shipping_method = orddd_get_selected_shipping_method();
        var shipping_method_to_check = shipping_method;
        
        if( shipping_method.indexOf( 'local_pickup' ) === -1 ) {
            jQuery( "#orddd_locations_field" ).hide();
            jQuery( "#orddd_locations" ).val( "select_location" ).trigger( "change" );    
        } else {
            jQuery( "#orddd_locations_field" ).show();    
        }
        
        if ( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == 'on' && "yes" == jQuery( "#orddd_shipping_method_based_settings" ).val() ) {
            var data = {
                shipping_method: shipping_method_to_check,
                action: "orddd_update_delivery_session"
            };
            
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                var response_arr = response.split( "/" );
                jQuery( "#orddd_common_delivery_days_for_product_category" ).val( response_arr[ 0 ] );
                jQuery( "#orddd_common_delivery_dates_for_product_category" ).val( response_arr[ 1 ] );
                jQuery( "#orddd_holidays_for_product_category" ).val( response_arr[ 2 ] );
                jQuery( "#orddd_common_locked_days" ).val( response_arr[ 3 ] );
                jQuery( "#orddd_is_days_common" ).val( response_arr[ 4 ] ); 
                jQuery( "#orddd_categories_settings_common" ).val( response_arr[ 5 ] ); 
                if( typeof response_arr[6] !== 'undefined' ) {
                    var availability = response_arr[6].split( '&' );
                    jQuery( "#orddd_partially_booked_dates" ).val( availability[ 0 ] );
                    jQuery( "#orddd_available_deliveries" ).val( availability[ 1 ] );    
                }
                var update_settings = load_delivery_date();
                if( update_settings == 'yes' && jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == 'on' ) {
                    orddd_autofil_date_time();
                }
            });
        }
    });

    jQuery(document).on( "change", '#ship-to-different-address input', function() {
        var shipping_method = orddd_get_selected_shipping_method();
        var shipping_method_to_check = shipping_method;

        if( shipping_method.indexOf( 'local_pickup' ) === -1 ) {
            jQuery( "#orddd_locations_field" ).hide();
            jQuery( "#orddd_locations" ).val( "select_location" ).trigger( "change" );    
        } else {
            jQuery( "#orddd_locations_field" ).show();    
        }
        
        if ( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == 'on' && "yes" == jQuery( "#orddd_shipping_method_based_settings" ).val() ) {
            var data = {
                shipping_method: shipping_method_to_check,
                action: "orddd_update_delivery_session"
            };
            
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                var response_arr = response.split( "/" );
                jQuery( "#orddd_common_delivery_days_for_product_category" ).val( response_arr[ 0 ] );
                jQuery( "#orddd_common_delivery_dates_for_product_category" ).val( response_arr[ 1 ] );
                jQuery( "#orddd_holidays_for_product_category" ).val( response_arr[ 2 ] );
                jQuery( "#orddd_common_locked_days" ).val( response_arr[ 3 ] );
                jQuery( "#orddd_is_days_common" ).val( response_arr[ 4 ] ); 
                jQuery( "#orddd_categories_settings_common" ).val( response_arr[ 5 ] );
                if( typeof response_arr[6] !== 'undefined' ) {
                    var availability = response_arr[6].split( '&' );
                    jQuery( "#orddd_partially_booked_dates" ).val( availability[ 0 ] );
                    jQuery( "#orddd_available_deliveries" ).val( availability[ 1 ] );    
                }
                var update_settings = load_delivery_date();
                if( update_settings == 'yes' && jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == 'on' ) {
                    orddd_autofil_date_time();
                }
            });
        }
    });

    if( '1' == jQuery( "#orddd_is_admin" ).val() ) {
        jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).width( "150px" );
        jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).attr( "readonly", true );
    }

    var formats = ["d.m.y", "d MM, yy","MM d, yy"];
    jQuery.extend( jQuery.datepicker, { afterShow: function( event ) {
        jQuery.datepicker._getInst( event.target ).dpDiv.css( "z-index", 9999 );
            if( jQuery( "#orddd_number_of_months" ).val() == "1" && '1' == jQuery( "#orddd_is_admin" ).val() ) {
                jQuery.datepicker._getInst( event.target ).dpDiv.css( "width", "17em" );
            } else if ( jQuery( "#orddd_number_of_months" ).val() == "1" ) {
                jQuery.datepicker._getInst( event.target ).dpDiv.css( "width", "300px" );
            } else {
                jQuery.datepicker._getInst( event.target ).dpDiv.css( "width", "41em" );
            }
        }
    });
    
    jQuery(document).on( 'change', '.address-field input.input-text, .update_totals_on_change input.input-text, .address-field select', function( e ) {
        if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" &&  jQuery( '#orddd_disable_delivery_fields' ).val() == 'yes' ) {
            jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", true );    
            jQuery( "#time_slot" ).attr( "disabled", "disabled" );
        }
    } );

    var old_zone_id = "";
    var old_shipping_method = "";
    jQuery(document).on( "ajaxComplete", function( event, xhr, options ) {
        var new_billing_postcode = jQuery( "#billing_postcode" ).val();
        var new_billing_country = jQuery( "#billing_country" ).val();
        var new_billing_state = jQuery( "#billing_state" ).val();

        var new_shipping_postcode = jQuery( "#shipping_postcode" ).val();
        var new_shipping_country = jQuery( "#shipping_country" ).val();
        var new_shipping_state = jQuery( "#shipping_state" ).val();

        if( options.url.indexOf( "wc-ajax=update_order_review" ) !== -1 ) {
            if( xhr.statusText != "abort" ) {
                var shipping_method = orddd_get_selected_shipping_method();
                if( shipping_method.indexOf( 'local_pickup' ) === -1 ) {
                    jQuery( "#orddd_locations_field" ).hide();
                    jQuery( "#orddd_locations" ).val( "select_location" ).trigger( "change" );    
                } else {
                    jQuery( "#orddd_locations_field" ).show();    
                }

                if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" ) {
                    var is_shipping_checked = jQuery( '#ship-to-different-address input' ).is( ":checked" );
                    var data = {
                        action: 'orddd_get_zone_id',
                        billing_postcode: new_billing_postcode,
                        billing_country: new_billing_country,
                        billing_state: new_billing_state,
                        shipping_postcode: new_shipping_postcode,
                        shipping_country: new_shipping_country,
                        shipping_state: new_shipping_state,
                        shipping_checkbox: is_shipping_checked
                    };
                    if( jQuery( '#orddd_disable_delivery_fields' ).val() == 'yes' ) {
                        if( ( new_billing_postcode != '' && new_billing_country != '' ) && ( false == is_shipping_checked || ( true == is_shipping_checked && '' != new_shipping_country && '' != new_shipping_postcode ) ) ) {    
                            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {    
                                var zone_id = 0;
                                if( "" != response ) {
                                    var zone_shipping_details = response.split('-');
                                    var zone_id = zone_shipping_details[ 0 ];
                                    var orddd_shipping_id = zone_shipping_details[ 1 ];
                                }
                                jQuery( "#orddd_zone_id" ).val( zone_id );
                                jQuery( "#orddd_shipping_id" ).val( orddd_shipping_id );
                                if ( old_zone_id != zone_id || old_shipping_method != orddd_shipping_id ) {
                                    jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", false );
                                    jQuery( "#time_slot" ).removeAttr( "disabled", "disabled" );
                                    load_delivery_date();
                                    if ( jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == "on" ) {
                                        orddd_autofil_date_time();
                                    }
                                    var e_deliverydate_session = localStorage.getItem( 'e_deliverydate_session' );
                                    if( typeof( e_deliverydate_session ) != 'undefined' && e_deliverydate_session != '' ) {
                                        var h_deliverydate_session = localStorage.getItem( 'h_deliverydate_session' );
                                        if ( h_deliverydate_session ) {
                                            var default_date_arr = h_deliverydate_session.split( '-' );
                                            var default_date = new Date( default_date_arr[ 1 ] + '/' + default_date_arr[ 0 ] + '/' + default_date_arr[ 2 ] );
                                            jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).datepicker( "setDate", default_date );
                                            jQuery( "#h_deliverydate" ).val( h_deliverydate_session );
                                            var hourValue = jQuery( ".ui_tpicker_time" ).html();
                                            jQuery( "#orddd_time_settings_selected" ).val( hourValue );
                                            jQuery( "body" ).trigger( "update_checkout" );
                                            if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
                                                jQuery( "body" ).trigger( "wc_update_cart" );
                                            }   
                                            var inst = jQuery.datepicker._getInst( jQuery( "#e_deliverydate" )[0] );
                                            if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" ) {
                                                show_times_custom( h_deliverydate_session, inst );
                                            } else {
                                                show_times( h_deliverydate_session, inst );
                                            }
                                        }
                                    }
                                    old_zone_id = zone_id;
                                    old_shipping_method = orddd_shipping_id; 
                                } else {
                                    jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", false );    
                                    jQuery( "#time_slot" ).removeAttr( "disabled", "disabled" );
                                }
                            });
                        } else {
                            jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", true );    
                            jQuery( "#time_slot" ).removeAttr( "disabled", "disabled" );
                        }
                    } else {
                        if( ( new_billing_postcode != '' && new_billing_country != '' ) ) {
                            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {    
                                var zone_id = 0;
                                if( "" != response ) {
                                    var zone_shipping_details = response.split('-');
                                    var zone_id = zone_shipping_details[ 0 ];
                                    var orddd_shipping_id = zone_shipping_details[ 1 ];
                                }
                                jQuery( "#orddd_zone_id" ).val( zone_id );
                                jQuery( "#orddd_shipping_id" ).val( orddd_shipping_id );
                                if ( old_zone_id != zone_id || old_shipping_method != orddd_shipping_id ) {
                                    jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", false );
                                    jQuery( "#time_slot" ).removeAttr( "disabled", "disabled" );
                                    load_delivery_date();
                                    if ( jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == "on" ) {
                                        orddd_autofil_date_time();
                                    }

                                    var e_deliverydate_session = localStorage.getItem( 'e_deliverydate_session' );
                                    if( typeof( e_deliverydate_session ) != 'undefined' && e_deliverydate_session != '' ) {
                                        var h_deliverydate_session = localStorage.getItem( 'h_deliverydate_session' );
                                        if ( h_deliverydate_session ) {
                                            var default_date_arr = h_deliverydate_session.split( '-' );
                                            var default_date = new Date( default_date_arr[ 1 ] + '/' + default_date_arr[ 0 ] + '/' + default_date_arr[ 2 ] );
                                            jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).datepicker( "setDate", default_date );
                                            jQuery( "#h_deliverydate" ).val( h_deliverydate_session );
                                            var hourValue = jQuery( ".ui_tpicker_time" ).html();
                                            jQuery( "#orddd_time_settings_selected" ).val( hourValue );
                                            jQuery( "body" ).trigger( "update_checkout" );
                                            if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
                                                jQuery( "body" ).trigger( "wc_update_cart" );
                                            }   
                                            var inst = jQuery.datepicker._getInst( jQuery( "#e_deliverydate" )[0] );
                                            if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" ) {
                                                show_times_custom( h_deliverydate_session, inst );
                                            } else {
                                                show_times( h_deliverydate_session, inst );
                                            }
                                        }
                                    }
                                    old_zone_id = zone_id;
                                    old_shipping_method = orddd_shipping_id; 
                                } else {
                                    jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", false );    
                                    jQuery( "#time_slot" ).removeAttr( "disabled", "disabled" );
                                }
                            });
                        }
                        else {
                            jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", false );    
                            jQuery( "#time_slot" ).removeAttr( "disabled", "disabled" );
                        }
                    }
                }
            }
        }
    });
    
    var old_shipping_method = '';
    jQuery( document ).ajaxSuccess(function( event, request, settings ) {
        if( settings.url.indexOf( "/cart" ) !== -1 ) {
            if( request.statusText != "abort" ) {
                if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" ) {
                    var shipping_method = orddd_get_selected_shipping_method();
                    if ( old_shipping_method != shipping_method ) {
                        localStorage.removeItem( "orddd_storage_next_time" );
                        localStorage.removeItem( "e_deliverydate_session" );
                        localStorage.removeItem( "h_deliverydate_session" );
                        localStorage.removeItem( "time_slot" );  
                        load_delivery_date();
                        if ( jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == "on" ) {
                            orddd_autofil_date_time();
                        }
                        old_shipping_method = shipping_method;
                    }
                }
            }
        }   
    });  
              
    if( '1' == jQuery( "#orddd_is_admin" ).val() ) {
        jQuery( "#save_delivery_date" ).click(function() {
        	save_delivery_dates( 'no' );
        }); 

        jQuery( "#save_delivery_date_and_notify" ).click(function() {
        	save_delivery_dates( 'yes' );
        });        
    }

    if( '1' == jQuery( "#orddd_is_account_page" ).val() ) {
        window.onload = orddd_my_account_init;
    } else if( '1' == jQuery( "#orddd_is_admin" ).val() ) {
        window.onload = orddd_init;
    } else {
        window.onload = load_functions;
    }

    jQuery( '#edit_delivery_date' ).on( 'click', function() {
        jQuery( '#orddd_edit_div' ).toggle();
    });
    jQuery( '#cancel_delivery_date' ).on( 'click', function() {
        jQuery( '#orddd_edit_div' ).fadeOut();
    });
    jQuery( '#update_date' ).on( 'click', function() {
        var ordd_date_and_time_validation = "allow";

        var ordd_is_delivery_date_mandatory = jQuery( '#orddd_date_field_mandatory' ).val();
        var ordd_is_delivery_time_mandatory = jQuery( '#orddd_timeslot_field_mandatory' ).val();
        
        var ordd_get_delivery_date = jQuery( '#e_deliverydate' ).val();
        var ordd_get_delivery_time = jQuery( '#time_slot' ).val();

        var ordd_date_label        = jQuery( '#orddd_field_label' ).val();
        var ordd_time_label        = jQuery( '#orddd_timeslot_field_label' ).val();

        var ordd_validation_message = "";
        if ( "checked" == ordd_is_delivery_date_mandatory && "checked" == ordd_is_delivery_time_mandatory ) {
            ordd_validation_message =  ordd_date_label + " is a required field." + ordd_time_label + " is a required field.";
            if ( ordd_get_delivery_date.length == 0 ||  "select" == ordd_get_delivery_time ) {
                ordd_date_and_time_validation = "no";
            }
        }else if ( "checked" == ordd_is_delivery_date_mandatory ) {
            ordd_validation_message = ordd_date_label +" is a required field.";
            if ( ordd_get_delivery_date.length == 0 ) {
                ordd_date_and_time_validation = "no";
            }
        } else if ( "checked" == ordd_is_delivery_time_mandatory ) {
            ordd_validation_message = ordd_time_label + " is a required field.";
            if ( "select" == ordd_get_delivery_time ) {
                ordd_date_and_time_validation = "no";
            }
        }

        if ( "no" == ordd_date_and_time_validation ) {
            jQuery( "#display_update_message" ).css( "color","red" );
            jQuery( "#display_update_message" ).html( ordd_validation_message );
            jQuery( "#display_update_message" ).fadeIn();
            var delay = 2000; 
            setTimeout(function() {
                jQuery( "#display_update_message" ).fadeOut();
            }, delay );
        }

        if ( "allow" == ordd_date_and_time_validation ) {
            var data = {
                order_id: jQuery( "#orddd_my_account_order_id" ).val(),
                e_deliverydate: jQuery( '#e_deliverydate' ).val(),
                h_deliverydate: jQuery( '#h_deliverydate' ).val(),
                shipping_method: jQuery( '#shipping_method' ).val(),
                orddd_category_settings_to_load: jQuery( '#orddd_category_settings_to_load' ).val(),
                time_setting_enable_for_shipping_method: jQuery( '#time_setting_enable_for_shipping_method' ).val(),
                orddd_time_settings_selected: jQuery( '#orddd_time_settings_selected' ).val(),
                time_slot: jQuery( '#time_slot' ).val(),
                is_my_account: jQuery( '#orddd_is_account_page' ).val(),
                action: 'orddd_update_delivery_date'
            };
            jQuery( '#display_update_message' ).html( '<b>Saving...</b>' );
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + 'admin-ajax.php', data, function( response, status ) {
                jQuery( '#display_update_message' ).html( '<b>Successfully edited the delivery date. Please wait until the page reloads.</b>' );
                var delay = 500; //10 second
                setTimeout(function() {
                      location.reload();
                }, delay);
            });
        }
    });
});