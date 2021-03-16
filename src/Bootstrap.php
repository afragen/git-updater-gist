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
		add_filter( 'gu_fix_repo_slug', [ $this, 'fix_repo_slug' ], 10, 2 );
		add_filter( 'gu_get_repo_parts', [ $this, 'add_repo_parts' ], 10, 2 );
		add_filter( 'gu_settings_auth_required', [ $this, 'set_auth_required' ], 10, 1 );
		add_filter( 'gu_get_repo_api', [ $this, 'set_repo_api' ], 10, 3 );
		add_filter( 'gu_api_repo_type_data', [ $this, 'set_repo_type_data' ], 10, 2 );
		add_filter( 'gu_api_url_type', [ $this, 'set_api_url_data' ], 10, 4 );
		add_filter( 'gu_post_get_credentials', [ $this, 'set_credentials' ], 10, 2 );
		add_filter( 'gu_git_servers', [ $this, 'set_git_servers' ], 10, 1 );
		add_filter( 'gu_installed_apis', [ $this, 'set_installed_apis' ], 10, 1 );
		add_filter( 'gu_post_api_response_body', [ $this, 'convert_remote_body_response' ], 10, 2 );
		add_filter( 'gu_install_remote_install', [ $this, 'set_remote_install_data' ], 10, 2 );
		add_filter( 'gu_get_git_icon_data', [ $this, 'set_git_icon_data' ], 10, 2 );
	}

	/**
	 * Fix Gist repo slug from gist_id to slug.
	 *
	 * @param \stdClass $config Git Updater config object.
	 * @param \stdClass $plugin Repository object.
	 *
	 * @return \stdClass
	 */
	public function fix_repo_slug( $config, $plugin ) {
		if ( 'gist' === $plugin->git ) {
			$plugin                  = ( new Gist_API() )->parse_gist_meta( $plugin );
			$config[ $plugin->slug ] = $plugin;
			unset( $config[ $plugin->gist_id ] );
		}

		return $config;
	}

	/**
	 * Add API specific data to `get_repo_parts()`.
	 *
	 * @param array  $repos Array of repo data.
	 * @param string $type  plugin|theme.
	 *
	 * @return array $repos
	 */
	public function add_repo_parts( $repos, $type ) {
		$repos['types'] = array_merge( $repos['types'], [ 'Gist' => 'gist_' . $type ] );
		$repos['uris']  = array_merge( $repos['uris'], [ 'Gist' => 'https://gist.github.com/' ] );

		return $repos;
	}

	/**
	 * Add API specific auth required data.
	 *
	 * @param array $auth_required Array of authentication required data.
	 *
	 * @return array $auth_required
	 */
	public function set_auth_required( $auth_required ) {
		return array_merge(
			$auth_required,
			[
				'gist'         => false,
				'gist_private' => true,
			]
		);
	}

	/**
	 * Return git host API object.
	 *
	 * @param \stdClass $repo_api Git API object.
	 * @param string    $git      Name of git host.
	 * @param \stdClass $repo     Repository object.
	 *
	 * @return \stdClass
	 */
	public function set_repo_api( $repo_api, $git, $repo ) {
		if ( 'gist' === $git ) {
			$repo_api = new Gist_API( $repo );
		}

		return $repo_api;
	}

	/**
	 * Add API specific repo data.
	 *
	 * @param array     $arr  Array of repo API data.
	 * @param \stdClass $repo Repository object.
	 *
	 * @return array
	 */
	public function set_repo_type_data( $arr, $repo ) {
		if ( 'gist' === $repo->git ) {
			$arr['git']           = 'gist';
			$arr['base_uri']      = 'https://api.github.com';
			$arr['base_download'] = 'https://gist.github.com';
			$arr['base_raw']      = 'https://gist.githubusercontent.com';
		}

		return $arr;
	}

	/**
	 * Add API specific URL data.
	 *
	 * @param array     $type          Array of API type data.
	 * @param \stdClass $repo          Repository object.
	 * @param bool      $download_link Boolean indicating a download link.
	 * @param string    $endpoint      API URL endpoint.
	 *
	 * @return array $type
	 */
	public function set_api_url_data( $type, $repo, $download_link, $endpoint ) {
		if ( 'gist' === $type['git'] ) {
			$method = ( new Gist_API() )->get_class_vars( 'API\Gist_API', 'method' );
			if ( in_array( $method, [ 'file', 'readme', 'changes' ], true ) ) {
				$type['base_uri'] = $type['base_raw'];
			}
		}

		return $type;
	}

	/**
	 * Add credentials data for API.
	 *
	 * @param array $credentials Array of repository credentials data.
	 * @param array $args        Hook args.
	 *
	 * @return array
	 */
	public function set_credentials( $credentials, $args ) {
		if ( isset( $args['type'], $args['headers'], $args['options'], $args['slug'], $args['object'] ) ) {
			$type    = $args['type'];
			$headers = $args['headers'];
			$options = $args['options'];
			$slug    = $args['slug'];
			$object  = $args['object'];
		}
		if ( 'gist' === $type || $object instanceof Gist_API ) {
			$token = ! empty( $options['github_access_token'] ) ? $options['github_access_token'] : null;
			$token = ! empty( $options[ $slug ] ) ? $options[ $slug ] : $token;

			$credentials['type']       = 'github';
			$credentials['isset']      = true;
			$credentials['token']      = isset( $token ) ? $token : null;
			$credentials['enterprise'] = ! in_array( $headers['host'], [ 'api.github.com', 'gist.githubusercontent.com' ], true );
		}

		return $credentials;
	}

	/**
	 * Add API as git server.
	 *
	 * @param array $git_servers Array of git servers.
	 *
	 * @return array $git_servers
	 */
	public function set_git_servers( $git_servers ) {
		return array_merge( $git_servers, [ 'gist' => 'Gist' ] );
	}

	/**
	 * Add API data to $installed_apis.
	 *
	 * @param array $installed_apis Array of installed APIs.
	 *
	 * @return array
	 */
	public function set_installed_apis( $installed_apis ) {
		return array_merge( $installed_apis, [ 'gist_api' => true ] );
	}

	/**
	 * Convert HHTP remote body response to JSON.
	 *
	 * @param array     $response HTTP GET response.
	 * @param \stdClass $obj      API object.
	 *
	 * @return array
	 */
	public function convert_remote_body_response( $response, $obj ) {
		if ( $obj instanceof Gist_API ) {
			$body = wp_remote_retrieve_body( $response );
			if ( null === json_decode( $body ) ) {
				$response['body'] = json_encode( $body );
			}
		}

		return $response;
	}

	/**
	 * Set remote installation data for specific API.
	 *
	 * @param array $install Array of remote installation data.
	 * @param array $headers Array of repository header data.
	 *
	 * @return array
	 */
	public function set_remote_install_data( $install, $headers ) {
		if ( 'gist' === $install['github_updater_api'] ) {
			$install = ( new Gist_API() )->remote_install( $headers, $install );
		}

		return $install;
	}

	/**
	 * Set API icon data for display.
	 *
	 * @param array  $icon_data Header data for API.
	 * @param string $type_cap  Plugin|Theme.
	 *
	 * @return array
	 */
	public function set_git_icon_data( $icon_data, $type_cap ) {
		$icon_data['headers'] = array_merge(
			$icon_data['headers'],
			[ "Gist{$type_cap}URI" => "Gist {$type_cap} URI" ]
		);
		$icon_data['icons']   = array_merge(
			$icon_data['icons'],
			[ 'gist' => basename( dirname( __DIR__ ) ) . '/assets/github-logo.svg' ]
		);

		return $icon_data;
	}
}
