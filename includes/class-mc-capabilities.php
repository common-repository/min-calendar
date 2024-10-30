<?php
/**
 * This is WordPress plugin Min Calendar
 *
 * @package MinCalendar
 */

/**
 * Manage capability.
 *
 * @package MinCalendar
 */
class MC_Capabilities {
	/**
	 * MC_Capabilities constructor.
	 */
	function __construct() {
		add_filter( 'map_meta_cap', array( &$this, 'map_meta_cap' ), 10, 4 );
	}

	/**
	 * Set Min Calendar capability.
	 *
	 * @param array  $caps Capability array.
	 * @param string $cap  Target capability.
	 *
	 * @return array
	 */
	public function map_meta_cap( $caps, $cap ) {
		$meta_caps = array(
			'edit'     => MC_ADMIN_READ_WRITE_CAPABILITY,
			'edit_all' => MC_ADMIN_READ_WRITE_CAPABILITY,
			'read'     => MC_ADMIN_READ_CAPABILITY,
			'delete'   => MC_ADMIN_READ_WRITE_CAPABILITY,
		);
		$meta_caps = apply_filters( 'mc_map_meta_cap', $meta_caps );
		$caps      = array_diff( $caps, array_keys( $meta_caps ) );
		if ( isset( $meta_caps[ $cap ] ) ) {
			$caps[] = $meta_caps[ $cap ];
		}

		return $caps;
	}
}
