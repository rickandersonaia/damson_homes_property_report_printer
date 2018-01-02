<?php
/**
 * Created by PhpStorm.
 * User: Rick Anderson
 * Date: 10/16/2017
 * Time: 2:02 PM
 */

use Dompdf\Dompdf;

class DHPRG_Create_PDFs_From_Images {
	protected $images_to_convert = array();

	public function __construct( $images_to_convert ) {
		$this->images_to_convert = $images_to_convert;
	}

	public function generate_pdfs() {
		foreach ( $this->images_to_convert as $original_pdf_file ) {
			$cntr = 1;
			foreach ( $original_pdf_file['image_paths'] as $path ) {
				$save_path         = $this->final_save_path( $path, $cntr );
				$converted_paths[] = $save_path;
				$this->print_single_pdf( $path, $save_path );
				$cntr ++;
			}
		}

		return $converted_paths;
	}

	protected function final_save_path( $path, $cntr ) {
		$raw_path = explode( '.pdf', $path );

		return $raw_path[0] . "_$cntr.pdf";

	}

	protected function template( $path ) {
		$output = $this->output_css();
		$output .= "<div class='container'>";
		$output .= "<img src=\"$path\">";
		$output .= "</div>";

		return $output;
	}


	protected function print_single_pdf( $path, $save_path ) {
		$content = $this->template( $path );
		$dompdf  = new Dompdf();
		$dompdf->loadHtml( $content );

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper( 'A4', 'portrait' );

		// Render the HTML as PDF
		$dompdf->render();
		// Output the generated PDF to Browser
		$output = $dompdf->output();
		file_put_contents( $save_path, $output );
	}


	public function output_css() {
		$output = "<style>
			p {margin-top: 0;}
		    .container{width:100%;}
		    img{max-width:100%; height:auto;}
        </style>\n";

		return $output;
	}
}