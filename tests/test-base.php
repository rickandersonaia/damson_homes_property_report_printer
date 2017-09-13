<?php
/**
 * DH_Propery_Report_Generator.
 *
 * @since   0.0.1
 * @package DH_Propery_Report_Generator
 */
class DH_Propery_Report_Generator_Test extends WP_UnitTestCase {

	/**
	 * Test if our class exists.
	 *
	 * @since  0.0.1
	 */
	function test_class_exists() {
		$this->assertTrue( class_exists( 'DH_Propery_Report_Generator') );
	}

	/**
	 * Test that our main helper function is an instance of our class.
	 *
	 * @since  0.0.1
	 */
	function test_get_instance() {
		$this->assertInstanceOf(  'DH_Propery_Report_Generator', dhprg() );
	}

	/**
	 * Replace this with some actual testing code.
	 *
	 * @since  0.0.1
	 */
	function test_sample() {
		$this->assertTrue( true );
	}
}
