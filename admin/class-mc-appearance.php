<?php
/**
 * This is WordPress plugin Min Calendar
 *
 * @package MinCalendar
 */

/**
 * MC_Appearance
 *
 * カレンダーのAppearance設定
 *
 * appearanceは全カレンダーで共通。
 * 個別に設定することはできない。
 *
 * @package    MinCalendar
 * @subpackage Admin
 */
class MC_Appearance {
	/**
	 * テーブルwp_optionsへキーmincalendar-optionsで設定する配列
	 *
	 * @var array $options
	 */
	private $options = array();

	/**
	 * 正しくない入力が行われたキーの配列
	 *
	 * @var array $errors
	 */
	private $errors = array();

	/**
	 * CSS文字列
	 *
	 * @var string $css
	 */
	private $css = '';

	/**
	 * Update options
	 *
	 * テーブルwp_optionsのmincalendar-optionにoptions配列をjson形式に変換して設定.
	 *
	 * @param string $key     Options of mincalendar-option.
	 * @param string $referer Referrer of update-appearance.
	 *
	 * @return bool
	 */
	private function update( $key = 'mincalendar-options', $referer = 'update-appearance' ) {
		if ( true === empty( $this->options ) ) {
			return false;
		}
		check_admin_referer( $referer );
		update_option( $key, json_encode( $this->options ) );
	}

	/**
	 * Add key/value pair to options
	 *
	 * @param string $key   Key of options.
	 * @param string $value Setting value of key.
	 */
	private function add_options( $key, $value ) {
		if ( true === array_key_exists( $key, $this->options ) ) {
			$this->options[ $key ] = $value;
		} else {
			$this->options += array(
				$key => $value,
			);
		}
	}

	/**
	 * フォームの入力値を検査しadd_option関数へ渡す
	 *
	 * @param string $key   Key to update.
	 * @param string $value Update value.
	 *
	 * @return mix          キーがあれば検査済み値、無いときはfalse
	 */
	private function prepare( $key, $value = null ) {
		if ( false === isset( $_POST[ $key ] ) ) {
			return false;
		}
		if ( true === is_null( $value ) ) {
			$value = $_POST[ $key ];
			$this->add_options( $key, $value );
		} else {
			$this->add_options( $key, $value );
		}

		return $value;
	}

	/**
	 * Validate and set css width/height
	 *
	 * @param string $key      Key of options.
	 * @param string $selector CSS selector name.
	 * @param string $property CSS value.
	 *
	 * @return bool 入力値が正しければtrue, 正しくない場合はerror配列に追加してfalseを返す
	 */
	private function prepare_size( $key, $selector, $property ) {
		if ( false === isset( $_POST[ $key ] ) || '' === $_POST[ $key ] ) {
			return false;
		}
		$value = $_POST[ $key ];
		if ( true === MC_Validation::is_size( $value ) ) {
			$value     = MC_Validation::normalize_size( $value );
			$value     = $this->prepare( $key, $value );
			$this->css .= $selector . ' { ' . $property . ': ' . $value . '; }' . PHP_EOL;

			return true;
		}
		$this->errors[ $key ] = __( 'The inputted value is not right.', 'mincalendar' );

		return false;
	}

	/**
	 * Validate and set css border.
	 *
	 * @param string $key      Key of options.
	 * @param string $selector CSS selector.
	 *
	 * @return boolean 入力値が正しければtrue, 正しくない場合はerror配列に追加してfalseを返す
	 */
	private function prepare_border( $key, $selector ) {
		if ( false === isset( $_POST[ $key ] ) || '' === $_POST[ $key ] ) {
			return false;
		}
		$value = $_POST[ $key ];
		if ( true === MC_Validation::is_color( $value ) ) {
			$value     = MC_Validation::normalize_color( $value );
			$value     = $this->prepare( $key, $value );
			$this->css .= $selector . '{ ' . 'border: 1px solid ' . $value . '; }' . PHP_EOL;

			return true;
		}
		$this->errors[ $key ] = __( 'The inputted value is not right.', 'mincalendar' );

		return false;
	}

	/**
	 * Validate and set css color.
	 *
	 * @param string $key      Key of options.
	 * @param string $selector CSS selector name.
	 * @param string $property CSS value.
	 *
	 * @return boolean 入力値が正しければtrue, 正しくない場合はerror配列に追加してfalseを返す
	 */
	private function prepare_color( $key, $selector, $property ) {
		if ( false === isset( $_POST[ $key ] ) || '' === $_POST[ $key ] ) {
			return false;
		}

		$value = $_POST[ $key ];
		if ( true === MC_Validation::is_color( $value ) ) {
			$value     = MC_Validation::normalize_color( $value );
			$value     = $this->prepare( $key, $value );
			$this->css .= $selector . '{ ' . $property . ': ' . $value . '; }' . PHP_EOL;

			return true;
		}
		$this->errors[ $key ] = __( 'The inputted value is not right.', 'mincalendar' );

		return false;
	}

	/**
	 * Validate and set align.
	 *
	 * @param string $key      Key of options.
	 * @param string $selector CSS selector name.
	 * @param string $property CSS value.
	 *
	 * @return boolean 入力値が正しければtrue, 正しくない場合はerror配列に追加してfalseを返す
	 */
	private function prepare_align( $key, $selector, $property ) {
		if ( false === isset( $_POST[ $key ] ) || '' === $_POST[ $key ] ) {
			return false;
		}
		$value = $_POST[ $key ];
		if ( true === MC_Validation::is_align( $value ) ) {
			$value     = $this->prepare( $key, $value );
			$this->css .= $selector . '{ ' . $property . ': ' . $value . '; }' . PHP_EOL;

			return true;
		}
		$this->errors[ $key ] = __( 'The inputted value is not right.', 'mincalendar' );

		return false;
	}


	/**
	 * Create input control mark up
	 *
	 * @param string $name    Display name.
	 * @param string $key     Value of name attribute.
	 * @param string $example Input example.
	 *
	 * @return string Created mark up for control
	 */
	private function create_field( $name, $key, $example = '' ) {
		$value  = esc_attr( isset( $this->options[ $key ] ) ? $this->options[ $key ] : '' );
		$error  = esc_attr( isset( $this->errors[ $key ] ) ? $this->errors[ $key ] : '' );
		$markup = '<tr>' . PHP_EOL;
		$markup .= '<th>' . $name . '</th>' . PHP_EOL;
		$markup .= '<td><input type="text" name="' . $key . '" value="' . $value . '" size="8">'
				   . '&nbsp;<span class="mc-example">' . $example . '</span>'
				   . '&nbsp;<span class="mc-error">' . $error . '</span></td>' . PHP_EOL;
		$markup .= '</tr>' . PHP_EOL;

		return $markup;
	}

	/**
	 * Set page of calendar appearance page
	 */
	public function admin_appearance_page() {
		wp_enqueue_style(
			'mincalendar-appearance',
			MC_Utilities::mc_plugin_url( 'admin/css/appearance.css' )
		);

		if ( ! current_user_can( 'edit' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page' ) );

			return false;
		}

		/*
		 * Update
		 */

		// 曜日見出し.
		$this->prepare( 'mc-sun' );
		$this->prepare( 'mc-mon' );
		$this->prepare( 'mc-tue' );
		$this->prepare( 'mc-wed' );
		$this->prepare( 'mc-thu' );
		$this->prepare( 'mc-fri' );
		$this->prepare( 'mc-sat' );
		$this->prepare( 'mc-value-1st' );
		$this->prepare( 'mc-value-2nd' );
		$this->prepare( 'mc-value-3rd' );

		// 日曜・土曜見出し色.
		$this->prepare_color( 'mincalendar-th-sun', '.mincalendar-th-sun', 'color' );
		$this->prepare_color( 'mincalendar-th-sat', '.mincalendar-th-sat', 'color' );

		// 日曜・土曜背景色.
		$this->prepare_color( 'mincalendar-th-sun-bg', '.mincalendar-th-sun', 'background' );
		$this->prepare_color( 'mincalendar-th-sat-bg', '.mincalendar-th-sat', 'background' );
		$this->prepare_color( 'mincalendar-td-sun-bg', '.mincalendar-td-sun', 'background' );
		$this->prepare_color( 'mincalendar-td-sat-bg', '.mincalendar-td-sat', 'background' );

		// Table Width.
		$this->prepare_size( 'mc-table-width', '.mincalendar', 'width' );

		// Cell width and height.
		$this->prepare_size( 'mc-width-th', '.mincalendar th', 'width' );
		$this->prepare_size( 'mc-height-th', '.mincalendar th', 'height' );
		$this->prepare_size( 'mc-width-td', '.mincalendar td', 'width' );
		$this->prepare_size( 'mc-height-td', '.mincalendar td', 'height' );

		// Table th border color.
		$this->prepare_border( 'mc-border-th', '.mincalendar th ' );
		$this->prepare_border( 'mc-border-td', '.mincalendar td ' );

		// Table td background color.
		$this->prepare_color( 'mc-bgcolor-1st', '.mc-bgcolor-1st', 'background' );
		$this->prepare_color( 'mc-bgcolor-2nd', '.mc-bgcolor-2nd', 'background' );
		$this->prepare_color( 'mc-bgcolor-3rd', '.mc-bgcolor-3rd', 'background' );

		// Text align.
		$this->prepare_align( 'mc-th-text-align', '.mincalendar th ', 'text-align' );
		$this->prepare_align( 'mc-text-align', '.mincalendar td ', 'text-align' );

		// Tag that related with post.
		$this->prepare( 'mc-tag' );

		// Update wp_options.
		$this->update( 'mincalendar-options', 'update-appearance' );

		/*
		 * Output to css file.
		 */
		if ( '' !== $this->css ) {
			// Create mincalendar.css.
			$fp = fopen( MC_CALENDAR_STYLESHEET, 'w' ) or die( __( 'Can not open file' ) );
			flock( $fp, LOCK_EX );
			fputs( $fp, $this->css );
			flock( $fp, LOCK_UN );
			fclose( $fp );
		}
		if ( false === empty( $_POST ) && '' == $this->css ) {
			if ( true === file_exists( MC_CALENDAR_STYLESHEET ) ) {
				unlink( MC_CALENDAR_STYLESHEET );
			}
		}

		/*
		 * Get existing setting.
		 */
		$this->options = (array) json_decode( get_option( 'mincalendar-options' ) );

		/*
		 * Display.
		 */
		?>
		<div class="wrap">
			<h2>Min Calendar Appearance</h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'update-appearance' ); ?>
				<table class="form-table">
					<?php
					echo $this->create_field(
						'sunday',
						'mc-sun',
						'(sunday label. e.g Sun)'
					);
					echo $this->create_field(
						'monday',
						'mc-mon',
						'(monday label. e.g Mon)'
					);
					echo $this->create_field(
						'tuesday',
						'mc-tue',
						'(tuesday label. e.g Tue)'
					);
					echo $this->create_field(
						'wednesday',
						'mc-wed',
						'(wednesday label. e.g Wed)'
					);
					echo $this->create_field(
						'thursday',
						'mc-thu',
						'(thursday label. e.g Tue)'
					);
					echo $this->create_field(
						'friday',
						'mc-fri',
						'(friday label. e.g Fri)'
					);
					echo $this->create_field(
						'saturday',
						'mc-sat',
						'(saturday label. Sat)'
					);
					echo $this->create_field(
						'sunday heading text color',
						'mincalendar-th-sun',
						'(e.g #000000)'
					);
					echo $this->create_field(
						'satday heading text color',
						'mincalendar-th-sat',
						'(e.g #000000)'
					);
					echo $this->create_field(
						'sunday heading background color',
						'mincalendar-th-sun-bg',
						'(e.g #000000)'
					);
					echo $this->create_field(
						'satday heading background color',
						'mincalendar-th-sat-bg',
						'(e.g #000000)'
					);
					echo $this->create_field(
						'sunday background color',
						'mincalendar-td-sun-bg',
						'(e.g #000000)'
					);
					echo $this->create_field(
						'satday background color',
						'mincalendar-td-sat-bg',
						'(e.g #000000)'
					);
					echo $this->create_field(
						'th text align',
						'mc-th-text-align',
						'(left or center or right)'
					);
					echo $this->create_field(
						'td text align',
						'mc-text-align',
						'(left or center or right)'
					);
					echo $this->create_field(
						'table width',
						'mc-table-width',
						'(table width. px or %. e.g 500px, 100%)'
					);
					echo $this->create_field(
						'th width',
						'mc-width-th',
						'(e.g 30px)'
					);
					echo $this->create_field(
						'th height',
						'mc-height-th',
						'(e.g 30px)'
					);
					echo $this->create_field(
						'td width',
						'mc-width-td',
						'(e.g 30px)'
					);
					echo $this->create_field(
						'td height',
						'mc-height-td',
						'(e.g 30px)'
					);
					echo $this->create_field(
						'th border',
						'mc-border-th',
						'(e.g #000000)'
					);
					echo $this->create_field(
						'td border',
						'mc-border-td',
						'(e.g #000000)'
					);
					echo $this->create_field(
						'1st value',
						'mc-value-1st',
						'(e.g -)'
					);
					echo $this->create_field(
						'2nd value',
						'mc-value-2nd',
						'(e.g o)'
					);
					echo $this->create_field(
						'3rd value',
						'mc-value-3rd',
						'(e.g x)'
					);
					echo $this->create_field(
						'background color of 1st value',
						'mc-bgcolor-1st',
						'(e.g #ffffff)'
					);
					echo $this->create_field(
						'background color of 2nd value',
						'mc-bgcolor-2nd',
						'(e.g #ffffff)'
					);
					echo $this->create_field(
						'background color of 3rd value',
						'mc-bgcolor-3rd',
						'(e.g #ffffff)'
					);
					echo $this->create_field(
						'tag attached for search post',
						'mc-tag',
						'(tag name not tag id, tag slug. delimiter is comma.)'
					);
					?>
				</table>
				<p class="submit"><input type="submit" class="button-primary"
										 value="<?php echo esc_attr( __( 'Save', 'mincalendar' ) ); ?>"/></p>
			</form>
		</div><!-- wrap -->
		<?php
	}
}
