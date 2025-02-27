<?php
/**
 * Git Updater - Gist.
 * Requires Git Updater plugin.
 *
 * @package git-updater-gist
 * @author  Andy Fragen
 * @link    https://github.com/afragen/git-updater-gist
 * @link    https://github.com/afragen/github-updater
 */

/**
 * Plugin Name:       Git Updater - Gist
 * Plugin URI:        https://github.com/afragen/git-updater-gist
 * Description:       Add GitHub Gist hosted repositories to the Git Updater plugin.
 * Version:           2.4.4
 * Author:            Andy Fragen
 * License:           MIT
 * Network:           true
 * Domain Path:       /languages
 * Text Domain:       git-updater-gist
 * GitHub Plugin URI: https://github.com/afragen/git-updater-gist
 * GitHub Languages:  https://github.com/afragen/git-updater-gist-translations
 * Primary Branch:    main
 * Requires at least: 5.9
 * Requires PHP:      7.2
 */

namespace Fragen\Git_Updater\Gist;

/*
 * Exit if called directly.
 * PHP version check and exit.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load Autoloader.
require_once __DIR__ . '/vendor/autoload.php';

( new Bootstrap() )->load_hooks();

add_action(
	'init',
	function () {
		( new Bootstrap() )->run();
	}
);
