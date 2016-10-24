<?php
//locate and load activity post form
function swa_show_post_form () {
	
	include swa_helper()->get_path() . 'template/post-form.php'; //no inc_once because we may need form multiple times
}

function swa_get_base_component_scope ( $include, $exclude ) {
	/* Fetch the names of components that have activity recorded in the DB */
	$components = swa_get_recorded_components();

	if ( ! empty( $include ) ) {
		$components = explode( ',', $include ); //array of component names
	}
	
	if ( ! empty( $exclude ) ) {  //exclude all the
		$components = array_diff( (array) $components, explode( ',', $exclude ) ); //diff of exclude/recorded components
	}

	return $components;
}

//helper function, return the single admin of this blog ok ok ok

function swa_get_blog_admin_id () {

	$blog_id = get_current_blog_id();
	$users = SWA_Helper::get_admin_users_for_blog( $blog_id );

	if ( ! empty( $users ) ) {
		$users = $users[0]; //just the first user
	}
	
	return $users;
}

function swa_get_recorded_components () {

	$components = BP_Activity_Activity::get_recorded_components();

	return array_diff( (array) $components, array( 'members' ) );
}

function swa_scope_has_changed ( $new_scopes ) {

	$old_scope = $_REQUEST['original_scope'];
	
	if ( ! $old_scope ) {
		return false;
	}

	if ( $old_scope == $new_scopes ) {
		return false;
	}
	
	return true;
}


function swa_activity_content_body( $word_count = 0 ) {
	
	if( ! $word_count ) {
		echo bp_get_activity_content_body();
		return ;
	}
	
	$content = strip_tags( strip_shortcodes( bp_get_activity_content_body() ) );
	
	$content = wp_trim_words( $content, $word_count );
	
	echo wpautop( $content );
}