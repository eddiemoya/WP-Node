<?php

class WP_Node {
	public $term;
	public $post;


	public function __construct($term, $taxonomy = null, $term_field = 'id')
	{
		$this->term = get_term_by($term_field, $term, $taxonomy);
		//$this->post = $this->get_post($term, $taxonomy);
		//$this->register_term_meta();
	}

	/**
	 * Tries to find the appropriate post object for the given term.
	 * If one is not found, it creates one then sets it.
	 *
	 * @uses self::set_post();
	 * @uses self::insert_post();
	 * @uses is_null();
	 *
	 * @return [array] Returns an associative array containing the id of the post that was set by set_post. If creating a new post was neccessary it will also include that post id;
	 */
	public function register_node()
	{
		$return['set_object'] = $this->set_post();
	
		if(is_null($this->post) && isset($this->term)){

			$return['inserted_id'] = $this->insert_post();
			$return['set_object'] = $this->set_post();

		}
		
		return $return;
	}

	/**
	 * Inserts the post and sets sets its term at the same time
	 *
	 * @uses wp_insert_post();
	 *
	 * @return [integer] The post id of the post that was created.
	 */
	private function insert_post()
	{
		if(isset($this->term)){
			$post_args = array(
				'post_status' 	=> 'publish',
				'post_type' 	=> $this->term->taxonomy,
				'post_name'		=> $this->term->slug,
				'post_title' 	=> ucfirst($this->term->taxonomy).': '.$this->term->name,
				'tax_input'		=> array( $this->term->taxonomy => array($this->term->term_id))
			);

			$post_id = wp_insert_post($post_args);
			return $post_id;
		} else {
			return NULL;
		}
	}

	/**
	 * Sets the post by retrieving it from the database. 
	 * This should be the way it the post object is always retrieved.
	 * 
	 * @uses get_posts();
	 * @uses array_shift();
	 *
	 * @return [stdClass|NULL] Returns the post object if found, otherwise returns NULL.
	 */
	private function set_post()
	{
		$post_args = array( 
			'post_type' => $this->term->taxonomy,
			'tax_query' => array(
					'terms' => $this->term->slug,
					'taxonomy' => $this->term->taxonomy,
					'field' => 'slug',
					'include_children' => false, //wtf
					'operator' => 'IN'
			)
		);
		//print_p
		$post = get_posts($post_args);
		$post = array_shift($post);

		$this->post = $post;

		return $post;
	}

}




