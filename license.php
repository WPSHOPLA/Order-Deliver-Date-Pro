<?php
/**
 * Order Delivery Date Pro for WooCommerce
 *
 * Introduces and Miantains Licenses for the plugin.
 *
 * @author      Tyche Softwares
 * @package     Order-Delivery-Date-Pro-for-WooCommerce/License
 * @since       2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * orddd_license Class
 *
 * @class orddd_license
 */
class orddd_license {
	
	/**
	 * Default Constructor
	 *
	 * @since 8.1
	 */
	
	public function __construct() {
		//License
	    add_action( 'admin_init', array( &$this, 'orddd_edd_sample_register_option' ) );
	    add_action( 'admin_init', array( &$this, 'orddd_edd_sample_deactivate_license' ) );
	    add_action( 'admin_init', array( &$this, 'orddd_edd_sample_activate_license' ) );
	}

	/** 
     * Activate plugin license if License key is valid  
     * 
     * @hook admin_init
     * @since 2.5
     */
	public function orddd_edd_sample_activate_license() {		
		// listen for our activate button to be clicked
		if ( isset( $_POST[ 'orddd_license_activate' ] ) ) {
			// run a quick security check
			if ( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
				return; // get out if we didn't click the Activate button
			// retrieve the license from the database
			$license = trim( get_option( 'edd_sample_license_key_odd_woo' ) );
			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license' 	 => $license,
				'item_name'  => urlencode( EDD_SL_ITEM_NAME_ODD_WOO ) // the name of our product in EDD
			);

			// Call the custom API.
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, EDD_SL_STORE_URL_ODD_WOO ) ), array( 'timeout' => 15, 'sslverify' => false ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "active" or "inactive"
			update_option( 'edd_sample_license_status_odd_woo', $license_data->license );
		}
	}

	/** 
    * Deactivate the License 
    * 
    * @hook admin_init
    * @since 2.5
    */
	public function orddd_edd_sample_deactivate_license() {
		// listen for our activate button to be clicked
		if ( isset( $_POST[ 'orddd_license_deactivate' ] ) ) {
			// run a quick security check
			if ( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
				return; // get out if we didn't click the Activate button
	
			// retrieve the license from the database
			$license = trim( get_option( 'edd_sample_license_key_odd_woo' ) );
			
			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license' 	 => $license,
				'item_name'  => urlencode( EDD_SL_ITEM_NAME_ODD_WOO ) // the name of our product in EDD
			);
	
			// Call the custom API.
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, EDD_SL_STORE_URL_ODD_WOO ) ), array( 'timeout' => 15, 'sslverify' => false ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if ( $license_data->license == 'deactivated' )
				delete_option( 'edd_sample_license_status_odd_woo' );
		}
	}
	
	/**
	* Checks if License key is valid or not
	* 
	* @since 2.5
	*/
	public static function orddd_edd_sample_check_license() {
		global $wp_version;
		$license = trim( get_option( 'edd_sample_license_key_odd_woo' ) );

		$api_params = array(
			'edd_action' => 'check_license',
			'license'	 => $license,
			'item_name'	 => urlencode( EDD_SL_ITEM_NAME_ODD_WOO )
		);
		// Call the custom API.
		$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, EDD_SL_STORE_URL_ODD_WOO ) ), array( 'timeout' => 15, 'sslverify' => false ) );

		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->license == 'valid' ) {
			echo 'valid'; exit;
			// this license is still valid
		} else {
			echo 'invalid'; exit;
			// this license is no longer valid
		}
	}

	/**
    * Stores the license key in database of the site 
    * once the plugin is installed and the license key saved.
    * 
    * @hook admin_init
    * @since 2.5
    */
	public function orddd_edd_sample_register_option() {
		// creates our settings in the options table
		register_setting( 'edd_sample_license', 'edd_sample_license_key_odd_woo',  array( &$this, 'orddd_get_edd_sanitize_license' ) );
	}

    /**
    * Checks if a new license has been entered, if yes plugin must be reactivated.
    * 
    * @param string $new - New License Key
    * @since 2.5
    */	
	public function orddd_get_edd_sanitize_license( $new ) {
		$old = get_option( 'edd_sample_license_key_odd_woo' );
		if( $old && $old != $new ) {
			delete_option( 'edd_sample_license_status_odd_woo' ); // new license has been entered, so must reactivate
		}
		return $new;
	}

	/**
    * Add the license page in the Order delivery date menu.
    * 
    * @since 2.5
    */
	public static function orddd_edd_sample_license_page() {
		$license 	= get_option( 'edd_sample_license_key_odd_woo' );
		$status 	= get_option( 'edd_sample_license_status_odd_woo' );
	
		?>
		<div class="wrap">
			<h2><?php _e( 'Plugin License Options', 'order-delivery-date' ); ?></h2>
				<form method="post" action="options.php">
					<?php settings_fields( 'edd_sample_license' ); ?>
						<table class="form-table">
							<tbody>
								<tr valign="top">	
									<th scope="row" valign="top">
										<?php _e( 'License Key', 'order-delivery-date' ); ?>
									</th>
									<td>
										<input id="edd_sample_license_key_odd_woo" name="edd_sample_license_key_odd_woo" type="text" class="regular-text"	value="<?php esc_attr_e( $license ); ?>" />
											<label class="description" for="edd_sample_license_key"><?php _e( 'Enter your license key', 'order-delivery-date' ); ?></label>
									</td>
								</tr>
								<?php if ( false !== $license ) { ?>
								<tr valign="top">	
									<th scope="row" valign="top">
										<?php _e( 'Activate License', 'order-delivery-date' ); ?>
									</th>
									<td>
									<?php if ( $status !== false && $status == 'valid' ) { ?>
										<span style="color:green;"><?php _e( 'active', 'order-delivery-date' ); ?></span>
										<?php wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
										<input type="submit" class="button-secondary" name="orddd_license_deactivate" value="<?php _e( 'Deactivate License', 'order-delivery-date' ); ?>"/>
									<?php } else {
											wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
											<input type="submit" class="button-secondary" name="orddd_license_activate" value="<?php _e( 'Activate License', 'order-delivery-date' ); ?>"/>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>	
					<?php submit_button(); ?>
				</form>
		<?php
	}
}
$orddd_license = new orddd_license();