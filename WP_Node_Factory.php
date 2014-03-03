<?php

class WP_Node_Factory {
	private $taxonomy;
	private $post_type;
	private $node;
	private $object_type;

	/**
	 * @author Eddie Moya
	 */
	public function __construct($object_slug, $object_type = "term", $args = null){

			$this->taxonomy = $object_slug;
			$this->post_type = $object_slug;
			$this->object_type = $object_type;
			$this->actions();
	}

	/**
	 * @author Eddie Moya
	 */
	public function actions(){

		if($this->object_type == "term"){
			add_action( "created_$this->taxonomy", 	array($this, 'create_node'));
			add_action( "edited_$this->taxonomy", 	array($this, 'create_node'));
			add_action('init', 						array($this, 'register_post_type'), 11);
		}

		if($this->object_type == "post") {
			add_action( 'admin_enqueue_scripts',	array($this, 'admin_enqueue_scripts' ));

			add_action( "save_post", 				array($this, 'create_node'));
			add_action('init', 						array($this, 'register_taxonomy'), 11);
			add_action( 'transition_post_status', 	array($this, 'transition_post_status'), 10, 3 );		
		}

		
	}

	function admin_enqueue_scripts() {
    	if ( $this->post_type == get_post_type() ){
	        wp_dequeue_script( 'autosave' );
	    }
	}
	
	public function create_node($id, $obj = null){

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      		return $post_id;

        if(defined('DOING_AJAX')) 
    		return;

      	if ( $this->object_type == "post" && $this->post_type != $_POST['post_type'] ) 
      		return $post_id;

		$this->node = new WP_Node($id, $this->taxonomy, 'id', $this->object_type);
		return $this->node->register_node();
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


	public function register_taxonomy(){

		$labels = apply_filters("wp_node_taxonomy_{$this->taxonomy}_labels", array(
			'name' 					=> _x(ucfirst($this->post_type) .'s', 'post type general name'),
			'singular_name' 		=> _x(ucfirst($this->post_type), 'post type singular name'),
			'add_new_item' 			=> __('Add New ' . ucfirst($this->post_type)),
			'edit_item' 			=> __('Edit ' . ucfirst($this->post_type)),
			'new_item' 				=> __('New ' . ucfirst($this->post_type)),
			'all_items' 			=> __('All ' . ucfirst($this->post_type) . 's'),
			'view_item' 			=> __('View ' . ucfirst($this->post_type) . 's'),
			'search_items' 			=> __('Search ' .ucfirst($this->post_type) .'s'),
			'not_found' 			=> __("No {$this->post_type}s found"),
			'menu_name' 			=> __(ucfirst($this->post_type))
		));


		$args = apply_filters("wp_node_taxonomy_{$this->taxonomy}_args", array(
			'label'					=> $this->post_type,
			'labels'				=> $labels,
			'public' 				=> false,
			'show_ui' 				=> false, 
			'rewrite' 				=> false,
			'capability_type' 		=> 'post', 
			'hierarchical' 			=> true,
			'exclude_from_search' => false,
			'taxonomies'			=> array( $this->taxonomy )
		)); 

		$post_type = apply_filters("wp_node_taxonomy_{$this->taxonomy}_post_types", $this->post_type);

		register_taxonomy($this->taxonomy, $post_type, $args);
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


   	public function transition_post_status( $new_status, $old_status, $post ) {

   		if ( $post->post_type != $this->post_type ) 
	    	return;

	    if ( $old_status == 'publish' && $new_status != 'publish' ) {

			$this->node = new WP_Node($post->ID, $this->taxonomy, 'id', $this->object_type);
			$this->node->register_node();

	        wp_delete_term($this->node->term->term_id, $this->taxonomy);
	    }
	}

}



