<?php
/*
Plugin Name: WP Node
Description: Magical things that sound like fun, but might end badly.
Version: 0.1
Author: Eddie Moya
Author URI: http://eddiemoya.com
*/




define('WPNODE_PATH', 		plugin_dir_path(__FILE__));


include (WPNODE_PATH 		. 'WP_Node_Factory.php');
include (WPNODE_PATH 		. 'WP_Node.php');
include (WPNODE_PATH 		. 'meta-importer.php');


add_action('init', create_function('', 'new WP_Node_Factory("nani_dialysis_center", "post");'), 10);



//Actually Identical functions, but use 
function get_node_by_term($taxonomy, $term_id){
	$factory = new WP_Node_Factory($taxonomy, 'term');
	$factory->create_node($term_id);
	return $factory->get_node();
}

function get_node_by_post($post_type, $post_id){

	$_POST['post_type'] = $post_type;
	$factory = new WP_Node_Factory($post_type, 'post');
	$factory->create_node($post_id, 'post');
	return $factory->get_node();
	
}
