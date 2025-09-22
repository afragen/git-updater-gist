<?php
/**
 * Git Updater - Gist
 *
 * @author   Andy Fragen
 * @license  MIT
 * @link     https://github.com/afragen/git-updater-gist
 * @package  git-updater-gist
 */

namespace Fragen\Git_Updater\API;

use Fragen\Singleton;
use stdClass;

/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Gist_API
 *
 * Get remote data from a Gist.
 *
 * @author  Andy Fragen
 */
class Gist_API extends API implements API_Interface {
	/**
	 * Constructor.
	 *
	 * @param stdClass $type plugin|theme.
	 */
	public function __construct( $type = null ) {
		parent::__construct();
		$this->type     = $type;
		$this->response = [];
		$this->settings_hook( $this );
		$this->add_settings_subtab();
		$this->add_install_fields( $this );
	}

	/**
	 * Read the remote file and parse headers.
	 *
	 * @param string $file Filename.
	 *
	 * @return bool
	 */
	public function get_remote_info( $file ) {
		return $this->get_remote_api_info( 'gist', "/:owner/:gist_id/raw/{$file}" );
	}

	/**
	 * Get remote info for tags.
	 *
	 * @return bool|void
	 */
	public function get_remote_tag() {
		// phpcs:ignore
		// return $this->get_remote_api_tag( '/repos/:owner/:repo/tags' );
	}

	/**
	 * Read the remote CHANGES.md file.
	 *
	 * @param null $changes The changelog filename - deprecated.
	 *
	 * @return bool
	 */
	public function get_remote_changes( $changes ) {
		return $this->get_remote_api_changes( 'gist', $changes, '/:owner/:gist_id/raw/:changelog' );
	}

	/**
	 * Read and parse remote readme.txt.
	 *
	 * @return bool|void
	 */
	public function get_remote_readme() {
		$this->get_remote_api_readme( 'gist', '/:owner/:gist_id/raw/:readme' );
	}

	/**
	 * Read the repository meta from API.
	 *
	 * @return bool
	 */
	public function get_repo_meta() {
		return $this->get_remote_api_repo_meta( 'gist', '/gists/:gist_id' );
	}

	/**
	 * Create array of branches and download links as array.
	 *
	 * @return bool|void
	 */
	public function get_remote_branches() {
		// phpcs:ignore
		// return $this->get_remote_api_branches( 'gist', '/repos/:owner/:repo/branches' );
	}

	/**
	 * Return list of repository assets.
	 *
	 * @return array
	 */
	public function get_repo_contents() {
		return $this->get_remote_api_contents( 'gist', '/gists/:gist_id' );
	}

	/**
	 * Return list of repository assets.
	 *
	 * @return array|void
	 */
	public function get_repo_assets() {
		// phpcs:ignore
		// return $this->get_remote_api_assets( 'gist', '/repos/:owner/:repo/contents/:path' );
	}

	/**
	 * Return the GitHub release asset URL.
	 *
	 * @return string|bool|void
	 */
	public function get_release_asset() {
		// phpcs:ignore
		// return $this->get_api_release_asset( 'gist', '/repos/:owner/:repo/releases/latest' );
		return [];
	}

	/**
	 * Construct $this->type->download_link using Repository Contents API.
	 *
	 * @url http://developer.github.com/v3/repos/contents/#get-archive-link
	 *
	 * @param boolean $branch_switch for direct branch changing.
	 *
	 * @return string $endpoint
	 */
	public function construct_download_link( $branch_switch = false ) {
		if ( ! isset( $this->response['meta'] ) || ! is_array( $this->response['meta'] ) ) {
			return;
		}

		self::$method       = 'download_link';
		$download_link_base = $this->get_api_url( '/:owner/:gist_id/archive/', true );
		$endpoint           = "{$this->response['meta']['current_hash']}.zip";
		$download_link      = $download_link_base . $endpoint;

		/**
		 * Filter download link so developers can point to specific ZipFile
		 * to use as a download link during a branch switch.
		 *
		 * @since 8.8.0
		 * @since 10.0.0
		 * @param string    $download_link Download URL.
		 * @param /stdClass $this->type    Repository object.
		 * @param string    $branch_switch Branch or tag for rollback or branch switching.
		 */
		return apply_filters( 'gu_post_construct_download_link', $download_link, $this->type, $branch_switch );
	}

	/**
	 * Create GitHub API endpoints.
	 *
	 * @param Gist_API|API $git      Git host specific API object.
	 * @param string       $endpoint Endpoint.
	 *
	 * @return string $endpoint
	 */
	public function add_endpoints( $git, $endpoint ) {
		return $endpoint;
	}

	/**
	 * Parse gist data.
	 *
	 * @param array $repo Repository meta array.
	 *
	 * @return array
	 */
	public function parse_gist_meta( $repo ) {
		$repo['gist_id'] = isset( $repo['gist_id'] ) ? $repo['gist_id'] : $repo['slug'];
		if ( isset( $repo['file'] ) ) {
			if ( ! strpos( $repo['file'], '/' ) ) {
				// For single file plugin.
				// Strip `.php` from the filename.
				$repo['slug'] = substr( $repo['file'], 0, -4 );
			} else {
				// After updated to containing folder.
				$repo['slug'] = dirname( $repo['file'] );
			}
		}

		return $repo;
	}

	/**
	 * Parse API response call and return only array of tag numbers.
	 *
	 * @param stdClass|array $response Response from API call.
	 *
	 * @return stdClass|array $arr Array of tag numbers, object is error.
	 */
	public function parse_tag_response( $response ) {
		if ( $this->validate_response( $response ) ) {
			return $response;
		}

		$arr = [];
		array_map(
			function ( $e ) use ( &$arr ) {
				$arr[] = $e->name;

				return $arr;
			},
			(array) $response
		);

		return $arr;
	}

	/**
	 * Parse API response and return array of meta variables.
	 *
	 * @param stdClass|array $response Response from API call.
	 *
	 * @return array $arr Array of meta variables.
	 */
	public function parse_meta_response( $response ) {
		if ( $this->validate_response( $response ) ) {
			return $response;
		}
		$arr      = [];
		$response = [ $response ];

		array_filter(
			$response,
			function ( $e ) use ( &$arr ) {
				$arr['private']      = isset( $e->public ) ? ! $e->public : false;
				$arr['last_updated'] = $e->updated_at ?? '';
				$arr['added']        = $e->created_at ?? '';
				$arr['watchers']     = $e->comments ?? 0;
				$arr['forks']        = isset( $e->forks ) && is_countable( $e->forks ) ? count( $e->forks ) : 0;
				$arr['open_issues']  = 0;
				$arr['current_hash'] = $e->history[0]->version ?? null;
			}
		);

		return $arr;
	}

	/**
	 * Parse API response and return array with changelog in base64.
	 *
	 * @param stdClass|array $response Response from API call.
	 *
	 * @return array $arr Array of changes in base64.
	 */
	public function parse_changelog_response( $response ) {
		if ( $this->validate_response( $response ) ) {
			return $response;
		}
		$arr      = [];
		$response = [ $response ];

		array_filter(
			$response,
			function ( $e ) use ( &$arr ) {
				$arr['changes'] = $e->content;
			}
		);

		return $arr;
	}

	/**
	 * Parse API response and return array of branch data.
	 *
	 * @param stdClass $response API response.
	 *
	 * @return array Array of branch data.
	 */
	public function parse_branch_response( $response ) {
		if ( $this->validate_response( $response ) ) {
			return $response;
		}
		$branches = [];

		return $branches;
	}

	/**
	 * Parse remote root files/dirs.
	 *
	 * TODO: check gists with multiple files.
	 *
	 * @param stdClass|array $response Response from API call.
	 *
	 * @return array
	 */
	protected function parse_contents_response( $response ) {
		$files = [];
		$dirs  = [];

		if ( property_exists( $response, 'files' ) ) {
			foreach ( $response->files as $content ) {
				$files[] = $content->filename;
			}
		}

		return [
			'files' => $files,
			'dirs'  => $dirs,
		];
	}

	/**
	 * Parse remote assets directory.
	 *
	 * @param stdClass|array $response Response from API call.
	 *
	 * @return stdClass|array
	 */
	protected function parse_asset_dir_response( $response ) {
		$assets = [];

		if ( isset( $response->message ) || is_wp_error( $response ) ) {
			return $response;
		}

		foreach ( $response as $asset ) {
			if ( 'file' === $asset->type ) {
				$assets[ $asset->name ] = $asset->download_url;
			}
		}

		if ( empty( $assets ) ) {
			$assets['message'] = 'No assets found';
			$assets            = (object) $assets;
		}

		return $assets;
	}

	/**
	 * Parse tags and create download links.
	 *
	 * @param stdClass|array $response  Response from API call.
	 * @param array          $repo_type Array of repo data.
	 *
	 * @return array
	 */
	protected function parse_tags( $response, $repo_type ) {
		return [];
	}

	/**
	 * Add settings for GitHub Personal Access Token.
	 *
	 * @param array $auth_required Array of authentication data.
	 *
	 * @return void
	 */
	public function add_settings( $auth_required ) {
		if ( $auth_required['gist'] ) {
			add_settings_section(
				'gist_settings',
				esc_html__( 'GitHub Gist Settings', 'git-updater-gist' ),
				null,
				'git_updater_gist_install_settings'
			);
		}

		/*
		 * Show section for private GitHub Gists.
		 */
		if ( $auth_required['gist_private'] ) {
			add_settings_section(
				'gist_id',
				esc_html__( 'Gist Private Settings', 'git-updater-gist' ),
				[ $this, 'print_section_gist_info' ],
				'git_updater_gist_install_settings'
			);
		}
	}

	/**
	 * Add values for individual repo add_setting_field().
	 *
	 * @return mixed
	 */
	public function add_repo_setting_field() {
		$setting_field['page']            = 'git_updater_gist_install_settings';
		$setting_field['section']         = 'gist_id';
		$setting_field['callback_method'] = [
			Singleton::get_instance( 'Settings', $this ),
			'token_callback_text',
		];

		return $setting_field;
	}

	/**
	 * Print the GitHub text.
	 */
	public function print_section_gist_info() {
		esc_html_e( 'Enter your GitHub Access Token. Leave empty for public repositories.', 'git-updater-gist' );
	}

	/**
	 * Add remote install settings fields.
	 *
	 * @param string $type plugin|theme.
	 */
	public function add_install_settings_fields( $type ) {
	}

	/**
	 * Add subtab to Settings page.
	 */
	private function add_settings_subtab() {
		add_filter(
			'gu_add_settings_subtabs',
			function ( $subtabs ) {
				return array_merge( $subtabs, [ 'gist' => esc_html__( 'Gist', 'git-updater-gist' ) ] );
			}
		);
	}

	/**
	 * Add remote install feature, create endpoint.
	 *
	 * @param array $headers Array of headers.
	 * @param array $install Array of install data.
	 *
	 * @return mixed
	 */
	public function remote_install( $headers, $install ) {
		$remote                              = $this->get_remote_gist_install( $headers );
		self::$method                        = 'download_link';
		$download_link_base                  = $this->get_api_url( '/:owner/:gist_id/archive/', true );
		$endpoint                            = "{$remote->meta['current_hash']}.zip";
		$install['download_link']            = $download_link_base . $endpoint;
		$install['git_updater_install_repo'] = property_exists( $remote, 'slug' ) ? $remote->slug : $install['git_updater_install_repo'];

		return $install;
	}

	/**
	 * Get and parse gist remote meta for Install.
	 *
	 * @param array $headers Array of headers.
	 *
	 * @return stdClass $remote
	 */
	private function get_remote_gist_install( $headers ) {
		$remote              = new stdClass();
		self::$method        = 'meta';
		$this->type          = new stdClass();
		$this->type->type    = 'gist';
		$this->type->git     = 'gist';
		$this->type->owner   = $headers['owner'];
		$this->type->slug    = $headers['repo'];
		$this->type->gist_id = $headers['repo'];
		$this->type->name    = $headers['repo'];
		$this->type->branch  = 'master';

		$response         = $this->api( '/gists/:gist_id' );
		$remote->meta     = $this->parse_meta_response( $response );
		$remote->is_theme = property_exists( $response->files, 'style.css' );
		$type             = $remote->is_theme ? 'theme' : 'plugin';
		foreach ( $response->files as $file ) {
			$file_headers = $this->get_file_headers( $file->content, $type );
			if ( ! empty( $file_headers ) && ! $remote->is_theme ) {
				$remote->slug = pathinfo( $file->filename )['filename'];
			}
		}

		return $remote;
	}
}
