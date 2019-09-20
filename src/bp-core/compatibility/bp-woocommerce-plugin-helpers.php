<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add BuddyBoss Platform Login Module when user sing up from WooCommerce Checkout Page
 *
 * Support WooCommerce
 *
 * @since BuddyBoss    1.1.9
 */
function bp_helper_woocommerce_created_customer_callback( $user_id, $new_customer_data, $ps_generated ) {
	global $wpdb, $customer_data, $password_generated;
	$customer_data      = $new_customer_data;
	$password_generated = $ps_generated;

	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->users} SET user_status = 2 WHERE ID = %d", $user_id ) );

	add_filter( 'bp_email_set_content_html', 'bp_helper_bp_email_set_content_html_callback', 100 );
	add_filter( 'bp_email_set_content_plaintext', 'bp_helper_bp_email_set_content_plaintext_callback', 100 );
	add_action( 'bp_core_signup_user', 'bp_helper_bp_core_signup_user_callback', 100, 6 );
	bp_core_signup_user( $new_customer_data['user_login'], $password_generated, $new_customer_data['user_email'], array( 'user_id' => $user_id ) );
	remove_filter( 'bp_email_set_content_plaintext', 'bp_helper_bp_email_set_content_plaintext_callback', 100 );
	remove_filter( 'bp_email_set_content_html', 'bp_helper_bp_email_set_content_html_callback', 100 );
	remove_action( 'bp_core_signup_user', 'bp_helper_bp_core_signup_user_callback', 100 );
}

function bp_helper_bp_email_set_content_html_callback( $content ) {
	global $customer_data, $password, $password_generated;

	if ( 'yes' === get_option( 'woocommerce_registration_generate_username', 'yes' ) && ! empty( $customer_data ) ) {
		ob_start();
		echo $content;

		printf( '<p>%s %s</p>',
			__( 'Your username has been automatically generated:', 'buddyboss' ),
			'<strong>' . $customer_data['user_login'] . '</strong>'
		);

		$content = ob_get_contents();
		ob_end_clean();
	}

	if ( ! empty( $password_generated ) ) {
		ob_start();
		echo $content;

		printf( '<p>%s %s</p>',
			__( 'Your password has been automatically generated:', 'buddyboss' ),
			'<strong>' . $password . '</strong>'
		);
		$content = ob_get_contents();
		ob_end_clean();
	}

	return $content;
}

function bp_helper_bp_email_set_content_plaintext_callback( $content ) {
	global $customer_data, $password, $password_generated;

	if ( 'yes' === get_option( 'woocommerce_registration_generate_username', 'yes' ) && ! empty( $customer_data ) ) {
		ob_start();

		printf( $content . __( '
Your username has been automatically generated: %s', 'buddyboss' ),
			esc_html( $customer_data['user_login'] )
		);
		$content = ob_get_contents();
		ob_end_clean();
	}

	if ( ! empty( $password_generated ) ) {
		ob_start();

		printf( $content . __( '
Your password has been automatically generated: %s', 'buddyboss' ),
			$password
		);
		$content = ob_get_contents();
		ob_end_clean();
	}

	return $content;
}

/**
 * Get called on Checkout page to add activation_key for the new user that is create by WooCommerce
 *
 * Support WooCommerce
 *
 * @since BuddyBoss    1.1.9
 */
function bp_helper_bp_core_signup_user_callback( $user_id, $user_login, $user_password, $user_email, $usermeta, $activation_key ) {
	if ( ! empty( $usermeta['user_id'] ) ) {
		bp_update_user_meta( $usermeta['user_id'], 'activation_key', $activation_key );
	}
}

/**
 * Get called on Checkout page to remove the new user role
 *
 * Support WooCommerce
 *
 * @since BuddyBoss    1.1.9
 */
function bp_helper_woocommerce_new_customer_data_callback( $args ) {
	global $password;

	if ( $args['role'] ) {
		unset( $args['role'] );
	}

	$password = $args['user_pass'];

	return $args;
}

/**
 * Get called on Checkout page when user is non login
 *
 * Support WooCommerce
 *
 * @since BuddyBoss    1.1.9
 */
function bp_helper_woocommerce_thankyou_order_received_text_callback( $text ) {
	if ( ! is_user_logged_in() && is_checkout() && bp_helper_woocommerce_change_checkout_process() ) {
		$text = __( 'Before you can login, you need to confirm your email address via the email we just sent to you.', 'buddyboss' );
	}

	return $text;
}

/**
 * Get called on Checkout page when user is non login
 *
 * Support WooCommerce
 *
 * @since BuddyBoss    1.1.9
 */
function bp_helper_woocommerce_checkout_process_callback() {

	if ( ! is_user_logged_in() && bp_helper_woocommerce_change_checkout_process() ) {
		/**
		 * Do not allow user to get login when they create account from checkout page
		 */
		add_filter( 'send_auth_cookies', '__return_false', 100 );

		/**
		 * Unset the User role of the user that is getting create via WooCommerce checkout Page
		 */
		add_filter( 'woocommerce_new_customer_data', 'bp_helper_woocommerce_new_customer_data_callback' );

		/**
		 * Do not allow the BuddyPress create account function to add nen user just add the Activation key for the user
		 */
		add_filter( 'bp_core_signups_do_not_skip_user_creation', '__return_false', 100 );

		/**
		 * Disable new account creating Email on Checkout page
		 */
		add_filter( 'woocommerce_email_enabled_customer_new_account', '__return_false', 100 );

		/**
		 * Called BuddyPress function to generate and send  user activation key
		 */
		add_action( 'woocommerce_created_customer', 'bp_helper_woocommerce_created_customer_callback', 100, 3 );
	}
}

add_action( 'woocommerce_checkout_process', 'bp_helper_woocommerce_checkout_process_callback' );
add_filter( 'woocommerce_thankyou_order_received_text', 'bp_helper_woocommerce_thankyou_order_received_text_callback', 100, 3 );

/**
 * Get called to check if the WooCommerce checkout process should get change
 *
 * Support WooCommerce Checkout
 *
 * @since BuddyBoss    1.1.9
 */
function bp_helper_woocommerce_change_checkout_process() {
	/**
	 * Filter to alter the WooCommerce checkout process
	 * Support WooCommerce Checkout
	 *
	 * @since BuddyBoss    1.1.9
	 *
	 * @param bool True if like BuddyPress Platform or else false
	 *
	 * @return bool True if like BuddyPress Platform or else false
	 */
	return (bool) apply_filters( 'bp_helper_woocommerce_change_checkout_process', true );
}