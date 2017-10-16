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

	protected function build_save_path(){
		$post = get_post($this->post_id);
		$name = $post->post_name;
		return REPORT_PATH . "$name/damson_property_report_$name.pdf";
	}

	protected function assemble_report() {
		$this->saved_path = $this->build_save_path();

		$title            = get_the_title( $this->post_id );
		$contact_details  = array( 'll_name', 'll_last_name', 'll_email', 'll_tel' );
		$address_segments = array( 'll_number', 'll_street', 'll_area', 'll_town', 'll_postcode' );
		foreach ( $address_segments as $segment ) {
			$address[ $segment ] = get_post_meta( $this->post_id, $segment, true );
		}


		$output = "<page backtop=\"25mm\" backbottom=\"19mm\" backleft=\"19mm\" backright=\"19mm\">";
		$output .= "<page_header>Damson Homes Report</page_header>\n\n";
		$output .= "<h1>$title</h1>\n\n";
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
		$output .= "<page_footer>Rick says Hi!</page_footer>";
		$output .= "</page>";

		return $output;
	}

	public function print_report(){
		try {

			$content = $this->assemble_report();
			$html2pdf = new Html2Pdf('P', 'A4', 'en');
			$html2pdf->setDefaultFont('Arial');
			$html2pdf->writeHTML($content);
			$html2pdf->output($this->saved_path, 'F');
		} catch (Html2PdfException $e) {
			$formatter = new ExceptionFormatter($e);
			echo $formatter->getHtmlMessage();
		}
	}
}