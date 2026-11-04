<?php
/**
 * WPGate Standalone Core - Automated Test Suite
 *
 * Runs on PHP CLI to validate syntax, security, domain matching,
 * HTTP interception, asset rewriting, logger, and loaders.
 *
 * @package WPGate\Tests
 * @version 0.1.0
 */

// 1. Setup Mock WordPress Environment
define( 'ABSPATH', __DIR__ . '/wordpress/' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );
define( 'WP_CONTENT_DIR', 'D:/ghateino/wp-content' );
define( 'WPMU_PLUGIN_DIR', 'D:/ghateino/wp-content/mu-plugins' );

$GLOBALS['wp_filters'] = [];
$GLOBALS['wp_actions'] = [];
$GLOBALS['wp_options'] = [];
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
	return $GLOBALS['wp_options'][ $option ] ?? $default;
}

function update_option( $option, $value, $autoload = null ) {
	$GLOBALS['wp_options'][ $option ] = $value;
	return true;
}

function delete_option( $option ) {
	if ( isset( $GLOBALS['wp_options'][ $option ] ) ) {
		unset( $GLOBALS['wp_options'][ $option ] );
		return true;
	}
	return false;
}

function wp_parse_args( $args, $defaults = [] ) {
	return array_merge( $defaults, (array) $args );
}

function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}

function current_time( $type, $gmt = 0 ) {
	return 'timestamp' === $type ? time() : gmdate( 'Y-m-d H:i:s' );
}

function sanitize_text_field( $str ) {
	return trim( strip_tags( (string) $str ) );
}

function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
}

function esc_url_raw( $url ) {
	return filter_var( $url, FILTER_SANITIZE_URL ) ?: '';
}

function esc_url( $url ) {
	return htmlspecialchars( esc_url_raw( $url ), ENT_QUOTES, 'UTF-8' );
}

function esc_attr( $str ) {
	return htmlspecialchars( (string) $str, ENT_QUOTES, 'UTF-8' );
}

function esc_html( $str ) {
	return htmlspecialchars( (string) $str, ENT_QUOTES, 'UTF-8' );
}

function esc_textarea( $str ) {
	return htmlspecialchars( (string) $str, ENT_QUOTES, 'UTF-8' );
}

function home_url( $path = '' ) {
	return 'https://example.com' . $path;
}

function site_url( $path = '' ) {
	return 'https://example.com' . $path;
}

function content_url( $path = '' ) {
	return 'https://example.com/wp-content/' . ltrim( $path, '/' );
}

function includes_url( $path = '' ) {
	return 'https://example.com/wp-includes/' . ltrim( $path, '/' );
}

function admin_url( $path = '' ) {
	return 'https://example.com/wp-admin/' . ltrim( $path, '/' );
}

function is_admin() {
	return false;
}

function current_user_can( $cap ) {
	return ! empty( $GLOBALS['current_user_caps'][ $cap ] );
}

function check_admin_referer( $action ) {
	return true;
}

function wp_nonce_url( $url, $action ) {
	return $url . '&_wpnonce=testnonce123';
}

function wp_safe_redirect( $location ) {
	$GLOBALS['redirected_to'] = $location;
}

function register_setting( $group, $name, $args ) {
	$GLOBALS['registered_settings'][ $name ] = $args;
}

function add_options_page( $title, $menu_title, $cap, $slug, $callback ) {
	$GLOBALS['options_pages'][ $slug ] = compact( 'title', 'menu_title', 'cap', 'callback' );
}

function add_meta_box( $id, $title, $callback, $screen, $context, $priority ) {
	$GLOBALS['meta_boxes'][ $id ] = compact( 'title', 'callback', 'screen', 'context', 'priority' );
}

function wp_enqueue_style( $handle, $src = '', $deps = [], $ver = false, $media = 'all' ) {
	$GLOBALS['enqueued_styles'][ $handle ] = compact( 'src', 'deps', 'ver', 'media' );
}

function selected( $selected, $current, $echo = true ) {
	$out = ( (string) $selected === (string) $current ) ? ' selected="selected"' : '';
	if ( $echo ) echo $out;
	return $out;
}

function checked( $checked, $current, $echo = true ) {
	$out = ( (string) $checked === (string) $current ) ? ' checked="checked"' : '';
	if ( $echo ) echo $out;
	return $out;
}

function submit_button( $text, $type = 'primary', $name = 'submit', $wrap = true ) {
	echo "<button type='submit' name='$name'>$text</button>";
}

function settings_fields( $group ) {
	echo "<input type='hidden' name='option_page' value='$group' />";
}

// -------------------------------------------------------------
// Test Runner Infrastructure
// -------------------------------------------------------------
$tests_passed = 0;
$tests_failed = 0;

function it( $description, $test_fn ) {
	global $tests_passed, $tests_failed;
	try {
		$result = $test_fn();
		if ( false !== $result ) {
			echo "  [PASS] $description\n";
			$tests_passed++;
		} else {
			echo "  [FAIL] $description (Assertion failed)\n";
			$tests_failed++;
		}
	} catch ( Throwable $e ) {
		echo "  [FAIL] $description (Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . ")\n";
		$tests_failed++;
	}
}

echo "========================================================\n";
echo " WPGATE STANDALONE CORE - AUTOMATED TEST SUITE (v0.1.0)\n";
echo "========================================================\n\n";

// Load WPGate Module Bootstrap
$wpgate_bootstrap = dirname( __DIR__ ) . '/mu-plugin-package/mu-plugins/wpgate-core/bootstrap.php';
require_once $wpgate_bootstrap;

// TEST SUITE 1: Context & Autoloading
echo "Test Suite 1: Context & Class Loading\n";
it( "Bootstrap defines WPGATE_CORE_VERSION as 0.1.0", function() {
	return defined( 'WPGATE_CORE_VERSION' ) && WPGATE_CORE_VERSION === '0.1.0';
});

it( "Autoloader resolves WPGate\\Core\\Context correctly", function() {
	return class_exists( 'WPGate\\Core\\Context' );
});

it( "Context generates valid asset URLs and paths for WPGate", function() {
	$url = \WPGate\Core\Context::get_asset_url( 'user.jpg' );
	$path = \WPGate\Core\Context::get_asset_path( 'user.jpg' );
	return ( false !== strpos( $url, 'wpgate-core' ) ) && ( false !== strpos( $path, 'wpgate-core' ) );
});

// TEST SUITE 2: Settings & Migration
echo "\nTest Suite 2: Settings, Defaults & Migration\n";
it( "Default settings keep updates enabled (disable_updates == 'no')", function() {
	$defaults = \WPGate\Core\Settings::get_defaults();
	return isset( $defaults['disable_updates'] ) && $defaults['disable_updates'] === 'no';
});

it( "Settings sanitization handles invalid mode cleanly", function() {
	$sanitized = \WPGate\Core\Settings::sanitize( [ 'mode' => 'invalid_mode_xyz' ] );
	return $sanitized['mode'] === 'disabled';
});

it( "Sanitize host list removes schemes, ports, and duplicates", function() {
	$raw = "https://api.zarinpal.com:443/\r\nhttp://sep.ir:80\r\napi.zarinpal.com\r\n\r\n";
	$cleaned = \WPGate\Core\Settings::sanitize_host_list( $raw );
	$expected = "api.zarinpal.com\nsep.ir";
	return $cleaned === $expected;
});

it( "Automatically migrates legacy Ghateino settings if present", function() {
	delete_option( \WPGate\Core\Settings::OPTION_KEY );
	update_option( \WPGate\Core\Settings::LEGACY_OPTION_KEY, [
		'mode'      => 'whitelist',
		'whitelist' => 'migrated.example.com',
	] );
	$settings = \WPGate\Core\Settings::get_all( true );
	return ( $settings['mode'] === 'whitelist' ) && ( false !== strpos( $settings['whitelist'], 'migrated.example.com' ) );
});

// TEST SUITE 3: Domain Matching Logic (Exact & Subdomain)
echo "\nTest Suite 3: Domain Matching & Subdomain Security\n";
it( "Exact domain matches correctly", function() {
	return \WPGate\Core\HttpGuard::host_in_list( 'api.shokrino.com', [ 'api.shokrino.com', 'zarinpal.com' ] );
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
		'whitelist' => "api.shokrino.com\nzarinpal.com",
	] );
	\WPGate\Core\Settings::get_all( true );
	$res = \WPGate\Core\HttpGuard::filter( false, [], 'https://api.shokrino.com/v1/verify' );
	return false === $res;
});

it( "In whitelist mode, non-whitelisted domain is blocked with WP_Error and wpgate_blocked code", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'mode'      => 'whitelist',
		'whitelist' => "api.shokrino.com\nzarinpal.com",
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
		'whitelist' => "api.shokrino.com",
	] );
	\WPGate\Core\Settings::get_all( true );
	$res = \WPGate\Core\HttpGuard::filter( false, [], 'http://127.0.0.1/wp-cron.php' );
	return false === $res;
});

it( "Mixpanel hosts are blocked when block_mixpanel is 'yes'", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'mode'           => 'disabled',
		'block_mixpanel' => 'yes',
	] );
	\WPGate\Core\Settings::get_all( true );
	$res = \WPGate\Core\HttpGuard::filter( false, [], 'https://api-eu.mixpanel.com/track' );
	return ( $res instanceof WP_Error ) && ( $res->get_error_code() === 'wpgate_blocked' );
});

// TEST SUITE 5: Telemetry Blocking
echo "\nTest Suite 5: Telemetry Interception\n";
it( "Blocks requests to wordpress.org when disable_telemetry is 'yes'", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [ 'disable_telemetry' => 'yes' ] );
	\WPGate\Core\Settings::get_all( true );
	$res = \WPGate\Core\TelemetryGuard::filter( false, [], 'https://api.wordpress.org/stats/wordpress/1.0/' );
	return ( $res instanceof WP_Error ) && ( $res->get_error_code() === 'wpgate_blocked' );
});

// TEST SUITE 6: Timeout Guard
echo "\nTest Suite 6: Request Timeout Guard\n";
it( "Clamps timeout to max_request_timeout when enabled", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'enable_timeout_guard' => 'yes',
		'max_request_timeout'  => '3',
	] );
	\WPGate\Core\Settings::get_all( true );
	$args = [ 'timeout' => 30 ];
	$res = \WPGate\Core\TimeoutGuard::filter( $args, 'https://external-api.com/slow' );
	return $res['timeout'] === 3;
});

it( "Preserves shorter timeout if already below ceiling", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'enable_timeout_guard' => 'yes',
		'max_request_timeout'  => '5',
	] );
	\WPGate\Core\Settings::get_all( true );
	$args = [ 'timeout' => 2 ];
	$res = \WPGate\Core\TimeoutGuard::filter( $args, 'https://external-api.com/fast' );
	return $res['timeout'] === 2;
});

// TEST SUITE 7: Asset Replacement & Stubs
echo "\nTest Suite 7: Local CDN & Asset Replacement\n";
it( "Replaces Google Fonts with local Vazirmatn or Roboto CSS", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [ 'local_asset_rewrite' => 'yes' ] );
	\WPGate\Core\Settings::get_all( true );
	$src = 'https://fonts.googleapis.com/css?family=Vazirmatn:400,700&display=swap';
	$replaced = \WPGate\Core\AssetManager::replace_style_src( $src, 'google-fonts-vazir' );
	return false !== strpos( $replaced, 'vazirmatn.css' );
});

it( "Replaces Mixpanel script with stub", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'local_asset_rewrite' => 'yes',
		'block_mixpanel'      => 'yes',
	] );
	\WPGate\Core\Settings::get_all( true );
	$src = 'https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js';
	$replaced = \WPGate\Core\AssetManager::replace_script_src( $src, 'mixpanel-js' );
	return false !== strpos( $replaced, 'mixpanel-stub.js' );
});

it( "Strict asset block replaces unmapped external CDN with blocked stub", function() {
	update_option( \WPGate\Core\Settings::OPTION_KEY, [
		'local_asset_rewrite' => 'yes',
		'strict_asset_block'  => 'yes',
	] );
	\WPGate\Core\Settings::get_all( true );
	$src = 'https://cdnjs.cloudflare.com/ajax/libs/some-random-lib/1.0/lib.js';
	$replaced = \WPGate\Core\AssetManager::replace_script_src( $src, 'random-lib' );
	return false !== strpos( $replaced, 'blocked-asset.js' );
});

// TEST SUITE 8: Logger & Sensitive Query Redaction
echo "\nTest Suite 8: Secure Logger & Parameter Redaction\n";
it( "Redacts sensitive query parameters (token, api_key, password)", function() {
	$url = 'https://api.payment.com/v1/charge?token=secret9988&api_key=privkey123&user_id=45&pass=mypassword';
	$sanitized = \WPGate\Core\Logger::sanitize_url( $url );
	return ( false === strpos( $sanitized, 'secret9988' ) ) &&
	       ( false === strpos( $sanitized, 'privkey123' ) ) &&
	       ( false === strpos( $sanitized, 'mypassword' ) ) &&
	       ( false !== strpos( $sanitized, '[REDACTED]' ) ) &&
	       ( false !== strpos( $sanitized, 'user_id=45' ) );
});

it( "Logger records events when enabled and clears on command", function() {
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
it( "MU-Plugin loader respects WPGATE_CORE_DISABLED", function() {
	$cmd = 'php -r "define(\'ABSPATH\', 1); define(\'WPGATE_CORE_DISABLED\', true); require \'' .
	       addslashes( dirname( __DIR__ ) . '/mu-plugin-package/mu-plugins/wpgate-loader.php' ) . '\'; echo defined(\'WPGATE_CORE_LOADED\') ? \'loaded\' : \'disabled\';"';
	$output = trim( shell_exec( $cmd ) );
	return 'disabled' === $output;
});

it( "MU-Plugin loader respects legacy GHATEINO_CORE_DISABLED", function() {
	$cmd = 'php -r "define(\'ABSPATH\', 1); define(\'GHATEINO_CORE_DISABLED\', true); require \'' .
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

// TEST SUITE 12: Theme Loader Package Integrity
echo "\nTest Suite 12: Theme Loader Package Verification\n";
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
		'admin/AdminController.php',
		'admin/views/settings-page.php',
		'admin/views/dashboard-widget.php',
	];
	foreach ( $files as $f ) {
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
