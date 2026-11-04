<?php
/**
 * Plugin Name: WPGate Loader (Must-Use)
 * Description: Automated loader for WPGate Standalone Core - WordPress HTTP Control & Local Asset Manager.
 * Version: 0.1.0
 * Author: satinbest
 * Author URI: https://github.com/satinbest/WPGate
 *
 * @package WPGate\Loader
 */

defined( 'ABSPATH' ) || exit;

// Kill-switch: check if module is disabled via wp-config.php constant
if ( ( defined( 'WPGATE_CORE_DISABLED' ) && WPGATE_CORE_DISABLED ) ||
     ( defined( 'GHATEINO_CORE_DISABLED' ) && GHATEINO_CORE_DISABLED ) ) {
	return;
}

$wpgate_bootstrap = __DIR__ . '/wpgate-core/bootstrap.php';
if ( file_exists( $wpgate_bootstrap ) ) {
	require_once $wpgate_bootstrap;
}
