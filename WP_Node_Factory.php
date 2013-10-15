<?php

class WP_Node_Factory {
	private $taxonomy;
	private $post_type;
	private $node;

	/**
	 * @author Eddie Moya
	 */
	public function __construct($taxonomy, $post_type = null){
		$this->taxonomy = $taxonomy;
		$this->post_type = (!empty($post_type)) ? $post_type : $this->taxonomy;
		$this->actions();
	}

	/**
	 * @author Eddie Moya
	 */
	public function actions(){

		add_action( "created_$this->taxonomy", array($this, 'create_node'));
		add_action( "edited_$this->taxonomy", array($this, 'create_node'));
		add_action('init', 						array($this, 'register_post_type'), 11);
	}
	
	public function create_node($term_id, $tt_id = null){
		$this->node = new WP_Node($term_id, $this->taxonomy);
		$this->node->register_node();
	}


	/**
	 * 
	 */
	public function register_post_type(){

		$labels = apply_filters("wp_node_post_type_{$this->taxonomy}_labels", array(
			'name' 					=> _x(ucfirst($this->post_type) .'s', 'post type general name'),
			'singular_name' 		=> _x(ucfirst($this->post_type), 'post type singular name'),
			'add_new' 				=> _x('Add New', $this->post_type),
			'add_new_item' 			=> __('Add New ' . ucfirst($this->post_type)),
			'edit_item' 			=> __('Edit ' . ucfirst($this->post_type)),
			'new_item' 				=> __('New ' . ucfirst($this->post_type)),
			'all_items' 			=> __('All ' . ucfirst($this->post_type) . 's'),
			'view_item' 			=> __('View ' . ucfirst($this->post_type) . 's'),
			'search_items' 			=> __('Search ' .ucfirst($this->post_type) .'s'),
			'not_found' 			=> __("No {$this->post_type}s found"),
			'not_found_in_trash' 	=> __("No {$this->post_type}s found in Trash"), 
			'parent_item_colon' 	=> '',
			'menu_name' 			=> __(ucfirst($this->post_type))
		));


		$args = apply_filters("wp_node_post_type_{$this->taxonomy}_args", array(
			'label'					=> $this->post_type,
			'labels'				=> $labels,
			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'show_ui' 				=> true, 
			'show_in_menu' 			=> true, 
			'query_var' 			=> false,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> true, 
			'hierarchical' 			=> false,
			'menu_position' 		=> null,
			'supports' 				=> array( 'title', 'custom-fields',  ),
			'taxonomies'			=> array( $this->taxonomy )
		)); 

		register_post_type($this->post_type, $args);
	}



	public function add_node_meta($key, $value){
    	add_post_meta($this->node->post->ID, $key, $value, true);
    }

    public function update_node_meta($key, $value){
    	add_post_meta($this->node->post->ID, $key, $value, true);
    }


   	public function get_node_meta($key){
   		//print_pre($this);
   	  	return get_post_meta($this->node->post->ID, $key, true);
   	}

   	public function get_post(){
   		return $this->node->post;
   	}

   	public function get_node(){
   		return $this->node;
   	}


   	/**
	 * This tests that we are able to create single posts will NULL post type and a colon for a title.
	 * Just like test_colon_bug() but uses a null variable for the term.
	 *
	 * @group wpnode
	 * @group wpnode_class
	 * @group bugfixes
	 * @group colonbug
	 *
	 */
	public function test_colon_bug_null_term(){

		$term = NULL;

		$node = new WP_Node_Factory($term, $term);

		$colon_post = get_page_by_title(': ', 'OBJECT', 'post');
		$query = new WP_Query('post_type=post');

		$this->assertAttributeEquals(0, 'post_count', $query, 'Test for Colon Bug failed. Wrong number of posts exist.');
		$this->assertAttributeEmpty('posts', $query, 'Test for Colon Bug failed. Wrong number of posts exist.');
		$this->assertNull($colon_post, 'Test for the Colon Bug failed. Found a post with a colon as the title');
	}

}



