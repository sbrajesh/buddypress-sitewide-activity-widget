<?php

/**
 * template/output control functions
 */


function bp_swa_list_activities( $args ){
    $defaults = array(
            'per_page'                  => 10,
            'page'                      => 1,
            'scope'                     => '',
            'max'                       => 20,
            'show_avatar'               => 'yes',
            'show_filters'              => 'yes',
            'included'                  => false,
            'excluded'                  => false,
            'is_personal'               => 'no',
            'is_blog_admin_activity'    => 'no',
            'show_post_form'            => 'no'
        );
    
    $args = wp_parse_args( $args, $defaults );
    extract( $args );
    
//check for the scope of activity
//is it the activity of logged in user/blog admin
//logged in user over rides blog admin
     global $bp;
     $primary_id = '';
     
     if( function_exists( 'bp_is_group' ) && bp_is_group () )
         $primary_id = null;
     
     $user_id = false;//for limiting to users

     if( $is_personal == 'yes' )
        $user_id = get_current_user_id ();
     elseif( $is_blog_admin_activity == 'yes' )
        $user_id = swa_get_blog_admin_id();
     else if( bp_is_user() )
        $user_id = null;
    
    $components_scope = swa_get_base_component_scope( $included, $excluded );

    $components_base_scope = '';

    if( !empty( $components_scope ) )
        $components_base_scope = join( ',', $components_scope );

   ?>
      <div class='swa-wrap'>
          <?php if( is_user_logged_in() && $show_post_form == 'yes' )
                swa_show_post_form();
          ?>
          
          <?php if( $show_filters == 'yes' ):?>
                <ul id="activity-filter-links">
                        <?php swa_activity_filter_links( 'scope=' . $scope . '&include=' . $included . '&exclude=' . $excluded ); ?>
                </ul>
                <div class="clear"></div>
          <?php endif;?>
        
          <?php if ( bp_has_activities( 'type=sitewide&max=' . $max . '&page='. $page . '&per_page=' . $per_page . '&object=' . $scope . "&user_id=" . $user_id . "&primary_id=" . $primary_id . '&scope=0' ) ) : ?>

                <div class="swa-pagination ">
                        <div class="pag-count" id="activity-count">
                                <?php bp_activity_pagination_count() ?>
                        </div>

                        <div class="pagination-links" id="activity-pag">
                                &nbsp; <?php bp_activity_pagination_links() ?>
                        </div>
                    <div class="clear" ></div>
                </div>


                <div class="clear" ></div>
                
                <ul  class="site-wide-stream swa-activity-list">
                    <?php while ( bp_activities() ) : bp_the_activity(); ?>
                        <?php swa_activity_entry($args);?>
                    <?php endwhile; ?>
               </ul>

	<?php else: ?>

                <div class="widget-error">
                    <?php if( $is_personal == 'yes' )
                            $error = sprintf( __( 'You have no recent %s activity.', 'swa' ), $scope );
                        else
                            $error = __( 'There has been no recent site activity.', 'swa' );
                        ?>
                   <?php echo $error; ?>
                </div>
	<?php endif;?>
     </div>
     
<?php
}

//individual entry in the activity stream
function swa_activity_entry( $args ){
    $args = wp_parse_args( $args );
    extract( $args );
    $allow_comment = false;//we can provide an option in future to allow commenting
    ?>
 
    <?php do_action( 'bp_before_activity_entry' ) ?>
    <li class="<?php bp_activity_css_class() ?>" id="activity-<?php bp_activity_id() ?>">
            <?php if( $show_avatar == 'yes' ): ?>
                <div class="swa-activity-avatar">
                      <a href="<?php bp_activity_user_link() ?>">
                              <?php bp_activity_avatar( 'type=thumb&width=50&height=50' ) ?>
                      </a>

              </div>
           <?php endif;?>
          
        <div class="swa-activity-content">
		<div class="swa-activity-header">
			<?php bp_activity_action() ?>
		</div>

		<?php if ( bp_activity_has_content()&& $show_activity_content ) : ?>
			<div class="swa-activity-inner">
				<?php bp_activity_content_body() ?>
			</div>
		<?php endif; ?>

	<?php do_action( 'bp_activity_entry_content' ) ?>
	<div class="swa-activity-meta">
            <?php if ( is_user_logged_in() && bp_activity_can_comment()&& $allow_comment ) : ?>
                    <a href="<?php bp_activity_comment_link() ?>" class="acomment-reply" id="acomment-comment-<?php bp_activity_id() ?>"><?php _e( 'Reply', 'buddypress' ) ?> (<span><?php bp_activity_comment_count() ?></span>)</a>
            <?php endif; ?>
           
            <?php do_action( 'bp_activity_entry_meta' ) ?>
        </div>
	<div class="clear" ></div>
    </div>
    <?php if ( 'activity_comment' == bp_get_activity_type() ) : ?>
	<div class="swa-activity-inreplyto">
            <strong><?php _e( 'In reply to', 'swa' ) ?></strong> - <?php bp_activity_parent_content() ?> &middot;
            <a href="<?php bp_activity_thread_permalink() ?>" class="view" title="<?php _e( 'View Thread / Permalink', 'swa' ) ?>"><?php _e( 'View', 'swa' ) ?></a>
	</div>
    <?php endif; ?>
    <?php if ( bp_activity_can_comment() && $show_activity_content ) : 
        
    if( ! $allow_comment ) {
        //hide reply link
        add_filter( 'bp_activity_can_comment_reply', '__return_false' );
    }
?>
        <div class="swa-activity-comments">
            <?php bp_activity_comments() ?>
            <?php if ( is_user_logged_in() && $allow_comment ) : ?>
			<form action="<?php bp_activity_comment_form_action() ?>" method="post" id="swa-ac-form-<?php bp_activity_id() ?>" class="swa-ac-form"<?php bp_activity_comment_form_nojs_display() ?>>
				<div class="ac-reply-avatar"><?php bp_loggedin_user_avatar( 'width=' . BP_AVATAR_THUMB_WIDTH . '&height=' . BP_AVATAR_THUMB_HEIGHT ) ?></div>
				<div class="ac-reply-content">
					<div class="ac-textarea">
						<textarea id="swa-ac-input-<?php bp_activity_id() ?>" class="ac-input" name="ac_input_<?php bp_activity_id() ?>"></textarea>
					</div>
					<input type="submit" name="swa_ac_form_submit" value="<?php _e( 'Post', 'buddypress' ) ?> &rarr;" /> &nbsp; <?php _e( 'or press esc to cancel.', 'buddypress' ) ?>
					<input type="hidden" name="comment_form_id" value="<?php bp_activity_id() ?>" />
				</div>
				<?php wp_nonce_field( 'new_activity_comment', '_wpnonce_new_activity_comment' ) ?>
			</form>
			<?php endif; ?>
	</div>
        <?php if( !$allow_comment ){
            //remove filter
            remove_filter( 'bp_activity_can_comment_reply', '__return_false' );
        }?>
    <?php endif; ?>
</li>
<?php do_action( 'bp_after_swa_activity_entry' ); ?>

<?php
}



/** Fix error for implode issue*/
//compat with filter links, will remove when bp adds it

function swa_activity_filter_links( $args = false ) {//copy of bp_activity_filter_link
	echo swa_get_activity_filter_links( $args );
}
	function swa_get_activity_filter_links( $args = false ) {
		global $activities_template, $bp;
                
                
                
        $link = '';
		$defaults = array(
			'style' => 'list'
		);
            //check scope, if not single entiry

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		$components = swa_get_base_component_scope( $include, $exclude );
                 
                
        if ( !$components )
			return false;
                 
		foreach ( (array) $components as $component ) {
			/* Skip the activity comment filter */
			if ( 'activity' == $component )
				continue;

			if ( isset( $_GET['afilter'] ) && $component == $_GET['afilter'] )
				$selected = ' class="selected"';
			else
				$selected = '';

			$component = esc_attr( $component );
                        //if($component=='xprofile')
                            //$component='profile';
                        
			switch ( $style ) {
				case 'list':
					$tag = 'li';
					$before = '<li id="afilter-' . $component . '"' . $selected . '>';
					$after = '</li>';
				break;
				case 'paragraph':
					$tag = 'p';
					$before = '<p id="afilter-' . $component . '"' . $selected . '>';
					$after = '</p>';
				break;
				case 'span':
					$tag = 'span';
					$before = '<span id="afilter-' . $component . '"' . $selected . '>';
					$after = '</span>';
				break;
			}

			$link = add_query_arg( 'afilter', $component );
			$link = remove_query_arg( 'acpage' , $link );

			$link = apply_filters( 'bp_get_activity_filter_link_href', $link, $component );

			/* Make sure all core internal component names are translatable */
			$translatable_components = array( __( 'profile', 'swa'), __( 'friends', 'swa' ), __( 'groups', 'swa' ), __( 'status', 'swa' ), __( 'blogs', 'swa' ) );

			$component_links[] = $before . '<a href="' . esc_attr( $link ) . '">' . ucwords( __( $component, 'swa' ) ) . '</a>' . $after;
		}

		

		
                 
                     
        if ( !empty( $_REQUEST['scope']) && swa_scope_has_changed( $_REQUEST['scope'] ) ){
            
            $link = remove_query_arg( 'afilter' , $link );            
            $link = $link."?afilter=";
        			
            $component_links[] = "<{$tag} id='afilter-clear'><a href='". esc_attr( $link ) . "'>" . __( 'Clear Filter', 'swa' ) . "</a></{$tag}>";
                     
            
        }

                     
        if( !empty( $component_links ) )
            return apply_filters( 'swa_get_activity_filter_links', implode( "\n", $component_links ), $component_links );
               
                 
        return false;
	}

