<?php
	require_once('constants.php');
	require_once('functions.php');
	
	$js_spec = array();
	
	// LESS to CSS compiling when MODE is DEV
	// Please skip the compilation when the site is online
	/*if(MODE == "DEV")
	{
		require_once('lessPHP/Less.php');
	
		$less_file = LESS_PATH.LESS_FILE.'.less';
		
		try
		{
			$less_options = array( 'compress'=>true );
			$less_parser = new Less_Parser($less_options);
			$less_parser->parseFile($less_file);
			file_put_contents(CSS_PATH.CSS_FILE.'.css', $less_parser->getCss());
		}
		catch(Exception $e)
		{
			exit('lessc fatal error:<br />'.$e->getMessage());
		}
	}*/
?>