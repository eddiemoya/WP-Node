<?php

class WP_Node {
	public $term;
	public $post;
	private $_node_type;


	/**
	 * @param $object_id (object|int) WP_Term | WP_Post
	 * @param $object_group (string) Taxonomy | Post Type
	 * @param $object_field (string) "id" | "slug"
	 * @param $object_type (string) "term" | "post"
	 */
	public function __construct($object, $object_group = null, $object_field = 'id', $object_type = "term")
	{
		$this->_node_type = $object_type;

		if($object_type == "term"){
			$this->term = get_term_by($object_field, $object, $object_group);
		}
		
		if($object_type == "post" ) {
			if($object_field == 'id'){
				$this->post = get_post($object);
			}
		}
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
		if($this->_node_type == "term"){

			$return['set_object'] = $this->set_post();

			if(is_null($this->post) && isset($this->term)){

				$return['inserted_id'] = $this->insert_post();
				$return['set_object'] = $this->set_post();

			} 
		}

		if ($this->_node_type == "post"){

			$return['set_object'] = $this->set_term();
			//echo "<pre>"; print_r($this); echo "</pre>";
			if (is_null($this->term) && isset($this->post)) {

		 	 	$return['inserted_id'] = $this->insert_term();
			    $return['set_object'] = $this->set_term();

		 	} else {
		 		$return['set_object'] = $this->update_term();
		 	}
		}


		//print_r($return);
		
		return $return;
	}



	/**
	 * 
	 *
	 *
	 */
	private function update_term(){

		if(isset($this->term)){
			$term_args = array(
				'name' => $this->post->post_title,
				'slug' => sanitize_title($this->post->post_title)
			);

			//print_r($term_args);
			//print_r($this->term->taxonomy);
			$term = wp_update_term($this->term->term_id, $this->term->taxonomy, $term_args);
			//print_r($term);exit();

		}
	}


	/**
	 * 
	 * @uses wp_insert_term();
	 * @uses wp_get_term_by();
	 * @uses wp_set_object_terms();
	 *
	 */
	private function insert_term(){

		if(isset($this->post)){
			$term_args = array(
				'title' => apply_filters("wp_node_insert_term_title_".$this->post->post_type, $this->post->post_title, $this->post),
				'slug' => apply_filters("wp_node_insert_term_slug_".$this->post->post_type, $this->post->post_name, $this->post),
				'taxonomy' =>$this->post->post_type
			);

			//print_r($term_args);

			$response = wp_insert_term($term_args['title'], $term_args['taxonomy'], array('slug' => $term_args['slug']));
			//echo "<pre>"; print_r($response); echo "</pre>"; 

			$term = get_term_by('id', $response['term_id'], $this->post->post_type);
			//echo "<pre>"; print_r($term); echo "</pre>";

			$term_id = wp_set_object_terms($this->post->ID, $term->slug, $term->taxonomy);
			//echo "<pre>"; print_r($response); echo "</pre>"; 

			return $term_id;
		} else {
			return NULL;
		}
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
				'post_title' 	=> ucfirst($this->term->taxonomy).': '.$this->term->name
			);

			//print_r($post_args);
			$post_id = wp_insert_post($post_args, true);

			//print_r(array($post_id));
			wp_set_object_terms($post_id, $this->term->slug, $this->term->taxonomy);

			return $post_id;
		} else {
			return NULL;
		}
	}


	/**
	 * Sets the term by retrieving it from the database.
	 *
	 */
	private function set_term(){

		$terms = wp_get_post_terms($this->post->ID, $this->post->post_type, array("fields" => "all"));

		if(empty($terms) || is_wp_error($terms))
			return NULL;


		$this->term = $terms[0];
		//echo "<pre>"; print_r($terms); echo "</pre>"; exit();
		return $terms;

	
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
			'posts_per_page' => -1,
			'tax_query' => 
			array(
				array(
					'terms' => array($this->term->slug),
					'taxonomy' => $this->term->taxonomy,
					'field' => 'slug',
				)
			)

		);

		//print_r($post_args);
		$post = get_posts($post_args);

		//print_r($post);
		$this->post = array_shift($post);

		//print_r($this);
		return $this->post;
	}

}




