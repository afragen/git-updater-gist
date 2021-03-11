<?php
/**
 * Git Updater - Gitea
 *
 * @author    Andy Fragen
 * @license   MIT
 * @link      https://github.com/afragen/git-updater-gitea
 * @package   git-updater-gitea
 */

namespace Fragen\Git_Updater\Gist;

use Fragen\GitHub_Updater\API\Gist_API;

/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load textdomain.
\add_action(
	'init',
	function () {
		load_plugin_textdomain( 'git-updater-gitlab' );
	}
);

/**
 * Class Bootstrap
 */
class Bootstrap {
	/**
	 * Holds main plugin file.
	 *
	 * @var $file
	 */
	protected $file;

	/**
	 * Holds main plugin directory.
	 *
	 * @var $dir
	 */
	protected $dir;

	/**
	 * Constructor.
	 *
	 * @param  string $file Main plugin file.
	 * @return void
	 */
	public function __construct( $file ) {
		$this->file = $file;
		$this->dir  = dirname( $file );
	}

	/**
	 * Run the bootstrap.
	 *
	 * @return bool|void
	 */
	public function run() {
		// Exit if GitHub Updater not running.
		if ( ! class_exists( '\\Fragen\\GitHub_Updater\\Bootstrap' ) ) {
			return false;
		}

		new Gist_API();
	}

	/**
	 * Load hooks.
	 *
	 * @return void
	 */
	public function load_hooks() {
		\add_filter(
			'gu_get_repo_parts',
			function ( $repos, $type ) {
				$repos['types'] = array_merge( $repos['types'], [ 'Gist' => 'gist_' . $type ] );
				$repos['uris']  = array_merge( $repos['uris'], [ 'Gist' => 'https://gist.github.com/' ] );

				return $repos;
			},
			10,
			2
		);

		\add_filter(
			'gu_settings_auth_required',
			function ( $auth_required ) {
				return \array_merge(
					$auth_required,
					[
						'gist'         => false,
						'gist_private' => false,
					]
				);
			},
			10,
			1
		);

		\add_filter(
			'gu_api_repo_type_data',
			function ( $arr, $repo ) {
				if ( 'gist' === $repo->git ) {
					$arr['git']           = 'gist';
					$arr['base_uri']      = 'https://api.github.com';
					$arr['base_download'] = 'https://gist.github.com';
					$arr['base_raw']      = 'https://gist.githubusercontent.com';
				}

				return $arr;
			},
			10,
			2
		);

		\add_filter(
			'gu_api_url_type',
			function ( $type, $repo, $download_link, $endpoint ) {
				if ( 'gist' === $type['git'] ) {
					$method = ( new Gist_API() )->get_class_vars( 'API\Gist_API', 'method' );
					if ( in_array( $method, [ 'file', 'readme', 'changes' ], true ) ) {
						$type['base_uri'] = $type['base_raw'];
					}
				}

				return $type;
			},
			10,
			4
		);

		\add_filter(
			'gu_git_servers',
			function ( $git_servers ) {
				return array_merge( $git_servers, [ 'gist' => 'Gist' ] );
			},
			10,
			1
		);

		\add_filter(
			'gu_installed_apis',
			function ( $installed_apis ) {
				return array_merge( $installed_apis, [ 'gist_api' => true ] );
			},
			10,
			1
		);

		\add_filter(
			'gu_install_remote_install',
			function ( $install, $headers ) {
				if ( 'gist' === $install['github_updater_api'] ) {
					$install = ( new Gist_API() )->remote_install( $headers, $install );
				}

				return $install;
			},
			10,
			2
		);
	}
}
