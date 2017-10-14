<?php
/**
 * Created by PhpStorm.
 * User: ander
 * Date: 10/14/2017
 * Time: 5:29 AM
 */
use \setasign\Fpdi;

class dhprg_assemble_report_from_files {

	public $post_id;

	public function __construct($post_id) {
		$this->post_id = $post_id;
	}

	public function test(){
		// define some files to concatenate
		$files = array(
			WP_CONTENT_DIR . '/uploads/2017/01/Manual-_-Setasign.pdf',
			WP_CONTENT_DIR . '/uploads/2017/04/MapSearch-20170407-170155.pdf'
		);

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
					$pdf->Write( 8, 'A simple concatenation demo with FPDI' );
				}
			}
		}

// Output the new PDF
		$pdf->Output();
	}



	public function test_for_encryption( $file ) {
		$pdf       = new Fpdi\Fpdi();
		try{
			$pageCount = $pdf->setSourceFile( $file );
			return true;
		}
		catch(Exception $e){
			return false;
		}
	}
}