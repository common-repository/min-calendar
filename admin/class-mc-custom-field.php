<?php
/**
 * This is WordPress plugin Min Calendar
 *
 * @package MinCalendar
 */

/**
 * MC_Custom_Field
 *
 * カレンダー投稿のカスタムフィールド
 *
 * @package    MinCalendar
 * @subpackage Admin
 */
class MC_Custom_Field {
	/**
	 * 日付入力フィールド処理
	 *
	 * @param MC_Post $calendar_post Wrapper of mincalendar post type.
	 * @param string  $html          Display markup.
	 */
	public static function set_field( $calendar_post, $html ) {
		$data = self::prepare( $calendar_post );
		// Display custom field value.
		$field = '<div id="fields"><div id="fields_year_month">'
				 . __( 'Year', 'mincalendar' )
				 . ' : <select name="year"><option value="-">--</option>';
		for ( $y = 2000; $y < 2050; $y ++ ) {
			if ( $y === $data['year'] ) {
				$field .= '<option value="' . esc_attr( $data['year'] ) . '" selected="selected">'
						  . esc_html( $data['year'] ) . '</option>';
			} else {
				$field .= '<option value="' . esc_attr( $y ) . '">' . esc_html( $y ) . '</option>';
			}
		}
		$field .= '</select> ';
		$field .= 'Month : <select name="month"><option value="-">--</option>';
		for ( $m = 1; $m < 13; $m ++ ) {
			if ( $m === $data['month'] ) {
				$field .= '<option value="' . esc_attr( $data['month'] ) . '" selected = "selected">'
						  . esc_html( $data['month'] ) . '</option>';
			} else {
				$field .= '<option value="' . esc_attr( $m ) . '">' . esc_html( $m ) . '</option>';
			}
		}
		$field .= '</select></div>' . PHP_EOL;

		/*
		 * Date processing.
		 */
		$field .= '<div id="fields_date">';
		// Get wp_option.
		$options  = (array) json_decode( get_option( 'mincalendar-options' ) );
		$context1 = ( true === isset( $options['mc-value-1st'] ) ) ? $options['mc-value-1st'] : '';
		$context2 = ( true === isset( $options['mc-value-2nd'] ) ) ? $options['mc-value-2nd'] : 'o';
		$context3 = ( true === isset( $options['mc-value-3rd'] ) ) ? $options['mc-value-3rd'] : 'x';
		$tags     = ( true === isset( $options['mc-tag'] ) ) ? $options['mc-tag'] : false;
		for ( $i = 1; $i <= $data['total_days']; $i ++ ) {
			$field .= '<div class="field">' . PHP_EOL;
			$field .= '<div class="cell cell-date">';
			if ( 'mc-value-1st' === $data['date'][ $i ] ) {
				$field .= '<span class="date">' . $i . '</span>' . ' '
						  . '<span class="days">' . $data['days'][ $i ] . '</span> '
						  . ' : <select name="date-' . $i . '">'
						  . '<option value="mc-value-1st" selected="selected">' . esc_html( $context1 ) . '</option>'
						  . '<option value="mc-value-2nd">' . esc_html( $context2 ) . '</option>'
						  . '<option value="mc-value-3rd">' . esc_html( $context3 ) . '</option>'
						  . '</select>';
			} elseif ( 'mc-value-2nd' === $data['date'][ $i ] ) {
				$field .= '<span class="date">' . $i . '</span>'
						  . ' ' . '<span class="days">' . $data['days'][ $i ] . '</span> '
						  . ' : <select name="date-' . $i . '">'
						  . '<option value="mc-value-1st">' . esc_html( $context1 ) . '</option>'
						  . '<option value="mc-value-2nd" selected="selected">' . esc_html( $context2 ) . '</option>'
						  . '<option value="mc-value-3rd">' . esc_html( $context3 ) . '</option>'
						  . '</select>';
			} elseif ( 'mc-value-3rd' === $data['date'][ $i ] ) {
				$field .= '<span class="date">' . $i . '</span>'
						  . ' ' . '<span class="days">' . $data['days'][ $i ] . '</span> '
						  . ' : <select name="date-' . $i . '">'
						  . '<option value="mc-value-1st">' . esc_html( $context1 ) . '</option>'
						  . '<option value="mc-value-2nd">' . esc_html( $context2 ) . '</option>'
						  . '<option value="mc-value-3rd" selected="selected">' . esc_html( $context3 ) . '</option>'
						  . '</select>';
			} else {
				$field .= '<span class="date">' . $i . '</span>'
						  . ' ' . '<span class="days">' . $data['days'][ $i ] . '</span> '
						  . ' : <select name="date-' . $i . '">'
						  . '<option value="mc-value-1st" selected="selected">' . esc_html( $context1 ) . '</option>'
						  . '<option value="mc-value-2nd">' . esc_html( $context2 ) . '</option>'
						  . '<option value="mc-value-3rd">' . esc_html( $context3 ) . '</option>'
						  . '</select>';
			}
			$field .= '</div><!-- cell-date -->';

			// Related posts.
			if ( ! empty( $tags ) ) {
				$tags_name = explode( ',', $tags );
				$tags_id   = array();
				foreach ( $tags_name as $key => $value ) {
					$tag_prop        = get_term_by( 'name', trim( $value ), 'post_tag' );
					$tags_id[ $key ] = $tag_prop->term_id;
				}
				$myposts = get_posts( array(
					'numberposts' => 100,
					'tag__in'     => $tags_id,
				) );
				$field   .= '<div class="cell cell-post">' . PHP_EOL;
				$field   .= 'post: <select name="post-' . $i . '">' . PHP_EOL;
				$field   .= '<option value="-">--</option>';
				foreach ( $myposts as $mypost ) {
					if ( $data['related_posts'][ $i ] == $mypost->ID ) {
						$field .= '<option value="' . esc_attr( $mypost->ID ) . '" selected="selected">'
								  . esc_html( $mypost->post_title ) . '</option>';
					} else {
						$field .= '<option value="' . esc_attr( $mypost->ID ) . '">'
								  . esc_html( $mypost->post_title ) . '</option>';
					}
				}
				$field .= '</select>' . PHP_EOL;
				$field .= '</div><!-- cell-post -->' . PHP_EOL;
			}

			// Text.
			$field .= '<div class="cell cell-text">' . PHP_EOL;
			$field .= 'text: <textarea type="text" rows="8" cols="30" name="text-' . $i . '">'
					  . esc_html( $data['texts'][ $i ] ) . '</textarea>';
			$field .= '</div><!-- cell-text -->';

			$field .= '</div><!-- field -->';
		} // End for().
		$field .= '</div><!-- fields-date -->' . PHP_EOL
				  . '</div><!-- fields -->' . PHP_EOL
				  . '</div><!-- poststuff -->' . PHP_EOL
				  . '</form>' . PHP_EOL
				  . '</div>';

		$calendar_post->set_html( $html . $field );
	}

	/**
	 * Prepare custom field
	 *
	 * @param MC_Post $calendar_post Wrapper of mincalendar post type.
	 *
	 * @return array
	 */
	public static function prepare( $calendar_post ) {
		$post_id = $calendar_post->get_id();
		// Get existing year and month.
		$year  = (int) get_post_meta( $post_id, 'year', true );
		$month = (int) get_post_meta( $post_id, 'month', true );
		// Set today when post is new.
		$today = getdate();
		$year  = ( ! empty( $year ) ) ? $year : (int) $today['year'];
		$month = ( ! empty( $month ) ) ? $month : (int) $today['mon'];
		// Get existing day.
		$day_and_week = MC_Date::get_days( $year, $month, true );
		$days         = $day_and_week['days'];
		$total_days   = count( $days );
		// Get existing date.
		$date = array();
		for ( $i = 1; $i <= $total_days; $i ++ ) {
			$key        = 'date-' . $i;
			$date[ $i ] = get_post_meta( $post_id, $key, true );
		}
		// Get existing posts.
		$related_posts = array();
		for ( $i = 1; $i <= $total_days; $i ++ ) {
			$key                 = 'post-' . $i;
			$related_posts[ $i ] = get_post_meta( $post_id, $key, true );
		}
		// Get text.
		$texts = array();
		for ( $i = 1; $i <= $total_days; $i ++ ) {
			$key         = 'text-' . $i;
			$texts[ $i ] = get_post_meta( $post_id, $key, true );
		}

		return array(
			'year'         => $year,
			'month'        => $month,
			'days'         => $days,
			'total_days'   => $total_days,
			'date'         => $date,
			'related_post' => $related_posts,
			'texts'        => $texts,
		);
	}

	/**
	 * Save
	 *
	 * @param MC_Post $calendar_post Wrapper of mincalendar post type.
	 * @return boolean
	 */
	public static function update_field( $calendar_post ) {
		$post_id = $calendar_post->get_id();
		$nonce   = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : null;
		if ( false === wp_verify_nonce( $nonce, 'save_' . $post_id )
			 && false === wp_verify_nonce( $nonce, 'save_' . - 1 )
		) {
			return $post_id;
		}
		// Date.
		$year  = (int) $_POST['year'];
		$month = (int) $_POST['month'];
		$days  = MC_Date::get_days( $year, $month, true );
		$total = count( $days['days'] );
		$date  = array();
		for ( $i = 1; $i <= $total; $i ++ ) {
			$key = 'date-' . $i;
			if ( isset( $_POST[ $key ] ) ) {
				$date[ $i ] = $_POST[ $key ];
			} else {
				$date[ $i ] = '';
			}
		}
		// Related posts.
		$related_posts = array();
		for ( $i = 1; $i <= $total; $i ++ ) {
			$key = 'post-' . $i;
			if ( isset( $_POST[ $key ] ) ) {
				$related_posts[ $i ] = $_POST[ $key ];
			} else {
				$related_posts[ $i ] = '';
			}
		}
		// Text.
		$texts = array();
		for ( $i = 1; $i <= $total; $i ++ ) {
			$key = 'text-' . $i;
			if ( isset( $_POST[ $key ] ) ) {
				$texts[ $i ] = $_POST[ $key ];
			} else {
				$texts[ $i ] = '';
			}
		}

		/*
		 * Update.
		 */
		if ( '' === $year ) {
			delete_post_meta( $post_id, 'year' );
		} else {
			update_post_meta( $post_id, 'year', $year );
		}
		if ( '' === $month ) {
			delete_post_meta( $post_id, 'month' );
		} else {
			update_post_meta( $post_id, 'month', $month );
		}
		for ( $i = 1; $i <= $total; $i ++ ) {
			$key = 'date-' . $i;
			if ( '' === $date[ $i ] ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $date[ $i ] );
			}
		}
		for ( $i = 1; $i <= $total; $i ++ ) {
			$key = 'post-' . $i;
			if ( '' === $related_posts[ $i ] ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $related_posts[ $i ] );
			}
		}
		for ( $i = 1; $i <= $total; $i ++ ) {
			$key = 'text-' . $i;
			if ( '' === $texts[ $i ] ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $texts[ $i ] );
			}
		}
		return true;
	}
}
