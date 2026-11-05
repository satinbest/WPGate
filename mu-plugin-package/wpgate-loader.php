<?php
/**
 * Plugin Name: WPGate Loader (Must-Use)
 * Description: Automated loader for WPGate Standalone Core - Outbound HTTP Control & Local Asset Gateway.
 * Version: 0.1.3
 * Author: Hossein Mohammadpour
 * Author URI: https://hosseinmohammadpour.ir
 *
 * @package WPGate\Loader
 */

defined( 'ABSPATH' ) || exit;

// Kill-switch: check if module is disabled via wp-config.php constant
if ( ( defined( 'WPGATE_DISABLED' ) && WPGATE_DISABLED ) ||
     ( defined( 'WPGATE_CORE_DISABLED' ) && WPGATE_CORE_DISABLED ) ) {
	return;
}

$wpgate_bootstrap = __DIR__ . '/wpgate-core/bootstrap.php';
if ( file_exists( $wpgate_bootstrap ) ) {
	require_once $wpgate_bootstrap;
}
