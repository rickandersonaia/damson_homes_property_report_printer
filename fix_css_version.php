<?php
/**
 * Created by PhpStorm.
 * User: ander
 * Date: 10/18/2017
 * Time: 8:54 AM
 */

function byob_add_daily_version_to_thesis_css(){
	$date = date("Y-m-d");
	wp_enqueue_script('thesis_css', THESIS_USER_SKIN_URL. '/css.css', false, $date);
}

add_action('init', 'byob_add_daily_version_to_thesis_css');

function byob_remove_thesis_css_from_filter( $link ){
	return __false;
}

add_filter('thesis_stylesheets_link', 'byob_remove_thesis_css_from_filter');