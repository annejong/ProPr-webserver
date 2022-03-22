<!--

PPP - Promoter Prediction Prokaryotes

Anne de Jong 

2021 June 

------------------------------------   HTML  ------------------------------------------>
<html>
<title>Prokaryote Promoter Prediction</title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">


<head>

	<!-- jquery -->
	<script src="js/jquery-3.5.0.min.js"></script>
	<script src="js/jquery-ui.js"></script> 
	<script src="js/jquery.tablesorter.min.js"></script> 
	<script src="js/jquery.tablesorter.widgets.js"></script> 

	<!-- Bootstrap -->
	<script src="js/bootstrap501/js/bootstrap.min.js" ></script>
	<link rel="stylesheet" href="js/bootstrap501/css/bootstrap.min.css">

	<!-- Dropzone -->
	<script src="js/dropzone57/dropzone.min.js"></script>
	<link rel="stylesheet" href="js/dropzone57/dropzone.min.css">
	<style>
		.dropzone { background-color: #b2d3ed; }

		#myProgressBarEmpty {
		  width: 100%;
		  background-color: #ddd;
		}
		#myProgressBarFill {
		  width: 1%;
		  height: 30px;
		  background-color: #167a87;
		} 
		
		tr:hover {	background-color: #cadcde;	}
		tr:nth-child(even) {background-color: #f2f2f2;}
		th { background-color: #096e75;  color: #f2feff;  text-align: center; }
		td { padding:2px 10px 2px 3px; }
		
		.buttonDownload {
			background-color: #b33c00; 
			border: none;
			color: white;
			padding: 4px 24px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			font-size: 12px;
			width: 200px;
		}

		.footer { font-size: 12px; line-height: 90%; text-align: right; }
			
		.DNA { font-family: courier; }
		
		.box{ width:100%; height:180px; } 
 
		
	</style>	

	<!-- D3 -->
	<script src="https://d3js.org/d3.v5.min.js"></script> 
	<script src="js/d3-tip.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/d3-legend/2.25.6/d3-legend.min.js"></script>


	<!-- GSEA-Pro -->
	<script src="js/FileSaver.min.js"></script>
	<!--link rel="stylesheet" href="gseapro_v3.css?v1.1"-->
</head>

<body>  
<div class="bg">
	<div class="container">
		<div id=Text_Intro></div>
		
		<!-- --------------------------------  INPUT --------------------------------- -->
		<div id=dropzone>
			<div class="row">
				<div class="col-sm-10">
					Drop or upload DNA sequence (<b>75 - 200,000bp</b>) below as a fasta file (accepted extensions; .fna .fasta) <a href='examples/S_aureus_USA300_TCH1516_100k.fna' download>[download example 100kbp]</a><br>
					Optionally, drop or upload an annotation file (accepted extensions; .gff .gff3) <a href='examples/S_aureus_USA300_TCH1516.gff' download>[download example]</a><br> 
					<a href='ppp_v1.manual.html' target=_blank>For more information read the manual</a><br>
					
					<br>
					<div id=pppDropzone class="dropzone box"></div>
					<br>
					<div class=bg-light>
						<input type="checkbox" id="TTchk" name="TT" value="TT"> Include Transcription Terminator prediction<br>
						<input type="checkbox" id="Interchk" name="Intergenic" value="Intergenic" > Only predict promoters in Intergenic regions (.gff file needed)<br>
						<hr>
						Advanced options;<br>
						<input type="checkbox" id="GCchk" name="GCfromInput" value="GCfromInput" onchange="toggle_GCinput()" checked> Use GC% from input data
						<div id="GCinput"> Type GC% for analysis <input type="number" id="GCinput" name="GCvalue"  min="20" max="100" value=50></div>
						<div id=ModelSelectList></div>
						<div id=ModelSelected></div>
					</div>
					<br>
					<button type=button class=GeneralButton onclick="Start_run()" id=start_analysis>Start analysis</button>	
				</div>
			</div>
		</div>		
		
		<br>

		<!-- --------------------------------  RESULTS --------------------------------- -->
		<div id=results>	
			<!-- HEADER -->
			<div class="row">			
				<div class="col-sm-8 align-self-end">
					<button type=button class=buttonDownload onclick="Download_Result('ppp.Promoters.txt')" >Download Promoters <br> as Table</button>	
					<button type=button class=buttonDownload onclick="Download_Result('ppp.Promoters.gff')" >Download Promoters <br> as GFF</button>	
					<button type=button class=buttonDownload onclick="Download_Result('ppp.Genes_Promoters.gff')" >Download Genes and Promoters as GFF</button>	
					<hr>	
				</div>
				<div class="col-sm-4">
					<hr>
					<h5><div id=BookmarkResults>Bookmark your results here</div>
					<hr>
					<a href=http://ppp.molgenrug.nl>Start a new session<a></h5>
					<hr>
				</div>
			</div>
			<!-- RESULT TABLE -->
			<div class="row">
				<div class="col-sm-12">
					<div id=BookmarkResults></div>
					<div id=ResultGraphics></div>
					<div id=ResultTable></div>
				</div>
			</div>
			<!-- DOWNLOAD BUTTONS -->
			<div class="row">
				<div class="col-sm-6">
				</div>
			</div>	
			
		</div>
		<br>


		
		<!-- --------------------------------  MESSAGES --------------------------------- -->
		<div class="row">
			<div class="col-sm-12">
				<div id=SessionProgress></div>
				<div id="MessageBox" class="bg-secondary text-white">No data</div>
				<div id="myProgressBarEmpty"><div id="myProgressBarFill"></div></div>
			</div>
		</div>	

		<hr>
		<div id=Text_Footer class=footer></div>
	</div>
</div>


<!-- -------------------------------------------- functions ----------------------------------- -->
<?php
	session_start(); 
	$sessionID = $_SERVER['REMOTE_ADDR'].'.'.session_id().'.'.rand(1, 1000) ; 
?>
	
<script>

	
	var PID="PPP";
	var sessionID = "<?php echo $sessionID;?>";
	var sessionurl = 'pppresults/'+sessionID ;
	var sessiondir = '/tmpdrive/PPP/'+sessionID ;
	var urlSessionID = '' ;
	var programdir = '/data/ppp' ;
	// https://www.dropzonejs.com/bootstrap.html
	Dropzone.autoDiscover = false;
	var myDropzone = new Dropzone("#pppDropzone", { 
		url: "php/dropzone_upload.php?sessiondir="+sessiondir,
		maxFilesize: 3 // MB
	});
	var ValidDNA = 'false' ;
	var ModelSelected = 'GC40';

	function SelectedModel(selected) { 
		ModelSelected = selected.value; 
		document.getElementById("ModelSelected").innerHTML = 'Prediction will be done on the basis of <i><b>'+ModelSelected+'</b></i> data'  ;
		document.getElementById("GCchk").checked = false ;
	}

	function MakeModelSelectList(Models) {
		document.getElementById("ModelSelectList").style.display = "none";
		var html = 'Optionally select specific Model <select onchange="SelectedModel(this)">';
		for (let i = 0; i < Models.length; i++) {
			if (Models[i].length>2) {
				html += '<option value='+Models[i]+'>'+Models[i]+'</option>' ;
			}	
		}
		html += '</select>' ;
		document.getElementById("ModelSelectList").innerHTML = html ;
	}
	
	function Start_run() {
		FileCount = myDropzone.files.length ;
		if (ValidDNA == 'true' ) { 
			document.getElementById("MessageBox").innerHTML = 'SessionID: '+sessionID ;
			ShellExec = programdir+'/00.ppp.sh '+ sessiondir +' '+	ModelSelected ;
			$.post( "php/ppp_shellexec.php", { sessiondir: sessiondir, content: ShellExec } ) ;
			progress(100);	
		} else { 
			document.getElementById("MessageBox").innerHTML = 'No valid data uploaded' ;
		} ;
	}		
 	
	function AddTextElements(ID) {
		// get text from 'ppp_v1.txt' ; ID text = ID element
		$.get({url: 'ppp_v1.txt', cache: false}).done( function(data) {
			var RecordFound = false ;
			var htmlText = '';
			var lines = data.split('\n') ;
			lines.forEach(function (line) {
				if (line.startsWith('//') && RecordFound) {
					document.getElementById(ID).innerHTML = htmlText ; 
					RecordFound = false ;
				}	
				if (RecordFound) { htmlText += line ; }
				if (line.startsWith('#'+ID)) { RecordFound = true; }
			});	
		});	
	}

		
	function progress(counter){
		// follow the progress until finished. Counter in seconds
		document.getElementById("myProgressBarFill").style.width = (100-(counter)) + '%'; 
		$.get(sessionurl+'/ppp.Promoters.txt', function(data) {
			$.post("php/ppp_hitcount.php") ;
			document.getElementById("dropzone").style.display = "none";
			document.getElementById("results").style.display = "block";
			document.getElementById("SessionProgress").innerHTML = '';
			Bookmark = '<h4><a href=http://ppp.molgenrug.nl?SessionID='+sessionID+' target=_blank>Bookmark results here</a></h4>';
			document.getElementById("BookmarkResults").innerHTML = Bookmark;
			Show_ResultTable(data,sessionID) ; // return list of all chroms aka scaffolds / nodes 
		})
			.fail(function() { 
				$.get({url: sessionurl+'/00.ppp.log', cache: false}).done( function(data) { 
					data = data.split(/\n/);
					document.getElementById("SessionProgress").innerHTML = data.join('<br>') ; 
				}) ;
				if(counter > 0){
					setTimeout(function(){
					  counter = counter-0.3;
					  console.log(counter);
					  progress(counter);
					}, 1000);
				} else { document.getElementById("MessageBox").innerHTML = 'Analysis error due to time out' }
			}) ;
	}
	
	
	function chk_file_content(data) {
		lines = data.split(/\n/);
		console.log(lines[0]);
		if (lines[0].charAt(0) == '>') {
			if (data.match(/\>/g).length > 1) {
				ValidDNA = 'false'; 
				document.getElementById("start_analysis").style.display = "none";
				document.getElementById("MessageBox").innerHTML += "WARNING: Multiple Fasta records detected: The web server only allows Fasta files with one DNA record <br>" ;
			} else {	
				document.getElementById("MessageBox").innerHTML = "FASTA header: " + lines[0] +"<br>" ;
				lines.shift() ;
				var dna = lines.join().toUpperCase()  ;
				var len = dna.match(/G|C|A|T/g).length ;
				var GC = 100 * dna.match(/G|C/g).length / len ; 
				// Update GC for autoselect model e.g., GC35
				ModelSelected = "GC"+Math.round(GC).toString();
				document.getElementById("MessageBox").innerHTML += "DNA length: " + String(len) +"<br>";
				document.getElementById("MessageBox").innerHTML += "GC: " + GC +"%<br>";
				if (len > 75 && len < 220000) {
					ValidDNA = 'true'; 
					document.getElementById("start_analysis").style.display = "block";
				} else {
					ValidDNA = 'false'; 
					document.getElementById("start_analysis").style.display = "none";
					document.getElementById("MessageBox").innerHTML += "WARNING: input limits are >75bp and <200,000bp. Upload smaller fragments or download the stand-alone version <br>" ;
				}
			}	
		}
	}
	
	function  Show_ResultTable(data,sessionID) {
		var chroms = [] ; // make list of all chroms aka scaffolds / nodes 
		var TableBody = '<table class=table1 >' ;
		data = data.split(/\n/);
		// the table header
		var items = data[0].split(/\t/); 
		TableBody += '<thead><tr><th>' + items.join('</th><th>') + '</th></tr></thead>' ;
		// the table body
		TableBody += '<tbody>'; 
		for(var i = 1; i < data.length-1; i++){	
			items = data[i].split(/\t/);
			if (items.length>2) { // skip blank rows
				if (chroms.indexOf(items[0]) === -1) chroms.push(items[0]) ; // add chrom is not already in the list
				items[6] = items[6].substring(0, 35)+'<b>'+items[6].substring(35, 42)+'</b>'+items[6].substring(42, 58)+'<b>'+items[6].substring(58, 66)+'</b>'+items[6].substring(66,71) ;
				TableBody += '<tr class=DNA ><td>' + items.join('</td><td>') + '</td></tr>' ;
			}	
		}
		TableBody +=  '</tbody></table>' ;
		document.getElementById("ResultTable").innerHTML = TableBody ;
		
		$(function() {
			$(".table1").tablesorter({ 
				theme : 'blue',			
				sortList: [[0,0], [1,0]] 
			});
		});
		
		document.getElementById("ResultGraphics").innerHTML = 'Graphics for chroms;<br>' ;
		for (let i = 0; i < chroms.length; i++) {
			document.getElementById("ResultGraphics").innerHTML += '<a href=http://ppp.molgenrug.nl/pppresults/'+sessionID+'/D3GB/index.html?r='+chroms[i]+':0-10000 target=_blank>'+chroms[i]+'</a><br>' ;
		}	
		document.getElementById("ResultGraphics").innerHTML += '<hr>' ;	
		
	}

	function download(url, filename) {
		fetch(url).then(function(t) {
			return t.blob().then((b)=>{
				var a = document.createElement("a");
				a.href = URL.createObjectURL(b);
				a.setAttribute("download", filename);
				a.click();
			}
			);
		});
	}

	function Download_Result(filename) {
		if (urlSessionID != '' ) {  sessionurl = 'pppresults/'+urlSessionID; }
		document.getElementById("MessageBox").innerHTML = sessionurl+'/'+filename ;
		download('http://ppp.molgenrug.nl/'+sessionurl+'/'+filename, filename)
	}

	function toggle_GCinput() {
		if (document.getElementById("GCchk").checked) {
			document.getElementById("ModelSelectList").style.display = "none";
		} else {
			document.getElementById("ModelSelectList").style.display = "block";
		}
	}

	function getUrlVars() {
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++) {
			hash = hashes[i].split('=');
			hash[1] = unescape(hash[1]);
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	}


	$(document).ready(function() {	
		
		AddTextElements("Text_Intro") ;
		AddTextElements("Text_Footer") ;
		
		document.getElementById("dropzone").style.display = "block";
		document.getElementById("GCinput").style.display = "none";
		document.getElementById("results").style.display = "none";
		document.getElementById("start_analysis").style.display = "none";

		// configure Dropzone
		// https://www.dropzonejs.com/bootstrap.html
		Dropzone.options.myDropzone = {
			paramName: "file", // The name that will be used for checking and transfer the file
			
			acceptedFiles: ".fna,.fasta,.gff,.gff3",
			accept: function(file, done) {
				document.getElementById("MessageBox").innerHTML = "Uploaded filename: " + file.name ;
				done(); 
			}
		}

		myDropzone.on("addedfile", function(file) {  // checking the content
			// document.getElementById("MessageBox").innerHTML = "Checking: " + file.name ;
			var reader = new FileReader();
			reader.onload = function(event) { chk_file_content(event.target.result) ;	};
			reader.readAsText(file);
		});

		// Process URL vars
		var	urlVars = getUrlVars();
		if (urlVars['SessionID'] != undefined ) {   // show stored session
			urlSessionID = urlVars['SessionID'] ;
			document.getElementById("MessageBox").innerHTML = "sessiondID=> " + urlVars['SessionID'] + '<br>' ;	
			$.get(['pppresults/'+urlVars['SessionID']+'/ppp.Promoters.txt'])
				.done(function(data) {
					document.getElementById("dropzone").style.display = "none";
					document.getElementById("results").style.display = "block";
					Show_ResultTable(data,urlVars['SessionID']) ; 
			});
		}
		
		// Make Selection list of CNN models
		$.get({url: 'Models.txt', cache: false}).done( function(data) { MakeModelSelectList(data.split(/\n/)); });
		
		
	});	

</script>


</body>
</html>
