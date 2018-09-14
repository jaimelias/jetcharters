<?php

class Jaimelias_Debug {

	public static function log($log) {
		
		$debugfile = plugin_dir_path( __FILE__ )."debug.log";
		
		if ( is_array( $log ) || is_object( $log ) ) 
		{			
			file_put_contents($debugfile, print_r( $log, true ));
		} 
		else 
		{
			file_put_contents($debugfile, $log);
		}
	}

}