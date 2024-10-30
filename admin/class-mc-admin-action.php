<?php
/**
 * This is WordPress plugin Min Calendar
 *
 * @package MinCalendar
 */

/**
 * MC_Admin_Action
 *
 * Manage post form action.
 *
 * @package MinCalendar
 * @subpackage Admin
 */
class MC_Admin_Action {
	/**
	 * Wrapper of mincalendar post type
	 *
	 * @var MC_Post
	 */
	public $calendar_post;

	/**
	 * MC_Admin_Action constructor.
	 */
	function __construct() {
		$this->calendar_post = null;
	}

	/**
	 * Get calendar_post
	 */
	public function get_calendar_post() {
		return $this->calendar_post;
	}

	/**
	 * Manage action at Min Calendar page.
	 *
	 * Min Calendar page is /wp-admin/admin.php?page=mincalendar.
	 *
	 * Execution order when load Min Calendar page.
	 *
	 * 1. MC_Admin_Action::manage_action. This method.
	 * 2. MC_Admin_Controller::admin_management_page.
	 *
	 * @see MC_Admin_Controller::admin_management_page()
	 */
	public function manage_action() {
		$action = MC_Admin_Utility::get_current_action();
		if ( 'save' === $action ) {
			$this->save();
		}
		if ( 'copy' === $action ) {
			$this->copy();
		}
		if ( 'delete' === $action ) {
			$this->delete();
		}
		$post_id      = filter_input( INPUT_GET, 'post_id', FILTER_VALIDATE_INT );
		$calendar_post = null;
		if ( 'new' === $action && current_user_can( 'edit' ) ) {
			$calendar_post = MC_Post_Factory::get_calendar_post();
		}
		if ( 'edit' === $action && false !== $post_id ) {
			$calendar_post = MC_Post_Factory::get_calendar_post( $post_id );
		}
		if ( $calendar_post && current_user_can( 'edit', $calendar_post->get_id() ) ) {
			$this->calendar_post = $calendar_post;
		} else {
			// Initial load.
			$current_screen = get_current_screen();
			add_filter(
				'manage_' . $current_screen->id . '_columns',
				array( 'MC_List_Table', 'define_columns' )
			);
		}
	}

	/**
	 * Save post.
	 *
	 * New post is -1. Existing post is post id.
	 */
	private function save() {
		// New post is -1.
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
		check_admin_referer( 'save_' . $post_id );
		if ( false === current_user_can( 'edit', $post_id ) ) {
			wp_die( __( 'You are not allowed to edit this item.', 'mincalendar' ) );
		}
		$this->calendar_post = MC_Post_Factory::get_calendar_post( $post_id );
		if ( false === $this->calendar_post ) {
			$this->calendar_post = MC_Post_Factory::get_calendar_post();
			$this->calendar_post->set_initial( true );
		}
		$this->calendar_post->set_title(
			filter_input(
				INPUT_POST,
				'mincalendar-title',
				FILTER_SANITIZE_STRING
			)
		);
		// New post data insert wp_posts so that ID number of wp_posts is defined.
		$this->calendar_post->save();
		// Update custom field.
		MC_Custom_Field::update_field( $this->calendar_post );
		$query            = array();
		$query['action']  = 'edit';
		$query['post_id'] = (int) $this->calendar_post->get_id();
		$redirect_to      = add_query_arg( $query, menu_page_url( 'mincalendar', false ) );
		wp_safe_redirect( $redirect_to );
		exit();
	}

	/**
	 * Copy post
	 */
	private function copy() {
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
		if ( false == $post_id || is_null( $post_id ) ) {
			$post_id = filter_input( INPUT_GET, 'post_id', FILTER_VALIDATE_INT );
		}
		check_admin_referer( 'copy_' . $post_id );
		if ( ! current_user_can( 'edit', $post_id ) ) {
			wp_die( __( 'You are not allowed to edit this item.', 'mincalendar' ) );
		}
		$query              = array();
		$this->calendar_post = MC_Post_Factory::get_calendar_post( $post_id );
		$new_calendar_post   = $this->calendar_post->copy();
		$new_calendar_post->save();
		$query['post_id'] = $new_calendar_post->get_id();
		$redirect_to      = add_query_arg( $query, menu_page_url( 'mincalendar', false ) );
		wp_safe_redirect( $redirect_to );
		exit();
	}

	/**
	 * Delete post
	 */
	private function delete() {
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
		if ( false !== $post_id && ! is_null( $post_id ) ) {
			check_admin_referer( 'delete_' . $post_id );
		} else {
			$post_id = filter_input( INPUT_GET, 'post_id' );
			if ( is_numeric( $post_id ) ) {
				check_admin_referer( 'delete_' . $post_id );
			} else {
				// Bulk action. post_id is array.
				$post_id = filter_input(
					INPUT_GET,
					'post_id',
					FILTER_DEFAULT,
					FILTER_REQUIRE_ARRAY
				);
				check_admin_referer( 'bulk-post_ids' );
			}
		}
		$post_ids = is_array( $post_id ) ? $post_id : array( $post_id );
		foreach ( $post_ids as $post_id ) {
			$calendar_post = MC_Post_Factory::get_calendar_post( $post_id );
			if ( ! current_user_can( 'delete', $calendar_post->get_id() ) ) {
				wp_die( __( 'You are not allowed to delete this item.', 'mincalendar' ) );
			}
			if ( ! $calendar_post->delete() ) {
				wp_die( __( 'Error in deleting.', 'mincalendar' ) );
			}
		}
		$redirect_to = add_query_arg( array(), menu_page_url( 'mincalendar', false ) );
		wp_safe_redirect( $redirect_to );
		exit();
	}
}
