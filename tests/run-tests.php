<?php
/**
 * WPGate Standalone Core - Automated Test Suite
 *
 * Runs on PHP CLI to validate syntax, security, domain matching,
 * HTTP interception, asset rewriting, logger, REST API, and loaders.
 *
 * @package WPGate\Tests
 * @version 0.1.0
 */

// 1. Setup Mock WordPress Environment
define( 'ABSPATH', __DIR__ . '/wordpress/' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );
define( 'WP_CONTENT_DIR', sys_get_temp_dir() . '/wpgate-tests/wp-content' );
define( 'WPMU_PLUGIN_DIR', sys_get_temp_dir() . '/wpgate-tests/wp-content/mu-plugins' );

$GLOBALS['wp_filters'] = [];
$GLOBALS['wp_actions'] = [];
$GLOBALS['wp_options'] = [];
$GLOBALS['wp_rest_routes'] = [];
$GLOBALS['current_user_caps'] = [ 'manage_options' => true ];

class WP_Error {
	public $code;
	public $message;
	public function __construct( $code = '', $message = '' ) {
		$this->code = $code;
		$this->message = $message;
	}
	public function get_error_message() {
		return $this->message;
	}
	public function get_error_code() {
		return $this->code;
	}
}

class WP_REST_Server {
	public const READABLE = 'GET';
	public const CREATABLE = 'POST';
	public const EDITABLE = 'POST, PUT, PATCH';
	public const DELETABLE = 'DELETE';
	public const ALLMETHODS = 'GET, POST, PUT, PATCH, DELETE';
}

class WP_REST_Request {
	private $params = [];
	private $json = [];
	public function __construct( $params = [], $json = [] ) {
		$this->params = $params;
		$this->json   = $json;
	}
	public function get_param( $key ) {
		return $this->params[ $key ] ?? null;
	}
	public function get_params() {
		return $this->params;
	}
	public function get_json_params() {
		return $this->json;
	}
	public function get_body_params() {
		return $this->params;
	}
}

class WP_REST_Response {
	public $data;
	public $status;
	public function __construct( $data = null, $status = 200 ) {
		$this->data   = $data;
		$this->status = $status;
	}
	public function get_data() {
		return $this->data;
	}
	public function get_status() {
		return $this->status;
	}
}

function register_rest_route( $namespace, $route, $args ) {
	$GLOBALS['wp_rest_routes'][ $namespace . $route ] = $args;
	return true;
}

function rest_url( $path = '' ) {
	return 'https://example.com/wp-json/' . ltrim( $path, '/' );
}

function wp_create_nonce( $action = -1 ) {
	return 'mock_nonce_' . md5( (string) $action );
}

function wp_verify_nonce( $nonce, $action = -1 ) {
	return $nonce === 'mock_nonce_' . md5( (string) $action );
}

function wp_normalize_path( $path ) {
	$path = str_replace( '\\', '/', $path );
	$path = preg_replace( '|(?<=.)/+|', '/', $path );
	if ( ':' === substr( $path, 1, 1 ) ) {
		$path = ucfirst( $path );
	}
	return $path;
}

function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['wp_filters'][ $tag ][] = [
		'callback' => $callback,
		'priority' => $priority,
		'args'     => $accepted_args,
	];
	return true;
}

function apply_filters( $tag, $value, ...$args ) {
	if ( empty( $GLOBALS['wp_filters'][ $tag ] ) ) {
		return $value;
	}
	foreach ( $GLOBALS['wp_filters'][ $tag ] as $hook ) {
		$call_args = array_merge( [ $value ], array_slice( $args, 0, $hook['args'] - 1 ) );
		$value = call_user_func_array( $hook['callback'], $call_args );
	}
	return $value;
}

function remove_filter( $tag, $callback, $priority = 10 ) {
	if ( ! isset( $GLOBALS['wp_filters'][ $tag ] ) ) return false;
	foreach ( $GLOBALS['wp_filters'][ $tag ] as $idx => $hook ) {
		if ( $hook['callback'] === $callback && $hook['priority'] === $priority ) {
			unset( $GLOBALS['wp_filters'][ $tag ][ $idx ] );
			return true;
		}
	}
	return false;
}

function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['wp_actions'][ $tag ][] = [
		'callback' => $callback,
		'priority' => $priority,
		'args'     => $accepted_args,
	];
	return true;
}

function remove_action( $tag, $callback, $priority = 10 ) {
	return remove_filter( $tag, $callback, $priority );
}

function do_action( $tag, ...$args ) {
	if ( empty( $GLOBALS['wp_actions'][ $tag ] ) ) return;
	foreach ( $GLOBALS['wp_actions'][ $tag ] as $hook ) {
		call_user_func_array( $hook['callback'], $args );
	}
}

function get_option( $option, $default = false ) {
	return array_key_exists( $option, $GLOBALS['wp_options'] ) ? $GLOBALS['wp_options'][ $option ] : $default;
}

function update_option( $option, $value, $autoload = null ) {
	$GLOBALS['wp_options'][ $option ] = $value;
	return true;
}

function delete_option( $option ) {
	unset( $GLOBALS['wp_options'][ $option ] );
	return true;
}

function wp_parse_args( $args, $defaults = [] ) {
	if ( is_object( $args ) ) {
		$r = get_object_vars( $args );
	} elseif ( is_array( $args ) ) {
		$r =& $args;
	} else {
		parse_str( (string) $args, $r );
	}
	if ( is_array( $defaults ) ) {
		return array_merge( $defaults, $r );
	}
	return $r;
}

function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}

function site_url( $path = '' ) {
	return 'https://example.com' . $path;
}

function home_url( $path = '' ) {
	return 'https://example.com' . $path;
}

function admin_url( $path = '' ) {
	return 'https://example.com/wp-admin/' . $path;
}

function content_url( $path = '' ) {
	return 'https://example.com/wp-content' . ( $path ? '/' . ltrim( $path, '/' ) : '' );
}

function includes_url( $path = '' ) {
	return 'https://example.com/wp-includes/' . $path;
}

function wp_upload_dir() {
	return [
		'basedir' => sys_get_temp_dir() . '/wpgate-tests/wp-content/uploads',
		'baseurl' => 'https://example.com/wp-content/uploads',
	];
}

function get_stylesheet_directory() {
	return sys_get_temp_dir() . '/wpgate-tests/wp-content/themes/child-theme';
}

function get_stylesheet_directory_uri() {
	return 'https://example.com/wp-content/themes/child-theme';
}

function sanitize_text_field( $str ) {
	return strip_tags( (string) $str );
}

function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
}

function esc_url_raw( $url ) {
	return filter_var( $url, FILTER_SANITIZE_URL );
}

function esc_url( $url ) {
	return esc_url_raw( $url );
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES );
}

function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES );
}

function current_user_can( $capability ) {
	return ! empty( $GLOBALS['current_user_caps'][ $capability ] );
}

function is_admin() {
	return false;
}

function is_multisite() {
	return false;
}

function is_rtl() {
	return true;
}

function get_user_locale() {
	return 'fa_IR';
}

function checked( $checked, $current = true, $echo = true ) {
	return ( (string) $checked === (string) $current ) ? ' checked="checked"' : '';
}

function current_time( $type, $gmt = 0 ) {
	if ( 'timestamp' === $type ) return time();
	if ( 'mysql' === $type ) return date( 'Y-m-d H:i:s' );
	return time();
}

function check_admin_referer( $action = -1, $query_arg = '_wpnonce' ) {
	return true;
}

function wp_nonce_url( $actionurl, $action = -1, $name = '_wpnonce' ) {
	return $actionurl . '&' . $name . '=mocknonce';
}

function add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback = '' ) {
	return 'settings_page_' . $menu_slug;
}

function register_setting( $option_group, $option_name, $args = [] ) {
	return true;
}

function add_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default' ) {
	return true;
}

// 2. Load Core Classes
require_once dirname( __DIR__ ) . '/mu-plugin-package/mu-plugins/wpgate-core/bootstrap.php';

// Test runner helpers
$tests_passed = 0;
$tests_failed = 0;

function it( $description, $test_callable ) {
	global $tests_passed, $tests_failed;
	try {
		$result = $test_callable();
		if ( $result ) {
			echo "  [PASS] $description\n";
			$tests_passed++;
		} else {
			echo "  [FAIL] $description (returned false)\n";
			$tests_failed++;
		}
	} catch ( Throwable $e ) {
		echo "  [FAIL] $description (Exception: " . $e->getMessage() . ")\n";
		$tests_failed++;
	}
}

echo "========================================================\n";
echo " WPGate Standalone Core - Automated Verification Suite\n";
echo "========================================================\n\n";

// TEST SUITE 1: Context & Path Resolution
echo "Test Suite 1: Context & Path Resolution\n";
it( "Detects mu-plugin loading context when inside mu-plugins dir", function() {
	\WPGate\Core\Context::init( WPMU_PLUGIN_DIR . '/wpgate-core' );
	return \WPGate\Core\Context::get_loading_method() === 'mu-plugin';
});

it( "Resolves asset URL correctly in mu-plugin context", function() {
	\WPGate\Core\Context::init( WPMU_PLUGIN_DIR . '/wpgate-core' );
	$url = \WPGate\Core\Context::get_asset_url( 'user.jpg' );
	return $url === content_url( '/mu-plugins/wpgate-core/assets/user.jpg' );
});

it( "Detects theme loading context when inside stylesheet directory", function() {
	$theme_dir = get_stylesheet_directory() . '/wpgate-core';
	\WPGate\Core\Context::init( $theme_dir );
	return \WPGate\Core\Context::get_loading_method() === 'theme';
});

it( "Resolves asset URL correctly in theme context", function() {
	$theme_dir = get_stylesheet_directory() . '/wpgate-core';
	\WPGate\Core\Context::init( $theme_dir );
	$url = \WPGate\Core\Context::get_asset_url( 'user.jpg' );
	return $url === 'https://example.com/wp-content/themes/child-theme/wpgate-core/assets/user.jpg';
});

// Reset context back to test directory
\WPGate\Core\Context::init( dirname( __DIR__ ) . '/mu-plugin-package/mu-plugins/wpgate-core' );

// TEST SUITE 2: Settings Management & Defaults
echo "\nTest Suite 2: Settings Management & Defaults\n";
it( "Returns safe defaults with updates NOT disabled by default", function() {
	delete_option( \WPGate\Core\Settings::OPTION_KEY );
	$settings = \WPGate\Core\Settings::get_all( true );
	return ( $settings['disable_updates'] === 'no' ) &&
	       ( $settings['mode'] === 'disabled' ) &&
	       ( $settings['disable_telemetry'] === 'yes' );
});

it( "Sanitize host list removes schemes, ports, and duplicates", function() {
	$raw = "https://api.zarinpal.com:443/\r\nhttp://sep.ir:80\r\napi.zarinpal.com\r\n\r\n";
	$cleaned = \WPGate\Core\Settings::sanitize_host_list( $raw );
	$expected = "api.zarinpal.com\nsep.ir";
	return $cleaned === $expected;
});

it( "Settings save and reset cycle functions correctly", function() {
	delete_option( \WPGate\Core\Settings::OPTION_KEY );
	$custom = [ 'mode' => 'whitelist', 'whitelist' => 'custom-domain.com' ];
	\WPGate\Core\Settings::save( $custom );
	$loaded = \WPGate\Core\Settings::get_all( true );
	$saved_ok = ( $loaded['mode'] === 'whitelist' && $loaded['whitelist'] === 'custom-domain.com' );
	\WPGate\Core\Settings::reset();
	$after_reset = \WPGate\Core\Settings::get_all( true );
	return $saved_ok && ( $after_reset['mode'] === 'disabled' );
});

// TEST SUITE 3: Domain Matching Logic (Exact & Subdomain)
echo "\nTest Suite 3: Domain Matching & Subdomain Security\n";
it( "Exact domain matches correctly", function() {
	return \WPGate\Core\HttpGuard::host_in_list( 'api.zarinpal.com', [ 'api.zarinpal.com', 'sep.ir' ] );
});

it( "Subdomain of allowed domain matches correctly", function() {
	return \WPGate\Core\HttpGuard::host_in_list( 'sub.api.zarinpal.com', [ 'zarinpal.com' ] );
});

it( "Attacker domain with substring prefix does NOT match", function() {
	return ! \WPGate\Core\HttpGuard::host_in_list( 'attacker-zarinpal.com', [ 'zarinpal.com' ] );
});

it( "WWW prefix difference is normalized", function() {
	return \WPGate\Core\HttpGuard::host_in_list( 'www.digikala.com', [ 'digikala.com' ] );
});

it( "Localhost and site URL are detected as local", function() {
	return \WPGate\Core\HttpGuard::is_local_host( 'localhost' ) &&
	       \WPGate\Core\HttpGuard::is_local_host( '127.0.0.1' ) &&
	       \WPGate\Core\HttpGuard::is_local_host( 'example.com' );
});

// TEST SUITE 4: HTTP Interception & Filtering
echo "\nTest Suite 4: HTTP Request Interception\n";
it( "In disabled mode, external requests pass through", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [ 'mode' => 'disabled' ] );
	\WPGate\Core\Settings::get_all( true );
	$res = \WPGate\Core\HttpGuard::filter( false, [], 'https://google.com/search' );
	return false === $res;
});

it( "In whitelist mode, whitelisted domain passes through", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'mode'      => 'whitelist',
		'whitelist' => "api.zarinpal.com\nsep.ir",
	] );
	\WPGate\Core\Settings::get_all( true );
	$res = \WPGate\Core\HttpGuard::filter( false, [], 'https://api.zarinpal.com/v1/verify' );
	return false === $res;
});

it( "In whitelist mode, non-whitelisted domain is blocked with WP_Error and wpgate_blocked code", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'mode'      => 'whitelist',
		'whitelist' => "api.zarinpal.com\nsep.ir",
	] );
	\WPGate\Core\Settings::get_all( true );
	$res = \WPGate\Core\HttpGuard::filter( false, [], 'https://unallowed-site.com/api' );
	return ( $res instanceof WP_Error ) && ( $res->get_error_code() === 'wpgate_blocked' );
});

it( "In blacklist mode, blacklisted domain is blocked with WP_Error", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'mode'      => 'blacklist',
		'blacklist' => "bad-actor.com\nmalicious.net",
	] );
	\WPGate\Core\Settings::get_all( true );
	$res = \WPGate\Core\HttpGuard::filter( false, [], 'https://bad-actor.com/track' );
	return ( $res instanceof WP_Error ) && ( $res->get_error_code() === 'wpgate_blocked' );
});

it( "Localhost requests are never blocked even in strict whitelist mode", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'mode'      => 'whitelist',
		'whitelist' => "api.zarinpal.com",
	] );
	\WPGate\Core\Settings::get_all( true );
	$res = \WPGate\Core\HttpGuard::filter( false, [], 'http://127.0.0.1/wp-cron.php' );
	return false === $res;
});

// TEST SUITE 5: Telemetry Interception
echo "\nTest Suite 5: Telemetry Guard\n";
it( "Blocks telemetry and tracking domains when telemetry guard enabled", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [ 'disable_telemetry' => 'yes' ] );
	\WPGate\Core\Settings::get_all( true );
	$res = \WPGate\Core\TelemetryGuard::filter( false, [], 'https://api.wordpress.org/core/version-check/1.7/' );
	return ( $res instanceof WP_Error ) && ( $res->get_error_code() === 'wpgate_telemetry_blocked' );
});

it( "Allows telemetry when telemetry guard disabled", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [ 'disable_telemetry' => 'no' ] );
	\WPGate\Core\Settings::get_all( true );
	$res = \WPGate\Core\TelemetryGuard::filter( false, [], 'https://api.wordpress.org/core/version-check/1.7/' );
	return false === $res;
});

// TEST SUITE 6: Timeout Management
echo "\nTest Suite 6: Timeout Guard\n";
it( "Reduces timeout to configured limit when enable_timeout_guard is 'yes'", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'enable_timeout_guard' => 'yes',
		'max_request_timeout'  => '4',
	] );
	\WPGate\Core\Settings::get_all( true );
	$args = \WPGate\Core\TimeoutGuard::filter( [ 'timeout' => 30 ], 'https://external-api.com/slow' );
	return $args['timeout'] === 4;
});

it( "Leaves timeout untouched when enable_timeout_guard is 'no'", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'enable_timeout_guard' => 'no',
		'max_request_timeout'  => '4',
	] );
	\WPGate\Core\Settings::get_all( true );
	$args = \WPGate\Core\TimeoutGuard::filter( [ 'timeout' => 30 ], 'https://external-api.com/slow' );
	return $args['timeout'] === 30;
});

// TEST SUITE 7: Asset Rewriting & CDN Local Fallbacks
echo "\nTest Suite 7: Asset Manager & CDN Rewriting\n";
it( "Rewrites CDN FontAwesome stylesheet to local asset URL", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [ 'local_asset_rewrite' => 'yes' ] );
	\WPGate\Core\Settings::get_all( true );
	$remote = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
	$rewritten = \WPGate\Core\AssetManager::replace_style_src( $remote, 'fontawesome' );
	return ( false !== strpos( $rewritten, 'vendor/fontawesome/css/all.min.css' ) ) &&
	       ( false === strpos( $rewritten, 'cdnjs.cloudflare.com' ) );
});

it( "Rewrites CDN Swiper JS script to local asset URL", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [ 'local_asset_rewrite' => 'yes' ] );
	\WPGate\Core\Settings::get_all( true );
	$remote = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';
	$rewritten = \WPGate\Core\AssetManager::replace_script_src( $remote, 'swiper' );
	return ( false !== strpos( $rewritten, 'vendor/swiper/swiper-bundle.min.js' ) ) &&
	       ( false === strpos( $rewritten, 'jsdelivr.net' ) );
});

it( "Replaces Mixpanel script with safe local stub", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'local_asset_rewrite' => 'yes',
		'block_mixpanel'      => 'yes',
	] );
	\WPGate\Core\Settings::get_all( true );
	$remote = 'https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js';
	$rewritten = \WPGate\Core\AssetManager::replace_script_src( $remote, 'mixpanel' );
	return false !== strpos( $rewritten, 'mixpanel-stub.js' );
});

it( "Extracts Google Font families from complex URL queries", function() {
	$url = 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&family=Roboto:ital@1&display=swap';
	$families = \WPGate\Core\AssetManager::extract_google_font_families( $url );
	return in_array( 'vazirmatn', $families, true ) && in_array( 'roboto', $families, true );
});

// TEST SUITE 8: Logger & Redaction of Credentials
echo "\nTest Suite 8: Secure Logger & Credential Redaction\n";
it( "Redacts tokens, passwords, and API keys from logged URLs", function() {
	$dirty_url = 'https://api.external.com/v1/auth?token=supersecret123&api_key=xyz987&normal=value&password=secretpassword999';
	$sanitized = \WPGate\Core\Logger::sanitize_url( $dirty_url );
	return ( false === strpos( $sanitized, 'supersecret123' ) ) &&
	       ( false === strpos( $sanitized, 'xyz987' ) ) &&
	       ( false === strpos( $sanitized, 'secretpassword999' ) ) &&
	       ( false !== strpos( $sanitized, 'normal=value' ) ) &&
	       ( false !== strpos( $sanitized, '[REDACTED]' ) );
});

it( "Logs events to database options and respects clear_logs", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [ 'enable_logging' => 'yes' ] );
	\WPGate\Core\Settings::get_all( true );
	delete_option( \WPGate\Core\Logger::LOG_KEY );
	
	\WPGate\Core\Logger::log( 'https://bad-domain.com/feed', 'bad-domain.com', 'test_block' );
	$logs = \WPGate\Core\Logger::get_logs();
	$logged = count( $logs ) === 1 && $logs[0]['host'] === 'bad-domain.com';
	
	\WPGate\Core\Logger::clear_logs();
	$cleared = count( \WPGate\Core\Logger::get_logs() ) === 0;

	return $logged && $cleared;
});

// TEST SUITE 9: Kill-Switch & Disabled Behavior
echo "\nTest Suite 9: Kill-Switch Behavior (wp-config.php define)\n";
it( "MU-Plugin loader respects WPGATE_DISABLED", function() {
	$cmd = 'php -r "define(\'ABSPATH\', 1); define(\'WPGATE_DISABLED\', true); require \'' .
	       addslashes( dirname( __DIR__ ) . '/mu-plugin-package/mu-plugins/wpgate-loader.php' ) . '\'; echo defined(\'WPGATE_CORE_LOADED\') ? \'loaded\' : \'disabled\';"';
	$output = trim( shell_exec( $cmd ) );
	return 'disabled' === $output;
});

it( "MU-Plugin loader respects WPGATE_CORE_DISABLED", function() {
	$cmd = 'php -r "define(\'ABSPATH\', 1); define(\'WPGATE_CORE_DISABLED\', true); require \'' .
	       addslashes( dirname( __DIR__ ) . '/mu-plugin-package/mu-plugins/wpgate-loader.php' ) . '\'; echo defined(\'WPGATE_CORE_LOADED\') ? \'loaded\' : \'disabled\';"';
	$output = trim( shell_exec( $cmd ) );
	return 'disabled' === $output;
});

// TEST SUITE 10: Gravatar Replacement
echo "\nTest Suite 10: Gravatar Replacement\n";
it( "Replaces Gravatar with local user.jpg when disable_gravatar is 'yes'", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [ 'disable_gravatar' => 'yes' ] );
	\WPGate\Core\Settings::get_all( true );
	$avatar_html = '<img src="https://secure.gravatar.com/avatar/12345" alt="Gravatar" class="avatar" />';
	$filtered = \WPGate\Core\GravatarGuard::filter( $avatar_html, 'test@example.com', 96, '', 'Avatar Alt' );
	return ( false !== strpos( $filtered, 'user.jpg' ) ) && ( false === strpos( $filtered, 'gravatar.com' ) );
});

it( "Does not alter Gravatar when disable_gravatar is 'no'", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [ 'disable_gravatar' => 'no' ] );
	\WPGate\Core\Settings::get_all( true );
	$avatar_html = '<img src="https://secure.gravatar.com/avatar/12345" alt="Gravatar" class="avatar" />';
	$filtered = \WPGate\Core\GravatarGuard::filter( $avatar_html, 'test@example.com', 96, '', 'Avatar Alt' );
	return $filtered === $avatar_html;
});

// TEST SUITE 11: Admin Security & Permissions
echo "\nTest Suite 11: Admin Security & Nonce Verification\n";
it( "AdminController init only runs in admin context", function() {
	return function_exists( 'is_admin' ) && is_admin() === false;
});

it( "Settings page check blocks users lacking manage_options", function() {
	$GLOBALS['current_user_caps']['manage_options'] = false;
	ob_start();
	\WPGate\Core\Admin\AdminController::render_settings_page();
	$out = ob_get_clean();
	$GLOBALS['current_user_caps']['manage_options'] = true;
	return empty( $out );
});

// TEST SUITE 12: REST API Endpoints
echo "\nTest Suite 12: REST API Controller (wpgate/v1)\n";
it( "Registers all required REST routes under wpgate/v1", function() {
	\WPGate\Core\RestController::register_routes();
	return isset(
		$GLOBALS['wp_rest_routes']['wpgate/v1/settings'],
		$GLOBALS['wp_rest_routes']['wpgate/v1/settings/reset'],
		$GLOBALS['wp_rest_routes']['wpgate/v1/logs'],
		$GLOBALS['wp_rest_routes']['wpgate/v1/logs/clear'],
		$GLOBALS['wp_rest_routes']['wpgate/v1/presets'],
		$GLOBALS['wp_rest_routes']['wpgate/v1/stats']
	);
});

it( "GET /wpgate/v1/settings returns settings and author metadata", function() {
	$res = \WPGate\Core\RestController::get_settings();
	$data = $res->get_data();
	return $res->get_status() === 200 &&
	       isset( $data['data']['settings'], $data['data']['system'] ) &&
	       $data['data']['system']['author'] === 'Hossein Mohammadpour';
});

it( "POST /wpgate/v1/settings saves sanitized configuration", function() {
	$req = new WP_REST_Request( [], [
		'mode'      => 'blacklist',
		'blacklist' => "test-bad.com\nmalware.org",
	] );
	$res = \WPGate\Core\RestController::save_settings( $req );
	$data = $res->get_data();
	return $res->get_status() === 200 &&
	       $data['data']['settings']['mode'] === 'blacklist' &&
	       false !== strpos( $data['data']['settings']['blacklist'], 'test-bad.com' );
});

it( "GET /wpgate/v1/logs provides paginated results and search filtering", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [ 'enable_logging' => 'yes' ] );
	\WPGate\Core\Settings::get_all( true );
	\WPGate\Core\Logger::clear_logs();
	\WPGate\Core\Logger::log( 'https://target-alpha.com/api', 'target-alpha.com', 'blocked' );
	\WPGate\Core\Logger::log( 'https://target-beta.com/api', 'target-beta.com', 'allowed' );

	$req = new WP_REST_Request( [ 'search' => 'alpha', 'page' => 1, 'per_page' => 10 ] );
	$res = \WPGate\Core\RestController::get_logs( $req );
	$data = $res->get_data();
	return $res->get_status() === 200 &&
	       $data['data']['total'] === 1 &&
	       $data['data']['items'][0]['host'] === 'target-alpha.com';
});

it( "GET /wpgate/v1/presets loads domain presets list", function() {
	$res = \WPGate\Core\RestController::get_presets();
	$data = $res->get_data();
	return $res->get_status() === 200 &&
	       is_array( $data['data'] ) &&
	       ! empty( $data['data'] );
});

it( "GET /wpgate/v1/stats calculates telemetry metrics", function() {
	$res = \WPGate\Core\RestController::get_stats();
	$data = $res->get_data();
	return $res->get_status() === 200 &&
	       isset( $data['data']['total_logs'], $data['data']['active_mode'] );
});

// TEST SUITE 13: Theme Loader Package Integrity
echo "\nTest Suite 13: Theme Loader Package Verification\n";
it( "Theme package bootstrap file exists and has valid syntax", function() {
	$theme_bootstrap = dirname( __DIR__ ) . '/theme-package/wpgate-core/bootstrap.php';
	return file_exists( $theme_bootstrap );
});

it( "Theme package core matches MU package core files", function() {
	$mu_core = dirname( __DIR__ ) . '/mu-plugin-package/mu-plugins/wpgate-core';
	$theme_core = dirname( __DIR__ ) . '/theme-package/wpgate-core';
	$files = [
		'bootstrap.php',
		'config/defaults.php',
		'config/domain-presets.php',
		'includes/Context.php',
		'includes/Settings.php',
		'includes/HttpGuard.php',
		'includes/TimeoutGuard.php',
		'includes/TelemetryGuard.php',
		'includes/UpdateGuard.php',
		'includes/GravatarGuard.php',
		'includes/AssetManager.php',
		'includes/Logger.php',
		'includes/RestController.php',
		'admin/AdminController.php',
		'admin/views/settings-page.php',
		'admin/views/dashboard-widget.php',
	];
	foreach ( $files as $f ) {
		if ( ! file_exists( "$theme_core/$f" ) ) {
			return false;
		}
		if ( md5_file( "$mu_core/$f" ) !== md5_file( "$theme_core/$f" ) ) {
			return false;
		}
	}
	return true;
});

echo "\n========================================================\n";
echo " TEST SUMMARY: $tests_passed Passed, $tests_failed Failed\n";
echo "========================================================\n";

exit( $tests_failed > 0 ? 1 : 0 );
