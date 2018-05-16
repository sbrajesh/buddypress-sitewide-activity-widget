<?php

// ajax action handling	for the filters(blogs/profile/groups).
function swa_ajax_list_activity() {

	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

	$scope = isset( $_POST['scope'] ) ? $_POST['scope'] : '';

	$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;

	$max = isset( $_POST['max'] ) ? absint( $_POST['max'] ) : 200;

	$show_avatar  = isset( $_POST['show_avatar'] ) ? $_POST['show_avatar'] : 1;
	$show_filters = isset( $_POST['show_filters'] ) ? $_POST['show_filters'] : 1;

	$show_content         = isset( $_POST['show_content'] ) ? $_POST['show_content'] : 1;
	$activity_words_count = isset( $_POST['activity_words_count'] ) ? $_POST['activity_words_count'] : 0;

	$included = isset( $_POST['included_components'] ) ? $_POST['included_components'] : false;
	$excluded = isset( $_POST['excluded_components'] ) ? $_POST['excluded_components'] : false;

	$is_personal            = isset( $_POST['is_personal'] ) ? $_POST['is_personal'] : 0;
	$is_blog_admin_activity = isset( $_POST['is_blog_admin_activity'] ) ? $_POST['is_blog_admin_activity'] : 0;

	$show_post_form = isset( $_POST['show_post_form'] ) ? $_POST['show_post_form'] : 0;
	$allow_comment = isset( $_POST['allow_comment'] ) ? absint( $_POST['allow_comment'] ) : 0;
	$allow_delete = isset( $_POST['allow_delete'] ) ? absint( $_POST['allow_delete'] ) : 0;
	//$show_filters=true,$included=false,$excluded=false
	bp_swa_list_activities( array(
		'per_page'               => $per_page,
		'page'                   => $page,
		'scope'                  => $scope,
		'max'                    => $max,
		'show_avatar'            => $show_avatar,
		'show_filters'           => $show_filters,
		'included'               => $included,
		'excluded'               => $excluded,
		'is_personal'            => $is_personal,
		'is_blog_admin_activity' => $is_blog_admin_activity,
		'show_activity_content'  => $show_content,
		'activity_words_count'   => $activity_words_count,
		'show_post_form'         => $show_post_form,
		'allow_comment'          => $allow_comment,
		'allow_delete'          => $allow_delete,
	) );

	exit( 0 );
}

add_action( 'wp_ajax_swa_fetch_content', 'swa_ajax_list_activity' );
add_action( 'wp_ajax_nopriv_swa_fetch_content', 'swa_ajax_list_activity' );


/* AJAX update posting */

function swa_post_update() {

	/* Check the nonce */
	check_admin_referer( 'swa_post_update', '_wpnonce_swa_post_update' );

	if ( ! is_user_logged_in() ) {
		echo '-1';

		return false;
	}

	// if content is empty and it is not MediaPress update. Fail.
	if ( empty( $_POST['content'] ) && empty( $_POST['mpp-attached-media'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'Please enter some content to post.', 'buddypress-sitewide-activity-widget' ) . '</p></div>';

		return false;
	}

	$activity_id = swa_post_activity_update( $_POST );

	if ( ! $activity_id ) {
		echo '-1<div id="message" class="error"><p>' . __( 'There was a problem posting your update, please try again.', 'buddypress-sitewide-activity-widget' ) . '</p></div>';

		return false;
	}
	$show_avatar          = isset( $_POST["show_avatar"] ) ? $_POST["show_avatar"] : 0;
	$show_content         = isset( $_POST["show_content"] ) ? $_POST["show_content"] : 0;
	$activity_words_count = isset( $_POST['activity_words_count'] ) ? absint( $_POST['activity_words_count'] ) : 0;
	if ( bp_has_activities( 'include=' . $activity_id ) ) :
		?>
		<?php while ( bp_activities() ) : bp_the_activity(); ?>
		<?php swa_activity_entry( array(
			'show_avatar'           => $show_avatar,
			'show_activity_content' => $show_content,
			'activity_words_count'  => $activity_words_count,
			'allow_comment'         => isset( $_POST['allow_comment'] ) ? absint( $_POST['allow_comment'] ) : 0,
			'allow_delete'         => isset( $_POST['allow_delete'] ) ? absint( $_POST['allow_delete'] ) : 0,
		) ); ?>
	<?php endwhile; ?>
		<?php

	endif;
	exit( 0 );
}

add_action( 'wp_ajax_swa_post_update', 'swa_post_update' ); // hook to post update
