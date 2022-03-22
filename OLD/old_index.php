<!--

PPP - Promoter Prediction Prokaryotes

Anne de Jong 

2021 June 

------------------------------------   HTML  ------------------------------------------>
<html>
<title>Prokaryote Promoter Prediction</title>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">


<head>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-112736406-4"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'UA-112736406-4');
	</script>

	<!-- jquery -->
	<script src="js/jquery-3.5.0.min.js"></script>
	<script src="js/jquery-ui.js"></script> 

	<!-- Bootstrap -->
	<script src="js/bootstrap501/js/bootstrap.min.js" ></script>
	<link rel="stylesheet" href="js/bootstrap501/css/bootstrap.min.css">

	<!-- Dropzone -->
	<script src="js/dropzone57/dropzone.min.js"></script>


	<!-- D3 -->
	<script src="https://d3js.org/d3.v5.min.js"></script> 
	<script src="js/d3-tip.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/d3-legend/2.25.6/d3-legend.min.js"></script>

	<!-- Tools -->
	

	<!-- GSEA-Pro -->
	<script src="js/FileSaver.min.js"></script>
	<link rel="stylesheet" href="gseapro_v3.css?v1.1">
</head>

<body>  
<div class="bg">
	<div class="container">
		<h2><b>Pro<sup>2</sup>Pr</b>: Prokaryote Promoter Prediction v2.0</h2>
		<br>
		<div id=introduction> 
			Prediction of Prokaryote Promoter sequences and the Transcription Start Site
		</div>	
		<br>
		
		<form action="/file-upload" class="dropzone" id="ppp-dropzone"></form>
		
		
		<div id=inputform class=inputform>	
			<div class="row">
				<div class="col-sm-6">
					Paste (Multi-)Fasta DNA Sequence below (Max=100 entries or 100k bp)<br>
					<!-- <button type=button class=smallButton onclick="Load_Example('examples/PromoterPrediction.fasta','Sequence')">Example</button> -->
					<button type=button class=smallButton onclick="Load_Example('examples/NC_0009643.0.AOI_01.fna','Sequence')">Example</button>
					<textarea id=Sequence rows=10 cols=75></textarea><br>
					<button type=button class=WideButton onclick="Start_run()">Start Run</button>
				</div>
				<div class="col-sm-6">
					Optional: paste GFF data below (this will be used for visualisation of the results)<br> 
					<!-- <button type=button class=smallButton onclick="Load_Example('examples/PromoterPrediction.gff','GFF')">Example</button> -->
					<button type=button class=smallButton onclick="Load_Example('examples/NC_0009643.0.AOI_01.gff','GFF')">Example</button>
					<textarea id=GFF rows=10 cols=75 wrap="off"></textarea><br>
				</div>	
			</div>
			<div class="row">
				<div class="col-sm-3"></div>
				<div class="col-sm-6">
					<br>For debugging: Paste PPP result json result here
					<textarea id=ppp_results rows=10 cols=75></textarea><br>
				</div>
			</div>
			
			<div id="myProgressBarEmpty"><div id="myProgressBarFill"></div></div><br>
			
		</div>
		<br>
		<div id=results>
			<div class="row">
				<div class="col-sm-8">
					<div id=ResultHeader></div>
					<div id=ResultText></div>
				</div>
				<div class="col-sm-2">
					<button type=button class=GeneralButton onclick="save_result()" >Download Result</button>	
				</div>
			</div>
		</div>
		<br>
		
			<div id="MessageBox"><br>

		
		<hr>
		<div id=test></div>
	</div>


</div>



<!-- -------------------------------------------- functions ----------------------------------- -->


<script>
	//var select_bloc = document.getElementById("select_bloc");
	var ip = "<?php echo $_SERVER['REMOTE_ADDR']; ?>" ;	
	var PID = 'ppp' ;
	var SessionID = "<?php session_start(); echo $_SERVER['REMOTE_ADDR'].'.'.session_id().'.'.rand(1, 1000) ; ?>" ;	
	var SessionDir = '/tmpdrive/' + PID + '/' + SessionID ;
	var ResultsDir = 'pppresults/' + SessionID ;
	var ProgramDir = '/data/ppp';

	var AnnotationData;
	var promoters ; 
	
	var blues = ["#ffffff","#ecf2f9","#d9e6f2","#c6d9ec","#b3cce5","#9fbfdf","#8cb2d9","#79a6d2","#6699cc","#538cc6","#407fbf","#3973ac","#336699","#2d5986","#264c73","#204060","#1a334c","#132639","#0d1926","#060d13","#000000"] ;

	
	function newSession() {
			document.getElementById("introduction").style.display = "block";
			document.getElementById("inputform").style.display = "block";
			document.getElementById("results").style.display = "none";
			document.getElementById("OverviewTable").innerHTML = '' ;		
	}
	
	
	function Start_run(method) {
		if (document.getElementById("Sequence").value !== '' ) {
			$.post( "php/save_file.php", { filename: 'Sequence.txt', sessionid: SessionID, pid: PID, content: document.getElementById("Sequence").value } );
			if (document.getElementById("GFF").value !== '' ) {
				$.post( "php/save_file.php", { filename: 'Annotation.gff', sessionid: SessionID, pid: PID, content: document.getElementById("GFF").value } );
			}	
			if (document.getElementById("ppp_results").value !== '' ) {
				$.post( "php/save_file.php", { filename: '00.promoters.json', sessionid: SessionID, pid: PID, content: document.getElementById("ppp_results").value } );
			}	
			ShellExec = ProgramDir + '/gff2json.pl -gff '+SessionDir+'/Annotation.gff' ;
			//ShellExec = 'python3 /data/ppp/ppp_v2.py -s '+SessionDir+' -o 00.promoters.json' ;
			$.post( "php/shellexec.php", { sessionid: SessionID, pid: PID, content: ShellExec } );
			document.getElementById("MessageBox").innerHTML = ShellExec + '<br>';
			document.getElementById("MessageBox").innerHTML += SessionID + '<br>';
			document.getElementById("MessageBox").innerHTML += PID + '<br>';
			document.getElementById("MessageBox").innerHTML += document.getElementById("Sequence").value ;
			setTimeout(function (){ progress(5); }, 500); // wait 0.5 second to clean old results, than start counting down
		}	
	}	
	
	function progress(counter){
		// follow the progress until finished. Counter in seconds
		document.getElementById("myProgressBarFill").style.width = (100-(5*counter)) + '%'; 
		//$.get(ResultsDir+'/00.promoters.json', function(data) {
		$.get(ResultsDir+'/Annotation.json', function(data) {
			document.getElementById("introduction").style.display = "none";
			document.getElementById("inputform").style.display = "none";
			document.getElementById("results").style.display = "block";
			ShowResults() ; 
		})
			.fail(function() { 
				if(counter > 0){
					setTimeout(function(){
					  counter--;
					  console.log(counter);
					  progress(counter);
					}, 1000);
				} else { document.getElementById("MessageBox").innerHTML += '<br>Analysis failed due to time-limit <br>' }
			}) ;
	}

	function ShowResults() {
		// show the results Graph and Table
		document.getElementById("ResultHeader").innerHTML = "Overview of predicted promoters<br>" ;
		
		my_headers = get_fasta_headers() ;
		document.getElementById("ResultText").innerHTML += "Analyzed fasta records<br>" ;
		document.getElementById("ResultText").innerHTML += "Launch the first record direct<br>" ;
		document.getElementById("ResultText").innerHTML += "First record="+my_headers[0] ;

		window.open("http://ppp.molgenrug.nl/ppp_Results.html?sessionID="+SessionID+"&queryname="+my_headers[0]);
	
	}
	
	function get_fasta_headers() {
		var result = [] ;
		var lines = document.getElementById("Sequence").value.split("\n");
		for (i = 0; i < lines.length; ++i) {
			document.getElementById("ResultText").innerHTML += "======>"+lines[i]+"<br>" ;
			if (lines[i].match(/^>/)) { result.push(lines[i].substring(1)) ; }  // remove first char
		};
		return result ;
	}
	
	function ShowResults_old() {
		// show the results Graph and Table
		document.getElementById("ResultHeader").innerHTML = "Overview of predicted promoters<br>" ;
		$.getJSON(ResultsDir+'/00.promoters.json',function(data){
			// 1. Table header
				firstID = Object.keys(data)[0] ;
				my_experiments = Object.keys(data[firstID]['experiments']) ;
				my_experiments.sort();
				var TableBody = '<table id=Tablepromoters class=table1><thead>' ;
				TableBody += '<tr><th>ClassID</th><th>Class</th><th>Description</th>'; 
				for (i = 0; i < my_experiments.length; i++) { TableBody += '<th>'+my_experiments[i]+'</th>'; }
				TableBody += '</tr></thead><tbody>' ;
			// 2. Fill the Table body	
				var ClassUrlData ;
				$.getJSON(ClassUrlJson, function(json){ ClassUrlData = json; });   // <============================== href to do ===============================
				for(var classID in data){
					if (data[classID]['Class'] == Class) {
						
						TableBody += '<tr><td><a href='+Class+'>'+classID+'</a></td>' ; // <============================== href to do ===============================
						TableBody += '<td>'+data[classID]['Class']+'</td>' ;
						TableBody += '<td>'+data[classID]['Description']+'</td>' ;
						for (i = 0; i < my_experiments.length; i++) { 
							experiment = my_experiments[i] ;
							//experiment keys: color, adj_pvalue, hits, geneset, ExpValue
							if (data[classID]['experiments'][experiment]['rank'] >-1) {
								geneset = data[classID]['experiments'][experiment]['geneset'] ;
								popupID = classID+experiment ;
								test = popupID+","+geneset;
								TableBody += '<td  style=background-color:'+blues[data[classID]['experiments'][experiment]['color']]+';>';
								TableBody += "<div class=popup onclick=\"showGeneAnnotation('"+popupID+"','"+geneset+"')\">";
								
								TableBody += "<span class='popuptext' id='"+popupID+"'>This is the Popup GeneSet Table</span>" ;
								TableBody += data[classID]['experiments'][experiment]['color'] + ' ('+
											data[classID]['experiments'][experiment]['hits']+ ' | '+
											data[classID]['experiments'][experiment]['adj_pvalue']+
											')</div></td>' ;
							} else {TableBody += '<td></td>'
							
							}
						}	
						TableBody += '</tr>' ;
					}	
				}			
				TableBody +=  '</tbody></table>' ;
				document.getElementById("Table1_body").innerHTML = TableBody ;
				sorttable.makeSortable(document.getElementById("Tablepromoters"));	// make the table sortable
				var myTH = document.getElementsByTagName("th")[3];
				sorttable.innerSortFunction.apply(myTH, []);
				sorttable.reverse();
		}) ;
	}
	
	
	
	function Load_Example(filename, id) {
		$.get(filename, function(data) { document.getElementById(id).value = data ; }) ;
	}		
	
	function Copy2Clipboard(my_type) {
		if (my_type == 'DNA') { 
			document.getElementById("ResultText").select();
		} else { 
			document.getElementById("ResultText2").select();
		}	
		document.execCommand("copy");
	}
	
	function savetextarea(){
		var textToSave = document.getElementById("ResultText").value;
		var textToSaveAsBlob = new Blob([textToSave], {type:"text/plain"});
		var textToSaveAsURL = window.URL.createObjectURL(textToSaveAsBlob);
		var downloadLink = document.createElement("a");
		downloadLink.download = "Results.txt";
		downloadLink.innerHTML = "Download File";
		downloadLink.href = textToSaveAsURL;
		downloadLink.onclick = destroyClickedElement;
		downloadLink.style.display = "none";
		document.body.appendChild(downloadLink);
		downloadLink.click();
	}
	
	function destroyClickedElement(event) {	document.body.removeChild(event.target); }
	

	$(document).ready(function() {	
		//select_bloc.style.display = "none";
		Load_Example('examples/NC_0009643.0.AOI_01.fna','Sequence') ;
		Load_Example('examples/NC_0009643.0.AOI_01.gff','GFF') ;
		Load_Example('examples/promoters.json.txt','ppp_results') ;
		document.getElementById("results").style.display = "none";
		}) ;
		
		
</script>

</body>
</html>
