<?php
require_once __DIR__ . '/../TestCase.php';
require_once '../lib/DBisso_GoogleAnalyticsInternal_Event.php';

class DBisso_GoogleAnalyticsInternal_Event_Tests extends DBisso_GoogleAnalyticsInternal_TestCase {
	/**
	 * @var DBisso_GoogleAnalyticsInternal_Event
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	// protected function setUp() {}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	// protected function tearDown() {
	// }

	/**
	 * @covers DBisso_GoogleAnalyticsInternal_Event::send
	 */
	public function testSend() {
		$ua_string      = $this->maybeDefineUAString();
		$wp_user_id     = get_current_user_id();
		$event_category = 'WordPress_Test';
		$event_label    = 'A label';
		$event_action   = 'An action';
		$event_value    = 1;

		$event = new DBisso_GoogleAnalyticsInternal_Event( $event_action, $event_label, $event_value, $event_category );

		// Spy on HTTP API
		$this->http_spy();

		// Send the event
		$event->send();

		// Get the response
		$request_body = $this->http_spy_get_request_body();

		$expected_request_body = array(
			'v'   => 1,
			'tid' => $ua_string,
			'cid' => $wp_user_id,
			'ec'  => $event_category,
			't'   => 'event',
			'ea'  => $event_action,
			'el'  => $event_label,
		);

		$this->assertEquals( $expected_request_body, $request_body );
	}
}
