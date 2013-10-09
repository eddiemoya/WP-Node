<?php

class WP_Node_Test extends WP_UnitTestCase {
	public $plugin_slug = 'wp-node';
	public $term;

	public function setUP()
	{
		parent::setUp();

		$term = wp_insert_term('test-category', 'category');
		$this->term = get_term_by('slug', 'test-category', 'category');
	}

	public function tearDown()
	{
		parent::tearDown();
		wp_delete_term($this->term->term_id, $this->term->taxonomy);
	}


	public function testTerm()
	{
		$this->assertEquals($this->term, get_term_by('slug', 'test-category', 'category'), "Term was not created, it is needed for the test to work properly");
	}

	public function testNode()
	{
		$node = new WP_Node($this->term->term_id, $this->term->taxonomy);
		$this->assertInstanceOf('WP_Node', $node);
		$this->assertObjectHasAttribute('term', $node);
		$this->assertObjectHasAttribute('post', $node);

	}

	public function testNode_TermBySlug() 
	{
		$node = new WP_Node($this->term->slug, $this->term->taxonomy, 'slug');
		$this->assertEquals($node->term, $this->term);
	}

	public function testNode_TermByID_Explicit()
	{
		$node = new WP_Node($this->term->term_id, $this->term->taxonomy, 'id');
		$this->assertEquals($node->term, $this->term);
	}

	public function testNode_TermByID_Implicit()
	{
		$node = new WP_Node($this->term->term_id, $this->term->taxonomy);
		$this->assertEquals($node->term, $this->term);
	}

	public function testNode_Post()
	{
		$node = new WP_Node($this->term->term_id, $this->term->taxonomy);
		$this->assertObjectHasAttribute('ID', $node->post);

	}
}