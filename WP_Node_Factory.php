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
		add_action('init', 					array($this, 'register_post_type'), 11);
	}
	
	public function create_node($term_id, $tt_id = null){
		$this->node = new WP_Node($term_id, $this->taxonomy);
		return $this->node->register_node();
	}


	/**
	 * 
	 */
	public function register_post_type(){

		$labels = apply_filters("wp_node_post_type_{$this->taxonomy}_labels", array(
			'name' 					=> _x(ucfirst($this->post_type) .' Layout', 'post type general name'),
			'singular_name' 		=> _x(ucfirst($this->post_type) . ' Layout', 'post type singular name'),
			'add_new' 				=> _x('Add New', $this->post_type),
			'add_new_item' 			=> __('Add New Layout'),
			'edit_item' 			=> __('Edit Layout'),
			'new_item' 				=> __('New Layout'),
			'all_items' 			=> __('All Layouts'),
			'view_item' 			=> __('View Layouts'),
			'search_items' 			=> __('Search Layouts'),
			'not_found' 			=> __("No Layouts found"),
			'not_found_in_trash' 	=> __("No Layouts found in Trash"), 
			'parent_item_colon' 	=> '',
			'menu_name' 			=> __(ucfirst($this->post_type) . ' Layout')
		));


		$args = apply_filters("wp_node_post_type_{$this->taxonomy}_args", array(
			'label'					=> $this->post_type,
			'labels'				=> $labels,
			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'show_ui' 				=> true, 
			'show_in_menu' 			=> true, 
			'query_var' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> true, 
			'hierarchical' 			=> false,
			'menu_position' 		=> null,
			'supports' 				=> array( 'title' ),
			'exclude_from_search' => false,
			'taxonomies'			=> array( $this->taxonomy )
		)); 

		register_post_type($this->post_type, $args);
	}



	public function add_node_meta($key, $value){
    	add_post_meta($this->node->post->ID, $key, $value, true);
    }

    public function update_node_meta($key, $value){
    	//print_pre($this->node);
    	update_post_meta($this->node->post->ID, $key, $value);
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
}



