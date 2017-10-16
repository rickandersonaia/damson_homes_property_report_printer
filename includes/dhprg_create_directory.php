<?php
/**
 * Created by PhpStorm.
 * User: ander
 * Date: 10/16/2017
 * Time: 7:51 AM
 */

class dhprg_create_directory {

	public $address = '';

	public function __construct($address) {
		$this->address = $address;
		include_once ABSPATH . '/wp-admin/includes/file.php';
	}

	public function create_directory(){
		$access_type = get_filesystem_method();
		if($access_type === 'direct'){
			/* you can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
			$creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());

			/* initialize the API */
			if ( ! WP_Filesystem($creds) ) {
				/* any problems and we exit */
				return false;
			}

			global $wp_filesystem;
			/* replace the 'direct' absolute path with the Filesystem API path */
			$upload_path = str_replace(ABSPATH, $wp_filesystem->abspath(), WP_CONTENT_DIR . '/uploads/' . $this->address);

			/* Now we can use path in all our Filesystem API method calls */
			if(!$wp_filesystem->is_dir($upload_path)){
				/* directory didn't exist, so let's create it */
				$wp_filesystem->mkdir($upload_path);
				return;
			}
		}
		else
		{
			/* don't have direct write access. Prompt user with our notice */
			add_action('admin_notices', 'you_admin_notice_function');
		}
	}


}