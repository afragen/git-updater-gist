<?php
/**
 * Git Updater - Gist.
 * Requires GitHub Updater plugin.
 *
 * @package git-updater-gist
 * @author  Andy Fragen
 * @link    https://github.com/afragen/git-updater-gist
 * @link    https://github.com/afragen/github-updater
 */

/**
 * Plugin Name:       GitHub Updater - Gist
 * Plugin URI:        https://github.com/afragen/git-updater-gist
 * Description:       Add GitHub Gist hosted repositories to the GitHub Updater plugin.
 * Version:           0.3.0
 * Author:            Andy Fragen
 * License:           MIT
 * Network:           true
 * Domain Path:       /languages
 * Text Domain:       git-updater-gist
 * GitHub Plugin URI: https://github.com/afragen/git-updater-gist
 * Primary Branch:    main
 * Requires at least: 5.1
 * Requires PHP:      5.6
 */

namespace Fragen\GitHub_Updater\Gist;

/*
 * Exit if called directly.
 * PHP version check and exit.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load Autoloader.
require_once __DIR__ . '/vendor/autoload.php';

( new Bootstrap( __FILE__ ) )->load_hooks();

add_action(
	'plugins_loaded',
	function () {
		( new Bootstrap( __FILE__ ) )->run();
	}
);
