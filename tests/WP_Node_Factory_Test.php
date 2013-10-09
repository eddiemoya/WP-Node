<?php

class WP_Node_Factory_Test extends WP_UnitTestCase {
	public $term;

	public function setUP()
	{
		parent::setUp();

		$term = wp_insert_term('test-category', 'category');
		$this->term = get_term_by('slug', 'test-category', 'category');
	}

	/**
	 * 
	 */
	public function tearDown()
	{
		parent::tearDown();
		wp_delete_term($this->term->term_id, $this->term->taxonomy);
	}

	/**
	 * 
	 */
	public function testNodeFactory()
	{
		$node_factory = new WP_Node_Factory($this->term->taxonomy);

		$this->assertInstanceOf('WP_Node_Factory', $node_factory);
		$this->assertAttributeInternalType('string', 'taxonomy', $node_factory);
		$this->assertAttributeInternalType('string', 'post_type', $node_factory);

	}

	/**
	 * @uses WP_Node_Controller::create_node()
	 * @uses WP_Node_Controller::get_node();
	 */
	public function testNodeFactory_getNode()
	{
		$node_factory = new WP_Node_Factory($this->term->taxonomy);
		$node_factory->create_node($this->term->term_id);
		$node = $node_factory->get_node();

		$this->assertAttributeInstanceOf('WP_Node', 'node', $node_factory, "Oops! The WP_Node_Factory::node property is not of type WP_Node.");
		$this->assertInstanceOf('WP_Node', $node, "Oops! WP_Node_Factory::get_node() returned something other than a WP_Node object.");

	}


	/**
	 * @uses WP_Node_Controller::create_node()
	 * @uses WP_Node_Controller::get_post();
	 */
	public function testNodeFactory_getPost()
	{
		$node_factory = new WP_Node_Factory($this->term->taxonomy);
		$node_factory->create_node($this->term->term_id);

		$post = $node_factory->get_post();

		$this->assertInstanceOf('stdClass', $post, "Post object is of the wrong object type.");
		$this->assertObjectHasAttribute('ID', $post, "Post object exists, but is not populated with post data.");
		$this->assertAttributeInternalType('integer', 'ID', $post, "Post object exists, but is not populated with post data.");

	}

	/**
	 * @uses WP_Node_Controller::create_node()
	 * @uses WP_Node_Controller::add_node_meta();
	 * @uses WP_Node_Controller::get_node_meta();
	 */
	public function testNodeFactory_nodeMeta()
	{
		$node_factory = new WP_Node_Factory($this->term->taxonomy);
		$node_factory->create_node($this->term->term_id);

		$meta_key = "meta_test_key";
		$meta_value = "meta_test_value";

		$post = $node_factory->add_node_meta($meta_key, $meta_value);
		$returned_meta = $node_factory->get_node_meta($meta_key);

		$this->assertInternalType('string', $returned_meta);
		$this->assertEquals($meta_value, $returned_meta);
	}


}


