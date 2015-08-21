<?php
/**
 * The Sitewide Activity Widget Class
 * 
 * 
 */
class BP_SWA_Widget extends WP_Widget {
    
	public function __construct() {
		parent::__construct( false, $name = __( '(BuddyPress) Site Wide Activity', 'swa' ) );
	}

	public function widget( $args, $instance ) {
		
        global $bp;
        
        if( $instance['is_personal'] == 'yes' && !is_user_logged_in() )
                return;//do  not show anything
		
        extract( $args );

        $included_components = $instance['included_components'];
        $excluded_components = $instance['excluded_components'];

        if( empty( $included_components ) )
            $included_components = swa_get_recorded_components();

        //let us assume that the scope is selected components
        $scope = $included_components;

        //if the user has excluded some of the components , let us remove it from scope
        if( !empty( $scope ) && is_array( $excluded_components ) )
            $scope = array_diff( $scope, $excluded_components );

        //ok, now we will create a comma separated list
        if( !empty( $scope ) )
            $scope = join( ',', $scope );


        if( !empty ( $included_components ) && is_array( $included_components ) )
            $included_components = join( ',', $included_components );

        
        if( !empty ( $excluded_components ) && is_array( $excluded_components ) )
            $excluded_components = join( ',', $excluded_components );

                 //find scope
                 

		echo $before_widget;
		echo $before_title
		   . $instance['title'] ;
                if($instance['show_feed_link'] == 'yes' )
		echo	 ' <a class="swa-rss" href="' . bp_get_sitewide_activity_feed_link() . '" title="' . __( 'Site Wide Activity RSS Feed', 'swa' ) . '">' . __( '[RSS]', 'swa' ) . '</a>';
		echo    $after_title;
		 
                $args = $instance;
                $args['page'] = 1;
                $args['scope'] = $scope;
                $args['max'] = $instance['max_items'];
                $args['show_filters'] = $instance["show_activity_filters"];
                $args['included'] = $included_components;
                $args['excluded'] = $excluded_components;
               //is_personal, is_blog_admin activity etc are set in the  
                
                   bp_swa_list_activities( $args );
		  ?>
		<input type='hidden' name='max' id='swa_max_items' value="<?php echo  $instance['max_items'];?>" />  
		<input type='hidden' name='max' id='swa_per_page' value="<?php echo  $instance['per_page'];?>" />  
		<input type='hidden' name='show_avatar' id='swa_show_avatar' value="<?php echo  $instance['show_avatar'];?>" />
		<input type='hidden' name='show_content' id='swa_show_content' value="<?php echo  $instance['show_activity_content'];?>" />
		<input type='hidden' name='show_filters' id='swa_show_filters' value="<?php echo  $instance['show_activity_filters'];?>" />
		<input type='hidden' name='included_components' id='swa_included_components' value="<?php echo  $included_components;?>" />
		<input type='hidden' name='excluded_components' id='swa_excluded_components' value="<?php echo  $excluded_components;?>" />
		<input type='hidden' name='is_personal' id='swa_is_personal' value="<?php echo  $instance['is_personal'];?>" />
		<input type='hidden' name='is_blog_admin_activity' id='swa_is_blog_admin_activity' value="<?php echo  $instance['is_blog_admin_activity'];?>" />
		<input type='hidden' name='show_post_form' id='swa_show_post_form' value="<?php echo  $instance['show_post_form'];?>" />
		<input type='hidden' name='swa_scope' id='swa_scope' value="<?php echo $scope;?>" />
		<input type='hidden' name='swa-original-scope' id='swa-original-scope' value="<?php echo $scope;?>" />

	<?php echo $after_widget; ?>
	<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
                
		$instance['title'] = strip_tags( $new_instance['title'] );
		
        $instance['max_items'] = strip_tags( $new_instance['max_items'] );
		$instance['per_page'] = strip_tags( $new_instance['per_page'] );
		
        $instance['show_avatar'] = $new_instance['show_avatar']; //avatar should be visible or not
		$instance['allow_reply'] = $new_instance['allow_reply']; //allow reply inside widget or not
		$instance['show_post_form'] = $new_instance['show_post_form']; //should we show the post form or not
		$instance['show_activity_filters'] = $new_instance['show_activity_filters'] ; //activity filters should be visible or not
		$instance['show_feed_link'] =  $new_instance['show_feed_link'] ; //feed link should be visible or not
        $instance["show_activity_content"]= $new_instance["show_activity_content"];

        $instance["included_components"] = $new_instance["included_components"];
        $instance["excluded_components"] = $new_instance["excluded_components"];

        $instance["is_blog_admin_activity"] = $new_instance["is_blog_admin_activity"];
        $instance["is_personal"] = $new_instance["is_personal"];


		return $instance;
	}

	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title'=>__('Site Wide Activities','swa'),'max_items' => 200, 'per_page' => 25,'is_personal'=>'no','is_blog_admin_activity'=>'no','show_avatar'=>'yes','show_activity_content'=>1,'show_feed_link'=>'yes','show_post_form'=>'no','allow_reply'=>'no','show_activity_filters'=>'yes','included_components'=>false,'excluded_components'=>false,'allow_comment' ) );
		$per_page = strip_tags( $instance['per_page'] );
		$max_items = strip_tags( $instance['max_items'] );
		$title = strip_tags( $instance['title'] );
                extract( $instance );
              
		?>
                
            <div class="swa-widgte-block">
                    <p><label for="bp-swa-title"><strong><?php _e('Title:', 'swa'); ?> </strong><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%" /></label></p>
                    <p><label for="bp-swa-per-page"><?php _e('Number of Items Per Page:', 'swa'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'per_page' ); ?>" name="<?php echo $this->get_field_name( 'per_page' ); ?>" type="text" value="<?php echo esc_attr( $per_page ); ?>" style="width: 30%" /></label></p>
                    <p><label for="bp-swa-max"><?php _e('Max items to show:', 'swa'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_items' ); ?>" name="<?php echo $this->get_field_name( 'max_items' ); ?>" type="text" value="<?php echo esc_attr( $max_items ); ?>" style="width: 30%" /></label></p>
            </div>  
            <div class="swa-widgte-block">
                <p><label for="bp-swa-is-personal"><strong><?php _e("Limit to Logged In user's activity:", 'swa'); ?></strong>
                       <label for="<?php echo $this->get_field_id( 'is_personal' ); ?>_yes" > <input id="<?php echo $this->get_field_id( 'is_personal' ); ?>_yes" name="<?php echo $this->get_field_name( 'is_personal' ); ?>" type="radio" <?php if($is_personal=='yes') echo "checked='checked'";?> value="yes" style="width: 10%" />Yes</label>
                       <label for="<?php echo $this->get_field_id( 'is_personal' ); ?>_no" > <input  id="<?php echo $this->get_field_id( 'is_personal' ); ?>_no" name="<?php echo $this->get_field_name( 'is_personal' ); ?>" type="radio" <?php if($is_personal!=='yes') echo "checked='checked'";?> value="no" style="width: 10%" />No</label>

                    </label>
               </p>
                <p><label for="bp-swa-is-blog-admin-activity"><strong><?php _e("List My Activity Only:", 'swa'); ?></strong>
                       <label for="<?php echo $this->get_field_id( 'is_blog_admin_activity' ); ?>_yes" > <input id="<?php echo $this->get_field_id( 'is_blog_admin_activity' ); ?>_yes" name="<?php echo $this->get_field_name( 'is_blog_admin_activity' ); ?>" type="radio" <?php if($is_blog_admin_activity=='yes') echo "checked='checked'";?> value="yes" style="width: 10%" />Yes</label>
                       <label for="<?php echo $this->get_field_id( 'is_blog_admin_activity' ); ?>_no" > <input  id="<?php echo $this->get_field_id( 'is_blog_admin_activity' ); ?>_no" name="<?php echo $this->get_field_name( 'is_blog_admin_activity' ); ?>" type="radio" <?php if($is_blog_admin_activity!=='yes') echo "checked='checked'";?> value="no" style="width: 10%" />No</label>

                    </label>
               </p>
            </div>
            <div class="swa-widgte-block">
                <p><label for="bp-swa-show-avatar"><strong><?php _e('Show Avatar:', 'swa'); ?></strong>
                    <label for="<?php echo $this->get_field_id( 'show_avatar' ); ?>_yes" > <input id="<?php echo $this->get_field_id( 'show_avatar' ); ?>_yes" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>" type="radio" <?php if($show_avatar=='yes') echo "checked='checked'";?> value="yes" style="width: 10%" />Yes</label>
                    <label for="<?php echo $this->get_field_id( 'show_avatar' ); ?>_no" > <input  id="<?php echo $this->get_field_id( 'show_avatar' ); ?>_no" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>" type="radio" <?php if($show_avatar!=='yes') echo "checked='checked'";?> value="no" style="width: 10%" />No</label>

                 </label>
                </p>
                <p><label for="bp-swa-show-feed-link"><?php _e('Show Feed Link:', 'swa'); ?>
                        <label for="<?php echo $this->get_field_id( 'show_feed_link' ); ?>_yes" > <input id="<?php echo $this->get_field_id( 'show_feed_link' ); ?>_yes" name="<?php echo $this->get_field_name( 'show_feed_link' ); ?>" type="radio" <?php if($show_feed_link=='yes') echo "checked='checked'";?> value="yes" style="width: 10%" />Yes</label>
                        <label for="<?php echo $this->get_field_id( 'show_feed_link' ); ?>_no" > <input  id="<?php echo $this->get_field_id( 'show_feed_link' ); ?>_no" name="<?php echo $this->get_field_name( 'show_feed_link' ); ?>" type="radio" <?php if($show_feed_link!=='yes') echo "checked='checked'";?> value="no" style="width: 10%" />No</label>

                     </label>
                </p>
                <p><label for="bp-swa-show-activity-content"><?php _e('Show Activity Content:', 'swa'); ?>
                        <label for="<?php echo $this->get_field_id( 'show_activity_content' ); ?>_yes" > <input id="<?php echo $this->get_field_id( 'show_activity_content' ); ?>_yes" name="<?php echo $this->get_field_name( 'show_activity_content' ); ?>" type="radio" <?php echo checked($show_activity_content,1) ?> value="1" style="width: 10%" />Yes</label>
                        <label for="<?php echo $this->get_field_id( 'show_activity_content' ); ?>_no" > <input  id="<?php echo $this->get_field_id( 'show_activity_content' ); ?>_no" name="<?php echo $this->get_field_name( 'show_activity_content' ); ?>" type="radio" <?php echo checked($show_activity_content,0) ?> value="0" style="width: 10%" />No</label>

                     </label>
                </p>
                <p><label for="bp-swa-show-post-form"><strong><?php _e('Show Post Form', 'swa'); ?></strong>
                        <label for="<?php echo $this->get_field_id( 'show_post_form' ); ?>_yes" > <input id="<?php echo $this->get_field_id( 'show_post_form' ); ?>_yes" name="<?php echo $this->get_field_name( 'show_post_form' ); ?>" type="radio" <?php if($show_post_form=='yes') echo "checked='checked'";?> value="yes" style="width: 10%" />Yes</label>
                        <label for="<?php echo $this->get_field_id( 'show_post_form' ); ?>_no" > <input  id="<?php echo $this->get_field_id( 'show_post_form' ); ?>_no" name="<?php echo $this->get_field_name( 'show_post_form' ); ?>" type="radio" <?php if($show_post_form!=='yes') echo "checked='checked'";?> value="no" style="width: 10%" />No</label>

                     </label>
                </p>
                <!-- <p><label for="bp-swa-show-reply-link"><?php _e('Allow reply to activity item:', 'swa'); ?>
                        <label for="<?php echo $this->get_field_id( 'allow_reply' ); ?>_yes" > <input id="<?php echo $this->get_field_id( 'allow_reply' ); ?>_yes" name="<?php echo $this->get_field_name( 'allow_reply' ); ?>" type="radio" <?php if($show_feed_link=='yes') echo "checked='checked'";?> value="yes" style="width: 10%" />Yes</label>
                        <label for="<?php echo $this->get_field_id( 'allow_reply' ); ?>_no" > <input  id="<?php echo $this->get_field_id( 'allow_reply' ); ?>_no" name="<?php echo $this->get_field_name( 'allow_reply' ); ?>" type="radio" <?php if($show_feed_link!=='yes') echo "checked='checked'";?> value="no" style="width: 10%" />No</label>

                     </label>
                </p>-->
                <p><label for="bp-swa-show-activity-filters"><strong><?php _e('Show Activity Filters:', 'swa'); ?></strong>
                        <label for="<?php echo $this->get_field_id( 'show_activity_filters' ); ?>_yes" > <input id="<?php echo $this->get_field_id( 'show_activity_filters' ); ?>_yes" name="<?php echo $this->get_field_name( 'show_activity_filters' ); ?>" type="radio" <?php if($show_activity_filters=='yes') echo "checked='checked'";?> value="yes" style="width: 10%" />Yes</label>
                        <label for="<?php echo $this->get_field_id( 'show_activity_filters' ); ?>_no" > <input  id="<?php echo $this->get_field_id( 'show_activity_filters' ); ?>_no" name="<?php echo $this->get_field_name( 'show_activity_filters' ); ?>" type="radio" <?php if($show_activity_filters!=='yes') echo "checked='checked'";?> value="no" style="width: 10%" />No</label>

                     </label>
                </p>

            </div>
            <div class="swa-widgte-block">
                <p><label for="bp-swa-included-filters"><strong><?php _e('Include only following Filters:', 'swa'); ?></strong></label></p>
                <p> 
                        <?php $recorded_components = swa_get_recorded_components();
                            foreach((array)$recorded_components as $component):?>
                                <label for="<?php echo $this->get_field_id( 'included_components' ).'_'.$component ?>" ><?php echo ucwords($component);?> <input id="<?php echo $this->get_field_id( 'included_components' ).'_'.$component ?>" name="<?php echo $this->get_field_name( 'included_components' ); ?>[]" type="checkbox" <?php if(is_array($included_components)&&in_array($component, $included_components)) echo "checked='checked'";?> value="<?php echo $component;?>" style="width: 10%" /></label>
                        <?php endforeach;?>

                </p>
            </div>
               <div class="swa-widgte-block">
                   
                <p><label for="bp-swa-included-filters"><strong><?php _e('Exclude following Components activity', 'swa'); ?></strong></label></p>
                    <p>
                        <?php //$recorded_components = BP_Activity_Activity::get_recorded_components();
                            foreach( (array)$recorded_components as $component ):?>
                                <label for="<?php echo $this->get_field_id( 'excluded_components' ).'_'.$component ?>" ><?php echo ucwords($component);?> <input id="<?php echo $this->get_field_id( 'excluded_components' ).'_'.$component ?>" name="<?php echo $this->get_field_name( 'excluded_components' ); ?>[]" type="checkbox" <?php if(is_array($excluded_components)&&in_array($component, $excluded_components)) echo "checked='checked'";?> value="<?php echo $component;?>" style="width: 10%" /></label>
                        <?php endforeach;?>

                    </p>
               </div>
	<?php
	}
}
