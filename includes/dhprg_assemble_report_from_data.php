<?php
/**
 * Created by PhpStorm.
 * User: ander
 * Date: 10/11/2017
 * Time: 9:29 AM
 */

use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;


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
		}


		// Start the output
		$output = $this->output_css();

		$output .= "<page backtop=\"25mm\" backbottom=\"19mm\" backleft=\"10mm\" backright=\"10mm\">";
		$output .= "<page_header><img class=\"dh-logo\" src=\"https://s3-us-west-2.amazonaws.com/s.cdpn.io/15184/dh-logo-ls-600.png\"></page_header>\n\n";
		$output .= "<div class=\"header\">\n";
		$output .= "<p class=\"title\">$title</p>\n";
		$output .= "<p class=\"address\">{$address['ll_number']} {$address['ll_street']}, {$address['ll_area']}, {$address['ll_town']}, {$address['ll_postcode']}</p>\n</div>\n";
		if($img_id){
			$output .= '<div class="ftr-image-wrap">';
			$output .= '<img class="ftr-image" src="' . $slide_att[0] . '" width="' . $slide_att[1] . '" height="' . $slide_att[2] . '">';
			$output .= '</div>';
		}
		$output .= '
			    <table border="0" cellspacing="5" cellpadding="5" style="width:180px">
			      <tbody>
			        <tr>
			          <th class="table-header" colspan="2">Heading</th>
			        </tr>
			        <tr>
			          <td style="width:40%" align="left">
			            <h4>Contact Details</h4>
			            <ul class="contact-details">
			              <li class="cd-li"><strong>' . $fname . ( ! empty( $lname ) ? ' ' . $lname : '' ) . '</strong></li>';

		foreach ( $contact_details as $detail ) {
			$data = get_post_meta( $this->post_id, $detail, true );
			if ( ! empty( $data ) ) {
				$output .= "<li class=\"cd-li\">$data</li>\n";
			}
		}

		$output .= '</ul>
			
			          </td>
			          <td style="width:40%" align="right">right hand stuff</td>
			        </tr>
			      </tbody>
			    </table>';
		$output .= "<div class=\"container\">";
		$output .= "<div class=\"col-1\">";
		$output .= "<p>Left</p>";
		$output .= "</div>";
		$output .= "<div class=\"col-2\">";
		$output .= "<p>Right</p>";
		$output .= "</div>";
		$output .= "<div style='clear:both'></div>";
		$output .= "</div>";


		//   $output .= '<div class="cd">';

		// $output .= "<h4>Contact Details</h4>\n";
		// $output .= "<ul class=\"contact-details\">\n";
		// $output .= "<li class=\"cd-li\"><strong>" . $fname . (!empty($lname) ? ' ' . $lname : '') ."</strong></li>\n";


		// foreach ( $contact_details as $detail ) {
		// 	$data = get_post_meta( $this->post_id, $detail, true );
		// 	if ( !empty( $data ) ) {
		// 		$output .= "<li class=\"cd-li\">$data</li>\n";
		// 	}
		// }

		// $output .= "<li>{$address['ll_number']} {$address['ll_street']}, {$address['ll_area']}, {$address['ll_town']} </li>\n";
		// $output .= "<li>{$address['ll_postcode']} </li>\n";


		// $output .= "</ul>\n";


		// $output .= "<page_footer>Rick says Hi!</page_footer>";
		$output .= "</page>";

		return $output;
	}

	public function print_report() {
		try {
			$content  = $this->assemble_report();
			$html2pdf = new Html2Pdf( 'P', 'A4', 'en' );
			$html2pdf->setDefaultFont( 'Arial' );
			$html2pdf->writeHTML( $content );
			$html2pdf->output( $this->saved_path, 'F' );
		} catch ( Html2PdfException $e ) {
			$formatter = new ExceptionFormatter( $e );
			echo $formatter->getHtmlMessage();
		}
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
		    .ftr-image {width: 150mm; margin: 5mm 10mm;}		
		    .width {background-color: #EEE; text-align: center; width: 180mm;}		
		    table {background-color: yellow; width: 180mm !important;}		
		    .table-header  {background-color: #AB8848; color: white; text-align: center;}		
		    .table-1-2, .table-2-2 {width: 50%;}
		    .col-1{width:40%;float:left;background-color:yellow;}
		    .col-2{width:40%;float:right;background-color: blue;}
		    .container{width:100%;background-color:red;}
        </style>\n";

		return $output;
	}


}