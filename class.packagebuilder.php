<?php

class PackageBuilder {

	function __construct($package) {
		
		$this->package = $package;
		$this->type = $package['file_type'];
		
	}
	
	public function get_package() {
	
		$file = $this->get_packaged_file_name();
		
		if(file_exists($file)) {

			return $file;
			
		} else {

			$this->build_package($this->type);

		}
		
	}
	
	public function get_packaged_file_name() {
		
		$file_data = $this->package['package'];
		
		$full_name = '.' . $file_data['directory'] . $file_data['base_file_name'] . '.' . $file_data['version'] . '.' . $file_data['extension'];
		
		return $full_name;
		
	}
	
	public function build_package($type) {

		$this->include_minifier($type);
		
		$file = $this->get_packaged_file_name();
		$contents = $this->get_code($type);
		$minified_contents = $this->minify($type, $contents);
		
		file_put_contents($file, $minified_contents);
		
	}
	
	public function include_minifier($type) {
		
		if($type == 'js') {
			require('./lib/jsmin.php');	
		} else {
			require('./lib/cssmin.php');
		}
		
		
	}
	
	public function get_code($type) {
		
		$results = '';

		$files = $this->get_files();
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
	
	public function get_files() {
		
		$files = array();
		$directory = $this->package['assets']['directory'];
		
		foreach($this->package['assets']['files'] as $file) {
			
			$files[] = $directory.$file;
			
		}
		
		return $files;
 		
	}
	
	public function minify($type, $code) {
		
		$minifier = $this->get_minifier_name($type);
		
		return call_user_func_array(
			array($minifier, 'minify'), 
			array($code)
		);

	}
	
	public function get_minifier_name($type) {
		
		if($type == 'js') {
			return 'JSMin';
		} else {
			return 'cssmin';
		}
		
	}

}

?>