<?php
/**
 * This is WordPress plugin Min Calendar
 *
 * @package MinCalendar
 */

/**
 * MC_Date
 *
 * @package MinCalendar
 */
class MC_Date {

	/**
	 * 指定年月の日付と曜日の配列を取得
	 *
	 * @param integer $year  対象年.
	 * @param integer $month 対象月.
	 * @param boolean $admin 管理者.
	 *
	 * @return array 連想配列 キーday_of_weekは曜日の見出し配列、キーdaysは曜日の配列
	 */
	public static function get_days( $year, $month, $admin = false ) {
		$options = (array) json_decode( get_option( 'mincalendar-options' ) );
		if ( false === $admin ) {
			if ( false === isset( $options['mc-sun'] )
				 || '' === $options['mc-sun']
			) {
				$sun = null;
			} else {
				$sun = $options['mc-sun'];
			}
			$sun = ( false === empty( $sun ) ) ? $sun : 'Sun';
			if ( false === isset( $options['mc-mon'] )
				 || '' === $options['mc-mon']
			) {
				$mon = null;
			} else {
				$mon = $options['mc-mon'];
			}
			$mon = ( false === empty( $mon ) ) ? $mon : 'Mon';
			if ( false === isset( $options['mc-tue'] )
				 || '' === $options['mc-tue']
			) {
				$tue = null;
			} else {
				$tue = $options['mc-tue'];
			}
			$tue = ( false === empty( $tue ) ) ? $tue : 'Tue';
			if ( false === isset( $options['mc-wed'] )
				 || '' === $options['mc-wed']
			) {
				$wed = null;
			} else {
				$wed = $options['mc-wed'];
			}
			$wed = ( false === empty( $wed ) ) ? $wed : 'Wed';
			if ( false === isset( $options['mc-thu'] )
				 || '' === $options['mc-thu']
			) {
				$thu = null;
			} else {
				$thu = $options['mc-thu'];
			}
			$thu = ( false === empty( $thu ) ) ? $thu : 'Thu';
			if ( false === isset( $options['mc-fri'] )
				 || '' === $options['mc-fri']
			) {
				$fri = null;
			} else {
				$fri = $options['mc-fri'];
			}
			$fri = ( false === empty( $fri ) ) ? $fri : 'Fri';
			if ( false === isset( $options['mc-sat'] )
				 || '' === $options['mc-sat']
			) {
				$sat = null;
			} else {
				$sat = $options['mc-sat'];
			}
			$sat = ( false === empty( $sat ) ) ? $sat : 'Sat';
		} else {
			$sun = 'Sun';
			$mon = 'Mon';
			$tue = 'Tue';
			$wed = 'Wed';
			$thu = 'Thu';
			$fri = 'Fri';
			$sat = 'Sat';
		} // End if().
		$day_of_week = array(
			0 => esc_html( $sun ),
			1 => esc_html( $mon ),
			2 => esc_html( $tue ),
			3 => esc_html( $wed ),
			4 => esc_html( $thu ),
			5 => esc_html( $fri ),
			6 => esc_html( $sat ),
		);
		// First day unix time.
		$timestamp_1st = mktime( 0, 0, 0, $month, 1, $year );
		// First day at month.
		$day_1st       = date( 'w', $timestamp_1st );
		// Total days at month.
		$total         = date( 't', $timestamp_1st );

		$days = array();
		for ( $i = 1 - $day_1st; $i <= $total + 7; $i ++ ) {
			// 対象月の日付が有効な場合の処理.
			if ( 0 < $i && $i <= $total ) {
				$timestamp  = mktime( 0, 0, 0, $month, $i, $year );
				 // Day of $i (0:日～6:土).
				$day        = date( 'w', $timestamp );
				$days[ $i ] = $day_of_week[ $day ];
			}
		}

		return array(
			'days'        => $days,
			'day_of_week' => $day_of_week,
		);
	}
}
