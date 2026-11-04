<?php
/**
 * WPGate Standalone Core - Default Configuration
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

defined( 'ABSPATH' ) || exit;

return [
	'mode'                     => 'disabled',
	'whitelist'                => 'api.shokrino.com',
	'blacklist'                => '',
	'disable_telemetry'        => 'yes',
	'disable_updates'          => 'no', // Safe default: Security updates remain enabled unless changed by admin.
	'allow_whitelisted_updates' => 'yes',
	'disable_gravatar'         => 'yes',
	'gravatar_url'             => '', // Resolved dynamically to Context::get_asset_url('user.jpg').
	'enable_logging'           => 'no',
	'log_retention_days'       => '7',
	'log_max_entries'          => '300',
	'log_asset_events'         => 'no',
	'local_asset_rewrite'      => 'yes',
	'block_mixpanel'           => 'yes',
	'strict_asset_block'       => 'yes',
	'enable_front_vazirmatn'   => 'no',
	'enable_timeout_guard'     => 'no',
	'max_request_timeout'      => '3',
];
