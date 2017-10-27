<?php
/**
 * Created by PhpStorm.
 * User: ander
 * Date: 10/11/2017
 * Time: 9:29 AM
 */

use Dompdf\Dompdf;


class dhprg_assemble_report_from_data {

	public $post_id = 0;
	public $saved_path = '';

	public function __construct( $post_id ) {
		$this->post_id = $post_id;
	}

	protected function build_save_path() {
		$post = get_post( $this->post_id );
		$name = $post->post_name;

		return REPORT_PATH . "$name/damson_property_report_$name.pdf";
	}

	protected function assemble_report() {
		$this->saved_path = $this->build_save_path();

		$title           = get_the_title( $this->post_id );
		$contact_details = array( 'll_email', 'll_tel' );

		$fname = get_post_meta( $this->post_id, 'll_name', true );
		$lname = get_post_meta( $this->post_id, 'll_last_name', true );


		$address_segments = array( 'll_number', 'll_street', 'll_area', 'll_town', 'll_postcode' );

		foreach ( $address_segments as $segment ) {
			$address[ $segment ] = get_post_meta( $this->post_id, $segment, true );
		}


		$img_id = get_post_thumbnail_id( $this->post_id );
		if ( $img_id ) {
			$slide_att = wp_get_attachment_image_src( $img_id, 'letterbox-small' );
			$path = get_attached_file( $img_id );
		}


		// Start the output
		$output = $this->output_css();
		$output .= "<div class=\"header\">\n";
		$output .= "<p class=\"title\">$title</p>\n";
		$output .= "<p class=\"address\">{$address['ll_number']} {$address['ll_street']}, {$address['ll_area']}, {$address['ll_town']}, {$address['ll_postcode']}</p>\n</div>\n";
		if($img_id){
			$output .= '<div class="ftr-image-wrap">';
			$output .= '<img class="ftr-image" src="' . $path . '" width="' . $slide_att[1] . '" height="' . $slide_att[2] . '">';
			$output .= '</div>';
		}
		$output .= "<div class='container'>";
		$output .= "<div class='col-1'>";
		$output .= '<h4>Contact Details</h4>
			            <ul class="contact-details">
			              <li class="cd-li"><strong>' . $fname . ( ! empty( $lname ) ? ' ' . $lname : '' ) . '</strong></li>';

		foreach ( $contact_details as $detail ) {
			$data = get_post_meta( $this->post_id, $detail, true );
			if ( ! empty( $data ) ) {
				$output .= "<li class='cd-li'>$data</li>\n";
			}
		}

		$output .= '</ul>';
		$output .= "</div>";
		$output .= "<div class='col-2'>";
		$output .= "<p>right hand stuff</p>";
		$output .= "</div>";
		$output .= "<div style='clear:both'></div>";
		$output .= "</div>";

		return $output;
	}

	public function print_report() {

			$content  = $this->assemble_report();
			$dompdf = new Dompdf();
			$dompdf->loadHtml($content);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'portrait');

			// Render the HTML as PDF
			$dompdf->render();
			// Output the generated PDF to Browser
			$output = $dompdf->output();
			file_put_contents($this->saved_path, $output);
	}

	public function output_css() {
		$output = "<style>
			p {margin-top: 0;}
			h3 {margin-bottom: 10pt;}
			.page-header {background-color: yellow;}	
		    .dh-logo {width: 40mm; margin-top: 5mm; margin-left: 80mm;} 		
		    .header {border: solid 1px #AAA; background-color: #F0F0F0; padding: 5mm;} 		
		    .title {font-size: 12pt; line-height: 18pt; color: #6B1C56; margin-bottom: 2pt;}
		    .address {font-size: 12pt; line-height: 18pt; font-weight: bold;}	
		    .col-1{width:50%;float:left;background-color:yellow;}
		    .col-2{width:50%;float:left;background-color: blue;}
		    .container{width:100%;background-color:red;}
        </style>\n";

		return $output;
	}


}