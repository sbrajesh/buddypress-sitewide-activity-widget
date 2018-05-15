<?php
/**
 * Load the Widget Activity post form
 */
function swa_show_post_form() {
	include swa_helper()->get_path() . 'template/post-form.php'; //no inc_once because we may need form multiple times
}

/**
 * Get an array of recorded components which contain $include and do not contain the components from $exclude
 *
 * @param array $include
 * @param array $exclude
 *
 * @return array
 */
function swa_get_base_component_scope( $include, $exclude ) {
	$components = swa_get_recorded_components();

	if ( ! empty( $include ) ) {
		$components = explode( ',', $include ); //array of component names
	}

	if ( ! empty( $exclude ) ) {  //exclude all the
		$components = array_diff( (array) $components, explode( ',', $exclude ) ); //diff of exclude/recorded components
	}

	return $components;
}

/**
 * Get the Id of the admin(Any one of the admin) of current blog
 *
 * @return int
 */
function swa_get_blog_admin_id() {

	$blog_id = get_current_blog_id();
	$users   = SWA_Helper::get_admin_users_for_blog( $blog_id );

	if ( ! empty( $users ) ) {
		$users = $users[0]; //just the first user
	}

	return $users;
}

/**
 * Get an array of recorded components
 *
 * @return array
 */
function swa_get_recorded_components() {

	$components = BP_Activity_Activity::get_recorded_components();

	return array_diff( (array) $components, array( 'members' ) );
}

/**
 * Check if the given request has scope changed?
 *
 * @param $new_scopes
 *
 * @return bool
 */
function swa_scope_has_changed( $new_scopes ) {

	$old_scope = $_REQUEST['original_scope'];

	if ( ! $old_scope ) {
		return false;
	}

	if ( $old_scope == $new_scopes ) {
		return false;
	}

	return true;
}

/**
 * Output the content body of an activity
 *
 * @param int $word_count how may words to limit
 */
function swa_activity_content_body( $word_count = 0 ) {

	if ( ! $word_count ) {
		echo bp_get_activity_content_body();

		return;
	}

	$content = strip_tags( strip_shortcodes( bp_get_activity_content_body() ) );

	$content = wp_trim_words( $content, $word_count );

	echo wpautop( $content );
}

/**
 * Post an activity update.
 *
 * @param array $args post args.
 *
 * @return bool|int
 */
function swa_post_activity_update( $args ) {

	$content = isset( $args['content'] ) ? trim( $args['content'] ) : '';

	if ( empty( $content ) && ! empty( $args['mpp-attached-media'] ) ) {
		return _swa_mpp_update_activity( $args );
	} else {
		return _swa_bp_update_activity( $args );
	}

}

/**
 * Post a BuddyPress activity.
 *
 * @param array $args args.
 *
 * @return bool|int
 */
function _swa_bp_update_activity( $args ) {
	$activity_id = 0;
	if ( empty( $args['object'] ) && function_exists( 'bp_activity_post_update' ) ) {
		$activity_id = bp_activity_post_update( array( 'content' => $args['content'] ) );
	} elseif ( 'groups' === $args['object'] ) {
		if ( ! empty( $args['item_id'] ) && function_exists( 'groups_post_update' ) ) {
			$activity_id = groups_post_update( array(
				'content'  => $args['content'],
				'group_id' => $args['item_id'],
			) );
		}
	} else {
		$activity_id = apply_filters( 'bp_activity_custom_update', $args['object'], $args['item_id'], $args['content'] );
	}

	return $activity_id;
}

/**
 * Post update in case MediaPress is active and there are attached media with empty content.
 *
 * @param array $args args.
 *
 * @return bool|int
 */
function _swa_mpp_update_activity( $args = array() ) {
	$activity_id = 0;
	if ( empty( $args['object'] ) && function_exists( 'mpp_activity_post_update' ) ) {
		$activity_id = mpp_activity_post_update( array( 'content' => $args['content'] ) );
	} elseif ( 'groups' === $args['object'] ) {
		if ( ! empty( $args['item_id'] ) && function_exists( 'mpp_activity_post_group_update' ) ) {
			$activity_id = mpp_activity_post_group_update( array(
				'content'  => $args['content'],
				'group_id' => $args['item_id'],
			) );
		}
	} else {
		$activity_id = apply_filters( 'bp_activity_custom_update', $args['object'], $args['item_id'], $args['content'] );
	}

	return $activity_id;
}

