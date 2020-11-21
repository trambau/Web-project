<!DOCTYPE>
  <html>
  <head>
      <script src="http://chmille4.github.com/Scribl/js/Scribl.1.0.min.js"></script>
      <script src="http://chmille4.github.com/Scribl/js/genbankData.js"></script>
      <link rel="stylesheet" id="themeCSS" href="http://chmille4.github.com/Scribl/css/iThing.css"> 
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js"></script>
      <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
      <script src="http://static.tumblr.com/fcdode8/Giymeu17u/jquery.mousewheel.min.js"></script>
      <script src="http://static.tumblr.com/fcdode8/WVbmeu18t/jqallrangesliders-min.js"></script>

      <script>
          function draw(canvasName) {  
					
	      // Get Canvas and Create Chart
	      var canvas = document.getElementById(canvasName);  	
					
	      // Create Chart
	      chart = new Scribl(canvas, 1200);
	      chart.laneSizes = 15;
	      chart.scale.auto = false
					
	      // parse and load genbank file
	      //chart.loadGenbank(getGenbankData());
            chart.addGene(0, 500, "+");
            chart.addGene(700, 5000, "+");
	      // Draw Chart
	      redraw(100000, 200000);
	}
			   
	function redraw(min, max) {
	    // Set scale and redraw
	    chart.scale.min = min;
	    chart.scale.max = max;
	    chart.redraw();
	 }
				
      </script>
  </head>
  <body onload="draw('canvas')">
     <div id="container" style="width:100%; text-align:center">
       <!-- add scribl chart -->
       <canvas id="canvas" width="1230" height="200" style="margin-left:auto;margin-right:auto"></canvas>  
       <!-- add slider -->
       <div id="slider" style="width:1230px;margin-left:auto;margin-right:auto"></div>
    </div>

    <script>
      // initialize slider
      $("#slider").editRangeSlider({ bounds:{min: 1, max: 400000}, defaultValues:{min: 100000, max: 200000}, wheelMode: "zoom" });

      // make slider redraw Scribl chart with every change
      $("#slider").on("valuesChanging", function(e, data){
        redraw(data.values.min, data.values.max);
      });

      // handle the changing of the edit boxes as well
      $("#slider").on("valuesChanged", function(e, data){
        redraw(data.values.min, data.values.max);
      });
    </script>
    
  </body>
</html>