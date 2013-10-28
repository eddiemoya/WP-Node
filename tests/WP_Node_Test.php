<?php

class WP_Node_Test extends WP_UnitTestCase {
	public $plugin_slug = 'wp-node';
	public $term;

	/**
	 * @group wpnode
	 * @group wpnode_class
	 */
	public function setUP()
	{
		parent::setUp();
		wp_delete_post(1, true);
		$term = wp_insert_term('test-category', 'category');
		$this->term = get_term_by('slug', 'test-category', 'category');
	}

	/**
	 * @group wpnode
	 * @group wpnode_class
	 */
	public function tearDown()
	{
		parent::tearDown();
		wp_delete_term($this->term->term_id, $this->term->taxonomy);
	}

	/**
	 * @group wpnode
	 * @group wpnode_class
	 */
	public function testTerm()
	{
		$this->assertEquals($this->term, get_term_by('slug', 'test-category', 'category'), "Term was not created, it is needed for the test to work properly");
	}

	/**
	 * @group wpnode
	 * @group wpnode_class
	 */
	public function testNode_TermBySlug() 
	{
		$node = new WP_Node($this->term->slug, $this->term->taxonomy, 'slug');
		$this->assertEquals($node->term, $this->term);
	}

	/**
	 * @group wpnode
	 * @group wpnode_class
	 */
	public function testNode_TermByID_Explicit()
	{
		$node = new WP_Node($this->term->term_id, $this->term->taxonomy, 'id');
		$this->assertEquals($node->term, $this->term);
	}

	/**
	 * @group wpnode
	 * @group wpnode_class
	 */
	public function testNode_TermByID_Implicit()
	{
		$node = new WP_Node($this->term->term_id, $this->term->taxonomy);
		$this->assertEquals($node->term, $this->term);
	}

	/**
	 * @group wpnode
	 * @group wpnode_class
	 */
	public function testNode()
	{
		$node = new WP_Node($this->term->term_id, $this->term->taxonomy);
		$this->assertInstanceOf('WP_Node', $node);
		$this->assertObjectHasAttribute('term', $node, "No term object in node");
		$this->assertObjectHasAttribute('post', $node, "No post object in node");

		$this->assertAttributeInstanceOf('stdClass', 'term', $node);
		$this->assertAttributeEmpty('post', $node);

	}


	/**
	 * Test to ensure that when a node is created, that the post created for it is added to the taxonomy term correctly, and can be retreived correctly
	 * @group wpnode
	 * @group wpnode_class
	 */
	public function testNode_registerNode_insertion(){
		$post_args = array(
			'post_status' 	=> 'publish',
			'post_title' 	=> 'Bad Post',
			'post_type' 	=> $this->term->taxonomy,
		);

		$bad_post = get_post(wp_insert_post($post_args));
		$bad_post2 = get_post(wp_insert_post($post_args));
		$bad_post3 = get_post(wp_insert_post($post_args));

		$node = new WP_Node($this->term->term_id, $this->term->taxonomy);
		$response = $node->register_node();

		// Assert that the array contains a set_object and an insert_id key
		$this->assertArrayHasKey('set_object', $response);
		$this->assertArrayHasKey('inserted_id', $response);



		$query = new WP_Query(
			array( 
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
			)
		);
		$post = $query->post;

		$this->assertInstanceOf('stdClass', $post);
		$this->assertAttributeEquals($response['inserted_id'], 'ID', $post);
	}

	/**
	 * @group wpnode
	 * @group wpnode_class
	 */
	public function testNode_registerNode_new()
	{
		$node = new WP_Node($this->term->term_id, $this->term->taxonomy);
		$response = $node->register_node();

		// Assert that the array contains a set_object and an insert_id key
		$this->assertArrayHasKey('set_object', $response);
		$this->assertArrayHasKey('inserted_id', $response);

		// Assert that set is a stdClass object, and insert_id is an integer
		$this->assertInstanceOf('stdClass', $response['set_object']);
		$this->assertInternalType('integer', $response['inserted_id']);

		// Assert that insert_id equals the ID property of set_object
		$this->assertAttributeEquals($response['inserted_id'], 'ID', $response['set_object'], "The post that was inserted does not match the post that SHOULD have been set");
		
		// Assert that set_object equals the post property of our node object.
		$this->assertAttributeEquals($response['set_object'], 'post', $node, "The post that SHOULD have been set, does not match the post object in the node.");

		// Assert that the node object has a term and post attribute.
		$this->assertObjectHasAttribute('term', $node, "No term object in node");
		$this->assertObjectHasAttribute('post', $node, "No post object in node");

		// Assert that both the term and node attributes are stdClass objects.
		$this->assertAttributeInstanceOf('stdClass', 'term', $node);
		$this->assertAttributeInstanceOf('stdClass', 'post', $node);
	}

	/**
	 * This tests what happens when a node in instantiated
	 * and the post object already exists
	 *
	 * @group wpnode
	 * @group wpnode_class
	 */
	public function testNode_registerNode_duplicate()
	{

		$node_old = new WP_Node($this->term->term_id, $this->term->taxonomy);
		$response = $node_old->register_node();

		$node = new WP_Node($this->term->term_id, $this->term->taxonomy);
		$response = $node->register_node();

		// Assert that the array contains a set_object and NOT insert_id key
		$this->assertArrayHasKey('set_object', $response);
		$this->assertArrayNotHasKey('inserted_id', $response);

		// Assert that set is a stdClass object
		$this->assertInstanceOf('stdClass', $response['set_object']);

		// Asser that set_object equals the post property of our node object.
		$this->assertAttributeEquals($response['set_object'], 'post', $node, "The post that SHOULD have been set, does not match the post object in the node.");

		// Assert that the node object has a term and post attribute.
		$this->assertObjectHasAttribute('term', $node, "No term object in node");
		$this->assertObjectHasAttribute('post', $node, "No post object in node");

		// Assert that both the term and node attributes are stdClass objects.
		$this->assertAttributeInstanceOf('stdClass', 'term', $node);
		$this->assertAttributeInstanceOf('stdClass', 'post', $node);
	}


	/**
	 * This tests that we are able to create single posts will NULL post type and a colon for a title.
	 * Its purpose is to prove that the premise of the test is valid.
	 *
	 * @group wpnode
	 * @group wpnode_class
	 * @group bugfixes
	 * @group colonbug
	 *
	 */
	public function test_colon_bug_control_test(){
		$term = NULL;

		$post_args = array(
			'post_status' 	=> 'publish',
			'post_title' 	=> ': ',
			'post_type' 	=> $term
		);

		$inserted = get_post(wp_insert_post($post_args));
		$colon_post = get_page_by_title(': ', 'OBJECT', 'post');
		$query = new WP_Query('post_type=post');
		
		$this->assertAttributeEquals(1, 'post_count', $query, 'The control test for Colon Bug failed. Wrong number of posts exist.');
		$this->assertAttributeEquals(': ', 'post_title', $query->posts[0], 'The control test for the Colon Bug failed. Post title not a colon');
		$this->assertAttributeEquals(': ', 'post_title', $colon_post, 'The control test for the Colon Bug failed. Could not find the post by the colon in the title');


		wp_delete_post($query->post->ID, true);

		$query = new WP_Query('post_type=post');

		$this->assertAttributeEquals(0, 'post_count', $query, 'The control test for Colon Bug failed. Unable to delete all "colon" posts.');
	}

	/**
	 * This tests that we are able to create single posts will NULL post type and a colon for a title.
	 *
	 * @group wpnode
	 * @group wpnode_class
	 * @group bugfixes
	 * @group colonbug
	 *
	 */
	public function test_colon_bug(){

		$node = new WP_Node($this->term->term_id, $this->term->taxonomy);
		$response = $node->register_node();

		$colon_post = get_page_by_title(': ', 'OBJECT', 'post');
		$query = new WP_Query('post_type=post');

		$this->assertAttributeEquals(0, 'post_count', $query, 'Test for Colon Bug failed. Wrong number of posts exist.');
		$this->assertAttributeEmpty('posts', $query, 'Test for Colon Bug failed. Wrong number of posts exist.');
		$this->assertNull($colon_post, 'Test for the Colon Bug failed. Found a post with a colon as the title');

		$query = new WP_Query('post_type=post');

		$this->assertAttributeEquals(0, 'post_count', $query, 'The control test for Colon Bug failed. Unable to delete all "colon" posts.');
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

		$node = new WP_Node($term, $term);

		$colon_post = get_page_by_title(': ', 'OBJECT', 'post');
		$query = new WP_Query('post_type=post');

		$this->assertAttributeEquals(0, 'post_count', $query, 'Test for Colon Bug failed. Wrong number of posts exist.');
		$this->assertAttributeEmpty('posts', $query, 'Test for Colon Bug failed. Wrong number of posts exist.');
		$this->assertNull($colon_post, 'Test for the Colon Bug failed. Found a post with a colon as the title');
	}


}