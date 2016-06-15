<?php

//ajax action handling	for the filters(blogs/profile/groups)
function swa_ajax_list_activity () {
	
	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

	$scope = isset( $_POST['scope'] ) ? $_POST['scope'] : '';

	$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;
	
	$max = isset( $_POST['max'] ) ? absint( $_POST['max'] ) : 200;

	$show_avatar = isset( $_POST['show_avatar'] ) ? $_POST['show_avatar'] : 'yes';
	$show_filters = isset( $_POST['show_filters'] ) ? $_POST['show_filters'] : 'yes';
	
	$show_content = isset( $_POST['show_content'] ) ? $_POST['show_content'] : 'yes';
	$activity_words_count = isset( $_POST['activity_words_count'] ) ? $_POST['activity_words_count']: 0;
	
	$included = isset( $_POST['included_components'] ) ? $_POST['included_components'] : false;
	$excluded = isset( $_POST['excluded_components'] ) ? $_POST['excluded_components'] : false;

	$is_personal = isset( $_POST['is_personal'] ) ? $_POST['is_personal'] : 'no';
	$is_blog_admin_activity = isset( $_POST['is_blog_admin_activity'] ) ? $_POST['is_blog_admin_activity'] : 'no';

	$show_post_form = isset( $_POST['show_post_form'] ) ? $_POST['show_post_form'] : 'no';
	//$show_filters=true,$included=false,$excluded=false
	bp_swa_list_activities( array(
		'per_page'					=> $per_page,
		'page'						=> $page,
		'scope'						=> $scope,
		'max'						=> $max,
		'show_avatar'				=> $show_avatar,
		'show_filters'				=> $show_filters,
		'included'					=> $included,
		'excluded'					=> $excluded,
		'is_personal'				=> $is_personal,
		'is_blog_admin_activity'	=> $is_blog_admin_activity,
		'show_activity_content'		=> $show_content,
		'activity_words_count'		=> $activity_words_count,
		'show_post_form'			=> $show_post_form
	) );

	exit( 0 );
}

add_action( 'wp_ajax_swa_fetch_content', 'swa_ajax_list_activity' );
add_action( 'wp_ajax_nopriv_swa_fetch_content', 'swa_ajax_list_activity' );


/* AJAX update posting */

function swa_post_update () {

	/* Check the nonce */
	check_admin_referer( 'swa_post_update', '_wpnonce_swa_post_update' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}

	if ( empty( $_POST['content'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'Please enter some content to post.', 'buddypress-sitewide-activity-widget' ) . '</p></div>';
		return false;
	}

	if ( empty( $_POST['object'] ) && function_exists( 'bp_activity_post_update' ) ) {
		$activity_id = bp_activity_post_update( array( 'content' => $_POST['content'] ) );
	} elseif ( $_POST['object'] == 'groups' ) {
		if ( !empty( $_POST['item_id'] ) && function_exists( 'groups_post_update' ) )
			$activity_id = groups_post_update( array( 'content' => $_POST['content'], 'group_id' => $_POST['item_id'] ) );
	} else
		$activity_id = apply_filters( 'bp_activity_custom_update', $_POST['object'], $_POST['item_id'], $_POST['content'] );

	if ( !$activity_id ) {
		echo '-1<div id="message" class="error"><p>' . __( 'There was a problem posting your update, please try again.', 'buddypress-sitewide-activity-widget' ) . '</p></div>';
		return false;
	}
	$show_avatar = isset( $_POST["show_avatar"] ) ? $_POST["show_avatar"] : "no";
	$show_content = isset( $_POST["show_content"] ) ? $_POST["show_content"] : "no";
	$activity_words_count = isset( $_POST['activity_words_count'] )? absint( $_POST['activity_words_count'] ) : 0;
	if ( bp_has_activities( 'include=' . $activity_id ) ) :
?>
		<?php while ( bp_activities() ) : bp_the_activity(); ?>
			<?php swa_activity_entry( 'show_avatar=' . $show_avatar . '&show_activity_content=' . $show_content . '&activity_words_count='.$activity_words_count  ) ?>
		<?php endwhile; ?>
		<?php

	endif;
	exit( 0 );
}

add_action( 'wp_ajax_swa_post_update', 'swa_post_update' ); //hook to post update
