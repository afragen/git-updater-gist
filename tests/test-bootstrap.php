<?php

/**
 * Class BootstrapTest
 *
 * @package Git_Updater_Gist
 */

use Fragen\Git_Updater\Gist\Bootstrap;

/**
 * Sample test case.
 */
class BootstrapTest extends WP_UnitTestCase {
	/**
	 * A single example test.
	 */
	public function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue(true);
	}

	public function test_add_repo_parts() {
		$empty     = ['types' => [], 'uris' => []];
		$expected  = [
			'types' => ['Gist' => 'gist_plugin'],
			'uris'  => ['Gist' => 'https://gist.github.com/'],
		];
		$acutal = (new Bootstrap())->add_repo_parts($empty, 'plugin');

		$this->assertEqualSetsWithIndex($expected, $acutal);
	}

	public function test_set_auth_required() {
		$expected = [
			'gist'         => false,
			'gist_private' => true,
		];
		$acutal = (new Bootstrap())->set_auth_required([]);
		$this->assertEqualSetsWithIndex($expected, $acutal);
	}

	public function test_set_repo_type_data() {
		$org             = new \stdClass();
		$org->git        = 'gist';
		$expected_org    = [
			'git'           => 'gist',
			'base_uri'      => 'https://api.github.com',
			'base_download' => 'https://gist.github.com',
			'base_raw'      => 'https://gist.githubusercontent.com',
		];

		$actual_org = (new Bootstrap())->set_repo_type_data([], $org);
		$this->assertEqualSetsWithIndex($expected_org, $actual_org);
	}

	public function test_get_icon_data() {
		$icon_data           = ['headers' => [], 'icons'=>[]];
		$expected['headers'] = ['GistPluginURI' => 'Gist Plugin URI'];
		$expected['icons']   = ['gist' => 'git-updater-gist/assets/github-logo.svg' ];

		$actual = (new Bootstrap())->set_git_icon_data($icon_data, 'Plugin');

		$this->assertEqualSetsWithIndex($expected, $actual);
	}



	/**
	 * Gist authenticates as GitHub, so its hosts join the github set.
	 */
	public function test_set_credential_hosts_adds_gist_hosts_to_github_set() {
		$hosts = (new Bootstrap())->set_credential_hosts([]);

		$this->assertContains('gist.github.com', $hosts['github']);
		$this->assertContains('gist.githubusercontent.com', $hosts['github']);
	}

	public function test_load_hooks_registers_credential_hosts_filter() {
		$bootstrap = new Bootstrap();
		$bootstrap->load_hooks();

		$this->assertNotFalse(has_filter('gu_credential_hosts', [$bootstrap, 'set_credential_hosts']));
	}

}
