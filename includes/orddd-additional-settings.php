<?php
/**
 * Order Delivery Date Additional Settings
 *
 * @author Tyche Softwares
 * @package Order-Delivery-Date-Pro-for-WooCommerce/Admin/Settings/General
 * @since 2.8.3
 * @category Classes
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class orddd_additional_settings {
    
    /**
     * Callback for adding Additional Settings tab settings
     */
    public static function orddd_additional_settings_section_callback() { }
     
    /**
     * Callback for adding Delivery date column on WooCommerce->Orders page setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_show_column_on_orders_page_check_callback( $args ) {
        $orddd_show_column_on_orders_page_check = $orddd_enable_default_sorting_of_column = '';
        if ( get_option( 'orddd_show_column_on_orders_page_check' ) == 'on' ) { 
            $orddd_show_column_on_orders_page_check = "checked";
        }
        
        if ( get_option( 'orddd_enable_default_sorting_of_column' ) == 'on' ) {
            $orddd_enable_default_sorting_of_column = "checked";
        }
        
        echo '<input type="checkbox" name="orddd_show_column_on_orders_page_check" id="orddd_show_column_on_orders_page_check" class="day-checkbox" ' . $orddd_show_column_on_orders_page_check . '/>';
        
        $html = '<label for="orddd_show_column_on_orders_page_check"> ' . $args[ 0 ] . '</label></br>';
        echo $html;
        
        if ( get_option( 'orddd_show_column_on_orders_page_check' ) == 'on' ) {
            echo '<input type="checkbox" name="orddd_enable_default_sorting_of_column" id="orddd_enable_default_sorting_of_column" class="day-checkbox" ' . $orddd_enable_default_sorting_of_column . '/>';
            
            $html_arr = '<label for="orddd_enable_default_sorting_of_column">' . __( 'Enable default sorting of orders (in descending order) by Delivery Date on WooCommerce -> Orders page', 'order-delivery-date' ) . '</label>';
            echo $html_arr;
        } else {
            echo '<input type="checkbox" name="orddd_enable_default_sorting_of_column" id="orddd_enable_default_sorting_of_column" class="day-checkbox" ' . $orddd_enable_default_sorting_of_column . '/>';
            
            $html_arr = '<label for="orddd_enable_default_sorting_of_column" id="orddd_enable_default_sorting_of_column">' . __( 'Enable default sorting of orders (in descending order) by Delivery Date on WooCommerce -> Orders page', 'order-delivery-date' ) . '</label>';
            echo $html_arr;
        }
        ?>
        <script type='text/javascript'>
            jQuery( document ).ready( function(){
            	if ( jQuery( "#orddd_show_column_on_orders_page_check" ).is(':checked') ) {
            		jQuery( '#orddd_enable_default_sorting_of_column' ).fadeIn();
    				jQuery( 'label[ for=\"orddd_enable_default_sorting_of_column\" ]' ).fadeIn();
    			} else {
    				jQuery( '#orddd_enable_default_sorting_of_column' ).fadeOut();
    				jQuery( 'label[ for=\"orddd_enable_default_sorting_of_column\" ]' ).fadeOut();
    			}
                jQuery( "#orddd_show_column_on_orders_page_check" ).on( 'change', function() {
        			if ( jQuery( this ).is(':checked') ) {
        				jQuery( '#orddd_enable_default_sorting_of_column' ).fadeIn();
        				jQuery( 'label[ for=\"orddd_enable_default_sorting_of_column\" ]' ).fadeIn();
        			} else {
        				jQuery( '#orddd_enable_default_sorting_of_column' ).fadeOut();
        				jQuery( 'label[ for=\"orddd_enable_default_sorting_of_column\" ]' ).fadeOut();
        			}
        		})
            });
        </script>
        <?php
    }
    
    /**
     * Callback for adding Filter on WooCommerce->Orders page setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_show_filter_on_orders_page_check_callback( $args ) {
        $orddd_show_filter_on_orders_page_check = '';
        if ( get_option( 'orddd_show_filter_on_orders_page_check' ) == 'on' ) { 
        	$orddd_show_filter_on_orders_page_check = "checked";
        }
        
        echo '<input type="checkbox" name="orddd_show_filter_on_orders_page_check" id="orddd_show_filter_on_orders_page_check" class="day-checkbox" ' . $orddd_show_filter_on_orders_page_check . ' />';
        
        $html = '<label for="orddd_show_filter_on_orders_page_check"> ' . $args[ 0 ] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for hiding Delivery Date fields on the checkout page for Virtual product setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_appearance_virtual_product_callback( $args ) {
        if ( get_option( 'orddd_no_fields_for_virtual_product' ) == 'on' ) {
            $orddd_no_fields_for_virtual_product = "checked";
        } else {
            $orddd_no_fields_for_virtual_product = "";
        }
    
        echo '<input type="checkbox" name="orddd_no_fields_for_virtual_product" id="orddd_no_fields_for_virtual_product" class="day-checkbox"' . $orddd_no_fields_for_virtual_product . '/><label class="orddd_no_fields_for_product_type">' . __( 'Virtual Products', 'order-delivery-date' ) . '</label>';
    
        if ( get_option( 'orddd_no_fields_for_featured_product' ) == 'on' ) {
            $orddd_no_fields_for_featured_product = "checked";
        } else {
            $orddd_no_fields_for_featured_product = "";
        }
    
        echo '<input type="checkbox" name="orddd_no_fields_for_featured_product" id="orddd_no_fields_for_featured_product" class="day-checkbox"' . $orddd_no_fields_for_featured_product . '/><label class="orddd_no_fields_for_product_type">' . __( 'Featured products', 'order-delivery-date' ) . '</label>';
    
        $html = '<label for="orddd_no_fields_for_product_type"> ' . $args[ 0 ] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for adding Integration with Other Plugins settings
     */
    public static function orddd_integration_with_other_plugins_callback() { }
    
    /**
     * Callback for adding Delivery date and/or Time slot in csv export setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_show_fields_in_csv_export_check_callback( $args ) {
        $orddd_show_fields_in_csv_export_check = '';
        if ( get_option( 'orddd_show_fields_in_csv_export_check' ) == 'on' ) { 
        	$orddd_show_fields_in_csv_export_check = "checked";
        }
        
        echo '<input type="checkbox" name="orddd_show_fields_in_csv_export_check" id="orddd_show_fields_in_csv_export_check" class="day-checkbox" ' . $orddd_show_fields_in_csv_export_check . ' />';
        
        $html = '<label for="orddd_show_fields_in_csv_export_check"> ' . $args[ 0 ] . '</label>';
        echo $html;
    }

    /**
     * Callback for adding Delivery date and/or Time slot in PDF invoices and Packing slips setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    
    public static function orddd_show_fields_in_pdf_invoice_and_packing_slips_callback( $args ) {
        $orddd_show_fields_in_pdf_invoice_and_packing_slips = '';
        if ( get_option( 'orddd_show_fields_in_pdf_invoice_and_packing_slips' ) == 'on' ) { 
            $orddd_show_fields_in_pdf_invoice_and_packing_slips = "checked";
        }
        
        echo '<input type="checkbox" name="orddd_show_fields_in_pdf_invoice_and_packing_slips" id="orddd_show_fields_in_pdf_invoice_and_packing_slips" class="day-checkbox" ' . $orddd_show_fields_in_pdf_invoice_and_packing_slips . '/>';
        
        $html = '<label for="orddd_show_fields_in_pdf_invoice_and_packing_slips"> ' . $args[ 0 ] . '</label>';
        echo $html;
   }

   /**
    * Callback for adding Delivery date and/or Time slot in Print Invoice and Packing slips setting
    *
    * @param array $args Extra arguments containing label & class for the field
    * @since 2.8.3
    */
    public static function orddd_show_fields_in_invoice_and_delivery_note_callback( $args ) {
        $orddd_show_fields_in_invoice_and_delivery_note = '';
        if ( get_option( 'orddd_show_fields_in_invoice_and_delivery_note' ) == 'on' ) { 
        	$orddd_show_fields_in_invoice_and_delivery_note = "checked";
        }
         
        echo '<input type="checkbox" name="orddd_show_fields_in_invoice_and_delivery_note" id="orddd_show_fields_in_invoice_and_delivery_note" class="day-checkbox" ' . $orddd_show_fields_in_invoice_and_delivery_note . '/>';
        
        $html = '<label for="orddd_show_fields_in_invoice_and_delivery_note"> ' . $args[ 0 ] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for adding Delivery date and/or Time slot in Cloud print setting
     *
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    public static function orddd_show_fields_in_cloud_print_orders_callback( $args ) {
        $orddd_show_fields_in_cloud_print_orders_check = '';
        if ( get_option( 'orddd_show_fields_in_cloud_print_orders' ) == 'on' ) { 
            $orddd_show_fields_in_cloud_print_orders_check = "checked";
        }
        
        echo '<input type="checkbox" name="orddd_show_fields_in_cloud_print_orders" id="orddd_show_fields_in_cloud_print_orders" class="day-checkbox" ' . $orddd_show_fields_in_cloud_print_orders_check . '/>';
        
        $html = '<label for="orddd_show_fields_in_cloud_print_orders"> '. $args[ 0 ] . '</label>';
        echo $html;
    } 

    /**
     * Callback for enabling tax calculation on the checkout page for Delivery Charges
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    public static function orddd_enable_tax_calculation_for_delivery_charges_callback( $args ) {
        $orddd_enable_tax_calculation_for_delivery_charges = '';
        if ( get_option( 'orddd_enable_tax_calculation_for_delivery_charges' ) == 'on' ) {
            $orddd_enable_tax_calculation_for_delivery_charges = "checked";
        }
        
        echo '<input type="checkbox" name="orddd_enable_tax_calculation_for_delivery_charges" id="orddd_enable_tax_calculation_for_delivery_charges" class="day-checkbox" ' . $orddd_enable_tax_calculation_for_delivery_charges . '/>';
        
        $html = '<label for="orddd_enable_tax_calculation_for_delivery_charges"> '. $args[ 0 ] . '</label>';
        echo $html;
    }
    
    /**
     * Callback for adding Compatibility with other plugin section
     */
    public static function orddd_compatibility_with_other_plugins_callback() {}
    
    /**
     * Enable Compatibility with WooCommerce Shipping Multiple Addresses plugin
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    public static function orddd_shipping_multiple_address_compatibility_callback( $args ) {
        $orddd_shipping_multiple_address_compatibility = '';
        if ( get_option( 'orddd_shipping_multiple_address_compatibility' ) == 'on' ) {
            $orddd_shipping_multiple_address_compatibility = "checked";
        }
        
        echo '<input type="checkbox" name="orddd_shipping_multiple_address_compatibility" id="orddd_shipping_multiple_address_compatibility" class="day-checkbox" ' . $orddd_shipping_multiple_address_compatibility . '/>';
        
        $html = '<label for="orddd_shipping_multiple_address_compatibility"> '. $args[ 0 ] . '</label>';
        echo $html;
    }

    /**
     * Enable Compatibility with WooCommerce Amazon Payments Advanced Gateway
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    public static function orddd_amazon_payments_advanced_gateway_compatibility_callback( $args ) {
        $orddd_amazon_payments_advanced_gateway_compatibility = '';
        if ( get_option( 'orddd_amazon_payments_advanced_gateway_compatibility' ) == 'on' ) {
            $orddd_amazon_payments_advanced_gateway_compatibility = "checked";
        }
    
        echo '<input type="checkbox" name="orddd_amazon_payments_advanced_gateway_compatibility" id="orddd_amazon_payments_advanced_gateway_compatibility" class="day-checkbox" ' . $orddd_amazon_payments_advanced_gateway_compatibility . '/>';
    
        $html = '<label for="orddd_amazon_payments_advanced_gateway_compatibility"> '. $args[ 0 ] . '</label>';
        echo $html;
    }
    
    /**
     * Autofill date & time on the checkout page 
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    public static function orddd_enable_autofill_of_delivery_date_callback( $args ) {
        $orddd_enable_autofill_of_delivery_date = '';
        if ( get_option( 'orddd_enable_autofill_of_delivery_date' ) == 'on' ) {
            $orddd_enable_autofill_of_delivery_date = "checked";
        }
        
        echo '<input type="checkbox" name="orddd_enable_autofill_of_delivery_date" id="orddd_enable_autofill_of_delivery_date" class="day-checkbox" ' . $orddd_enable_autofill_of_delivery_date . '/>';
        
        $html = '<label for="orddd_enable_autofill_of_delivery_date"> '. $args[ 0 ] . '</label>';
        echo $html;
    }
    
    /**
     * Enable customers to edit or modify the deliveru date
     * 
     * @param array $args Extra arguments containing label & class for the field
     * @since 2.8.3
     */
    public static function orddd_allow_customers_to_edit_date_callback( $args ) {
        $orddd_allow_customers_to_edit_date = $orddd_send_email_to_admin_when_date_updated = '';
        if ( get_option( 'orddd_allow_customers_to_edit_date' ) == 'on' ) {
            $orddd_allow_customers_to_edit_date = "checked";
        }
        
        if ( get_option( 'orddd_send_email_to_admin_when_date_updated' ) == 'on' ) {
            $orddd_send_email_to_admin_when_date_updated = "checked";
        }
        
        echo '<input type="checkbox" name="orddd_allow_customers_to_edit_date" id="orddd_allow_customers_to_edit_date" class="day-checkbox" ' . $orddd_allow_customers_to_edit_date . '/>';
        
        $html = '<label for="orddd_allow_customers_to_edit_date"> '. $args[ 0 ] . '</label>';
        echo $html;
        
        if ( get_option( 'orddd_allow_customers_to_edit_date' ) == 'on' ) {
            echo '<input type="checkbox" name="orddd_send_email_to_admin_when_date_updated" id="orddd_send_email_to_admin_when_date_updated" class="day-checkbox" ' . $orddd_send_email_to_admin_when_date_updated . '/>';
        
            $html_arr = '<label for="orddd_send_email_to_admin_when_date_updated">' . __( 'Send a notification to the Admin when the Delivery Date & Time is updated by the customers.', 'order-delivery-date' ) . '</label>';
            echo $html_arr;
        } else {
            echo '<input type="checkbox" name="orddd_send_email_to_admin_when_date_updated" id="orddd_send_email_to_admin_when_date_updated" class="day-checkbox" ' . $orddd_send_email_to_admin_when_date_updated . '/>';
        
            $html_arr = '<label for="orddd_send_email_to_admin_when_date_updated" id="orddd_send_email_to_admin_when_date_updated">' . __( 'When enabled, email notification will be sent to the admin when the Delivery Date & Time is edited by the customers on the My Account -> Orders -> View page. So customers will be able to edit the date and time once the order is placed.', 'order-delivery-date' ) . '</label>';
            echo $html_arr;
        }
        ?>
        <script type='text/javascript'>
            jQuery( document ).ready( function(){
            	if ( jQuery( "#orddd_allow_customers_to_edit_date" ).is(':checked') ) {
            		jQuery( '#orddd_send_email_to_admin_when_date_updated' ).fadeIn();
    				jQuery( 'label[ for=\"orddd_send_email_to_admin_when_date_updated\" ]' ).fadeIn();
    			} else {
    				jQuery( '#orddd_send_email_to_admin_when_date_updated' ).fadeOut();
    				jQuery( 'label[ for=\"orddd_send_email_to_admin_when_date_updated\" ]' ).fadeOut();
    			}
                jQuery( "#orddd_allow_customers_to_edit_date" ).on( 'change', function() {
        			if ( jQuery( this ).is(':checked') ) {
        				jQuery( '#orddd_send_email_to_admin_when_date_updated' ).fadeIn();
        				jQuery( 'label[ for=\"orddd_send_email_to_admin_when_date_updated\" ]' ).fadeIn();
        			} else {
        				jQuery( '#orddd_send_email_to_admin_when_date_updated' ).fadeOut();
        				jQuery( 'label[ for=\"orddd_send_email_to_admin_when_date_updated\" ]' ).fadeOut();
        			}
        		})
            });
        </script>
        <?php
    }
}