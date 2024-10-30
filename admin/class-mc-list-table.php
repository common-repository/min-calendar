<?php
/**
 * List for posts of mincalendar post type
 *
 * @package    MinCalendar
 * @subpackage Admin
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * MC_List_Table
 *
 * @package    MinCalendar
 * @subpackage Admin
 */
class MC_List_Table extends WP_List_Table {
	/**
	 * Total amount of mincalendar post.
	 *
	 * @var integer $items
	 * @see WP_List_Table::$items
	 */
	public $items;

	/**
	 * MC_List_Table constructor.
	 */
	function __construct() {
		parent::__construct(
			array(
				'singular' => 'post_id',
				'plural'   => 'post_ids',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Define columns.
	 *
	 * @return array
	 */
	public static function define_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __( 'Title', 'mincalendar' ),
			'shortcode' => __( 'Shortcode', 'mincalendar' ),
			'author'    => __( 'Author', 'mincalendar' ),
			'date'      => __( 'Date', 'mincalendar' ),
		);

		return $columns;
	}

	/**
	 * Prepare items
	 *
	 * @see WP_List_Table::prepare_items()
	 */
	function prepare_items() {
		$per_page              = $this->get_items_per_page( 'mc_per_page' );
		$this->_column_headers = $this->get_column_info();
		$args                  = array(
			'posts_per_page' => $per_page,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'offset'         => ( $this->get_pagenum() - 1 ) * $per_page,
		);
		$search_word           = filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
		if ( ! empty( $search_word ) ) {
			$args['s'] = $search_word;
		}
		// Sort column.
		$selected_column = filter_input( INPUT_GET, 'orderby', FILTER_DEFAULT );
		if ( false !== $selected_column && ! is_null( $selected_column ) ) {
			switch ( $selected_column ) {
				case 'title':
					$args['orderby'] = 'title';
					break;
				case 'author':
					$args['orderby'] = 'author';
					break;
				case 'date':
					$args['orderby'] = 'date';
					break;
			}
		}
		$selected_order = filter_input( INPUT_GET, 'order', FILTER_DEFAULT );
		if ( false !== $selected_order && ! is_null( $selected_order ) ) {
			switch ( strtolower( $selected_order ) ) {
				case 'asc':
					$args['order'] = 'ASC';
					break;
				case 'desc':
					$args['order'] = 'DESC';
					break;
			}
		}
		$this->items = $this->find( $args );
		$total_pages = ceil( count( $this->items ) / $per_page );
		$this->set_pagination_args(
			array(
				'items'       => $this->items,
				'total_items' => count( $this->items ),
				'total_pages' => $total_pages,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Get the column headers for a screen
	 *
	 * @see WP_List_Table::get_columns()
	 * @return array
	 */
	function get_columns() {
		return get_column_headers( get_current_screen() );
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		$columns = array(
			'title'  => array( 'title', true ),
			'author' => array( 'author', false ),
			'date'   => array( 'date', false ),
		);

		return $columns;
	}

	/**
	 * Get bulk actions.
	 *
	 * @return array
	 */
	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete',
		);

		return $actions;
	}

	/**
	 * Set checkbox of item for bulk action
	 *
	 * @param object $item Item of list.
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item->get_id()
		);
	}

	/**
	 * Set title of item
	 *
	 * @param object $item Item of list.
	 *
	 * @return string
	 */
	function column_title( $item ) {
		$post_id = $item->get_id();
		if ( current_user_can( 'edit', $post_id ) ) {
			$url       = admin_url( 'admin.php?page=mincalendar&post_id=' . absint( $post_id ) );
			$edit_link = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'edit',
					),
					$url
				),
				'edit'
			);
			$actions   = array(
				'edit' => '<a href="' . $edit_link . '">' . __( 'Edit', 'mincalendar' ) . '</a>',
			);
			$copy_link = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'copy',
					),
					$url
				),
				'copy_' . absint( $post_id )
			);
			$actions   = array_merge(
				$actions,
				array(
					'copy' => '<a href="' . $copy_link . '">' . __( 'Copy', 'mincalendar' ) . '</a>',
				)
			);
			/* translators: %s is a placeholder that must be string. */
			$a = sprintf(
				'<a class="row-title" href="%1$s" title="%2$s">%3$s</a>',
				$edit_link,
				$title = $item->get_title(),
				/* translators: %s is a placeholder that must be string. */
				esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'mincalendar' ), $title ) ),
				esc_html( $title )
			);

			return '<strong>' . $a . '</strong> ' . $this->row_actions( $actions );
		} // End if().
	}

	/**
	 * Set author of item
	 *
	 * @param object $item List item.
	 *
	 * @return bool|string
	 */
	function column_author( $item ) {
		$post = get_post( $item->get_id() );
		if ( false === $post ) {
			return false;
		}
		$author = get_userdata( $post->post_author );

		return esc_html( $author->display_name );
	}


	/**
	 * Set short code of item
	 *
	 * @param object $item List item.
	 *
	 * @return string
	 */
	function column_shortcode( $item ) {
		$short_codes = array(
			sprintf(
				'[mincalendar id="%1$d" title="%2$s"]',
				$item->get_id(),
				$item->get_title()
			),
		);
		$output      = '';
		foreach ( $short_codes as $short_code ) {
			$output .= "\n" . '<input type="text" onfocus="this.select();" readonly="readonly" value="'
					   . esc_attr( $short_code ) . '" class="shortcode-in-list-table" />';
		}

		return trim( $output );
	}

	/**
	 * Set date of item.
	 *
	 * @param  object $item List item.
	 *
	 * @return string|void
	 */
	function column_date( $item ) {
		$post = get_post( $item->get_id() );
		if ( false === $post ) {
			return false;
		}
		$t_time    = mysql2date( __( 'Y/m/d g:i:s A', 'mincalendar' ), $post->post_date, true );
		$m_time    = $post->post_date;
		$time      = mysql2date( 'G', $post->post_date ) - get_option( 'gmt_offset' ) * 3600;
		$time_diff = time() - $time;
		if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
			/* translators: %s is a placeholder that must be string. */
			$h_time = sprintf( __( '%s ago', 'mincalendar' ), human_time_diff( $time ) );
		} else {
			$h_time = mysql2date( __( 'Y/m/d', 'mincalendar' ), $m_time );
		}

		return '<a title="' . $t_time . '">' . $h_time . '</a>';
	}

	/**
	 * Find posts
	 *
	 * @param array $args Argument to find mincalendar posts.
	 *
	 * @return array
	 */
	public function find( $args = array() ) {
		$defaults    = array(
			'post_type'      => 'mincalendar',
			'post_status'    => 'any',
			'posts_per_page' => - 1,
			'offset'         => 0,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);
		$args        = wp_parse_args( $args, $defaults );
		$query       = new WP_Query();
		$posts       = $query->query( $args );
		$this->items = $query->found_posts;
		$objs        = array();
		foreach ( (array) $posts as $post ) {
			$objs[] = MC_Post_Factory::get_calendar_post( $post->ID );
		}

		return $objs;
	}
}
