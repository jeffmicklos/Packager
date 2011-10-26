<?php

class PackageBuilder {

	const JS_MINIFIER_PATH = './libs/jsmin.php';
	const CSS_MINIFIER_PATH = './libs/cssmin.php';

	const JS_MINIFIER_NAME= 'JSMin';
	const CSS_MINIFIER_NAME = 'cssmin';

	function __construct($package) {

		$this->package = $package;
		$this->type = $package['file_type'];

	}

	/**
	* Checks if packaged file already exists,
	* if not, build a new package
	*
	* @return string $file the package path
	*/
	public function get_package() {

		$file = $this->get_packaged_file_name();

		if(file_exists($file)) {

			return $this->get_url_from_path($file);

		} else {

			$this->build_package();

		}

	}

	/**
	* Compiles the name/path of a package file from it's array
	*
	* @return string $full_name name of the packaged file
	*/
	private function get_packaged_file_name() {

		$file_data = $this->package['package'];

		$full_name = $_SERVER['DOCUMENT_ROOT'] . $file_data['directory'] . $file_data['base_file_name'] . '.' . $file_data['version'] . '.' . $file_data['extension'];
			
		return $full_name;

	}

	/**
	* Puts minified and concat'd code into file
	*/
	private function build_package() {

		$this->include_minifier();

		$file = $this->get_packaged_file_name();
		$contents = $this->get_code();
		$minified_contents = $this->minify($contents);

		file_put_contents($file, $minified_contents);

	}

	/**
	* Includes the desired minification script based on file type
	*/
	private function include_minifier() {

		if($this->type == 'js') {
			require(self::JS_MINIFIER_PATH);	
		} else {
			require(self::CSS_MINIFIER_PATH);
		}


	}

	/**
	* cURLs to all unpackaged files and returns them all contact'd
	
	* @return string $results source from all files
	*/
	private function get_code() {

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
			$ex = curl_multi_exec($master, $running);
		} while($running > 0);

		for($i = 0; $i < $number_of_files; $i++) {
			$results .= curl_multi_getcontent($curl_arr[$i]);
		}
			
		return $results;

	}

	/**
	* @return array $files a list of files to compact
	*/
	private function get_files() {

		$files = array();
		$directory = $this->package['package']['directory'];

		foreach($this->package['assets']['files'] as $file) {

			$files[] = $file;

		}

		return $files;
 		
	}

	/**
	* Minfies code with external libs
	*/
	private function minify($code) {

		$minifier = $this->get_minifier_name($this->type);

		return call_user_func_array(
			array($minifier, 'minify'), 
			array($code)
		);

	}

	/**
	* @return string minififer name
	*/
	private function get_minifier_name() {

		if($this->type == 'js') {
			return self::JS_MINIFIER_NAME;
		} else {
			return self::CSS_MINIFIER_NAME;
		}

	}
	
	private function get_url_from_path($file, $scheme = 'http://') {
		return $scheme.$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
	}

}

?>