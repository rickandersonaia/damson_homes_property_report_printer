<?php
/**
 * Created by PhpStorm.
 * User: ander
 * Date: 10/16/2017
 * Time: 2:02 PM
 */

use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

class dhprg_create_pdfs_from_images {
	protected $images_to_convert = array();

	public function __construct($images_to_convert) {
		$this->images_to_convert = $images_to_convert;
	}

	public function generate_pdfs(){
		foreach($this->images_to_convert as $original_pdf_file ){
			$cntr = 1;
			foreach($original_pdf_file['image_paths'] as $path ){
				$save_path = $this->final_save_path($path, $cntr);
				$converted_paths[] = $save_path;
				$this->print_single_pdf($path, $save_path);
				$cntr++;
			}
		}
		return $converted_paths;
	}

	protected function final_save_path($path, $cntr){
		$raw_path = explode('.pdf', $path);
		return $raw_path[0] . "_$cntr.pdf";

	}

	protected function template($path){
		$output = "<page backtop=\"9mm\" backbottom=\"9mm\" backleft=\"9mm\" backright=\"9mm\">";
		$output .= "<page_header>Damson Homes Report</page_header>\n\n";
		$output .= "<img src='$path' style='max-width:100%; height:auto;'>";
		$output .= "<page_footer>Rick says Hi!</page_footer>";
		$output .= "</page>";
		return $output;
	}



	protected function print_single_pdf($path, $save_path){
		$content = $this->template($path);

		try {

			$html2pdf = new Html2Pdf('P', 'A4', 'en');
			$html2pdf->setDefaultFont('Arial');

			$html2pdf->writeHTML($content);

			$html2pdf->output($save_path, 'F');
		} catch (Html2PdfException $e) {
			$formatter = new ExceptionFormatter($e);
			echo $formatter->getHtmlMessage();
		}
	}
}