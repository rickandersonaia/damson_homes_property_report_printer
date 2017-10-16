<?php
/**
 * Created by PhpStorm.
 * User: ander
 * Date: 10/14/2017
 * Time: 5:29 AM
 */

//@todo - try using imagrick to create jpg of encrypted pdfs

use \setasign\Fpdi;

class dhprg_assemble_report_from_files {

	public $post_id;

	public function __construct( $post_id ) {
		$this->post_id = $post_id;
	}

	public function test() {
		$files    = $this->get_list_of_attached_pdf_paths();
		if ( empty( $files ) ) {
			return false;
		}

// initiate FPDI
		$pdf = new Fpdi\Fpdi();

// iterate through the files
		foreach ( $files AS $file ) {
			$not_encrypted = $this->test_for_encryption( $file );
			if ( $not_encrypted ) {

				// get the page count
				$pageCount = $pdf->setSourceFile( $file );
				// iterate through all pages
				for ( $pageNo = 1; $pageNo <= $pageCount; $pageNo ++ ) {
					// import a page
					$templateId = $pdf->importPage( $pageNo );
					// get the size of the imported page
					$size = $pdf->getTemplateSize( $templateId );

					// add a page with the same orientation and size
					$pdf->AddPage( $size['orientation'], $size );

					// use the imported page
					$pdf->useTemplate( $templateId );

					$pdf->SetFont( 'Helvetica' );
					$pdf->SetXY( 5, 5 );
					$pdf->Write( 8, 'Damson Homes test' );
				}
			}
		}

// Output the new PDF
		$pdf->Output();
	}


}