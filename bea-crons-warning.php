<?php
/*
  Plugin Name: BEA Crons Warning
  Plugin URI: http://www.beapi.fr
  Description: Warn administrator when the defined maximum quantity of crons is detected
  Author: BeAPI
  Author URI: http://www.beapi.fr
  Version: 0.1
 */

class BEA_Crons_Warning {

	/**
	 * Maximum of allowed crons
	 */
	const BEA_CRONS_WARNING_MAX = 200;

	/**
	 * BEA_Crons_Warning constructor.
	 */
	public function __construct() {
		add_filter( 'admin_notices', [ $this, 'admin_notices' ] );
	}

	/**
	 * Display an admin warning when maximum of cron events is reached
	 */
	public function admin_notices() {
		if ( ! is_admin() || 'dashboard' !== get_current_screen()->base ) {
			return;
		}

		if ( false === ( $crons = get_transient( 'crons_list' ) ) ) {
			$crons = $this->get_crons_list();
			set_transient( 'crons_list', $crons, HOUR_IN_SECONDS );
		}

		if ( false === $crons ) {
			return;
		}

		if ( $crons <= self::BEA_CRONS_WARNING_MAX ) {
			return;
		}
		?>
        <div class="notice notice-error">
            <p><?php echo esc_html( sprintf( _n( 'There is %1$d currently activated cron. Please consider about cleaning your unnecessary cron job.', ' There are %1$d currently activated crons. Please consider about cleaning your unnecessary cron jobs.', $crons, 'bea_crons_warning' ), $crons ) ); ?></p>
        </div>
		<?php
	}

	/**
	 * Get an array of current cron event names
	 *
	 * @return array|bool
	 */
	public function get_crons_list() {
		$crons = _get_cron_array();

		$result = array_reduce( $crons, [ $this, 'callback_sum_crons' ] );

		return ( ! empty( $result ) ) ? $result : false;
	}

	/**
	 * Callback to sum crons events
	 *
	 * @param $carry
	 * @param $item
	 *
	 * @return int
	 */
	public function callback_sum_crons( $carry, $item ) {
		$carry += count( $item );

		return $carry;
	}

}

add_action( 'plugins_loaded', 'bea_bea_crons_warning' );
function bea_bea_crons_warning() {
	new BEA_Crons_Warning();
}