<?php
/**
 * This is WordPress plugin Min Calendar
 *
 * @package MinCalendar
 */

/**
 * MC_Post_Factory
 */
class MC_Post_Factory {
	/**
	 * Get post wrapper.
	 *
	 * @param integer|null $post_id Post id of custom post type 'mincalendar'.
	 *
	 * @return MC_Post
	 */
	public static function get_calendar_post( $post_id = null ) {
		$calendar_post = new MC_Post();
		$calendar_post->set_initial( true );
		$calendar_post->set_id( $post_id );
		$post = get_post( $post_id );

		if ( ! is_null( $post ) || MC_POST_TYPE === get_post_type( $post ) ) {
			$calendar_post->set_initial( false );
			$calendar_post->set_id( $post->ID );
			$calendar_post->set_title( $post->post_title );
		} else {
			$calendar_post->set_initial( true );
			$calendar_post->set_title( __( 'Untitled', 'mincalendar' ) );
		}

		return $calendar_post;
	}
}
