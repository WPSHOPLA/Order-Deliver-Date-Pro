/**
 * Functions to initiliaze/load the settings in the calendar.
 *
 * @namespace orddd_initialize_functions
 * @since 8.6
 */


/**
 * Handles the functionality of Delivery fields on My Account page.
 *
 * @function orddd_my_account_init
 * @memberof orddd_initialize_functions
 * @since 5.7
 */
function orddd_my_account_init() {
    if( ( '' != jQuery( '#shipping_method' ).val() || '' != jQuery( '#orddd_location' ).val() ) && 'on' == jQuery( '#orddd_enable_shipping_based_delivery' ).val() ) {
        load_delivery_date();
    }
    var default_date_str = jQuery( "#orddd_my_account_default_date" ).val();
    if( default_date_str != '' ) {
        var default_date_arr = default_date_str.split( '-' );
        var default_date = new Date( default_date_arr[ 1 ] + '/' + default_date_arr[ 0 ] + '/' + default_date_arr[ 2 ] );
        var show = jQuery( "#orddd_show_datepicker" ).val();
        if( 'datetimepicker' == show ) {
            jQuery( '#e_deliverydate' ).datetimepicker( 'setDate', default_date );
        } else {
            jQuery( '#e_deliverydate' ).datepicker( 'setDate', default_date );
        }
        jQuery( '#h_deliverydate' ).val( jQuery( "#orddd_my_account_default_h_date" ).val() );
        var default_date_inst = jQuery.datepicker._getInst( jQuery( '#e_deliverydate' )[0] );;

        if( jQuery( '#orddd_enable_shipping_based_delivery' ).val() == 'on' ) {
            show_times_custom( default_date_str, default_date_inst );
        } else {
            show_times( default_date_str, default_date_inst );
        }
    }
}

/**
 * Adds the Delivery information on Admin order page load.
 *
 * @function orddd_init
 * @memberof orddd_initialize_functions
 * @since 3.2
 */
function orddd_init() {
    if( '' != jQuery( '#shipping_method' ).val() && 'on' == jQuery( '#orddd_enable_shipping_based_delivery' ).val() ) {
        load_delivery_date();
    }
    var default_date_str = jQuery( "#orddd_default_date" ).val();
    if( default_date_str != "" ) {
        var default_date_arr = default_date_str.split( "-" );
        var default_date = new Date( default_date_arr[ 1 ] + "/" + default_date_arr[ 0 ] + "/" + default_date_arr[ 2 ] );
        var show = jQuery( "#orddd_show_datepicker" ).val();
        if( 'datetimepicker' == show ) {
            // get the delivery time
            var default_datetime = jQuery( "#default_date_time" ).val();
            var time = default_datetime.split( ':' );
            // Set the Hours & minutes to be prepopulated in the time slider
            default_date.setHours( time[0] );
			default_date.setMinutes( time[1] );
            jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).datetimepicker( "setDate", default_date );
        } else {
            jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).datepicker( "setDate", default_date );    
        }
        
        jQuery( "#h_deliverydate" ).val( jQuery( "#orddd_default_h_date" ).val() );
        var default_date_inst = jQuery.datepicker._getInst( jQuery( "#e_deliverydate" )[0] );
		if( 'datetimepicker' == show ) {
			default_date_inst.settings.timepicker.hour = parseInt( time[0] );
			default_date_inst.settings.timepicker.minute = parseInt( time[1] );
		}
        show_admin_times( default_date_str, default_date_inst );
    }
    
    if( 'no' == jQuery( "#orddd_delivery_enabled" ).val() && 'on' != jQuery( "#orddd_enable_delivery_date_for_category" ).val() ) {
        jQuery( "#admin_time_slot_field" ).remove();
        jQuery( "#admin_delivery_date_field" ).remove()
        jQuery( "#save_delivery_date_button" ).remove();
        jQuery( "#is_virtual_product" ).html( "Delivery date settings are not enabled for the products." );                    
    }    

}

/**
 * Options for JQuery Datepicker
 *
 * @function get_datepicker_options
 * @memberof orddd_initialize_functions
 * @since 1.0
 */
function get_datepicker_options() {
    var option_str = {}
    
    option_str[ 'beforeShowDay' ] = chd;
    option_str[ 'firstDay' ] = parseInt( jQuery( "#orddd_start_of_week" ).val() );

    if( jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).length == 0 ) {
        if( "on" == jQuery( "#orddd_same_day_delivery" ).val() || "on" == jQuery( "#orddd_next_day_delivery" ).val() ) { 
            var avd_obj                     = maxdt();
        } else {      
            var avd_obj                     = avd();
        }

        option_str[ 'minDate' ]   = avd_obj.minDate;
        option_str[ 'maxDate' ]   = avd_obj.maxDate;
    } else {
        var show = jQuery( "#orddd_show_datepicker" ).val();
        if( show == "datepicker" ){
            option_str[ "showButtonPanel" ] = true; 
            option_str[ "closeText" ] = jsL10n.clearText;
        }

        option_str[ 'onClose' ] = function( dateStr, inst ) {
            if ( dateStr != "" ) {
                var monthValue = inst.selectedMonth+1;
                var dayValue = inst.selectedDay;
                var yearValue = inst.selectedYear;
                var all = dayValue + "-" + monthValue + "-" + yearValue;
                jQuery( "#h_deliverydate" ).val( all );var hourValue = jQuery( ".ui_tpicker_time" ).html();
                jQuery( "#orddd_time_settings_selected" ).val( hourValue );
                var event = arguments.callee.caller.caller.arguments[0];
                // If "Clear" gets clicked, then really clear it
                if( typeof( event ) !== "undefined" ) {
                    if ( jQuery( event.delegateTarget ).hasClass( "ui-datepicker-close" ) ) {
                        jQuery( this ).val( "" ); 
                        jQuery( "#h_deliverydate" ).val( "" );
                        jQuery( "#time_slot" ).prepend( "<option value=\"select\">" + jsL10n.selectText + "</option>" );
                        jQuery( "#time_slot" ).children( "option:not(:first)" ).remove();
                        jQuery( "#time_slot" ).attr( "disabled", "disabled" );
                        if( jQuery( "#orddd_is_cart" ).val() == 1 ) {
                            jQuery( "#time_slot" ).attr( "style", "cursor: not-allowed !important;max-width:300px" );
                        } else {
                            jQuery( "#time_slot" ).attr( "style", "cursor: not-allowed !important" );
                        }
                        jQuery( "#time_slot_field" ).css({ opacity: "0.5" });
                    }
                }
                jQuery( "body" ).trigger( "update_checkout" );
                if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
                    jQuery( "body" ).trigger( "wc_update_cart" );
                }
            }
            jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).blur();
        };

        if ( "1" == jQuery( "#orddd_is_admin" ).val() ) {
            option_str[ 'onSelect' ] = show_admin_times;
        } else {
            if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" ) {
                option_str[ 'onSelect' ] = show_times_custom;    
            } else {
                option_str[ 'onSelect' ] = show_times;    
            }
        }

        var options = jQuery( "#orddd_option_str" ).val();
        var df_arr = options.split( "dateFormat: '" );
        var df_arr2 = df_arr[1].split("'");
        var df_dateformat = df_arr2[0];
        var before_df_arr = df_arr[0].split( ', ' );
        before_df_arr[6] = "dateFormat:'" + df_dateformat + "'";

        jQuery.each( before_df_arr, function( key, value ) {
            if( '' != value && 'undefined' != typeof( value ) ) {
                var split_value = value.split( ":" );
                if( split_value.length != '2' ) {
                    var str = split_value[1] + ":" + split_value[2];
                    option_str[ split_value[0] ] = str.trim().replace( /'/g, "" );
                } else if( 'hourMax' == split_value[0] || 'hourMin' == split_value[0] || 'minuteMin' == split_value[0] || 'stepMinute' == split_value[0] ) {
                    option_str[ split_value[0] ] = parseInt( split_value[1].trim() );  
                } else if( 'beforeShow' == split_value[0] ) {
                    if( "on" == jQuery( "#orddd_same_day_delivery" ).val() || "on" == jQuery( "#orddd_next_day_delivery" ).val() ) { 
                         option_str[ split_value[0] ] = maxdt;      
                    } else {      
                     option_str[ split_value[0] ] = avd;        
                    }  
                } else {
                    option_str[ split_value[0] ] = split_value[1].trim().replace( /'/g, "" );    
                }    
            }
        });
    }

    return option_str;
}

/**
 * Loads the hidden variables. 
 *
 * @function load_hidden_vars
 * @memberof orddd_initialize_functions
 * @since 1.0
 */
function load_hidden_vars( value ) {
    jQuery.each( value, function( pkey, pvalue ) { 
        jQuery( "<input>" ).attr({id: pkey, name: pkey, type: "hidden", value: pvalue }).appendTo( "#orddd_dynamic_hidden_vars" );
    });

    if( jQuery( "#orddd_categories_settings_common" ).val() == 'yes' ) {
        if( typeof( jQuery( "#orddd_common_delivery_dates_for_product_category" ).val() ) !== "undefined" && jQuery( "#orddd_common_delivery_dates_for_product_category" ).val() != '' ) {
            var specific_dates = eval( '[' + jQuery( "#orddd_common_delivery_dates_for_product_category" ).val() + ']' );
            var disabled_common_days = eval( '[' + jQuery( '#orddd_holidays_for_product_category' ).val() + ']' );
            
            var specific_dates_str = "";
            for( j = 0 ; j <= specific_dates.length; j++ ) {
                if( typeof( specific_dates[j] ) != 'undefined' ) {
                    if( jQuery.inArray( specific_dates[j], disabled_common_days ) !== -1 ) {
                        delete specific_dates[j];
                    } else {
                        specific_dates_str += '"' + specific_dates[j] +  '",';
                    }    
                }
            }
            
            specific_dates_str = specific_dates_str.substring( 0, specific_dates_str.length - 1);

            jQuery( "#orddd_dynamic_hidden_vars #orddd_delivery_dates" ).val( specific_dates_str );
            if( typeof jQuery( "#orddd_dynamic_hidden_vars #orddd_specific_delivery_dates" ).val() != 'undefined' ) {
                jQuery( "#orddd_dynamic_hidden_vars #orddd_specific_delivery_dates" ).val( "on" );   
            } else {
                jQuery( "<input>" ).attr({id: "orddd_specific_delivery_dates", name: "orddd_specific_delivery_dates", type: "hidden", value: "on" }).appendTo( "#orddd_dynamic_hidden_vars" );
            }
        } else {
            jQuery( "#orddd_dynamic_hidden_vars #orddd_delivery_dates" ).val( "" );
        }

        var common_delivery_days = [];
        if( typeof( jQuery( "#orddd_common_delivery_days_for_product_category" ).val() ) !== "undefined" && jQuery( "#orddd_common_delivery_days_for_product_category" ).val() != '' ) {
            common_delivery_days = jQuery( "#orddd_common_delivery_days_for_product_category" ).val();
            common_delivery_days = jQuery.parseJSON( common_delivery_days );
        } 
		
		if( common_delivery_days.length == 0 ) {
			if( typeof jQuery( "#orddd_dynamic_hidden_vars #orddd_specific_delivery_dates" ).val() != 'undefined' ) {
                jQuery( "#orddd_dynamic_hidden_vars #orddd_specific_delivery_dates" ).val( "on" );   
            } else {
                jQuery( "<input>" ).attr({id: "orddd_specific_delivery_dates", name: "orddd_specific_delivery_dates", type: "hidden", value: "on" }).appendTo( "#orddd_dynamic_hidden_vars" );
            }
		}
		
        for( i=0; i<7; i++ ) {
            if ( typeof( common_delivery_days[ "orddd_weekday_" + i ] ) !== "undefined" ) {
                jQuery( "#orddd_dynamic_hidden_vars #orddd_weekday_" + i ).val( "checked" ); 
            } else {
                jQuery( "#orddd_dynamic_hidden_vars #orddd_weekday_" + i ).val( "" );
            }    
        }

        if( typeof( jQuery( "#orddd_common_locked_days" ).val() ) !== "undefined" && jQuery( "#orddd_common_locked_days" ).val() != '' ) {
            jQuery( "#orddd_dynamic_hidden_vars #orddd_lockout_days" ).val( jQuery( "#orddd_common_locked_days" ).val() );   
        }
    }
}

/**
 * Loads the removed global weekday hidden fields
 *
 *
 * @function load_weekday_vars
 * @memberof load_delivery_date
 *
 * @since 7.8
 */

 function load_weekday_vars( vars ) {
    jQuery.each( vars, function( pkey, pvalue ) { 
        if( typeof jQuery( "#" + pkey ).val() === 'undefined' && typeof pvalue != "undefined" ) {        
            jQuery( "<input>" ).attr({id: pkey, name: pkey, type: "hidden", value: pvalue }).insertAfter( "#h_deliverydate" );
        }
    });
 }

/**
 * Loads the Custom Date settings on the Delivery Date field.
 *
 * @function load_delivery_date
 * @memberof orddd_initialize_functions
 * @returns {string} update_settings
 * @since 3.0
 */
function load_delivery_date() {
    if( jQuery( "#orddd_delivery_date_on_cart_page" ).val() != 'on' ) {
        localStorage.removeItem( "orddd_storage_next_time" );
        localStorage.removeItem( "e_deliverydate_session" );
        localStorage.removeItem( "h_deliverydate_session" );
        localStorage.removeItem( "time_slot" );  
    }

    var string = "", enable_delivery_date = "";  
    var i = 0;
    var method_found = 0;
    var disabled_days_arr = [];
    
    var shipping_class = jQuery( "#orddd_shipping_class_settings_to_load" ).val();
    shipping_class_arr = shipping_class.split( "," );

    var product_category = jQuery( "#orddd_category_settings_to_load" ).val();
    product_category_arr = product_category.split( "," );

    var shipping_method = orddd_get_selected_shipping_method();
    var shipping_method_to_check = shipping_method;

    if( typeof orddd_lpp_method_func == 'function' ) {
        shipping_method = orddd_lpp_method_func( shipping_method );
    }
    
    var location = jQuery( "select[name=\"orddd_locations\"]" ).find(":selected").val();

    if( typeof location === "undefined" ) {
        var location = jQuery( "#orddd_location" ).val();
    }

    if( typeof location === "undefined" ) {
        var location = "";
    }

    var update_settings = 'no';
    var unique_settings_key_to_check = '';
    var unique_custom_setting = jQuery( "#orddd_unique_custom_settings" ).val();
    var custom_settings_to_load = new Object();

    var hidden_var_obj = jQuery( "#orddd_hidden_vars_str" ).val();
    var html_vars_obj = jQuery.parseJSON( hidden_var_obj );
    if( html_vars_obj == null ) {
        html_vars_obj = {};
    }

    if ( shipping_method != "" || shipping_class != "" || product_category != "" ) {
        // hidden vars
        jQuery.each( html_vars_obj, function( key, value ) {
            if( typeof value.orddd_locations !== "undefined" ) {
                var locations = value.orddd_locations.split( "," );
                if( jQuery.inArray( location, locations ) != -1 ) {                   
                    custom_settings_to_load = value;
                    method_found = 1;
                    unique_settings_key_to_check = location;
                    return false;
                }
            }             
        });

        if( method_found == 0 ) {
            // hidden vars
            jQuery.each( html_vars_obj, function( key, value ) {
                if( typeof value.shipping_methods !== "undefined" ) {
                    var shipping_methods = value.shipping_methods.split( "," );
                    if( jQuery.inArray( shipping_method, shipping_methods ) != -1 ) {                   
                        custom_settings_to_load = value;
                        method_found = 1;
                        unique_settings_key_to_check = shipping_method;
                        return false;
                    }
                }             
            });
        }

        if( method_found == 0 ) {
            jQuery.each( product_category_arr, function( pkey, pvalue ) { 
                jQuery.each( html_vars_obj, function( key, value ) {
                    if( typeof value.product_categories !== "undefined" ) {
                        var shipping_methods_for_categories = value.shipping_methods_for_categories.split( "," );
                        var product_categories = value.product_categories.split( "," );
                        if( jQuery.inArray( pvalue, product_categories ) != -1 && ( jQuery.inArray( shipping_method_to_check, shipping_methods_for_categories ) != -1 ) ) {
                            custom_settings_to_load = value;
                            method_found = 1;
                            unique_settings_key_to_check = pvalue;
                            return false;
                        }
                    } 
                });  
            });
        }    

        if( method_found == 0 ) {
            jQuery.each( product_category_arr, function( pkey, pvalue ) { 
                var category_flag = true;
                jQuery.each( html_vars_obj, function( key, value ) {
                    if( typeof value.product_categories !== "undefined" ) {
                        var product_categories = value.product_categories.split( "," );
                        if( jQuery.inArray( pvalue, product_categories ) != -1 && value.shipping_methods_for_categories.length == "" ) {
                            custom_settings_to_load = value;
                            method_found = 1;
                            unique_settings_key_to_check = pvalue;
                            enable_delivery_date = custom_settings_to_load.enable_delivery_date;
                            if( enable_delivery_date == "" ) {
                                category_flag = false;
                                return category_flag;
                            } 
                            return false;
                        }
                    } 
                });
                return category_flag;  
            });
        }  

        if( method_found == 0 && shipping_class != "" ) {
            // hidden vars
            jQuery.each( shipping_class_arr, function( skey, svalue ) {
                var shipping_class_flag = true; 
                jQuery.each( html_vars_obj, function( key, value ) {
                    if( typeof value.shipping_methods !== "undefined" ) {
                        var shipping_methods = value.shipping_methods.split( "," );
                        if( jQuery.inArray( svalue, shipping_methods ) != -1 ) {
                            custom_settings_to_load = value;
                            method_found = 1;
                            unique_settings_key_to_check = svalue;
                            enable_delivery_date = custom_settings_to_load.enable_delivery_date;
                            if( enable_delivery_date == "" ) {
                                shipping_class_flag = false;
                                return shipping_class_flag;
                            }
                            return false;
                        }
                    } 
                });
                return shipping_class_flag;  
            });
        }  
    }
    
    jQuery( '#orddd_custom_settings_to_load' ).val( JSON.stringify( custom_settings_to_load ) );
    if( jQuery.isEmptyObject( custom_settings_to_load ) == false ) {
        var hidden_obj = custom_settings_to_load.hidden_vars;
        var hidden_vars = jQuery.parseJSON( hidden_obj );
        if( hidden_vars == null ) {
            hidden_vars = [];
        }

        jQuery( "#orddd_dynamic_hidden_vars" ).empty();
        load_hidden_vars( hidden_vars );

        var current_unique_setting_key = custom_settings_to_load.unique_settings_key;
        if( typeof( custom_settings_to_load.unique_settings_key ) !== 'undefined' && unique_custom_setting != current_unique_setting_key[ unique_settings_key_to_check ] ) {
            update_settings = 'yes';
            jQuery( "#orddd_unique_custom_settings" ).val( current_unique_setting_key[ unique_settings_key_to_check ] );
            if( jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).length == 0 ) {
                jQuery( "#orddd_availability_calendar" ).datepicker( "destroy" );
            } else {
                if ( "1" == jQuery( "#orddd_is_admin" ).val() ) {
                    jQuery( "#admin_time_slot_field" ).remove();
                    jQuery( "#admin_delivery_date_field" ).remove();
                } else {
                    jQuery( "#e_deliverydate_field label[ for=\"e_deliverydate\" ] abbr" ).remove();
                    jQuery( "#e_deliverydate_field" ).fadeOut();
                    jQuery( "#time_slot_field" ).fadeOut();
                    jQuery( "#time_slot_field" ).empty();
                }

                jQuery( "#h_deliverydate" ).val( "" );
                jQuery( "#e_deliverydate" ).datepicker( "destroy" );
                if( typeof jQuery.fn.datetimepicker !== "undefined" ) {
                    jQuery( "#e_deliverydate" ).datetimepicker( "destroy" );
                }
                jQuery( "#time_slot_field" ).empty();
                jQuery( ".orddd_text_block" ).hide();
                jQuery( "#orddd_estimated_shipping_date" ).val( "" );
                
                enable_delivery_date = custom_settings_to_load.enable_delivery_date;
                jQuery( "<input>" ).attr({id: "orddd_enable_shipping_delivery_date", name: "orddd_enable_shipping_delivery_date", type: "hidden", value: custom_settings_to_load.enable_delivery_date }).appendTo( "#orddd_dynamic_hidden_vars" );
                
                if( enable_delivery_date == "on" ) {
                    if( 'delivery_calendar' == custom_settings_to_load.orddd_delivery_checkout_options ) {

                        if ( "1" == jQuery( "#orddd_is_admin" ).val() ) {
                            jQuery( "#admin_delivery_fields tr:first" ).before( "<tr id=\"admin_delivery_date_field\" ><td><label class =\"orddd_delivery_date_field_label\"> " + jQuery( "#orddd_field_name_admin" ).val() + "</label></td><td><input type=\"text\" id=\"e_deliverydate\" name=\"e_deliverydate\" class=\"e_deliverydate\" /><input type=\"hidden\" id=\"h_deliverydate\" name=\"h_deliverydate\" /></td></tr>");
                            jQuery( "#admin_delivery_fields tr:first" ).after( "<tr id=\"admin_time_slot_field\"><td>" + jQuery( '#orddd_time_field_name_admin' ).val() + "</td><td><select name=\"time_slot\" id=\"time_slot\" class=\"orddd_custom_time_slot\" disabled=\"disabled\" placeholder=\"\"><option value=\"select\">" + jsL10n.selectText + "</option></select></td></tr>");
                        } else {    
                            jQuery( "#e_deliverydate_field" ).fadeIn();
                            jQuery( "#time_slot_field" ).fadeIn();
                        }
                        
                        if( "1" !=  jQuery( "#orddd_is_admin" ).val() ) {
                            if( '' != custom_settings_to_load.orddd_date_field_label ) {
                                jQuery( "#e_deliverydate_field label[for=\"e_deliverydate\"]" ).html( custom_settings_to_load.orddd_date_field_label );    
                            } else {
                                jQuery( "#e_deliverydate_field label[for=\"e_deliverydate\"]" ).html(  jQuery( "#orddd_field_label" ).val() );
                            }
                            
                            var date_field_mandatory = custom_settings_to_load.date_field_mandatory;
                            if( date_field_mandatory == "checked" ) {
                                jQuery( "#e_deliverydate_field label[for=\"e_deliverydate\"]").append( "<abbr class=\"required\" title=\"required\">*</abbr>" );
                                jQuery( "<input>" ).attr({id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: "checked"}).appendTo( "#orddd_dynamic_hidden_vars" );
                                jQuery( "#e_deliverydate_field" ).attr( "class", "form-row form-row-wide validate-required" );
                            } else {
                                jQuery( "#e_deliverydate_field label[for=\"e_deliverydate\"] abbr" ).remove();
                                jQuery( "<input>" ).attr({id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: ""}).appendTo( "#orddd_dynamic_hidden_vars" );
                                jQuery( "#e_deliverydate_field" ).attr( "class", "form-row form-row-wide" );
                            }
                        } else {
                            var date_field_mandatory = custom_settings_to_load.date_field_mandatory;
                            if( date_field_mandatory == "checked" ) {
                                jQuery( "<input>" ).attr({id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: "checked"}).appendTo( "#orddd_dynamic_hidden_vars" );
                            } else {
                                jQuery( "<input>" ).attr({id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: ""}).appendTo( "#orddd_dynamic_hidden_vars" );
                            }
                        }
                            
                        if ( custom_settings_to_load.time_settings != "" ) {
                            string = custom_settings_to_load.time_settings;
                            jQuery( "<input>" ).attr({id: "time_setting_enable_for_shipping_method", name: "time_setting_enable_for_shipping_method", type: "hidden", value: "on"}).appendTo( "#orddd_dynamic_hidden_vars" );                       
                        } else {
                            string = "off";
                            jQuery( "<input>" ).attr({id: "time_setting_enable_for_shipping_method", name: "time_setting_enable_for_shipping_method", type: "hidden", value: "off"}).appendTo( "#orddd_dynamic_hidden_vars" );
                        }
                        
                        if( "1" !=  jQuery( "#orddd_is_admin" ).val() ) {
                            if ( custom_settings_to_load.time_slots == "on" ) {
                                var time_slot_field_mandatory = custom_settings_to_load.timeslot_field_mandatory;
                                if( '' != custom_settings_to_load.orddd_time_field_label ) {
                                    var orddd_time_field_label = custom_settings_to_load.orddd_time_field_label;
                                } else {
                                    var orddd_time_field_label = jQuery( '#orddd_timeslot_field_label' ).val();
                                }
                                if( time_slot_field_mandatory == "checked" ) {
                                    jQuery( "#time_slot_field" ).append( "<label for=\"time_slot\" class=\"\">" + orddd_time_field_label + "<abbr class=\"required\" title=\"required\">*</abbr></label><select name=\"time_slot\" id=\"time_slot\" class=\"orddd_custom_time_slot_mandatory\" disabled=\"disabled\" placeholder=\"\"><option value=\"select\">" + jsL10n.selectText + "</option></select>" );
                                    jQuery( "<input>" ).attr({id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: "checked"}).appendTo( "#orddd_dynamic_hidden_vars" );
                                    jQuery( "#time_slot_field" ).attr( "class", "form-row form-row-wide validate-required" );
                                    jQuery( "#time_slot_field" ).attr( "style", "opacity: 0.5;" );
                                } else {
                                    jQuery( "#time_slot_field" ).append( "<label for=\"time_slot\" class=\"\">" + orddd_time_field_label + "</label><select name=\"time_slot\" id=\"time_slot\" class=\"orddd_custom_time_slot_mandatory\" disabled=\"disabled\" placeholder=\"\"><option value=\"select\">" + jsL10n.selectText + "</option></select>" );
                                    jQuery( "<input>" ).attr({id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: ""}).appendTo( "#orddd_dynamic_hidden_vars" );
                                    jQuery( "#time_slot_field" ).attr( "class", "form-row form-row-wide" );
                                    jQuery( "#time_slot_field" ).attr( "style", "opacity: 0.5;" );
                                }
                                jQuery("<input>").attr({id: "time_slot_enable_for_shipping_method", name: "time_slot_enable_for_shipping_method", type: "hidden", value: "on"}).appendTo( "#orddd_dynamic_hidden_vars" );
                            } else {
                                jQuery( "#time_slot_field" ).empty();
                                jQuery( "<input>" ).attr({id: "time_slot_enable_for_shipping_method", name: "time_slot_enable_for_shipping_method", type: "hidden", value: "off"}).appendTo( "#orddd_dynamic_hidden_vars" );
                            }
                        } else {
                            if ( custom_settings_to_load.time_slots == "on" ) {
                                var time_slot_field_mandatory = custom_settings_to_load.timeslot_field_mandatory;
                                if( time_slot_field_mandatory == "checked" ) {
                                    jQuery( "<input>" ).attr({id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: "checked"}).appendTo( "#orddd_dynamic_hidden_vars" );
                                } else {
                                    jQuery( "<input>" ).attr({id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: ""}).appendTo( "#orddd_dynamic_hidden_vars" );   
                                }
                                jQuery("<input>").attr({id: "time_slot_enable_for_shipping_method", name: "time_slot_enable_for_shipping_method", type: "hidden", value: "on"}).appendTo( "#orddd_dynamic_hidden_vars" );
                            } else {
                                jQuery( "#admin_time_slot_field" ).remove();
                            }
                        }
                        
                        var specific_dates = jQuery( "#orddd_specific_delivery_dates" ).val();
                        var recurring_weekdays = jQuery( "#orddd_recurring_days" ).val();
                        if( specific_dates == "on" && ( recurring_weekdays == "" || recurring_weekdays == "on" && jQuery( "#orddd_dynamic_hidden_vars #orddd_is_all_weekdays_disabled" ).val() == 'yes' ) )  {                             
                            for( i = 0; i < 7; i++ ) {
                                jQuery( "#orddd_weekday_" + i ).remove();
                            }
                        }
                        jQuery( "#orddd_is_shipping_text_block" ).val( "no" );
                        jQuery( ".orddd_text_block" ).hide();
                        jQuery( "#orddd_estimated_shipping_date" ).val( "" );
                    } else if( 'text_block' == custom_settings_to_load.orddd_delivery_checkout_options ) {
                        jQuery( "#e_deliverydate_field" ).fadeOut();
                        jQuery( "#e_deliverydate" ).val( "" );
                        jQuery( "#h_deliverydate" ).val( "" );
                        jQuery( "#e_deliverydate_field label[for=\"e_deliverydate\"] abbr" ).remove();
                        jQuery( "<input>" ).attr( {id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: ""} ).appendTo( "#orddd_dynamic_hidden_vars" );
                        jQuery( "#time_slot_field" ).fadeOut();
                        jQuery( "<input>" ).attr( {id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: ""} ).appendTo( "#orddd_dynamic_hidden_vars" );
                        jQuery( "<input>" ).attr({id: "time_slot_enable_for_shipping_method", name: "time_slot_enable_for_shipping_method", type: "hidden", value: "off"}).appendTo( "#orddd_dynamic_hidden_vars" );
                        jQuery( "#orddd_is_shipping_text_block" ).val( "yes" );
                        jQuery( ".orddd_text_block" ).show();
                        var shipping_date = orddd_get_text_block_shipping_date( custom_settings_to_load.orddd_minimum_delivery_time );
                        var orddd_between_range = custom_settings_to_load.orddd_min_between_days + "-" + custom_settings_to_load.orddd_max_between_days;
                        jQuery( "#orddd_between_range" ).html( orddd_between_range );
                        jQuery( "#shipping_date" ).html( shipping_date[ 'shipping_date' ] );
                        jQuery( "#orddd_estimated_shipping_date" ).val( shipping_date[ 'hidden_shipping_date' ] );
                    }
                } else {
                    if( "1" !=  jQuery( "#orddd_is_admin" ).val() ) {
                        jQuery( "#e_deliverydate_field" ).fadeOut();
                        jQuery( "#e_deliverydate" ).val( "" );
                        jQuery( "#h_deliverydate" ).val( "" );
                        jQuery( "#e_deliverydate_field label[for=\"e_deliverydate\"] abbr" ).remove();
                        jQuery( "<input>" ).attr( {id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: ""} ).appendTo( "#orddd_dynamic_hidden_vars" );
                        jQuery( "#time_slot_field" ).fadeOut();
                        jQuery( "<input>" ).attr( {id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: ""} ).appendTo( "#orddd_dynamic_hidden_vars" );
                        jQuery( "<input>" ).attr({id: "time_slot_enable_for_shipping_method", name: "time_slot_enable_for_shipping_method", type: "hidden", value: "off"}).appendTo( "#orddd_dynamic_hidden_vars" );
                        jQuery( ".orddd_text_block" ).hide();
                        jQuery( "#orddd_estimated_shipping_date" ).val( "" );
                    } else {
                        jQuery( "#admin_delivery_fields" ).empty();
                        jQuery( "#is_virtual_product" ).html( "Delivery is not available for the shipping method." )
                    }
                }
            }
        } else {
            enable_delivery_date = custom_settings_to_load.enable_delivery_date;
            jQuery( "<input>" ).attr({id: "orddd_enable_shipping_delivery_date", name: "orddd_enable_shipping_delivery_date", type: "hidden", value: custom_settings_to_load.enable_delivery_date }).appendTo( "#orddd_dynamic_hidden_vars" );
            
            if( enable_delivery_date == "on" ) {
                if( 'delivery_calendar' == custom_settings_to_load.orddd_delivery_checkout_options ) {
                    if( "1" !=  jQuery( "#orddd_is_admin" ).val() ) {
                        var date_field_mandatory = custom_settings_to_load.date_field_mandatory;
                        if( date_field_mandatory == "checked" ) {
                            jQuery( "<input>" ).attr({id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: "checked"}).appendTo( "#orddd_dynamic_hidden_vars" );
                        } else {
                            jQuery( "<input>" ).attr({id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: ""}).appendTo( "#orddd_dynamic_hidden_vars" );
                        }
                    } else {
                        var date_field_mandatory = custom_settings_to_load.date_field_mandatory;
                        if( date_field_mandatory == "checked" ) {
                            jQuery( "<input>" ).attr({id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: "checked"}).appendTo( "#orddd_dynamic_hidden_vars" );
                        } else {
                            jQuery( "<input>" ).attr({id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: ""}).appendTo( "#orddd_dynamic_hidden_vars" );
                        }
                    }
                        
                    if ( custom_settings_to_load.time_settings != "" ) {
                        string = custom_settings_to_load.time_settings;
                        jQuery( "<input>" ).attr({id: "time_setting_enable_for_shipping_method", name: "time_setting_enable_for_shipping_method", type: "hidden", value: "on"}).appendTo( "#orddd_dynamic_hidden_vars" );                       
                    } else {
                        string = "off";
                        jQuery( "<input>" ).attr({id: "time_setting_enable_for_shipping_method", name: "time_setting_enable_for_shipping_method", type: "hidden", value: "off"}).appendTo( "#orddd_dynamic_hidden_vars" );
                    }
                    
                    if( "1" !=  jQuery( "#orddd_is_admin" ).val() ) {
                        if ( custom_settings_to_load.time_slots == "on" ) {
                            var time_slot_field_mandatory = custom_settings_to_load.timeslot_field_mandatory;
                            if( time_slot_field_mandatory == "checked" ) {
                                jQuery( "<input>" ).attr({id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: "checked"}).appendTo( "#orddd_dynamic_hidden_vars" );
                            } else {
                                jQuery( "<input>" ).attr({id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: ""}).appendTo( "#orddd_dynamic_hidden_vars" );
                            }
                            jQuery("<input>").attr({id: "time_slot_enable_for_shipping_method", name: "time_slot_enable_for_shipping_method", type: "hidden", value: "on"}).appendTo( "#orddd_dynamic_hidden_vars" );
                        } else {
                            jQuery( "<input>" ).attr({id: "time_slot_enable_for_shipping_method", name: "time_slot_enable_for_shipping_method", type: "hidden", value: "off"}).appendTo( "#orddd_dynamic_hidden_vars" );
                        }
                    } else {
                        if ( custom_settings_to_load.time_slots == "on" ) {
                            var time_slot_field_mandatory = custom_settings_to_load.timeslot_field_mandatory;
                            if( time_slot_field_mandatory == "checked" ) {
                                jQuery( "<input>" ).attr({id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: "checked"}).appendTo( "#orddd_dynamic_hidden_vars" );
                            } else {
                                jQuery( "<input>" ).attr({id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: ""}).appendTo( "#orddd_dynamic_hidden_vars" );   
                            }
                            jQuery("<input>").attr({id: "time_slot_enable_for_shipping_method", name: "time_slot_enable_for_shipping_method", type: "hidden", value: "on"}).appendTo( "#orddd_dynamic_hidden_vars" );
                        }
                    }
                } else if( 'text_block' == custom_settings_to_load.orddd_delivery_checkout_options ) {
                    jQuery( "<input>" ).attr( {id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: ""} ).appendTo( "#orddd_dynamic_hidden_vars" );
                    jQuery( "<input>" ).attr( {id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: ""} ).appendTo( "#orddd_dynamic_hidden_vars" );
                    jQuery( "<input>" ).attr({id: "time_slot_enable_for_shipping_method", name: "time_slot_enable_for_shipping_method", type: "hidden", value: "off"}).appendTo( "#orddd_dynamic_hidden_vars" );
                }
            } else {
                if( "1" !=  jQuery( "#orddd_is_admin" ).val() ) {
                    jQuery( "<input>" ).attr( {id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: ""} ).appendTo( "#orddd_dynamic_hidden_vars" );
                    jQuery( "<input>" ).attr( {id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: ""} ).appendTo( "#orddd_dynamic_hidden_vars" );
                    jQuery( "<input>" ).attr({id: "time_slot_enable_for_shipping_method", name: "time_slot_enable_for_shipping_method", type: "hidden", value: "off"}).appendTo( "#orddd_dynamic_hidden_vars" );
                }
            }
        }
    } else {
        if( unique_custom_setting != "global_settings" ) {
            var enabled_weekdays = jQuery( "#orddd_load_delivery_date_var" ).val();
            var hidden_enabled_weekdays_var = jQuery.parseJSON( enabled_weekdays );
            if( hidden_enabled_weekdays_var == null ) {
                hidden_enabled_weekdays_var = [];
            }
                
            load_weekday_vars( hidden_enabled_weekdays_var );

            update_settings = 'yes';
            jQuery( "#orddd_unique_custom_settings" ).val( "global_settings" );
            if( jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).length == 0 ) {
                jQuery( "#orddd_dynamic_hidden_vars" ).empty();
                jQuery( "#orddd_availability_calendar" ).datepicker( "destroy" );
            } else {
                if ( "1" == jQuery( "#orddd_is_admin" ).val() ) {
                    jQuery( "#admin_time_slot_field" ).remove();
                    jQuery( "#admin_delivery_date_field" ).remove();
                } else {
                    jQuery( "#e_deliverydate_field label[ for=\"e_deliverydate\" ] abbr" ).remove();
                    jQuery( "#e_deliverydate_field" ).fadeOut();
                    jQuery( "#time_slot_field" ).fadeOut();
                    jQuery( "#time_slot_field" ).empty();
                }

                jQuery( "#h_deliverydate" ).val( "" );
                jQuery( "#e_deliverydate" ).datepicker( "destroy" );
                if( typeof jQuery.fn.datetimepicker !== "undefined" ) {
                    jQuery( "#e_deliverydate" ).datetimepicker( "destroy" );
                }
                jQuery( "#e_deliverydate_field label[ for=\"e_deliverydate\" ] abbr" ).remove();
                if( 'delivery_calendar' == jQuery( "#orddd_delivery_checkout_options" ).val() ) {
                    if( "1" !=  jQuery( "#orddd_is_admin" ).val() ) {
                        jQuery( "#time_slot_field" ).empty();
                        jQuery( "#e_deliverydate_field" ).fadeIn();
                        jQuery( "#time_slot_field" ).fadeIn();
                    } else {
                        if( jQuery( "#admin_delivery_date_field" ).length == 0 ) { 
                            jQuery( "#admin_delivery_fields tr:first" ).before( "<tr id=\"admin_delivery_date_field\" ><td><label class =\"orddd_delivery_date_field_label\">" + jQuery( "#orddd_field_name_admin" ).val() + ": </label></td><td><input type=\"text\" id=\"e_deliverydate\" name=\"e_deliverydate\" class=\"e_deliverydate\" /><input type=\"hidden\" id=\"h_deliverydate\" name=\"h_deliverydate\" /></td></tr>");
                        }
                     // Time slot field is not present and the order uses a time slot, then display the field
                        var fixed_time = 'off';
                        if( jQuery( '#orddd_fixed_time' ).length > 0 ) {
                            fixed_time = jQuery( '#orddd_fixed_time' ).val();
                        }
                        if( jQuery( "#admin_time_slot_field" ).length == 0 && fixed_time != 'on' ) { 
                            jQuery( "#admin_delivery_fields tr:first" ).after( "<tr id=\"admin_time_slot_field\"><td>" + jQuery( '#orddd_time_field_name_admin' ).val() + "</td><td><select name=\"time_slot\" id=\"time_slot\" class=\"orddd_custom_time_slot\" disabled=\"disabled\" placeholder=\"\"><option value=\"select\">" + jsL10n.selectText + "</option></select></td></tr>");
                        }
                        if( jQuery( "#save_delivery_date_button" ).length == 0 ) {
                            jQuery( "#admin_delivery_fields tr:second" ).after( "<tr id=\"save_delivery_date_button\"><td><input type=\"button\" value=\"Update\" id=\"save_delivery_date\" class=\"save_button\"></td></tr>" );
                        }
                    }
                    jQuery( "#e_deliverydate" ).val( "" );
                    jQuery( "#orddd_dynamic_hidden_vars" ).empty();

                    jQuery( "#e_deliverydate_field label[for=\"e_deliverydate\"]" ).html( jQuery( "#orddd_field_label" ).val() );
                    var time_slot_enabled = jQuery( '#orddd_enable_time_slot' ).val();
                    if( "1" !=  jQuery( "#orddd_is_admin" ).val() ) {
                        if( jQuery( "#time_slot_field" ).is(":empty") && time_slot_enabled == "on" ) { 
                            var time_slot_field_mandatory = jQuery( '#orddd_timeslot_field_mandatory' ).val();
                            if( time_slot_field_mandatory == "checked" ) {
                                jQuery( "#time_slot_field" ).append( "<label for=\"time_slot\" class=\"\">" + jQuery( '#orddd_timeslot_field_label' ).val() + "<abbr class=\"required\" title=\"required\">*</abbr></label><select name=\"time_slot\" id=\"time_slot\" class=\"orddd_custom_time_slot_mandatory\" disabled=\"disabled\" placeholder=\"\"><option value=\"select\">" + jsL10n.selectText + "</option></select>" );
                                jQuery( "<input>").attr({id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: "checked"}).appendTo( "#orddd_dynamic_hidden_vars" );
                                jQuery( "#time_slot_field" ).attr( "class", "form-row form-row-wide validate-required" );
                                jQuery( "#time_slot_field" ).attr( "style", "opacity: 0.5;" );                               
                            } else {
                                jQuery( "#time_slot_field" ).append( "<label for=\"time_slot\" class=\"\">" + jQuery( '#orddd_timeslot_field_label' ).val() + "</label><select name=\"time_slot\" id=\"time_slot\" class=\"orddd_custom_time_slot_mandatory\" disabled=\"disabled\" placeholder=\"\"><option value=\"select\">" + jsL10n.selectText + "</option></select>" );
                                jQuery( "<input>").attr({id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: ""}).appendTo( "#orddd_dynamic_hidden_vars" );
                                jQuery( "#time_slot_field" ).attr( "class", "form-row form-row-wide" );
                                jQuery( "#time_slot_field" ).attr( "style", "opacity: 0.5;" );
                            }
                        }
                    } else {
                        if( time_slot_enabled != "on" ) {
                            jQuery( "#admin_time_slot_field" ).remove();
                        }
                    }

                    if( "1" !=  jQuery( "#orddd_is_admin" ).val() ) {
                        var date_field_mandatory = jQuery( '#orddd_date_field_mandatory' ).val();
                        if( date_field_mandatory == "checked" ) {
                            jQuery( "#e_deliverydate_field label[ for = \"e_deliverydate\" ]" ).append( "<abbr class=\"required\" title=\"required\">*</abbr>" );
                            jQuery( "#e_deliverydate_field" ).attr( "class", "form-row form-row-wide validate-required" );
                        } else {
                            jQuery( "#e_deliverydate_field" ).attr( "class", "form-row form-row-wide" );
                        }
                    }
                    jQuery( ".orddd_text_block" ).hide();
                    jQuery( "#orddd_estimated_shipping_date" ).val( "" );
                    jQuery( "#orddd_is_shipping_text_block" ).val( "no" );
                } else if ( 'text_block' == jQuery( "#orddd_delivery_checkout_options" ).val() ) {
                    jQuery( "#e_deliverydate_field" ).fadeOut();
                    jQuery( "#e_deliverydate" ).val( "" );
                    jQuery( "#h_deliverydate" ).val( "" );
                    jQuery( "#e_deliverydate_field label[for=\"e_deliverydate\"] abbr" ).remove();
                    jQuery( "<input>" ).attr( {id: "date_mandatory_for_shipping_method", name: "date_mandatory_for_shipping_method", type: "hidden", value: ""} ).appendTo( "#orddd_dynamic_hidden_vars" );
                    jQuery( "#time_slot_field" ).fadeOut();
                    jQuery( "<input>" ).attr( {id: "time_slot_mandatory_for_shipping_method", name: "time_slot_mandatory_for_shipping_method", type: "hidden", value: ""} ).appendTo( "#orddd_dynamic_hidden_vars" );
                    jQuery( "<input>" ).attr({id: "time_slot_enable_for_shipping_method", name: "time_slot_enable_for_shipping_method", type: "hidden", value: "off"}).appendTo( "#orddd_dynamic_hidden_vars" );
                    jQuery( "#orddd_is_shipping_text_block" ).val( "yes" );
                    jQuery( ".orddd_text_block" ).show();
                    var shipping_date = orddd_get_text_block_shipping_date( jQuery( "#orddd_minimum_delivery_time" ).val() );
                    var orddd_between_range = jQuery( "#orddd_min_between_days" ).val() + "-" + jQuery( "#orddd_max_between_days" ).val();
                    jQuery( "#orddd_between_range" ).html( orddd_between_range );
                    jQuery( "#shipping_date" ).html( shipping_date[ 'shipping_date' ] );
                    jQuery( "#orddd_estimated_shipping_date" ).val( shipping_date[ 'hidden_shipping_date' ] );
                }
            }
        }
    }

    if( 'yes' == update_settings ) {
        if( jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).length == 0 ) {
            var a = { firstDay: parseInt( jQuery( "#orddd_start_of_week" ).val() ), beforeShowDay: chd };
                        
            if( jQuery( "#orddd_custom_based_same_day_delivery" ).val() == "on" || jQuery( "#orddd_custom_based_next_day_delivery" ).val() == "on" ) {
                var b = maxdt();
            } else if( jQuery( "#orddd_custom_based_same_day_delivery" ).val() == "" && jQuery( "#orddd_custom_based_next_day_delivery" ).val() == "" ) {
                var b = avd()
            } else if( jQuery( "#orddd_same_day_delivery" ).val() == "on" || jQuery( "#orddd_next_day_delivery" ).val() == "on" ) {
                var b = maxdt();
            } else {
                var b = avd();
            }

            var c = { minDate: b.minDate, maxDate: b.maxDate };

            var option_str = {};
            option_str = jsonConcat( option_str, a );
            option_str = jsonConcat( option_str, c );

            jQuery( "#orddd_availability_calendar" ).datepicker( option_str );
            jQuery( '.undefined' ).addClass( "ui-datepicker-unselectable" );
            jQuery( '.ui-state-default' ).replaceWith(function(){
                return jQuery( "<span class='ui-state-default'/>" ).append( jQuery(this).contents());
            });
        } else {
            var date_format = jQuery( '#orddd_delivery_date_format' ).val();
            var a = { firstDay: parseInt( jQuery( "#orddd_start_of_week" ).val() ), beforeShowDay: chd, dateFormat: date_format,
                onClose:function( dateStr, inst ) {
                if ( dateStr != "" ) {
                    var monthValue = inst.selectedMonth+1;
                    var dayValue = inst.selectedDay;
                    var yearValue = inst.selectedYear;
                    var all = dayValue + "-" + monthValue + "-" + yearValue;
                    jQuery( "#h_deliverydate" ).val( all );var hourValue = jQuery( ".ui_tpicker_time" ).html();
                    jQuery( "#orddd_time_settings_selected" ).val( hourValue );
                    var event = arguments.callee.caller.caller.arguments[0];
                    // If "Clear" gets clicked, then really clear it
                    if( typeof( event ) !== "undefined" ) {
                        if ( jQuery( event.delegateTarget ).hasClass( "ui-datepicker-close" )) {
                            jQuery( this ).val(""); 
                            jQuery( "#h_deliverydate" ).val( "" );
                            jQuery( "#time_slot" ).prepend( "<option value=\"select\">" + jsL10n.selectText + "</option>" );
                            jQuery( "#time_slot" ).children( "option:not(:first)" ).remove();
                            jQuery( "#time_slot" ).attr( "disabled", "disabled" );
                            if( jQuery( "#orddd_is_cart" ).val() == 1 ) {
                                jQuery( "#time_slot" ).attr( "style", "cursor: not-allowed !important;max-width:300px" );
                            } else {
                                jQuery( "#time_slot" ).attr( "style", "cursor: not-allowed !important" );
                            }
                            jQuery( "#time_slot_field" ).css({ opacity: "0.5" });
                        }
                    }
                    jQuery( "body" ).trigger( "update_checkout" );
                    if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
                        jQuery( "#hidden_e_deliverydate" ).val( jQuery( "#e_deliverydate" ).val() );
                        jQuery( "#hidden_h_deliverydate" ).val( all );
                        jQuery( "#hidden_timeslot" ).val( jQuery( "#time_slot" ).find(":selected").val() );
                        jQuery( "#hidden_shipping_method" ).val( shipping_method );
                        jQuery( "#hidden_shipping_class" ).val( jQuery( "#orddd_shipping_class_settings_to_load" ).val() );
                        jQuery( "body" ).trigger( "wc_update_cart" );
                    }
                }
                jQuery( "#e_deliverydate" ).blur();
            },
            onSelect: show_times_custom }; 

            if( jQuery( "#orddd_custom_based_same_day_delivery" ).val() == "on" || jQuery( "#orddd_custom_based_next_day_delivery" ).val() == "on" ) {
                var b = { beforeShow: maxdt };
            } else if( jQuery( "#orddd_custom_based_same_day_delivery" ).val() == "" && jQuery( "#orddd_custom_based_next_day_delivery" ).val() == "" ) {
                var b = { beforeShow: avd };
            } else if( jQuery( "#orddd_same_day_delivery" ).val() == "on" || jQuery( "#orddd_next_day_delivery" ).val() == "on" ) {
                var b = { beforeShow: maxdt };
            } else {
                var b = { beforeShow: avd };
            }
            var time_settings_enabled = jQuery( '#orddd_enable_time_slider' ).val();
            if ( string != "" && string != "off" ) {
                var clear_button_text = {};
            } else if ( string == "" && time_settings_enabled == "on" ) {
                var clear_button_text = {};
            } else {
                var clear_button_text = {showButtonPanel: true, closeText: jsL10n.clearText };
            }
            var option_str = {};
            option_str = jsonConcat( option_str, a );
            option_str = jsonConcat( option_str, b );
            option_str = jsonConcat( option_str, clear_button_text );
            if ( string != "" && string != "off" ) {
                var c = jQuery.parseJSON( string );                    
                var hour_min = parseInt( c.hourMin );
                var hour_max = parseInt( c.hourMax );
                var minute_min = parseInt( c.minuteMin );
                var step_minute = parseInt( c.stepMinute );
                var time_format = ( c.timeFormat );
                option_str = jsonConcat( option_str, { hourMin: hour_min, minuteMin: minute_min, hourMax: hour_max, stepMinute: step_minute, timeFormat: time_format } );
                jQuery( "#e_deliverydate" ).val( "" ).datetimepicker( option_str ).focus( function ( event ) {
                    jQuery(this).trigger( "blur" );
                    jQuery.datepicker.afterShow( event );
                });
            } else if ( string == "" && time_settings_enabled == "on" ) {
                var options = jQuery( "#orddd_option_str" ).val();
                var df_arr = options.split("dateFormat: '");
                var df_arr2 = df_arr[1].split("'");
                var df_dateformat = df_arr2[0];
                var before_df_arr = df_arr[0].split( ', ' );
                before_df_arr[6] = "dateFormat:'" + df_dateformat + "'";
                var c = {};
                jQuery.each( before_df_arr, function( key, value ) {
                    if( '' != value && 'undefined' != typeof( value ) ) {
                        var split_value = value.split( ":" );
                        if( split_value.length != '2' ) {
                            var str = split_value[1] + ":" + split_value[2];
                            c[ split_value[0] ] = str.trim().replace( /'/g, "" );
                        } else if( 'hourMax' == split_value[0] || 'hourMin' == split_value[0] || 'minuteMin' == split_value[0] || 'stepMinute' == split_value[0] ) {
                            c[ split_value[0] ] = parseInt( split_value[1].trim() );  
                        } else if( 'beforeShow' == split_value[0] ) {
                            if( "on" == jQuery( "#orddd_same_day_delivery" ).val() || "on" == jQuery( "#orddd_next_day_delivery" ).val() ) {
                                c[ split_value[0] ] = maxdt;
                            } else {
                                c[ split_value[0] ] = avd;
                            }  
                        } else {
                            c[ split_value[0] ] = split_value[1].trim().replace( /'/g, "" );    
                        }    
                    }
                });
                option_str = jsonConcat( option_str, c );
                jQuery( "#e_deliverydate" ).val( "" ).datetimepicker( option_str ).focus( function ( event ) {
                    jQuery(this).trigger( "blur" );
                    jQuery.datepicker.afterShow( event );
                });
            } else if ( string == "" && time_settings_enabled != "on" ) {
                jQuery( "#e_deliverydate" ).val( "" ).datepicker( option_str ).focus( function ( event ) {
                    jQuery(this).trigger( "blur" );
                    jQuery.datepicker.afterShow( event );
                });
            } else {
                jQuery( "#e_deliverydate" ).val( "" ).datepicker( option_str ).focus( function ( event ) {
                    jQuery(this).trigger( "blur" );
                    jQuery.datepicker.afterShow( event );
                });
            }
        }
        
        var orddd_available_dates_color = jQuery( "#orddd_available_dates_color" ).val() + '59';
        var orddd_booked_dates_color    = jQuery( "#orddd_booked_dates_color" ).val() + '59';

        jQuery( ".partially-booked" ).children().attr( 'style', 'background: linear-gradient(to bottom right, ' + orddd_booked_dates_color + ' 0%, ' + orddd_booked_dates_color + ' 50%, ' + orddd_available_dates_color + ' 50%, ' + orddd_available_dates_color + ' 100%);' );
        jQuery( ".available-deliveries" ).children().attr( 'style', 'background: ' + orddd_available_dates_color + ' !important;' );
        
    }
    return update_settings; 
}

/**
 * Returns the Text block information for the shipping method.
 *
 * @function orddd_get_text_block_shipping_date
 * @memberof orddd_initialize_functions
 * @param {timestamp} delivery_time_seconds - Minimum Delivery time in seconds
 * @returns {array} shipping_info - Shipping information
 * @since 6.7
 */
function orddd_get_text_block_shipping_date( delivery_time_seconds ) {
    var shipping_date = '';
    var date_format = jQuery( '#orddd_delivery_date_format' ).val();
    var js_date_format = get_js_date_formats( date_format );

    var current_date = jQuery( "#orddd_current_day" ).val();
    var split_current_date = current_date.split( '-' );
    
    var current_day = new Date( split_current_date[ 2 ], ( split_current_date[ 1 ] - 1 ), split_current_date[ 0 ], jQuery( "#orddd_current_hour" ).val(), jQuery( "#orddd_current_minute" ).val() );
    var current_time = current_day.getTime();
    var current_weekday = current_day.getDay();

    var shipping_info = [];
    if( delivery_time_seconds != 0 && delivery_time_seconds != '' ) {
        var cut_off_timestamp = current_time + parseInt( delivery_time_seconds * 60 * 60 * 1000 );
        var cut_off_date = new Date( cut_off_timestamp );
        var cut_off_weekday = cut_off_date.getDay();

        if( 'on' == jQuery( '#orddd_enable_shipping_days' ).val() ) {
            for( i = current_weekday; current_time <= cut_off_timestamp; i++ ) {
                if( i >= 0 ) {
                    var shipping_day = 'orddd_shipping_day_' + current_weekday;
                    var shipping_day_check = jQuery( "#" + shipping_day ).val();
                    if ( shipping_day_check == '' ) {
                        current_day.setDate( current_day.getDate()+1 );
                        current_weekday = current_day.getDay();
                        current_time = current_day.getTime();
                        cut_off_date.setDate( cut_off_date.getDate()+1 );
                        cut_off_timestamp = cut_off_date.getTime();
                    } else {
                        if( current_time <= cut_off_timestamp ) {
                            current_day.setDate( current_day.getDate()+1 );
                            current_weekday = current_day.getDay();
                            current_time = current_day.getTime();
                        }
                    }
                } else {
                    break;
                }
            }
        }
        shipping_info[ 'shipping_date' ] = moment( cut_off_date ).format( js_date_format ) ;     
        shipping_info[ 'hidden_shipping_date' ] = moment( cut_off_date ).format( 'D-M-YYYY' ) ;     
    } else {
        shipping_info[ 'shipping_date' ] = moment( current_day ).format( js_date_format ) ;    
        shipping_info[ 'hidden_shipping_date' ] = moment( current_day ).format( 'D-M-YYYY' ) ;     
    }

    return shipping_info;
}

/**
 * Returns the date format in JS date format.
 *
 * @function get_js_date_formats
 * @memberof orddd_initialize_functions
 * @param {string} date_format - Date format
 * @returns {string} year_str - JS date format
 * @since 6.7
 */
function get_js_date_formats( date_format ) {
    var date_str = '';
    var month_str = '';
    var year_str = '';
    var day_str = '';
    switch( date_format ) {
        case "mm/dd/y":
            date_str = date_format.replace( new RegExp("\\bdd\\b"), 'DD' );
            month_str = date_str.replace( new RegExp("\\bmm\\b"), 'MM' );
            year_str = month_str.replace( new RegExp("\\by\\b"), 'YY' );
            break;
        case "dd/mm/y": 
            date_str = date_format.replace( new RegExp("\\bdd\\b"), 'DD' );
            month_str = date_str.replace( new RegExp("\\bmm\\b"), 'MM' );
            year_str = month_str.replace( new RegExp("\\by\\b"), 'YY' );
            break;
        case "y/mm/dd":
            date_str = date_format.replace( new RegExp("\\bdd\\b"), 'DD' );
            month_str = date_str.replace( new RegExp("\\bmm\\b"), 'MM' );
            year_str = month_str.replace( new RegExp("\\by\\b"), 'YY' );
            break;
        case "mm/dd/y, D":
            day_str = date_format.replace( new RegExp("\\bD\\b"), 'ddd' );
            date_str = day_str.replace( new RegExp("\\bdd\\b"), 'DD' );
            month_str = date_str.replace( new RegExp("\\bmm\\b"), 'MM' );
            year_str = month_str.replace( new RegExp("\\by\\b"), 'YY' );
            break;
        case "dd.mm.y":
            date_str = date_format.replace( new RegExp("\\bdd\\b"), 'DD' );
            month_str = date_str.replace( new RegExp("\\bmm\\b"), 'MM' );
            year_str = month_str.replace( new RegExp("\\by\\b"), 'YY' );
            break;
        case "y.mm.dd":
            date_str = date_format.replace( new RegExp("\\bdd\\b"), 'DD' );
            month_str = date_str.replace( new RegExp("\\bmm\\b"), 'MM' );
            year_str = month_str.replace( new RegExp("\\by\\b"), 'YY' );
            break;
        case "yy-mm-dd":
            date_str = date_format.replace( new RegExp("\\bdd\\b"), 'DD' );
            month_str = date_str.replace( new RegExp("\\bmm\\b"), 'MM' );
            year_str = month_str.replace( new RegExp("\\byy\\b"), 'YYYY' );
            break;
        case "dd-mm-y":
            date_str = date_format.replace( new RegExp("\\bdd\\b"), 'DD' );
            month_str = date_str.replace( new RegExp("\\bmm\\b"), 'MM' );
            year_str = month_str.replace( new RegExp("\\by\\b"), 'YY' );
            break;
        case 'd M, y':
            date_str = date_format.replace( new RegExp("\\bd\\b"), 'D' );
            month_str = date_str.replace( new RegExp("\\bM\\b"), 'MMM' );
            year_str = month_str.replace( new RegExp("\\by\\b"), 'YY' );
            break;
        case 'd M, yy':
            date_str = date_format.replace( new RegExp("\\bd\\b"), 'D' );
            month_str = date_str.replace( new RegExp("\\bM\\b"), 'MMM' );
            year_str = month_str.replace( new RegExp("\\byy\\b"), 'YYYY' );
            break;
        case 'd MM, y':
            date_str = date_format.replace( new RegExp("\\bd\\b"), 'D' );
            month_str = date_str.replace( new RegExp("\\bMM\\b"), 'MMMM' );
            year_str = month_str.replace( new RegExp("\\by\\b"), 'YY' );
            break;
        case 'd MM, yy':
            date_str = date_format.replace( new RegExp("\\bd\\b"), 'D' );
            month_str = date_str.replace( new RegExp("\\bMM\\b"), 'MMMM' );
            year_str = month_str.replace( new RegExp("\\byy\\b"), 'YYYY' );
            break;
        case 'DD, d MM, yy':
            day_str = date_format.replace( new RegExp("\\bDD\\b"), 'dddd' );
            date_str = day_str.replace( new RegExp("\\bd\\b"), 'D' );
            month_str = date_str.replace( new RegExp("\\bMM\\b"), 'MMMM' );
            year_str = month_str.replace( new RegExp("\\byy\\b"), 'YYYY' );
            break;
        case 'D, M d, yy':
            day_str = date_format.replace( new RegExp("\\bD\\b"), 'ddd' );
            date_str = day_str.replace( new RegExp("\\bd\\b"), 'D' );
            month_str = date_str.replace( new RegExp("\\bM\\b"), 'MMM' );
            year_str = month_str.replace( new RegExp("\\byy\\b"), 'YYYY' );
            break;
        case 'DD, M d, yy':
            day_str = date_format.replace( new RegExp("\\bDD\\b"), 'dddd' );
            date_str = day_str.replace( new RegExp("\\bd\\b"), 'D' );
            month_str = date_str.replace( new RegExp("\\bM\\b"), 'MMM' );
            year_str = month_str.replace( new RegExp("\\byy\\b"), 'YYYY' );
            break;
        case 'DD, MM d, yy':
            day_str = date_format.replace( new RegExp("\\bDD\\b"), 'dddd' );
            date_str = day_str.replace( new RegExp("\\bd\\b"), 'D' );
            month_str = date_str.replace( new RegExp("\\bMM\\b"), 'MMMM' );
            year_str = month_str.replace( new RegExp("\\byy\\b"), 'YYYY' );
            break;
        case 'D, MM d, yy':
            day_str = date_format.replace( new RegExp("\\bD\\b"), 'ddd' );
            date_str = day_str.replace( new RegExp("\\bd\\b"), 'D' );
            month_str = date_str.replace( new RegExp("\\bMM\\b"), 'MMMM' );
            year_str = month_str.replace( new RegExp("\\byy\\b"), 'YYYY' );
            break;
    }

    return year_str;
}

/**
 * Concatenation of options for jQuery datepicker
 *
 * @function jsonConcat
 * @memberof orddd_initialize_functions
 * @param {string} o1 - Options of datepicker
 * @param {string} o2 - Options of datepicker
 * @returns {string} o1 - Concatenation of two Options o1 and o2
 * @since 1.0
 */
function jsonConcat( o1, o2 ) {
    for ( var key in o2 ) {
        o1[ key ] = o2[ key ];
    }
    return o1;
}

/**
 * Shows the Custom Time Slots
 *
 * @function show_times_custom
 * @memberof orddd_initialize_functions
 * @param {date} date - Date
 * @param {object} inst 
 * @since 3.0
 */
function show_times_custom( date, inst ) {
    if( jQuery( '#orddd_disable_delivery_fields' ).val() == 'yes' && "1" != jQuery( "#orddd_is_admin" ).val() ) {
        jQuery( "#e_deliverydate" ).datepicker( "option", "disabled", true );
    }

    var location = jQuery( "select[name=\"orddd_locations\"]" ).find(":selected").val();
    if( typeof location === "undefined" ) {
        var location = "";
    }

    var shipping_method = orddd_get_selected_shipping_method();
    if( shipping_method.indexOf( 'usps' ) !== -1 && (shipping_method.split(":").length ) < 3 ) {
        shipping_method = jQuery( "#orddd_zone_id" ).val() + ":" + shipping_method;
    }

    if( shipping_method.indexOf( 'wf_fedex_woocommerce_shipping' ) === -1 && shipping_method.indexOf( 'fedex' ) !== -1 && ( shipping_method.split( ":" ).length ) < 3 ) {
        shipping_method = jQuery( "#orddd_zone_id" ).val() + ":" + shipping_method;
    }

    var shipping_class = jQuery( "#orddd_shipping_class_settings_to_load" ).val();
    
    var product_category = jQuery( "#orddd_category_settings_to_load" ).val();

    var pickup_location = '';
    if( typeof orddd_lpp_method_func == 'function' ) {
        pickup_location = orddd_lpp_method_func( shipping_method );    
    }
   
    var monthValue = inst.selectedMonth+1;
    var dayValue = inst.selectedDay;
    var yearValue = inst.selectedYear;
    var all = dayValue + "-" + monthValue + "-" + yearValue;

    if( jQuery( "#time_slot_enable_for_shipping_method" ).val() == "on" ) {
        
        if( typeof( inst.id ) !== "undefined" ) {  
            var data = {
                current_date: all,
                shipping_method: shipping_method,
                pickup_location: pickup_location,
                shipping_class: shipping_class, 
                product_category: product_category,
                orddd_location: location,
                time_slot_session: localStorage.getItem( "time_slot" ),
                min_date: jQuery( "#orddd_min_date_set" ).val(),
                current_date_to_check: jQuery( "#orddd_current_date_set" ).val(),
                holidays_str: jQuery( "#orddd_delivery_date_holidays" ).val(),
                lockout_str: jQuery( "#orddd_lockout_days" ).val(),
                action: "check_for_time_slot_orddd",
                admin: jsL10n.is_admin,
            };
            var option_selected = jQuery( '#orddd_auto_populate_first_available_time_slot' ).val();
            jQuery( "#time_slot" ).attr("disabled", "disabled");
            jQuery( "#time_slot_field" ).attr( "style", "opacity: 0.5" );
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                jQuery( "#time_slot_field" ).attr( "style" ,"opacity:1" );
                if( jQuery( "#orddd_is_cart" ).val() == 1 ) {
                    jQuery( "#time_slot" ).attr( "style", "cursor: pointer !important;max-width:300px" );
                } else {
                    jQuery( "#time_slot" ).attr( "style", "cursor: pointer !important" );
                }
                jQuery( "#time_slot" ).removeAttr( "disabled" ); 
                
                orddd_load_time_slots( response );

                if( option_selected == "on" || ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() && localStorage.getItem( "time_slot" ) != '' ) ) {
                    jQuery( "body" ).trigger( "update_checkout" );
                    if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
                        jQuery( "#hidden_e_deliverydate" ).val( jQuery( "#e_deliverydate" ).val() );
                        jQuery( "#hidden_h_deliverydate" ).val( all );
                        jQuery( "#hidden_timeslot" ).val( jQuery( "#time_slot" ).find(":selected").val() );
                        jQuery( "#hidden_shipping_method" ).val( shipping_method );
                        jQuery( "#hidden_shipping_class" ).val( jQuery( "#orddd_shipping_class_settings_to_load" ).val() );
                        jQuery( "body" ).trigger( "wc_update_cart" );
                    }
                } 
            });
        }
    } else if( jQuery( "#time_setting_enable_for_shipping_method" ).val() == "on" ) {
        var is_min_hour_set = 'no';
        if( ( all == jQuery( "#orddd_current_day" ).val() || all == jQuery( "#orddd_min_date_set" ).val() ) && ( jQuery( "#orddd_custom_based_same_day_delivery" ).val() != 'on' && jQuery( "#orddd_custom_based_next_day_delivery" ).val() != 'on' ) ) {
            is_min_hour_set = 'yes';
        } else if( all == jQuery( "#orddd_current_day" ).val() && ( jQuery( "#orddd_custom_based_same_day_delivery" ).val() == 'on' || jQuery( "#orddd_custom_based_next_day_delivery" ).val() != 'on' ) ) {
            is_min_hour_set = 'yes';
        } 

        if( typeof( inst.id ) !== "undefined" ) {  
            var orddd_disable_minimum_delivery_time_slider = jQuery( "#orddd_disable_minimum_delivery_time_slider" ).val();
            var tp_inst = jQuery.datepicker._get( inst, "timepicker" );
            if( "yes" != orddd_disable_minimum_delivery_time_slider ) {
                if( 'yes' == is_min_hour_set ) {
                    inst.settings.hourMin = parseInt( jQuery( "#orddd_min_hour" ).val() );
                    tp_inst._defaults.hourMin = parseInt( jQuery( "#orddd_min_hour" ).val() );
                    inst.settings.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    tp_inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    tp_inst._limitMinMaxDateTime(inst, true);
                } else {
                    inst.settings.hourMin = parseInt( jQuery( "#orddd_min_hour_set" ).val() );
                    tp_inst._defaults.hourMin = parseInt( jQuery( "#orddd_min_hour_set" ).val() );
                    inst.settings.minuteMin = 0;
                    tp_inst._defaults.minuteMin = 0;
                    tp_inst._limitMinMaxDateTime(inst, true);
                }
            } else {
                inst.settings.hourMin = parseInt( jQuery( "#orddd_min_hour_set" ).val() );
                tp_inst._defaults.hourMin = parseInt( jQuery( "#orddd_min_hour_set" ).val() );
                inst.settings.minuteMin = 0;
                tp_inst._defaults.minuteMin = 0;
                tp_inst._limitMinMaxDateTime(inst, true);
            }
            jQuery.datepicker._updateDatepicker(inst);
        } else if( typeof( inst.inst.id ) !== "undefined" )  {
            var monthValue = inst.inst.currentMonth+1;
            var dayValue = inst.inst.currentDay;
            var yearValue = inst.inst.currentYear;
            var all = dayValue + "-" + monthValue + "-" + yearValue;
            var is_min_hour_set = 'no';
            if( ( all == jQuery( "#orddd_current_day" ).val() || all == jQuery( "#orddd_min_date_set" ).val() ) && ( jQuery( "#orddd_same_day_delivery" ).val() != 'on' && jQuery( "#orddd_next_day_delivery" ).val() != 'on' ) ) {
                is_min_hour_set = 'yes';
            } else if( all == jQuery( "#orddd_current_day" ).val() && ( jQuery( "#orddd_same_day_delivery" ).val() == 'on' || jQuery( "#orddd_next_day_delivery" ).val() == 'on' ) ) {
                is_min_hour_set = 'yes';
            } 
            var tp_inst = jQuery.datepicker._get( inst.inst, "timepicker" );
            if( 'yes' == is_min_hour_set ) {
                var time_format = jQuery( '#orddd_delivery_time_format' ).val();
                var split = inst.formattedTime.split( ":" );
                if( time_format == "1" ) {
                    if( "PM".indexOf( split[ 1 ] ) !== -1 ) {
                        var hour_time  = parseInt( split[ 0 ] ) + parseInt( 12 );    
                    } else {
                        var hour_time = parseInt( split[ 0 ] );
                    }
                } else {
                    var hour_time  = parseInt( split[ 0 ] );
                }
                if( hour_time == parseInt( jQuery( "#orddd_min_hour" ).val() ) ) {
                    inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    inst.inst.settings.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    tp_inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    tp_inst._limitMinMaxDateTime( inst.inst, true );
                } else {
                    inst._defaults.minuteMin = 0;
                    inst.inst.settings.minuteMin = 0;
                    tp_inst._defaults.minuteMin = 0;
                    tp_inst._limitMinMaxDateTime( inst.inst, true );
                }
            }
            jQuery.datepicker._updateDatepicker(inst.inst);
        }
    } else if( jQuery( "#orddd_enable_time_slot" ).val() == "on"  ) {
        if( typeof( inst.id ) !== "undefined" ) {  
            var data = {
                current_date: all,
                shipping_method: shipping_method,
                pickup_location: pickup_location,
                shipping_class: shipping_class, 
                product_category: product_category,
                orddd_location: location,
                time_slot_session: localStorage.getItem( "time_slot" ),
                min_date: jQuery( "#orddd_min_date_set" ).val(),
                current_date_to_check: jQuery( "#orddd_current_date_set" ).val(),
                holidays_str: jQuery( "#orddd_delivery_date_holidays" ).val(),
                lockout_str: jQuery( "#orddd_lockout_days" ).val(),
                action: "check_for_time_slot_orddd",
                admin: jsL10n.is_admin,
            };
            var option_selected = jQuery( '#orddd_auto_populate_first_available_time_slot' ).val();
            jQuery( "#time_slot" ).attr("disabled", "disabled");
            jQuery( "#time_slot_field" ).attr( "style", "opacity: 0.5" );
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                jQuery( "#time_slot_field" ).attr( "style" ,"opacity:1" );
                if( jQuery( "#orddd_is_cart" ).val() == 1 ) {
                    jQuery( "#time_slot" ).attr( "style", "cursor: pointer !important;max-width:300px" );
                } else {
                    jQuery( "#time_slot" ).attr( "style", "cursor: pointer !important" );
                }

                
                jQuery( "#time_slot" ).removeAttr( "disabled" ); 
                
                orddd_load_time_slots( response );

                if( option_selected == "on" || ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() && localStorage.getItem( "time_slot" ) != '' ) ) {
                    jQuery( "body" ).trigger( "update_checkout" );
                    if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
                        jQuery( "#hidden_e_deliverydate" ).val( jQuery( "#e_deliverydate" ).val() );
                        jQuery( "#hidden_h_deliverydate" ).val( all );
                        jQuery( "#hidden_timeslot" ).val( jQuery( "#time_slot" ).find(":selected").val() );
                        jQuery( "#hidden_shipping_method" ).val( shipping_method );
                        jQuery( "#hidden_shipping_class" ).val( jQuery( "#orddd_shipping_class_settings_to_load" ).val() );
                        jQuery( "body" ).trigger( "wc_update_cart" );
                    }
                } 
            });
        }
    } else if( jQuery( "#orddd_enable_time_slider" ).val() == "on" ) {
        var is_min_hour_set = 'no';
        if( ( all == jQuery( "#orddd_current_day" ).val() || all == jQuery( "#orddd_min_date_set" ).val() ) && ( jQuery( "#orddd_same_day_delivery" ).val() != 'on' && jQuery( "#orddd_next_day_delivery" ).val() != 'on' ) ) {
            is_min_hour_set = 'yes';
        } else if( all == jQuery( "#orddd_current_day" ).val() && ( jQuery( "#orddd_same_day_delivery" ).val() == 'on' || jQuery( "#orddd_next_day_delivery" ).val() != 'on' ) ) {
            is_min_hour_set = 'yes';
        } 

        if( typeof( inst.id ) !== "undefined" ) {  
            var tp_inst = jQuery.datepicker._get( inst, "timepicker" );
            if( 'yes' == is_min_hour_set ) {
                inst.settings.hourMin = parseInt( jQuery( "#orddd_min_hour" ).val() );
                tp_inst._defaults.hourMin = parseInt( jQuery( "#orddd_min_hour" ).val() );
                inst.settings.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                tp_inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                tp_inst._limitMinMaxDateTime(inst, true);
            } else {
                inst.settings.hourMin = parseInt( jQuery( "#orddd_min_hour_set" ).val() );
                tp_inst._defaults.hourMin = parseInt( jQuery( "#orddd_min_hour_set" ).val() );
                inst.settings.minuteMin = 0;
                tp_inst._defaults.minuteMin = 0;
                tp_inst._limitMinMaxDateTime(inst, true);
            }
            jQuery.datepicker._updateDatepicker(inst);
        } else if( typeof( inst.inst.id ) !== "undefined" )  {
            var monthValue = inst.inst.currentMonth+1;
            var dayValue = inst.inst.currentDay;
            var yearValue = inst.inst.currentYear;
            var all = dayValue + "-" + monthValue + "-" + yearValue;
            var tp_inst = jQuery.datepicker._get( inst.inst, "timepicker" );
            if( 'yes' == is_min_hour_set ) {
                var time_format = jQuery( '#orddd_delivery_time_format' ).val();
                var split = inst.formattedTime.split( ":" );
                if( time_format == "1" ) {
                    if( "PM".indexOf( split[ 1 ] ) !== -1 ) {
                        var hour_time  = parseInt( split[ 0 ] ) + parseInt( 12 );    
                    } else {
                        var hour_time = parseInt( split[ 0 ] );
                    }
                } else {
                    var hour_time  = parseInt( split[ 0 ] );
                }
                if( hour_time == parseInt( jQuery( "#orddd_min_hour" ).val() ) ) {
                    inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    inst.inst.settings.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    tp_inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    tp_inst._limitMinMaxDateTime( inst.inst, true );
                } else {
                    inst._defaults.minuteMin = 0;
                    inst.inst.settings.minuteMin = 0;
                    tp_inst._defaults.minuteMin = 0;
                    tp_inst._limitMinMaxDateTime( inst.inst, true );
                }
            }
            jQuery.datepicker._updateDatepicker(inst.inst);
        }
    }

    if( jQuery( "#orddd_delivery_date_on_cart_page" ).val() == 'on' ) {
        localStorage.setItem( "e_deliverydate_session", jQuery( "#e_deliverydate" ).val() );
        localStorage.setItem( "h_deliverydate_session", all );
        if( localStorage.getItem( "time_slot" ) == null ) {
            localStorage.setItem( "time_slot", jQuery( "#time_slot" ).find(":selected").val() );
        } 

        var current_date = jQuery( "#orddd_current_day" ).val();
        var split_current_date = current_date.split( '-' );
        var ordd_next_date = new Date( split_current_date[ 2 ], ( split_current_date[ 1 ] - 1 ), split_current_date[ 0 ], jQuery( "#orddd_current_hour" ).val(), jQuery( "#orddd_current_minute" ).val() );

        ordd_next_date.setHours( ordd_next_date.getHours() + 2 );
        localStorage.setItem( "orddd_storage_next_time", ordd_next_date.getTime() );
    }
}

/**
 * This function disables the date in the calendar for holidays.
 *
 * @function nd
 * @memberof orddd_initialize_functions
 * @param {object} date - date to be checked
 * @returns {bool} Returns true or false based on date available or not
 * @since 1.0
 */
function nd( date ) {
    var disabledDays = eval( '[' + jQuery( '#orddd_delivery_date_holidays' ).val() + ']' );
    var m = date.getMonth(), d = date.getDate(), y = date.getFullYear(), w = date.getDay();
    var currentdt = m + '-' + d + '-' + y;
    
    var dt = new Date();
    var today = dt.getMonth() + '-' + dt.getDate() + '-' + dt.getFullYear();
    for ( i = 0; i < disabledDays.length; i++ ) {
        var holidays_array = disabledDays[ i ].split( ":" );
        if( holidays_array[ 1 ] == ( ( m+1 ) + '-' + d + '-' + y ) || holidays_array[ 1 ] == ( ( m+1 ) + '-' + d ) ) {
            if( '' == holidays_array[ 0 ] ) {
                return [ false, "holidays", "Holiday" ];
            } else {
                return [ false, "holidays", holidays_array[ 0 ]  ];
            }
        } 
    }
	var weekdays = [];
    weekdays[ 'monday' ] = 1;
    weekdays[ 'tuesday' ] = 2;
    weekdays[ 'wednesday' ] = 3;
    weekdays[ 'thursday' ] = 4;
    weekdays[ 'friday' ] = 5;
    weekdays[ 'saturday' ] = 6;
    weekdays[ 'sunday' ] = 0;
	
    var add_tooltip_for_weekday = jQuery( '#add_tooltip_for_weekday' ).val();
    var is_tooltip_set = 'no';
    if( '' != add_tooltip_for_weekday ) {
        var weekday_tooltip_arr = add_tooltip_for_weekday.split( ";" );
        for( i=0; i < weekday_tooltip_arr.length; i++ ) {
            var tooltip_arr = weekday_tooltip_arr[ i ].split( '=>' );
            var weekday = tooltip_arr[ 0 ];
            var weekday_tooltip = tooltip_arr[ 1 ];
            if( typeof weekdays[ weekday ] != 'undefined' && date.getDay() == weekdays[ weekday ] ) {
                return [ true, '', weekday_tooltip ];        
            } else {
                is_tooltip_set = 'no';
            }    
        }
    } 
    if( 'no' == is_tooltip_set ) {
        return [ true ];    
    }
}

/**
 * This function disables the date in the calendar for disabled weekdays and for which lockout is reached.
 *
 * @function dwd
 * @memberof orddd_initialize_functions
 * @param {object} date - date to be checked
 * @returns {bool} Returns true or false based on date available or not
 * @since 1.0
 */
function dwd( date ) {
    var lockout_calculation = 'yes';
    if ( jQuery( "#orddd_subscriptions_settings" ).val() == 'on' && typeof jQuery( "#orddd_if_renewal_subscription" ).val() != "undefined" && jQuery( "#orddd_if_renewal_subscription" ).val() == 'yes' ) {
        lockout_calculation = 'no'; 
    }
    
    var lockoutDays = eval( '[' + jQuery( '#orddd_lockout_days' ).val() + ']' );
    var m = date.getMonth(), d = date.getDate(), y = date.getFullYear();
     
    var startDaysDisabled = eval( "[" + jQuery( "#orddd_disabled_days_str" ).val() + "]" );
    
    for ( i = 0; i < startDaysDisabled.length; i++ ) {
        if( jQuery.inArray( ( m+1 ) + '-' + d + '-' + y, startDaysDisabled ) != -1 ) {
            return [ false, "cut_off_time_over", jsL10n.cutOffTimeText ];
        }
    }
    
    if( lockout_calculation == "yes" ) {
        for ( i = 0; i < lockoutDays.length; i++ ) {
            if( jQuery.inArray( ( m+1 ) + '-' + d + '-' + y, lockoutDays ) != -1 ) {
                return [ false, "booked_dates", jsL10n.bookedText ];
            }
        }
    }
	
	var weekdays = [];
    weekdays[ 'monday' ] = 1;
    weekdays[ 'tuesday' ] = 2;
    weekdays[ 'wednesday' ] = 3;
    weekdays[ 'thursday' ] = 4;
    weekdays[ 'friday' ] = 5;
    weekdays[ 'saturday' ] = 6;
    weekdays[ 'sunday' ] = 0;
	
    var day = 'orddd_weekday_' + date.getDay();
    if ( jQuery( "#" + day ).val() == 'checked' ) {
        var add_tooltip_for_weekday = jQuery( '#add_tooltip_for_weekday' ).val();
        var is_tooltip_set = 'no';
        if( '' != add_tooltip_for_weekday ) {
            var weekday_tooltip_arr = add_tooltip_for_weekday.split( ";" );
            for( i=0; i < weekday_tooltip_arr.length; i++ ) {
                var tooltip_arr = weekday_tooltip_arr[ i ].split( '=>' );
                var weekday = tooltip_arr[ 0 ];
                var weekday_tooltip = tooltip_arr[ 1 ];
                if( typeof weekdays[ weekday ] != 'undefined' && date.getDay() == weekdays[ weekday ] ) {
                    return [ true, '', weekday_tooltip ];        
                } else {
                    is_tooltip_set = 'no';
                }    
            }
        } 
        if( 'no' == is_tooltip_set ) {
            return [ true ];    
        }
    } else if ( jQuery( "#orddd_specific_delivery_dates" ).val() == "on" ) {
        if ( jQuery( "#orddd_delivery_dates" ).val()  != '' ) {
            var deliveryDates = eval( '[' + jQuery( "#orddd_delivery_dates" ).val() + ']');
            var dt = new Date();
            var today = dt.getMonth() + '-' + dt.getDate() + '-' + dt.getFullYear();
            for ( i = 0; i < deliveryDates.length; i++ ) {
                if( jQuery.inArray( ( m+1 ) + '-' + d + '-' + y, deliveryDates ) != -1 ) {
                    return [ true ];
                }
            }
        }
    }
    return [ false ];
}

/**
 * This function returns the availability of the dates in the calendar. 
 *
 * @function pd
 * @memberof orddd_initialize_functions
 * @param {object} date - date to be checked
 * @returns {bool} Returns true or false based on date available or not
 * @since 1.0
 */

function pd( date ) {
    var field_name = jQuery( "#orddd_field_name" ).val();    
    var current_date = jQuery( "#orddd_current_day" ).val();
    var split_current_date = current_date.split( '-' );
    var current_day_to_check = new Date ( split_current_date[ 1 ] + '/' + split_current_date[ 0 ] + '/' + split_current_date[ 2 ] );
    var current_time = current_day_to_check.getTime();
    var date_time = date.getTime();

    if( date_time >= current_time ) {
        var day = 'orddd_weekday_' + date.getDay();
        var m = date.getMonth(), d = date.getDate(), y = date.getFullYear();
        var partially_booked_str = jQuery( '#orddd_partially_booked_dates' ).val();
        if( partially_booked_str != '' ) {
            var partially_booked = eval( '[' + partially_booked_str + ']' );
            var partial_availability_str = "";
            for ( i = 0; i <= partially_booked.length; i++ ) {
                if( typeof partially_booked[ i ] != 'undefined' ) {
                    var partially_booked_arr = partially_booked[ i ].split( ">" );
                    if( partially_booked_arr[0] == 'available_slots' ) {
                        partial_availability_str = partial_availability_str + partially_booked_arr[1].replace( /nl/gi, "\n" );
                    }
                    if( partially_booked_arr[0] == ( ( m+1 ) + '-' + d + '-' + y ) ) {
                        partial_availability_str = partial_availability_str + partially_booked_arr[1].replace( /nl/gi, "\n" );
                        if( jQuery( '#' + field_name ).length == 0 && jQuery( 'input[id^=' + field_name + ']' ).length == 0 ) {
                            return [ true, "undefined ui-datepicker-unselectable partially-booked", partial_availability_str ];
                        } else {
                            return [ true, "partially-booked", partial_availability_str ];
                        }
                    }
                }
            } 
        }

        var available_deliveries = jQuery( '#orddd_available_deliveries' ).val();
        if( available_deliveries.indexOf( ',' ) !== -1 ) {
            var availability_str = '';
            var available_deliveries_arr = eval( '[' + jQuery( '#orddd_available_deliveries' ).val() + ']' );
            var availability_str = "";
            for ( i = 0; i <= available_deliveries_arr.length; i++ ) {
                if( typeof available_deliveries_arr[ i ] != 'undefined' && ( available_deliveries_arr[ i ].indexOf( day ) !== -1 || available_deliveries_arr[ i ].indexOf( 'available_slots' ) !== -1 ) ) {
                   var availability_arr = available_deliveries_arr[ i ].split( ">" );
                   var availability_str = availability_str + availability_arr[ 1 ] + "\n";
                }
            }

            if( jQuery( '#' + field_name ).length == 0 && jQuery( 'input[id^=' + field_name + ']' ).length == 0 ) {
                return [ true, 'undefined ui-datepicker-unselectable available-deliveries', availability_str ];    
            } else {
                return [ true, 'available-deliveries', availability_str ];    
            }
        }

        if( jQuery( '#' + field_name ).length == 0 && jQuery( 'input[id^=' + field_name + ']' ).length == 0 ) {
            return [ true, 'undefined ui-datepicker-unselectable available-deliveries', available_deliveries ];    
        } else {
            return [ true, 'available-deliveries', available_deliveries ];    
        }
    } else {
        return true;
    }
}

/**
 * The function is called for each day in the datepicker before it is displayed.
 *
 * @function chd
 * @memberof orddd_initialize_functions
 * @param {object} date - date to be checked
 * @returns {array} Returns an array
 * @since 1.0
 */
function chd( date ) {
    var nW = dwd( date );
    if( nW[ 0 ] == true ) {
        var holiday = nd( date );
        if( holiday[ 0 ] == false ) {
            return holiday
        } else {
            return pd( date );
        } 
    } else {
        return nW;
    }
}

/**
 * This function is called just before the datepicker is displayed.
 *
 * @function avd
 * @memberof orddd_initialize_functions
 * @param {object} date - date to be checked
 * @returns {object} options object to update the datepicker
 * @since 1.0
 */
function avd( date ) {
    var if_subscription = 'no';
    var current_date = jQuery( "#orddd_current_day" ).val();
    var split_current_date = current_date.split( '-' );
    var current_day = new Date ( split_current_date[ 1 ] + '/' + split_current_date[ 0 ] + '/' + split_current_date[ 2 ] );
    var current_day_to_check = new Date ( split_current_date[ 1 ] + '/' + split_current_date[ 0 ] + '/' + split_current_date[ 2 ] );
    var disabledDays = eval( '[' + jQuery( '#orddd_delivery_date_holidays' ).val() + ']' );
    var bookedDays = eval( "[" + jQuery( "#orddd_lockout_days" ).val() + "]" );
    var holidays = [];
    for ( i = 0; i < disabledDays.length; i++ ) {
        var holidays_array = disabledDays[ i ].split( ":" );
        holidays[i] = holidays_array[ 1 ];
    }
    
    var specific_dates = jQuery( "#orddd_specific_delivery_dates" ).val();
    var deliveryDates = [];

    if ( jQuery( "#orddd_subscriptions_settings" ).val() == 'on' ) {
        if ( typeof jQuery( "#orddd_start_date_for_subscription" ).val() != "undefined" && typeof jQuery( "#orddd_number_of_dates_for_subscription" ).val() != "undefined" ) {
            var start_date = jQuery( "#orddd_start_date_for_subscription" ).val();
            var split_date = start_date.split( '-' );
            var delay_days = new Date ( split_date[1] + '/' + split_date[0] + '/' + split_date[2] );
            
            var noOfDaysToFind = parseInt( jQuery( "#orddd_number_of_dates_for_subscription" ).val() );
            
            if ( isNaN( delay_days ) ) {
                delay_days = new Date();
                delay_days.setDate( delay_days.getDate()+1 );
            }
            
            if( isNaN( noOfDaysToFind ) ) {
                noOfDaysToFind = 1000;
            }
            if_subscription = 'yes';
        }   
    }
    
    if( if_subscription == 'no' ) {
        var delay_date = jQuery( "#orddd_minimumOrderDays" ).val();
        if( delay_date != '' ) {
            var split_date = delay_date.split( '-' );
            var delay_days = new Date ( split_date[ 1 ] + '/' + split_date[ 0 ] + '/' + split_date[ 2 ] );
        } else {
            var delay_days = current_day;
        }
    
        var noOfDaysToFind = parseInt( jQuery( "#orddd_number_of_dates" ).val() );
        if ( isNaN( delay_days ) ) {
            delay_days = new Date();
            delay_days.setDate( delay_days.getDate()+1 );
        }
        
        if( isNaN( noOfDaysToFind ) ) {
            noOfDaysToFind = 1000;
        }
    }
    
    // re-calculate the Minimum Delivery time (in days): to include weekdays that are disabled for delivery
    if ( jQuery( "#orddd_disable_for_delivery_days" ).val() != 'yes' ) {
        if( delay_date != "" ) {
            if( 'on' == jQuery( '#orddd_enable_shipping_days' ).val() ) {
                var delay_weekday = delay_days.getDay();
                for ( j = delay_weekday ; ;j++ ) {
                    day = 'orddd_weekday_' + delay_weekday;
                    day_check = jQuery( "#" + day ).val();
                    if ( day_check == '' ) {
                        delay_days.setDate( delay_days.getDate()+1 );
                        delay_weekday = delay_days.getDay();
                    } else {
                        break;
                    }
                }
                var current_date_to_set = jQuery( "#orddd_current_date_set" ).val();
                var split_current_date_to_check = current_date_to_set.split( '-' );
                var current_day_to_check = new Date ( split_current_date_to_check[ 1 ] + '/' + split_current_date_to_check[ 0 ] + '/' + split_current_date_to_check[ 2 ] );
            } 
        }
    }

    var min_date_to_set = delay_days.getDate() + "-" + ( delay_days.getMonth()+1 ) + "-" + delay_days.getFullYear();
    jQuery( "#orddd_min_date_set" ).val( min_date_to_set );
    
    var current_day_to_set = current_day_to_check.getDate() + "-" + ( current_day_to_check.getMonth()+1 ) + "-" + current_day_to_check.getFullYear();
    jQuery( "#orddd_current_date_set" ).val( current_day_to_set );

    var minDate = delay_days;

    var todays_date = new Date();
    var t_year = todays_date.getFullYear();
    var t_month = todays_date.getMonth()+1;
    var t_day = todays_date.getDate();
    var t_month_days = new Date( t_year, t_month, 0 ).getDate();

    start = ( delay_days.getMonth()+1 ) + "/" + delay_days.getDate() + "/" + delay_days.getFullYear();
    var start_month = delay_days.getMonth()+1;
    var start_year = delay_days.getFullYear();
    
    var end_date = new Date( ad( delay_days , noOfDaysToFind ) );
    end = (end_date.getMonth()+1) + "/" + end_date.getDate() + "/" + end_date.getFullYear();
    
    var specific_max_date = start;
    var m = todays_date.getMonth(), d = todays_date.getDate(), y = todays_date.getFullYear();
    var currentdt = m + '-' + d + '-' + y;
    
    var dt = new Date();
    var today = dt.getMonth() + '-' + dt.getDate() + '-' + dt.getFullYear();
    
     if ( jQuery( "#orddd_delivery_dates" ).val()  != '' ) {
        var deliveryDates = eval( '[' + jQuery( "#orddd_delivery_dates" ).val() + ']');
        for ( ii = 0; ii < deliveryDates.length; ii++ ) {
            var split = deliveryDates[ ii ].split( '-' );
            var specific_date = split[ 0 ] + '/' + split[ 1 ] + '/' + split[ 2 ];
            var diff = gd( specific_max_date , specific_date , 'days' );
            if ( diff >= 0 ) {
                specific_max_date = specific_date;
            }
        }
    }
    
    var loopCounter = gd( start , end , 'days' );
    var prev = delay_days;
    var new_l_end, is_holiday;
    for ( var i = 1; i <= loopCounter ; i++ ) {
        var l_start = new Date( start );
        var l_end = new Date( end );
        new_l_end = l_end;
        var new_date = new Date( ad( l_start, i ) );
        var day = "";
        day = 'orddd_weekday_' + new_date.getDay();
        day_check = jQuery( "#" + day ).val();
        is_holiday = nd( new_date );
        if ( day_check != "checked" || is_holiday != 'true' ) {
            new_l_end = l_end = new Date( ad( l_end, 2 ) );
            end = ( l_end.getMonth()+1 ) + "/" + l_end.getDate() + "/" + l_end.getFullYear();
            if ( specific_dates == "on" ) {
                diff = gd( l_end, specific_max_date, 'days' );
                if ( diff >= 0 ){
                    loopCounter = gd( start, end, 'days' );
                }
            } else {
                loopCounter = gd( start, end , 'days' );
            }
        }
    }
    
    var maxMonth = new_l_end.getMonth()+1;
    var maxYear = new_l_end.getFullYear();
    var number_of_months = parseInt( jQuery( "#orddd_number_of_months" ).val() );
    if ( "1" == jQuery( "#orddd_is_admin" ).val() ) {
        return {
            minDate: '',
            maxDate: '',
            numberOfMonths: number_of_months             
        };
    } else {
        if ( maxMonth > start_month || maxYear > start_year ) {
            return {
                minDate: new Date(start),
                maxDate: l_end,
                numberOfMonths: number_of_months 
            };
        }
        else {
            return {
                minDate: new Date(start),
                maxDate: l_end,
                numberOfMonths: number_of_months                 
            };
        }
    }
}

/**
 * This function is called to find the end date to be set in the calendar.
 *
 * @function ad
 * @memberof orddd_initialize_functions
 * @param {object} dateObj
 * @param {number} numDays - number of dates to choose
 * @returns {number} returns the end date to be set in the calendar
 * @since 1.0
 */
function ad( dateObj, numDays ) {
    return dateObj.setDate( dateObj.getDate() + ( numDays - 1 ) );
}

/**
 * This function is called to find the difference between the two dates.
 *
 * @function gd
 * @memberof orddd_initialize_functions
 * @param {string} date1 - start date
 * @param {string} date2 - end date
 * @param {string} interval - days
 * @returns {number} returns the number between two dates.
 * @since 1.0
 */
function gd( date1, date2, interval ) {
    var second = 1000,
    minute = second * 60,
    hour = minute * 60,
    day = hour * 24,
    week = day * 7;
    
    date1 = new Date( date1 ).getTime();
    date2 = ( date2 == 'now' ) ? new Date().getTime() : new Date( date2 ).getTime();
    
    var timediff = date2 - date1;
    if ( isNaN( timediff ) ) return NaN;
        switch ( interval ) {
        case "years":
            return date2.getFullYear() - date1.getFullYear();
        case "months":
            return ( (date2.getFullYear() * 12 + date2.getMonth() ) - ( date1.getFullYear() * 12 + date1.getMonth() ) );
        case "weeks":
            return Math.floor( timediff / week );
        case "days":
            return ( Math.floor( timediff / day ) ) + 1;
        case "hours":
            return Math.floor( timediff / hour );
        case "minutes":
            return Math.floor( timediff / minute );
        case "seconds":
            return Math.floor( timediff / second );
        default:
            return undefined;
    }
}

/**
 * This function is called when Same day or Next day is enabled.
 *
 * @function maxdt
 * @memberof orddd_initialize_functions
 * @param {string} date - Date
 * @returns {array} returns max date, min date and number of months.
 * @since 1.0
 */
function maxdt( date ) {
    var if_subscription = "no";
    var disabledDays = eval( '[' + jQuery( '#orddd_delivery_date_holidays' ).val() + ']' );
    if ( jQuery( "#orddd_subscriptions_settings" ).val() == 'on' ) {
        if ( typeof jQuery( "#orddd_start_date_for_subscription" ).val() != "undefined" && typeof jQuery( "#orddd_number_of_dates_for_subscription" ).val() != "undefined" ) {
            var start_date = jQuery( "#orddd_start_date_for_subscription" ).val();
            var split_date = start_date.split( '-' );
            var min_date = new Date ( split_date[1] + '/' + split_date[0] + '/' + split_date[2] );
            
            var noOfDaysToFind = parseInt( jQuery( "#orddd_number_of_dates_for_subscription" ).val() );
                        
            if ( isNaN( min_date ) ) {
                min_date = new Date();
            }
            
            if( isNaN( noOfDaysToFind ) ) {
                noOfDaysToFind = 1000;
            }
            if_subscription = 'yes';
        }   
    }
    
    if( if_subscription == "no" ) {
        var current_date = jQuery( "#orddd_minimumOrderDays" ).val();
        var split_current_date = current_date.split( '-' );
        var min_date = new Date ( split_current_date[ 1 ] + '/' + split_current_date[ 0 ] + '/' + split_current_date[ 2 ]);

        if( jQuery( "#orddd_custom_based_same_day_delivery" ).val() != "on" && jQuery( "#orddd_custom_based_next_day_delivery" ).val() == "on" ) {
            min_date.setDate( min_date.getDate()+1 );
        } else if( typeof jQuery( "#orddd_custom_based_same_day_delivery" ).val() == "undefined" && typeof jQuery( "#orddd_custom_based_next_day_delivery" ).val() == "undefined" ) {
            if( jQuery( "#orddd_same_day_delivery" ).val() != 'on' && jQuery( "#orddd_next_day_delivery" ).val() == 'on' ) {
                min_date.setDate( min_date.getDate()+1 );
            }
        }
        
        var noOfDaysToFind = parseInt( jQuery( "#orddd_number_of_dates" ).val() )
        if( isNaN( min_date ) ) {
            min_date = new Date();
        }
        if( isNaN( noOfDaysToFind ) ) {
            noOfDaysToFind = 1000;
        }
    }
    
    var specific_dates = jQuery( "#orddd_specific_delivery_dates" ).val();
    
    min_date = same_day_next_day_to_set( min_date );

    if( min_date == "" ) {
        var current_date = jQuery( "#orddd_current_day" ).val();
        var split_current_date = current_date.split( '-' );
        var min_date = new Date ( split_current_date[ 1 ] + '/' + split_current_date[ 0 ] + '/' + split_current_date[ 2 ]);
        if( jQuery( "#orddd_custom_based_same_day_delivery" ).val() != "on" && jQuery( "#orddd_custom_based_next_day_delivery" ).val() == "on" ) {
            min_date.setDate( min_date.getDate()+1 );
        } else if( typeof jQuery( "#orddd_custom_based_same_day_delivery" ).val() == "undefined" && typeof jQuery( "#orddd_custom_based_next_day_delivery" ).val() == "undefined" ) {
            if( jQuery( "#orddd_same_day_delivery" ).val() != 'on' && jQuery( "#orddd_next_day_delivery" ).val() == 'on' ) {
                min_date.setDate( min_date.getDate()+1 );
            }
        }
        if( isNaN( min_date ) ) {
            min_date = new Date();
        }
    }
    
    var date = new Date();
    var t_year = date.getFullYear();
    var t_month = date.getMonth()+1;
    var t_day = date.getDate();
    var t_month_days = new Date( t_year, t_month, 0 ).getDate();
    
    start = ( min_date.getMonth()+1 ) + "/" + min_date.getDate() + "/" + min_date.getFullYear();
    var start_month = min_date.getMonth()+1;
    var start_year = min_date.getFullYear();
    
    var end_date = new Date( ad( min_date , noOfDaysToFind ) );
    end = ( end_date.getMonth()+1 ) + "/" + end_date.getDate() + "/" + end_date.getFullYear();

    var specific_max_date = start;

     if ( jQuery( "#orddd_delivery_dates" ).val()  != '' ) {
            var deliveryDates = eval( '[' + jQuery( "#orddd_delivery_dates" ).val() + ']');
        for ( ii = 0; ii < deliveryDates.length; ii++ ) {
            var split = deliveryDates[ ii ].split( '-' );
            var specific_date = split[ 0 ] + '/' + split[ 1 ] + '/' + split[ 2 ];
            var diff = gd( specific_max_date , specific_date , 'days');
            if ( diff >= 0 ) {
                specific_max_date = specific_date;
            }
        }
    }
    
    var loopCounter = gd( start , end , 'days' );
    var prev = min_date;
    var new_l_end, is_holiday;
    for( var i = 1; i <= loopCounter; i++ ) {
        var l_start = new Date( start );
        var l_end = new Date( end );
        new_l_end = l_end;
        var new_date = new Date( ad( l_start, i ) );

        var day = "";
        day = 'orddd_weekday_' + new_date.getDay();
        day_check = jQuery( "#" + day ).val();
        is_holiday = nd( new_date );
        
        if( day_check != "checked" || is_holiday != 'true' ) {
            new_l_end = l_end = new Date( ad( l_end,2 ) );
            end = ( l_end.getMonth()+1 ) + "/" + l_end.getDate() + "/" + l_end.getFullYear();
            if ( specific_dates == "on" ) {
                diff = gd( l_end, specific_max_date, 'days' );
                if (diff >= 0) {
                    loopCounter = gd( start, end, 'days' );
                }
            } else {
                loopCounter = gd( start, end , 'days' );
            }
        }
    }
    
    var maxMonth = new_l_end.getMonth()+1;
    var maxYear = new_l_end.getFullYear();
    var number_of_months = parseInt( jQuery( "#orddd_number_of_months" ).val() );
    if ( "1" == jQuery( "#orddd_is_admin" ).val() ) {
        return {
            minDate: '',
            maxDate: '',
            numberOfMonths: number_of_months             
        };
    } else {
        if ( maxMonth > start_month || maxYear > start_year ) {
            return {
                minDate: new Date( start ),
                maxDate: l_end,
                numberOfMonths: number_of_months 
            };
        } else {
            return {
                minDate: new Date( start ),
                maxDate: l_end,
                numberOfMonths: number_of_months                 
            };
        }
    }
}

/**
 * Sorts the Specific dates
 *
 * @function sortSpecificDates
 * @memberof orddd_initialize_functions
 * @param {array} value_1 - Date
 * @param {array} value_2 - Date
 * @returns {array} returns the sorted array.
 * @since 4.6
 */
function sortSpecificDates( value_1 , value_2 ) {
    return value_1 - value_2;
}

/**
 * Auto populates the first available delivery date on the Delivery Date field
 *
 * @function orddd_autofil_date_time
 * @memberof orddd_initialize_functions
 * @since 4.6
 */
function orddd_autofil_date_time() {
    if( 'no' == jQuery( "#orddd_is_shipping_text_block" ).val() || ( '' == jQuery( "#orddd_is_shipping_text_block" ).val() && 'delivery_calendar' == jQuery( "#orddd_delivery_checkout_options" ).val() ) )  {
        var current_date = jQuery( "#orddd_current_day" ).val();
        var split_current_date = current_date.split( "-" );
        var current_day = new Date ( split_current_date[ 1 ] + "/" + split_current_date[ 0 ] + "/" + split_current_date[ 2 ] );
        var if_subscription = 'no';
        if ( jQuery( "#orddd_subscriptions_settings" ).val() == 'on' ) {
            if ( typeof jQuery( "#orddd_start_date_for_subscription" ).val() != "undefined" && typeof jQuery( "#orddd_number_of_dates_for_subscription" ).val() != "undefined" ) {
                var start_date = jQuery( "#orddd_start_date_for_subscription" ).val();
                var split_date = start_date.split( '-' );
                var delay_days = new Date ( split_date[1] + '/' + split_date[0] + '/' + split_date[2] );
                if_subscription = 'yes';
            }
        }
        
        if( if_subscription == 'no' ) {
            var delay_date = jQuery( "#orddd_minimumOrderDays" ).val();
            if( delay_date != "" ) {
                var split_date = delay_date.split( "-" );
                var delay_days = new Date ( split_date[ 1 ] + "/" + split_date[ 0 ] + "/" + split_date[ 2 ] );
            } else {
                var delay_days = current_day;
            }
        }

        if ( isNaN( delay_days ) ) {
            delay_days = new Date();
            delay_days.setDate( delay_days.getDate()+1 );
        }
        
        if( typeof jQuery( "#orddd_custom_based_next_day_delivery" ).val() != "undefined" && typeof jQuery( "#orddd_custom_based_same_day_delivery" ).val() != "undefined" ) {
            if( jQuery( "#orddd_custom_based_same_day_delivery" ).val() != "on" && jQuery( "#orddd_custom_based_next_day_delivery" ).val() != "on" ) {
                if( delay_date != "" ) {
                    delay_days = minimum_date_to_set( delay_days );
                    if( delay_days != '' ) {
                        var min_date_to_set = delay_days.getDate() + "-" + ( delay_days.getMonth()+1 ) + "-" + delay_days.getFullYear();
                        jQuery( "#orddd_min_date_set" ).val( min_date_to_set );
                    }
                }
            } else if( jQuery( "#orddd_custom_based_same_day_delivery" ).val() != "on" && jQuery( "#orddd_custom_based_next_day_delivery" ).val() == "on" ) {
                delay_days.setDate( delay_days.getDate()+1 );
                delay_days = same_day_next_day_to_set( delay_days );
                if( delay_days != '' ) {
                    var min_date_to_set = delay_days.getDate() + "-" + ( delay_days.getMonth()+1 ) + "-" + delay_days.getFullYear();
                    jQuery( "#orddd_min_date_set" ).val( min_date_to_set );
                }
            } else if( jQuery( "#orddd_custom_based_same_day_delivery" ).val() == "on" && jQuery( "#orddd_custom_based_next_day_delivery" ).val() != "on"  ) {
                delay_days = same_day_next_day_to_set( delay_days );
                if( delay_days != '' ) {
                    var min_date_to_set = delay_days.getDate() + "-" + ( delay_days.getMonth()+1 ) + "-" + delay_days.getFullYear();
                    jQuery( "#orddd_min_date_set" ).val( min_date_to_set );
                }
            } else if( jQuery( "#orddd_custom_based_same_day_delivery" ).val() == "on" && jQuery( "#orddd_custom_based_next_day_delivery" ).val() == "on" ) {
                delay_days = same_day_next_day_to_set( delay_days );
                if( delay_days != '' ) {
                    var min_date_to_set = delay_days.getDate() + "-" + ( delay_days.getMonth()+1 ) + "-" + delay_days.getFullYear();
                    jQuery( "#orddd_min_date_set" ).val( min_date_to_set );
                }
            }
        } else if( jQuery( "#orddd_same_day_delivery" ).val() != 'on' && jQuery( "#orddd_next_day_delivery" ).val() != 'on' ) {
            if( delay_date != "" ) {
                delay_days = minimum_date_to_set( delay_days );
                if( delay_days != '' ) {
                    var min_date_to_set = delay_days.getDate() + "-" + ( delay_days.getMonth()+1 ) + "-" + delay_days.getFullYear();
                    jQuery( "#orddd_min_date_set" ).val( min_date_to_set );
                }
            }
        } else if( jQuery( "#orddd_same_day_delivery" ).val() != 'on' && jQuery( "#orddd_next_day_delivery" ).val() == 'on' ) {
            delay_days.setDate( delay_days.getDate()+1 );
            delay_days = same_day_next_day_to_set( delay_days );
            if( delay_days != '' ) {
                var min_date_to_set = delay_days.getDate() + "-" + ( delay_days.getMonth()+1 ) + "-" + delay_days.getFullYear();
                jQuery( "#orddd_min_date_set" ).val( min_date_to_set );
            }
        } else if( jQuery( "#orddd_same_day_delivery" ).val() == 'on' && jQuery( "#orddd_next_day_delivery" ).val() != 'on' ) {
            delay_days = same_day_next_day_to_set( delay_days );
            if( delay_days != '' ) {
                var min_date_to_set = delay_days.getDate() + "-" + ( delay_days.getMonth()+1 ) + "-" + delay_days.getFullYear();
                jQuery( "#orddd_min_date_set" ).val( min_date_to_set );
            }
        } else if( jQuery( "#orddd_same_day_delivery" ).val() == 'on' && jQuery( "#orddd_next_day_delivery" ).val() == 'on' ) {
            delay_days = same_day_next_day_to_set( delay_days );
            if( delay_days != '' ) {
                var min_date_to_set = delay_days.getDate() + "-" + ( delay_days.getMonth()+1 ) + "-" + delay_days.getFullYear();
                jQuery( "#orddd_min_date_set" ).val( min_date_to_set );
            }
        }
        
        if ( jQuery( "#orddd_subscriptions_settings" ).val() == 'on' ) {
            if ( typeof jQuery( "#orddd_start_date_for_subscription" ).val() != "undefined" && typeof jQuery( "#orddd_end_date_for_subscription" ).val() != "undefined" ) {
                var subscription_date = jQuery( "#orddd_start_date_for_subscription" ).val();
                var split_subscription_date = subscription_date.split( "-" );
                delay_days = new Date ( split_subscription_date[ 1 ] + "/" + split_subscription_date[ 0 ] + "/" + split_subscription_date[ 2 ] );
            }
        }
        
        if( jQuery( "#orddd_enable_shipping_delivery_date" ).val() == 'on' || typeof jQuery( "#orddd_enable_shipping_delivery_date" ).val() == 'undefined' ) {
            var date_to_set = delay_days;
            var e_deliverydate_session = localStorage.getItem( 'e_deliverydate_session' );
            if( typeof( e_deliverydate_session ) != 'undefined' && e_deliverydate_session != '' && e_deliverydate_session != null ) {
                var h_deliverydate_session = localStorage.getItem( 'h_deliverydate_session' );
                if ( h_deliverydate_session ) {
                    var session_date_arr = h_deliverydate_session.split( '-' );
                    date_to_set = new Date( session_date_arr[ 1 ] + '/' + session_date_arr[ 0 ] + '/' + session_date_arr[ 2 ] );

                    var show = jQuery( "#orddd_show_datepicker" ).val();
                    if( 'datetimepicker' == show ) {
                        var time = e_deliverydate_session.split( ' ' );
                        var time_value = time[time.length-1];
                        if( time_value.indexOf( ':' ) !== -1 ) {
                            // Set the Hours & minutes to be prepopulated in the time slider
                            var time_arr = time_value.split( ":" );
                            date_to_set.setHours( time_arr[0], time_arr[1] );
                            jQuery( "#orddd_time_settings_selected" ).val( time_value );    
                        }                    
                    }

                    min_date_to_set = h_deliverydate_session;
                }
            }        
			
            jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).datepicker( "setDate", date_to_set );
            jQuery( "#h_deliverydate" ).val( min_date_to_set );
            var inst = jQuery.datepicker._getInst( jQuery( "#e_deliverydate" )[0] );
            if( jQuery( "#orddd_enable_shipping_based_delivery" ).val() == "on" ) {
                show_times_custom( min_date_to_set, inst );
            } else {
                show_times( min_date_to_set, inst );
            }
            
            jQuery( "body" ).trigger( "update_checkout" );
            if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
                jQuery( "#hidden_e_deliverydate" ).val( jQuery( "#e_deliverydate" ).val() );
                jQuery( "#hidden_h_deliverydate" ).val( min_date_to_set );
                jQuery( "#hidden_shipping_method" ).val( orddd_get_selected_shipping_method() );
                jQuery( "#hidden_shipping_class" ).val( jQuery( "#orddd_shipping_class_settings_to_load" ).val() );
                jQuery( "body" ).trigger( "wc_update_cart" );
            }
        }
    }
}

/**
 * Loads the Delivery information from the Local storage
 *
 * @function load_functions
 * @memberof orddd_initialize_functions
 * @since 7.0
 */
function load_functions() {
    var orddd_location_session = localStorage.getItem( 'orddd_location_session' );    
    if( typeof( orddd_location_session ) != 'undefined' && orddd_location_session != '' && orddd_location_session != 'null' ) {
        jQuery( "#orddd_locations" ).val( orddd_location_session ).trigger( "change" );    
    }

    var shipping_method = orddd_get_selected_shipping_method();
    var shipping_method_to_check = shipping_method;
    
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
        
        if( jQuery( '#orddd_enable_shipping_based_delivery' ).val() == "on" ) {
            load_delivery_date();
        }

        if( jQuery( "#orddd_enable_autofill_of_delivery_date" ).val() == "on" ) {
            orddd_autofil_date_time();
        }

        var e_deliverydate_session = localStorage.getItem( 'e_deliverydate_session' );
        if( typeof( e_deliverydate_session ) != 'undefined' && e_deliverydate_session != '' ) {
            var h_deliverydate_session = localStorage.getItem( 'h_deliverydate_session' );
            if ( h_deliverydate_session ) {
                var default_date_arr = h_deliverydate_session.split( '-' );
                var default_date = new Date( default_date_arr[ 1 ] + '/' + default_date_arr[ 0 ] + '/' + default_date_arr[ 2 ] );
                var show = jQuery( "#orddd_show_datepicker" ).val();
                if( 'datetimepicker' == show ) {
                    var time = e_deliverydate_session.split( ' ' );
                    var time_value = time[time.length-1];
                    if( time_value.indexOf( ':' ) !== -1 ) {
                        // Set the Hours & minutes to be prepopulated in the time slider
                        var time_arr = time_value.split( ":" );
                        default_date.setHours( time_arr[0], time_arr[1] );
                        jQuery( "#orddd_time_settings_selected" ).val( time_value );    
                    }                    
                }

                jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).datepicker( "setDate", default_date );
                jQuery( "#h_deliverydate" ).val( h_deliverydate_session );

                jQuery( "body" ).trigger( "update_checkout" );
                if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
                    jQuery( "#hidden_e_deliverydate" ).val( jQuery( "#e_deliverydate" ).val() );
                    jQuery( "#hidden_h_deliverydate" ).val( h_deliverydate_session );
                    jQuery( "#hidden_timeslot" ).val( jQuery( "#time_slot" ).find( ":selected" ).val() );
                    jQuery( "#hidden_shipping_method" ).val( shipping_method );
                    jQuery( "#hidden_shipping_class" ).val( jQuery( "#orddd_shipping_class_settings_to_load" ).val() );
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
    });
}

/**
 * Concatenation of options for jQuery datepicker
 *
 * @function jsonConcat
 * @memberof orddd_initialize_functions
 * @param {string} o1 - Options of datepicker
 * @param {string} o2 - Options of datepicker
 * @returns {string} o1 - Concatenation of two Options o1 and o2
 * @since 1.0
 */
function jsonConcat( o1, o2 ) {
    for ( var key in o2 ) {
        o1[ key ] = o2[ key ];
    }
    return o1;
}

/**
 * Calculates the Minimum date to be set in the calendar where holiday, lockout, 
 * specific dates, Minimum Delivery time etc., is considered.
 *
 * @function minimum_date_to_set
 * @memberof orddd_initialize_functions
 * @param {object} delay_days - Delay Day
 * @returns {object} delay_days - Delay Days
 * @since 1.0
 */
function minimum_date_to_set( delay_days ) {
    var disabledDays = eval( "[" + jQuery( "#orddd_delivery_date_holidays" ).val() + "]" );
    var holidays = [];
    for ( i = 0; i < disabledDays.length; i++ ) {
        var holidays_array = disabledDays[ i ].split( ":" );
        holidays[i] = holidays_array[ 1 ];
    }
    
    var bookedDays = eval( "[" + jQuery( "#orddd_lockout_days" ).val() + "]" );
    
    var current_date = jQuery( "#orddd_current_day" ).val();
    var split_current_date = current_date.split( "-" );
    var current_day = new Date ( split_current_date[ 1 ] + "/" + split_current_date[ 0 ] + "/" + split_current_date[ 2 ] );
    
    var delay_time = delay_days.getTime();
    var current_time = current_day.getTime();
    var current_weekday = current_day.getDay();
    
    var delivery_day_3 = '';
    var specific_dates_sorted_array = new Array ();
    var specific_dates = jQuery( "#orddd_specific_delivery_dates" ).val();
    var deliveryDates = [];
    var delivery_dates_to_check = [];

    var is_all_past_dates = 'No';
    var is_all_holidays = 'No';
    var is_all_booked_days = 'No';

    var past_dates = [];
    var highest_delivery_date = [];
    var specific_days_in_holidays = 0;
    var specific_days_in_booked_days = 0;
    if ( specific_dates == "on" ) {
        if ( jQuery( "#orddd_delivery_dates" ).val()  != '' ) {
            deliveryDates = eval( '[' + jQuery( "#orddd_delivery_dates" ).val() + ']');
            delivery_dates_to_check = eval( '[' + jQuery( "#orddd_delivery_dates" ).val() + ']');
            for ( sort = 0; sort < deliveryDates.length; sort++ ) {
                var split_delivery_date_1 = deliveryDates[sort].split( "-" );
                var delivery_day_1 = new Date ( split_delivery_date_1[ 0 ] + "/" + split_delivery_date_1[ 1 ] + "/" + split_delivery_date_1[ 2 ] );
                specific_dates_sorted_array[sort] = delivery_day_1.getTime();
            }
            
            highest_delivery_date = specific_dates_sorted_array[ specific_dates_sorted_array.length - 1 ];

            specific_dates_sorted_array.sort( sortSpecificDates );
            for ( i = 0; i < specific_dates_sorted_array.length; i++ ) {
                if ( specific_dates_sorted_array[i] >= current_day.getTime() ){
                    delivery_day_3 = specific_dates_sorted_array[i];
                    break;
                }
            }
            
            for ( j = 0; j < deliveryDates.length; j++ ) {
                var split_delivery_date = deliveryDates[j].split( "-" );
                var delivery_date = new Date ( split_delivery_date[ 0 ] + "/" + split_delivery_date[ 1 ] + "/" + split_delivery_date[ 2 ] );
                if ( delivery_date.getTime() >= current_day.getTime() ){
                    past_dates[j] = deliveryDates[j];
                }

                if( jQuery.inArray( deliveryDates[j], holidays ) >= 0 ) {
                    specific_days_in_holidays++;
                } else if( jQuery.inArray( deliveryDates[j], holidays ) == -1 && jQuery.inArray( deliveryDates[j], past_dates ) == -1 ) {
                    specific_days_in_holidays++;
                }

                if( jQuery.inArray( deliveryDates[j], bookedDays ) >= 0 ) {
                    specific_days_in_booked_days++;
                } else if( jQuery.inArray( deliveryDates[j], bookedDays ) == -1 && jQuery.inArray( deliveryDates[j], past_dates ) == -1 ) {
                    specific_days_in_booked_days++;
                }
            }       

            if( past_dates.length == 0 ) {
                is_all_past_dates = 'Yes';
            }

            if( specific_days_in_holidays == deliveryDates.length ) {
                is_all_holidays = 'Yes';
            }

            if( specific_days_in_booked_days == deliveryDates.length ) {
                is_all_booked_days = 'Yes';
            }
        } else {
            is_all_past_dates = 'Yes';
        }
    }

    var j;
    if( 'on' == jQuery( '#orddd_enable_shipping_days' ).val() ) {
        var delay_weekday = delay_days.getDay();

        var delay_date_to_check = jQuery( "#orddd_minimumOrderDays" ).val();
        if( delay_date_to_check != "" ) {
            var split_date_to_check = delay_date_to_check.split( "-" );
            var delay_days_to_check = new Date ( split_date_to_check[ 1 ] + "/" + split_date_to_check[ 0 ] + "/" + split_date_to_check[ 2 ] );
        }

        var delay_time_to_check = delay_days_to_check.getTime();
        var delay_weekday_to_check = delay_days_to_check.getDay();
        for ( j = delay_weekday_to_check ; delay_time_to_check <= delay_time ; j++ ) {
            if( j >= 0 ) {
                day = "orddd_weekday_" + delay_weekday_to_check;
                day_check = jQuery( "#" + day ).val();
                if ( day_check == "" || typeof day_check == "undefined" ) {
                    var increment_delay_day = 'no';
                    if ( jQuery( "#orddd_specific_delivery_dates" ).val() == "on" ) {
                        if( ( 'Yes' ==  is_all_past_dates || 'Yes' == is_all_holidays || 'Yes' == is_all_booked_days ) && 'yes' == jQuery( "#orddd_is_all_weekdays_disabled" ).val() ) {
                            delay_days = '';
                            break;
                        } else if( ( 'Yes' ==  is_all_past_dates || 'Yes' == is_all_holidays || 'Yes' == is_all_booked_days ) && 'no' == jQuery( "#orddd_is_all_weekdays_disabled" ).val() ) {
                            increment_delay_day = 'yes';
                        } else {
                            var m = current_day.getMonth(), d = current_day.getDate(), y = current_day.getFullYear();
                            if( jQuery.inArray( ( m+1 ) + "-" + d + "-" + y, deliveryDates ) == -1 && 'no' == jQuery( "#orddd_is_all_weekdays_disabled" ).val() ) {
                                increment_delay_day = 'yes';
                            } else if ( typeof delivery_day_3 != "undefined" && delivery_day_3 != '' && delay_days != '' ) {
                                if( delivery_day_3 != delay_days.getTime() && delay_days.getTime() < delivery_day_3 ) {
                                    delay_days.setDate( delay_days.getDate()+1 );
                                    delay_time = delay_days.getTime();
                                    delay_weekday = delay_days.getDay();
                                } else {
                                    break;
                                }
                            } else {
                                break;
                            }
                        }
                    } else {
                        increment_delay_day = 'yes';
                    }
                    if( 'yes' == increment_delay_day ) {
                        delay_days.setDate( delay_days.getDate()+1 );
                        delay_time = delay_days.getTime();
                        delay_days_to_check.setDate( delay_days_to_check.getDate()+1 );
                        delay_time_to_check = delay_days_to_check.getTime();
                        delay_weekday_to_check = delay_days_to_check.getDay();
                    }
                } else {
                    if( delay_days_to_check <= delay_days ) {
                        var m = delay_days_to_check.getMonth(), d = delay_days_to_check.getDate(), y = delay_days_to_check.getFullYear();
                        if ( jQuery( "#orddd_disable_for_holidays" ).val() != 'yes' ) {
                            if( jQuery.inArray( ( m+1 ) + "-" + d + "-" + y, holidays ) != -1 || jQuery.inArray( ( m+1 ) + "-" + d + "-" + y, bookedDays ) != -1 ) {
                                delay_days.setDate( delay_days.getDate()+1 );
                                delay_time = delay_days.getTime();
                            }
                        }
                        delay_days_to_check.setDate( delay_days_to_check.getDate()+1 );
                        delay_time_to_check = delay_days_to_check.getTime();
                        delay_weekday_to_check = delay_days_to_check.getDay();
                    }
                }
            } else {
                break;
            }
        }
    } 
    
    if( delay_days != '' ) {
        var dm = delay_days.getMonth(), dd = delay_days.getDate(), dy = delay_days.getFullYear();
        if( jQuery.inArray( ( dm+1 ) + "-" + dd + "-" + dy, holidays ) != -1 ) {
            delay_days.setDate( delay_days.getDate()+1 );
            delay_time = delay_days.getTime();
        }

        if( jQuery.inArray( ( dm+1 ) + "-" + dd + "-" + dy, bookedDays ) != -1 ) {
            delay_days.setDate( delay_days.getDate()+1 );
            delay_time = delay_days.getTime();
        } 
    }

    var common_delivery_days = [];
    if( typeof( jQuery( "#orddd_common_delivery_days_for_product_category" ).val() ) !== "undefined" && jQuery( "#orddd_common_delivery_days_for_product_category" ).val() != '' ) {
        common_delivery_days_str = jQuery( "#orddd_common_delivery_days_for_product_category" ).val();
        common_delivery_days = jQuery.parseJSON( common_delivery_days_str );
    }

    var specific_dates = [];
    if( typeof( jQuery( "#orddd_common_delivery_dates_for_product_category" ).val() ) !== "undefined" && jQuery( "#orddd_common_delivery_dates_for_product_category" ).val() != '' ) {
        specific_dates = eval( '[' + jQuery( "#orddd_common_delivery_dates_for_product_category" ).val() + ']' );
    }

    var disabled_common_days = [];
    if( typeof( jQuery( "#orddd_holidays_for_product_category" ).val() ) !== "undefined" && jQuery( "#orddd_holidays_for_product_category" ).val() != '' ) {
        disabled_common_days = eval( '[' + jQuery( "#orddd_holidays_for_product_category" ).val() + ']' );
    }

    //var dm = delay_days.getMonth(), dd = delay_days.getDate(), dy = delay_days.getFullYear();
    for( i = 0; ;i++ ) {
        var dm = delay_days.getMonth(), dd = delay_days.getDate(), dy = delay_days.getFullYear();
        var delay_weekday = delay_days.getDay();
        if( jQuery( "#orddd_is_days_common" ).val() == 'yes' && 
            ( ( specific_dates.length > 0 && 
                    jQuery.inArray( ( dm+1 ) + "-" + dd + "-" + dy, specific_dates ) == -1 ) || 
                specific_dates.length == 0 
            ) && 
            ( jQuery.isEmptyObject( common_delivery_days ) == true || 
                ( jQuery.isEmptyObject( common_delivery_days ) == false && 
                    !common_delivery_days.hasOwnProperty( "orddd_weekday_" + delay_weekday ) 
                ) 
            ) 
        ) {
            delay_days.setDate( delay_days.getDate()+1 );
        } else if( jQuery( "#orddd_categories_settings_common" ).val() == 'yes' && jQuery( "#orddd_is_days_common" ).val() == 'no' ) {
            delay_days = '';
        } else if( 'yes' == jQuery( "#orddd_is_all_weekdays_disabled" ).val() && 
            delay_days.getTime() < highest_delivery_date && 
            jQuery.inArray( ( dm+1 ) + "-" + dd + "-" + dy, specific_dates ) != -1 ) {
            delay_days.setDate( delay_days.getDate()+1 );
        } else if( ( 'Yes' ==  is_all_past_dates || 'Yes' == is_all_holidays || 'Yes' == is_all_booked_days ) && 'yes' == jQuery( "#orddd_is_all_weekdays_disabled" ).val() ) {
            delay_days = '';  
            break;
        } else {
            break;
        }
    }
      
    return delay_days;
}

/**
 * Calculates the date to be set in the calendar after the checking the Same day and Next day cut-off time.
 *
 * @function same_day_next_day_to_set
 * @memberof orddd_initialize_functions
 * @param {object} current_day - Current Day
 * @returns {object} current_day - Date to be set in the calendar
 * @since 1.0
 */
function same_day_next_day_to_set( current_day ) {
    var startDaysDisabled = eval( "[" + jQuery( "#orddd_disabled_days_str" ).val() + "]" );

    var disabledDays = eval( "[" + jQuery( "#orddd_delivery_date_holidays" ).val() + "]" );
    var holidays = [];
    for ( i = 0; i < disabledDays.length; i++ ) {
        var holidays_array = disabledDays[ i ].split( ":" );
        holidays[i] = holidays_array[ 1 ];
    }
    
    var bookedDays = eval( "[" + jQuery( "#orddd_lockout_days" ).val() + "]" );
    var delivery_day_3 = '';
    var specific_dates_sorted_array = new Array();
    var specific_dates = jQuery( "#orddd_specific_delivery_dates" ).val();
    var is_all_past_dates = 'No';
    if ( specific_dates == "on" ) {
         if ( jQuery( "#orddd_delivery_dates" ).val()  != '' ) {
            var deliveryDates = eval( '[' + jQuery( "#orddd_delivery_dates" ).val() + ']');
            for ( sort = 0; sort < deliveryDates.length; sort++ ) {
                var split_delivery_date_1 = deliveryDates[sort].split( "-" );
                var delivery_day_1 = new Date ( split_delivery_date_1[ 0 ] + "/" + split_delivery_date_1[ 1 ] + "/" + split_delivery_date_1[ 2 ] );
                specific_dates_sorted_array[sort] = delivery_day_1.getTime();
            }
            specific_dates_sorted_array.sort( sortSpecificDates );
            for ( i = 0; i < specific_dates_sorted_array.length; i++ ) {
                if ( specific_dates_sorted_array[i] >= current_day.getTime() ){
                    delivery_day_3 = specific_dates_sorted_array[i];
                    break;
                }
            }   
			var highest_delivery_date = specific_dates_sorted_array[ specific_dates_sorted_array.length - 1 ];
            var past_dates = [];
            for ( j = 0; j < deliveryDates.length; j++ ) {
                var split_delivery_date = deliveryDates[j].split( "-" );
                var delivery_date = new Date ( split_delivery_date[ 0 ] + "/" + split_delivery_date[ 1 ] + "/" + split_delivery_date[ 2 ] );
                if ( delivery_date.getTime() >= current_day.getTime() ){
                    past_dates[j] = deliveryDates[j];
                }
            }           
            if( past_dates.length == 0 ) {
                is_all_past_dates = 'Yes';
            }       
        } else {
            is_all_past_dates = 'Yes';
        }
    }
    
    if( current_day != '' ) {
        var current_weekday = current_day.getDay();
        var k;
        if( jQuery( "#orddd_next_day_delivery" ).val() == 'on' 
            && 'undefined' == typeof jQuery( "#orddd_dynamic_hidden_vars #orddd_custom_based_next_day_delivery" ).val()
            && ( jQuery( "#is_sameday_cutoff_reached" ).val() == 'yes' 
                || 'undefined' == typeof jQuery( "#is_sameday_cutoff_reached" ).val() ) ) {
            for ( k = current_weekday ; k <= 6; ) {
                if( jQuery( "#is_nextday_cutoff_reached" ).val() == 'yes' )  {
                    if( typeof( jQuery( '#orddd_after_cutoff_weekday' ).val() ) != "undefined" && jQuery( '#orddd_after_cutoff_weekday' ).val() != '' ) {
                        var weekday = "orddd_weekday_" + current_day.getDay();
                        var after_weekday = jQuery( '#orddd_after_cutoff_weekday' ).val();
                        if( weekday != after_weekday ) {
                            current_day.setDate( current_day.getDate()+1 );
                            k = current_day.getDay();
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }   
                } else {
                    if( typeof( jQuery( '#orddd_before_cutoff_weekday' ).val() ) != "undefined" && jQuery( '#orddd_before_cutoff_weekday' ).val() != '' ) {
                        var weekday = "orddd_weekday_" + current_day.getDay();
                        var before_weekday = jQuery( '#orddd_before_cutoff_weekday' ).val();
                        if( weekday != before_weekday ) {
                            current_day.setDate( current_day.getDate()+1 );
                            k = current_day.getDay();
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }
                }
            }
        }
    }
    
    var current_time = current_day.getTime();
    var current_weekday = current_day.getDay();
    var j;
   
    for ( j = current_weekday ;  j <= 6; ) {
        var m = current_day.getMonth(), d = current_day.getDate(), y = current_day.getFullYear();
        if( jQuery.inArray( ( m+1 ) + '-' + d + '-' + y, startDaysDisabled ) != -1 ) {
            current_day.setDate( current_day.getDate()+1 );
            j = current_day.getDay();
        } else if( jQuery.inArray( ( m+1 ) + '-' + d + '-' + y, bookedDays ) != -1 ) {
            current_day.setDate( current_day.getDate()+1 );
            j = current_day.getDay();			
        } else if( jQuery.inArray( ( m+1 ) + "-" + d + "-" + y, holidays ) != -1 ) {
            current_day.setDate( current_day.getDate()+1 );
            j = current_day.getDay();
        } else {
            var shipping_day_check = '';
            if( jQuery( '#orddd_enable_shipping_days' ).val() == 'on' ) {
                shipping_day = 'orddd_weekday_' + j;
                shipping_day_check = jQuery( "#" + shipping_day ).val();
                if( typeof shipping_day_check == "undefined" ) {
                    shipping_day_check = '';
                }
                if( ( shipping_day_check == "" || typeof shipping_day_check == "undefined" ) && jQuery( '#orddd_enable_shipping_days' ).val() == 'on' ) {
                    if ( jQuery( "#orddd_specific_delivery_dates" ).val() == "on" ) {
                        if( is_all_past_dates != 'Yes' || ( 'Yes' ==  is_all_past_dates && 'no' == jQuery( "#orddd_is_all_weekdays_disabled" ).val() ) ) {
                            if ( typeof delivery_day_3 != "undefined" ) {
                                if ( delivery_day_3 != current_day.getTime() && current_day.getTime() < delivery_day_3 ) {
                                    current_day.setDate( current_day.getDate()+1 );
                                    j = current_day.getDay();
                                } else {
                                    break;
                                }
                            } else {
                                break;
                            }    
                        } else {
                            current_day.setDate( current_day.getDate()+1 );
                            j = current_day.getDay();    
                            break;
                        }
                    } else {
                        current_day.setDate( current_day.getDate()+1 );
                        j = current_day.getDay();
                    }   
                } else {
                    break;
                }
            } else {
                day = "orddd_weekday_" + j;
                day_check = jQuery( "#" + day ).val();
                if ( day_check == "" || typeof day_check == "undefined" ) {
                    var increment_delay_day = 'no';
                    if ( jQuery( "#orddd_specific_delivery_dates" ).val() == "on" ) {
                        if( 'Yes' ==  is_all_past_dates && 'yes' == jQuery( "#orddd_is_all_weekdays_disabled" ).val() ) {
                            current_day = '';
                            break;
                        } else if( 'Yes' ==  is_all_past_dates && 'no' == jQuery( "#orddd_is_all_weekdays_disabled" ).val() ) {
                            increment_delay_day = 'yes';
                        } else {
                            var m = current_day.getMonth(), d = current_day.getDate(), y = current_day.getFullYear();
							highest_delivery_date
                            if( jQuery.inArray( ( m+1 ) + "-" + d + "-" + y, deliveryDates ) == -1 && current_day.getTime() < highest_delivery_date ) {
                                increment_delay_day = 'yes';
                            } else if ( typeof delivery_day_3 != "undefined" && delivery_day_3 != '' ) {
                                if ( delivery_day_3 != current_day.getTime() && current_day.getTime() < delivery_day_3 && current_day.getTime() < highest_delivery_date ) {
                                     increment_delay_day = 'yes';
                                } else {
									if( 'yes' == jQuery( "#orddd_is_all_weekdays_disabled" ).val() && current_day.getTime() > highest_delivery_date ) {
										current_day = '';
									}
                                    break;
                                }
                            } else {
                                break;
                            }
                        }
                    } else {
                        increment_delay_day = 'yes';
                    }

                    if( 'yes' == increment_delay_day ) {
                        current_day.setDate( current_day.getDate()+1 );
                        j = current_day.getDay();
                    }
                } else {
                    break;
                }
            }
        } 
    }
	
	if( '' != current_day ) {
		var current_date = current_day.getDate() + "-" + ( current_day.getMonth()+1 ) + "-" + current_day.getFullYear();
		if( current_date == jQuery( "#orddd_current_day" ).val() && "on" == jQuery( "#orddd_enable_time_slider" ).val() && jQuery( "#orddd_max_hour_set" ).val() < jQuery( "#orddd_current_hour" ).val() ) {
			current_day.setDate( current_day.getDate()+1 );
		}
	}
	
    return current_day;
}

/**
 * Shows the Global Time Slots
 *
 * @function show_times
 * @memberof orddd_initialize_functions
 * @param {date} date - Date
 * @param {object} inst 
 * @since 1.0
 */
function show_times( date, inst ) {
    var monthValue = inst.selectedMonth+1;
    var dayValue = inst.selectedDay;
    var yearValue = inst.selectedYear;
    var all = dayValue + "-" + monthValue + "-" + yearValue;

    if( jQuery( "#orddd_enable_time_slot" ).val() == "on" ) {
        if( typeof( inst.id ) !== "undefined" ) {  
            var data = {
                current_date: all,
                order_id: jQuery( "#orddd_my_account_order_id" ).val(),
                min_date: jQuery( "#orddd_min_date_set" ).val(),
                current_date_to_check: jQuery( "#orddd_current_date_set" ).val(),
                time_slot_session: localStorage.getItem( "time_slot" ),
                holidays_str: jQuery( "#orddd_delivery_date_holidays" ).val(),
                lockout_str: jQuery( "#orddd_lockout_days" ).val(),
                action: "check_for_time_slot_orddd"
            };

            var option_selected = jQuery( '#orddd_auto_populate_first_available_time_slot' ).val();
            jQuery( "#time_slot" ).attr( "disabled", "disabled" );
            jQuery( "#time_slot_field" ).attr( "style", "opacity: 0.5" );
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                jQuery( "#time_slot_field" ).attr( "style", "opacity: 1" );
                if( jQuery( "#orddd_is_cart" ).val() == 1 ) {
                    jQuery( "#time_slot" ).attr( "style", "cursor: pointer !important;max-width:300px" );
                } else {
                    jQuery( "#time_slot" ).attr( "style", "cursor: pointer !important" );
                }
                jQuery( "#time_slot" ).removeAttr( "disabled" ); 

                orddd_load_time_slots( response );

                if( option_selected == "on" || ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() && localStorage.getItem( "time_slot" ) != '' ) ) {
                    jQuery( "body" ).trigger( "update_checkout" );
                    if ( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
                        jQuery( "#hidden_e_deliverydate" ).val( jQuery( "#e_deliverydate" ).val() );
                        jQuery( "#hidden_h_deliverydate" ).val( all );
                        jQuery( "#hidden_timeslot" ).val( jQuery( "#time_slot" ).val() );
                        jQuery( "body" ).trigger( "wc_update_cart" );
                    }
                }  
            });
        }
    } else if( jQuery( "#orddd_enable_time_slider" ).val() == "on" ) { 
        if( typeof( inst.id ) !== "undefined" ) {  
            var tp_inst = jQuery.datepicker._get( inst, "timepicker" );
            var orddd_disable_minimum_delivery_time_slider = jQuery( "#orddd_disable_minimum_delivery_time_slider" ).val();
            var is_min_hour_set = 'no';
            if( ( all == jQuery( "#orddd_current_day" ).val() || all == jQuery( "#orddd_min_date_set" ).val() ) && ( jQuery( "#orddd_same_day_delivery" ).val() != 'on' && jQuery( "#orddd_next_day_delivery" ).val() != 'on' ) ) {
                is_min_hour_set = 'yes';
            } else if( all == jQuery( "#orddd_current_day" ).val() && ( jQuery( "#orddd_same_day_delivery" ).val() == 'on' || jQuery( "#orddd_next_day_delivery" ).val() == 'on' ) ) {
                is_min_hour_set = 'yes';
            } 
        
            if( 'yes' != orddd_disable_minimum_delivery_time_slider ) {
                if( 'yes' == is_min_hour_set ) {
                    var time_format = jQuery( '#orddd_delivery_time_format' ).val();
                    var split = tp_inst.formattedTime.split( ":" );
                    if( time_format == "1" ) {
                        if( "PM".indexOf( split[ 1 ] ) !== -1 ) {
                            var hour_time  = parseInt( split[ 0 ] ) + parseInt( 12 );    
                        } else {
                            var hour_time = parseInt( split[ 0 ] );
                        }
                    } else {
                        var hour_time  = parseInt( split[ 0 ] );
                    }                      

                    inst.settings.hourMin = parseInt( jQuery( "#orddd_min_hour" ).val() );
                    tp_inst._defaults.hourMin = parseInt( jQuery( "#orddd_min_hour" ).val() );

                    if( hour_time == parseInt( jQuery( "#orddd_min_hour" ).val() ) ) {
                        inst.settings.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                        tp_inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    } else {
                        inst.settings.minuteMin = 0;
                        tp_inst._defaults.minuteMin = 0;
                    }
                    tp_inst._limitMinMaxDateTime(inst, true);
                } else {
                    inst.settings.hourMin = parseInt( jQuery( "#orddd_delivery_from_hours" ).val() );
                    tp_inst._defaults.hourMin = parseInt( jQuery( "#orddd_delivery_from_hours" ).val() );
                    inst.settings.minuteMin = 0;
                    tp_inst._defaults.minuteMin = 0;
                    tp_inst._limitMinMaxDateTime(inst, true);
                }
            } else {
                var time_format = jQuery( '#orddd_delivery_time_format' ).val();
                var split = tp_inst.formattedTime.split( ":" );
                if( time_format == "1" ) {
                    if( "PM".indexOf( split[ 1 ] ) !== -1 ) {
                        var hour_time  = parseInt( split[ 0 ] ) + parseInt( 12 );    
                    } else {
                        var hour_time = parseInt( split[ 0 ] );
                    }
                } else {
                    var hour_time  = parseInt( split[ 0 ] );
                }  
                inst.settings.hourMin = parseInt( jQuery( "#orddd_delivery_from_hours" ).val() );
                tp_inst._defaults.hourMin = parseInt( jQuery( "#orddd_delivery_from_hours" ).val() );
                inst.settings.minuteMin = 0;
                tp_inst._defaults.minuteMin = 0;
                tp_inst._limitMinMaxDateTime(inst, true);
            }
            jQuery.datepicker._updateDatepicker(inst);
        } else if( typeof( inst.inst.id ) !== "undefined" )  {
            var monthValue = inst.inst.currentMonth+1;
            var dayValue = inst.inst.currentDay;
            var yearValue = inst.inst.currentYear;
            var all = dayValue + "-" + monthValue + "-" + yearValue;

            var is_min_hour_set = 'no';
            if( ( all == jQuery( "#orddd_current_day" ).val() || all == jQuery( "#orddd_min_date_set" ).val() ) && ( jQuery( "#orddd_same_day_delivery" ).val() != 'on' && jQuery( "#orddd_next_day_delivery" ).val() != 'on' ) ) {
                is_min_hour_set = 'yes';
            } else if( all == jQuery( "#orddd_current_day" ).val() && ( jQuery( "#orddd_same_day_delivery" ).val() == 'on' || jQuery( "#orddd_next_day_delivery" ).val() == 'on' ) ) {
                is_min_hour_set = 'yes';
            } 

            var tp_inst = jQuery.datepicker._get( inst.inst, "timepicker" );
            if( 'yes' == is_min_hour_set ) {
                var time_format = jQuery( '#orddd_delivery_time_format' ).val();
                var split = inst.formattedTime.split( ":" );
                if( time_format == "1" ) {
                    if( "PM".indexOf( split[ 1 ] ) !== -1 ) {
                        var hour_time  = parseInt( split[ 0 ] ) + parseInt( 12 );    
                    } else {
                        var hour_time = parseInt( split[ 0 ] );
                    }
                } else {
                    var hour_time  = parseInt( split[ 0 ] );
                }
                if( hour_time == parseInt( jQuery( "#orddd_min_hour" ).val() ) ) {
                    inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    inst.inst.settings.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    tp_inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    tp_inst._limitMinMaxDateTime( inst.inst, true );
                } else {
                    inst._defaults.minuteMin = 0;
                    inst.inst.settings.minuteMin = 0;
                    tp_inst._defaults.minuteMin = 0;
                    tp_inst._limitMinMaxDateTime( inst.inst, true );
                }
            }
            jQuery.datepicker._updateDatepicker(inst.inst);
        }
    }

    if( 'on' == jQuery( '#orddd_delivery_date_on_cart_page' ).val() ) {
        localStorage.setItem( "e_deliverydate_session", jQuery( "#e_deliverydate" ).val() );
        localStorage.setItem( "h_deliverydate_session", all );
        if( localStorage.getItem( "time_slot" ) == null ) {
            localStorage.setItem( "time_slot", jQuery( "#time_slot" ).find( ":selected" ).val() );
        } 

        var current_date = jQuery( "#orddd_current_day" ).val();
        var split_current_date = current_date.split( '-' );
        var ordd_next_date = new Date( split_current_date[ 2 ], ( split_current_date[ 1 ] - 1 ), split_current_date[ 0 ], jQuery( "#orddd_current_hour" ).val(), jQuery( "#orddd_current_minute" ).val() );

        ordd_next_date.setHours( ordd_next_date.getHours() + 2 );
        localStorage.setItem( "orddd_storage_next_time", ordd_next_date.getTime() );
    }
}

/**
 * Decodes the html entities for currency symbol.
 *
 * @function decodeHtml
 * @param {string} html - String to decode
 * @returns {string} Decoded string.
 * @since 8.0
 */
function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

/**
 * Shows the Time Slots in the admin Orders page
 *
 * @function show_admin_times
 * @memberof orddd_initialize_functions
 * @param {date} date - Date
 * @param {object} inst 
 * @since 3.2
 */
function show_admin_times( date, inst ) {
    var shipping_class = "";
    var shipping_method_id = jQuery( "input[name=\"shipping_method_id[]\"]" ).val();
    if( typeof shipping_method_id === "undefined" ) {
        var shipping_method_id = "";
    }
    var shipping_method = jQuery( "select[name=\"shipping_method[" + shipping_method_id + "]\"]" ).find(":selected").val();
    if( typeof shipping_method === "undefined" ) {
        var shipping_method = "";
    }
    
    var hidden_var_obj = jQuery("#orddd_hidden_vars_str").val();
    var html_vars_obj = jQuery.parseJSON( hidden_var_obj );
    if( html_vars_obj == null ) {
        html_vars_obj = [];
    } 

    var time_enable = "";
    jQuery.each( html_vars_obj, function( key, value ) {
        if( typeof value.shipping_methods !== "undefined" ) {
            var shipping_methods = value.shipping_methods.split(",");
            for( i = 0; i < shipping_methods.length; i++ ) {
                if( shipping_method.indexOf( shipping_methods[ i ] ) !== -1 ) {
                    shipping_method = shipping_methods[ i ];
                }
            }
            var shipping_class = jQuery( "#orddd_shipping_class_settings_to_load" ).val(); 
        } else if ( typeof value.orddd_pickup_locations !== "undefined" ) {
            var shipping_methods = value.orddd_pickup_locations.split(",");
            for( i = 0; i < shipping_methods.length; i++ ) {
                if( shipping_method.indexOf( shipping_methods[ i ] ) !== -1 ) {
                    shipping_method = shipping_methods[ i ];
                }
            }
        } else {
            var shipping_methods = value.product_categories.split(",");
            shipping_method = jQuery( "#orddd_category_settings_to_load" ).val();
            shipping_class = "";
        }        

        if( shipping_method.indexOf( 'wf_fedex_woocommerce_shipping' ) === -1 && shipping_method.indexOf( 'fedex' ) !== -1 && ( shipping_method.split( ":" ).length ) < 3 ) {
            shipping_method = jQuery( "#orddd_zone_id" ).val() + ":" + shipping_method;
        }

        if ( jQuery.inArray( shipping_method, shipping_methods ) !== -1 || jQuery.inArray( shipping_class, shipping_methods ) !== -1 ) {
            if ( value.time_slots == "on" ) {
                time_enable = value.time_slots;    
            } 
        }
    });

    var monthValue = inst.selectedMonth+1;
    var dayValue = inst.selectedDay;
    var yearValue = inst.selectedYear;
    var all = dayValue + "-" + monthValue + "-" + yearValue;

    if( jQuery( "#orddd_enable_time_slot" ).val() == "on" || jQuery( "#time_slot_enable_for_shipping_method" ).val() == "on" ) {
        if( typeof( inst.id ) !== "undefined" ) {  
            var data = {
                current_date: all,
                shipping_method: shipping_method,
                shipping_class: shipping_class,
                order_id: jQuery( "#orddd_order_id" ).val(),
                min_date: jQuery( "#orddd_min_date_set" ).val(),
                current_date_to_check: jQuery( "#orddd_current_date_set" ).val(),
                time_slot_session: localStorage.getItem( "time_slot" ),
                holidays_str: jQuery( "#orddd_delivery_date_holidays" ).val(),
                lockout_str: jQuery( "#orddd_lockout_days" ).val(),
                action: "check_for_time_slot_orddd",
                admin: true,
            };

            jQuery( "#time_slot" ).attr("disabled", "disabled");
            jQuery( "#time_slot_field" ).attr( "style", "opacity: 0.5" );
            jQuery.post( jQuery( '#orddd_admin_url' ).val() + "admin-ajax.php", data, function( response ) {
                jQuery( "#time_slot_field" ).attr( "style" ,"opacity:1" );
                if( jQuery( "#orddd_is_cart" ).val() == 1 ) {
                    jQuery( "#time_slot" ).attr( "style", "cursor: pointer !important;max-width:300px" );
                } else {
                    jQuery( "#time_slot" ).attr( "style", "cursor: pointer !important" );
                }
                jQuery( "#time_slot" ).removeAttr( "disabled" ); 
                
                orddd_load_time_slots( response );
            });
        }
    } else if( jQuery( "#orddd_enable_time_slider" ).val() == "on" ) {
        if( typeof( inst.id ) !== "undefined" ) {  
            var tp_inst = jQuery.datepicker._get( inst, "timepicker" );
            if( ( all == jQuery( "#orddd_current_day" ).val() || all == jQuery( "#orddd_min_date_set" ).val() ) && ( jQuery( "#orddd_same_day_delivery" ).val() != 'on' && jQuery( "#orddd_next_day_delivery" ).val() != 'on' && jQuery( "#orddd_custom_based_same_day_delivery" ).val() != "on" && jQuery( "#orddd_custom_based_next_day_delivery" ).val() != "on" ) ) {
                inst.settings.hourMin = parseInt( jQuery( "#orddd_min_hour" ).val() );
                tp_inst._defaults.hourMin = parseInt( jQuery( "#orddd_min_hour" ).val() );
                inst.settings.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );;
                tp_inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );;
                tp_inst._limitMinMaxDateTime(inst, true);
            } else {
                inst.settings.hourMin = parseInt( jQuery( "#orddd_delivery_from_hours" ).val() );
                tp_inst._defaults.hourMin = parseInt( jQuery( "#orddd_delivery_from_hours" ).val() );
                inst.settings.minuteMin = 0;
                tp_inst._defaults.minuteMin = 0;
                tp_inst._limitMinMaxDateTime(inst, true);
            }
            jQuery.datepicker._updateDatepicker(inst);
        } else if( typeof( inst.inst.id ) !== "undefined" )  {
            var monthValue = inst.inst.currentMonth+1;
            var dayValue = inst.inst.currentDay;
            var yearValue = inst.inst.currentYear;
            var all = dayValue + "-" + monthValue + "-" + yearValue;
            var tp_inst = jQuery.datepicker._get( inst.inst, "timepicker" );
            if( all == jQuery( "#orddd_current_day" ).val() || all == jQuery( "#orddd_min_date_set" ).val() ) {
                var time_format = jQuery( "#orddd_delivery_time_format" ).val();
                var split = inst.formattedTime.split( ":" );
                if( time_format == "1" ) {
                    if( "PM".indexOf( split[ 1 ] ) !== -1 ) {
                        var hour_time  = parseInt( split[ 0 ] ) + parseInt( 12 );    
                    } else {
                        var hour_time = parseInt( split[ 0 ] );
                    }
                } else {
                    var hour_time  = parseInt( split[ 0 ] );
                }
                if( hour_time == parseInt( jQuery( "#orddd_min_hour" ).val() ) ) {
                    inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    inst.inst.settings.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    tp_inst._defaults.minuteMin = parseInt( jQuery( "#orddd_min_minute" ).val() );
                    tp_inst._limitMinMaxDateTime( inst.inst, true );
                } else {
                    inst._defaults.minuteMin = 0;
                    inst.inst.settings.minuteMin = 0;
                    tp_inst._defaults.minuteMin = 0;
                    tp_inst._limitMinMaxDateTime( inst.inst, true );
                }
            }
            jQuery.datepicker._updateDatepicker(inst.inst);
        }
    }
}

/** 
 * Load the time slots in the time slot dropdown on select of date
 *
 * @function orddd_load_time_slots
 * @param {string} Response returned from the ajax call
 * @since 8.
 */

 function orddd_load_time_slots( response ) {
    var orddd_time_slots = response.split( "," );
    jQuery( "#time_slot" ).empty(); 
    var selected_value = '';
    for( i = 0; i < orddd_time_slots.length; i++ ) {
        var time_slot_to_display = orddd_time_slots[ i ].split( "_" );
        if( 'select' == time_slot_to_display[ 0 ] ) {
            jQuery( "#time_slot" ).append( jQuery( "<option></option>" ).attr( { value:"select", selected:"selected" } ).text( jsL10n.selectText ) );
            selected_value = orddd_time_slots[ i ];
        } else if( 'asap' == time_slot_to_display[ 0 ] ) {
            if( typeof time_slot_to_display[ 2 ] != 'undefined' ) {
                jQuery( "#time_slot option:selected" ).removeAttr( "selected" );
                jQuery( "#time_slot" ).append( jQuery( "<option></option>" ).attr( {value:time_slot_to_display[ 0 ], selected:"selected"}).text( jsL10n.asapText ) );
                selected_value = time_slot_to_display[ 0 ];    
            } else {
                jQuery( "#time_slot" ).append( jQuery( "<option></option>" ).attr( {value:time_slot_to_display[ 0 ]} ).text( jsL10n.asapText ) );
            }
        } else if( 'NA' == time_slot_to_display[ 0 ] ) {
            if( typeof time_slot_to_display[ 2 ] != 'undefined' ) {
                jQuery( "#time_slot option:selected" ).removeAttr( "selected" );
                jQuery( "#time_slot" ).append( jQuery( "<option></option>" ).attr( {value:time_slot_to_display[ 0 ], selected:"selected"}).text( jsL10n.NAText ) );
                selected_value = time_slot_to_display[ 0 ];    
            } else {
                jQuery( "#time_slot" ).append( jQuery( "<option></option>" ).attr( {value:time_slot_to_display[ 0 ]} ).text( jsL10n.NAText ) );
            }
        } else if( typeof time_slot_to_display[ 2 ] != 'undefined' ) {
            jQuery( "#time_slot option:selected" ).removeAttr( "selected" );
            if( typeof time_slot_to_display[ 1 ] != 'undefined' && time_slot_to_display[ 1 ] != '' ) {
                var time_slot_charges = decodeHtml( time_slot_to_display[ 1 ] );
                jQuery( "#time_slot" ).append( jQuery( "<option></option>" ).attr( {value:time_slot_to_display[ 0 ], selected:"selected"}).text( time_slot_to_display[ 0 ] + " " + time_slot_charges ) );
            } else {
                jQuery( "#time_slot" ).append( jQuery( "<option></option>" ).attr( {value:time_slot_to_display[ 0 ], selected:"selected"}).text( time_slot_to_display[ 0 ] ) );
            }
            selected_value = time_slot_to_display[ 0 ];
        } else {
            if( typeof time_slot_to_display[ 1 ] != 'undefined' && time_slot_to_display[ 1 ] != '' ) {
                var time_slot_charges = decodeHtml( time_slot_to_display[ 1 ] );
                jQuery( "#time_slot" ).append( jQuery( "<option></option>" ).attr( "value", time_slot_to_display[ 0 ] ).text( time_slot_to_display[ 0 ] + " " + time_slot_charges ) );
            } else {
                jQuery( "#time_slot" ).append( jQuery( "<option></option>" ).attr( "value", time_slot_to_display[ 0 ] ).text( time_slot_to_display[ 0 ] ) );
            }
        }                   
    }
}

/**
 * Gets the selected shipping method
 *
 * @function orddd_get_selected_shipping_method
 * @memberof orddd_initialize_functions
 * @returns {string} shipping_method - Shipping Method
 * @since 7.1
 */
function orddd_get_selected_shipping_method() {
    if ( "1" == jQuery( "#orddd_is_admin" ).val() ) {
        var shipping_method_id = jQuery( "input[name=\"shipping_method_id[]\"]" ).val();
        if( typeof shipping_method_id === "undefined" ) {
            var shipping_method_id = "";
        }
        var shipping_method = jQuery( "select[name=\"shipping_method[" + shipping_method_id + "]\"]" ).find(":selected").val();
        if( typeof shipping_method === "undefined" ) {
            var shipping_method = "";
        }
    } else if( "1" == jQuery( "#orddd_is_account_page" ).val() ) {
        var shipping_method = jQuery( "#shipping_method" ).val();
    } else {
        var shipping_method = jQuery( "input[name=\"shipping_method[0]\"]:checked" ).val();
        if( typeof shipping_method === "undefined" ) {
            var shipping_method = jQuery( "select[name=\"shipping_method[0]\"] option:selected" ).val();
        }
        if( typeof shipping_method === "undefined" ) {
            var shipping_method = jQuery( "input[name=\"shipping_method[0]\"]" ).val();                    
        }
        
        if( typeof shipping_method === "undefined" ) {
            var shipping_method = jQuery( "#orddd_shipping_id" ).val();                    
        }

        if( typeof shipping_method === "undefined" ) {
            var shipping_method = "";
        }
    }

    if( shipping_method.indexOf( 'usps' ) !== -1 && ( shipping_method.split( ":" ).length ) < 3 ) {
        shipping_method = jQuery( "#orddd_zone_id" ).val() + ":" + shipping_method;
    } else if( shipping_method.indexOf( 'wf_fedex_woocommerce_shipping' ) === -1 && shipping_method.indexOf( 'fedex' ) !== -1 && ( shipping_method.split( ":" ).length ) < 3 ) {
        shipping_method = jQuery( "#orddd_zone_id" ).val() + ":" + shipping_method;
    } else if( "1" == jQuery( "#orddd_is_admin" ).val() ) { 
        shipping_method = jQuery( "#orddd_shipping_id" ).val();
    }
    
    return shipping_method;
}

/**
 * Saves the delivery information which are changed in the admin Orders page
 *
 * @function save_delivery_dates
 * @memberof orddd_initialize_functions
 * @param {string} notify - Yes/No
 * @since 3.2
 */
function save_delivery_dates( notify ) {
    var hourValue = jQuery( ".ui_tpicker_time" ).html() 
    var shipping_method_id = jQuery( "input[name=\"shipping_method_id[]\"]" ).val();
    if( typeof shipping_method_id === "undefined" ) {
        var shipping_method_id = "";
    }

    var shipping_method =  [ jQuery( "select[name=\"shipping_method[" + shipping_method_id + "]\"]" ).find(":selected").val() ];
    if( typeof shipping_method === "undefined" ) {
        var shipping_method = [];
    }
    
    var data = {
        order_id: jQuery( "#orddd_order_id" ).val(),
        e_deliverydate: jQuery( '#' + jQuery( "#orddd_field_name" ).val() ).val(),
        h_deliverydate: jQuery( "#h_deliverydate" ).val(),
        time_slot: jQuery( "#time_slot option:selected" ).val(),
        orddd_time_settings_selected: hourValue,
        shipping_method: shipping_method,
        orddd_post_type: jQuery( "#orddd_post_type" ).val(),
        orddd_category_settings_to_load: jQuery( "#orddd_category_settings_to_load" ).val(),
        orddd_shipping_class_settings_to_load: jQuery( "#orddd_shipping_class_settings_to_load" ).val(),
        orddd_notify_customer: notify,
        orddd_charges: jQuery( '#del_charges' ).val(),
        action: "save_delivery_dates"
    };
    jQuery.post( jQuery( '#orddd_admin_url' ).val() + 'admin-ajax.php', data, function( response ) {
        var validations = response.split( "," );
        if(  validations[ 0 ] == "yes" && validations[ 1 ] == "yes" && validations[2] == "yes" ) {
            jQuery( "#orddd_update_notice" ).html( "Delivery details have been updated." );
            jQuery( "#orddd_update_notice" ).attr( "color", "green" );
            jQuery( "#orddd_update_notice" ).fadeIn();
            setTimeout( function() {
                jQuery( "#orddd_update_notice" ).fadeOut();
            },3000 );
        } else if ( validations[ 0 ] == "no" && ( jQuery( "#orddd_date_field_mandatory" ).val() == "checked" || jQuery( "#date_mandatory_for_shipping_method" ).val() == "checked" ) ) {
            jQuery( "#orddd_update_notice" ).html( jQuery( "#orddd_field_label" ).val() + " is mandatory." );
            jQuery( "#orddd_update_notice" ).attr( "color", "red" );
            jQuery( "#orddd_update_notice" ).fadeIn();
            setTimeout( function() {
                jQuery( "#orddd_update_notice" ).fadeOut();
            },3000 );
        } else if ( validations[ 1 ] == "no" && ( ( jQuery( "#orddd_enable_time_slot" ).val() == "on" && jQuery( "#orddd_timeslot_field_mandatory" ).val() == "checked" ) || ( jQuery( "#time_slot_enable_for_shipping_method" ).val() == "on" && jQuery( "#time_slot_mandatory_for_shipping_method" ).val() == "checked" ) ) ) {
            jQuery( "#orddd_update_notice" ).html( jQuery( "#orddd_timeslot_field_label" ).val() + " is mandatory." );
            jQuery( "#orddd_update_notice" ).attr( "color", "red" );
            jQuery( "#orddd_update_notice" ).fadeIn();
            setTimeout( function() {
                jQuery( "#orddd_update_notice" ).fadeOut();
            },3000 );
        }
    });
}