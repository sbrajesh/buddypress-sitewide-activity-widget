<?php
/*
Plugin name: Buddypress Sitewide activity widget
Description: Sitewide Activity Widget for BuddyPress 1.7+ (Works with BuddyPress 1.6 too)
Author:Brajesh Singh
Author URI: http://buddydev.com
Plugin URI: http://buddydev.com/plugins/buddypress-sitewide-activity-widget/
Version: 1.1.7
Last Updated: June9, 2013
*/

/**
 * Define plugin constants
 */
 $bp_swa_dir =str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
 define('BP_SWA_DIR_NAME',$bp_swa_dir);//the directory name of swa widget
 define('SWA_PLUGIN_DIR',  plugin_dir_path(__FILE__));//the dir path of this plugin with a trailing slash
 define('BP_SWA_PLUGIN_URL',  plugin_dir_url(__FILE__));//the url of this plugin dir with a trailing slash
 /**
  * Sitewide Activity Helper Class
  * 
  * Loads the assets and does basic duties
  * @since v1.14
  * 
  */
class SWA_Helper{
    private static $instance;
    
       
    private function __construct() {
       //for enqueuing javascript
        add_action('wp_print_scripts',array($this,'load_js'));
        //load css
        add_action('wp_print_styles',array($this,'load_css'));
        
        //load text domain
        
        add_action ( 'bp_loaded', array($this,'load_textdomain'), 2 );
        
        //register widget
        //register the widget
        add_action( 'bp_include', array($this,'include_files' ));
        add_action( 'bp_loaded', array($this,'register_widgets' ));
        //load admin css on widgets.php
       add_action('admin_print_styles-widgets.php', array($this, 'load_admin_css'));
    }
    
    //factory for singleton instance
    public static function get_instance(){
        if(!isset(self::$instance))
                self::$instance=new self();
        
        return self::$instance;
        
    }
    
    function include_files(){
        include_once(SWA_PLUGIN_DIR.'ajax.php');
        include_once(SWA_PLUGIN_DIR.'widget.php');
        include_once(SWA_PLUGIN_DIR.'template-tags.php');
        
    }
    
    //get the list of admin users of a blog
    function get_admin_users_for_blog($blog_id) {
	global $wpdb,$current_blog;
        
        $meta_key=$wpdb->prefix."_capabilities";//.."_user_level";

	$role_sql="select user_id,meta_value from {$wpdb->usermeta} where meta_key='". $meta_key."'";

	$role=$wpdb->get_results($wpdb->prepare($role_sql),ARRAY_A);
	//clean the role
	$all_user=array_map("swa_serialize_role",$role);//we are unserializing the role to make that as an array

	foreach($all_user as $key=>$user_info)
		if($user_info['meta_value']['administrator']==1)//if the role is admin
			$admins[]=$user_info['user_id'];

	return $admins;

    }
    
    
    //register the widget
    function register_widgets(){
        add_action('bp_widgets_init', create_function('', 'return register_widget("BP_SWA_Widget");') );
    }



    //load js if required
    function load_js(){
        if(!is_admin())//load only on front end
            wp_enqueue_script('swa-js',BP_SWA_PLUGIN_URL.'swa.js',array('jquery'));
    }
    
    function load_css(){
        if(apply_filters('swa_load_css',true))//allow theme developers to override it
            wp_register_style ('swa-css', BP_SWA_PLUGIN_URL.'swa.css');
            wp_enqueue_style ('swa-css');
    }
    
      function load_admin_css(){
            wp_register_style ('swa-admin-css', BP_SWA_PLUGIN_URL.'swa-admin.css');
            wp_enqueue_style ('swa-admin-css');
    }
    
    //localization
    function load_textdomain() {
            $locale = apply_filters( 'swa_load_textdomain_get_locale', get_locale() );
            // if load .mo file
            if ( !empty( $locale ) ) {
                    $mofile_default = sprintf( '%slanguages/%s.mo', SWA_PLUGIN_DIR, $locale );
                    $mofile = apply_filters( 'swa_load_textdomain_mofile', $mofile_default );
                    // make sure file exists, and load it
                    if ( file_exists( $mofile ) ) {
                            load_textdomain( 'swa', $mofile );
                    }
            }
    }
    
    
}//end of helper class

//instantiate the singleton class
 SWA_Helper::get_instance();


function swa_serialize_role($roles){
	$roles['meta_value']=maybe_unserialize($roles['meta_value']);
return $roles;
}

//locate and load activity post form
function swa_show_post_form(){
    include SWA_PLUGIN_DIR.'post-form.php';//no inc_once because we may need form multiple times
}


 function swa_get_base_component_scope($include,$exclude){
     /* Fetch the names of components that have activity recorded in the DB */
		$components = BP_Activity_Activity::get_recorded_components();

                if(!empty($include))
                    $components=explode(",",$include);//array of component names

                if(!empty($exclude)){  //exclude all the
                    $components=array_diff((array)$components, explode(",",$exclude));//diff of exclude/recorded components
                    }
       return $components;
 }



 //helper function, return the single admin of this blog ok ok ok

 function swa_get_blog_admin_id(){
    
     $blog_id=  get_current_blog_id();
     $users=  SWA_Helper::get_admin_users_for_blog($blog_id);
     if(!empty($users))
         $users=$users[0];//just the first user
     return $users;
 }



/**
 * We do not use the code below in current version, I am leaving it in the hope that in future version, we may need this
 */
/*
 * 
 *
 * 
 *
	function bp_swa_activity_get_comments( $args = '' ) {
		global $activities_template, $bp;

		if ( !$activities_template->activity->children )
			return false;

		$comments_html = bp_swa_activity_recurse_comments( $activities_template->activity );

		return apply_filters( 'bp_swa_activity_get_comments', $comments_html );
	}
		function bp_swa_activity_recurse_comments( $comment ) {
			global $activities_template, $bp;

			if ( !$comment->children )
				return false;

			$content .= '<ul>';
			foreach ( (array)$comment->children as $comment ) {
				if ( !$comment->user_fullname )
					$comment->user_fullname = $comment->display_name;

				$content .= '<li id="swa-acomment-' . $comment->id . '">';
				$content .= '<div class="swa-acomment-avatar"><a href="' . bp_core_get_user_domain( $comment->user_id, $comment->user_nicename, $comment->user_login ) . '">' . bp_core_fetch_avatar( array( 'item_id' => $comment->user_id, 'width' => 25, 'height' => 25, 'email' => $comment->user_email ) ) . '</a></div>';
				$content .= '<div class="swa-acomment-meta"><a href="' . bp_core_get_user_domain( $comment->user_id, $comment->user_nicename, $comment->user_login ) . '">' . apply_filters( 'bp_get_member_name', $comment->user_fullname ) . '</a> &middot; ' . sprintf( __( '%s ago', 'swa' ), bp_core_time_since( strtotime( $comment->date_recorded ) ) );


				 Delete link 
				if ( $bp->loggedin_user->is_site_admin || $bp->loggedin_user->id == $comment->user_id )
					$content .= ' &middot; <a href="' . wp_nonce_url( $bp->root_domain . '/' . $bp->activity->slug . '/delete/?cid=' . $comment->id, 'bp_activity_delete_link' ) . '" class="delete acomment-delete">' . __( 'Delete', 'swa' ) . '</a>';

				$content .= '</div>';
				$content .= '<div class="swa-acomment-content">' . apply_filters( 'bp_get_activity_content', $comment->content ) . '</div>';

				$content .= bp_activity_recurse_comments( $comment );
				$content .= '</li>';
			}
			$content .= '</ul>';

			return apply_filters( 'bp_swa_activity_recurse_comments', $content );
		}


*/
                

?>