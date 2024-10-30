<?php
/**
 * This is WordPress plugin Min Calendar
 *
 * @package MinCalendar
 */

/**
 * MC_Admin_Controller
 *
 * @package    MinKalendar
 * @subpackage Admin
 */
class MC_Admin_Controller {

	/**
	 * Post action
	 *
	 * @var MC_Admin_Action $action
	 */
	private $action;

	/**
	 * Post form
	 *
	 * @var MC_Post_Form
	 */
	private $post_form;

	/**
	 * Appearance setting page
	 *
	 * @var MC_Appearance
	 */
	private $appearance;

	/**
	 * Post list of mincalendar post type
	 *
	 * @var MC_List_Table
	 */
	private $list_table;

	/**
	 * MC_Admin_Controller constructor.
	 */
	function __construct() {
		$this->post_form  = new MC_Post_Form();
		$this->action     = new MC_Admin_Action();
		$this->appearance = new MC_Appearance();
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Add Min Calendar page to admin page
	 */
	public function admin_menu() {
		if ( current_user_can( 'edit' ) ) {
			add_menu_page(
				'Min Calendar',
				'Min Calendar',
				'read',
				'mincalendar',
				array( $this, 'admin_management_page' )
			);
			// All posts of mincalendar post type.
			$post_list = add_submenu_page(
				'mincalendar',
				__( 'Edit Calendar', 'mincalendar' ),
				__( 'Edit', 'mincalendar' ),
				'read',
				'mincalendar',
				array( $this, 'admin_management_page' )
			);
			// Set callback for MC_Admin_Action::manage_action.
			add_action( 'load-' . $post_list, array( $this->action, 'manage_action' ) );
			// Set Appearance page.
			add_submenu_page(
				'mincalendar',
				__( 'Edit Appearance', 'mincalendar' ),
				__( 'Appearance', 'mincalendar' ),
				'read',
				'mincalenar-appearance',
				array( $this->appearance, 'admin_appearance_page' )
			);
		}
	}

	/**
	 * Call Min Calendar page
	 *
	 * 投稿の編集は編集画面表示しその他は投稿リストを表示する。
	 */
	public function admin_management_page() {
		$calendar_post = $this->action->get_calendar_post();
		// Display calender post edit page.
		if ( $calendar_post ) {
			$post_form = new MC_Post_Form();
			$html      = $post_form->get_form( $calendar_post );
			MC_Custom_Field::set_field( $calendar_post, $html );
			echo $calendar_post->get_html();

			return;
		}
		// Display calendar post lists page.
		$this->list_table = new MC_List_Table();
		?>
		<div class="wrap">
			<h2>Min Calendar <a href="admin.php?page=mincalendar&action=new">
					<?php echo esc_html( __( 'Add New', 'mincalendar' ) ) . '</a>' ?>
					<?php
					$search_word = filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
					if ( ! empty( $search_word ) ) {
						echo sprintf(
							'<span class="subtitle">'
							/* translators: %1$s is replaced with "string" */
							. esc_html( __( 'Search results for "%s"', 'mincalendar' ) )
							. '</span>',
							esc_html( $search_word )
						);
					}
					$page_name = filter_input( INPUT_GET, 'page', FILTER_DEFAULT );
					?>
			</h2>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>">
				<?php
				$this->list_table->prepare_items();
				$this->list_table->search_box( __( 'Search Calendar', 'mincalendar' ), 'mincalendar' );
				$this->list_table->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts for admin page
	 *
	 * @param string $hook_suffix Plugin suffix.
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, 'mincalendar' ) ) {
			return;
		}
		wp_enqueue_style(
			'mincalendar-admin',
			MC_Utilities::mc_plugin_url( 'admin/css/styles.css' ),
			array( 'thickbox' ),
			MC_VERSION,
			'all'
		);
		wp_enqueue_script(
			'mincalendar-admin-scripts',
			MC_Utilities::mc_plugin_url( 'admin/js/scripts.js' ),
			array(
				'jquery',
				'thickbox',
				'postbox',
			),
			MC_VERSION,
			true
		);
		wp_enqueue_script(
			'mincalendar-admin',
			MC_Utilities::mc_plugin_url( 'admin/js/admin.js' ),
			array(),
			MC_VERSION,
			true
		);
		wp_enqueue_script(
			'mincalendar-admin-custom-fields',
			MC_Utilities::mc_plugin_url( 'admin/js/custom_fields.js' ),
			array(),
			MC_VERSION,
			true
		);
		wp_enqueue_script(
			'mincalendar-admin-custom-fields_handler',
			MC_Utilities::mc_plugin_url( 'admin/js/custom_fields_handler.js' ),
			array(),
			MC_VERSION,
			true
		);
	}
}
