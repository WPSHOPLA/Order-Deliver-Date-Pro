<?php
/**
 * Order Delivery Date Uninstall
 *
 * Deletes all the settings for the plugin from the database when plugin is uninstalled.
 *
 * @author      Tyche Softwares
 * @category    Core
 * @package     Order-Delivery-Date-Pro-for-WooCommerce/Uninstall
 * @version     7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb, $wp_version, $woocommerce;
delete_option( 'orddd_db_version' );

$orddd_weekdays = array( 
    'orddd_weekday_0' => __( 'Sunday', 'order-delivery-date' ),
    'orddd_weekday_1' => __( 'Monday', 'order-delivery-date' ),
    'orddd_weekday_2' => __( 'Tuesday', 'order-delivery-date' ),
    'orddd_weekday_3' => __( 'Wednesday', 'order-delivery-date' ),
    'orddd_weekday_4' => __( 'Thursday', 'order-delivery-date' ),
    'orddd_weekday_5' => __( 'Friday', 'order-delivery-date' ),
    'orddd_weekday_6' => __( 'Saturday', 'order-delivery-date' ) );

$orddd_shipping_days = array(
    'orddd_shipping_day_0' => __( 'Sunday', 'order-delivery-date' ),
    'orddd_shipping_day_1' => __( 'Monday', 'order-delivery-date' ),
    'orddd_shipping_day_2' => __( 'Tuesday', 'order-delivery-date' ),
    'orddd_shipping_day_3' => __( 'Wednesday', 'order-delivery-date' ),
    'orddd_shipping_day_4' => __( 'Thursday', 'order-delivery-date' ),
    'orddd_shipping_day_5' => __( 'Friday', 'order-delivery-date' ),
    'orddd_shipping_day_6' => __( 'Saturday', 'order-delivery-date' ) );

// date options
foreach ( $orddd_weekdays as $n => $day_name ) {
    delete_option( $n );
}
foreach ( $orddd_weekdays as $n => $day_name ) {
    delete_option( 'additional_charges_' . $n );
}
foreach ( $orddd_weekdays as $n => $day_name ) {
    delete_option( 'delivery_charges_label_' . $n );
}

delete_option( 'orddd_enable_delivery_date' );
delete_option( 'orddd_minimumOrderDays' );
delete_option( 'orddd_number_of_dates' );
delete_option( 'orddd_date_field_mandatory' );
delete_option( 'orddd_lockout_date_after_orders' );
delete_option( 'orddd_lockout_date_quantity_based' );
delete_option( 'orddd_lockout_days' );
delete_option( 'orddd_show_fields_in_csv_export_check' );
delete_option( 'orddd_show_fields_in_pdf_invoice_and_packing_slips' );
delete_option( 'orddd_show_fields_in_invoice_and_delivery_note' );
delete_option( 'orddd_show_fields_in_cloud_print_orders' );
delete_option( 'orddd_show_filter_on_orders_page_check' );
delete_option( 'orddd_show_column_on_orders_page_check' );
delete_option( 'orddd_enable_default_sorting_of_column' );
delete_option( 'orddd_enable_tax_calculation_for_delivery_charges' );
delete_option( 'orddd_amazon_payments_advanced_gateway_compatibility' );

// Shipping days
foreach ( $orddd_shipping_days as $n => $day_name ) {
    delete_option( $n );
}
delete_option( 'orddd_enable_shipping_days' );

// time options
delete_option( 'orddd_enable_delivery_time' );
delete_option( 'orddd_delivery_from_hours' );
delete_option( 'orddd_delivery_to_hours' );
delete_option( 'orddd_delivery_time_format' );

// same day delivery options
delete_option( 'orddd_enable_same_day_delivery' );
delete_option( 'orddd_disable_same_day_delivery_after_hours' );
delete_option( 'orddd_disable_same_day_delivery_after_minutes' );
delete_option( 'orddd_same_day_additional_charges' );

// next day delivery options
delete_option( 'orddd_enable_next_day_delivery' );
delete_option( 'orddd_disable_next_day_delivery_after_hours' );
delete_option( 'orddd_disable_next_day_delivery_after_minutes' );
delete_option( 'orddd_next_day_additional_charges' );

// appearance options
delete_option( 'orddd_delivery_date_field_label' );
delete_option( 'orddd_delivery_date_field_placeholder' );
delete_option( 'orddd_delivery_date_field_note' );
delete_option( 'orddd_delivery_date_format' );
delete_option( 'orddd_number_of_months' );
delete_option( 'orddd_calendar_theme' );
delete_option( 'orddd_calendar_theme_name' );
delete_option( 'orddd_language_selected' );
delete_option( 'orddd_delivery_date_fields_on_checkout_page' );
delete_option( 'orddd_no_fields_for_virtual_product' );
delete_option( 'orddd_custom_hook_for_fields_placement' );

// holiday options
delete_option( 'orddd_delivery_date_holidays' );

// specific delivery dates
delete_option( 'orddd_enable_specific_delivery_dates' );
delete_option( 'orddd_delivery_dates' );
delete_option( 'additional_charges_1' );
delete_option( 'additional_charges_2' );
delete_option( 'additional_charges_3' );
delete_option( 'specific_charges_label_1' );
delete_option( 'specific_charges_label_2' );
delete_option( 'specific_charges_label_3' );

// time slot
delete_option( 'orddd_delivery_time_slot_log' );
delete_option( 'orddd_lockout_time_slot' );
delete_option( 'orddd_enable_time_slot' );
delete_option( 'orddd_time_slot_mandatory', '' );
delete_option( 'orddd_delivery_timeslot_field_label', '' );
delete_option( 'orddd_specific_array_format', '' );
delete_option( 'orddd_delivery_timeslot_format' );
delete_option( 'orddd_show_first_available_time_slot_as_selected' );
delete_option( 'orddd_global_lockout_time_slots' );
delete_option( 'orddd_auto_populate_first_available_time_slot' );

// additional settings
delete_option( 'orddd_enable_autofill_of_delivery_date' );

delete_option( 'orddd_database_updated_27' );
delete_option( 'orddd_abp_hrs' );
delete_option( 'update_weekdays_value' );

// Settings by Shipping methods
delete_option( 'orddd_enable_shipping_based_delivery' );
delete_option( 'orddd_shipping_based_settings_option_key' );

$shipping_based_settings_query = "SELECT option_value, option_name FROM `" . $wpdb->prefix . "options` WHERE option_name LIKE 'orddd_shipping_based_settings_%' AND option_name != 'orddd_shipping_based_settings_option_key' ORDER BY option_id";
$results = $wpdb->get_results( $shipping_based_settings_query );
foreach ( $results as $key => $value ) {
    delete_option( $value->option_name );
}

// Google Calendar Sync settings
delete_option( 'orddd_calendar_event_location' );
delete_option( 'orddd_add_to_calendar_order_received_page' );
delete_option( 'orddd_add_to_calendar_customer_email' );
delete_option( 'orddd_add_to_calendar_my_account_page' );
delete_option( 'orddd_calendar_in_same_window' );
delete_option( 'orddd_calendar_sync_integration_mode' );
delete_option( 'orddd_calendar_event_summary' );
delete_option( 'orddd_calendar_event_description' );
delete_option( 'orddd_admin_add_to_calendar_email_notification' );
delete_option( 'orddd_admin_add_to_calendar_delivery_calendar' );
delete_option( 'orddd_calendar_details_1' );
delete_option( 'orddd_ics_feed_urls' );

//Extra Options
delete_option( 'update_time_slot_log_for_tv' );
delete_option( 'orddd_abp_hrs' );
delete_option( 'update_weekdays_value' );
delete_option( 'update_delivery_product_category' );
delete_option( 'update_placeholder_value' );
delete_option( 'orddd_update_shipping_delivery_settings_based' );
delete_option( 'orddd_update_time_slot_for_shipping_delivery' );
delete_option( 'orddd_default_sorting' );
delete_option( 'orddd_tax_calculation_enabled' );
delete_option( 'orddd_delivery_date_on_checkout_page_enabled' );
delete_option( 'orddd_update_additional_charges_records' );
delete_option( 'orddd_update_time_format' );
delete_option( 'orddd_update_auto_populate_first_available_time_slot' );
delete_option( 'orddd_update_shipping_method_id' );
delete_option( 'orddd_update_shipping_method_id_delete' );

delete_option( 'orddd_delivery_checkout_options' );
delete_option( 'orddd_advance_settings' );
delete_option( 'orddd_update_advance_settings' );
delete_option( 'orddd_update_delivery_checkout_options' );


delete_option( 'orddd_enable_day_wise_settings' );
delete_option( 'orddd_min_between_days' );
delete_option( 'orddd_max_between_days' );
delete_option( 'orddd_time_slot_for_delivery_days' );
delete_option( 'orddd_disable_time_slot_log' );
delete_option( 'orddd_delivery_date_on_cart_page' );
delete_option( 'orddd_no_fields_for_featured_product' );
delete_option( 'orddd_allow_customers_to_edit_date' );
delete_option( 'orddd_send_email_to_admin_when_date_updated' );
delete_option( 'orddd_shipping_multiple_address_compatibility' );

do_action( 'orddd_plugin_deactivate' );