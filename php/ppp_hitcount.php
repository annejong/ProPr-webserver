<?php

    $hitcountdir='/tmpdrive/PPP';

	//opens countlog.txt to read the number of hits
	$file = fopen("$hitcountdir/hitcount.txt","r");
	$count = fgets($file,1000);
	fclose($file);

	echo "$count  ";	
	
	// add 1 and write updated counter
	$count=$count + 1 ;
	$file = fopen("$hitcountdir/hitcount.txt","w");
	fwrite($file, $count);
	fclose($file);

	echo "$count updated ";	
	
	// write the last date
	$file = fopen("$hitcountdir/datelog.txt","w");
	fwrite($file, date("l M-d h:i (Y)"));
	fclose($file);
	//show_date($hitcountdir);


function show_date($hitcountdir)
{
	//opens countlog.txt to read the number of hits
	$file = fopen("$hitcountdir");
	$lastdate = fgets($file,1000);
	fclose($file);
	echo "$lastdate";
}

?>
