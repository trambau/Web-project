<!DOCTYPE HTML> 
<html lang="en">
	<head>
		<title>Scribl - viewer</title>
      <script src="./assets/Scribl.1.1.4.min.js" ></script>        

		
		
		
		<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
		<link rel="stylesheet" type="text/css" href=".assets/demos.css" />

	  	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
      
	  	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js"></script>
      <script type="text/javascript" src="js/dragscrollable.js"></script> 
      <script type="text/javascript" src="js/genbankData.js"></script> 
	  	<script type="text/javascript" src="prototype.js"></script> 
      
		<style>
		   #scribl-zoom-slider {
		      width: 4px;
		   }
		</style>
		
		<script type="text/javascript">
		  
		  function go() {
            var zoomCanvas = document.getElementById('canvas');
            origZoomChart = new Scribl(zoomCanvas, 900);
            origZoomChart.loadGenbank(getGenbankData());
            origZoomChart.scrollable = true;
            origZoomChart.scrollValues = [200000, 250000];
            origZoomChart.draw();
		  }

		</script>

	</head>  
	
	<body onLoad='go()'>  	   				
		<div id="description"><h2>Simple Viewer</h2>
			<div>(Best viewed in Chrome/Safari)</div>
		<br/><form>
			<div style="margin-left: auto; margin-right: auto; width:50%">
				<div>Viewing 360kb region of Ecoli Genome</div><br/>
				<div style="font-size:.8em">Zoom with vertical slider <br/> Drag to scroll left and right</div>				
				<br/>
			</div>
			
		</div>
		<div id="container">
		            <canvas id="canvas" width="940px" height="400px"  style="margin-left:auto; margin-right:auto"></canvas>  
		</div>		
	</body>
	
</html>