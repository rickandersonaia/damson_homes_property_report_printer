<?php
/**
 * Created by PhpStorm.
 * User: ander
 * Date: 10/16/2017
 * Time: 8:20 AM
 */

use \setasign\Fpdi;

class dhprg_sort_attachment_pdfs {


	public $post_id;

	protected $sorted_pdfs = array();
	protected $list_of_pdf_attachments = array();
	protected $pdf_path_list = array();

	public function __construct( $post_id ) {
		$this->post_id = $post_id;
		$this->set_sorted_pdfs();
	}

	public function set_sorted_pdfs(){
		$this->list_of_pdf_attachments = $this->get_list_of_attached_pdfs();
		$this->pdf_path_list = $this->get_pdf_paths($this->list_of_pdf_attachments);
		$this->sorted_pdfs = $this->test_for_encryption_and_sort($this->pdf_path_list);
	}

	public function get_sorted_pdfs(){
		return $this->sorted_pdfs;
	}

	public function get_list_of_attached_pdfs() {
		$pdf_list    = array();// array of post objects
		$pdf_list = get_attached_media( 'application/pdf', $this->post_id );
		if ( empty( $pdf_list ) ) {
			return false;
		}
		return $pdf_list;
	}

	protected function get_pdf_paths($pdf_list){
		$pdf_path_list = array(); // array of absolute paths to pdf attachments
		foreach($pdf_list as $attachment_file){
			$filename = basename( get_attached_file( $attachment_file->ID ) );
			$pdf_path_list[$filename] = get_attached_file( $attachment_file->ID );
		}
		return $pdf_path_list;
	}


	protected function test_for_encryption_and_sort($pdf_path_list) {

		foreach($pdf_path_list as $filename => $path){
			$pdf = new Fpdi\Fpdi();
			try {
				$pdf_ready                        = $pdf->setSourceFile( $path );
				$this->sorted_pdfs['ready'][$filename] = $path;

			} catch ( Exception $e ) {
				$this->sorted_pdfs['need_to_convert'][$filename] = $path;

			}
		}

		return $this->sorted_pdfs;
	}

}
