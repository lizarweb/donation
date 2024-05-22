<?php
defined( 'ABSPATH' ) || die();

require_once DNM_PLUGIN_DIR_PATH . 'includes/helpers/DNM_Config.php';
require_once DNM_PLUGIN_DIR_PATH . 'includes/helpers/DNM_Helper.php';
require_once DNM_PLUGIN_DIR_PATH . 'admin/inc/DNM_Database.php';
require_once DNM_PLUGIN_DIR_PATH . 'includes/vendor/autoload.php';

use PhonePe\PhonePe;

try {
	$user_data = get_transient( 'user_data' ); // Transaction ID to track and identify the transaction, make sure to save this in your database.

	$user         = $user_data['user'];
	$payment_type = isset( $user['payment_type'] ) ? $user['payment_type'] : 'membership';
	$reference_id = isset( $user['reference_id'] ) ? $user['reference_id'] : null;

	// $transaction_id = 'MERCHANT' . rand( 100000, 999999 ); // Transaction ID to track and identify the transaction, make sure to save this in your database.
	$phone_pay_settings = DNM_Config::get_phone_pay_settings();
	if ( empty( $phone_pay_settings ) ) {
		throw new Exception( 'Phone pay settings are not configured properly.' );
	}
	$phonepe = PhonePe::init(
		$phone_pay_settings['phone_pay_merchant_id'], // Merchant ID
		$phone_pay_settings['phone_pay_merchant_user_id'], // Merchant User ID
		$phone_pay_settings['phone_pay_salt_key'], // Salt Key
		$phone_pay_settings['phone_pay_salt_index'], // Salt Index
		$phone_pay_settings['phone_pay_redirect_url'], // Redirect URL, can be defined on per transaction basis
		$phone_pay_settings['phone_pay_redirect_url'], // Callback URL, can be defined on per transaction basis
	);

	$success = $phonepe->standardCheckout()->isTransactionSuccessByTransactionId( $user_data['transactionID'] ); // Returns true if transaction is successful, false otherwise.

	if ( $success ) {
		global $wpdb;


		// check if transactionID already exists.
		$exists = DNM_Database::getRecord( DNM_ORDERS, 'transaction_id', $user_data['transactionID'] );

		// If transactionID does not exist, then proceed
		if ( ! $exists ) {
			$wpdb->query( 'BEGIN' );

			try {
				$customerData = array(
					'name'         => $user['name'],
					'email'        => $user['email'],
					'phone'        => $user['phone'],
					'city'         => $user['city'],
					'state'        => $user['state'],
					'address'      => $user['address'],
					'reference_id' => $reference_id,
					'created_at'   => current_time( 'mysql' ),
				);

				$customer_id = DNM_Database::insertIntoTable( DNM_CUSTOMERS, $customerData );

				if ( ! $customer_id ) {
					throw new Exception( 'Failed to insert customer data' );
				}

				$order_data = array(
					'order_id'       => DNM_Helper::getNextOrderId(),
					'transaction_id' => $user_data['transactionID'], // Corrected 'tnasaction_id' to 'transaction_id'
					'type'           => $payment_type,
					'payment_method' => 'Phonepe',
					'customer_id'    => $customer_id,
					'amount'         => $user['amount'],
					'created_at'     => current_time( 'mysql' ),
				);
				$order_id   = DNM_Database::insertIntoTable( DNM_ORDERS, $order_data );

				if ( ! $order_id ) {
					throw new Exception( 'Failed to insert order data' );
				}

				$wpdb->query( 'COMMIT' );

			} catch ( Exception $e ) {
				$wpdb->query( 'ROLLBACK' ); // If any exception is thrown, rollback the transaction
				error_log( $e->getMessage() ); // Log the error message for debugging
			}
		}
		?>
		<div class="alert alert-success" role="alert">
			<p>Your transaction was successful.</p>
		</div>
		<?php
	} else {
		?>
		<div class="alert alert-danger" role="alert">
			<p>Your transaction was not successful. Please try again later.</p>
		</div>
		<?php
	}
} catch ( Exception $e ) {
	$wpdb->query( 'ROLLBACK' );
	// error_log($e->getMessage());
	// Handle exception here, e.g., show a user-friendly error message
	?>
		<div class="alert alert-danger" role="alert">
			<p>There was an error processing your transaction. Please try again later.</p>
		</div>
	<?php
}

