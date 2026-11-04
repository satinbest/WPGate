<?php
/**
 * WPGate Standalone Core - Bootstrap
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

defined( 'ABSPATH' ) || exit;

// Safe kill-switch: Allows completely disabling the module via wp-config.php
if ( ( defined( 'WPGATE_DISABLED' ) && WPGATE_DISABLED ) ||
     ( defined( 'WPGATE_CORE_DISABLED' ) && WPGATE_CORE_DISABLED ) ) {
	return;
}

// Prevent duplicate initialization
if ( defined( 'WPGATE_CORE_LOADED' ) ) {
	return;
}
define( 'WPGATE_CORE_LOADED', true );

// Define core constants
define( 'WPGATE_CORE_VERSION', '0.1.0' );
define( 'WPGATE_CORE_FILE', __FILE__ );
define( 'WPGATE_CORE_DIR', __DIR__ );

// Register dedicated autoloader for WPGate\Core namespace
spl_autoload_register(
	function( $class ) {
		$prefix = 'WPGate\\Core\\';
		$base_dir = __DIR__;

		$len = strlen( $prefix );
		if ( 0 !== strncmp( $prefix, $class, $len ) ) {
			return;
		}

		$relative_class = substr( $class, $len );

		// Check Admin namespace
		if ( 0 === strncmp( 'Admin\\', $relative_class, 6 ) ) {
			$admin_class = substr( $relative_class, 6 );
			$file = $base_dir . '/admin/' . str_replace( '\\', '/', $admin_class ) . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
				return;
			}
		}

		// Check Includes directory
		$file = $base_dir . '/includes/' . str_replace( '\\', '/', $relative_class ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

// Initialize Context
\WPGate\Core\Context::init( __DIR__ );

// Register HTTP & Request interception hooks
add_filter( 'pre_http_request', [ \WPGate\Core\HttpGuard::class, 'filter' ], 10, 3 );
add_filter( 'pre_http_request', [ \WPGate\Core\TelemetryGuard::class, 'filter' ], 20, 3 );
add_filter( 'http_request_args', [ \WPGate\Core\TimeoutGuard::class, 'filter' ], 20, 2 );

// Register Asset replacement hooks
add_filter( 'script_loader_src', [ \WPGate\Core\AssetManager::class, 'replace_script_src' ], 20, 2 );
add_filter( 'style_loader_src', [ \WPGate\Core\AssetManager::class, 'replace_style_src' ], 20, 2 );
add_action( 'wp_enqueue_scripts', [ \WPGate\Core\AssetManager::class, 'maybe_enqueue_vazirmatn' ], 20 );

// Register Gravatar replacement hook
add_filter( 'get_avatar', [ \WPGate\Core\GravatarGuard::class, 'filter' ], 10, 5 );

// Initialize Updates control
\WPGate\Core\UpdateGuard::init();

// Register REST API endpoints
add_action( 'rest_api_init', [ \WPGate\Core\RestController::class, 'register_routes' ] );

// Initialize Admin interface only if in WordPress admin area
if ( is_admin() ) {
	\WPGate\Core\Admin\AdminController::init();
}
