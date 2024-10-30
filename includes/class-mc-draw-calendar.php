<?php
/**
 * This is WordPress plugin Min Calendar
 *
 * @package MinCalendar
 */

/**
 * MC_Draw_Calendar
 *
 * Create mark up from post id of mincalendar post type.
 *
 * @package MinCalendar
 */
class MC_Draw_Calendar {

	/**
	 * Create calendar mark up for display.
	 *
	 * @param integer $post_id Post id.
	 *
	 * @return string  Markup to display calendar.
	 */
	public static function draw( $post_id ) {
		$year        = (int) get_post_meta( $post_id, 'year', true );
		$month       = (int) get_post_meta( $post_id, 'month', true );
		$res         = MC_Date::get_days( $year, $month, false );
		$days        = $res['days'];
		$day_of_week = $res['day_of_week'];
		$total       = count( $days );

		for ( $i = 1; $i <= $total; $i ++ ) {
			$key_date           = 'date-' . $i;
			$key_post           = 'post-' . $i;
			$key_text           = 'text-' . $i;
			$date[ $i ]         = get_post_meta( $post_id, $key_date, true );
			$relate_posts[ $i ] = get_post_meta( $post_id, $key_post, true );
			$texts[ $i ]        = get_post_meta( $post_id, $key_text, true );
		}

		$html = self::make(
			$year,
			$month,
			$date,
			$day_of_week,
			$relate_posts,
			$texts
		);

		return $html;
	}

	/**
	 * 曜日出力用マークアップ作成
	 *
	 * @param string $y            year is yyyy.
	 * @param string $m            month is 1～13.
	 * @param array  $date         日付情報.
	 * @param array  $day_of_week  曜日のラベル.
	 * @param array  $relate_posts 曜日に紐づいた投稿.
	 * @param array  $texts        テキスト.
	 *
	 * @return string 曜日のマークアップ
	 */
	private static function make( $y, $m, $date, $day_of_week, $relate_posts, $texts ) {
		$t = mktime( 0, 0, 0, $m, 1, $y );
		// 1日の曜日（0:日～6:土）.
		$w = date( 'w', $t );
		// Total days at y year, m month.
		$n = date( 't', $t );
		if ( $m < 10 ) {
			$m = '0' . $m;
		}
		$sun = $day_of_week[0];
		$mon = $day_of_week[1];
		$tue = $day_of_week[2];
		$wed = $day_of_week[3];
		$thu = $day_of_week[4];
		$fri = $day_of_week[5];
		$sat = $day_of_week[6];

		$html = <<<HTML
    <table class="mincalendar">
    <caption>${y} . ${m}</caption>
    <tr>
        <th class="mincalendar-th-sun">${sun}</th>
        <th>${mon}</th>
        <th>${tue}</th>
        <th>${wed}</th>
        <th>${thu}</th>
        <th>${fri}</th>
        <th class="mincalendar-th-sat">${sat}</th>
    </tr>
HTML;
		// Get options.
		$options = (array) json_decode( get_option( 'mincalendar-options' ) );
		for ( $i = 1 - $w; $i <= $n + 7; $i ++ ) {
			if ( ( ( $i + $w ) % 7 ) == 1 ) {
				$html .= '<tr>' . PHP_EOL;
			}
			// 日付が有効な場合の処理.
			if ( ( 0 < $i ) && ( $i <= $n ) ) {
				// Get Date information. $key = 'date-' . $i.
				$option  = $date[ $i ];
				$context = '';
				$html    .= '<td';
				// Get days.
				$hizuke = mktime( 0, 0, 0, $m, $i, $y );
				// 1日の曜日（0:日～6:土）.
				$youbi  = date( 'w', $hizuke );
				// Setting of mc-value.
				if ( isset( $options['mc-value-1st'] ) && 'mc-value-1st' === $option ) {
					$html    .= self::set_holiday( '1st', $youbi );
					$context = ( "\x20" === $options['mc-value-1st'] ) ? '&nbsp;' : $options['mc-value-1st'];
				} elseif ( isset( $options['mc-value-2nd'] ) && 'mc-value-2nd' === $option ) {
					$html    .= self::set_holiday( '2nd', $youbi );
					$context = ( "\x20" === $options['mc-value-2nd'] ) ? '&nbsp;' : $options['mc-value-2nd'];
				} elseif ( isset( $options['mc-value-3rd'] ) && 'mc-value-3rd' === $option ) {
					$html    .= self::set_holiday( '3rd', $youbi );
					$context = ( "\x20" === $options['mc-value-3rd'] ) ? '&nbsp;' : $options['mc-value-3rd'];
				}
				// 紐づけた投稿.
				if ( is_numeric( $relate_posts[ $i ] ) ) {
					$relate = get_post( $relate_posts[ $i ] );
					$link   = '<a target="_blank" href="' . get_permalink( $relate_posts[ $i ] ) . '">' . $relate->post_title . '</a>';
				} else {
					$link = '';
				}
				// テキスト.
				if ( '' !== $texts[ $i ] ) {
					$text = nl2br( esc_html( $texts[ $i ] ) );
				} else {
					$text = '';
				}
				// Context.
				$html .= '><div class="td-inner">';
				$html .= '<div class="mc-date">' . $i . '</div><div>' . esc_html( $context ) . '</div>';
				if ( false === empty( $link ) ) {
					$html .= '<div class="mc-link">' . $link . '</div>';
				}
				if ( false === empty( $text ) ) {
					$html .= '<div class="mc-text">' . $text . '</div>';
				}
				$html .= '</div></td>' . PHP_EOL;
			} else {
				// 日付が無効な場合.
				$html .= '<td>&nbsp;</td>' . PHP_EOL;
			} // End if().
			if ( ( ( $i + $w ) % 7 ) == 0 ) {
				$html .= '</div></tr>' . PHP_EOL;
				if ( $i >= $n ) {
					break;
				}
			}
		} // End for().
		$html .= '</table>' . PHP_EOL;

		// Variable html is escaped.
		return $html;
	}

	/**
	 * Set holiday.
	 *
	 * @param string  $index (1st, 2nd, 3rd).
	 * @param integer $youbi 曜日 0:日, 6:土曜日.
	 *
	 * @return string Markup
	 */
	public static function set_holiday( $index, $youbi ) {
		$html = ' class="mc-bgcolor-' . $index;
		if ( 0 === (int) $youbi ) {
			$html .= ' mincalendar-td-sun';
		} elseif ( 6 === (int) $youbi ) {
			$html .= ' mincalendar-td-sat';
		}
		$html .= '"';

		return $html;
	}
}
