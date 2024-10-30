<?php
/*
Plugin Name: Memoria Ticket System
Description: A simple and user friendly ticket system to use for support, contacting or for reporting buggs. It is possible to create different categories and departments and for each ticket you can set which user is in charge for solving the issue. It is possible to attach a file to a ticket and rate a ticket when it is closed. It is also possible to rate each comment on a ticket to help the support to understand the issuer better.
Plugin URI: http://www.memoria.se
Version: 1.0.4
Author: Vatan Bytyqi
Author URI: http://www.memoria.se
*/

function sts_install (){
// enable comments
    global $wpdb;
    $scomment_query ="UPDATE ".$wpdb->prefix."posts SET comment_status = 'open' WHERE post_type = 'sts-ticket'";
    $wpdb->query($scomment_query);

    $upload_dir = wp_upload_dir();
    if (!file_exists($upload_dir["basedir"]."/sts-ticket/index.htm")) {
        if (!file_exists($upload_dir["basedir"]."/sts-ticket/")){
            mkdir($upload_dir["basedir"]."/sts-ticket/", 0755, true);
        }
        $myfile = fopen($upload_dir["basedir"]."/sts-ticket/index.htm", "w");
        fclose($myfile);
    }
}

register_activation_hook( __FILE__, 'sts_install' );



define('STSDIR', plugin_dir_path( __FILE__ )); 
define('STSURL', plugin_dir_url( __FILE__ )); 
define('STSICON', STSURL.'images/sts_icon_16.png'); 
define('STSICON_32', STSURL.'/images/sts_icon.png'); 
include_once ABSPATH . 'wp-admin/includes/file.php';


add_action('wp_head', 'sts_add_style_code');
add_action('admin_head', 'sts_add_style_code'); 
function sts_add_style_code() {
    global $post;
    #wp_enqueue_script( 'jquery', plugins_url( 'includes/jquery/jquery-1.10.2.js', __FILE__ ), array(), '1.10.2', false );
    if( (is_admin() && $_GET['post_type'] = "sts-ticket") 
        || (is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'sts_memoria_ticket_system') ) 
        || (get_post_type( $post )=="sts-ticket") ) {
        echo '<style type="text/css">';
        echo str_replace("[%plugin_url]", STSURL, sts_get_plugin_style());
        echo "</style>";
    }
}
#wp_enqueue_style('memoria-ticket-system', plugins_url( 'default_style.css', __FILE__ ) );


function sts_custom_post_type() {
global $wpdb;
$label_all = array(
        'name'               => _x( 'Tickets', 'post type general name' ),
        'singular_name'      => _x( 'Ticket', 'post type singular name' ),
        'add_new'            => _x( 'New ticket', 'ticket' ),
        'add_new_item'       => __( 'Create new Ticket' ),
        'edit_item'          => __( 'Edit ticket' ),
        'new_item'           => __( 'New ticket' ),        
        'view_item'          => __( 'View ticket' ),
        'search_items'       => __( 'Search tickets' ),
        'not_found'          => __( 'No tickets found' ),
        'not_found_in_trash' => __( 'No tickets found in the Trash' ), 
        'parent_item_colon'  => '',
        'menu_name'          => 'Memoria Ticket System'
    );
    if( current_user_can( 'administrator' ) ){
        $label_subscriber= array( 'all_items' => __( 'All tickets' ),);
    }
    else {
        $label_subscriber = array();    
    }
    $labels = array_merge((array)$label_all, (array)$label_subscriber);
    
    $args = array(
        'labels'        => $labels,
        'supports' => 'revisions',
        'description'   => 'Displays tickets and status.',
        'public'        => true,
        'menu_position' => '75.8398',
        'menu_icon' => STSICON,
        'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
        'has_archive'   => false,   
        'show_ui'=> true,
        'exclude_from_search'=> true,
        'capability_type' => 'sts-ticket',
        'capabilities' => array(
                'publish_posts' => 'sts_publish',
                'edit_post' => 'sts_edit_post',
                'edit_posts' => 'sts_edit_posts',
                'edit_others_posts' => 'sts_edit_others',
                'delete_posts' => 'sts_delete_posts',
                'delete_post' => 'sts_delete_post',
                'delete_others_posts' => 'sts_delete_others_posts',
                'read_private_posts' => 'sts_read_private_posts',                
                'read_post' => 'sts_read_post',
                'read_posts' => 'sts_read_posts',
                'read' => 'sts_read',
            ),      
        'rewrite' => array("slug" => "sts-ticket"), 
        'register_meta_box_cb' => 'sts_ticket_metaboxes',
    );
    register_post_type( 'sts-ticket', $args ); 
    flush_rewrite_rules();  
        
        
// ADD Capabilities
  $caps = array(
    'read',
    'read_post', 
    'read_posts', 
    'read_private_posts',
    'edit_post', 
    'edit_posts', 
    'edit_others_posts',
    'publish_posts',
    'delete_post',  
    'delete_posts',  
    'delete_others_posts',
    'sts_read',
    'sts_read_post',
    'sts_read_posts',
    'sts_publish',
    'sts_edit_post',
    'sts_edit_posts',
    'sts_edit_others',
    'sts_delete_post',
    'sts_delete_posts',
    'sts_delete_others_posts',
    'sts_read_private_posts',
  );
  $roles = array(
    get_role( 'administrator' ),
    get_role( 'editor' ),
  );
  foreach ($roles as $role) {
    foreach ($caps as $cap) {
        if ( is_object($role)) {
      $role->add_cap( $cap );
        }
    }
  }
  // ADD Capabilities for subscribers
  $caps2 = array(   
    'read',
    'read_post', 
    'read_posts', 
    'edit_post', 
    'edit_posts', 
    'publish_posts',
    'delete_post',  
    'delete_posts',  
    'sts_read',
    'sts_read_post',
    'sts_read_posts',
    'sts_publish',
    'sts_edit_post',
    'sts_edit_posts',
  );
  $roles2 = array(
    get_role( 'subscriber' ),
     );
  foreach ($roles2 as $role2) {
    foreach ($caps2 as $cap2) {
        if ( is_object($role2)) {
      $role2->add_cap( $cap2 );
    }
  }  
}
}

add_action( 'init', 'sts_custom_post_type' );

add_action('admin_menu' , 'sts_admin_settings_menu'); 
function sts_admin_settings_menu() {  
    add_submenu_page('edit.php?post_type=sts-ticket', 'Ticket System Settings', 'Settings', 'manage_options', 'sts-ticket', 'sts_admin_settings');

    global $submenu;
    if ( isset( $submenu['sts_dashboard'] ) ) {
        $submenu['sts_dashboard'][0][0] = "All tickets";
    }
    //call register settings function
    add_action( 'admin_init', 'sts_register_settings' );
    #wp_enqueue_script("jquery-effects-core");
    #wp_enqueue_script( 'jquery', plugins_url( 'includes/jquery/jquery-1.10.2.js', __FILE__ ), array(), '1.0.0', true );
    wp_enqueue_script( 'sts-ticket-system', plugins_url( 'includes/actions.js', __FILE__ ), array("jquery", "jquery-ui-core", "jquery-ui-tabs"), '1.0.2', true );
    #wp_enqueue_script( 'jquery', plugins_url( 'includes/jquery/jquery-1.10.2.js', __FILE__ ), array(), '1.0.0', true );
    #wp_enqueue_script( 'jquery-ui', plugins_url( 'includes/jquery/jquery-ui.js', __FILE__ ), array("jquery"), '1.0.0', true );
    wp_enqueue_style('jquery-ui', plugins_url( 'includes/jquery/jquery-ui.css', __FILE__ ) );
}

function sts_register_settings()
{
    // Register the settings with Validation callback
    register_setting( 'sts_ticket_options', 'sts_categories', 'sts_validate_settings' );
    register_setting( 'sts_ticket_options', 'sts_departments', 'sts_validate_departments' );
    //register_setting( 'sts_ticket_options', 'sts_default_approver', 'sts_validate_user' );
    register_setting( 'sts_ticket_options', 'sts_email_notification');
    register_setting( 'sts_ticket_options', 'sts_plugin_style');
    register_setting( 'sts_ticket_options', 'sts_recaptcha_settings');
    register_setting( 'sts_ticket_options', 'sts_powerdby_display');

}

add_filter( 'views_edit-sts-ticket', 'sts_ticket_meta_views', 10, 1 );
function sts_ticket_meta_views( $views ) 
{
    $statuses = sts_get_statuses();
    $args = array(
    'post_type' => 'sts-ticket',
    'meta_query' => array(
        array(
            'key' => 'sts_ticket_status',
            'value' => $statuses['st_open']
        )));
    wp_reset_query();
    $query = new WP_Query( $args );
    $current_ticket_status = strtoupper($_GET['sts_ticket_status']);
    if($current_ticket_status==strtoupper($statuses['st_open'])){
        $print_cur_open = 'class="current"';
        $views['all'] = str_replace('class="current"', "", $views['all']);
    }
    $views['sts_ticket_status_open'] = '<a '.$print_cur_open.' href="edit.php?sts_ticket_status='.$statuses['st_open'].'&post_type=sts-ticket">Open <span class="count">('.$query->found_posts.')</span></a>';
    $args2 = array(
    'post_type' => 'sts-ticket',
    'meta_query' => array(
        array(
            'key' => 'sts_ticket_status',
            'value' => $statuses['st_closed']
        )));
    wp_reset_query();
    $query2 = new WP_Query( $args2 );
    if($current_ticket_status==strtoupper($statuses['st_closed'])){
        $print_cur_closed = 'class="current"';
        $views['all'] = str_replace('class="current"', "", $views['all']);
    }

    $views['sts_ticket_status_closed'] = '<a '.$print_cur_closed.' href="edit.php?sts_ticket_status='.$statuses['st_closed'].'&post_type=sts-ticket">Closed <span class="count">('.$query2->found_posts.')</span></a>';
    unset($views['publish']);
    return $views;
}

add_action( 'load-edit.php', 'sts_ticket_load_custom_filter' );
function sts_ticket_load_custom_filter()
{
    global $typenow;

    // Adjust the Post Type
    if( 'sts-ticket' != $typenow )
        return;

    add_filter( 'posts_where' , 'sts_ticket_posts_where' );
}

function sts_ticket_posts_where( $where ) 
{
    global $wpdb;       
    if ( isset( $_GET[ 'sts_ticket_status' ] ) && !empty( $_GET[ 'sts_ticket_status' ] ) ) 
    {
        $meta = esc_sql( strtoupper($_GET['sts_ticket_status']) );
        $where .= " AND ID IN (SELECT post_id FROM ".$wpdb->postmeta." WHERE meta_key='sts_ticket_status' AND meta_value=UPPER('".$meta."') )";
    }
    return $where;
}

function sts_validate_user($id){
    $aux = get_userdata( $id );

    if($aux==false){
        return get_current_user_id();
    }else{
        return $id;
    }
}

function sts_validate_settings($input)
{
  $new_cat_id = 0;
  foreach($input as $k => $v)
  {
    if(strpos($k, "c_") !== 0){
        if($new_cat_id==0){
            $all_cats = sts_get_categories();
            if(is_array($all_cats)){
                end($all_cats);         // move the internal pointer to the end of the array
                $latest_cat = key($all_cats);
            }
            $latest_id = str_replace("c_", "", $latest_cat);
            $new_cat_id = empty($latest_id) ? 0 : $latest_id;
        }
        $new_cat_id = $new_cat_id+1;
        $latest_id = "c_".$new_cat_id;
        $newinput[$latest_id] = $v;
    } else {
       $newinput[$k] = $v; 
    }
  }
  return $newinput;
}
function sts_validate_departments($input)
{
  $new_dep_id = 0;
  foreach($input as $k => $v)
  {
    if(strpos($k, "d_") !== 0){
        if($new_dep_id==0){
            $all_departments = sts_get_departments();
            if(is_array($all_departments)){
                end($all_departments);         // move the internal pointer to the end of the array
                $latest_dep = key($all_departments);
            }
            $latest_id = str_replace("d_", "", $latest_dep);
            $new_dep_id = empty($latest_id) ? 0 : $latest_id;
        }
        $new_dep_id = $new_dep_id+1;
        $latest_id = "d_".$new_dep_id;
        $newinput[$latest_id] = $v;
    } else {
       $newinput[$k] = $v; 
    }
  }
  return $newinput;
}


function sts_ticket_metaboxes() {
    add_meta_box(
        'sts-ticket-system',      // Unique ID
        esc_html__( 'Memoria Ticket System', 'example' ),    // Title
        'sts_ticket_system_meta_box',   // Callback function
        'sts-ticket',         // Admin page (or post type)
        'advanced',         // Context
        'default'         // Priority
    );
}
function sts_ticket_system_meta_box( $object, $box ) {
    global $wpdb, $post;
    wp_nonce_field( basename( __FILE__ ), 'sts_ticket_system_nonce' ); ?>
  <p>
    <label for="sts_ticket_status"><?php _e( "Status", 'sts-ticket' ); ?></label>
    <br />
    <?php
    $statuses = sts_get_statuses();
    echo '<select name="sts_ticket_status" id="sts_ticket_status">';
    foreach ($statuses as $value) {
        echo '<option value="'.$value.'" '.selected(strtoupper(get_post_meta( $object->ID, 'sts_ticket_status', true )), strtoupper(trim($value)), false ).'>'.$value.'</option>';
    }
    echo '</select>';
    ?>
  </p>
  <p>
    <?php
        $args  = array(
            'orderby' => 'first_name',  // Order by display name    
        );
        $wp_user_query = new WP_User_Query($args);
        // Get the results
        $users = $wp_user_query->get_results();
        $selected_approver = get_post_meta($post->ID, 'sts_ticket_approver', true);
        // Check for results
        echo '<label for="sts_ticket_approver">'.__( "Approver").'</label><br/>';
        echo '<select id="sts_ticket_approver" name="sts_ticket_approver">';
        echo '<option value="">Choose approver</option>';
        foreach ($users as $user ) {
            echo '<option value="'.$user->user_email.'" '.selected($selected_approver, $user->user_email, false ).'>'.$user->display_name.'</option>';
        }
        echo '</select>';
    ?>
  </p>
  <p>
    <label for="sts-ticket-id"><?php _e( "Ticket ID", 'example' ); ?></label>
    <br />
    <input class="widefat" type="text" name="sts-ticket-id" id="sts-ticket-id" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sts_ticket_id', true ) ); ?>" size="30" disabled="disabled" />
  </p>
  <p>
    <label for="sts-ticket-author"><?php _e( "Author", 'example' ); ?></label>
    <br />
    <input class="widefat" type="text" name="sts_ticket_author" id="sts-ticket-author" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sts_ticket_author', true ) ); ?>" size="30" />
  </p>
  <p>
    <label for="sts-ticket-author-email"><?php _e( "Author email", 'example' ); ?></label>
    <br />
    <input class="widefat" type="text" name="sts_ticket_author_email" id="sts-ticket-author-email" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sts_ticket_author_email', true ) ); ?>" size="30" />
  </p>
  <p>
    <label for="sts-ticket-category"><?php _e( "Category", 'example' ); ?></label>
    <br />
    <input class="widefat" type="text" name="sts_ticket_category" id="sts-ticket-category" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sts_ticket_category', true ) ); ?>" size="30" />
  </p>
  <p>
    <label for="sts-ticket-department"><?php _e( "Department", 'example' ); ?></label>
    <br />
    <input class="widefat" type="text" name="sts_ticket_department" id="sts-ticket-department" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sts_ticket_department', true ) ); ?>" size="30" />
  </p>
<?php }


add_action( 'add_meta_boxes', 'sts_ticket_metaboxes' );
add_action( 'save_post', 'sts_ticket_meta_box_save' );
function sts_ticket_meta_box_save( $post_id )
{
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
     
    if( !isset( $_POST['sts_ticket_system_nonce'] ) || !wp_verify_nonce( $_POST['sts_ticket_system_nonce'], basename( __FILE__ ) ) ) return;
     
    if( !current_user_can( 'edit_post' ) ) return;
     
     
    if( isset( $_POST['sts_ticket_author'] ) )
        update_post_meta( $post_id, 'sts_ticket_author', esc_attr( $_POST['sts_ticket_author']) );
    if( isset( $_POST['sts_ticket_author_email'] ) )
        update_post_meta( $post_id, 'sts_ticket_author_email', esc_attr( $_POST['sts_ticket_author_email']) );
    if( isset( $_POST['sts_ticket_category'] ) )
        update_post_meta( $post_id, 'sts_ticket_category', esc_attr( $_POST['sts_ticket_category']) );
    if( isset( $_POST['sts_ticket_department'] ) )
        update_post_meta( $post_id, 'sts_ticket_department', esc_attr( $_POST['sts_ticket_department']) );
    if( isset( $_POST['sts_ticket_status'] ) )
        update_post_meta( $post_id, 'sts_ticket_status', esc_attr( $_POST['sts_ticket_status'] ) );
    if( isset( $_POST['sts_ticket_approver'] ) )
        update_post_meta( $post_id, 'sts_ticket_approver', esc_attr( $_POST['sts_ticket_approver'] ) );
}

function sts_get_creator_header(){
    return '<p style="float:left"><img src="'.STSICON_32.'" align="middle" alt="Memoria"> &nbsp;&nbsp;Memoria Ticket System is created by Vatan Bytyqi at <a href="http://www.memoria.se" target="_blank">Memoria Konsulting</a></p>
    <div style="float:right;width:200px;text-align:center;margin-right:40px">
If you like Memoria Ticket System and like to support its continued development, please consider a donation. Thank you!<br/>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="B3XMHSASQ77JA">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/sv_SE/i/scr/pixel.gif" width="1" height="1">
</form>




</div><div style="clear:both"></div>
    ';
}


function sts_admin_settings(){

    include(STSDIR.'includes/default_admin_settings_form.php');

}


// Always set a post to be publish
add_action( 'transition_post_status', 'sts_ticket_post_status_new', 10, 3 );
function sts_ticket_post_status_new( $new_status, $old_status, $post ) { 
    if ( $post->post_type == 'sts-ticket' && $new_status == 'publish' && $old_status  != $new_status ) {
        $post->post_status = 'publish';
        wp_update_post( $post );
    }
} 



// Custom columns
add_filter( 'manage_sts-ticket_posts_columns', 'sts_ticket_columns' ) ;

function sts_ticket_columns( $columns ) {

    $columns = array(
        'cb' => '<input type="checkbox" />',
        'ticket' =>__( 'Ticket #' ),
        'title' => __( 'Description' ),
        'sts_author' => __( 'Author' ),               
        'sts_approver' => __( 'Approver' ),
        'sts_status' => __( 'Status' ),
        'comments' => __('Replies'),
        'sts_rating' => __( 'Rating' ),
        'date' => __( 'Created' ), 
    );

    return $columns;
}

add_action( 'manage_sts-ticket_posts_custom_column', 'my_manage_support_columns', 10, 2 );


function my_manage_support_columns( $column, $post_id ) {
    global $post,$parent,$edate,$sdate,$updated_days_used;
    $statuses = sts_get_statuses();
    $sts_ticket_status= get_post_meta($post_id, 'sts_ticket_status', true);
    switch( $column ) {
        
        case 'ticket' :
        /* Get the post meta. */
        echo $post_id;
        break;
        
        case 'sts_author' :
        /* Get the post meta. */
        //the_author();
        echo get_post_meta($post_id, 'sts_ticket_author', true); 
        break;      
        
        
        case 'sts_approver' :
            global $post,$approver_id;
            $approver_email= get_post_meta($post_id, 'sts_ticket_approver', true);
            $user = get_user_by( "email",  $approver_email);  
            if(!$user){
                $approver_name = '<span style="color:orange">None</span>';
            } else {
                $approver_name = $user->display_name;
            }
            echo $approver_name;             
        break;  
        
        case 'sts_status' :
            $support_status2= get_post_meta($post_id, 'sts_ticket_status', true);
            if($support_status2==''){
                update_post_meta($post_id, 'sts_ticket_status', $statuses["st_open"]);
            }
            echo get_post_meta($post_id, 'sts_ticket_status', true);                  
        break;

        case 'sts_rating' :
            if($sts_ticket_status==$statuses['st_closed']){
                $ticket_rate = get_post_meta($post_id, 'sts_ticket_rate', true);
                switch ($ticket_rate) {
                    case '1':
                        echo '<div class="sts-ticket"><div class="smillies"><a href="#" class="greenSmiley selected"></a></div></div>';
                        break;
                    case '0':
                        echo '<div class="sts-ticket"><div class="smillies"><a href="#" class="orangeSmiley selected"></a></div></div>';
                        break;
                    case '-1':
                        echo '<div class="sts-ticket"><div class="smillies"><a href="#" class="redSmiley selected"></a></div></div>';
                        break;
                    default:
                        echo "";
                        break;
                }
            }
        break;      
        
    }
}


//Returns a list of all categories
function sts_get_categories(){
    return get_option('sts_categories');
}
//Returns a list of all departments
function sts_get_departments(){
    return get_option('sts_departments');
}
//Returns a list of all approvers
function sts_get_approvers(){
    return get_option('sts_approvers');
}



//Adds the ticket to the system after submit form
add_action('init','sts_add_ticket');

function sts_add_ticket(){ 
global $post, $error, $wp_rewrite;
if(!empty($_POST['submit_ticket']) && $_POST['submit_ticket']=="submit" && !empty( $_POST['action2'] )) {
    if(!is_wp_error($error)){
        $error = new WP_Error();
    }
    
    
$title2     = esc_html($_POST['sts_subject']);
$scontent = esc_html($_POST['sts_message']); 
$statuses = sts_get_statuses();
$default_status = $statuses["st_open"];
$current_user = wp_get_current_user();
$author_id = $current_user->ID;
if ( !is_user_logged_in() ) {
    $ticket_author = trim(esc_attr( $_POST['sts_name'] ));
    $ticket_author_email = esc_attr( $_POST['sts_email'] );
} else {
    $ticket_author = $current_user->display_name;
    $ticket_author_email = $current_user->user_email;
}
$all_cats = sts_get_categories();
$all_dep = sts_get_departments();
$ticket_category = $all_cats[esc_attr( $_POST['sts_category'] )];
$ticket_department = $all_dep[esc_attr( $_POST['sts_department'] )];

if(!is_email($ticket_author_email)){
    $error->add('regerror','Email not valid');
    return false; 
}
if(empty($ticket_author)){
    $error->add('regerror','Check your name');
    return; 
}

$recaptcha_settings = get_option('sts_recaptcha_settings');
if($recaptcha_settings['active']=="1"){
    require_once(plugin_dir_path( __FILE__ ) . '/includes/recaptchalib.php');
    $privatekey = $recaptcha_settings['private_key'];
    $resp = recaptcha_check_answer ($privatekey,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);
    if (!$resp->is_valid) {
        $error->add('regerror', $resp->error); 
    }
}

//the array of arguments to be inserted with wp_insert_post
$new_post = array(
'post_title'    => $title2,
'post_type'     =>'sts-ticket',
'post_status'   => 'publish',
'post_content'  => $scontent,
'comment_status' => 'open'
);

//insert the the ticket as post into database
if(count($error->get_error_codes())<=0){
    $pid = wp_insert_post($new_post);
    if($pid==0){
        $error->add('regerror','Could not create ticket. Please check your values.');
    } 

    if(count($error->get_error_codes())<=0){
        $ticket_id = sts_generate_ticket_id($pid);

        add_filter( 'upload_dir', 'sts_upload_dir' );

        $attachment_file = $_FILES['attachment_file'];
        $attachment_file["name"] = $ticket_id."-".$attachment_file["name"]; 
        $mimes = array("jpeg"=>"image/jpeg", "jpg"=>"image/jpg", "gif"=>"image/gif", "png"=>"image/png", "bmp"=>"image/bmp", "zip"=>"application/zip", "pdf"=>"application/pdf", "doc"=>"text/doc", "docx"=>"application/vnd.openxmlformats-officedocument.wordprocessingml.document", "txt"=>"text/plain", "gzip"=>"application/gzip", "sql"=>"text/plain", "ppt"=>"application/vnd.ms-powerpoint", "pptx"=>"application/vnd.openxmlformats-officedocument.presentationml.presentation", "rtf"=>"text/rtf","rar"=>"application/x-rar-compressed", "psd"=>"application/photoshop", "htm"=> "text/htm", "xlsx"=>"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","xlt"=>"application/vnd.ms-excel", "csv"=>"text/csv");
        $overrides = array( 'test_form' => false );
        $uploaded_file = wp_handle_upload( $attachment_file, $overrides );

        if ( !isset($uploaded_file['error']) && $uploaded_file ) {

            $wp_filetype = $uploaded_file['type'];
            $filename = $uploaded_file['file'];
            $wp_upload_dir = wp_upload_dir();
            $attachment = array(
                'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
                'post_mime_type' => $wp_filetype,
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                'post_content' => 'File',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $filename, $pid);
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
            wp_update_attachment_metadata($attach_id,  $attach_data);
            //$file_attach_url = wp_get_attachment_url( $attach_id );
           
        }
         remove_filter( 'upload_dir', 'sts_upload_dir' );
        
        //we now use $pid (post id) to help add our post meta data
        add_post_meta($pid, 'sts_ticket_id', $ticket_id, true);
        #add_post_meta($pid, 'sts_ticket_approver', $support_approver, true);
        #add_post_meta($pid, 'sts_ticket_approver_email', $support_approver_email, true);
        add_post_meta($pid, 'sts_ticket_status', $default_status, true);
        add_post_meta($pid, 'sts_ticket_author', $ticket_author, true);
        add_post_meta($pid, 'sts_ticket_author_email', $ticket_author_email, true);
        add_post_meta($pid, 'sts_ticket_category', $ticket_category, true);
        add_post_meta($pid, 'sts_ticket_department', $ticket_department, true);

        $post = get_post($post_id);
        if ( ! empty( $_POST['action2'] ) && 'new_ticket' == $_POST['action2'] ) {
            $author_id=$post->post_author; 
            if ($wp_rewrite->permalink_structure == '')
                $sign = "&";
            else
                $sign = "?";
            $ticket_url = get_permalink( $pid ).$sign."sts-ticket-id=".get_post_meta($pid, 'sts_ticket_id', true);
            $admin_email = get_option('admin_email'); 
            $website_name = get_option('blogname');

            $p=$post_id;
            if ($p==''){
                $p=$post->ID;
            }
            $meta_approval_status = get_post_meta($p, 'si_support_approved', true);
            $app_name = get_post_meta($p, 'si_support_approver', true);
            $app_email = get_post_meta($p, 'si_support_approver_email', true);
            if($app_email==''){
                $app_email = get_option('default_approver_email');  
            }
            if($app_email==''){
                $app_email= get_option('admin_email');  
            }

            $message = sts_get_email_notification();
            $message = nl2br($message);
            $message = str_replace("[%name]", esc_attr($_POST['sts_name']), $message);
            $message = str_replace("[%email]", $_POST['sts_email'], $message);
            $message = str_replace("[%category]", esc_attr($_POST['sts_category']), $message);
            $message = str_replace("[%department]", esc_attr($_POST['sts_department']), $message);
            $message = str_replace("[%subject]", esc_attr($_POST['sts_subject']), $message);
            $message = str_replace("[%message]", esc_html($_POST['sts_message']), $message);
            $message = str_replace("[%ticket_id]", $pid, $message);
            $message = str_replace("[%ticket_url]", $ticket_url, $message);
            $message = str_replace("[%blogg_name]", $website_name, $message);


            $message2 = "Please respond by commenting on this support post: ".$support_url."

            You can mark this post Resolved or Pending here: ".$support_front."
            ";

            $ws_title = get_bloginfo('name');
            $ws_email = get_bloginfo('admin_email');
            $headers = 'From: '.$ws_title.' <'.$ws_email.'>' . "\r\n";
            $subject = "[Ticket: #".$pid."] ".$_POST['sts_subject'];
            $support_author_email = $_POST['sts_email']=="" ? $current_user->user_email : $_POST['sts_email'];

            add_filter( 'wp_mail_content_type', 'set_html_content_type' );
            wp_mail($support_author_email, $subject, $message, $headers);
            remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

            wp_redirect($ticket_url);
            exit();

            // end of email stuff
        }//Notify user
    }
        
} else {
  
}   
}//If post not empty
}//function

function sts_upload_dir( $dir ) {
    return array(
        'path'   => $dir['basedir'] . '/sts-ticket',
        'url'    => $dir['baseurl'] . '/sts-ticket',
        'subdir' => '/sts-ticket',
    ) + $dir;
}


add_action('init','sts_update_comment_status');
function sts_update_comment_status(){ 
    if(wp_verify_nonce($_POST['_wpnonce'],'sts_update_status_action')) {
        $post_id = $_POST['post_id'];
        $ticket_id = $_POST['ticket_id'];
        $status = esc_attr($_POST['sts_status']);
        $the_post_ticket =  get_post_meta( $post_id, "sts_ticket_id", true );
        if($the_post_ticket==$ticket_id){
            update_post_meta($post_id, "sts_ticket_status", $status);
        }
    }
}
add_shortcode( 'sts_memoria_ticket_system', 'sts_default_create_form' );
function sts_default_create_form($atts) {
    global $error;
    include(STSDIR.'includes/default_create_form.php');
    return $ticket_form;
}

function set_html_content_type() {
    return 'text/html';
}
function sts_get_email_notification(){
    $form = get_option('sts_email_notification');
    if(empty($form)){
        return sts_get_default_email_msg();
    }
    return $form;
}
function sts_get_default_email_msg(){
    return "Hi [%name],
Thank you for contacting us.
We have received your inquiry on the subject '[%subject]'. The team will provide an answer as soon as possible.

To view your ticket and to add replies follow this link:
[%ticket_url]

Kind regards,
[%blogg_name]";
}
function sts_get_plugin_style(){
    $style = get_option('sts_plugin_style');
    if(empty($style)){
        return sts_get_default_style();
    }
    return $style;
}
function sts_get_default_style(){
    return file_get_contents(plugin_dir_path( __FILE__ ) . '/default_style.css');
}


add_filter( 'template_include', 'include_template_function', 1 );
function include_template_function( $template_path ) {
    if ( get_post_type() == 'sts-ticket' ) {
        if ( is_single() ) {
            // checks if the file exists in the theme first,
            // otherwise serve the file from the plugin
            if ( $theme_file = locate_template( array ( 'single-ticket-view.php' ) ) ) {
                $template_path = $theme_file;
            } else {
                $template_path = plugin_dir_path( __FILE__ ) . '/includes/single-ticket-view.php';
            }
        }
    }
    return $template_path;
}


add_filter( 'comment_form_defaults', 'cd_pre_comment_text' );
/**
* Shows only one field for replying a ticket.
*
*/
function cd_pre_comment_text( $arg ) {
    if( is_singular( 'sts-ticket' ) ) {
        unset($arg['fields']['email']); 
        unset($arg['fields']['author']);
        unset($arg['fields']['url']);
        unset($arg['comment_notes_before']);
    }
    return $arg;
}
add_filter( "comments_template", "include_comments_function", 1 );
function include_comments_function( $template_path ) {
    if ( get_post_type() == 'sts-ticket' ) {
        if ( is_single() ) {
            // checks if the file exists in the theme first,
            // otherwise serve the file from the plugin
            if ( $theme_file = locate_template( array ( 'single-ticket-comments-form.php' ) ) ) {
                $template_path = $theme_file;
            } else {
                $template_path = plugin_dir_path( __FILE__ ) . '/includes/single-ticket-comments-form.php';
            }
        }
    }
    return $template_path;
}

function sts_update_comment_data($post_id) {
    global $comment_registration;
    $post = get_post($post_id);
    if ( $post->post_type=="sts-ticket" ) {
        if(!is_user_logged_in()){  
            $_POST['email'] = get_post_meta($post->ID, "sts_ticket_author_email", true);
            $_POST['author'] = get_post_meta($post->ID, "sts_ticket_author", true);
        }
        $comment_registration = get_option('comment_registration'); // make sure it's cached
        update_option('comment_registration', '0');
    }
    
}
add_filter('pre_comment_on_post', 'sts_update_comment_data');


function sts_update_approved_comment($comment_id){
    global $comments_notify;
    $comment = get_comment($comment_id);
    $post = get_post($comment->comment_post_ID);
    if ( $post->post_type=="sts-ticket" ) {
        $commentarr = array();
        $commentarr['comment_ID'] = $comment->comment_ID;
        $commentarr['comment_approved'] = 1;
        wp_update_comment( $commentarr );
        $comments_notify = get_option('comments_notify'); // make sure it's cached
        
        update_option('comments_notify', '0');
        $author = get_post_meta($post->ID, 'sts_ticket_author_email', true);
        $comment_author = $comment->comment_author_email;
        $ticket_approver = get_post_meta($post->ID, 'sts_ticket_approver', true);
        $receivers = array();
        if($author!=$comment_author){
            array_push($receivers, $author);
        }
        if(!empty($ticket_approver) && $ticket_approver!=$comment_author){
            array_push($receivers, $ticket_approver);
        }
        foreach ($receivers as $receiver) {
            $admin_email = get_option('admin_email'); 
            $website_name = get_option('blogname');
            $headers = 'From: '.$website_name.' <'.$admin_email.'>' . "\r\n";
            $subject = "[Ticket: #".$post->ID."] ".$post->post_title;
            $message = 'There is a new answer to your inquiry on the subject \''.$post->post_title.'\':';
            if($receiver==$ticket_approver){
                $url_comment = get_permalink( $post->ID )."#comment-".$comment->comment_ID;
            } else {
                $url_comment = get_permalink( $post->ID )."?sts-ticket-id=".get_post_meta($post->ID, 'sts_ticket_id', true)."#comment-".$comment->comment_ID;
            }
            $message .= '<br/><br/>To add a reply follow this link <a href="'.$url_comment.'">'.$url_comment.'</a>';
            $message .= '<br/><hr><br/><i>'.$comment->comment_author.' wrote:</i>';
            $message .= '<br/>'.nl2br($comment->comment_content);
            add_filter( 'wp_mail_content_type', 'set_html_content_type' );
            wp_mail($receiver, $subject, $message, $headers);
            remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
        }

    }
    //Send email notfication;
}
add_action('comment_post', 'sts_update_approved_comment');

add_filter('comment_post_redirect', 'sts_redirect_after_comment', 10, 2);
function sts_redirect_after_comment($location, $comment){
    global $comments_notify, $comment_registration;
    $url = explode("#", $location);
    update_option('comments_notify', $comments_notify);
    update_option('comment_registration', $comment_registration);
    return $_SERVER["HTTP_REFERER"]."#".$url[1];

}
/*
 Generates a unique ID for a ticket
*/
function sts_generate_ticket_id($id){
    return mt_rand(100, 999)."".$id."".time();
}



add_action( 'wp_footer', 'my_action_javascript', 100 );
function my_action_javascript() {
?>
<script type="text/javascript" >
function sts_rate_comment(comment_id, post_id, ticket_id, rating, smiley){

    var data = {
        'action': 'sts_ticket_comment_rate',
        'comment_id': comment_id,
        'post_id' : post_id,
        'ticket_id' : ticket_id,
        'rating' : rating
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post('<?php echo admin_url( 'admin-ajax.php' )?>', data, function(response) {
        if(response=="1"){
            jQuery("#comment-"+comment_id+" .smillies a").removeClass("selected");
            jQuery(smiley).addClass("selected");
        }
    });
    return false;
}
jQuery("form#sts_update_status_form #sts_status").change(function(){
    var val = jQuery(this).val();
    if(val=="Closed"){
        jQuery("form#sts_update_status_form input[type=submit]").css("display", "inline");
    } else {
        jQuery("form#sts_update_status_form input[type=submit]").css("display", "none");
    }
});
</script>
<?php
}
add_action( 'wp_ajax_sts_ticket_comment_rate', 'sts_rate_comment_callback' );
add_action( 'wp_ajax_nopriv_sts_ticket_comment_rate', 'sts_rate_comment_callback' );

function sts_rate_comment_callback() {
    global $wpdb; // this is how you get access to the database

    $comment_id = $_POST['comment_id'];
    $post_id = $_POST['post_id'];
    $ticket_id =  $_POST['ticket_id'];
    $rating = intval( $_POST['rating'] );

    $the_ticket_id = get_post_meta( $post_id, 'sts_ticket_id', true ); 
    $the_comment = get_comment($comment_id);
    $rate = "0";
    switch ($rating) {
        case '1':
            $rate = "1";
            break;
        case '-1':
            $rate = "-1";
            break;
    }
    if($comment_id=="o98dnsn0976q327" && $the_ticket_id==$ticket_id){
        update_post_meta( $post_id, 'sts_ticket_rate', $rate );
        echo 1;
    } else if($the_ticket_id==$ticket_id && $the_comment->comment_post_ID==$post_id){
        update_comment_meta( $comment_id, 'sts_comment_rate', $rate );
        echo 1;
    } else {
        echo 0;
    }

    die(); // this is required to return a proper result
}

function sts_get_statuses(){
    return array("st_open"=>"Open", "st_closed"=>"Closed");
}