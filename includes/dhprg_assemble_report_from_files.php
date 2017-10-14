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

	public function test_imagic() {
		$rotate  = false;
		$cntr    = 1;
		$counter = new Imagick( WP_CONTENT_DIR . '/uploads/2017/01/MapSearch-20170113-145846.pdf' );
		if ( $counter->readImage( WP_CONTENT_DIR . '/uploads/2017/01/MapSearch-20170113-145846.pdf' ) ) {
			$cntr   = $counter->getNumberImages();
			$height = $counter->getImageHeight();
			$width  = $counter->getImageWidth();
			$rotate = ( $height < $width ) ? true : false;
			$counter->clear();
		} else {
			return;
		}


		$imagick = new Imagick();
		$imagick->setResolution( 300, 300 );
		$imagick->setCompressionQuality( 100 );

		$x = 0;
		while ( $x < $cntr ) {
			try {
				$imagick->readImage( WP_CONTENT_DIR . "/uploads/2017/01/MapSearch-20170113-145846.pdf[$x]" );
				$imagick->setImageBackgroundColor( 'white' );
				$imagick->setImageAlphaChannel( imagick::ALPHACHANNEL_REMOVE );
				$imagick->mergeImageLayers( imagick::LAYERMETHOD_FLATTEN );
				if ( $rotate ) {
					$imagick->rotateImage( 'white', 270 );
				}

				// Writes an image
				$imagick->writeImage( REPORT_PATH . "/MapSearch-20170113-145846_$x.jpg" );
				$x ++;
			}
			catch(Exception $e){
				$x ++;
			}
		}
	}

	public function get_list_of_attached_pdf_paths(){
		$files    = array();
		$pdf_list = get_attached_media( 'application/pdf', $this->post_id );
		if ( empty( $pdf_list ) ) {
			return false;
		}

		foreach ( $pdf_list as $file ) {
			$files[] = get_attached_file( $file->ID );
		}
		return $files;
	}


	public function test_for_encryption( $file ) {
		$pdf = new Fpdi\Fpdi();
		try {
			$pageCount = $pdf->setSourceFile( $file );

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}
}