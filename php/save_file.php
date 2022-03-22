Save File
<?php
	// inherited from g2d_v2 version for saving data to sessiondir
	// test php/save_file.php?filename=Sequence.txt&sessionid=SessionID&pid=ppp&content=GATC:
	if ( isset($_REQUEST['filename']) and isset($_REQUEST['sessionid']) and isset($_REQUEST['pid']) and isset($_REQUEST['content']) ) {
		$sessionid = $_REQUEST['sessionid'] ;
		$pid = $_REQUEST['pid'] ;
		//if(!is_dir("/tmpdrive/$pid")) 				{ mkdir("/tmpdrive/$pid", 0777); } 
		if(!is_dir("/tmpdrive/$pid/$sessionid"))	{ mkdir("/tmpdrive/$pid/$sessionid", 0777) ; }
		$file = fopen("/tmpdrive/$pid/$sessionid/".$_REQUEST['filename'], 'w') ;
		if ($file) {
			fwrite($file, strip_tags($_REQUEST['content'])) ;
			fclose($file);
		}
	}	
?>

