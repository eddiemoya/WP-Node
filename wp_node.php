<?php

class WP_Node {
	public $term;
	public $post;


	public function __construct($term, $taxonomy = null, $term_field = 'id'){
		$this->term = get_term_by($term_field, $term, $taxonomy);
		$this->post = $this->get_post($term, $taxonomy);
		$this->register_term_meta();

	}

	/**
	 * TODO: Allow for some way of mapping logic to metadata or other taxonomies/terms
	 */
	private function register_term_meta(){
		if(empty($this->post)){
			$this->post = $this->insert_post();
		}
	}

	/**
	 * 
	 */
	private function insert_post(){
		
		$post_id = wp_insert_post(array(
			'post_status' 	=> 'publish',
			'post_type' 	=> $this->term->taxonomy,
			'post_name'		=> $this->term->slug,
			'post_title' 	=> ucfirst($this->term->taxonomy).': '.$this->term->name,
			'tax_input'		=> array( $this->term->taxonomy => array($this->term->term_id))
		));

		return get_post($post_id);
	}


	private function get_post($term_id, $taxonomy = null){
		$post = get_posts( 
			array(
				'post_type' => $taxonomy,
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
		if(!empty($post)){
			$post = $post[0];
		}
	
		return $post;
	}

}




