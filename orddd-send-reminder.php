<?php 
/**
 * Order Delivery Date Pro for WooCommerce
 *
 * Menu page for sending manual reminder emails and setting automatic reminders for deliveries.
 *
 * @author      Tyche Softwares
 * @package     Order-Delivery-Date-Pro-for-WooCommerce/Reminder-Emails
 * @since       8.6
 * @category    Classes
 */


if ( !class_exists( 'orddd_send_reminder' ) ) {

    class orddd_send_reminder {
        /**
         * Default Constructor
         *
         * @since 8.6
         */
        public function __construct() {
            //Add a sub menu in the main menu of the plugin if added.
            add_action( 'orddd_add_submenu', array( &$this, 'orddd_re_add_submenu' ) );
            add_action( 'admin_init',        array( &$this, 'orddd_send_automatic_reminder' ), 10 );
            add_filter( 'woocommerce_screen_ids', array( $this, 'orddd_add_screen_id' ) );
            add_action( 'orddd_auto_reminder_emails', array( $this, 'orddd_send_auto_reminder_emails' ) );
            add_filter( 'woocommerce_template_directory', array( &$this, 'orddd_template_directory' ),10, 2 );
        }
        
        /**
         * Change the template directory for overridding templates
         *
         * @since 8.7
         */
        public static function orddd_template_directory( $woocommerce, $template ) {
            if( strpos( $template, 'customer-delivery-reminder.php' ) !== false ) {
                return 'order-delivery-date';
            } else {
                return $woocommerce;
            }
        }
		
        /**
         * Add the id of the Send reminder page into screen ids page.
         * 
         * @since 8.6
         */

        public static function orddd_add_screen_id ( $screen_ids ) {
            $screen_ids[] = 'order-delivery-date_page_orddd_send_reminder_page';
            return $screen_ids;
        }

        /**
         * Adds a submenu to main menu. 
         * 
         * @since 8.6
         */
        public function orddd_re_add_submenu() {
            $page = add_submenu_page( 'order_delivery_date', 
                __( 'Send Reminder', 'order-delivery-date' ), 
                __( 'Send Reminder', 'order-delivery-date' ), 
                'manage_woocommerce', 
                'orddd_send_reminder_page', 
                array( 'orddd_send_reminder', 'orddd_send_reminder_page' ) 
            );

        }

        /**
         * Add content to the Send Reminder page. 
         *
         * @since 8.6
         */
        public static function orddd_send_reminder_page() {
            if ( ! empty( $_POST ) && check_admin_referer( 'orddd_delivery_reminder' ) ) {
                $order_ids    = isset( $_POST[ 'orddd_reminder_order_id' ] ) && '' != $_POST[ 'orddd_reminder_order_id' ] ? $_POST[ 'orddd_reminder_order_id' ] : ''; 
                $subject      = isset( $_POST[ 'orddd_reminder_subject' ] ) && '' != $_POST[ 'orddd_reminder_subject' ] ? $_POST[ 'orddd_reminder_subject' ] : 'Delivery Reminder'  ;
                $message      = isset( $_POST[ 'orddd_reminder_message' ] ) && '' != $_POST[ 'orddd_reminder_message' ] ? $_POST[ 'orddd_reminder_message' ] : '' ;
                $mailer       = WC()->mailer();
                $reminder     = $mailer->emails[ 'ORDDD_Email_Delivery_Reminder' ];
                
                if( is_array( $order_ids ) && !empty( $order_ids ) ) {
                    foreach( $order_ids as $key => $value ) {
                        $reminder->trigger( $value, $subject, $message );
                        echo '<div class="updated fade"><p>' . __( 'Reminder sent successfully', 'order-delivery-date' ) . '</p></div>';
                    }
                }
            }

            $all_order_ids = orddd_common::orddd_get_all_future_orders();
            wc_get_template( 
                'orddd-reminder-email-view.php',
                array(
                    'order_ids'         => $all_order_ids
                ),
                'order-delivery-date/',
                ORDDD_TEMPLATE_PATH );
        }

        /**
         * Add a setting for automatic reminders to set the number of days
         *  
         * @since 8.6
         */

        public static function orddd_send_automatic_reminder() {
            add_settings_section(
                'orddd_reminder_section',        
                __( '', 'order-delivery-date' ),    
                array( 'orddd_send_reminder', 'orddd_reminder_settings_section_callback' ),       
                'orddd_send_reminder_page'             
            );
            add_settings_field(
                'orddd_reminder_email_before_days',
                __( 'Number of days for reminder before Delivery Date', 'order-delivery-date' ),
                array( 'orddd_send_reminder', 'orddd_reminder_email_before_days_callback' ),
                'orddd_send_reminder_page',
                'orddd_reminder_section',
                array( __( 'Send the reminder email X number of days before the Delivery Date.', 'order-delivery-date' ) )
            );

            register_setting(
                'orddd_reminder_settings',
                'orddd_reminder_email_before_days'
            );        

        }

        public static function orddd_reminder_settings_section_callback() {}

        public static function orddd_reminder_email_before_days_callback( $args ) {
            $reminder_email_before_days = get_option( 'orddd_reminder_email_before_days' );
            if( $reminder_email_before_days == '' ) {
                $reminder_email_before_days = 0;
            }

            if( $reminder_email_before_days > 0 ) {
                if ( ! wp_next_scheduled( 'orddd_auto_reminder_emails' ) ) {
                    wp_schedule_event( time(), 'daily', 'orddd_auto_reminder_emails' );
                }
            } else {
                wp_clear_scheduled_hook( 'orddd_auto_reminder_emails' );
            }

            echo '<input type="number" name="orddd_reminder_email_before_days" id="orddd_reminder_email_before_days" value="' . $reminder_email_before_days .'"/>';
            $html = '<label for="orddd_reminder_email_before_days"> ' . $args[ 0 ] . '</label>';
            echo $html;
        }
        
        /**
         * Scheduled event for the automatic reminder emails
         * 
         * @since 4.10.0
         */
        public static function orddd_send_auto_reminder_emails() {
            $gmt = false;
            if( has_filter( 'orddd_gmt_calculations' ) ) {
                $gmt = apply_filters( 'orddd_gmt_calculations', '' );
            }

            $current_time = current_time( 'timestamp', $gmt );
            $future_orders              = orddd_common::orddd_get_all_future_orders();
            $reminder_email_before_days = get_option( 'orddd_reminder_email_before_days' );
            
            $mailer       = WC()->mailer();
            $reminder     = $mailer->emails[ 'ORDDD_Email_Delivery_Reminder' ];
            $current_date = date( 'j-n-Y', $current_time );
            $current_date_time = strtotime( $current_date );
            foreach( $future_orders as $key => $value ) {
                $orddd_timestamp = get_post_meta( $value->ID, '_orddd_timestamp', true );
                $orddd_date               = date( 'j-n-Y', $orddd_timestamp );
                $orddd_date_timestamp     = strtotime( $orddd_date );
                $days_diff = absint( ( $orddd_date_timestamp - $current_date_time ) );
                if( $days_diff == absint( $reminder_email_before_days * 86400 ) ) {
                    $reminder->trigger( $value->ID );
                }
            }
        }

        /**
         * Ajax call for saving the email draft on Manual Reminder page
         * 
         * @since 8.6
         */
        public static function orddd_save_reminder_message() {
            $message = $_POST[ 'message' ];
            $subject = $_POST[ 'subject' ];

            if( isset( $message ) && '' != $message ) {
                update_option( 'orddd_reminder_message', $message );
            }

            if( isset( $subject ) && '' != $subject ) {
                update_option( 'orddd_reminder_subject', $subject );
            }
        }
    }
    new orddd_send_reminder();
}