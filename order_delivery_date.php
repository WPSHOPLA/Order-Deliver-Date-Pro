<?php 
/*
* Plugin Name: Order Delivery Date Pro for WooCommerce
* Plugin URI: https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/
* Description: This plugin allows customers to choose their preferred Order Delivery Date & Delivery Time during checkout. The plugin works with <strong>WooCommerce</strong>. To get started: Go to <strong>Dashboard -> <a href="admin.php?page=order_delivery_date">Order Delivery Date</a></strong>.
* Author: Tyche Softwares
* Version: 8.7
* Author URI: http://www.tychesoftwares.com/about
* Contributor: Tyche Softwares, http://www.tychesoftwares.com/
* Text Domain: order-delivery-date
* Requires PHP: 5.6
* WC requires at least: 3.0.0
* WC tested up to: 3.4.3
* Copyright: © 2009-2015 Tyche Softwares.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Current Order Delivery Date Pro version
 * 
 * @since 1.0
 */

global $orddd_version;
$orddd_version = '8.7';

if ( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater if it doesn't already exist
	include( dirname( __FILE__ ) . '/plugin-updates/EDD_SL_Plugin_Updater.php' );
}

include_once( 'orddd-config.php' );
include_once( 'lang.php' );
include_once( 'orddd-widget.php' );
include_once( 'orddd-availability-widget.php' );
include_once( 'orddd-update.php' );
include_once( 'orddd-send-reminder.php' );

/**
 * Retrieve our license key from the DB
 * 
 * @since 1.0
 */
$license_key = trim( get_option( 'edd_sample_license_key_odd_woo' ) );

/** 
 * Define Url for the license checker.
 *
 * @since 2.5
 */
define( 'EDD_SL_STORE_URL_ODD_WOO', 'http://www.tychesoftwares.com/' ); 

/** 
 * Define Download name for the license checker.
 *
 * @since 2.5
 */
define( 'EDD_SL_ITEM_NAME_ODD_WOO', 'Order Delivery Date Pro for Woocommerce' ); 

/** 
 * Setup the updater
 * 
 * @since 2.5
 */
$edd_updater = new EDD_SL_Plugin_Updater( EDD_SL_STORE_URL_ODD_WOO, __FILE__, array(
    'version' 	=> '8.7', 		// current version number
    'license' 	=> $license_key, 	// license key (used get_option above to retrieve from DB)
    'item_name' => EDD_SL_ITEM_NAME_ODD_WOO, 	// name of this plugin
    'author' 	=> 'Ashok Rane'  // author of this plugin
)
);

/** 
* Schedule an action if it's not already scheduled for deleting back dated data
* 
* @since 4.0
*/
if ( ! wp_next_scheduled( 'orddd_delete_old_lockout_data_action' ) ) {
    wp_schedule_event( time(), 'daily_once', 'orddd_delete_old_lockout_data_action' );
    
}

/** 
* Schedule an action if it's not already scheduled for tracking data
* 
* @since 6.8
*/

if ( ! wp_next_scheduled( 'ts_tracker_send_event' ) ) {
    wp_schedule_event( time(), 'daily_once', 'ts_tracker_send_event' );
}

if ( !class_exists( 'order_delivery_date' ) ) {

	/**
	 * Main order_delivery_date Class
	 *
	 * @class order_delivery_date
	 */
    class order_delivery_date {

        /**
         * Default Constructor.
         * 
         * @since 1.0
         */
		public function __construct() {
			/**
			 * Including files
			 */
			
			add_action( 'init', 									array( &$this, 'orddd_include_files' ), 5 );
			add_action( 'admin_init', 								array( &$this, 'orddd_include_files' ) );

			//Installation
			register_activation_hook( __FILE__, array( &$this, 'orddd_activate' ) );

		    //Cron to run script for deleting past date lockouts
		    add_filter( 'cron_schedules',                       array( &$this, 'orddd_add_cron_schedule' ) );
		    add_action( 'orddd_delete_old_lockout_data_action', array( &$this, 'orddd_delete_old_lockout_data_cron' ) );

		    //Capabilities to access Order Delivery Menu
		    add_action( 'admin_init', array( &$this, 'orddd_capabilities' ) );
			
			//Settings link and Documentation link added on the Plugins page
		    add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( &$this, 'orddd_plugin_settings_link' ) );
		    add_filter( 'plugin_row_meta',                                  array( &$this, 'orddd_plugin_row_meta' ), 10, 2 );
		    
		    // Language Translation
		    add_action( 'init', array( &$this, 'orddd_update_po_file' ) );
			
			//Ajax calls
			add_action( 'init', array( &$this, 'orddd_load_ajax' ) );
			add_action( 'init', array( &$this, 'orddd_add_component_file' ) );

			//Check for Lite version
			add_action( 'admin_init', array( &$this, 'orddd_check_if_lite_active' ) );
		}
        
        /**
         *
         * 
         * @since 8.7
         */

        public function orddd_include_files() {
        	include_once( 'integration.php' );	
			include_once( 'orddd-process.php' );        		
    		include_once( 'orddd-shipping-multiple-address.php' );
        	
        	include_once( 'orddd-common.php' );
			include_once( 'orddd-scripts.php' );

			include_once( 'orddd-calendar-sync.php' );
			include_once( 'class-orddd-email-manager.php' );
			include_once( 'orddd-locations.php' );

        	if( is_admin() ) {
        		include_once( 'filter.php' );
        		include_once( 'orddd-settings.php' );
        		include_once( 'license.php' );
        		include_once( 'orddd-view-deliveries.php' );
				include_once( 'orddd-admin-delivery.php' );
				include_once( 'includes/adminend-events-jsons.php' );
				include_once( 'orddd-privacy.php' );
				
        	}
        }
        /**
		 * Add Default settings to WordPress options table when plugin is installed.
		 * 
		 * @hook register_activation_hook
		 * @globals resource $wpdb WordPress Object
		 * @globals array $orddd_weekdays Weekdays array
		 * 
		 * @since 1.0
		 */
		public function orddd_activate() {
		    if ( ! order_delivery_date::orddd_check_woo_installed() ) {
		        return;
		    }

		    global $wpdb, $orddd_weekdays;
		    
		    //Check if installed for the first time.
		    add_option( 'orddd_pro_installed', 'yes' );
		    
		    //Date Settings
		    add_option( 'orddd_enable_delivery_date', '' );
		    foreach ( $orddd_weekdays as $n => $day_name ) {
		        add_option( $n, 'checked' );
		    }
		    add_option( 'orddd_minimumOrderDays', '0' );
		    add_option( 'orddd_number_of_dates', '30' );
		    add_option( 'orddd_date_field_mandatory', '' );
		    add_option( 'orddd_lockout_date_after_orders', '' );
		    add_option( 'orddd_lockout_days', '' );
		    add_option( 'orddd_delivery_checkout_options', 'delivery_calendar' );
		    
		    //Specific delivery dates
		    add_option( 'orddd_enable_specific_delivery_dates', '' );
		    add_option( 'orddd_delivery_dates', '' );
		    
		    //Time options
		    add_option( 'orddd_enable_delivery_time', '' );
		    add_option( 'orddd_delivery_from_hours', '' );
		    add_option( 'orddd_delivery_to_hours', '' );
		    add_option( 'orddd_delivery_time_format', '2' );
		    
		    //Same day delivery options
		    add_option( 'orddd_enable_same_day_delivery', '' );
		    add_option( 'orddd_disable_same_day_delivery_after_hours', '' );
		    add_option( 'orddd_disable_same_day_delivery_after_minutes', '' );
		    
		    //Next day delivery options
		    add_option( 'orddd_enable_next_day_delivery', '' );
		    add_option( 'orddd_disable_next_day_delivery_after_hours', '' );
		    add_option( 'orddd_disable_next_day_delivery_after_minutes', '' );
		    
		    //Holidays
		    add_option( 'orddd_delivery_date_holidays', '' );
		    
		    //Appearance options
		    add_option( 'orddd_delivery_date_format', ORDDD_DELIVERY_DATE_FORMAT );
		    add_option( 'orddd_delivery_date_field_label', ORDDD_DELIVERY_DATE_FIELD_LABEL );
		    add_option( 'orddd_delivery_date_field_placeholder', ORDDD_DELIVERY_DATE_FIELD_PLACEHOLDER );
		    add_option( 'orddd_delivery_date_field_note', ORDDD_DELIVERY_DATE_FIELD_NOTE );
		    add_option( 'orddd_number_of_months', '1' );
		    add_option( 'orddd_calendar_theme', ORDDD_CALENDAR_THEME );
		    add_option( 'orddd_calendar_theme_name', ORDDD_CALENDAR_THEME_NAME );
		    add_option( 'orddd_language_selected', 'en-GB' );
		    add_option( 'orddd_delivery_date_fields_on_checkout_page', 'billing_section' );
		    add_option( 'orddd_no_fields_for_virtual_product', 'on' );
		    add_option( 'orddd_cut_off_time_color', '#ff0000' );
		    add_option( 'orddd_booked_dates_color', '#ff0000' );
		    add_option( 'orddd_holiday_color', '#ff0000' );
		    add_option( 'orddd_available_dates_color', '#90EE90' );

		    
		    //Time slot
		    add_option( 'orddd_time_slot_mandatory', '' );
		    add_option( 'orddd_delivery_timeslot_format', '2' );
		    add_option( 'orddd_delivery_timeslot_field_label', ORDDD_DELIVERY_TIMESLOT_FIELD_LABEL );
		    add_option( 'orddd_show_first_available_time_slot_as_selected', '' );
		    
		    //Additional Settings
		    add_option( 'orddd_show_filter_on_orders_page_check', 'on' );
		    add_option( 'orddd_show_column_on_orders_page_check', 'on' );
		    add_option( 'orddd_show_fields_in_csv_export_check', 'on' );
		    add_option( 'orddd_show_fields_in_pdf_invoice_and_packing_slips', 'on'  );
		    add_option( 'orddd_show_fields_in_invoice_and_delivery_note', 'on' );
		    add_option( 'orddd_show_fields_in_cloud_print_orders', 'on' );
		    add_option( 'orddd_enable_default_sorting_of_column', 'on' );
		    add_option( 'orddd_enable_tax_calculation_for_delivery_charges', '' );
		    add_option( 'orddd_amazon_payments_advanced_gateway_compatibility', '' );
		    add_option( 'orddd_enable_autofill_of_delivery_date', 'on' );
		    	
		    //Extra Options
		    add_option( 'orddd_abp_hrs', 'HOURS' );
		    add_option( 'update_weekdays_value', 'yes' );
		    add_option( 'update_placeholder_value', 'no' );
		    
		    // Google Calendar Sync settings
		    add_option( 'orddd_calendar_event_location', 'ADDRESS' );
		    add_option( 'orddd_calendar_event_summary', 'SITE_NAME - ORDER_NUMBER' );
		    add_option( 'orddd_calendar_event_description', 'CLIENT (EMAIL), <br> PRODUCT_WITH_QTY' );

		    do_action( 'orddd_plugin_activate' );

		    orddd_update::orddd_update_install();
		}

		/**
		 * Function checks if the WooCommerce plugin is active or not. If it is not active then it will display a notice.
		 * 
		 * @hook admin_init
		 *
		 * @since 5.3
		 */
		
		function orddd_check_if_woocommerce_active() {
		    if ( ! self::orddd_check_woo_installed() ) {
		        if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
		            deactivate_plugins( plugin_basename( __FILE__ ) );
		            add_action( 'admin_notices', array( 'order_delivery_date', 'orddd_disabled_notice' ) );
		            if ( isset( $_GET[ 'activate' ] ) ) {
		                unset( $_GET[ 'activate' ] );
		            }
		        }
		    }
		}

		/**
		 * Check if WooCommerce is active.
		 * 
		 * @return bool True if WooCommerce is active, else false.
		 * @since 5.3
		 */
		public static function orddd_check_woo_installed() {
		    if ( class_exists( 'WooCommerce' ) ) {
		        return true;
		    } else {
		        return false;
		    }
		}
		
		
        /**
         * Run a cron once in week to delete old records for lockout
         * 
         * @hook cron_schedules
         *
         * @param array $schedules Existing Cron Schedules
         *
         * @return array Array of schedules
         * @since 4.0
         */
        function orddd_add_cron_schedule( $schedules ) {
            $schedules[ 'daily_once' ] = array(
                'interval' => 604800,  // one week in seconds
                'display'  => __( 'Once in a Week', 'order-delivery-date' ),
            );
            return $schedules;
        }

        /**
         * Hook into that action that'll fire once a week
         *
         * @hook orddd_delete_old_lockout_data_action
         * @since 4.0
         */
        function orddd_delete_old_lockout_data_cron() {
            $plugin_dir_path = plugin_dir_path( __FILE__ );
            require_once( $plugin_dir_path . 'orddd-run-script.php' );
        }

		
		/**
		 * Display a notice in the admin Plugins page if the plugin is activated while WooCommerce is deactivated.
		 * 
		 * @hook admin_notices
		 * @since 5.3
		 */
		public static function orddd_disabled_notice() {
		    $class = 'notice notice-error';
		    $message = __( 'Order Delivery Date Pro for WooCommerce plugin requires WooCommerce installed and activate.', 'order-delivery-date' );
		    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
		}
		
		/**
		 * Settings link on Plugins page
		 * 
		 * @hook plugin_action_links_order-delivery-date
		 * 
		 * @param array $links 
		 * @return array
		 * @since 1.0
		 */
		public function orddd_plugin_settings_link( $links ) {
		    $setting_link[ 'settings' ] = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=order_delivery_date' ) ) . '">Settings</a>';
		    $links = $setting_link + $links; 
		    return $links;
		}
		
		/**
		 * Documentation links on Plugins page
		 *
		 * @hook plugin_row_meta
		 * 
		 * @param array $links
		 * @param string $file
		 * @return array
		 *
		 * @since 1.0
		 */
		public function orddd_plugin_row_meta( $links, $file ) {
		    if ( $file == plugin_basename( __FILE__ ) ) {
		        unset( $links[2] );
		        $row_meta = array(
		            'plugin_site' => '<a href="' . esc_url( apply_filters( 'orddd_plugin_site_url', 'https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/' ) ) . '" title="' . esc_attr( __( 'Visit plugin site', 'order-delivery-date' ) ) . '">' . __( 'Visit plugin site', 'order-delivery-date' ) . '</a>',
		            'docs'        => '<a href="' . esc_url( apply_filters( 'orddd_docs_url',        'https://www.tychesoftwares.com/docs/docs/order-delivery-date-pro-for-woocommerce/' ) ) . '" title="' . esc_attr( __( 'View Documentation', 'order-delivery-date' ) ) . '">' . __( 'Docs', 'order-delivery-date' ) . '</a>',
		            'support'     => '<a href="' . esc_url( apply_filters( 'orddd_support_url',     'https://tychesoftwares.freshdesk.com/support/tickets/new' ) ) . '" title="' . esc_attr( __( 'Submit Ticket', 'order-delivery-date' ) ) . '">' . __( 'Submit Ticket', 'order-delivery-date' ) . '</a>',
		        );
		        return array_merge( $links, $row_meta );
		    }
		    return (array) $links;
		}
		
		/**
		 * Load Localization files.
		 * 
		 * @hook init
		 * 
		 * @return string $loaded Text domain
		 * @since 2.6.3
		 */
		public function orddd_update_po_file() {
		    $domain = 'order-delivery-date';
		    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		    if ( $loaded = load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '-' . $locale . '.mo' ) ) {
		        return $loaded;
		    } else {
		        load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
		    }
		}
		
		/** 
		 * Capability to allow shop manager to edit settings
		 * 
		 * @hook admin_init
		 * @since 3.1
		 */
		public function orddd_capabilities() {
		    $role = get_role( 'shop_manager' );
		    if( $role != '' ) {
		        $role->add_cap( 'manage_options' );
		    }
		}

		/** 
         * Used to load ajax functions required by plugin.
         * 
         * @since 1.0
         */
		public function orddd_load_ajax() {
			if ( !is_user_logged_in() ) {
				add_action( 'wp_ajax_nopriv_check_for_time_slot_orddd',    array( 'orddd_process', 'check_for_time_slot_orddd' ) );
				add_action( 'wp_ajax_nopriv_orddd_order_calendar_content', array( 'orddd_class_view_deliveries', 'orddd_order_calendar_content' ) );
				add_action( 'wp_ajax_nopriv_orddd_update_delivery_date',   array( 'orddd_process', 'orddd_update_delivery_date' ) );
				add_action( 'wp_ajax_nopriv_orddd_get_zone_id', array( 'orddd_common', 'orddd_get_zone_id' ) );
				add_action( 'wp_ajax_nopriv_orddd_update_delivery_session', array( 'orddd_process', 'orddd_update_delivery_session' ) );
				add_action( 'wp_ajax_nopriv_orddd_save_reminder_message',   array( 'orddd_send_reminder', 'orddd_save_reminder_message' ) );
			} else {
				add_action( 'wp_ajax_check_for_time_slot_orddd',           array( 'orddd_process', 'check_for_time_slot_orddd' ) );
				add_action( 'wp_ajax_orddd_order_calendar_content',        array( 'orddd_class_view_deliveries', 'orddd_order_calendar_content' ) );
				add_action( 'wp_ajax_orddd_update_delivery_date',          array( 'orddd_process', 'orddd_update_delivery_date' ) );
				add_action( 'wp_ajax_orddd_get_zone_id', array( 'orddd_common', 'orddd_get_zone_id' ) );
				add_action( 'wp_ajax_orddd_update_delivery_session', array( 'orddd_process', 'orddd_update_delivery_session' ) );
				add_action( 'wp_ajax_orddd_save_reminder_message', array( 'orddd_send_reminder', 'orddd_save_reminder_message' ) );
			}
		}

		/**
         * It will load the boilerplate components file. In this file we have included all boilerplate files.
         * We need to inlcude this file after the init hook.
         * @hook init
         */

        public static function orddd_add_component_file () {
            if ( true === is_admin() ) {
				include_once( 'includes/ordd-all-component.php' );
			}
        }

        /**
		 * Returns version number of the plugin
		 * 
		 * @return string Plugin version number
		 * @since 1.0
		 */
		public static function get_orddd_version() {
			$plugin_data = get_plugin_data( __FILE__ );
			$plugin_version = $plugin_data[ 'Version' ];
			return $plugin_version;
		}

		/**
		 * Function checks if the Order Delivery Date Lite version is active or not. If it is active then it will deactivate the lite version.
		 * 
		 * @hook admin_init
		 *
		 * @since 8.7
		 */
		
		public static function orddd_check_if_lite_active() {
			$is_insatlled = order_delivery_date::orddd_check_lite_installed();
			if ( order_delivery_date::orddd_check_lite_installed() ) {
				if ( is_plugin_active( 'order-delivery-date-for-woocommerce/order_delivery_date.php' ) ) {
		            deactivate_plugins( 'order-delivery-date-for-woocommerce/order_delivery_date.php' );
		            if ( isset( $_GET[ 'activate' ] ) ) {
		                unset( $_GET[ 'activate' ] );
		            }
		        }
		    }
		}

		/**
		 * Check if Order Delivery Date lite is active.
		 * 
		 * @return bool True if Lite version is active, else false.
		 * @since 8.7
		 */
		public static function orddd_check_lite_installed() {
		    if ( class_exists( 'order_delivery_date_lite' ) ) {
		        return true;
		    } else {
		        return false;
		    }
		}
	}
}
$order_delivery_date = new order_delivery_date();
