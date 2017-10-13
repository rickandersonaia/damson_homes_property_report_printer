<?php
/**
 * Created by PhpStorm.
 * User: ander
 * Date: 10/11/2017
 * Time: 9:29 AM
 */


class dhprg_assemble_report {

	public $post_id = 0;

	public function __construct( $post_id = 0 ) {
		$this->post_id = $post_id;
	}

	public function assemble_report() {
		$title            = get_the_title( $this->post_id );
		$contact_details  = array( 'll_name', 'll_last_name', 'll_email', 'll_tel' );
		$address_segments = array( 'll_number', 'll_street', 'll_area', 'll_town', 'll_postcode' );
		foreach ( $address_segments as $segment ) {
			$address[ $segment ] = get_post_meta( $this->post_id, $segment, true );
		}

		$output = "<h1>$title</h1>\n\n";
		$output .= "<h3>Contact Details</h3>\n";
		$output .= "<ul>\n";
		foreach ( $contact_details as $detail ) {
			$data = get_post_meta( $this->post_id, $detail, true );
			if ( isset( $data ) ) {
				$output .= "<li>$data</li>\n";
			}
		}

		$output .= "<li>{$address['ll_number']} {$address['ll_street']}, {$address['ll_area']}, {$address['ll_town']} </li>\n";
		$output .= "<li>{$address['ll_postcode']} </li>\n";
		$output .= "</ul>\n";

		return $output;
	}
}