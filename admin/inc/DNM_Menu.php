<?php
defined( 'ABSPATH' ) || die();

require_once DNM_PLUGIN_DIR_PATH . 'includes/helpers/DNM_Config.php';
require_once DNM_PLUGIN_DIR_PATH . 'includes/helpers/DNM_Helper.php';

class Dnm_menu {
	// Create menu pages.
	public static function create_menu() {
		$dnm = add_menu_page( esc_html__( 'Donation', 'donation' ), esc_html__( 'Donation', 'donation' ), DNM_ADMIN_CAPABILITY, DNM_DASHBOARD, array( 'dnm_menu', 'donation_dash' ), 'dashicons-heart', 27 );
		self::add_admin_print_styles( $dnm );

		$dashboard_submenu = add_submenu_page( DNM_DASHBOARD, esc_html__( 'Dashboard', 'donation' ), esc_html__( 'Dashboard', 'donation' ), DNM_ADMIN_CAPABILITY, DNM_DASHBOARD, array( 'dnm_menu', 'donation_dash' ) );
		self::add_admin_print_styles( $dashboard_submenu );

		$orders_memberships_submenu = add_submenu_page( DNM_DASHBOARD, esc_html__( 'Memberships', 'donation' ), esc_html__( 'Memberships', 'donation' ), DNM_ADMIN_CAPABILITY, DNM_MEMBERSHIPS_ORDERS_PAGE, array( 'dnm_menu', 'donation_memberships_orders' ) );
		self::add_admin_print_styles( $orders_memberships_submenu );

		$orders_submenu = add_submenu_page( DNM_DASHBOARD, esc_html__( 'Fixed', 'donation' ), esc_html__( 'Fixed', 'donation' ), DNM_ADMIN_CAPABILITY, DNM_ORDERS_PAGE, array( 'dnm_menu', 'donation_orders' ) );
		self::add_admin_print_styles( $orders_submenu );

		$orders_custom_submenu = add_submenu_page( DNM_DASHBOARD, esc_html__( 'Custom', 'donation' ), esc_html__( 'Custom', 'donation' ), DNM_ADMIN_CAPABILITY, DNM_CUSTOM_ORDERS_PAGE, array( 'dnm_menu', 'donation_custom_orders' ) );
		self::add_admin_print_styles( $orders_custom_submenu );

		$setting_submenu = add_submenu_page( DNM_DASHBOARD, esc_html__( 'Settings', 'donation' ), esc_html__( 'Settings', 'donation' ), DNM_ADMIN_CAPABILITY, DNM_SETTING_PAGE, array( 'dnm_menu', 'donation_setting' ) );
		self::add_admin_print_styles( $setting_submenu );
	}

	private static function add_admin_print_styles( $hook_suffix ) {
		add_action( 'admin_print_styles-' . $hook_suffix, array( 'dnm_menu', 'menu_page_assets' ) );
	}

	public static function menu_page_assets() {
		wp_enqueue_style( 'dnm-bootstrap', DNM_PLUGIN_URL . '/assets/css/bootstrap.min.css', array(), DNM_VERSION );
		wp_enqueue_style( 'dnm-data-table', DNM_PLUGIN_URL . '/assets/css/datatables.min.css', array(), DNM_VERSION );
		wp_enqueue_style( 'dnm-admin-css', DNM_PLUGIN_URL . '/assets/css/admin.css', array(), DNM_VERSION );
		wp_enqueue_style( 'dnm-bootstrap-icons', DNM_PLUGIN_URL . '/assets//css/bootstrap-icons.min.css', array(), DNM_VERSION );

		wp_enqueue_script( 'dnm-bootstrap-js', DNM_PLUGIN_URL . '/assets/js/bootstrap.min.js', array( 'jquery', 'dnm-data-table-js' ), DNM_VERSION, true );
		wp_enqueue_script( 'dnm-data-table-js', DNM_PLUGIN_URL . '/assets/js/datatables.min.js', array( 'jquery' ), DNM_VERSION, true );
		wp_enqueue_script( 'dnm-admin-js', DNM_PLUGIN_URL . '/assets/js/admin.js', array( 'jquery', 'dnm-data-table-js' ), DNM_VERSION, true );
		wp_localize_script(
			'dnm-admin-js',
			'dnmData',
			array(
				'date_format' => DNM_Config::date_format(),
				'currency'    => DNM_Config::get_currency(),
			)
		);
	}

	// dashboard.
	public static function donation_dash() {
		require_once DNM_PLUGIN_DIR_PATH . 'admin/inc/manager/dashboard/index.php';
	}

	// Orders.
	public static function donation_orders() {
		require_once DNM_PLUGIN_DIR_PATH . 'admin/inc/manager/orders/route.php';
	}

	// Custom Orders.
	public static function donation_custom_orders() {
		require_once DNM_PLUGIN_DIR_PATH . 'admin/inc/manager/custom-orders/route.php';
	}

	// Memberships Orders.
	public static function donation_memberships_orders() {
		require_once DNM_PLUGIN_DIR_PATH . 'admin/inc/manager/memberships/route.php';
	}

	// Setting.
	public static function donation_setting() {
		require_once DNM_PLUGIN_DIR_PATH . 'admin/inc/manager/setting/index.php';
	}
}
