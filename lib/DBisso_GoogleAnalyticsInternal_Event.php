<?php
/**
 * A Google Analytics Measurement Protocol Event
 */
class DBisso_GoogleAnalyticsInternal_Event {
	private $event;
	private $action;
	private $label = false;
	private $value = false;
	private $category = 'WordPress';
	private $ga_endpoint = 'https://ssl.google-analytics.com/collect';

	/**
	 * Contstruct the evenet
	 * @param string  $action Event action
	 * @param string $label   Event label
	 * @param int $value      Event value
	 */
	public function __construct( $action, $label = false, $value = false ) {
		$this->action = $action;
		$this->label  = $label;
		$this->value  = $value;
	}

	/**
	 * Send the request to Google Analytics
	 *
	 * Checks if a UA string is available before sending
	 */
	public function send() {
		$uastring = $this->get_analytics_ua();

		if ( empty( $uastring ) ) {
			error_log( __( 'DBisso Google Analytics Internal: UA string not found', 'dbisso-google-analytics-internal' ) );
			return false;
		}

		$data = $this->get_post_data_event( $this->action, $this->label, $this->value );
		$data['tid'] = $uastring;

		return wp_remote_post(
			$this->ga_endpoint,
			array( 'body' => apply_filters( 'dbisso_gai_event_data', $data ) )
		);
	}

	/**
	 * Get POST data for triggering an event
	 * @param  string  $action The event action.
	 * @param  string $label   The event label.
	 * @param  int $value      Value to assign to the event.
	 * @return array           The data to send with the POST request
	 */
	private function get_post_data_event( $action, $label = false, $value = false ) {
		$data = $this->get_post_data();

		$data['t']  = 'event';
		$data['ea'] = $action;

		if ( $label ) {
			$data['el'] = $label;
		}

		if ( $value ) {
			$data['el'] = $label;
		}

		return $data;
	}

	/**
	 * Get the default POST data for a measurement protocol request.
	 * @return array The data for the POST request
	 */
	private function get_post_data() {
		$ua = $this->get_analytics_ua();

		$data = array(
			'v' => 1,
			'tid' => $ua,
			'cid' => get_current_user_id(),
			'ec' => $this->category,
		);

		return $data;
	}

	/**
	 * Get the Google Analytics UA string.
	 *
	 * Use string from Yoast's Google Analytics is installed. String
	 * can be overridden with a global constant.
	 *
	 * @return string|boolean The UA string to use or false if none found.
	 */
	private function get_analytics_ua() {
		$yoast_config = get_option( 'Yoast_Google_Analytics' );
		$ua           = false;

		if ( $yoast_config && ! empty( $yoast_config['uastring'] ) ) {
			$ua = $yoast_config['uastring'];
		}

		if ( defined( 'DBISSO_GA_UA' )  ) {
			$ua = DBISSO_GA_UA;
		}

		return $ua;
	}

	public function set_category( $category ) {
		$this->category = $category;
	}
}