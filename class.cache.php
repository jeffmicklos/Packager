<?php

class Cache {
	
	public static $site = 'http://jeffmicklos.com';
	
	public static $packaged_files = array(
		'js' => array(
			'version'		 => 1
			'directory' 	 => '/js/',
			'extension'		 => 'js',
			'base_file_name' => 'package'
		),
		'css' => array(
			'version'		 => 1
			'directory' 	 => '/css/',
			'extension'		 => 'css',
			'base_file_name' => 'package'
		)
	);

	public static $unpackaged_js_files = array(
		'/js/jQueryMissingTools.js',
		'/js/class.contactform.js',
		'/js/class.painter.js',
		'/js/class.socialactivity.js',
		'/js/class.gallery.js',
		'class.util.js',
		'/js//js/jquery.fancybox-1.3.4.js'
	);
		
	public static $unpackaged_css_files = array(
		'/css/reset.css',
		'/css/style.css',
		'/css/jquery.fancybox-1.3.4.css'
	);
	
	public static function get_files($type) {
		
		if($type == 'js') {
			return self::$unpackaged_js_files;
		} else {
			return self::$unpackaged_css_files;
		}
 		
	}
	
	public static function get_packaged_file_name($type) {
		
		$file_data = self::$packaged_files[$type];
		
		$full_name = $file_data['directory'] . $file_data['base_file_name'] . '.' . $file_data['version'] '.' . $file_data['extension'];
		
		return $full_name;
		
	}
	
	public static function getFile($type){
		
		$file = self::get_packaged_file_name($type);
		
		if(file_exists($file)) {
			
			return $file;
			
		} else {
		
			self::build_package($type);

		}
		
	}
	
	public static function include_minifier($type) {
		
		if($type == 'js') {
			require('jsmin.php');	
		} else {
			require('cssmin.php');
		}
		
		
	}
	
	public static function get_minifier_name($type) {
		
		if($type == 'js') {
			return 'JSMin';
		} else {
			return 'cssmin';
		}
		
	}
	
	public static function minify($type, $code) {
		
		$minifier = self::get_minifier_name($type);
		
		call_user_func_array(
			array($minifier, 'minify'), 
			array($code)
		);

	}
	
	public static function build_package($type) {

		self::include_minifier($type);
		
		$file = self::get_packaged_file_name($type);
		$contents = self::get_code($type);
		$minified_contents = self::minify($type, $contents);
		
		file_put_contents($file, $code, FILE_APPEND);
		
	}
	
	public static function get_code($type) {
		
		$results = '';

		$files = self::get_files($type);
		$number_of_files = count($files);
		
		$curl_arr = array();
		$master = curl_multi_init();
		
		for($i = 0; $i < $number_of_files; $i++) {
			$url = $files[$i];
			$curl_arr[$i] = curl_init($url);
			curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
			curl_multi_add_handle($master, $curl_arr[$i]);
		}
		
		do {
			curl_multi_exec($master, $running);
		} while($running > 0);
		
		for($i = 0; $i < $number_of_files; $i++) {
			$results .= curl_multi_getcontent($curl_arr[$i]);
		}
		
		return $results;
		
	}

}