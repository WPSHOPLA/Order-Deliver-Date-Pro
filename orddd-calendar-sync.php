<?php
/**
 * Order Delivery Date Pro for WooCommerce
 *
 * Handles the two way Google Calendar sync.
 *
 * @author      Tyche Softwares
 * @package     Order-Delivery-Date-Pro-for-WooCommerce/Google-Calendar
 * @since       4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Main class for Google Calendar Sync for deliveries.
 *
 * @class orddd_calendar_sync
 */
class orddd_calendar_sync {

    /**
    * Default constructor
    * 
    * @since 4.0
    */
    public function __construct() {
        $this->gcal_api = false;
        add_action( 'init', array( $this, 'orddd_setup_gcal_sync' ), 10 );
        $this->plugin_dir = plugin_dir_path( __FILE__ );
        $this->plugin_url = plugins_url( basename( dirname( __FILE__ ) ) );
        
        add_action( 'woocommerce_checkout_update_order_meta', array( &$this, 'orddd_google_calendar_update_order_meta' ), 11 );
        
        if( get_option( 'orddd_add_to_calendar_order_received_page' ) == 'on' ) {
            add_filter( 'woocommerce_order_details_after_order_table', array( &$this, 'orddd_add_to_calendar_customer' ), 11, 3 );
        }
        if( get_option( 'orddd_add_to_calendar_my_account_page' ) == 'on' ) {
            add_filter( 'woocommerce_my_account_my_orders_actions', array( &$this, 'orddd_add_to_calendar_customer_my_account' ), 10, 3 );
        }
        
        if( get_option( 'orddd_add_to_calendar_customer_email' ) == 'on' ) {
            add_filter( 'woocommerce_email_customer_details', array( &$this, 'orddd_add_to_calendar_customer_email' ), 1, 3 );
        }

        if( get_option( 'orddd_admin_add_to_calendar_email_notification' ) == 'on' && get_option( 'orddd_calendar_sync_integration_mode' ) == 'manually' ) {
            add_filter( 'woocommerce_email_customer_details', array( &$this, 'orddd_admin_add_to_calendar_email_notification' ), 1, 3 );
        }
        
        add_action( 'wp_ajax_import_events', array( &$this, 'import_events' ) );
        add_action( 'wp_ajax_save_ics_url_feed', array( &$this, 'save_ics_url_feed' ) );
        add_action( 'wp_ajax_delete_ics_url_feed', array( &$this, 'delete_ics_url_feed' ) );
        add_action( 'wp_ajax_orddd_admin_delivery_calendar_events', array( &$this, 'orddd_admin_delivery_calendar_events' ) );
        
        require_once $this->plugin_dir . 'includes/iCal/SG_iCal.php';
    }

    /**
     * Adds the GCal class file
     *
     * @hook init
     * @since 4.0
     */
    function orddd_setup_gcal_sync () {
        // GCal Integration
        $this->gcal_api = false;
        // Allow forced disabling in case of emergency
        require_once $this->plugin_dir . '/includes/class.gcal.php';
        $this->gcal_api = new OrdddGcal();
    }
    
    /**
     * This function adds an event/delivery information to the Google Calendar if automated sync is enabled.
     *
     * @param integer $order_id - Order ID for which delivery information need to be synced
     * @hook woocommerce_checkout_update_order_meta
     * @since 4.0
     */
    function orddd_google_calendar_update_order_meta( $order_id ) {
        $gcal = new OrdddGcal();
        if( $gcal->get_api_mode() == "directly" ) {
            $gcal->insert_event( $_POST, $order_id, false );
        }
    }
    
    /**
     * Adds buttons on WooCommerce Order received page
     * using which customers can add deliveries into their calendars.
     *
     * @param WC_Order $order - Order Object
     *
     * @hook woocommerce_order_details_after_order_table
     * @since 4.0
     */    
    function orddd_add_to_calendar_customer( $order ) {
        wp_enqueue_style( 'calendar-sync', plugins_url( '/css/calendar-sync.css', __FILE__ ) , '', '', false );
        if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
            $order_id = $order->get_id();
        } else {
            $order_id = $order->id;
        }
        $data = get_post_meta( $order_id );

        $location = orddd_common::orddd_get_order_location( $order_id );
        $shipping_method = orddd_common::orddd_get_order_shipping_method( $order_id );
        $product_category = orddd_common::orddd_get_order_product_category( $order_id );
        $shipping_class = orddd_common::orddd_get_product_shipping_class( $order_id );

        $timeslot_field_label = orddd_common::orddd_get_delivery_time_field_label( $shipping_method, $product_category, $shipping_class, $location ); 

        $orddd = new stdClass();
        if ( isset( $data[ '_orddd_timestamp' ][ 0 ] ) && $data[ '_orddd_timestamp' ][ 0 ] != '' ) {
            $delivery_date = date( "d-m-Y", $data[ '_orddd_timestamp' ][ 0 ] );
            $orddd->client_address = __( $data[ '_shipping_address_1' ][ 0 ] . " " . $data[ '_shipping_address_2' ][ 0 ] , 'order-delivery-date');
            $orddd->client_full_address = str_replace( "<br/>", ",", $order->get_formatted_shipping_address() );
            $orddd->client_city = __( $data[ '_shipping_city' ][ 0 ], 'order-delivery-date');
            $orddd->start = date( 'Y-m-d', strtotime( $delivery_date ) );
            $orddd->end = date( 'Y-m-d', strtotime( $delivery_date ) );
            if( isset( $data[ $timeslot_field_label ][ 0 ] ) && $data[ $timeslot_field_label ][ 0 ] != '' && $data[ $timeslot_field_label ][ 0 ] != 'NA'  && $data[ $timeslot_field_label ][ 0 ] != 'choose' && $data[ $timeslot_field_label ][ 0 ] != 'select' ) {
                $timeslot = explode( " - ", $data[ $timeslot_field_label ][ 0 ] );
                $from_time = date( "H:i", strtotime( $timeslot[ 0 ] ) );
                if( isset( $timeslot[ 1 ] ) && $timeslot[ 1 ] != '' ) {
                    $to_time = date( "H:i", strtotime( $timeslot[ 1 ] ) );
                    $orddd->end_time = $to_time;
                    $time_end = explode( ':', $to_time );
                } else {
                    $orddd->end_time = $from_time;
                    $time_end = explode( ':', $from_time );
                }
                $orddd->start_time = $from_time;
            } else {
                $from_time = date( "H:i", $data[ '_orddd_timestamp' ][ 0 ] );
                if( $from_time != '00:00' && $from_time != '00:01' && $from_time != '' ) {
                    $orddd->start_time = $from_time;
                    $orddd->end_time = $from_time;
                } else {
                    $orddd->start_time = "";
                    $orddd->end_time = "";
                }
            } 
            $orddd->client_email = $data[ '_billing_email' ][ 0 ];
            $orddd->client_name = $data[ '_shipping_first_name' ][ 0 ] . " " . $data[ '_shipping_last_name' ][ 0 ];
            $orddd->client_address = $data[ '_shipping_address_1' ][ 0 ]  . " " . $data[ '_shipping_address_2' ][ 0 ];
            $orddd->client_full_address = str_replace( "<br/>", ",", $order->get_formatted_shipping_address() );
            $orddd->client_phone = $data[ '_billing_phone' ][ 0 ];
            if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
                $order_customer_note = $order->get_customer_note();
            } else {
                $order_customer_note = $order->customer_note;
            }
            $orddd->order_note  = $order_customer_note;
            $get_order_items = $order->get_items();
            $products = $product_with_qty = "";
            foreach( $get_order_items as $key => $value ) {
                $products .= $value[ 'name' ] . ", ";
                $product_with_qty .= $value[ 'name' ] . "(QTY: " . $value[ 'qty' ] . "), ";
            }
            $products = substr( $products, 0, strlen( $products )-2 );
            $product_with_qty = substr( $product_with_qty, 0, strlen( $product_with_qty )-2 );
            $orddd->order_total  = $order->get_total();
            $orddd->products = $products;
            $orddd->product_with_qty = $product_with_qty;
            if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
                $order_date_time = $order->get_date_created();
            } else {
                $order_date_time = $order->post->post_date;
            }
            $orddd->order_date_time = $order_date_time;
            $order_date = date( "Y-m-d", strtotime( $order_date_time ) );
            $orddd->order_date = $order_date;
            $orddd->id = $order->get_order_number();
            
            $gcal = new OrdddGcal();
            $href = $gcal->gcal( $orddd );
            $other_calendar_href = $gcal->other_cal( $orddd );
            if( get_option( 'orddd_calendar_in_same_window' ) == 'on' ) {
                ?>
                <div class="add_to_calendar">
                    <button onclick="myFunction()" class="dropbtn">Add To Calendar<i class="claret"></i></button>
                    <div id="add_to_calendar_menu" class="add_to_calendar-content">
                        <a href="<?php echo $href; ?>" target= "_self" id="add_to_google_calendar" ><img class="icon" src="<?php echo plugins_url(); ?>/order-delivery-date/images/google-icon.ico">Add to Google Calendar</a>
                        <a href="<?php echo $other_calendar_href; ?>" target="_self" id="add_to_other_calendar" ><img class="icon" src="<?php echo plugins_url(); ?>/order-delivery-date/images/calendar-icon.ico">Add to other Calendar</a>
                    </div>
                </div>
                <?php 
            } else {
                ?>
                <div class="add_to_calendar">
                    <button onclick="myFunction()" class="dropbtn">Add To Calendar<i class="claret"></i></button>
                    <div id="add_to_calendar_menu" class="add_to_calendar-content">
                        <a href="<?php echo $href; ?>" target= "_blank" id="add_to_google_calendar" ><img class="icon" src="<?php echo plugins_url(); ?>/order-delivery-date/images/google-icon.ico">Add to Google Calendar</a>
                        <a href="<?php echo $other_calendar_href; ?>" target="_blank" id="add_to_other_calendar" ><img class="icon" src="<?php echo plugins_url(); ?>/order-delivery-date/images/calendar-icon.ico">Add to other Calendar</a>
                    </div>
                </div>
                <?php 
            }
            ?>
            <script type="text/javascript">
                /* When the user clicks on the button, toggle between hiding and showing the dropdown content */
                function myFunction() {
                    document.getElementById( "add_to_calendar_menu" ).classList.toggle( "show" );
                }
                // Close the dropdown if the user clicks outside of it
                window.onclick = function(event) {
                    if ( !event.target.matches( '.dropbtn' ) ) {
                        var dropdowns = document.getElementsByClassName( "dropdown-add_to_calendar-content" );
                        var i;
                        for ( i = 0; i < dropdowns.length; i++ ) {
                            var openDropdown = dropdowns[i];
                            if ( openDropdown.classList.contains( 'show' ) ) {
                                openDropdown.classList.remove( 'show' );
                            }
                        }
                    }
                }
            </script>
            <?php 
        }
    }
    
    /**
     * Adds buttons on My Account page using which customers can add deliveries into their calendars.
     *
     * @param WC_Order $action - Order Object
     * @param array $order - Order actions (Pay, Cancel, View)
     *
     * @hook woocommerce_my_account_my_orders_actions
     * @since 4.0
     */
    function orddd_add_to_calendar_customer_my_account( $order, $action ) {
        wp_enqueue_style( 'calendar-sync', plugins_url( '/css/calendar-sync.css', __FILE__ ) , '', '', false );
        if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
            $action_id = $action->get_id();
        } else {
            $action_id = $action->id;
        }

        $data = get_post_meta( $action_id );

        $location = orddd_common::orddd_get_order_location( $order_id );
        $shipping_method = orddd_common::orddd_get_order_shipping_method( $action_id );
        $product_category = orddd_common::orddd_get_order_product_category( $action_id );
        $shipping_class = orddd_common::orddd_get_product_shipping_class( $action_id );

        $timeslot_field_label = orddd_common::orddd_get_delivery_time_field_label( $shipping_method, $product_category, $shipping_class, $location ); 

        $orddd = new stdClass();
        if ( isset( $data[ '_orddd_timestamp' ][ 0 ] ) && $data[ '_orddd_timestamp' ][ 0 ] != '' ) {
            $delivery_date = date( "d-m-Y", $data[ '_orddd_timestamp' ][ 0 ] );
            $orddd->client_address = __( $data[ '_shipping_address_1' ][ 0 ] . " " . $data[ '_shipping_address_2' ][ 0 ] , 'order-delivery-date');
            $orddd->client_full_address = str_replace( "<br/>", ",", $action->get_formatted_shipping_address() );
            $orddd->client_city = __( $data[ '_shipping_city' ][ 0 ], 'order-delivery-date');
            $orddd->start = date( 'Y-m-d', strtotime( $delivery_date ) );
            $orddd->end = date( 'Y-m-d', strtotime( $delivery_date ) );
            if( isset( $data[ $timeslot_field_label ][ 0 ] ) && $data[ $timeslot_field_label ][ 0 ] != '' && $data[ $timeslot_field_label ][ 0 ] != 'NA'  && $data[ $timeslot_field_label ][ 0 ] != 'choose' && $data[ $timeslot_field_label ][ 0 ] != 'select' ) {
                $timeslot = explode( " - ", $data[ $timeslot_field_label ][ 0 ] );
                $from_time = date( "H:i", strtotime( $timeslot[ 0 ] ) );
                if( isset( $timeslot[ 1 ] ) && $timeslot[ 1 ] != '' ) {
                    $to_time = date( "H:i", strtotime( $timeslot[ 1 ] ) );
                    $orddd->end_time = $to_time;
                } else {
                    $orddd->end_time = $from_time;
                }
                $orddd->start_time = $from_time;
                 
            } else {
                $from_time = date( "H:i", $data[ '_orddd_timestamp' ][ 0 ] );
                if( $from_time != '00:00' && $from_time != '00:01' && $from_time != '' ) {
                    $orddd->start_time = $from_time;
                    $orddd->end_time = $from_time;
                } else {
                    $orddd->start_time = "";
                    $orddd->end_time = "";
                }
            } 
            $orddd->client_email = $data[ '_billing_email' ][ 0 ];
            $orddd->client_name = $data[ '_shipping_first_name' ][ 0 ] . " " . $data[ '_shipping_last_name' ][ 0 ];
            $orddd->client_address = $data[ '_shipping_address_1' ][ 0 ]  . " " . $data[ '_shipping_address_2' ][ 0 ];
            $orddd->client_full_address = str_replace( "<br/>", ",", $action->get_formatted_shipping_address() );
            $orddd->client_phone = $data[ '_billing_phone' ][ 0 ];
            if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
                $order_customer_note = $action->get_customer_note();
            } else {
                $order_customer_note = $action->customer_note;
            }
            $orddd->order_note  = $order_customer_note;
            $get_order_items = $action->get_items();
            $products = $product_with_qty = "";
            foreach( $get_order_items as $key => $value ) {
                $products .= $value[ 'name' ] . ", ";
                $product_with_qty .= $value[ 'name' ] . "(QTY: " . $value[ 'qty' ] . "), ";
            }
            $products = substr( $products, 0, strlen( $products )-2 );
            $product_with_qty = substr( $product_with_qty, 0, strlen( $product_with_qty )-2 );
            $orddd->order_total  = $action->get_total();
            $orddd->products = $products;
            $orddd->product_with_qty = $product_with_qty;
            if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
                $order_date_time = $action->get_date_created();
            } else {
                $order_date_time = $action->post->post_date;
            }
            $orddd->order_date_time = $order_date_time;
            $order_date = date( "Y-m-d", strtotime( $order_date_time ) );
            $orddd->order_date = $order_date;
            $orddd->id = $action->get_order_number();
            $gcal = new OrdddGcal();
            $href = $gcal->gcal( $orddd );
            $other_calendar_href = $gcal->other_cal( $orddd );
            $order[ 'add_to_google_calendar' ] = array( "url" => $href,
                "name"  => __( "Add to Google Calendar", 'order-delivery-date' ) );

            $order[ 'add_to_other_calendar' ] = array( "url" => $other_calendar_href,
                "name"  => __( "Add to other Calendar", 'order-delivery-date' ) );           
        }
        return $order;
    }
    
    /**
     * Adds buttons in the WooCommerce customer emails when Integration mode is selected as directly
     * using which customers can add deliveries/event into their calendars.
     *
     * @param WC_Order $order - Order Object
     * @param bool $sent_to_admin (default: false)
     * @param bool $plain_text (default: false)
     *
     * @hook woocommerce_email_customer_details
     * @since 4.0
     */
    function orddd_add_to_calendar_customer_email( $order, $sent_to_admin = false, $plain_text = false ) {
        if( $sent_to_admin === false ) {
            if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
                $order_id = $order->get_id();
            } else {
                $order_id = $order->id;
            }
            $data = get_post_meta( $order_id );

            $location = orddd_common::orddd_get_order_location( $order_id );
            $shipping_method = orddd_common::orddd_get_order_shipping_method( $order_id );
            $product_category = orddd_common::orddd_get_order_product_category( $order_id );
            $shipping_class = orddd_common::orddd_get_product_shipping_class( $order_id );

            $timeslot_field_label = orddd_common::orddd_get_delivery_time_field_label( $shipping_method, $product_category, $shipping_class, $location ); 

            $orddd = new stdClass();
            if ( isset( $data[ '_orddd_timestamp' ][ 0 ] ) && $data[ '_orddd_timestamp' ][ 0 ] != '' ) {
                $delivery_date = date( "d-m-Y", $data[ '_orddd_timestamp' ][ 0 ] );
                $orddd->client_address = __( $data[ '_shipping_address_1' ][ 0 ] . " " . $data[ '_shipping_address_2' ][ 0 ] , 'order-delivery-date');
                $orddd->client_full_address = str_replace( "<br/>", ",", $order->get_formatted_shipping_address() );
                $orddd->client_city = __( $data[ '_shipping_city' ][ 0 ], 'order-delivery-date');
                $orddd->start = date( 'Y-m-d', strtotime( $delivery_date ) );
                $orddd->end = date( 'Y-m-d', strtotime( $delivery_date ) );
                if( isset( $data[ $timeslot_field_label ][ 0 ] ) && $data[ $timeslot_field_label ][ 0 ] != '' && $data[ $timeslot_field_label ][ 0 ] != 'NA'  && $data[ $timeslot_field_label ][ 0 ] != 'choose' && $data[ $timeslot_field_label ][ 0 ] != 'select' ) {
                    $timeslot = explode( " - ", $data[ $timeslot_field_label ][ 0 ] );
                    $from_time = date( "H:i", strtotime( $timeslot[ 0 ] ) );
                    if( isset( $timeslot[ 1 ] ) && $timeslot[ 1 ] != '' ) {
                        $to_time = date( "H:i", strtotime( $timeslot[ 1 ] ) );
                        $orddd->end_time = $to_time;
                    } else {
                        $orddd->end_time = $from_time;
                    }
                    $orddd->start_time = $from_time;
                     
                } else {
                    $from_time = date( "H:i", $data[ '_orddd_timestamp' ][ 0 ] );
                    if( $from_time != '00:00' && $from_time != '00:01' && $from_time != '' ) {
                        $orddd->start_time = $from_time;
                        $orddd->end_time = $from_time;
                    } else {
                        $orddd->start_time = "";
                        $orddd->end_time = "";
                    }
                } 
                $orddd->client_email = $data[ '_billing_email' ][ 0 ];
                $orddd->client_name = $data[ '_shipping_first_name' ][ 0 ] . " " . $data[ '_shipping_last_name' ][ 0 ];
                $orddd->client_address = $data[ '_shipping_address_1' ][ 0 ]  . " " . $data[ '_shipping_address_2' ][ 0 ];
                $orddd->client_full_address = str_replace( "<br/>", ",", $order->get_formatted_shipping_address() );
                $orddd->client_phone = $data[ '_billing_phone' ][ 0 ];
                if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
                    $order_customer_note = $order->get_customer_note();
                } else {
                    $order_customer_note = $order->customer_note;
                }
                $orddd->order_note  = $order_customer_note;
                $get_order_items = $order->get_items();
                $products = $product_with_qty = "";
                foreach( $get_order_items as $key => $value ) {
                    $products .= $value[ 'name' ] . ", ";
                    $product_with_qty .= $value[ 'name' ] . "(QTY: " . $value[ 'qty' ] . "), ";
                }
                $products = substr( $products, 0, strlen( $products )-2 );
                $product_with_qty = substr( $product_with_qty, 0, strlen( $product_with_qty )-2 );
                $orddd->order_total  = $order->get_total();
                $orddd->products = $products;
                $orddd->product_with_qty = $product_with_qty;
                if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
                    $order_date_time = $order->get_date_created();
                } else {
                    $order_date_time = $order->post->post_date;
                }
                $orddd->order_date_time = $order_date_time;
                $order_date = date( "Y-m-d", strtotime( $order_date_time ) );
                $orddd->order_date = $order_date;
                $orddd->id = $order->get_order_number();
                $gcal = new OrdddGcal();
                $href = $gcal->gcal( $orddd );
                $other_calendar_href = $gcal->other_cal( $orddd );

                if( get_option( 'orddd_calendar_in_same_window' ) == 'on' ) {
                    ?>
                    <table cellspacing="0" cellpadding="0" border="0" style="border-collapse: separate!important;border-radius: 3px;background-color: #00add8" class="add_to_gcal_button">
                        <tbody>
                            <tr>    
                                <td valign="middle" align="center" style="font-family:Helvetica;font-size:12px;padding:7px">
                                    <a style="font-weight:bold;letter-spacing:normal;line-height:100%;text-align:center;text-decoration:none;color:#f5f5f5;word-wrap:break-word" title="Add to Google Calendar" href="<?php echo $href; ?>" target="_self"><?php _e( 'Add to Google Calendar', 'order-delivery-date' ); ?></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <table cellspacing="0" cellpadding="0" border="0" style="border-collapse: separate!important;border-radius: 3px;background-color: #00add8" class="add_to_other_cal_button">
                        <tbody>
                            <tr>    
                                <td valign="middle" align="center" style="font-family:Helvetica;font-size:12px;padding:7px">
                                    <a style="font-weight:bold;letter-spacing:normal;line-height:100%;text-align:center;text-decoration:none;color:#f5f5f5;word-wrap:break-word" title="Add to other Calendar" href="<?php echo $other_calendar_href; ?>" target="_self"><?php _e( 'Add to other Calendar', 'order-delivery-date' ); ?></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php 
                } else {
                    ?>
                    <table cellspacing="0" cellpadding="0" border="0" style="border-collapse: separate!important;border-radius: 3px;background-color: #00add8" class="add_to_gcal_button">
                        <tbody>
                            <tr>    
                                <td valign="middle" align="center" style="font-family:Helvetica;font-size:12px;padding:7px">
                                    <a style="font-weight:bold;letter-spacing:normal;line-height:100%;text-align:center;text-decoration:none;color:#ffffff;word-wrap:break-word" title="Add to Google Calendar" href="<?php echo $href; ?>" target="_blank"><?php _e( 'Add to Google Calendar', 'order-delivery-date' ); ?></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <table cellspacing="0" cellpadding="0" border="0" style="border-collapse: separate!important;border-radius: 3px;background-color: #00add8" class="add_to_other_cal_button">
                        <tbody>
                            <tr>    
                                <td valign="middle" align="center" style="font-family:Helvetica;font-size:12px;padding:7px">
                                    <a style="font-weight:bold;letter-spacing:normal;line-height:100%;text-align:center;text-decoration:none;color:#f5f5f5;word-wrap:break-word" title="Add to other Calendar" href="<?php echo $other_calendar_href; ?>" target="_blank"><?php _e( 'Add to other Calendar', 'order-delivery-date' ); ?></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php 
                }
            }
        }
    }
    
    /**
     * Adds buttons in the WooCommerce customer emails when Integration mode is selected as manually
     * using which customers can add deliveries/event into their calendars.
     *
     * @param WC_Order $order - Order Object
     * @param bool $sent_to_admin (default: false)
     * @param bool $plain_text (default: false)
     *
     * @hook woocommerce_email_customer_details
     * @since 4.0
     */
    function orddd_admin_add_to_calendar_email_notification( $order, $sent_to_admin = true, $plain_text = false ) {
        if( $sent_to_admin === true ) {
            if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
                $ordd_order_id = $order->get_id();
            } else {
                $ordd_order_id = $order->id;
            }

            $data = get_post_meta( $ordd_order_id );    

            $location = orddd_common::orddd_get_order_location( $ordd_order_id );
            $shipping_method = orddd_common::orddd_get_order_shipping_method( $ordd_order_id );
            $product_category = orddd_common::orddd_get_order_product_category( $ordd_order_id );
            $shipping_class = orddd_common::orddd_get_product_shipping_class( $ordd_order_id );

            $timeslot_field_label = orddd_common::orddd_get_delivery_time_field_label( $shipping_method, $product_category, $shipping_class, $location );

            $orddd = new stdClass();
            if ( isset( $data[ '_orddd_timestamp' ][ 0 ] ) && $data[ '_orddd_timestamp' ][ 0 ] != '' ) {
                $delivery_date = date( "d-m-Y", $data[ '_orddd_timestamp' ][ 0 ] );
                $orddd->client_address = __( $data[ '_shipping_address_1' ][ 0 ] . " " . $data[ '_shipping_address_2' ][ 0 ] , 'order-delivery-date');
                $orddd->client_full_address = str_replace( "<br/>", ",", $order->get_formatted_shipping_address() );
                $orddd->client_city = __( $data[ '_shipping_city' ][ 0 ], 'order-delivery-date');
                $orddd->start = date( 'Y-m-d', strtotime( $delivery_date ) );
                $orddd->end = date( 'Y-m-d', strtotime( $delivery_date ) );
                if( isset( $data[ $timeslot_field_label ][ 0 ] ) && $data[ $timeslot_field_label ][ 0 ] != '' && $data[ $timeslot_field_label ][ 0 ] != 'NA'  && $data[ $timeslot_field_label ][ 0 ] != 'choose' && $data[ $timeslot_field_label ][ 0 ] != 'select' ) {
                    $timeslot = explode( " - ", $data[ $timeslot_field_label ][ 0 ] );
                    $from_time = date( "H:i", strtotime( $timeslot[ 0 ] ) );
                    if( isset( $timeslot[ 1 ] ) && $timeslot[ 1 ] != '' ) {
                        $to_time = date( "H:i", strtotime( $timeslot[ 1 ] ) );
                        $orddd->end_time = $to_time;
                    } else {
                        $orddd->end_time = $from_time;
                    }
                    $orddd->start_time = $from_time;
                     
                } else {
                    $from_time = date( "H:i", $data[ '_orddd_timestamp' ][ 0 ] );
                    if( $from_time != '00:00' && $from_time != '00:01' && $from_time != '' ) {
                        $orddd->start_time = $from_time;
                        $orddd->end_time = $from_time;
                    } else {
                        $orddd->start_time = "";
                        $orddd->end_time = "";
                    }
                } 
                $orddd->client_email = $data[ '_billing_email' ][ 0 ];
                $orddd->client_name = $data[ '_shipping_first_name' ][ 0 ] . " " . $data[ '_shipping_last_name' ][ 0 ];
                $orddd->client_address = $data[ '_shipping_address_1' ][ 0 ]  . " " . $data[ '_shipping_address_2' ][ 0 ];
                $orddd->client_full_address = str_replace( "<br/>", ",", $order->get_formatted_shipping_address() );
                $orddd->client_phone = $data[ '_billing_phone' ][ 0 ];
                if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
                    $order_customer_note = $order->get_customer_note();
                } else {
                    $order_customer_note = $order->customer_note;
                }
                $orddd->order_note  = $order_customer_note;
                $get_order_items = $order->get_items();
                $products = $product_with_qty = "";
                foreach( $get_order_items as $key => $value ) {
                    $products .= $value[ 'name' ] . ", ";
                    $product_with_qty .= $value[ 'name' ] . "(QTY: " . $value[ 'qty' ] . "), ";
                }
                $products = substr( $products, 0, strlen( $products )-2 );
                $product_with_qty = substr( $product_with_qty, 0, strlen( $product_with_qty )-2 );
                $orddd->order_total  = $order->get_total();
                $orddd->products = $products;
                $orddd->product_with_qty = $product_with_qty;
                if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
                    $order_date_time = $order->get_date_created();
                } else {
                    $order_date_time = $order->post->post_date;
                }
                $orddd->order_date_time = $order_date_time;
                $order_date = date( "Y-m-d", strtotime( $order_date_time ) );
                $orddd->order_date = $order_date;
                $orddd->id = $order->get_order_number();
                $gcal = new OrdddGcal();
                $href = $gcal->gcal( $orddd );
                if( get_option( 'orddd_calendar_in_same_window' ) == 'on' ) {
                    ?>
                    <form method="post" action="<?php echo $href; ?>" target= "_self" id="add_to_google_calendar_form">
                        <input type="submit" id="add_to_google_calendar" name="add_to_google_calendar" value="<?php _e( 'Add to Google Calendar', 'order-delivery-date' ); ?>" />
                    </form>
                    <?php 
                } else {
                    ?>
                    <form method="post" action="<?php echo $href; ?>" target= "_blank" id="add_to_google_calendar_form">
                        <input type="submit" id="add_to_google_calendar" name="add_to_google_calendar" value="<?php _e( 'Add to Google Calendar', 'order-delivery-date' ); ?>" />
                    </form>
                    <?php 
                }
            }
        }
    }
    
    /**
     * Creates orders when the events are imported from the Google Calendar 
     *
     * @globals array $orddd_date_formats
     * @since 4.0
     */
    function import_events() {
        global $orddd_date_formats;
        
        $gmt = false;
        if( has_filter( 'orddd_gmt_calculations' ) ) {
            $gmt = apply_filters( 'orddd_gmt_calculations', '' );
        }
        $current_time = current_time( 'timestamp', $gmt );

        if( isset( $_POST[ 'ics_feed_key' ] ) ) {
            $ics_url_key = $_POST[ 'ics_feed_key' ];
        } else {
            $ics_url_key = '';
        }
       
        $event_uids = get_option( 'orddd_event_uids_ids' );
        if( $event_uids == '' || $event_uids == '{}' || $event_uids == '[]' || $event_uids == 'null' ) {
            $event_uids = array();
        }
        
        $ics_feed_urls = get_option( 'orddd_ics_feed_urls' );
        if( $ics_feed_urls == '' || $ics_feed_urls == '{}' || $ics_feed_urls == '[]' || $ics_feed_urls == 'null' ) {
            $ics_feed_urls = array();
        }
        
        if( is_array( $ics_feed_urls ) && count( $ics_feed_urls ) > 0 && isset( $ics_feed_urls[ $ics_url_key ] ) ) {
            $ics_feed = $ics_feed_urls[ $ics_url_key ];
            $ics_feed = str_replace( 'https://', '', $ics_feed );
        } else {
            $ics_feed = '';
        }
        $ical = new SG_Orddd_iCalReader( $ics_feed );
        $ical_array = $ical->getEvents();
        if( isset( $ical_array ) ) {
            foreach( $ical_array as $key_event => $value_event ) {
               //   Do stuff with the event $event
                if( !in_array( $value_event->uid, $event_uids ) ) {
                    if ( !$current_time ) {
                        $tdif = 0;
                    } else {
                        $tdif = $current_time - time();
                    }
                    
                    $time_format = get_option( 'orddd_delivery_time_format' );
                    if ( $time_format == '1' ) {
                        $time_format_to_show = 'h:i A';
                    } else {
                        $time_format_to_show = 'H:i';
                    }
                    
                    if( $value_event->getStart() != "" ) {
                        $event_start = $value_event->getStart() + $tdif;
                        if( $event_start >= $current_time ) {
                            $my_post = array(
                                'post_type' => 'shop_order',
                                'post_status' => 'wc-pending'
                            );
                            // Insert the post into the database
                            $order_id = wp_insert_post( $my_post );
                            
                            $user = get_user_by( 'slug', 'admin' );
                            $author_email = '';
                            if ( isset( $user->data->user_email ) ) {
                                $author_email = $user->data->user_email;
                            }

                            $data = array(
                                'comment_post_ID' => $order_id,
                                'comment_content' => $value_event->getSummary(),
                                'comment_author' => 'author',
                                'comment_author_email' => $author_email,
                                'comment_agent' => 'WooCommerce',
                                'comment_type' => 'order-note'
                            );
                            wp_insert_comment( $data );
                            
                            $data = array(
                                'comment_post_ID' => $order_id,
                                'comment_content' => $value_event->getDescription(),
                                'comment_author' => 'author',
                                'comment_author_email' => $author_email,
                                'comment_agent' => 'WooCommerce',
                                'comment_type' => 'order-note'
                            );
                            wp_insert_comment( $data );
                        }
                    }
                   
                    if( $value_event->getEnd() != "" && $value_event->getStart() != "" ) {
                        $event_start = $value_event->getStart() + $tdif;
                        $event_end = $value_event->getEnd() + $tdif;
                        $event_date_to_update = date( $orddd_date_formats[ get_option( 'orddd_delivery_date_format' ) ], $event_start );
                        $event_timestamp = strtotime( date( "Y-m-d", $event_start ) );
                        if( $event_end >= $current_time && $event_start >= $current_time ) {
                            $lockout_date = date( "Y-m-d", $event_start );
                            $event_from_time = date( $time_format_to_show, $event_start );
                            $event_to_time = date( $time_format_to_show, $event_end );
                            $time_slot_for_event = $event_from_time . " - " . $event_to_time;
                            $existing_timeslots_str = get_option( 'orddd_delivery_time_slot_log' );
                            $existing_timeslots_arr = json_decode( $existing_timeslots_str );
                            if ( is_array( $existing_timeslots_arr ) && count( $existing_timeslots_arr ) > 0 ) {
                                if( $existing_timeslots_arr == 'null' ) {
                                    $existing_timeslots_arr = array();
                                }
                                foreach ( $existing_timeslots_arr as $k => $v ) {
                                    $fh = $v->fh;
                                    $fm = $v->fm;
                                    $th = $v->th;
                                    $tm = $v->tm;
                                    $from_time = $v->fh . ":" . $v->fm;
                                    $ft = date( $time_format_to_show, strtotime( $from_time ) );
                                    if ( $v->th != 00 ){
                                        $to_time = $v->th . ":" . $v->tm;
                                        $tt = date( $time_format_to_show, strtotime( $to_time ) );
                                        $key = $ft . " - " . $tt;
                                    } else {
                                        $key = $ft;
                                    }
                                    if ( $key == $time_slot_for_event ) {
                                        $event_date = date( "j-n-Y", $event_start );
                                        orddd_process::orddd_update_time_slot( $time_slot_for_event, $event_date );
                                        update_post_meta( $order_id, get_option( 'orddd_delivery_timeslot_field_label' ), esc_attr( $time_slot_for_event ) );
                                    }   
                                }
                            }
                            orddd_process::orddd_update_lockout_days( $lockout_date );
                            update_post_meta( $order_id, '_orddd_timestamp', $event_timestamp );
                            update_post_meta( $order_id, get_option( 'orddd_delivery_date_field_label' ), esc_attr( $event_date_to_update ) );
                        }
                    } else if( $value_event->getStart() != "" && $value_event->getEnd() == "" ) {
                        $event_start = $value_event->getStart() + $tdif;
                        $event_date_to_update = date( $orddd_date_formats[ get_option( 'orddd_delivery_date_format' ) ], $event_start );
                        $event_timestamp = strtotime( date( "Y-m-d", $event_start ) );
                        if( $event_start >= $current_time ) {
                            $lockout_date = date( "Y-m-d", $event_start );
                            if( get_option( 'orddd_enable_time_slot' ) == "on" ) {
                                $event_from_time = date( $time_format_to_show, $event_start );
                                $existing_timeslots_str = get_option( 'orddd_delivery_time_slot_log' );
                                $existing_timeslots_arr = json_decode( $existing_timeslots_str );
                                if ( is_array( $existing_timeslots_arr ) && count( $existing_timeslots_arr ) > 0 ) {
                                    if( $existing_timeslots_arr == 'null' ) {
                                        $existing_timeslots_arr = array();
                                    }
                                    foreach ( $existing_timeslots_arr as $k => $v ) {
                                        $fh = $v->fh;
                                        $fm = $v->fm;
                                        $th = $v->th;
                                        $tm = $v->tm;
                                        $from_time = $v->fh . ":" . $v->fm;
                                        $ft = date( $time_format_to_show, strtotime( $from_time ) );
                                        if ( $v->th != 00 ){
                                            $to_time = $v->th . ":" . $v->tm;
                                            $tt = date( $time_format_to_show, strtotime( $to_time ) );
                                            $key = $ft . " - " . $tt;
                                        } else {
                                            $key = $ft;
                                        }
                                        if ( $key == $event_from_time ) {
                                            $event_date = date( "j-n-Y", $event_start );
                                            orddd_process::orddd_update_time_slot( $event_from_time, $event_date );
                                            update_post_meta( $order_id, get_option( 'orddd_delivery_timeslot_field_label' ), esc_attr( $event_from_time ) );
                                        }
                                    }
                                }
                            } else if( get_option( 'orddd_enable_delivery_time' ) == 'on' ) {
                                $event_date_to_update = date( $orddd_date_formats[ get_option( 'orddd_delivery_date_format' ) ] . " " . $time_format_to_show , $event_start );
                                $event_timestamp = $event_start;
                            }
                            orddd_process::orddd_update_lockout_days( $lockout_date );
                            update_post_meta( $order_id, '_orddd_timestamp', $event_timestamp );
                            update_post_meta( $order_id, get_option( 'orddd_delivery_date_field_label' ), esc_attr( $event_date_to_update ) );
                        }
                    }               
                    
                    array_push( $event_uids, $value_event->uid );
                    update_option( 'orddd_event_uids_ids', $event_uids );
                    
                    $event_orders = get_option( 'orddd_event_order_ids' );
                    if( $event_orders == '' || $event_orders == '{}' || $event_orders == '[]' || $event_orders == 'null' ) {
                        $event_orders = array();
                    }
                    if( isset( $order_id ) && '' != $order_id ) {
                        array_push( $event_orders, $order_id );
                    }
                    update_option( 'orddd_event_order_ids', $event_orders );
                }
            }
            echo "All the Events are Imported.";
        }
        die();
    }
    
    /**
     * Save the Google Import URL Feeds from
     * Order Delivery Date->Settings->Google Calendar Sync tab->Import Events
     * Called via AJAX
     *
     * @since 4.0
     */
    function save_ics_url_feed() {
        $ics_table_content = '';
        if( isset( $_POST[ 'ics_url' ] ) ) {
            $ics_url = $_POST[ 'ics_url' ];
        } else {
            $ics_url = '';
        }

        if( $ics_url != '' ) {
            $ics_feed_urls = get_option( 'orddd_ics_feed_urls' );
            if( $ics_feed_urls == '' || $ics_feed_urls == '{}' || $ics_feed_urls == '[]' || $ics_feed_urls == 'null' ) {
                $ics_feed_urls = array();
            }
            
            if( !in_array( $ics_url, $ics_feed_urls ) ) {
                array_push( $ics_feed_urls, $ics_url );
                update_option( 'orddd_ics_feed_urls', $ics_feed_urls );
                $ics_table_content = 'yes';
            }
        }
        
        echo $ics_table_content;
        die();
    }
    
    /**
     * Delete the Google Import URL Feeds from
     * Order Delivery Date->Settings->Google Calendar Sync tab->Import Events
     * Called via AJAX
     *
     * @since 4.0
     */
    function delete_ics_url_feed() {
        $ics_table_content = '';
        if( isset( $_POST[ 'ics_feed_key' ] ) ) {
            $ics_url_key = $_POST[ 'ics_feed_key' ];
        } else {
            $ics_url_key = '';
        }
        
        if( $ics_url_key != '' ) {
            $ics_feed_urls = get_option( 'orddd_ics_feed_urls' );
            if( $ics_feed_urls == '' || $ics_feed_urls == '{}' || $ics_feed_urls == '[]' || $ics_feed_urls == 'null' ) {
                $ics_feed_urls = array();
            }
        
            unset( $ics_feed_urls[ $ics_url_key ] );
            update_option( 'orddd_ics_feed_urls', $ics_feed_urls );
            $ics_table_content = 'yes';
        }
        
        echo $ics_table_content;
        die();
    }
    
    /**
     * Export deliveries to Google Calendar from the Order Delivery Date->Delivery Calendar->Add to Calendar button.
     * This is especially used to export deliveries for orders that were placed before
     * Automated Google Calendar Sync is enabled.
     *
     * @globals resource $wpdb
     * @since 4.0
     */
    public static function orddd_admin_delivery_calendar_events() {
        global $wpdb;
        
        $gmt = false;
        if( has_filter( 'orddd_gmt_calculations' ) ) {
            $gmt = apply_filters( 'orddd_gmt_calculations', '' );
        }
        $current_time = current_time( 'timestamp', $gmt );

        $total_orders_to_export = orddd_common::orddd_get_total_orders_to_export();
        $gcal = new OrdddGcal();
        $current_timestamp = strtotime( date( 'd-m-Y', $current_time ) );
        foreach( $total_orders_to_export as $key => $value ) {
            $event_details = array();
            $data = get_post_meta( $value );
            $order = new WC_Order( $value );
            
            $location = orddd_common::orddd_get_order_location( $order_id );
            $shipping_method = orddd_common::orddd_get_order_shipping_method( $value );
            $product_category = orddd_common::orddd_get_order_product_category( $value );
            $shipping_class = orddd_common::orddd_get_product_shipping_class( $value );

            $timeslot_field_label = orddd_common::orddd_get_delivery_time_field_label( $shipping_method, $product_category, $shipping_class, $location );
            
            $date_field_label = get_option( 'orddd_delivery_date_field_label' );
            $orddd = new stdClass();
            if ( isset( $data[ '_orddd_timestamp' ][ 0 ] ) && $data[ '_orddd_timestamp' ][ 0 ] != '' && $data[ '_orddd_timestamp' ][ 0 ] >= $current_timestamp ) {
                $delivery_date = date( "d-m-Y", $data[ '_orddd_timestamp' ][ 0 ] );
                $event_details[ 'h_deliverydate' ] = $delivery_date;
                $event_details[ '_orddd_timestamp' ] = $data[ '_orddd_timestamp' ][ 0 ];
                if( isset( $data[ $date_field_label ][ 0 ] ) ) {
                    $event_details[ 'e_deliverydate' ] = $data[ $date_field_label ][ 0 ];
                } else {
                    $event_details[ 'e_deliverydate' ] = "";
                }
            } elseif ( isset( $data[ $date_field_label ][ 0 ] ) && $data[ $date_field_label ][ 0 ] != '' && $data[ $date_field_label ][ 0 ] != 'null' && $data[ $date_field_label ][ 0 ] != '{}' && $data[ $date_field_label ][ 0 ] != '[]' ) {
                $delivery_date_timestamp = strtotime( str_replace( ",", " ", $data[ $date_field_label ][ 0 ] ) );
                $delivery_date = date( "d-m-Y", $delivery_date_timestamp );
                $event_details[ 'h_deliverydate' ] = $delivery_date;
                $event_details[ 'e_deliverydate' ] = $data[ $date_field_label ][ 0 ];
            } elseif ( isset( $data[ ORDDD_DELIVERY_DATE_FIELD_LABEL ][ 0 ] ) && $data[ ORDDD_DELIVERY_DATE_FIELD_LABEL ][ 0 ] != '' && $data[ ORDDD_DELIVERY_DATE_FIELD_LABEL ][ 0 ] != 'null' && $data[ ORDDD_DELIVERY_DATE_FIELD_LABEL ][ 0 ] != '{}' && $data[ ORDDD_DELIVERY_DATE_FIELD_LABEL ][ 0 ] != '[]' ) {
                $delivery_date_timestamp = strtotime( $data[ ORDDD_DELIVERY_DATE_FIELD_LABEL ][ 0 ] );
                $delivery_date = date( "d-m-Y", $delivery_date_timestamp );
                $event_details[ 'h_deliverydate' ] = $delivery_date;
                if( isset( $data[ ORDDD_DELIVERY_DATE_FIELD_LABEL ][ 0 ] ) ) {
                    $event_details[ 'e_deliverydate' ] = $data[ ORDDD_DELIVERY_DATE_FIELD_LABEL ][ 0 ];
                } else {
                    $event_details[ 'e_deliverydate' ] = "";
                }
            }
            if( isset( $event_details[ 'h_deliverydate' ] ) && $event_details[ 'h_deliverydate' ] != "" ) {
                if( isset( $data[ $timeslot_field_label ][ 0 ] ) && $data[ $timeslot_field_label ][ 0 ] != '' && $data[ $timeslot_field_label ][ 0 ] != 'NA'  && $data[ $timeslot_field_label ][ 0 ] != 'choose' && $data[ $timeslot_field_label ][ 0 ] != 'select' ) {
                    $timeslot = explode( " - ", $data[ $timeslot_field_label ][ 0 ] );
                    $from_time = date( "H:i", strtotime( $timeslot[ 0 ] ) );
                    if( isset( $timeslot[ 1 ] ) && $timeslot[ 1 ] != '' ) {
                        $to_time = date( "H:i", strtotime( $timeslot[ 1 ] ) );
                        $time_slot = $from_time . " - " . $to_time;
                    } else {
                        $time_slot = $from_time;
                    }
                    $event_details[ 'time_slot' ] = $time_slot;
                     
                }
                $event_details[ 'billing_email' ] = $data[ '_billing_email' ][ 0 ];
                $event_details[ 'shipping_first_name' ] = $data[ '_shipping_first_name' ][ 0 ];
                $event_details[ 'shipping_last_name' ] = $data[ '_shipping_last_name' ][ 0 ];
                $event_details[ 'shipping_address_1' ] = $data[ '_shipping_address_1' ][ 0 ];
                $event_details[ 'shipping_address_2' ] = $data[ '_shipping_address_2' ][ 0 ];
                $event_details[ 'billing_phone' ] = $data[ '_billing_phone' ][ 0 ];
                if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
                    $order_customer_note = $order->get_customer_note();
                } else {
                    $order_customer_note = $order->customer_note;
                }
                $event_details[ 'order_comments' ]  = $order_customer_note;
                $gcal->insert_event( $event_details, $value, false );
            }
        }
        die();
    }
}
$orddd_calendar_sync = new orddd_calendar_sync();
?>