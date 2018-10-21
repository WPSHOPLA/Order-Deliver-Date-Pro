<?php 
/**
 * Order Delivery Time Settings
 *
 * @author Tyche Softwares
 * @package Order-Delivery-Date-Pro-for-WooCommerce/Admin/Settings/General
 * @since 2.4
 * @category Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class orddd_time_settings {
    
    /**
     * Callback for adding Time settings tab settings
     */
	public static function orddd_delivery_time_settings_callback() { }
	
	/**
	 * Callback for adding Enable Time capture setting
	 *
	 * @param array $args Extra arguments containing label & class for the field
	 * @since 2.4
	 */
	public static function orddd_enable_delivery_time_capture_callback( $args ) {
		$enable_delivery_time = "";
		if ( get_option( 'orddd_enable_delivery_time' ) == 'on' ) {
			$enable_delivery_time = "checked";
		}
		
		echo '<input type="checkbox" name="orddd_enable_delivery_time" id="orddd_enable_delivery_time" class="day-checkbox" ' . $enable_delivery_time . '/>';
		
		$html = '<label for="orddd_enable_delivery_time"> ' . $args[0] . '</label>';
		echo $html;
    }
	
    /**
     * Callback for adding Time range setting
     *
     * @param array $args Extra arguments containing label & class for the field
	 * @since 2.4
     */
    
	public static function orddd_time_range_callback( $args ) {
        echo '<select name="orddd_delivery_from_hours" id="orddd_delivery_from_hours" size="1">';
	    // time options
	    $delivery_from_hours = get_option( 'orddd_delivery_from_hours' );
	    $delivery_to_hours = get_option( 'orddd_delivery_to_hours' );
	    
	    for ( $i = 1 ; $i <= 23 ; $i++ ) {
            printf( "<option %s value='%s'>%s</option>\n",
                selected( $i, get_option( 'orddd_delivery_from_hours' ), false ),
                esc_attr( $i ),
                $i
            );
	    }
	    echo '</select>&nbsp;:&nbsp;' . __( '00 minutes ', 'order-delivery-date' );
	    echo '<select name="orddd_delivery_to_hours" id="orddd_delivery_to_hours" size="1">';
	    
	    for ( $i = 1 ; $i <= 23 ; $i++ ) {
	        printf( "<option %s value='%s'>%s</option>\n",
                selected( $i, get_option('orddd_delivery_to_hours'), false ),
                esc_attr( $i ),
                $i
	        );
	    }
	    echo '</select>&nbsp;:&nbsp;' . __('59 minutes', 'order-delivery-date' );
	    
	    $html = '<label for="orddd_time_range"> ' . $args[0] . '</label>';
	    echo $html;
	}

	

    /**
     * Callback for adding Same day settings
     */
	public static function orddd_same_day_delivery_callback() { }

	/**
	 * Callback for adding Enable same day delivery setting
	 * 
	 * @param array $args Extra arguments containing label & class for the field
	 * @since 2.4
	 */
    public static function orddd_enable_same_day_delivery_callback( $args ) {
	   $enable_same_day_delivery = "";
	   if ( get_option( 'orddd_enable_same_day_delivery' ) == 'on' ) {
	       $enable_same_day_delivery = "checked";
	   }
	   
	   echo '<input type="checkbox" name="orddd_enable_same_day_delivery" id="orddd_enable_same_day_delivery" class="day-checkbox" ' . $enable_same_day_delivery . '/>';
	   
	   $html = '<label for="orddd_enable_same_day_delivery"> ' . $args[0] . '</label>';
	   echo $html;
	}
	
	/**
	 * Callback for adding Cut-off time for same day delivery setting
	 *
	 * @param array $args Extra arguments containing label & class for the field
	 * @since 2.4
	 */
	
	public static function orddd_cutoff_time_for_same_day_delivery_orders_callback( $args ) {
         
        echo __( 'Hours:', 'order-delivery-date' ) . '<select name="orddd_disable_same_day_delivery_after_hours" id="orddd_disable_same_day_delivery_after_hours" size="1">';
	    // same day delivery options
	    $cut_off_hour = get_option( 'orddd_disable_same_day_delivery_after_hours' );
	    $cut_off_minute = get_option( 'orddd_disable_same_day_delivery_after_minutes' );
	    for ( $i = 0 ; $i <= 23 ; $i++ ) {
	       $selected = "";
	       if ( $cut_off_hour == $i ) {
	           $selected = "selected";
	       }
	       
	       echo '<option value="' . $i . '" ' . $selected.'>' . $i . '</option>';
		}
	    
        echo '</select>&nbsp;&nbsp;' . __( 'Mins:', 'order-delivery-date' ).'<select name="orddd_disable_same_day_delivery_after_minutes" id="orddd_disable_same_day_delivery_after_minutes" size="1">';
        
	    for ( $i = 0 ; $i <= 59 ; $i++ ){
            $selected = "";
            if ( $cut_off_minute == $i ){
                $selected = "selected";
            }
            echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
	    }
	    echo '</select>';
	    
	    $html = '<label for="cutoff_time_for_same_day_delivery_orders"> ' . $args[0] . '</label>';
	    echo $html;
	}

	/**
	 * Callback for adding Additional charges setting
	 *
	 * @param array $args Extra arguments containing label & class for the field
	 * @since 2.4
	 */
	
	public static function orddd_additional_charges_for_same_day_delivery_callback( $args ) {
	   echo '<input type="text" name="orddd_same_day_additional_charges" id="orddd_same_day_additional_charges" value="' . get_option(	"orddd_same_day_additional_charges"	) . '"/>';

	   $html = '<label for="orddd_same_day_additional_charges"> ' . $args[0] . '</label>';
	   echo $html;
    }
	
    /**
     * Callback for adding Next day settings
     */
    
	public static function orddd_next_day_delivery_callback() { }

	/**
	 * Callback for adding Enable next day delivery setting
	 *
	 * @param array $args Extra arguments containing label & class for the field
	 * @since 2.4
	 */
	
	public static function orddd_enable_next_day_delivery_callback( $args ) {
	   $enable_next_day_delivery = "";
	   if ( get_option( 'orddd_enable_next_day_delivery' ) == 'on' ) {
	       $enable_next_day_delivery = "checked";
	   }
	   
	   echo '<input type="checkbox" name="orddd_enable_next_day_delivery" id="orddd_enable_next_day_delivery" class="day-checkbox" ' . $enable_next_day_delivery . '/>';

	   $html = '<label for="orddd_enable_next_day_delivery"> ' . $args[0] . '</label>';
	   echo $html;
    }

    /**
     * Callback for adding Cut-off time for next day delivery setting
     *
     * @param array $args Extra arguments containing label & class for the field
	 * @since 2.4
     */
    
	public static function orddd_cutoff_time_for_next_day_delivery_orders_callback( $args ) {
	   echo __( 'Hours:', 'order-delivery-date' ) . '<select name="orddd_disable_next_day_delivery_after_hours" id="orddd_disable_next_day_delivery_after_hours" size="1">';
	   // next day delivery options
	   $cut_off_hour = get_option( 'orddd_disable_next_day_delivery_after_hours' );
	   $cut_off_minute = get_option( 'orddd_disable_next_day_delivery_after_minutes' );
	        							
	   for ( $i = 0 ; $i <= 23 ; $i++ ) {
	       $selected = "";
	       if ( $cut_off_hour == $i ) {
	           $selected = " selected ";
	       }
	       echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
	   }

	   echo '</select>&nbsp;&nbsp;' . __( 'Mins:', 'order-delivery-date' ) . '<select name="orddd_disable_next_day_delivery_after_minutes" id="orddd_disable_next_day_delivery_after_minutes" size="1">';
	   
	   for ( $i = 0 ; $i <= 59 ; $i++ ){
	       $selected = "";
	       if ( $cut_off_minute == $i ){
	           $selected = "selected";
	       }
	       
	       echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
	   }
	   
	   printf('</select>');

	   $html = '<label for="cutoff_time_for_next_day_delivery_orders"> ' . $args[0] . '</label>';
	   echo $html;
	}

	/**
	 * Callback for adding Additional charges for next day delivery setting
	 *
	 * @param array $args Extra arguments containing label & class for the field
	 * @since 2.4
	 */
	
	public static function orddd_additional_charges_for_next_day_delivery_callback( $args ) {
	   printf( 
	       '<input type="text" name="orddd_next_day_additional_charges" id="orddd_next_day_additional_charges" value="' . get_option(	"orddd_next_day_additional_charges"	) . '"/>'
	   );

	   $html = '<label for="orddd_next_day_additional_charges"> ' . $args[0] . '</label>';
	   echo $html;
    }
}