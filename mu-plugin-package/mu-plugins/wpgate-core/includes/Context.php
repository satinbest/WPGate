<?php
/**
 * WPGate Standalone Core - Runtime Context
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

namespace WPGate\Core;

defined( 'ABSPATH' ) || exit;

class Context {

	/**
	 * Module base directory path (normalized with forward slashes).
	 *
	 * @var string
	 */
	private static $base_dir = '';

	/**
	 * Module base URL.
	 *
	 * @var string|null
	 */
	private static $base_url = null;

	/**
	 * Detected loading method ('mu-plugin', 'theme', 'standalone').
	 *
	 * @var string|null
	 */
	private static $loading_method = null;

	/**
	 * Initialize context.
	 *
	 * @param string $dir
	 */
	public static function init( string $dir ): void {
		self::$base_dir       = rtrim( wp_normalize_path( $dir ), '/' );
		self::$base_url       = null;
		self::$loading_method = null;
	}

	/**
	 * Get normalized base directory.
	 *
	 * @return string
	 */
	public static function get_base_dir(): string {
		if ( empty( self::$base_dir ) ) {
			self::$base_dir = rtrim( wp_normalize_path( dirname( __DIR__ ) ), '/' );
		}
		return self::$base_dir;
	}

	/**
	 * Detect how the module is being loaded.
	 *
	 * @return string
	 */
	public static function get_loading_method(): string {
		if ( null !== self::$loading_method ) {
			return self::$loading_method;
		}

		$dir = self::get_base_dir();

		// Check MU Plugin path
		if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
			$mu_dir = rtrim( wp_normalize_path( WPMU_PLUGIN_DIR ), '/' );
			if ( 0 === stripos( $dir, $mu_dir ) ) {
				self::$loading_method = 'mu-plugin';
				return self::$loading_method;
			}
		}

		if ( defined( 'WP_CONTENT_DIR' ) ) {
			$fallback_mu = rtrim( wp_normalize_path( WP_CONTENT_DIR . '/mu-plugins' ), '/' );
			if ( 0 === stripos( $dir, $fallback_mu ) ) {
				self::$loading_method = 'mu-plugin';
				return self::$loading_method;
			}
		}

		// Check Theme path
		if ( function_exists( 'get_theme_root' ) ) {
			$theme_root = rtrim( wp_normalize_path( get_theme_root() ), '/' );
			if ( 0 === stripos( $dir, $theme_root ) ) {
				self::$loading_method = 'theme';
				return self::$loading_method;
			}
		}

		if ( defined( 'WP_CONTENT_DIR' ) ) {
			$fallback_theme = rtrim( wp_normalize_path( WP_CONTENT_DIR . '/themes' ), '/' );
			if ( 0 === stripos( $dir, $fallback_theme ) ) {
				self::$loading_method = 'theme';
				return self::$loading_method;
			}
		}

		self::$loading_method = 'standalone';
		return self::$loading_method;
	}

	/**
	 * Get module base URL.
	 *
	 * @return string
	 */
	public static function get_base_url(): string {
		if ( null !== self::$base_url ) {
			return self::$base_url;
		}

		$method = self::get_loading_method();
		$dir    = self::get_base_dir();

		if ( 'mu-plugin' === $method ) {
			$content_dir = defined( 'WP_CONTENT_DIR' ) ? rtrim( wp_normalize_path( WP_CONTENT_DIR ), '/' ) : '';
			if ( '' !== $content_dir && 0 === stripos( $dir, $content_dir ) ) {
				$rel = ltrim( substr( $dir, strlen( $content_dir ) ), '/' );
				self::$base_url = rtrim( content_url( $rel ), '/' );
				return self::$base_url;
			}
			self::$base_url = rtrim( content_url( 'mu-plugins/wpgate-core' ), '/' );
			return self::$base_url;
		}

		if ( 'theme' === $method ) {
			if ( function_exists( 'get_stylesheet_directory' ) && function_exists( 'get_stylesheet_directory_uri' ) ) {
				$child_dir = rtrim( wp_normalize_path( get_stylesheet_directory() ), '/' );
				if ( 0 === stripos( $dir, $child_dir ) ) {
					$rel = ltrim( substr( $dir, strlen( $child_dir ) ), '/' );
					self::$base_url = rtrim( get_stylesheet_directory_uri() . '/' . $rel, '/' );
					return self::$base_url;
				}
			}

			if ( function_exists( 'get_template_directory' ) && function_exists( 'get_template_directory_uri' ) ) {
				$parent_dir = rtrim( wp_normalize_path( get_template_directory() ), '/' );
				if ( 0 === stripos( $dir, $parent_dir ) ) {
					$rel = ltrim( substr( $dir, strlen( $parent_dir ) ), '/' );
					self::$base_url = rtrim( get_template_directory_uri() . '/' . $rel, '/' );
					return self::$base_url;
				}
			}
		}

		// Fallback: Calculate relative to site content URL
		if ( defined( 'WP_CONTENT_DIR' ) && function_exists( 'content_url' ) ) {
			$content_dir = rtrim( wp_normalize_path( WP_CONTENT_DIR ), '/' );
			if ( 0 === strpos( $dir, $content_dir ) ) {
				$rel = ltrim( substr( $dir, strlen( $content_dir ) ), '/' );
				self::$base_url = rtrim( content_url( $rel ), '/' );
				return self::$base_url;
			}
		}

		if ( function_exists( 'site_url' ) ) {
			self::$base_url = rtrim( site_url( '/wp-content/mu-plugins/wpgate-core' ), '/' );
			return self::$base_url;
		}

		self::$base_url = '/wp-content/mu-plugins/wpgate-core';
		return self::$base_url;
	}

	/**
	 * Get absolute path to an asset file.
	 *
	 * @param string $relative_path
	 * @return string
	 */
	public static function get_asset_path( string $relative_path ): string {
		return self::get_base_dir() . '/assets/' . ltrim( $relative_path, '/' );
	}

	/**
	 * Get URL to an asset file.
	 *
	 * @param string $relative_path
	 * @return string
	 */
	public static function get_asset_url( string $relative_path ): string {
		return self::get_base_url() . '/assets/' . ltrim( $relative_path, '/' );
	}
}
