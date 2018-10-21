<?php 
/**
 * Order Calendar Sync Settings
 * @author Tyche Softwares
 * @package Order-Delivery-Date-Pro-for-WooCommerce/Admin/Settings/Google-Calendar-Sync
 * @since 4.0
 * @category Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class orddd_calendar_sync_settings {
    /**
     * Callback for adding Date Settings tab settings
     */
    public static function orddd_calendar_sync_general_settings_callback() {}
    
    /**
     * Callback for adding the Event Location field in the Google sync settings
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_calendar_event_location_callback( $args ) {
        $google_calendar_location = get_option( "orddd_calendar_event_location" );
        echo '<input type="text" name="orddd_calendar_event_location" id="orddd_calendar_event_location" value="' . $google_calendar_location . '" />';
        $html = '<label for="orddd_calendar_event_location"> ' . $args[0] . '</label>';
        echo $html;
    } 

    /**
     * Callback for adding the Event Summary name field in the Google sync settings
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_calendar_event_summary_callback( $args ) {
        $gcal_summary = get_option( 'orddd_calendar_event_summary' );
        echo '<input id="orddd_calendar_event_summary" name="orddd_calendar_event_summary" value="' . $gcal_summary . '" size="90" name="gcal_summary" type="text"/>';
    }
    
   
    /**
     * Callback for adding the Event description field in the Google sync settings
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */ 
    public static function orddd_calendar_event_description_callback( $args ) {
        $gcal_description = get_option( 'orddd_calendar_event_description' );
        echo '<textarea id="orddd_calendar_event_description" name="orddd_calendar_event_description" cols="90" rows="4" name="gcal_description">' . $gcal_description .'</textarea>';
        $html = '<label for="orddd_calendar_event_description"> ' . $args[0] . '</label>';
        echo $html;
    }
    
    
    public static function orddd_calendar_sync_customer_settings_callback() { }
    

    /**
     * Callback for adding the Add to Calendar button on Order Received Page
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_add_to_calendar_order_received_page_callback( $args ) {
        $add_to_calendar_order_received = "";
        if ( get_option( 'orddd_add_to_calendar_order_received_page' ) == 'on' ) {
            $add_to_calendar_order_received = "checked";
        }
         
        echo '<input type="checkbox" name="orddd_add_to_calendar_order_received_page" id="orddd_add_to_calendar_order_received_page" class="day-checkbox" value="on" ' . $add_to_calendar_order_received . ' />';
        $html = '<label for="orddd_add_to_calendar_order_received_page"> ' . $args[0] . '</label>';
        echo $html;
    }
    

    /**
     * Callback for adding the Add to Calendar button in the customer notification email
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_add_to_calendar_customer_email_callback( $args ) {
        $add_to_calendar_customer_email = "";
        if ( get_option( 'orddd_add_to_calendar_customer_email' ) == 'on' ) {
            $add_to_calendar_customer_email = "checked";
        }
         
        echo '<input type="checkbox" name="orddd_add_to_calendar_customer_email" id="orddd_add_to_calendar_customer_email" class="day-checkbox" value="on" ' . $add_to_calendar_customer_email . ' />';
        $html = '<label for="orddd_add_to_calendar_customer_email"> ' . $args[0] . '</label>';
        echo $html;
    }
    
     /**
     * Callback for adding the Add to Calendar button on My Account page
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_add_to_calendar_my_account_page_callback( $args ) {
        $orddd_add_to_calendar_my_account_page = "";
        if ( get_option( 'orddd_add_to_calendar_my_account_page' ) == 'on' ) {
            $orddd_add_to_calendar_my_account_page = "checked";
        }
         
        echo '<input type="checkbox" name="orddd_add_to_calendar_my_account_page" id="orddd_add_to_calendar_my_account_page" class="day-checkbox" value="on" ' . $orddd_add_to_calendar_my_account_page . ' />';
        $html = '<label for="orddd_add_to_calendar_my_account_page"> ' . $args[0] . '</label>';
        echo $html;
    }
    
     /**
     * Callback to open the calendar in the same window and tab
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_calendar_in_same_window_callback( $args ) {
        $google_calendar_same_window = "";
        if ( get_option( 'orddd_calendar_in_same_window' ) == 'on' ) {
            $google_calendar_same_window = "checked";
        }
         
        echo '<input type="checkbox" name="orddd_calendar_in_same_window" id="orddd_calendar_in_same_window" class="day-checkbox" value="on" ' . $google_calendar_same_window . ' />';
        $html = '<label for="orddd_calendar_in_same_window"> ' . $args[0] . '</label>';
        echo $html;
    } 
    
    public static function orddd_calendar_sync_admin_settings_section_callback() { }
    
     /**
     * Callback to select the type of Calendar sync integration - automatically, manually or disabled
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_calendar_sync_integration_mode_callback( $args ) {
        $sync_directly = "";
        $sync_manually = "";
        $sync_disable = "checked";
        if ( get_option( 'orddd_calendar_sync_integration_mode' ) == 'manually' ) {
            $sync_manually = "checked";
            $sync_disable = "";
        } else if( get_option( 'orddd_calendar_sync_integration_mode' ) == 'directly' ) {
            $sync_directly = "checked";
            $sync_disable = "";
        } 
        echo '<input type="radio" name="orddd_calendar_sync_integration_mode" id="orddd_calendar_sync_integration_mode" value="directly" ' . $sync_directly . '/>' . __( 'Sync Automatically', 'order-delivery-date' ) . '&nbsp;&nbsp;
            <input type="radio" name="orddd_calendar_sync_integration_mode" id="orddd_calendar_sync_integration_mode" value="manually" ' . $sync_manually . '/>' . __( 'Sync Manually', 'order-delivery-date' ) . '&nbsp;&nbsp;
            <input type="radio" name="orddd_calendar_sync_integration_mode" id="orddd_calendar_sync_integration_mode" value="disabled" ' . $sync_disable . '/>' . __( 'Disabled', 'order-delivery-date' );
        
        $html = '<label for="orddd_calendar_sync_integration_mode"> ' . $args[0] . '</label>';
        echo $html;
        
        print( '<script type="text/javascript">
            jQuery( document ).ready( function() {
                var isChecked = jQuery( "#orddd_calendar_sync_integration_mode:checked" ).val();
                if( isChecked == "directly" ) {
                   i = 0;
                   jQuery( ".form-table" ).each( function() {
                        if( i == 2 ) {
                            k = 0;
                            var row = jQuery( this ).find( "tr" );
                            jQuery.each( row , function() {
                                if( k == 7 ) {
                                    jQuery( this ).fadeOut();
                                } else {
                                    jQuery( this ).fadeIn();
                                }
                                k++;
                            });
                        } else {
                            jQuery( this ).fadeIn();
                        }
                        i++;
                    } );
                } else if( isChecked == "manually" ) {
                    i = 0;
                    jQuery( ".form-table" ).each( function() {
                        if( i == 2 ) {
                            k = 0;
                            var row = jQuery( this ).find( "tr" );
                            jQuery.each( row , function() {
                                if( k != 7 && k != 0 ) {
                                    jQuery( this ).fadeOut();
                                } else {
                                    jQuery( this ).fadeIn();
                                }
                                k++;
                            });
                        } else {
                            jQuery( this ).fadeIn();
                        }
                        i++;
                    });
                } else if( isChecked == "disabled" ) {
                    i = 0;
                    jQuery( ".form-table" ).each( function() {
                        if( i == 2 ) {
                            k = 0;
                            var row = jQuery( this ).find( "tr" );
                            jQuery.each( row , function() {
                                if( k != 0 ) {
                                    jQuery( this ).fadeOut();
                                } else {
                                    jQuery( this ).fadeIn();
                                }
                                k++;
                            });
                        } else {
                            jQuery( this ).fadeIn();
                        }
                        i++;
                    });
                }
                jQuery( "input[type=radio][id=orddd_calendar_sync_integration_mode]" ).change( function() {
                    var isChecked = jQuery( this ).val();
                    if( isChecked == "directly" ) {
                        i = 0;
                        jQuery( ".form-table" ).each( function() {
                            if( i == 2 ) {
                                k = 0;
                                var row = jQuery( this ).find( "tr" );
                                jQuery.each( row , function() {
                                    if( k == 7 ) {
                                        jQuery( this ).fadeOut();
                                    } else {
                                        jQuery( this ).fadeIn();
                                    }
                                    k++;
                                });
                            } else {
                                jQuery( this ).fadeIn();
                            }
                            i++;
                        } );
                    } else if( isChecked == "manually" ) {
                        i = 0;
                        jQuery( ".form-table" ).each( function() {
                            if( i == 2 ) {
                                k = 0;
                                var row = jQuery( this ).find( "tr" );
                                jQuery.each( row , function() {
                                    if( k != 7 && k != 0 ) {
                                        jQuery( this ).fadeOut();
                                    } else {
                                        jQuery( this ).fadeIn();
                                    }
                                    k++;
                                });
                            } else {
                                jQuery( this ).fadeIn();
                            }
                            i++;
                        });
                    } else if( isChecked == "disabled" ) {
                        i = 0;
                        jQuery( ".form-table" ).each( function() {
                            if( i == 2 ) {
                                k = 0;
                                var row = jQuery( this ).find( "tr" );
                                jQuery.each( row , function() {
                                    if( k != 0 ) {
                                        jQuery( this ).fadeOut();
                                    } else {
                                        jQuery( this ).fadeIn();
                                    }
                                    k++;
                                });
                            } else {
                                jQuery( this ).fadeIn();
                            }
                            i++;
                        });
                    }
                })
            });
        </script>');
    }
    
     /**
     * Display the stepd for syncing the Google Calendar on clicking 'Show me how'
     *
     * @since 4.0
     */
    public static function orddd_sync_calendar_instructions_callback( ) {
        echo '' . __( 'To set up Google Calendar API, please click on "Show me how" link and carefully follow these steps:', '' ) . '
            <span class="description" ><a href="#orddd-instructions" id="show_instructions" data-target="api-instructions" class="orddd-info_trigger" title="' . __ ( 'Click to toggle instructions', 'order-delivery-date') . '">' . __( 'Show me how', 'order-delivery-date' ) . '</a></span>';
        ?> <div class="description orddd-info_target api-instructions" style="display: none;">
            <ul style="list-style-type:decimal;">
                <li><?php _e( 'Google Calendar API requires PHP V5.3+ and some PHP extensions.', 'order-delivery-date' ) ?> </li>
                <li><?php printf( __( 'Go to Google APIs console by clicking %s. Login to your Google account if you are not already logged in.', 'order-delivery-date' ), '<a href="https://code.google.com/apis/console/" target="_blank">https://code.google.com/apis/console/</a>' ) ?></li>
                <li><?php _e( "Click on 'Create Project'. Name the project 'Deliveries' (or use your chosen name instead) and create the project.", 'order-delivery-date' ) ?></li>
                <li><?php _e( 'Click on APIs & Services from the left side panel. Select the Project created. ', 'order-delivery-date' ) ?></li>
                <li><?php _e( "Click on 'Enable APIs and services' on the dashboard. Search for 'Google Calendar API' and enable this API.", 'order-delivery-date' ) ?></li>
                <li><?php _e( "Go to 'Credentials' menu in the left side pane and click on 'New Credentials' dropdown.", 'order-delivery-date' ) ?></li>
                <li><?php _e( "Click on New Credentials dropdown and select 'Service account key'.", 'order-delivery-date' )?></li>
                <li><?php _e( "Click 'Service account' and select 'New service account' and enter the name.", 'order-delivery-date' ) ?></li>
                <li><?php _e( "Now select key type as 'P12' and create the service account. Choose the desired service role from the popup and click on create. A file with extension .p12 will be downloaded.", 'order-delivery-date' )?></li>
                <li><?php printf( __( 'Using your FTP client program ( e.g.: %s, %s ), copy this key file to folder: %s . This file is required as you will grant access to your Google Calendar account even if you are not online. So this file serves as a proof of your consent to access to your Google calendar account. <br><b>Note:</b> This file cannot be uploaded in any other way. If you do not have FTP access, ask the website admin to do it for you.', 'order-delivery-date' ), '<a href="https://filezilla-project.org/" target="_blank">FileZilla</a>', '<a href="https://winscp.net/eng/index.php" target="_blank">WinSCP</a>', plugin_dir_path( __FILE__ ) .'gcal/key/'  ) ?></li>
                <li><?php _e( "Enter the name of the key file to 'Key file name' setting of Order Delivery Date. Exclude the extention .p12.", 'order-delivery-date' ) ?></li>
                <li><?php _e( "Copy 'Service Account ID' from Manage service account under API service-> Credentials of Google apis console and paste it to 'Service account email address' setting of Order Delivery Date.", 'order-delivery-date' ) ?></li>
                <li><?php printf( __( 'Open your Google Calendar by clicking this link: %s', 'order-delivery-date' ), '<a href="https://www.google.com/calendar/render" target="_blank">https://www.google.com/calendar/render</a>' ) ?></li>
                <li><?php _e( "Create a new Calendar by clicking on '+' sign next to 'Add a friends calendar' text box on left side pane. <b>Try NOT to use your primary calendar.</b>", 'order-delivery-date' ) ?></li>
                <li><?php _e( 'Give a name to the new calendar, e.g. Order Delivery Date calendar. <b>Check that Calendar Time Zone setting matches with time zone setting of your WordPress website.</b> Otherwise there will be a time shift.', 'order-delivery-date' ) ?></li>		
                <li><?php _e( "Paste already copied 'Service Account ID' from Manage service account of Google APIs console to 'Add People' field under 'Share with specific people'.", 'order-delivery-date' ) ?></li>
                <li><?php _e( "Set 'Permission Settings' of this person as 'Make changes to events' and add the person. Now create the calendar.", 'order-delivery-date' ) ?></li>
                <li><?php _e( "Select the created calendar and click 'Calendar settings'. Now copy 'Calendar ID' value on Calendar Address row and paste the value to 'Calendar to be used' field of Order Delivery Date settings.", 'order-delivery-date' ) ?></li>
                <li><?php _e( "After saving the settings, you can test the connection by clicking on the 'Test Connection' link.", 'order-delivery-date' ) ?></li>
                <li><?php _e( 'If you get a success message, you should see a test event inserted into the Google Calendar and you are ready to go. If you get an error message, double check your settings.', 'order-delivery-date' ) ?></li>
            </ul>
        </div>
        <script type="text/javascript">
            function toggle_target (e) {
            	if ( e && e.preventDefault ) { 
                    e.preventDefault();
                }
            	if ( e && e.stopPropagation ) {
                    e.stopPropagation();
                }
            	var target = jQuery(".orddd-info_target.api-instructions" );
            	if ( !target.length ) {
                    return false;
                }
                
            	if ( target.is( ":visible" ) ) {
                    target.hide( "fast" );
                } else {
                    target.show( "fast" );
                }
            
            	return false;
            }
            jQuery(function () {
            	jQuery(document).on("click", ".orddd-info_trigger", toggle_target);
            });
        </script>
        <?php
    }
    
     /**
     * Callback for adding Key File name field to enter the file name without extension 
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_calendar_key_file_name_callback( $args ) {
        $gcal_key_file_arr = get_option( 'orddd_calendar_details_1' );
        if( isset( $gcal_key_file_arr[ 'orddd_calendar_key_file_name' ] ) ) {
            $gcal_key_file = $gcal_key_file_arr[ 'orddd_calendar_key_file_name' ];
        } else {
            $gcal_key_file = '';
        }
        echo '<input id="orddd_calendar_details_1[orddd_calendar_key_file_name]" name= "orddd_calendar_details_1[orddd_calendar_key_file_name]" value="' . $gcal_key_file .'" size="90" name="gcal_key_file" type="text" />';
        $html = '<label for="orddd_calendar_key_file_name"> ' . $args[0] . '</label>';
        echo $html;
    }

    /**
     * Callback for adding the 'Serveice Account Email Address' field in the settings
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_calendar_service_acc_email_address_callback( $args ) {
        $gcal_service_account_arr = get_option( 'orddd_calendar_details_1' );
        if( isset( $gcal_service_account_arr[ 'orddd_calendar_service_acc_email_address' ] ) ) {
            $gcal_service_account = $gcal_service_account_arr[ 'orddd_calendar_service_acc_email_address' ];
        } else {
            $gcal_service_account = '';
        }
        
        echo '<input id="orddd_calendar_details_1[orddd_calendar_service_acc_email_address]" name="orddd_calendar_details_1[orddd_calendar_service_acc_email_address]" value="' . $gcal_service_account . '" size="90" name="gcal_service_account" type="text"/>';
        $html = '<label for="orddd_calendar_service_acc_email_address"> ' . $args[0] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for adding the 'Calendar to be used' field in the settings to enter the Calendar ID
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_calendar_id_callback( $args ) {
        $gcal_selected_calendar_arr = get_option( 'orddd_calendar_details_1' ); 
        if( isset( $gcal_selected_calendar_arr[ 'orddd_calendar_id' ] ) ) {
            $gcal_selected_calendar = $gcal_selected_calendar_arr[ 'orddd_calendar_id' ];
        } else {
            $gcal_selected_calendar = '';
        }
        echo '<input id="orddd_calendar_details_1[orddd_calendar_id]" name="orddd_calendar_details_1[orddd_calendar_id]" value="' . $gcal_selected_calendar . '" size="90" name="gcal_selected_calendar" type="text" />';
        $html = '<label for="orddd_calendar_id"> ' . $args[0] . '</label>';
        echo $html;
    }

    /**
     * Callback for adding the 'Test Connection' link and checks if the connection is succesful or not
     *
     * @since 4.0
     */
    public static function orddd_calendar_test_connection_callback() {
        echo "<script type='text/javascript'>
            jQuery( document ).on( 'click', '#test_connection', function( e ) {
                e.preventDefault();    
                var data = {
                        gcal_api_test_result: '',
                        gcal_api_pre_test: '',
                        gcal_api_test: 1,
                        action: 'display_nag'
                    };
                    jQuery( '#test_connection_ajax_loader' ).show();
                    jQuery.post( '" . get_admin_url() . "/admin-ajax.php', data, function( response ) {
                        jQuery( '#test_connection_message' ).html( response );
                        jQuery( '#test_connection_ajax_loader' ).hide();
                    });
                
                
            });
        </script>";
        print "<a href='admin.php?page=order_delivery_date&action=calendar_sync_settings' id='test_connection'>" . __( 'Test Connection', 'order-delivery-date' ) . "</a> 
            <img src='" . plugins_url() . "/order-delivery-date/images/ajax-loader.gif' id='test_connection_ajax_loader'>";
        print "<div id='test_connection_message'></div>";
    }
    
    /**
     * Callback for adding the 'Add to Calendar' button in the New Order email notification
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_admin_add_to_calendar_email_notification_callback( $args ) {
        $orddd_admin_add_to_calendar_email_notification = "";
        if( get_option( 'orddd_admin_add_to_calendar_email_notification' ) == "on" ) {
            $orddd_admin_add_to_calendar_email_notification = "checked";
        }
        echo '<input type="checkbox" name="orddd_admin_add_to_calendar_email_notification" id="orddd_admin_add_to_calendar_email_notification" value="on" ' . $orddd_admin_add_to_calendar_email_notification . ' />';
        $html = '<label for="orddd_admin_add_to_calendar_email_notification"> ' . $args[0] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for adding the 'Add to Calendar' button in the admin Delivery Calendar page
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_admin_add_to_calendar_delivery_calendar_callback( $args ) {
        $orddd_admin_add_to_calendar_view_deliveries = "";
        if( get_option( 'orddd_admin_add_to_calendar_delivery_calendar' ) == "on" ) {
            $orddd_admin_add_to_calendar_view_deliveries = "checked";
        }
        echo '<input type="checkbox" name="orddd_admin_add_to_calendar_delivery_calendar" id="orddd_admin_add_to_calendar_delivery_calendar" value="on" ' . $orddd_admin_add_to_calendar_view_deliveries . ' />';
        $html = '<label for="orddd_admin_add_to_calendar_delivery_calendar"> ' . $args[0] . '</label>';
        echo $html;
    }
    
    /**
     * Display the description for the Import Events section
     *
     * @since 4.0
     */
    public static function orddd_calendar_import_ics_feeds_section_callback() {
        _e( 'Events will be imported using the ICS Feed url. Each event will create a new WooCommerce Order. The event\'s date & time will be set as that order\'s Delivery Date & Time. <br>Lockout will be updated for global settings for the set Delivery Date & Time.', 'order-delivery-date' );
    }
    
    /**
     * Callback for adding instructions to set up Import events using ics feed urls
     *
     * @since 4.0
     */
    public static function orddd_ics_feed_url_instructions_callback() {
        echo '' . __( 'To set up Import events using ics feed urls, please click on "Show me how" link and carefully follow these steps:', 'order-delivery-date' ) . '
        <span class="ics-feed-description" ><a href="#orddd-ics-feed-instructions" id="show_instructions" data-target="api-instructions" class="orddd_ics_feed-info_trigger" title="' . __ ( 'Click to toggle instructions', 'order-delivery-date') . '">' . __( 'Show me how', 'order-delivery-date' ) . '</a></span>';
        ?> <div class="ics-feed-description orddd_ics_feed-info_target api-instructions" style="display: none;">
            <ul style="list-style-type:decimal;">
                <li><?php printf( __( 'Open your Google Calendar by clicking this link: %s', 'order-delivery-date' ), '<a href="https://www.google.com/calendar/render" target="_blank">https://www.google.com/calendar/render</a>' ) ?></li>
                <li><?php _e( 'Select the calendar to be imported and click "Calendar settings".', 'order-delivery-date' ) ?></li>
                <li><?php _e( 'Click on "ICAL" button in Calendar Address option.', 'order-delivery-date' ) ?></li>		
                <li><?php _e( 'Copy the basic.ics file URL. <i>If you are importing events from a private calendar please copy the basic.ics file URL for private calendar.</i>', 'order-delivery-date' ) ?></li>
                <li><?php _e( 'Paste this link in the text box under Google Calendar Sync tab -> Import Events section.', 'order-delivery-date' ) ?></li>
                <li><?php _e( 'Save the URL.', 'order-delivery-date' ) ?></li>
                <li><?php _e( 'Click on "Import Events" button to import the events from the calendar.', 'order-delivery-date' ) ?></li>
                <li><?php _e( 'You can import multiple calendars by using ics feeds. Add them using the Add New Ics Feed url button.', 'order-delivery-date' ) ?></li>
            </ul>
        </div>
        <script type="text/javascript">
            function orddd_ics_feed_toggle_target (e) {
            	if ( e && e.preventDefault ) { 
                    e.preventDefault();
                }
            	if ( e && e.stopPropagation ) {
                    e.stopPropagation();
                }
            	var target = jQuery( ".orddd_ics_feed-info_target.api-instructions" );
            	if ( !target.length ) {
                    return false;
                }
                
            	if ( target.is( ":visible" ) ) {
                    target.hide( "fast" );
                } else {
                    target.show( "fast" );
                }
            
            	return false;
            }
            jQuery( function () { 
            	jQuery(document).on( "click", ".orddd_ics_feed-info_trigger", orddd_ics_feed_toggle_target );
            });
        </script>
        <?php
    }
       
    /**
     * Callback for adding the 'iCalendar/.ics Feed URL' field in the Import Events section
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 4.0
     */
    public static function orddd_ics_feed_url_callback( $args ) {
        echo '<table id="orddd_ics_url_list">';
        $ics_feed_urls = get_option( 'orddd_ics_feed_urls' );
        if( $ics_feed_urls == '' || $ics_feed_urls == '{}' || $ics_feed_urls == '[]' || $ics_feed_urls == 'null' ) {
            $ics_feed_urls = array();
        }
        
        if( is_array( $ics_feed_urls ) && count( $ics_feed_urls ) > 0 ) {
            foreach ( $ics_feed_urls as $key => $value ) {
                echo "<tr id='$key'>
                    <td class='ics_feed_url'>
                        <input type='text' id='orddd_ics_fee_url_$key' size='60' value='" . $value. "'>
                    </td>
                    <td class='ics_feed_url'>
                        <input type='button' value='Save' id='save_ics_url' class='save_button' name='$key' disabled='disabled'>
                    </td>
                    <td class='ics_feed_url'>
                        <input type='button' class='save_button' id='$key' name='import_ics' value='Import Events'>
                    </td>
                    <td class='ics_feed_url'>
                        <input type='button' class='save_button' id='$key' value='Delete' name='delete_ics_feed'>
                    </td>
                    <td class='ics_feed_url'>
                        <div id='import_event_message'>
                            <img src='" . plugins_url() . "/order-delivery-date/images/ajax-loader.gif'>
                        </div>
                        <div id='success_message' ></div>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr id='0' >
                <td class='ics_feed_url'>
                    <input type='text' id='orddd_ics_fee_url_0' size='60' >
                </td>
                <td class='ics_feed_url'>
                    <input type='button' value='Save' id='save_ics_url' class='save_button' name='0' >
                </td>
                <td class='ics_feed_url'>
                    <input type='button' class='save_button' id='0' name='import_ics' value='Import Events' disabled='disabled'>
                </td>
                <td class='ics_feed_url'>
                    <input type='button' class='save_button' id='0' name='delete_ics_feed' value='Delete' disabled='disabled'>
                </td>
                <td class='ics_feed_url'>
                    <div id='import_event_message'>
                        <img src='" . plugins_url() . "/order-delivery-date/images/ajax-loader.gif'>
                    </div>
                    <div id='success_message' ></div>
                </td>
            </tr>";
        }
        echo'</table>';
        
        echo "<input type='button' class='save_button' id='add_new_ics_feed' name='add_new_ics_feed' value='" . __( 'Add New Ics feed url', 'order-delivery-date' ) ."'>";
        echo "<script type='text/javascript'>
            jQuery( document ).ready( function() {
                
                jQuery( '#add_new_ics_feed' ).on( 'click', function() {
                    var rowCount = jQuery( '#orddd_ics_url_list tr' ).length;
                    jQuery( '#orddd_ics_url_list' ).append( '<tr id=\'' + rowCount + '\'><td class=\'ics_feed_url\'><input type=\'text\' id=\'orddd_ics_fee_url_' + rowCount + '\' size=\'60\' ></td><td class=\'ics_feed_url\'><input type=\'button\' value=\'Save\' id=\'save_ics_url\' class=\'save_button\' name=\'' + rowCount + '\'></td><td class=\'ics_feed_url\'><input type=\'button\' class=\'save_button\' id=\'' + rowCount + '\' name=\'import_ics\' value=\'Import Events\' disabled=\'disabled\'></td><td class=\'ics_feed_url\'><input type=\'button\' class=\'save_button\' id=\'' + rowCount + '\' value=\'Delete\' disabled=\'disabled\'  name=\'delete_ics_feed\' ></td><td class=\'ics_feed_url\'><div id=\'import_event_message\'><img src=\'" . plugins_url() . "/order-delivery-date/images/ajax-loader.gif\'></div><div id=\'success_message\' ></div></td></tr>' );
                });
            
                jQuery( document ).on( 'click', '#save_ics_url', function() {
                    var key = jQuery( this ).attr( 'name' );
                    var data = {
                        ics_url: jQuery( '#orddd_ics_fee_url_' + key ).val(),
                        action: 'save_ics_url_feed'
                    };
                    jQuery.post( '" . get_admin_url() . "/admin-ajax.php', data, function( response ) {
                        if( response == 'yes' ) {
                            jQuery( 'input[name=\'' + key + '\']' ).attr( 'disabled','disabled' );
                            jQuery( 'input[id=\'' + key + '\']' ).removeAttr( 'disabled' );
                        } 
                    });
                });
                
                jQuery( document ).on( 'click', 'input[type=\'button\'][name=\'delete_ics_feed\']', function() {
                    var key = jQuery( this ).attr( 'id' );
                    var data = {
                        ics_feed_key: key,
                        action: 'delete_ics_url_feed'
                    };
                    jQuery.post( '" . get_admin_url() . "/admin-ajax.php', data, function( response ) {
                        if( response == 'yes' ) {
                            jQuery( 'table#orddd_ics_url_list tr#' + key ).remove();
                        } 
                    });
                });
                
                jQuery( document ).on( 'click', 'input[type=\'button\'][name=\'import_ics\']', function() {
                    jQuery( '#import_event_message' ).show();
                    var key = jQuery( this ).attr( 'id' );
                    var data = {
                        ics_feed_key: key,
                        action: 'import_events'
                    };
                    jQuery.post( '" . get_admin_url() . "/admin-ajax.php', data, function( response ) {
                        jQuery( '#import_event_message' ).hide();
                        jQuery( '#success_message' ).html( response );  
                        jQuery( '#success_message' ).fadeIn();
                        setTimeout( function() {
                            jQuery( '#success_message' ).fadeOut();
                        },3000 );
                    });
                });
            });
        </script>";
    }
}
?>