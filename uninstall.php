<?php
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

function mc_delete_plugin() {
	delete_option( 'mincalendar-options' );
	// 投稿タイプがmincalendarの投稿削除
	$posts = get_posts( array(
		'numberposts' => - 1,
		'post_type'   => 'mincalendar',
		'post_status' => 'any',
	) );
	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}

mc_delete_plugin();

