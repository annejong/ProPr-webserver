<?php
	# PPP Dropzone file upload
	$sessiondir = $_REQUEST[sessiondir] ;
	
	if (!file_exists($sessiondir)) {  mkdir($sessiondir, 0777, true); }
	$path_parts = pathinfo($_FILES['file']['name']);
	print "Uploading file <br>" ;
	print "Extension=".$path_parts['extension'];
	$extensions = array("fna","fasta","gff","gff3","jpg") ;  # extensions allowed
	if (in_array($path_parts['extension'], $extensions)) {
		$safeName      = preg_replace("/[^a-zA-Z0-9.]+/", "", $_FILES['file']['name']); // remove unwanted chars before uploading
		move_uploaded_file($_FILES['file']['tmp_name'], $sessiondir.'/'.$safeName);	
	} else {
		print "WRONG file format" ;
	}		
?>  