<?php

class Package {
	
	public static $packages = array(
		
		// Package name
		'js' =>	array(
			// File extension (js, css)
			'file_type' => 'js',
			// Specifi package info
			'package' => array(
				// Version, this should be bumped when you want a new file
				'version'  => 6,
				'directory' => '/js/',
				'extension' => 'js',
				'base_file_name' => 'package'
			),
			'assets' => array(
				// Files to compress into the package
				'files' => array(
					'jQueryMissingTools.js',
					'class.contactform.js',
					'class.painter.js',
					'class.socialactivity.js',
					'class.gallery.js',
					'class.util.js',
					'jquery.fancybox-1.3.4.js'
				)
			)
		),
		
		'css' =>	array(
			'file_type' => 'css',
			'package' => array(
				'version'  => 1,
				'directory' => '/css/',
				'extension' => 'css',
				'base_file_name' => 'package'
			),
			'assets' => array(
				'files' => array(
					'reset.css',
					'style.css',
					'jquery.fancybox-1.3.4.css'
				)
			)
		)

	);
	
	public static function get_package($name) {
		
		require('class.packagebuilder.php');
		
		$package = self::$packages[$name];
		
		$package_builder = new PackageBuilder($package);

		echo $package_builder->get_package();
		 
		
	}
	
}

?>