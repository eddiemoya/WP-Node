<?php

class WP_Node {
	public $term;
	public $post;
	public $post_type;


	public function __construct($term, $taxonomy = null, $term_field = 'id', $post_type = null){
		$this->term = get_term_by($term_field, $term, $taxonomy);
		$this->post_type = (is_null($post_type)) ? $this->term->taxonomy : $post_type;
		$this->post = $this->get_post($term, $taxonomy);
	}

	/**
	 * TODO: Allow for some way of mapping logic to metadata or other taxonomies/terms
	 */
	public function register_term_meta(){
		if(empty($this->post)){
			$this->post = $this->insert_post();
		}
	}

	public function add_meta_data($key, $value){
		add_post_meta($this->post->ID, $key, $value, true);
	}


	public function get_meta_data($key){
		return get_post_meta($this->post->ID, $key, true);
	}

	/**
	 * 
	 */
	private function insert_post($post_type = null){

		$post_id = wp_insert_post(array(
			'post_status' 	=> 'publish',
			'post_type' 	=> $this->post_type,
			'post_name'		=> $this->term->slug,
			'post_title' 	=> ucfirst($this->term->taxonomy).': '.$this->term->name,
			'tax_input'		=> array( $this->term->taxonomy => array($this->term->term_id))
		));

		return get_post($post_id);
	}


	private function get_post($term_id, $taxonomy = null){
		$post = get_posts( 
			array(
				'post_type' => $this->post_type,
				'tax_query' => array(
					array(
						'terms' => $this->term->slug,
						'taxonomy' => $this->term->taxonomy,
						'field' => 'slug',
						'include_children' => false //wtf
					)
				)

			)
		);
		$post = $post[0];

		if (empty($post)) {
			$this->insert_post();
		}

		return $post;
	}

}




