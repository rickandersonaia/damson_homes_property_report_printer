<?php
/**
 * Created by PhpStorm.
 * User: ander
 * Date: 10/16/2017
 * Time: 10:25 AM
 */

class dhprg_create_images_from_pdfs {
	protected $pdfs_to_convert = array();
	protected $images = array();
	protected $slug = '';

	public function __construct( $pdfs_to_convert, $slug ) {
		$this->pdfs_to_convert = $pdfs_to_convert;
		$this->slug = $slug;
		$this->set_images();
	}

	protected function set_images(){
		$this->images = $this->create_images();
	}

	public function get_images(){
		return $this->images;
	}

	protected function create_images(){
		foreach($this->pdfs_to_convert as $pdf_filename => $path){
			$images[$pdf_filename]['old_path'] = $path;
			$images[$pdf_filename]['image_paths'] = $this->convert_using_imagic($pdf_filename, $path);
		}
		return $images;
	}

	protected function convert_using_imagic($pdf_filename, $path) {
		$pdf_data['rotate']  = false;
		$pdf_data['cntr']    = 1;
		$pdf_data = $this->get_pdf_data($path);

		if($pdf_data) {
			$imagick = new Imagick();
			$imagick->setResolution( 300, 300 );
			$imagick->setCompressionQuality( 100 );

			$x = 0;
			while ( $x < $pdf_data['cntr'] ) {
				try {
					$imagick->readImage( $path . "[$x]" );
					$imagick->setImageBackgroundColor( 'white' );
					$imagick->setImageAlphaChannel( imagick::ALPHACHANNEL_REMOVE );
					$imagick->mergeImageLayers( imagick::LAYERMETHOD_FLATTEN );
					if ( $pdf_data['rotate'] ) {
						$imagick->rotateImage( 'white', 270 );
					}
					$image_path    = REPORT_PATH . $this->slug . "/$pdf_filename" . "_$x.jpg";
					$image_paths[] = $image_path;
					// Writes an image
					$imagick->writeImage( $image_path );
					$x ++;
				} catch ( Exception $e ) {
					$x ++;
				}
			}

			return $image_paths;
		}else{
			return false;
		}
	}

	protected function get_pdf_data($path){
		$pdf_data = array();
		$counter = new Imagick( $path );
		if ( $counter->readImage( $path ) ) {
			$pdf_data['cntr']   = $counter->getNumberImages();
			$pdf_data['height'] = $counter->getImageHeight();
			$pdf_data['width']  = $counter->getImageWidth();
			$pdf_data['rotate'] = ( $pdf_data['height'] < $pdf_data['width'] ) ? true : false;
			$counter->clear();
		} else{
			return false;
		}
		return $pdf_data;
	}
}

