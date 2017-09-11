<?php
/*
Plugin Name: PGI REST API Meta Data 
Plugin URI:  
Description: Playground Ideas | Add Meta Data in REST API Response
Version:     1.0
Author:      Carlos Mendoza
Author URI:  https://www.mendozalearninghub.com
*/

/*
    Working with registered meta in the WP REST API
*/    
// The object type. For custom post types, this is 'post';
// for custom comment types, this is 'comment'. For user meta,
// this is 'user'.
$object_type = 'user';

/* User PayPal*/
$args1 = array( // Validate and sanitize the meta value.
    // Note: currently (4.7) one of 'string', 'boolean', 'integer',
    // 'number' must be used as 'type'. The default is 'string'.
    'type'         => 'string',
    // Shown in the schema for the meta key.
    'description'  => 'paypal',
    // Return a single value of the type.
    'single'       => true,
    // Show in the WP REST API response. Default: false.
    'show_in_rest' => true,
);
register_meta( $object_type, 'paypal', $args1 );

/* User Country */
$args1 = array( 
    'type'         => 'string',
    'description'  => 'country',
    'single'       => true,
    'show_in_rest' => true,
);
register_meta( $object_type, '_country', $args1 );

/* 
    Register Custom Routes 
*/
add_action( 'rest_api_init', 'register_courses_route' );

function register_courses_route() {
    register_rest_route( 'pgi/v1', '/courses/', array(
        'methods' => 'GET',
        'callback' => 'mlh_get_all_courses',
    ) );    
}
function mlh_get_all_courses(){
    global $wpdb;
    $table_name = $wpdb->prefix . "wffilemods";
    global $wpdb;
    $query = $wpdb->prepare(
            "SELECT *  FROM wp_wffilemods  
             WHERE filename LIKE %s OR filename LIKE %s", 
             '%'.$wpdb->esc_like('plugins/course/course_templates/_inc/img'), 
             '%'.$wpdb->esc_like('plugins/course/course_templates/_inc/img').'%' );
    
    // $courses = array(
    //     'starter_kit' => array( 'Welcome', 'Let\'s Build a Playground', 'Site Plan', 'Elements', 'Join Us!' ),
    //     'builders_handbook' => array( 'Introduction', 'Step 1: Listen', 'Step 2: Plan', 'Step 3: Design', 'Step 4: Build', 'Step 5: Maintain', 'Appendices' ),
    //     'safety_manual' => array('managing', 'hazards', 'heights + fall zone'),
    //     'loose_parts_manual' => array( 'welcome', 'benefits of loose parts', 'step 1: assess your environment', 'step 2: gather loose parts materials', 'step 3: storage and maintenence', 'step 4: train staff', 'going deeper', 'thank you', 'further reading & resources' ),
    //     'inclusive_design_manual' => array('intro', 'listen', 'tips and strategies', 'design'),
    //     'cut_paste_designer' => array(),
    //     'teaching_training_manual' => array('welcome', 'the transforming power of play', 'why do children play', 'what does play look like', 'obstacles to play', 'rote based learning vs. play based learning', 'be a play advocate', 'keep learning', 'references'),
    //     'the_case_for_play' => array( 'executive summary', 'introduction', 'the issue', 'potential of play interventions', 'call to action', 'references' )
    // );

    $courses = $wpdb->get_results($query);
    return $courses;
}

/*
    Adding Custom Fields to API Responses (meta data posts and users)
*/   
 add_action( 'rest_api_init', 'add_user_meta_courses' );
 add_action( 'rest_api_init', 'add_user_meta_sections_completed' );
 add_action( 'rest_api_init', 'add_user_meta_current_pages' );
 add_action( 'rest_api_init', 'add_user_meta_favdesign' );
 add_action( 'rest_api_init', 'add_user_meta_avatar' );
 
// example of adding meta data to Post response
//add_action( 'rest_api_init', 'add_post_meta_campaign_video' );
//    /* Post Campaign Video */
//    function add_post_meta_campaign_video() {
//     register_rest_field( 'post',
//         'campaign_video',
//         array(
//             'get_callback'    => 'mlh_get_post_meta',
//             'update_callback' => 'mlh_update_post_meta',
//             'schema'          => null,
//         )
//     );
// }

  /* User Avatar */
 function add_user_meta_avatar() {
     register_rest_field( 'user',
         '_thumbnail_id',
         array(
             'get_callback'    => 'mlh_get_user_avatar',
             'update_callback' => 'mlh_update_user_avatar',
             'schema'          => null,
         )
     );
 }
 
 /* User Courses */
 function add_user_meta_courses() {
     register_rest_field( 'user',
         'courses',
         array(
             'get_callback'    => 'mlh_get_user_meta',
             'update_callback' => 'mlh_update_user_meta',
             'schema'          => null,
         )
     );
 }

 /* Courses Sections completed */
 function add_user_meta_sections_completed() {
    register_rest_field( 'user',
        'sections_completed',
        array(
            'get_callback'    => 'mlh_get_user_meta',
            'update_callback' => 'mlh_update_user_meta',
            'schema'          => null,
        )
    );
}

/* Courses current pages */
function add_user_meta_current_pages() {
    register_rest_field( 'user',
        '_current_pages',
        array(
            'get_callback'    => 'mlh_get_user_meta',
            'update_callback' => 'mlh_update_user_meta',
            'schema'          => null,
        )
    );
}

  /* User Fav Designs */
  function add_user_meta_favdesign() {
    register_rest_field( 'user',
        '_favourites_designs',
        array(
            'get_callback'    => 'mlh_get_user_meta',
            'update_callback' => 'mlh_update_user_meta',
            'schema'          => null,
        )
    );
}

 /* Get and Update callback functions for users meta data */
 function mlh_get_user_meta( $user, $field_name, $request ) {
     return get_user_meta( $user[ 'id' ], $field_name, true ); //last parameter "true" determines if single or array
 }
 
 
 function mlh_update_user_meta( $value, $user, $field_name ) {
     if ( ! $value || ! is_string( $value ) ) {
         return;
     }
 
     return update_user_meta( $user->ID, $field_name, strip_tags( $value ) );
 
 }

 /* Get and Update callback functions for posts meta data*/
 function mlh_get_post_meta( $object, $field_name, $request ) {
    return get_post_meta( $object[ 'id' ], $field_name );
}

function mlh_update_post_meta( $value, $object, $field_name ) {
    if ( ! $value || ! is_string( $value ) ) {
        return;
    }

    return update_post_meta( $object->ID, $field_name, strip_tags( $value ) );

}

 /* Get and Update callback functions for user avatar*/
 function mlh_get_user_avatar($user, $field_name, $request){
    $user_agent_id = get_user_meta( $user[ 'id' ], 'user_agent_id', true );
    return get_post_meta( $user_agent_id, '_thumbnail_id', true );
 }

 function mlh_update_user_avatar( $value, $user, $field_name ) {
    if ( ! $value || ! is_string( $value ) ) {
        return;
    }

    $user_agent_id = get_user_meta( $user[ 'id' ], 'user_agent_id', true );
    return update_post_meta( $user_agent_id, $field_name, strip_tags( $value ) );

}
