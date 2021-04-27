<?php
/**
 * Git Updater - Gist
 *
 * @author    Andy Fragen
 * @license   MIT
 * @link      https://github.com/afragen/git-updater-gist
 * @package   git-updater-gist
 */

namespace Fragen\Git_Updater\Gist;

/**
 * Class Freemius_Addon
 *
 * Add Freemius add-on initialization.
 */
class Freemius_Addon {
	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( $this->gug_fs_is_parent_active_and_loaded() ) {
			// If parent already included, init add-on.
			$this->gug_fs_init();
		} elseif ( $this->gug_fs_is_parent_active() ) {
			// Init add-on only after the parent is loaded.
			add_action( 'gu_fs_loaded', [ $this, 'gug_fs_init' ] );
		} else {
			// Even though the parent is not activated, execute add-on for activation / uninstall hooks.
			$this->gug_fs_init();
		}
	}

	/**
	 * Initiate Freemius.
	 *
	 * @return \stdClass
	 */
	public function freemius() {
		if ( ! function_exists( 'gug_fs' ) ) {
			/**
			 * Load Freemius SDK.
			 *
			 * @return \stdClass
			 */
			function gug_fs() {
				global $gug_fs;

				if ( ! isset( $gug_fs ) ) {
					// Activate multisite network integration.
					if ( ! defined( 'WP_FS__PRODUCT_8236_MULTISITE' ) ) {
						define( 'WP_FS__PRODUCT_8236_MULTISITE', true );
					}

					// Include Freemius SDK from parent plugin vendor directory.
					if ( file_exists( dirname( __FILE__, 3 ) . '/git-updater/vendor/freemius/wordpress-sdk/start.php' ) ) {
						require_once dirname( __FILE__, 3 ) . '/git-updater/vendor/freemius/wordpress-sdk/start.php';
					}

					$gug_fs = fs_dynamic_init(
						[
							'id'               => '8236',
							'slug'             => 'git-updater-gist',
							'premium_slug'     => 'git-updater-gist-pre',
							'type'             => 'plugin',
							'public_key'       => 'pk_a84974535875515919cb887e60ecd',
							'is_premium'       => false,
							'has_paid_plans'   => false,
							'is_org_compliant' => false,
							'parent'           => [
								'id'         => '8195',
								'slug'       => 'git-updater',
								'public_key' => 'pk_2cf29ecaf78f5e10f5543c71f7f8b',
								'name'       => 'Git Updater',
							],
							'menu'             => [
								'first-path' => 'plugins.php',
								'support'    => false,
							],
						]
					);
				}

				return $gug_fs;
			}
		}
		gug_fs();
	}

	/**
	 * Check if parent is active and loaded.
	 *
	 * @return bool
	 */
	public function gug_fs_is_parent_active_and_loaded() {
		// Check if the parent's init SDK method exists.
		return function_exists( 'Fragen\Git_Updater\gu_fs' );
	}

	/**
	 * Check if parent is active.
	 *
	 * @return bool
	 */
	public function gug_fs_is_parent_active() {
		$active_plugins = get_option( 'active_plugins', [] );

		if ( is_multisite() ) {
			$network_active_plugins = get_site_option( 'active_sitewide_plugins', [] );
			$active_plugins         = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
		}

		foreach ( $active_plugins as $basename ) {
			if ( 0 === strpos( $basename, 'git-updater/' ) ||
				0 === strpos( $basename, 'git-updater/' )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Initialize Freemius.
	 *
	 * @return void
	 */
	public function gug_fs_init() {
		if ( $this->gug_fs_is_parent_active_and_loaded() ) {
			// Init Freemius, gug_fs().
			$this->freemius();

			// Signal that the add-on's SDK was initiated.
			do_action( 'gug_fs_loaded' );

			// Parent is active, add your init code here.

		} else {
			// Parent is inactive, add your error handling here.
		}
	}

}
