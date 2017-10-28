<?php
/**
 * Class SampleTest
 *
 * @package Badgeup
 */

/**
 * Sample test case.
 */
class BadgeUpWordPressPluginTests extends WP_UnitTestCase {

	/**
	 * Able to set admin notifications
	 */
	function test_notifications() {
		$this->assertTrue( BadgeUp::notify( "Hi Admin! What's going on?" ) );
	}

	/**
	 * Setup Badgeup\Client class, Get user data available.
	 */
	public function test_api_init() {
		global $current_user;

		// Setup logged in user.
		$current_user = new WP_User( 1 );

		// Set up API with key (it's not there in test environment)
		$api = badgeup_api()->setup_api(
			'eyJhY2NvdW50SWQiOiJ0aGViZXN0IiwiYXBwbGljYXRpb25JZCI6IjEzMzciLCJrZXkiOiJpY2VjcmVhbWFuZGNvb2tpZXN5dW0ifQ',
			1 );

		$this->assertInstanceOf( '\BadgeUp\Client', $api );
	}

	/**
	 * badgeup_api() returns instance of BadgeUp_API
	 */
	function test_badgeup_api() {
		$this->assertInstanceOf( 'BadgeUp_API', badgeup_api() );
	}

	/**
	 * badgeup_api() create event returns truish value
	 */
	function test_create_event_not_false() {
		// create_event is truish

		$this->assertTrue( !! badgeup_api()->create_event( 'wp:unit-test:create-event' ) );
	}

	/**
	 * badgeup_api() create event returns promise (is thenable)
	 * @depends test_create_event_not_false
	 */
	function test_create_event() {
		// create_event is thenable
		$this->assertTrue( method_exists( badgeup_api()->create_event( 'wp:unit-test:create-event' ), 'then' ) );
	}

	/**
	 * badgeup_api() create event supports wait
	 * @depends test_create_event
	 */
	function test_create_event_wait() {

		try {

			$response = badgeup_api()->create_event( 'wp:unit-test:create-event:wait' )->wait();

		} catch ( Exception $e ) {

			echo "\n\n" . self::class . "::test_create_event_wait() \n";
			echo 'Testing if API responded with 401, which means request went through successfully, ';
			echo 'but key didn\'t have permissions to access the resource';

			$this->assertTrue( '401' == $e->getCode() );

		}

		if ( !empty( $response ) ) {

			$this->assertObjectHasAttribute( 'event', $response );

		}
	}

	/**
	 * Able to get earned achievements
	 */
	function testEarnedAchievements() {

		// getEarnedAchievements via api is thenable
		$this->assertTrue(
			method_exists(
				badgeup_api()->api( 'getEarnedAchievements', [ 1 ], false ),
				'then'
			)
		);

		$response = badgeup_api()->api( 'getEarnedAchievements', [ 1 ] );
		if ( $response instanceof Exception ) {

			echo "\n\n" . self::class . "::testEarnedAchievements() \n";
			echo 'Testing if API responded with 401, which means request went through successfully, ';
			echo 'but key didn\'t have permissions to access the resource';

			$this->assertTrue( '401' == $response->getCode() );

		} else {

			$this->assertTrue( is_array( $response ) );

		}
	}

	/**
	 * Able to get achievements created by user in BadgeUp dashboard.
	 */
	function test_get_achievements() {
		$this->assertTrue( is_array( badgeup_api()->get_achievements() ) );
	}

	public function return_key() {
		return $this->key;
	}
}