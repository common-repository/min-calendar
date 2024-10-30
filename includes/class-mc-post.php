<?php
/**
 * This is WordPress plugin Min Calendar
 *
 * @package MinCalendar
 */

/**
 * MC_Post
 *
 * Wrapper for mincalendar post type.
 */
class MC_Post {

	/**
	 * Min calendar post type name
	 *
	 * @var string POST_TYPE
	 */
	const POST_TYPE = 'mincalendar';

	/**
	 * New post is false. Exiting post is true
	 *
	 * @var bool $initial
	 */
	private $initial = false;

	/**
	 * Post id
	 *
	 * @var integer $id
	 */
	private $id;

	/**
	 * Post tile
	 *
	 * @var string $title
	 */
	private $title;

	/**
	 * Custom Field mark up.
	 *
	 * @var string $html Custom field Markup.
	 */
	public $html;

	/**
	 * Get initial
	 */
	public function get_initial() {
		return $this->initial;
	}

	/**
	 * Set initial
	 *
	 * @param boolean $initial New post is true. Existing post is false.
	 */
	public function set_initial( $initial ) {
		$this->initial = $initial;
	}

	/**
	 * Get id
	 */
	public function get_id() {
		return $this->id;
	}
	/**
	 * Set id
	 *
	 * @param integer $post_id Post id.
	 */
	public function set_id( $post_id ) {
		$this->id = $post_id;
	}

	/**
	 * Get tile
	 */
	public function get_title() {
		return $this->title;
	}
	/**
	 * Set title
	 *
	 * @param string $title Calendar(Post) title.
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Get custom field markup.
	 *
	 * @return string Escaped  mark up string.
	 */
	public function get_html() {
		return $this->html;
	}

	/**
	 * Set custom field mark up.
	 *
	 * @param string $html Escaped  mark up string.
	 */
	public function set_html( $html ) {
		$this->html = $html;
	}

	/**
	 * Save post.
	 */
	function save() {
		// Get post id when post is new.
		if ( true === $this->initial ) {
			$post_id = wp_insert_post( array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => $this->title,
			) );
		} else {
			$post_id = wp_update_post( array(
				'ID'          => (int) $this->id,
				'post_status' => 'publish',
				'post_title'  => $this->title,
			) );
		}
		if ( $post_id ) {
			if ( $this->initial ) {
				$this->initial = false;
				$this->id      = $post_id;
			}
		}

		return $post_id;
	}

	/**
	 * Copy post.
	 *
	 * Execute when MC_List_Table do copy.
	 */
	function copy() {
		$new          = MC_Post_Factory::get_calendar_post();
		$new->initial = true;
		$new->title   = $this->title . '_copy';

		return $new;
	}

	/**
	 * Delete post.
	 */
	function delete() {
		if ( true === $this->initial ) {
			return;
		}
		if ( wp_delete_post( $this->id, true ) ) {
			$this->initial = true;
			$this->id      = null;

			return true;
		}

		return false;
	}
}
