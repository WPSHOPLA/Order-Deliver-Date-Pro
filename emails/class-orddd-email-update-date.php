<?php 
/**
 * Order Delivery Date Pro for WooCommerce
 *
 *
 * Delivery Details Edited Email. An email sent to the admin or customer when the delivery details are edited.
 *
 * @author      Tyche Softwares
 * @package     Order-Delivery-Date-Pro-for-WooCommerce/Emails/Class-ORDDD-Email-Update-Date
 * @since       5.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * ORDDD_Email_Update_Date Class
 *
 * @class ORDDD_Email_Update_Date
 * @extends     WC_Email
 */
class ORDDD_Email_Update_Date extends WC_Email {

    /**
     * Constructor.
     * 
     * Defines class variables and hooks as needed.
     * @since 5.7
     */
    function __construct() {
        
        $this->id                   = 'orddd_update_date';
        $this->title                = __( 'Delivery Date & Time Updated', 'order-delivery-date' );
        $this->description          = __( 'Delivery Date & Time is is being updated for the order.', 'order-delivery-date' );
        
        $this->heading              = __( 'Delivery Date & Time Updated', 'order-delivery-date' );
        $this->subject              = __( '[{blogname}] Delivery Date & Time is Updated for (Order {order_number}) - {order_date}', 'woocommerce-booking' );
        
        $this->template_html    = 'emails/admin-update-date.php';
        $this->template_plain   = 'emails/plain/admin-update-date.php';
        
        // Triggers for this email
        add_action( 'orddd_admin_update_date_notification', array( $this, 'trigger' ), 10, 2 );
        
        // Call parent constructor
        parent::__construct();
        
        // Other settings
        $this->template_base = ORDDD_TEMPLATE_PATH;
        $this->recipient     = $this->get_option( 'recipient', get_option( 'admin_email' ) );
        
    }
    
    /**
     * Sends an email to the admin or customer when the customer or admin 
     * edits the delivery details for an order respectively.
     * 
     * @param integer $order_id - Order ID for which details are being edited.
     * @param string $updated_by - States by whom are the details being updated. Valid Values: admin|customer
     * 
     * @hook orddd_admin_update_date_notification
     * @since 5.7
     */
    function trigger( $order_id, $updated_by ) {
        if ( $order_id ) {
            $this->order_id = $order_id;
            $order = new WC_Order( $order_id );
            $order_date = $order->get_date_created();
            $this->find[]    = '{order_date}';
            $this->replace[] = date_i18n( wc_date_format(), strtotime( $order_date ) );

            $this->find[]    = '{order_number}';
            $this->replace[] = $order_id;
            if ( ! $this->get_recipient() ) {
                return;
            }

            if( $updated_by == 'admin' ) {
                $recipient = $order->get_billing_email();
            } else {
                $recipient = $this->get_recipient();
            }

            $this->updated_by = $updated_by;
            $this->send( $recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }
    }
    
    /**
     * This function gets the HTML content for the email sent to the admin or customer 
     * when the customer or admin edits the delivery details for an order respectively.
	 * 
	 * @since 5.7
	 */
    function get_content_html() {
        ob_start();
        wc_get_template( $this->template_html, array(
            'order_id'       => $this->order_id,
            'sent_to_admin' => true,
            'plain_text'    => false,
            'email'			=> $this,
            'email_heading' => $this->get_heading(),
            'updated_by'    => $this->updated_by,
        ), 'order-delivery-date/', $this->template_base );
        return ob_get_clean();
    }
    
    /**
     * This function gets the Plain content for the email sent to the admin or customer
     * when the customer or admin edits the delivery details for an order respectively.
     *
     * @since 5.7
     */
    function get_content_plain() {
        ob_start();
        wc_get_template( $this->template_plain, array(
            'order_id'       => $this->order_id,
            'sent_to_admin' => true,
            'plain_text'    => false,
            'email'			=> $this,
            'email_heading' => $this->get_heading(),
            'updated_by'    => $this->updated_by, 
        ),  'order-delivery-date/', $this->template_base );
        return ob_get_clean();
    }
    
    /**
     * This function gets the subject for the email sent to the admin or customer
     * when the customer or admin edits the delivery details for an order respectively.
     *
     * @since 5.7
     */
    function get_subject() {
        $order = new WC_order( $this->order_id );
        return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $this->subject ), $this->object );
    }
    
    /**
     * This function gets the heading for the email sent to the admin or customer
     * when the customer or admin edits the delivery details for an order respectively.
     *
     * @since 5.7
     */
    public function get_heading() {
        $order = new WC_order( $this->order_id );
        return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->heading ), $this->object );
    }
    
    /**
     * This function adds the form fields for the Email to be visible in
     * WooCommerce->Settings->Emails->Delivery Date & Time Updated
     *
     * @since 5.7
     */
    function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' 		=> __( 'Enable/Disable', 'woocommerce-booking' ),
                'type' 			=> 'checkbox',
                'label' 		=> __( 'Enable this email notification', 'woocommerce-booking' ),
                'default' 		=> 'yes'
            ),
            'recipient' => array(
                'title'         => __( 'Recipient(s)', 'woocommerce' ),
                'type'          => 'text',
                'description'   => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'woocommerce' ), esc_attr( get_option('admin_email') ) ),
                'placeholder'   => '',
                'default'       => ''
            ),
            'subject' => array(
                'title' 		=> __( 'Subject', 'woocommerce-booking' ),
                'type' 			=> 'text',
                'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce-booking' ), $this->subject ),
                'placeholder' 	=> '',
                'default' 		=> ''
            ),
            'heading' => array(
                'title' 		=> __( 'Email Heading', 'woocommerce-booking' ),
                'type' 			=> 'text',
                'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce-booking' ), $this->heading ),
                'placeholder' 	=> '',
                'default' 		=> ''
            ),
            'email_type' => array(
                'title' 		=> __( 'Email type', 'woocommerce-booking' ),
                'type' 			=> 'select',
                'description' 	=> __( 'Choose which format of email to send.', 'woocommerce-booking' ),
                'default' 		=> 'html',
                'class'			=> 'email_type',
                'options'		=> array(
                    'plain'		 	=> __( 'Plain text', 'woocommerce-booking' ),
                    'html' 			=> __( 'HTML', 'woocommerce-booking' ),
                    'multipart' 	=> __( 'Multipart', 'woocommerce-booking' ),
                )
            )
        );
    }
    
}
return new ORDDD_Email_Update_Date();
?>