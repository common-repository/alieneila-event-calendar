<?php
function al_event_setting() {
?>
	<script type="text/javascript" language="javascript">
			function togglePreview( whichLayer )
			{
				var elem, vis;
			  	if( document.getElementById ) // this is the way the standards work
			    	elem = document.getElementById( whichLayer );
			  	else if( document.all ) // this is the way old msie versions work
			      	elem = document.all[whichLayer];
			  	else if( document.layers ) // this is the way nn4 works
			    	elem = document.layers[whichLayer];
			  	vis = elem.style;
			  	// if the style.display value is blank we try to figure it out here
			  	if(vis.display==''&&elem.offsetWidth!=undefined&&elem.offsetHeight!=undefined)
			    	vis.display = (elem.offsetWidth!=0&&elem.offsetHeight!=0)?'block':'none';
			  	vis.display = (vis.display==''||vis.display=='block')?'none':'block';
			}

	   var http_request = false;
	   function makePOSTRequest(url, parameters) {
	      http_request = false;
	      if (window.XMLHttpRequest) { // Mozilla, Safari,...
	         http_request = new XMLHttpRequest();
	         if (http_request.overrideMimeType) {
	         	// set type accordingly to anticipated content type
	            //http_request.overrideMimeType('text/xml');
	            http_request.overrideMimeType('text/html');
	         }
	      } else if (window.ActiveXObject) { // IE
	         try {
	            http_request = new ActiveXObject("Msxml2.XMLHTTP");
	         } catch (e) {
	            try {
	               http_request = new ActiveXObject("Microsoft.XMLHTTP");
	            } catch (e) {}
	         }
	      }
	      if (!http_request) {
	         alert('Cannot create XMLHTTP instance');
	         return false;
	      }
	      
	      http_request.onreadystatechange = alertContents;
	      http_request.open('POST', url, true);
	      http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	      http_request.setRequestHeader("Content-length", parameters.length);
	      http_request.setRequestHeader("Connection", "close");
	      http_request.send(parameters);
	   }
	
	   function alertContents() {
	      if (http_request.readyState == 4) {
	         if (http_request.status == 200) {
	            result = http_request.responseText.split("|");
	            document.getElementById('hiddenspan').innerHTML = result[3];

		   var linkList = document.getElementsByName( result[0] );
		   for (i = 0; i < linkList.length; i++) {
		      linkList[i].style[result[1]] ='url('+result[2]+')';
		   }

//		    document.getElementById(result[0]).style[result[1]]='url('+result[2]+')';
	         } else {
	            alert('There was a problem with the request.');
	         }
	      }
	   }
	   
	   function get(obj, field, value) {
		if (value != '') {
		      var poststr = "target="+obj+
		                    "&field="+field+
	        	            "&value="+value;

		      makePOSTRequest('<?php echo AL_CAL_URL; ?>/updatecss.php', poststr);
		}
	   }


	function changecss(obj,attr,value) {
	   var linkList = document.getElementsByName( obj );
	   for (i = 0; i < linkList.length; i++) {
	      linkList[i].style[attr] =''+value+'';
	   }
	}

	</script>

<span id="hiddenspan" style="display:none"></span>

<style>
#miniWindow {
	display:none;
	position:fixed;
	bottom: 15px;
	right: 0;
	width: auto;
	height: auto;
	background-color: transparent;
}
#fcWindow {
	display:none;
	position:fixed;
	bottom: 0;
	right: 0;
	width: auto;
	height: auto;
	background-color: transparent;
}
#listWindow {
	display:none;
	position:fixed;
	bottom: 45px;
	right: 0;
	width: auto;
	height: auto;
	background-color: transparent;
}
#eventWindow {
	display:none;
	position:fixed;
	bottom: 30px;
	right: 0;
	width: auto;
	height: auto;
	background-color: transparent;
}
#popupFC {
	display:none;
	position:fixed;
	bottom: 60px;
	right: 0;
	width: auto;
	height: auto;
	z-index:100;
}
#cssSave {
	display:none;
	background-color: white;
	color: black;
	position:absolute;
	border: 3px solid black;
	padding:10px;
	top: 0;
	right: 0;
	width: 80%;
	height: auto;
	z-index: 5000;
}
</style>

<div id="miniWindow">
<div style="display:block; background-color:black; color: white"><a href="javascript:togglePreview('cal')">Show/Hide Calendar</a></div>
	<style>

<?php

	if(get_option('alien_event_new_layout') == "picker") {
		$newstyles = get_option('alien_event_new_styles');
		$cssSave = '';
		foreach ($newstyles AS $k => $v) {
			echo '#'.str_replace("'", "", stripslashes($k)).' {';
			$cssSave .= '<p>#'.str_replace("'", "", stripslashes($k)).' {<br />';

			foreach($v AS $s => $set) {
				$s = str_replace("'", "", stripslashes($s));
				$set = str_replace("'", "", stripslashes($set));
				if ($s == "background-image") {
					if (!$set || $set == "none") {
						echo $s.':none;';
						$cssSave .= $s.':none;<br />';
					}
					else {
						echo $s.':url('.$set.');';
						$cssSave .= $s.':url('.$set.');<br />';
					}
				}
				else {
					echo $s.':'.$set.';';
					$cssSave .= $s.':'.$set.';<br />';
				}
			}
		
			echo '} ';
			$cssSave .= '} </p>';
		}
	}
	else {
		echo get_option('alien_cal_style');
	}
?>

	</style>
<?php
$theminical = "";
	$theminical .= '<div id="cal">';
		$theminical .= '<div id="calTable">';
			$theminical .= '<div id="calTH">';
				$theminical .= '<a href="#" id="calPrev"><</a>';
				$theminical .= '<span id="calMonth">February</span> <span id="calYear">2010</span>';
				$theminical .= '<a href="#" id="calNext">></a>';
			$theminical .= '</div>';

			$theminical .= '<div style="clear: both"></div>';
			$theminical .= '<div id="tableTR" name="tableTR">';
				$theminical .= '<div id="calDW" name="calDW">SU</div>';
				$theminical .= '<div id="calDW" name="calDW">MO</div>';
				$theminical .= '<div id="calDW" name="calDW">TU</div>';
				$theminical .= '<div id="calDW" name="calDW">WE</div>';
				$theminical .= '<div id="calDW" name="calDW">TH</div>';
				$theminical .= '<div id="calDW" name="calDW">FR</div>';
				$theminical .= '<div id="calDW" name="calDW">SA</div>';
			$theminical .= '</div>';
			$theminical .= '<div style="clear: both"></div>';

			$active = array();
			$active[] = strtotime("18 June 2010");
			$active[] = strtotime("3 June 2010");
			$active[] = strtotime("10 June 2010");
			$active[] = strtotime("14 June 2010");
			$active[] = strtotime("15 June 2010");
			$active[] = strtotime("21 June 2010");
			$active[] = strtotime("23 June 2010");

			$date = strtotime("18 June 2010");
			$day = date('d', $date) ;
			$month = date('m', $date) ;
			$year = date('Y', $date) ;

			$first_day = mktime(0,0,0,$month, 1, $year) ;
			$title = date('F', $first_day) ; 
			$day_of_week = date('D', $first_day) ;
			$utitle = strtoupper($title);
			$days_in_month = date('t', $date) ; 
			$day_count = 1;

			$theminical .= '<div id="tableTR" name="tableTR">';
			while ( $blank > 0 ) {
				$theminical .= '<div id="calBlank" name="calBlank">&nbsp;</div>';
				$blank = $blank-1;
				$day_count++;
			} 

			$day_num = 1;

			$newmonth = 1;
			while ( $day_num <= $days_in_month ) {
				$mot = date('F', strtotime('18 June 2010'));
				$thisdate = strtotime("".$day_num." ".$title." ".$year."");
				if (in_array($thisdate, $active)) {
					if ($day_num == $day && $title == $mot) {
						$theminical .= '<div id="calTActive" name="calTActive"><a href="#" name="calTActive a">'.$day_num.'</a></div>';
					}
					else {
						$theminical .= '<div id="calActive" name="calActive"><a href="#" name="calActive a">'.$day_num.'</a></div>';
					}
				}
				else {
					if ($day_num == $day && $title == $mot) {
						$theminical .= '<div id="calToday" name="calToday">'.$day_num.'</div>';
					}
					else {
						$theminical .= '<div id="calDay" name="calDay">'.$day_num.'</div>';
					}
				}
				$day_num++;
				$day_count++;

				if ($day_count > 7) {
					$theminical .= '</div>';
					$theminical .= '<div style="clear: both"></div>';
					$theminical .= '<div id="tableTR" name=tableTR>';
					$day_count = 1;
				}
			} 

			while ( $day_count >1 && $day_count <=7 ) {
				$theminical .= '<div id="calBlank" name="calBlank">&nbsp;</div>';
				$day_count++;
			}

			$theminical .= '</div>';
			$theminical .= '<div style="clear: both"></div>';
			$theminical .= '</div>'; 
			$theminical .= '</div>';

echo $theminical;

?>

</div>
<div id="cssSave">
<div style="display:block; background-color:white; color: black"><a href="javascript:togglePreview('cssSave')">close</a></div>
<p>Once you have saved the style using the form method, you can copy this css Stylesheet and switch back to CSS and either paste it in the textarea or into your theme CSS file for further editing.</p>
<?php echo $cssSave; ?>
</div>

<div id="eventWindow">
<div style="display:block; background-color:black; color: white"><a href="javascript:togglePreview('eventWrap')">Show/Hide Event Details</a></div>
<div id="eventWrap">
	<div id="eventMedia">
	</div>
	<div id="eventHeader" name="eventHeader">
		<h1><a name="eventHeader a">Event Title</a></h1>
	</div>
	<div id="eventBlock" name="eventBlock">
		Category
		<div id="eventOptions">
			<span name="eventOptions span"><a href="#" name="eventOptions span a">Comments</a></span>
			<span name="eventOptions span"><a href="#" name="eventOptions span a">More</a></span>
			<span name="eventOptions span"><a href="#" name="eventOptions span a:hover">Hover</a></span>
		</div>
		<p>Online Event</p>
		<p><span id="myEventTitle" name="myEventTitle">Website: </span><a href="http://area51.alieneila.net">http://area51.alieneila.net</a></p>
		<p><span id="myEventTitle" name="myEventTitle">Location:</span> My House</p>
		<br><span id="myEventTitle" name="myEventTitle">Duration:</span> All Day
		</p>
		<p><span id="myEventTitle" name="myEventTitle">Repeats:</span> once until 18 June 2010</p>
		<p>This is where the description of the event is displayed.</p>
		<p><span id="myEventTitle" name="myEventTitle">RSVPs:</span> 0</p>
		<p><span id="myEventTitle" name="myEventTitle">Price:</span> Free</p>
		<p><span id="myEventTitle" name="myEventTitle">Address:</span> 15656 NE Highway 314 Silver Springs FL 34488 United States</p>
		<p><span id="myEventTitle" name="myEventTitle">Contact:</span> Joshua Segatto joshua.segatto@gmail.com (352) 625-3938</p>
	</div>
</div>
</div>


<div id="listWindow">
<div style="display:block; background-color:black; color: white"><a href="javascript:togglePreview('listWrap')">Show/Hide Event List</a></div>
<div id="listWrap">
	<p id="fullCalLink" name="fullCalLink"><a href="/?pagename=cal-events&fc=1" name="fullCalLink a">Full Calendar</a></p>

	<div id="eventCatList" name="eventCatList">
		<form method="get" action="">
		<input type="hidden" name="pagename" value="cal-events">
		<input type="hidden" name="month" value="">
		<select name="evcatid">
		<option>Select Category</option>
		<option value="11">Demo Category 2</option>
		<option value="12">- Sub-Demo 1</option>
		<option value="13">-- Grand-Demo 1</option>
		<option value="5">Events</option>
		<option value="9">- Demo Events 1</option>
		<option value="10">- Demo Events 2</option>
		</select>
		</form>

		<div style="clear: both"></div>
	</div>
	<div id="eventsList" name="eventsList">
		<div id="eventDateHead" name="eventDateHead">18 June 2010</div>
		<div id="eventBlock" name="eventBlock">
			<div id="eventOptions" name="eventOptions">
				<span name="eventOptions span"><a href="#" name="eventOptions span a">More</a></span>
			</div>
			<div id="eventDetails" name="eventDetails">
				<span id="myEventTitle" name="myEventTitle">Working on a new Layout Method</span> 
				<span id="myEventLocationTitle" name="myEventLocationTitle">Location:</span> 
				<span id="myEventLocation" name="myEventLocation">http://area51.alieneila.net</span> 
				<span id="myEventTimeTitle" name="myEventTimeTitle">Time:</span> 
				<span id="myEventTime" name="myEventTime">All Day</span> 
			</div>
			<div style="clear:both"></div>
		</div>
		<div id="eventDateHead" name="eventDateHead">19 June 2010</div>
		<div id="eventBlock" name="eventBlock">
			<div id="eventOptions" name="eventOptions">
				<span name="eventOptions span"><a href="#" name="eventOptions span a">More</a></span>
			</div>
			<div id="eventDetails" name="eventDetails">
				<span id="myEventTitle" name="myEventTitle">Working on a new Layout Method</span> 
				<span id="myEventLocationTitle" name="myEventLocationTitle">Location:</span> 
				<span id="myEventLocation" name="myEventLocation">http://area51.alieneila.net</span> 
				<span id="myEventTimeTitle" name="myEventTimeTitle">Time:</span> 
				<span id="myEventTime" name="myEventTime">All Day</span> 
			</div>
			<div style="clear:both"></div>
		</div>
		<div id="eventDateHead" name="eventDateHead">20 June 2010</div>
		<div id="eventBlock" name="eventBlock">
			<div id="eventOptions" name="eventOptions">
				<span name="eventOptions span"><a href="#" name="eventOptions span a">More</a></span>
			</div>
			<div id="eventDetails" name="eventDetails">
				<span id="myEventTitle" name="myEventTitle">Working on a new Layout Method</span> 
				<span id="myEventLocationTitle" name="myEventLocationTitle">Location:</span> 
				<span id="myEventLocation" name="myEventLocation">http://area51.alieneila.net</span> 
				<span id="myEventTimeTitle" name="myEventTimeTitle">Time:</span> 
				<span id="myEventTime" name="myEventTime">All Day</span> 
			</div>
			<div style="clear:both"></div>
		</div>
	</div>
</div>
</div>

<div id="popupFC" name="popupFC">
This is how the popup will look when floating over the event names<br />
This is how the popup will look when floating over the event names<br />
This is how the popup will look when floating over the event names<br />
This is how the popup will look when floating over the event names<br />
<a href="javascript:togglePreview('popupFC')">Close</a>
</div>

<div id="fcWindow">
<div style="display:block; background-color:black; color: white"><a href="javascript:togglePreview('calFC')">Show/Hide Calendar</a></div>
<?php
$theminical = "";

	$theminical .= '<div id="calFC">';
		$theminical .= '<div id="calTableFC">';
			$theminical .= '<div id="calTHFC">';
				$theminical .= '<a href="#" id="calPrevFC"><</a>';
				$theminical .= '<span id="calMonthFC">February</span> <span id="calYearFC">2010</span>';
				$theminical .= '<a href="#" id="calNextFC">></a>';
			$theminical .= '</div>';

			$theminical .= '<div style="clear: both"></div>';
			$theminical .= '<div id="tableTRFC" name="tableTRFC">';
				$theminical .= '<div id="calDWFC" name="calDWFC">SU</div>';
				$theminical .= '<div id="calDWFC" name="calDWFC">MO</div>';
				$theminical .= '<div id="calDWFC" name="calDWFC">TU</div>';
				$theminical .= '<div id="calDWFC" name="calDWFC">WE</div>';
				$theminical .= '<div id="calDWFC" name="calDWFC">TH</div>';
				$theminical .= '<div id="calDWFC" name="calDWFC">FR</div>';
				$theminical .= '<div id="calDWFC" name="calDWFC">SA</div>';
			$theminical .= '</div>';
			$theminical .= '<div style="clear: both"></div>';

			$active = array();
			$active[] = strtotime("18 June 2010");
			$active[] = strtotime("3 June 2010");
			$active[] = strtotime("10 June 2010");
			$active[] = strtotime("14 June 2010");
			$active[] = strtotime("15 June 2010");
			$active[] = strtotime("21 June 2010");
			$active[] = strtotime("23 June 2010");

			$date = strtotime("18 June 2010");
			$day = date('d', $date) ;
			$month = date('m', $date) ;
			$year = date('Y', $date) ;

			$first_day = mktime(0,0,0,$month, 1, $year) ;
			$title = date('F', $first_day) ; 
			$day_of_week = date('D', $first_day) ;
			$utitle = strtoupper($title);
			$days_in_month = date('t', $date) ; 
			$day_count = 1;

			$theminical .= '<div id="tableTRFC" name="tableTRFC">';
			while ( $blank > 0 ) {
				$theminical .= '<div id="calBlankFC" name="calBlankFC">&nbsp;</div>';
				$blank = $blank-1;
				$day_count++;
			} 

			$day_num = 1;

			$newmonth = 1;
			while ( $day_num <= $days_in_month ) {
				$mot = date('F', strtotime('18 June 2010'));
				$thisdate = strtotime("".$day_num." ".$title." ".$year."");
				if (in_array($thisdate, $active)) {
					if ($day_num == $day && $title == $mot) {
						$theminical .= '<div id="calTActiveFC" name="calTActiveFC">';
						$theminical .= '<a href="#" name="calTActiveFC a">'.$day_num.'</a><br />';
						$theminical .= '<a href="javascript:togglePreview(\'popupFC\')" name="calTActiveFC a">Show Float</a><br />';
						$theminical .= '<a href="javascript:togglePreview(\'popupFC\')" name="calTActiveFC a">Show Float</a><br />';
						$theminical .= '</div>';
					}
					else {
						$theminical .= '<div id="calActiveFC" name="calTActiveFC">';
						$theminical .= '<a href="#" name="calActiveFC a">'.$day_num.'</a><br />';
						$theminical .= '<a href="javascript:togglePreview(\'popupFC\')" name="calActiveFC a">Show Float</a><br />';
						$theminical .= '<a href="javascript:togglePreview(\'popupFC\')" name="calActiveFC a">Show Float</a><br />';
						$theminical .= '</div>';
					}
				}
				else {
					if ($day_num == $day && $title == $mot) {
						$theminical .= '<div id="calTodayFC" name="calTodayFC">'.$day_num.'</div>';
					}
					else {
						$theminical .= '<div id="calDayFC" name="calDayFC">'.$day_num.'</div>';
					}
				}
				$day_num++;
				$day_count++;

				if ($day_count > 7) {
					$theminical .= '</div>';
					$theminical .= '<div style="clear: both"></div>';
					$theminical .= '<div id="tableTRFC" name=tableTRFC>';
					$day_count = 1;
				}
			} 

			while ( $day_count >1 && $day_count <=7 ) {
				$theminical .= '<div id="calBlankFC" name="calBlankFC">&nbsp;</div>';
				$day_count++;
			}

			$theminical .= '</div>';
			$theminical .= '<div style="clear: both"></div>';
			$theminical .= '</div>'; 
			$theminical .= '</div>';

echo $theminical;

?>

</div>

		<script language="javascript" type="text/javascript">
			function toggleLayer( whichLayer )
			{
				var hide1;
				var hide2;

				if (whichLayer == 'Main') {
				  	if( document.getElementById ) {
		    				hide1 = document.getElementById('Display');
		    				hide2 = document.getElementById('Appearance');
					}
				  	else if( document.all ) {
					      	hide1 = document.all['Display'];
					      	hide2 = document.all['Appearance'];
					}
				  	else if( document.layers ) {
					    	hide1 = document.layers['Display'];
					    	hide2 = document.layers['Appearance'];
					}
				}
				else if (whichLayer == 'Display') {
				  	if( document.getElementById ) {
		    				hide1 = document.getElementById('Main');
		    				hide2 = document.getElementById('Appearance');
					}
				  	else if( document.all ) {
					      	hide1 = document.all['Main'];
					      	hide2 = document.all['Appearance'];
					}
				  	else if( document.layers ) {
					    	hide1 = document.layers['Main'];
					    	hide2 = document.layers['Appearance'];
					}
				}
				else if (whichLayer == 'Appearance') {
				  	if( document.getElementById ) {
		    				hide1 = document.getElementById('Display');
		    				hide2 = document.getElementById('Main');
					}
				  	else if( document.all ) {
					      	hide1 = document.all['Display'];
					      	hide2 = document.all['Main'];
					}
				  	else if( document.layers ) {
					    	hide1 = document.layers['Display'];
					    	hide2 = document.layers['Main'];
					}
				}
				var elem, vis;
			  	if( document.getElementById ) // this is the way the standards work
		    			elem = document.getElementById( whichLayer );
			  	else if( document.all ) // this is the way old msie versions work
				      	elem = document.all[whichLayer];
			  	else if( document.layers ) // this is the way nn4 works
				    	elem = document.layers[whichLayer];

			  	vis = elem.style;
			  	vis.display = 'block';
			  	vis = hide1.style;
			    	vis.display = 'none';
			  	vis = hide2.style;
			    	vis.display = 'none';
			}
			function togglePick( whichLayer )
			{
				var hide1;

				if (whichLayer == 'CSStable') {
				  	if( document.getElementById ) {
		    				hide1 = document.getElementById('Picktable');
					}
				  	else if( document.all ) {
					      	hide1 = document.all['Picktable'];
					}
				  	else if( document.layers ) {
					    	hide1 = document.layers['Picktable'];
					}
				}
				else if (whichLayer == 'Picktable') {
				  	if( document.getElementById ) {
		    				hide1 = document.getElementById('CSStable');
					}
				  	else if( document.all ) {
					      	hide1 = document.all['CSStable'];
					}
				  	else if( document.layers ) {
					    	hide1 = document.layers['CSStable'];
					}
				}
				var elem, vis;
			  	if( document.getElementById ) // this is the way the standards work
		    			elem = document.getElementById( whichLayer );
			  	else if( document.all ) // this is the way old msie versions work
				      	elem = document.all[whichLayer];
			  	else if( document.layers ) // this is the way nn4 works
				    	elem = document.layers[whichLayer];

			  	vis = elem.style;
			  	vis.display = 'block';
			  	vis = hide1.style;
			    	vis.display = 'none';
			}

			function toggleStyles( whichLayer, hidee1, hidee2, hidee3 )
			{
				var elem, vis;

			  	if( document.getElementById ) {
		    			elem = document.getElementById( whichLayer );
	    				hide1 = document.getElementById( hidee1 );
	    				hide2 = document.getElementById( hidee2 );
	    				hide3 = document.getElementById( hidee3 );
				}
			  	else if( document.all ) {
				      	elem = document.all[whichLayer];
				      	hide1 = document.all[hidee1];
				      	hide2 = document.all[hidee2];
				      	hide3 = document.all[hidee3];
				}
			  	else if( document.layers ) {
				    	elem = document.layers[whichLayer];
				    	hide1 = document.layers[hidee1];
				    	hide2 = document.layers[hidee2];
				    	hide3 = document.layers[hidee3];
				}

			  	vis = elem.style;
			  	vis.display = 'block';
			  	vis = hide1.style;
			    	vis.display = 'none';
			  	vis = hide2.style;
			    	vis.display = 'none';
			  	vis = hide3.style;
			    	vis.display = 'none';
			}

		</script>


	<div class="icon32" id="icon-options-general"><br /></div>
	<div class="wrap">
	<h2>AlieneilA Calendar Settings</h2>
<?php
	al_event_menu();
?>
<a href="javascript:toggleLayer('Main');"><img src="<?php echo AL_CAL_URL; ?>/icons/main.png" alt="Main" title="Main" width="80" height="24" border="0"></a><a href="javascript:toggleLayer('Display');"><img src="<?php echo AL_CAL_URL; ?>/icons/display-options.png" alt="Display Options" title="Display Options" width="112" height="24" border="0"></a><a href="javascript:toggleLayer('Appearance');"><img src="<?php echo AL_CAL_URL; ?>/icons/appearance.png" alt="Appearance" title="Appearance" width="80" height="24" border="0"></a>
<form method="post" action="options.php">
    <?php settings_fields( 'alien-cal-group' ); ?>
<div id="Main" style="display:block">
    <table class="widefat page">
	<thead>
        <tr valign="top">
        <th colspan="2">Word Press Url and Plugin Page ID</th>
        </tr>
	</thead>
        <tr valign="top">
        <td colspan="2">
		<p>
		<input type="text" name="alien_cal_wpurl" value="<?php echo get_option('alien_cal_wpurl'); ?>" /><br />
		<small>If you are having problems with the links pointing to the right page, put the proper url to your home page here without the ending slash.</small>
		</p>
	</td>
        </tr>
        <tr valign="top">
        <td colspan="2">
		<p>
		<input type="text" name="alien_cal_page_id" value="<?php echo get_option('alien_cal_page_id'); ?>" /> Update on Upgrade? <input type="radio" name="alien_cal_page_update" value="1" <?php if (get_option('alien_cal_page_update') == 1) { echo 'checked="checked"'; } ?>> Yes <input type="radio" name="alien_cal_page_update" value="0" <?php if (get_option('alien_cal_page_update') != 1) { echo 'checked="checked"'; } ?>> No<br />
		<small>If you are not using the page that the plugin creates, put the page id number to the page you created. You can also set it to not automatically update this page number when the plugin has updates.</small>
		</p>
	</td>
        </tr>
</table>
    <table class="widefat page">
	<thead>
        <tr valign="top">
        <th colspan="2">Role Access<br />
	<small>Allows other people to post events through the dashboard.</small></th>
        </tr>
	</thead>
        <tr valign="top">
        <td colspan="2">
		<p>
		<?php $curRoles = get_option('alien_event_roles'); ?>
		<?php if (!$curRoles) { $curRoles = array('administrator'); } ?>
		<input type="hidden" name="alien_event_roles[]" value="administrator">
		<input type="checkbox" name="alien_event_roles[]" value="editor" <?php if (in_array('editor', $curRoles)) { echo 'checked="checked"'; } ?>> Editor 
		<input type="checkbox" name="alien_event_roles[]" value="author" <?php if (in_array('author', $curRoles)) { echo 'checked="checked"'; } ?>> Author 
		<input type="checkbox" name="alien_event_roles[]" value="contributor" <?php if (in_array('contributor', $curRoles)) { echo 'checked="checked"'; } ?>> Contributor <br />
		<small>Administrators are given access automatically.</small>
		</p>
	</td>
        </tr>
    </table>
    <table class="widefat page">
	<thead>
        <tr valign="top">
        <th colspan="2">Mailing Options for RSVP and Notify friends. This is currently experimental.</th>
        </tr>
	</thead>
        <tr valign="top">
        <td colspan="2">
		<p>
		Send <input type="text" name="alien_event_email_per" size="5" value="<?php echo get_option('alien_event_email_per'); ?>" /> 
		emails per <input type="text" name="alien_event_email_time" size="5" value="<?php echo get_option('alien_event_email_time'); ?>" /> seconds. (3600 seconds is an hour)
		</p>
	</td>
        </tr>
        <tr valign="top">
        <td colspan="2">
		<p>
		Include Excerpt in Mail: <input type="checkbox" name="alien_event_email_me" <?php if (get_option('alien_event_email_me')) { echo 'checked="checked"'; } ?> /> <br />
		Cut to <input type="text" name="alien_event_email_mel" size="5" value="<?php echo get_option('alien_event_email_mel'); ?>" /> characters. (1 for full text).
		</p>
	</td>
        </tr>
</table>
<table class="widefat page">
	<thead>
        <tr valign="top">
        <th colspan="2">The events use the post categories for sorting. Check which categories will be displayed on the events page.</th>
        </tr>
	</thead>
        <tr valign="top">
        <th scope="row">Categories:</th>
        <td>

	 <?php 
	$categories=  get_categories('parent=0&hide_empty=0');

	$cats = get_option( "alien_event_cats" );
	foreach ($categories as $cat) {
		?>
			<input type="checkbox" name="alien_event_cats[]" value="<?php echo $cat->term_id; ?>" <?php if ($cats && in_array($cat->term_id, $cats)) { echo 'checked="checked"'; } ?> /> <?php echo $cat->cat_name; ?> 
		<?php
	}
	?>
	<p>Children and grandchidren of an activated category will automatically be displayed. (Though not beyond grandchildren)</p>

	</td>
        </tr>
</table>
    <table class="widefat page">
	<thead>
        <tr valign="top">
        <th colspan="2">Internationalization Settings</th>
        </tr>
	</thead>
        <tr valign="top">
	<th scope="row">US/International?</th>
	<td><input type="radio" name="alien_event_international" value="us" <?php if (get_option('alien_event_international') == "us") { echo 'checked="checked"'; } ?> /> U.S. <input type="radio" name="alien_event_international" value="international" <?php if (get_option('alien_event_international') == "international") { echo 'checked="checked"'; } ?> />International</td>
	</tr>
	<tr valign="top">
	<th scope="row">Money Sign:</th>
	<td>
	<select name="alien_cal_money">
	<?php if (get_option('alien_cal_money')) { echo '<option value="'.get_option('alien_cal_money').'">'.get_option('alien_cal_money').'</option>'; } ?>
	<option value="&#36;">&#36;</option>
	<option value="&#163;">&#163;</option>
	<option value="&#165;">&#165;</option>
	<option value="&#8364;">&#8364;</option>

	</select>
	</td>
	</tr>
	<tr valign="top">
	<th scope="row">Auto Format Phone#?</th>
	<td><input type="radio" name="alien_event_autophone" value="yes" <?php if (get_option('alien_event_autophone') == "yes") { echo 'checked="checked"'; } ?> /> Yes <input type="radio" name="alien_event_autophone" value="no" <?php if (get_option('alien_event_autophone') == "no") { echo 'checked="checked"'; } ?> /> No</td>
	</tr>
	<tr valign="top">
	<th scope="row">Provinces:</th>
	<td>(Use commas to seperate provinces)<br /><textarea name="alien_event_provinces"><?php echo get_option('alien_event_provinces'); ?></textarea></td>
	</tr>
	<tr valign="top">
	<th scope="row">Countries:</th>
	<td>(Use commas to seperate countries)<br /><textarea name="alien_event_countries"><?php echo get_option('alien_event_countries'); ?></textarea></td>
        </tr>
</table>
</div>
<div id="Appearance" style="display:none">

	<table class="widefat page">
	        <tr valign="top">
	        <th scope="row">CSS or Live Preview Layout Form</th>
	        <td>
			CSS <input type="radio" onFocus="javascript:togglePick('CSStable');" name="alien_event_new_layout" value="css" <?php if (get_option('alien_event_new_layout') == "css") { echo 'checked="checked"';} ?>>
			Layout Form <input type="radio" onFocus="javascript:togglePick('Picktable');" name="alien_event_new_layout" value="picker" <?php if (get_option('alien_event_new_layout') == "picker") { echo 'checked="checked"';} ?>> ( <a href="javascript:togglePreview('cssSave')">View Saved as CSS</a> )
		</td>
	        </tr>
	    </table>

	<?php 
	if(get_option('alien_event_new_layout') == "picker") {
		?><div id="Picktable" style="display:block"><?php
	}
	else {
		?><div id="Picktable" style="display:none"><?php
	}
	$newstyles = get_option('alien_event_new_styles');
	?>
	<table class="widefat page">
		<thead>
	        <tr valign="top">
	        <th colspan="2">
			Choose colors and layout of widget calendar and full calendar.

			<input type="radio" onFocus="javascript:toggleStyles('calwidform','listwidform','fullcalform','eventlistform');" name="switch" value="0" checked="checked"> Calendar Widget 
			<input type="radio" onFocus="javascript:toggleStyles('listwidform','fullcalform','eventlistform','calwidform');" name="switch" value="0"> Event List Widget 
			<input type="radio" onFocus="javascript:toggleStyles('fullcalform','eventlistform','calwidform','listwidform');" name="switch" value="0"> Full Calendar 
			<input type="radio" onFocus="javascript:toggleStyles('eventlistform','calwidform','listwidform','fullcalform');" name="switch" value="0"> Event List Page 

		</th>
	        </tr>
		</thead>
	</table>
<!-- Start listwidform -->
<div id="listwidform" style="display:none">
</div>
<!-- End listwidform -->

<!-- Start eventlistform -->
<div id="eventlistform" style="display:none">
	<table class="widefat page">
		<thead>
			<tr valign="top">
				<th>
					<a href="javascript:togglePreview('listWindow')">Show/Hide List Preview</a> | 
					<a href="javascript:togglePreview('eventWindow')">Show/Hide Details Preview</a>
				</th>
			</tr>
		</thead>
	</table>
	<table class="widefat page">
	        <tr valign="top">
	        <th scope="row">Full Calendar Link</th>
		<td>
			Display: <select name="alien_event_new_styles[fullCalLink][display]" onChange="changecss('fullCalLink','display',''+this.value+'')">
					<option value="block" <?php if($newstyles['fullCalLink']['display'] == "block") { ?>selected="selected"<?php } ?>>Yes</option>
					<option value="none" <?php if($newstyles['fullCalLink']['display'] == "none") { ?>selected="selected"<?php } ?>>No</option>
				</select><br />
			<input type="hidden" name="alien_event_new_styles[fullCalLink a][text-decoration]" value="none">
			BG Image: <input type="text" name="alien_event_new_styles[fullCalLink][background-image]" value="<?php echo $newstyles['fullCalLink']['background-image']; ?>" onChange="javascript:get('fullCalLink','backgroundImage',''+this.value+'');"><br>
			BG Color: <input type="text" name="alien_event_new_styles[fullCalLink][background-color]" value="<?php echo $newstyles['fullCalLink']['background-color']; ?>" onChange="changecss('fullCalLink','backgroundColor',''+this.value+'')"><br>
			Link Color: <input type="text" name="alien_event_new_styles[fullCalLink a][color]" value="<?php echo $newstyles['fullCalLink a']['color']; ?>" onChange="changecss('fullCalLink a','color',''+this.value+'')"><br>
		</td>
	        </tr>
	    </table>
		<table class="widefat page">
			<thead>
		        <tr valign="top">
		        <th colspan="2">
				Category List
			</th>
		        </tr>
			</thead>
			<tr valign="top">
			<th scope="row">Category Wrapper</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[eventCatList][display]" value="block">
				BG Image: <input type="text" name="alien_event_new_styles[eventCatList][background-image]" value="<?php echo $newstyles['eventCatList']['background-image']; ?>" onChange="javascript:get('eventCatList','backgroundImage',''+this.value+'');"><br />
				BG Color: <input type="text" name="alien_event_new_styles[eventCatList][background-color]" value="<?php echo $newstyles['eventCatList']['background-color']; ?>" onChange="changecss('eventCatList','backgroundColor',''+this.value+'')"><br />
				Width: <input type="text" name="alien_event_new_styles[eventCatList][width]" value="<?php echo $newstyles['eventCatList']['width']; ?>" onChange="changecss('eventCatList','width',''+this.value+'')"><br />
				Left Margin: <input type="text" name="alien_event_new_styles[eventCatList][margin-left]" value="<?php echo $newstyles['eventCatList']['margin-left']; ?>" onChange="changecss('eventCatList','marginLeft',''+this.value+'')"><br />
				Right Margin: <input type="text" name="alien_event_new_styles[eventCatList][margin-right]" value="<?php echo $newstyles['eventCatList']['margin-right']; ?>" onChange="changecss('eventCatList','marginRight',''+this.value+'')"><br />
				Text Align: <select name="alien_event_new_styles[eventCatList][text-align]" onChange="changecss('eventCatList','textAlign',''+this.value+'')">
					<option value="left" <?php if($newstyles['eventCatList']['text-align'] == "left") { ?>selected="selected"<?php } ?>>Left</option>
					<option value="center" <?php if($newstyles['eventCatList']['text-align'] == "center") { ?>selected="selected"<?php } ?>>Center</option>
					<option value="right" <?php if($newstyles['eventCatList']['text-align'] == "right") { ?>selected="selected"<?php } ?>>Right</option>
				</select><br />
			Border: <select name="alien_event_new_styles[eventCatList][border]" onChange="changecss('eventCatList','border',''+this.value+'')">
					<?php if ($newstyles['eventCatList']['border']) { ?> <option value="<?php echo $newstyles['eventCatList']['border']; ?>"><?php echo $newstyles['eventCatList']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[eventCatList][border-style]" onChange="changecss('eventCatList','borderStyle',''+this.value+'')">
					<?php if ($newstyles['eventCatList']['border-style']) { ?> <option value="<?php echo $newstyles['eventCatList']['border-style']; ?>"><?php echo $newstyles['eventCatList']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[eventCatList][border-color]" value="<?php echo $newstyles['eventCatList']['border-color']; ?>" onChange="changecss('eventCatList','borderColor',''+this.value+'')">
			</td>
			</tr>

<?php if (get_option('alien_event_cat_disp') == "list") { ?>
<!--			<tr valign="top">
			<th scope="row">Main Cat List</th>
			<td>
				Display: <select name="alien_event_new_styles[catMainUL][display]" onChange="changecss('catMainUL','display',''+this.value+'')">
					<option value="none" <?php if($newstyles['catMainUL']['display'] == "none") { ?>selected="selected"<?php } ?>>none</option>
					<option value="block" <?php if($newstyles['catMainUL']['display'] == "block") { ?>selected="selected"<?php } ?>>block</option>
					<option value="inline" <?php if($newstyles['catMainUL']['display'] == "inline") { ?>selected="selected"<?php } ?>>inline</option>
					<option value="inline-block" <?php if($newstyles['catMainUL']['display'] == "inline-block") { ?>selected="selected"<?php } ?>>inline-block</option>
				</select><br />
				List Style: <select name="alien_event_new_styles[catMainUL][list-style]" onChange="changecss('catMainUL','listStyle',''+this.value+'')">
					<option value="none" <?php if($newstyles['catMainUL']['list-style'] == "none") { ?>selected="selected"<?php } ?>>none</option>
					<option value="cirlce" <?php if($newstyles['catMainUL']['list-style'] == "circle") { ?>selected="selected"<?php } ?>>circle</option>
					<option value="disc" <?php if($newstyles['catMainUL']['list-style'] == "disc") { ?>selected="selected"<?php } ?>>disc</option>
					<option value="square" <?php if($newstyles['catMainUL']['list-style'] == "square") { ?>selected="selected"<?php } ?>>square</option>
					<option value="decimal" <?php if($newstyles['catMainUL']['list-style'] == "decimal") { ?>selected="selected"<?php } ?>>decimal</option>
					<option value="lower-alpha" <?php if($newstyles['catMainUL']['list-style'] == "lower-alpha") { ?>selected="selected"<?php } ?>>lower-alpha</option>
					<option value="upper-alpha" <?php if($newstyles['catMainUL']['list-style'] == "upper-alpha") { ?>selected="selected"<?php } ?>>upper-alpha</option>
					<option value="lower-roman" <?php if($newstyles['catMainUL']['list-style'] == "lower-roman") { ?>selected="selected"<?php } ?>>lower-roman</option>
					<option value="upper-roman" <?php if($newstyles['catMainUL']['list-style'] == "upper-roman") { ?>selected="selected"<?php } ?>>upper-roman</option>
				</select><br />
				Float: <select name="alien_event_new_styles[catMainList][float]" onChange="changecss('catMainList','float',''+this.value+'')">
					<option value="left" <?php if($newstyles['catMainList']['float'] == "left") { ?>selected="selected"<?php } ?>>Left</option>
					<option value="none" <?php if($newstyles['catMainList']['float'] == "none") { ?>selected="selected"<?php } ?>>None</option>
					<option value="right" <?php if($newstyles['catMainList']['float'] == "right") { ?>selected="selected"<?php } ?>>Right</option>
				</select><br />
				Link Size: <input type="text" name="alien_event_new_styles[catMainList li a][font-size]" value="<?php echo $newstyles['catMainList li a']['font-size']; ?>" onChange="changecss('catMainList li a','fontSize',''+this.value+'')"><br />
				Link Color: <input type="text" name="alien_event_new_styles[catMainList li a][color]" value="<?php echo $newstyles['catMainList li a']['color']; ?>" onChange="changecss('catMainList li a','color',''+this.value+'')"><br />
				Hover Color: <input type="text" name="alien_event_new_styles[catMainList li a:hover][color]" value="<?php echo $newstyles['catMainList li a:hover']['color']; ?>" onChange="changecss('catMainList li a:hover','color',''+this.value+'')">
			</td>
			</tr>
			<tr valign="top">
			<th scope="row">Child Cat List</th>
			<td>
				Display: <select name="alien_event_new_styles[catSubUL][display]" onChange="changecss('catSubUL','display',''+this.value+'')">
					<option value="none" <?php if($newstyles['catSubUL']['display'] == "none") { ?>selected="selected"<?php } ?>>none</option>
					<option value="block" <?php if($newstyles['catSubUL']['display'] == "block") { ?>selected="selected"<?php } ?>>block</option>
					<option value="inline" <?php if($newstyles['catSubUL']['display'] == "inline") { ?>selected="selected"<?php } ?>>inline</option>
					<option value="inline-block" <?php if($newstyles['catSubUL']['display'] == "inline-block") { ?>selected="selected"<?php } ?>>inline-block</option>
				</select><br />
				List Style: <select name="alien_event_new_styles[catSubUL][list-style]" onChange="changecss('catSubUL','listStyle',''+this.value+'')">
					<option value="none" <?php if($newstyles['catSubUL']['list-style'] == "none") { ?>selected="selected"<?php } ?>>none</option>
					<option value="cirlce" <?php if($newstyles['catSubUL']['list-style'] == "circle") { ?>selected="selected"<?php } ?>>circle</option>
					<option value="disc" <?php if($newstyles['catSubUL']['list-style'] == "disc") { ?>selected="selected"<?php } ?>>disc</option>
					<option value="square" <?php if($newstyles['catSubUL']['list-style'] == "square") { ?>selected="selected"<?php } ?>>square</option>
					<option value="decimal" <?php if($newstyles['catSubUL']['list-style'] == "decimal") { ?>selected="selected"<?php } ?>>decimal</option>
					<option value="lower-alpha" <?php if($newstyles['catSubUL']['list-style'] == "lower-alpha") { ?>selected="selected"<?php } ?>>lower-alpha</option>
					<option value="upper-alpha" <?php if($newstyles['catSubUL']['list-style'] == "upper-alpha") { ?>selected="selected"<?php } ?>>upper-alpha</option>
					<option value="lower-roman" <?php if($newstyles['catSubUL']['list-style'] == "lower-roman") { ?>selected="selected"<?php } ?>>lower-roman</option>
					<option value="upper-roman" <?php if($newstyles['catSubUL']['list-style'] == "upper-roman") { ?>selected="selected"<?php } ?>>upper-roman</option>
				</select><br />
				Float: <select name="alien_event_new_styles[catSubList][float]" onChange="changecss('catSubList','float',''+this.value+'')">
					<option value="left" <?php if($newstyles['catSubList']['float'] == "left") { ?>selected="selected"<?php } ?>>Left</option>
					<option value="none" <?php if($newstyles['catSubList']['float'] == "none") { ?>selected="selected"<?php } ?>>None</option>
					<option value="right" <?php if($newstyles['catSubList']['float'] == "right") { ?>selected="selected"<?php } ?>>Right</option>
				</select><br />
				Link Size: <input type="text" name="alien_event_new_styles[catSubList li a][font-size]" value="<?php echo $newstyles['catSubList li a']['font-size']; ?>" onChange="changecss('catSubList li a','fontSize',''+this.value+'')"><br />
				Link Color: <input type="text" name="alien_event_new_styles[catSubList li a][color]" value="<?php echo $newstyles['catSubList li a']['color']; ?>" onChange="changecss('catSubList li a','color',''+this.value+'')"><br />
				Hover Color: <input type="text" name="alien_event_new_styles[catSubList li a:hover][color]" value="<?php echo $newstyles['catSubList li a:hover']['color']; ?>" onChange="changecss('catSubList li a:hover','color',''+this.value+'')">
			</td>
			</tr>
			<tr valign="top">
			<th scope="row">Grand Child Cat List</th>
			<td>
				Display: <select name="alien_event_new_styles[catChildUL][display]" onChange="changecss('catChildUL','display',''+this.value+'')">
					<option value="none" <?php if($newstyles['catChildUL']['display'] == "none") { ?>selected="selected"<?php } ?>>none</option>
					<option value="block" <?php if($newstyles['catChildUL']['display'] == "block") { ?>selected="selected"<?php } ?>>block</option>
					<option value="inline" <?php if($newstyles['catChildUL']['display'] == "inline") { ?>selected="selected"<?php } ?>>inline</option>
					<option value="inline-block" <?php if($newstyles['catChildUL']['display'] == "inline-block") { ?>selected="selected"<?php } ?>>inline-block</option>
				</select><br />
				List Style: <select name="alien_event_new_styles[catChildUL][list-style]" onChange="changecss('catChildUL','listStyle',''+this.value+'')">
					<option value="none" <?php if($newstyles['catChildUL']['list-style'] == "none") { ?>selected="selected"<?php } ?>>none</option>
					<option value="cirlce" <?php if($newstyles['catChildUL']['list-style'] == "circle") { ?>selected="selected"<?php } ?>>circle</option>
					<option value="disc" <?php if($newstyles['catChildUL']['list-style'] == "disc") { ?>selected="selected"<?php } ?>>disc</option>
					<option value="square" <?php if($newstyles['catChildUL']['list-style'] == "square") { ?>selected="selected"<?php } ?>>square</option>
					<option value="decimal" <?php if($newstyles['catChildUL']['list-style'] == "decimal") { ?>selected="selected"<?php } ?>>decimal</option>
					<option value="lower-alpha" <?php if($newstyles['catChildUL']['list-style'] == "lower-alpha") { ?>selected="selected"<?php } ?>>lower-alpha</option>
					<option value="upper-alpha" <?php if($newstyles['catChildUL']['list-style'] == "upper-alpha") { ?>selected="selected"<?php } ?>>upper-alpha</option>
					<option value="lower-roman" <?php if($newstyles['catChildUL']['list-style'] == "lower-roman") { ?>selected="selected"<?php } ?>>lower-roman</option>
					<option value="upper-roman" <?php if($newstyles['catChildUL']['list-style'] == "upper-roman") { ?>selected="selected"<?php } ?>>upper-roman</option>
				</select><br />
				Float: <select name="alien_event_new_styles[catChildList][float]" onChange="changecss('catChildList','float',''+this.value+'')">
					<option value="left" <?php if($newstyles['catChildList']['float'] == "left") { ?>selected="selected"<?php } ?>>Left</option>
					<option value="none" <?php if($newstyles['catChildList']['float'] == "none") { ?>selected="selected"<?php } ?>>None</option>
					<option value="right" <?php if($newstyles['catChildList']['float'] == "right") { ?>selected="selected"<?php } ?>>Right</option>
				</select><br />
				Link Size: <input type="text" name="alien_event_new_styles[catChildList li a][font-size]" value="<?php echo $newstyles['catChildList li a']['font-size']; ?>" onChange="changecss('catChildList li a','fontSize',''+this.value+'')"><br />
				Link Color: <input type="text" name="alien_event_new_styles[catChildList li a][color]" value="<?php echo $newstyles['catChildList li a']['color']; ?>" onChange="changecss('catChildList li a','color',''+this.value+'')"><br />
				Hover Color: <input type="text" name="alien_event_new_styles[catChildList li a:hover][color]" value="<?php echo $newstyles['catChildList li a:hover']['color']; ?>" onChange="changecss('catChildList li a:hover','color',''+this.value+'')"><br />
			</td>
			</tr>
-->
<?php } ?>
		<table class="widefat page">
			<thead>
		        <tr valign="top">
		        <th colspan="2">
				Event List
			</th>
		        </tr>
			</thead>
			<tr valign="top">
			<th scope="row">Date Heading</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[eventDateHead][display]" value="block">
				BG Image: <input type="text" name="alien_event_new_styles[eventDateHead][background-image]" value="<?php echo $newstyles['eventDateHead']['background-image']; ?>" onChange="javascript:get('eventDateHead','backgroundImage',''+this.value+'');"><br />
				BG Color: <input type="text" name="alien_event_new_styles[eventDateHead][background-color]" value="<?php echo $newstyles['eventDateHead']['background-color']; ?>" onChange="changecss('eventDateHead','backgroundColor',''+this.value+'')"><br />
				Width: <input type="text" name="alien_event_new_styles[eventDateHead][width]" value="<?php echo $newstyles['eventDateHead']['width']; ?>" onChange="changecss('eventDateHead','width',''+this.value+'')"><br />
				Left Margin: <input type="text" name="alien_event_new_styles[eventDateHead][margin-left]" value="<?php echo $newstyles['eventDateHead']['margin-left']; ?>" onChange="changecss('eventDateHead','marginLeft',''+this.value+'')"><br />
				Right Margin: <input type="text" name="alien_event_new_styles[eventDateHead][margin-right]" value="<?php echo $newstyles['eventDateHead']['margin-right']; ?>" onChange="changecss('eventDateHead','marginRight',''+this.value+'')"><br />
				Text Align: <select name="alien_event_new_styles[eventDateHead][text-align]" onChange="changecss('eventDateHead','textAlign',''+this.value+'')">
					<option value="left" <?php if($newstyles['eventDateHead']['text-align'] == "left") { ?>selected="selected"<?php } ?>>Left</option>
					<option value="center" <?php if($newstyles['eventDateHead']['text-align'] == "center") { ?>selected="selected"<?php } ?>>Center</option>
					<option value="right" <?php if($newstyles['eventDateHead']['text-align'] == "right") { ?>selected="selected"<?php } ?>>Right</option>
				</select><br />
				Text Color: <input type="text" name="alien_event_new_styles[eventDateHead][color]" value="<?php echo $newstyles['eventDateHead']['color']; ?>" onChange="changecss('eventDateHead','color',''+this.value+'')" ><br />
				Font Size: <input type="text" name="alien_event_new_styles[eventDateHead][font-size]" value="<?php echo $newstyles['eventDateHead']['font-size']; ?>" onChange="changecss('eventDateHead','fontSize',''+this.value+'')"><br />
			Border: <select name="alien_event_new_styles[eventDateHead][border]" onChange="changecss('eventDateHead','border',''+this.value+'')">
					<?php if ($newstyles['eventDateHead']['border']) { ?> <option value="<?php echo $newstyles['eventDateHead']['border']; ?>"><?php echo $newstyles['eventDateHead']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[eventDateHead][border-style]" onChange="changecss('eventDateHead','borderStyle',''+this.value+'')">
					<?php if ($newstyles['eventDateHead']['border-style']) { ?> <option value="<?php echo $newstyles['eventDateHead']['border-style']; ?>"><?php echo $newstyles['eventDateHead']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[eventDateHead][border-color]" value="<?php echo $newstyles['eventDateHead']['border-color']; ?>" onChange="changecss('eventDateHead','borderColor',''+this.value+'')">
			</td>
			</tr>
			<tr valign="top">
			<th scope="row">List Wrapper (wraps all the events)</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[eventsList][display]" value="block">
				BG Image: <input type="text" name="alien_event_new_styles[eventsList][background-image]" value="<?php echo $newstyles['eventsList']['background-image']; ?>" onChange="javascript:get('eventsList','backgroundImage',''+this.value+'');"><br />
				BG Color: <input type="text" name="alien_event_new_styles[eventsList][background-color]" value="<?php echo $newstyles['eventsList']['background-color']; ?>" onChange="changecss('eventsList','backgroundColor',''+this.value+'')"><br />
				Width: <input type="text" name="alien_event_new_styles[eventsList][width]" value="<?php echo $newstyles['eventsList']['width']; ?>" onChange="changecss('eventsList','width',''+this.value+'')"><br />
				Left Margin: <input type="text" name="alien_event_new_styles[eventsList][margin-left]" value="<?php echo $newstyles['eventsList']['margin-left']; ?>" onChange="changecss('eventsList','marginLeft',''+this.value+'')"><br />
				Right Margin: <input type="text" name="alien_event_new_styles[eventsList][margin-right]" value="<?php echo $newstyles['eventsList']['margin-right']; ?>" onChange="changecss('eventsList','marginRight',''+this.value+'')"><br />
				Text Align: <select name="alien_event_new_styles[eventsList][text-align]" onChange="changecss('eventsList','textAlign',''+this.value+'')">
					<option value="left" <?php if($newstyles['eventsList']['text-align'] == "left") { ?>selected="selected"<?php } ?>>Left</option>
					<option value="center" <?php if($newstyles['eventsList']['text-align'] == "center") { ?>selected="selected"<?php } ?>>Center</option>
					<option value="right" <?php if($newstyles['eventsList']['text-align'] == "right") { ?>selected="selected"<?php } ?>>Right</option>
				</select><br />
			Border: <select name="alien_event_new_styles[eventsList][border]" onChange="changecss('eventsList','border',''+this.value+'')">
					<?php if ($newstyles['eventsList']['border']) { ?> <option value="<?php echo $newstyles['eventsList']['border']; ?>"><?php echo $newstyles['eventsList']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[eventsList][border-style]" onChange="changecss('eventsList','borderStyle',''+this.value+'')">
					<?php if ($newstyles['eventsList']['border-style']) { ?> <option value="<?php echo $newstyles['eventsList']['border-style']; ?>"><?php echo $newstyles['eventsList']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[eventsList][border-color]" value="<?php echo $newstyles['eventsList']['border-color']; ?>" onChange="changecss('eventsList','borderColor',''+this.value+'')">

			</td>
			</tr>

			<tr valign="top">
			<th scope="row">Event Wrapper <br />(wraps individual events)</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[eventBlock][display]" value="block">
				BG Image: <input type="text" name="alien_event_new_styles[eventBlock][background-image]" value="<?php echo $newstyles['eventBlock']['background-image']; ?>" onChange="javascript:get('eventBlock','backgroundImage',''+this.value+'');"><br />
				BG Color: <input type="text" name="alien_event_new_styles[eventBlock][background-color]" value="<?php echo $newstyles['eventBlock']['background-color']; ?>" onChange="changecss('eventBlock','backgroundColor',''+this.value+'')"><br />
				Width: <input type="text" name="alien_event_new_styles[eventBlock][width]" value="<?php echo $newstyles['eventBlock']['width']; ?>" onChange="changecss('eventBlock','width',''+this.value+'')"><br />
				Left Margin: <input type="text" name="alien_event_new_styles[eventBlock][margin-left]" value="<?php echo $newstyles['eventBlock']['margin-left']; ?>" onChange="changecss('eventBlock','leftMargin',''+this.value+'')"><br />
				Right Margin: <input type="text" name="alien_event_new_styles[eventBlock][margin-right]" value="<?php echo $newstyles['eventBlock']['margin-right']; ?>" onChange="changecss('eventBlock','rightMargin',''+this.value+'')"><br />
				Font Size: <input type="text" name="alien_event_new_styles[eventBlock][font-size]" value="<?php echo $newstyles['eventBlock']['font-size']; ?>" onChange="changecss('eventBlock','fontSize',''+this.value+'')"><br />
				Text Color: <input type="text" name="alien_event_new_styles[eventBlock][color]" value="<?php echo $newstyles['eventBlock']['color']; ?>" onChange="changecss('eventBlock','color',''+this.value+'')"><br />
				Text Align: <select name="alien_event_new_styles[eventBlock][text-align]" onChange="changecss('eventBlock','textAlign',''+this.value+'')">
					<option value="left" <?php if($newstyles['eventBlock']['text-align'] == "left") { ?>selected="selected"<?php } ?>>Left</option>
					<option value="center" <?php if($newstyles['eventBlock']['text-align'] == "center") { ?>selected="selected"<?php } ?>>Center</option>
					<option value="right" <?php if($newstyles['eventBlock']['text-align'] == "right") { ?>selected="selected"<?php } ?>>Right</option>
				</select><br />
			Border: <select name="alien_event_new_styles[eventBlock][border]" onChange="changecss('eventBlock','border',''+this.value+'')">
					<?php if ($newstyles['eventBlock']['border']) { ?> <option value="<?php echo $newstyles['eventBlock']['border']; ?>"><?php echo $newstyles['eventBlock']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[eventBlock][border-style]" onChange="changecss('eventBlock','borderStyle',''+this.value+'')">
					<?php if ($newstyles['eventBlock']['border-style']) { ?> <option value="<?php echo $newstyles['eventBlock']['border-style']; ?>"><?php echo $newstyles['eventBlock']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[eventBlock][border-color]" value="<?php echo $newstyles['eventBlock']['border-color']; ?>" onChange="changecss('eventBlock','borderColor',''+this.value+'')">

			</td>
			</tr>
			<tr valign="top">
			<th scope="row">Event Detail Wrapper (Wraps the details of the event)</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[eventDetails][display]" value="block">
				BG Image: <input type="text" name="alien_event_new_styles[eventDetails][background-image]" value="<?php echo $newstyles['eventDetails']['background-image']; ?>" onChange="javascript:get('eventDetails','backgroundImage',''+this.value+'');"><br />
				BG Color: <input type="text" name="alien_event_new_styles[eventDetails][background-color]" value="<?php echo $newstyles['eventDetails']['background-color']; ?>" onChange="changecss('eventDetails','backgroundColor',''+this.value+'')"><br />
				Text Color: <input type="text" name="alien_event_new_styles[eventDetails][color]" value="<?php echo $newstyles['eventDetails']['color']; ?>" onChange="changecss('eventDetails','color',''+this.value+'')"><br />
				Font Size: <input type="text" name="alien_event_new_styles[eventDetails][font-size]" value="<?php echo $newstyles['eventDetails']['font-size']; ?>" onChange="changecss('eventDetails','fontSize',''+this.value+'')"><br />
				Width: <input type="text" name="alien_event_new_styles[eventDetails][width]" value="<?php echo $newstyles['eventDetails']['width']; ?>" onChange="changecss('eventDetails','width',''+this.value+'')"><br />
				Height: <input type="text" name="alien_event_new_styles[eventDetails][height]" value="<?php echo $newstyles['eventDetails']['height']; ?>" onChange="changecss('eventDetails','height',''+this.value+'')"><br />
				Left Margin: <input type="text" name="alien_event_new_styles[eventDetails][margin-left]" value="<?php echo $newstyles['eventDetails']['margin-left']; ?>" onChange="changecss('eventDetails','marginLeft',''+this.value+'')"><br />
				Right Margin: <input type="text" name="alien_event_new_styles[eventDetails][margin-right]" value="<?php echo $newstyles['eventDetails']['margin-right']; ?>" onChange="changecss('eventDetails','marginRight',''+this.value+'')"><br />
				Float: <select name="alien_event_new_styles[eventDetails][float]" onChange="changecss('eventDetails','float',''+this.value+'')">
					<option value="left" <?php if($newstyles['eventDetails']['float'] == "left") { ?>selected="selected"<?php } ?>>Left</option>
					<option value="none" <?php if($newstyles['eventDetails']['float'] == "none") { ?>selected="selected"<?php } ?>>None</option>
					<option value="right" <?php if($newstyles['eventDetails']['float'] == "right") { ?>selected="selected"<?php } ?>>Right</option>
				</select><br />
				Text Align: <select name="alien_event_new_styles[eventDetails][text-align]" onChange="changecss('eventDetails','textAlign',''+this.value+'')">
					<option value="left" <?php if($newstyles['eventDetails']['text-align'] == "left") { ?>selected="selected"<?php } ?>>Left</option>
					<option value="center" <?php if($newstyles['eventDetails']['text-align'] == "center") { ?>selected="selected"<?php } ?>>Center</option>
					<option value="right" <?php if($newstyles['eventDetails']['text-align'] == "right") { ?>selected="selected"<?php } ?>>Right</option>
				</select><br />
			Border: <select name="alien_event_new_styles[eventDetails][border]" onChange="changecss('eventDetails','border',''+this.value+'')">
					<?php if ($newstyles['eventDetails']['border']) { ?> <option value="<?php echo $newstyles['eventDetails']['border']; ?>"><?php echo $newstyles['eventDetails']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[eventDetails][border-style]" onChange="changecss('eventDetails','borderStyle',''+this.value+'')">
					<?php if ($newstyles['eventDetails']['border-style']) { ?> <option value="<?php echo $newstyles['eventDetails']['border-style']; ?>"><?php echo $newstyles['eventDetails']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[eventDetails][border-color]" value="<?php echo $newstyles['eventDetails']['border-color']; ?>" onChange="changecss('eventDetails','borderColor',''+this.value+'')">

			</td>
			</tr>
			<tr valign="top">
			<th scope="row">Buttons</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[btnLink][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[eventOptions span][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[eventOptions span a][text-decoration]" value="none">
				BG Image: <input type="text" name="alien_event_new_styles[eventOptions span][background-image]" value="<?php echo $newstyles['eventOptions span']['background-image']; ?>" onChange="javascript:get('eventOptions span','backgroundImage',''+this.value+'');"><br />
				BG Color: <input type="text" name="alien_event_new_styles[eventOptions span][background-color]" value="<?php echo $newstyles['eventOptions span']['background-color']; ?>" onChange="changecss('eventOptions span','backgroundColor',''+this.value+'')"><br />
				Link Color: <input type="text" name="alien_event_new_styles[eventOptions span a][color]" value="<?php echo $newstyles['eventOptions span a']['color']; ?>" onChange="changecss('eventOptions span a','color',''+this.value+'')"><br />
				Hover Color: <input type="text" name="alien_event_new_styles[eventOptions span a:hover][color]" value="<?php echo $newstyles['eventOptions span a:hover']['color']; ?>" onChange="changecss('eventOptions span a:hover','color',''+this.value+'')"><br />
				Font Size: <input type="text" name="alien_event_new_styles[eventOptions span a][font-size]" value="<?php echo $newstyles['eventOptions span a']['font-size']; ?>"><br />
				Width: <input type="text" name="alien_event_new_styles[eventOptions span][width]" value="<?php echo $newstyles['eventOptions span']['width']; ?>" onChange="changecss('eventOptions span','width',''+this.value+'')"><br />
				Height: <input type="text" name="alien_event_new_styles[eventOptions span][height]" value="<?php echo $newstyles['eventOptions span']['height']; ?>" onChange="changecss('eventOptions span','height',''+this.value+'')"><br />
				Left Margin: <input type="text" name="alien_event_new_styles[eventOptions span][margin-left]" value="<?php echo $newstyles['eventOptions span']['margin-left']; ?>" onChange="changecss('eventOptions span','marginLeft',''+this.value+'')"><br />
				Right Margin: <input type="text" name="alien_event_new_styles[eventOptions span][margin-right]" value="<?php echo $newstyles['eventOptions span']['margin-right']; ?>" onChange="changecss('eventOptions span','marginRight',''+this.value+'')"><br />
				Padding: <input type="text" name="alien_event_new_styles[eventOptions span][padding]" value="<?php echo $newstyles['eventOptions span']['padding']; ?>" onChange="changecss('eventOptions span','padding',''+this.value+'')"><br />
				Float: <select name="alien_event_new_styles[eventOptions span][float]" onChange="changecss('eventOptions span','float',''+this.value+'')">
					<option value="left" <?php if($newstyles['eventOptions span']['float'] == "left") { ?>selected="selected"<?php } ?>>Left</option>
					<option value="none" <?php if($newstyles['eventOptions span']['float'] == "none") { ?>selected="selected"<?php } ?>>None</option>
					<option value="right" <?php if($newstyles['eventOptions span']['float'] == "right") { ?>selected="selected"<?php } ?>>Right</option>
				</select><br />
				Text Align: <select name="alien_event_new_styles[eventOptions span][text-align]" onChange="changecss('eventOptions span','float',''+this.value+'')">
					<option value="left" <?php if($newstyles['eventOptions span']['text-align'] == "left") { ?>selected="selected"<?php } ?>>Left</option>
					<option value="center" <?php if($newstyles['eventOptions span']['text-align'] == "center") { ?>selected="selected"<?php } ?>>Center</option>
					<option value="right" <?php if($newstyles['eventOptions span']['text-align'] == "right") { ?>selected="selected"<?php } ?>>Right</option>
				</select><br />
			Border: <select name="alien_event_new_styles[eventOptions span][border]" onChange="changecss('eventOptions span','border',''+this.value+'')">
					<?php if ($newstyles['eventOptions span']['border']) { ?> <option value="<?php echo $newstyles['eventOptions span']['border']; ?>"><?php echo $newstyles['eventOptions span']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[eventOptions span][border-style]" onChange="changecss('eventOptions span','borderStyle',''+this.value+'')">
					<?php if ($newstyles['eventOptions span']['border-style']) { ?> <option value="<?php echo $newstyles['eventOptions span']['border-style']; ?>"><?php echo $newstyles['eventOptions span']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[eventOptions span][border-color]" value="<?php echo $newstyles['eventOptions span']['border-color']; ?>" onChange="changecss('eventOptions span','borderColor',''+this.value+'')">

			</td>
			</tr>
			<tr valign="top">
			<th scope="row">Event Title</th>
			<td>
				Color: <input type="text" name="alien_event_new_styles[myEventTitle][color]" value="<?php echo $newstyles['myEventTitle']['color']; ?>" onChange="changecss('myEventTitle','color',''+this.value+'')">
				Font Size: <input type="text" name="alien_event_new_styles[myEventTitle][font-size]" value="<?php echo $newstyles['myEventTitle']['font-size']; ?>" onChange="changecss('myEventTitle','fontSize',''+this.value+'')">
				Weight: <select name="alien_event_new_styles[myEventTitle][font-weight]" onChange="changecss('myEventTitle','fontWeight',''+this.value+'')">
					<?php if ($newstyles['myEventTitle']['font-weight']) { ?>  <option value="<?php echo $newstyles['myEventTitle']['font-weight']; ?>"><?php echo $newstyles['myEventTitle']['font-weight']; ?></option>  <?php } ?>
					<option value="normal">normal</option>
					<option value="bold">bold</option>
					</select>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row">Location Title</th>
			<td>
				Color: <input type="text" name="alien_event_new_styles[myEventLocationTitle][color]" value="<?php echo $newstyles['myEventLocationTitle']['color']; ?>" onChange="changecss('myEventLocationTitle','color',''+this.value+'')">
				Font Size: <input type="text" name="alien_event_new_styles[myEventLocationTitle][font-size]" value="<?php echo $newstyles['myEventLocationTitle']['font-size']; ?>" onChange="changecss('myEventLocationTitle','fontSize',''+this.value+'')">
				Weight: <select name="alien_event_new_styles[myEventLocationTitle][font-weight]" onChange="changecss('myEventLocationTitle','fontWeight',''+this.value+'')">
					<?php if ($newstyles['myEventLocationTitle']['font-weight']) { ?>  <option value="<?php echo $newstyles['myEventLocationTitle']['font-weight']; ?>"><?php echo $newstyles['myEventLocationTitle']['font-weight']; ?></option>  <?php } ?>
					<option value="normal">normal</option>
					<option value="bold">bold</option>
					</select>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row">Location Text</th>
			<td>
				Color: <input type="text" name="alien_event_new_styles[myEventLocation][color]" value="<?php echo $newstyles['myEventLocation']['color']; ?>" onChange="changecss('myEventLocation','color',''+this.value+'')">
				Font Size: <input type="text" name="alien_event_new_styles[myEventLocation][font-size]" value="<?php echo $newstyles['myEventLocation']['font-size']; ?>" onChange="changecss('myEventLocation','fontSize',''+this.value+'')">
				Weight: <select name="alien_event_new_styles[myEventLocation][font-weight]" onChange="changecss('myEventLocation','fontWeight',''+this.value+'')">
					<?php if ($newstyles['myEventLocation']['font-weight']) { ?>  <option value="<?php echo $newstyles['myEventLocation']['font-weight']; ?>"><?php echo $newstyles['myEventLocation']['font-weight']; ?></option>  <?php } ?>
					<option value="normal">normal</option>
					<option value="bold">bold</option>
					</select>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row">Time Title</th>
			<td>
				Color: <input type="text" name="alien_event_new_styles[myEventTimeTitle][color]" value="<?php echo $newstyles['myEventTimeTitle']['color']; ?>" onChange="changecss('myEventTimeTitle','color',''+this.value+'')">
				Font Size: <input type="text" name="alien_event_new_styles[myEventTimeTitle][font-size]" value="<?php echo $newstyles['myEventTimeTitle']['font-size']; ?>" onChange="changecss('myEventTimeTitle','fontSize',''+this.value+'')">
				Weight: <select name="alien_event_new_styles[myEventTimeTitle][font-weight]" onChange="changecss('myEventTimeTitle','fontWeight',''+this.value+'')">
					<?php if ($newstyles['myEventTimeTitle']['font-weight']) { ?>  <option value="<?php echo $newstyles['myEventTimeTitle']['font-weight']; ?>"><?php echo $newstyles['myEventTimeTitle']['font-weight']; ?></option>  <?php } ?>
					<option value="normal">normal</option>
					<option value="bold">bold</option>
					</select>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row">Time Text</th>
			<td>
				Color: <input type="text" name="alien_event_new_styles[myEventTime][color]" value="<?php echo $newstyles['myEventTime']['color']; ?>" onChange="changecss('myEventTime','color',''+this.value+'')">
				Font Size: <input type="text" name="alien_event_new_styles[myEventTime][font-size]" value="<?php echo $newstyles['myEventTime']['font-size']; ?>" onChange="changecss('myEventTime','fontSize',''+this.value+'')">
				Weight: <select name="alien_event_new_styles[myEventTime][font-weight]" onChange="changecss('myEventTime','fontWeight',''+this.value+'')">
					<?php if ($newstyles['myEventTime']['font-weight']) { ?>  <option value="<?php echo $newstyles['myEventTime']['font-weight']; ?>"><?php echo $newstyles['myEventTime']['font-weight']; ?></option>  <?php } ?>
					<option value="normal">normal</option>
					<option value="bold">bold</option>
					</select>
			</td>
			</tr>
		</table>

</div>
<!-- End eventlistform -->

<!-- Start calwidform -->
<div id="calwidform" style="display:block">
	<table class="widefat page">
		<thead>
	        <tr valign="top">
	        <th colspan="3">Widget<br><a href="javascript:togglePreview('miniWindow')">Show/Hide Live Preview</a>
		<input type="hidden" name="alien_event_new_styles[cal][display]" value="block">
		<input type="hidden" name="alien_event_new_styles[cal][overflow]" value="hidden">
		</th>
	        </tr>
		</thead>
	        <tr valign="top">
	        <th scope="row">Appearance</th>
		<td>
			BG Image: <input type="text" name="alien_event_new_styles[cal][background-image]" value="<?php echo $newstyles['cal']['background-image']; ?>" onChange="javascript:get('cal','backgroundImage',''+this.value+'');"> <small>use full url</small><br />
	  		Width: <input type="text" name="alien_event_new_styles[cal][width]" value="<?php echo $newstyles['cal']['width']; ?>" onChange="javascript:document.getElementById('cal').style.width=''+this.value+'';"> <small>in pixels, ie... 200px</small><br />
			BG Color: <input type="text" name="alien_event_new_styles[cal][background-color]" value="<?php echo $newstyles['cal']['background-color']; ?>" onChange="javascript:document.getElementById('cal').style.backgroundColor=''+this.value+'';">
		</td>
	        </tr>
	    </table>
	<table class="widefat page">
		<thead>
	        <tr valign="top">
	        <th colspan="3">Widget Calendar<br /><small>If you change the border size, you must change the style and color for it to show in the mini calendar</small>
		<input type="hidden" name="alien_event_new_styles[calTable][display]" value="block">
		<input type="hidden" name="alien_event_new_styles[calTable][overflow]" value="hidden">
		<input type="hidden" name="alien_event_new_styles[calTable][width]" value="100%">
		<input type="hidden" name="alien_event_new_styles[calTH][width]" value="100%">
		</th>
	        </tr>
		</thead>
	        <tr valign="top">
	        <th scope="row">Outer Table</th>
			<td>
			BG Image: <input type="text" name="alien_event_new_styles[calTable][background-image]" value="<?php echo $newstyles['calTable']['background-image']; ?>" onChange="javascript:get('calTable','backgroundImage',''+this.value+'');"> <small>use full url</small><br />
			BG Color: <input type="text" name="alien_event_new_styles[calTable][background-color]" value="<?php echo $newstyles['calTable']['background-color']; ?>" onChange="javascript:document.getElementById('calTable').style.backgroundColor=this.value;"><br />
			Border: <select name="alien_event_new_styles[calTable][border]" onChange="javascript:document.getElementById('calTable').style.border=this.value;">
					<?php if ($newstyles['calTable']['border']) { ?> <option value="<?php echo $newstyles['calTable']['border']; ?>"><?php echo $newstyles['calTable']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calTable][border-style]"  onChange="javascript:document.getElementById('calTable').style.borderStyle=this.value;">
					<?php if ($newstyles['calTable']['border-style']) { ?> <option value="<?php echo $newstyles['calTable']['border-style']; ?>"><?php echo $newstyles['calTable']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calTable][border-color]" value="<?php echo $newstyles['calTable']['border-color']; ?>" onChange="javascript:document.getElementById('calTable').style.borderColor=this.value;">
			</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row">Month Header:</th>
	        <td>
			<input type="hidden" name="alien_event_new_styles[calTH][text-align]" value="center">
			BG Image: <input type="text" name="alien_event_new_styles[calTH][background-image]" value="<?php echo $newstyles['calTH']['background-image']; ?>" onChange="javascript:get('calTH','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			BG Color: <input type="text" name="alien_event_new_styles[calTH][background-color]" value="<?php echo $newstyles['calTH']['background-color']; ?>" onChange="javascript:document.getElementById('calTH').style.backgroundColor=''+this.value+'';"><br />
		        Height: <input type="text" name="alien_event_new_styles[calTH][height]" value="<?php echo $newstyles['calTH']['height']; ?>" onChange="javascript:document.getElementById('calTH').style.height=''+this.value+'';"><br />
			Border: <select name="alien_event_new_styles[calTH][border]" onChange="javascript:document.getElementById('calTH').style.border=''+this.value+'';">
					<?php if ($newstyles['calTH']['border']) { ?> <option value="<?php echo $newstyles['calTH']['border']; ?>"><?php echo $newstyles['calTH']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calTH][border-style]" onChange="javascript:document.getElementById('calTH').style.borderStyle=''+this.value+'';">
					<?php if ($newstyles['calTH']['border-style']) { ?> <option value="<?php echo $newstyles['calTH']['border-style']; ?>"><?php echo $newstyles['calTH']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calTH][border-color]" value="<?php echo $newstyles['calTH']['border-color']; ?>" onChange="javascript:document.getElementById('calTH').style.borderColor=''+this.value+'';">
		</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row">Month Text:</th>
	        <td>
			Color: <input type="text" name="alien_event_new_styles[calMonth][color]" value="<?php echo $newstyles['calMonth']['color']; ?>" onChange="javascript:document.getElementById('calMonth').style.color=''+this.value+'';"> 
			Weight: <select name="alien_event_new_styles[calMonth][font-weight]"  onChange="javascript:document.getElementById('calMonth').style.fontWeight=''+this.value+'';">
				<?php if ($newstyles['calMonth']['font-weight']) { ?>  <option value="<?php echo $newstyles['calMonth']['font-weight']; ?>"><?php echo $newstyles['calMonth']['font-weight']; ?></option>  <?php } ?>
					<option value="normal">normal</option>
					<option value="bold">bold</option>
				</select>
		</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row">Year Text:</th>
	        <td>
			Color: <input type="text" name="alien_event_new_styles[calYear][color]" value="<?php echo $newstyles['calYear']['color']; ?>" onChange="javascript:document.getElementById('calYear').style.color=''+this.value+'';"> 
			Weight: <select name="alien_event_new_styles[calYear][font-weight]" onChange="javascript:document.getElementById('calYear').style.fontWeight=''+this.value+'';">
				<?php if ($newstyles['calYear']['font-weight']) { ?>  <option value="<?php echo $newstyles['calYear']['font-weight']; ?>"><?php echo $newstyles['calYear']['font-weight']; ?></option>  <?php } ?>
					<option value="normal">normal</option>
					<option value="bold">bold</option>
				</select>
		</td>
	        </tr>
		<tr valign="top">
			<th scope="row">Previous Button</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calPrev][text-align]" value="center">
				<input type="hidden" name="alien_event_new_styles[calPrev][text-decoration]" value="none">
				<input type="hidden" name="alien_event_new_styles[calPrev][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calPrev][display]" value="block">
			        BG Image: <input type="text" name="alien_event_new_styles[calPrev][background-image]" value="<?php echo $newstyles['calPrev']['background-image']; ?>" onChange="javascript:get('calPrev','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calPrev][background-color]" value="<?php echo $newstyles['calPrev']['background-color']; ?>" onChange="javascript:document.getElementById('calPrev').style.backgroundColor=''+this.value+'';"><br />
			        Arrow Color: <input type="text" name="alien_event_new_styles[calPrev][color]" value="<?php echo $newstyles['calPrev']['color']; ?>" onChange="javascript:document.getElementById('calPrev').style.color=''+this.value+'';"><br />
				<input type="hidden" name="alien_event_new_styles[calPrev a][text-decoration]" value="none">
			        Width: <input type="text" name="alien_event_new_styles[calPrev][width]" value="<?php echo $newstyles['calPrev']['width']; ?>" onChange="javascript:document.getElementById('calPrev').style.width=''+this.value+'';"><br />
			        Height: <input type="text" name="alien_event_new_styles[calPrev][height]" value="<?php echo $newstyles['calPrev']['height']; ?>" onChange="javascript:document.getElementById('calPrev').style.height=''+this.value+'';"><br />
			Border: <select name="alien_event_new_styles[calPrev][border]" onChange="javascript:document.getElementById('calPrev').style.border=''+this.value+'';">
					<?php if ($newstyles['calPrev']['border']) { ?> <option value="<?php echo $newstyles['calPrev']['border']; ?>"><?php echo $newstyles['calPrev']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calPrev][border-style]" onChange="javascript:document.getElementById('calPrev').style.borderStyle=''+this.value+'';">
					<?php if ($newstyles['calPrev']['border-style']) { ?> <option value="<?php echo $newstyles['calPrev']['border-style']; ?>"><?php echo $newstyles['calPrev']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calPrev][border-color]" value="<?php echo $newstyles['calPrev']['border-color']; ?>" onChange="javascript:document.getElementById('calPrev').style.borderColor=''+this.value+'';">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Next Button</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calNext][text-align]" value="center">
				<input type="hidden" name="alien_event_new_styles[calNext][text-decoration]" value="none">
				<input type="hidden" name="alien_event_new_styles[calNext][float]" value="right">
				<input type="hidden" name="alien_event_new_styles[calNext][display]" value="block">
			        BG Image: <input type="text" name="alien_event_new_styles[calNext][background-image]" value="<?php echo $newstyles['calNext']['background-image']; ?>" onChange="javascript:get('calNext','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calNext][background-color]" value="<?php echo $newstyles['calNext']['background-color']; ?>" onChange="javascript:document.getElementById('calNext').style.backgroundColor=''+this.value+'';"><br />
			        Arrow Color: <input type="text" name="alien_event_new_styles[calNext][color]" value="<?php echo $newstyles['calNext']['color']; ?>" onChange="javascript:document.getElementById('calNext').style.color=''+this.value+'';"><br />
				<input type="hidden" name="alien_event_new_styles[calNext a][text-decoration]" value="none">
			        Width: <input type="text" name="alien_event_new_styles[calNext][width]" value="<?php echo $newstyles['calNext']['width']; ?>" onChange="javascript:document.getElementById('calNext').style.width=''+this.value+'';"><br />
			        Height: <input type="text" name="alien_event_new_styles[calNext][height]" value="<?php echo $newstyles['calNext']['height']; ?>" onChange="javascript:document.getElementById('calNext').style.height=''+this.value+'';"><br />
			Border: <select name="alien_event_new_styles[calNext][border]" onChange="javascript:document.getElementById('calNext').style.border=''+this.value+'';">
					<?php if ($newstyles['calNext']['border']) { ?> <option value="<?php echo $newstyles['calNext']['border']; ?>"><?php echo $newstyles['calNext']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calNext][border-style]" onChange="javascript:document.getElementById('calNext').style.borderStyle=''+this.value+'';">
					<?php if ($newstyles['calNext']['border-style']) { ?> <option value="<?php echo $newstyles['calNext']['border-style']; ?>"><?php echo $newstyles['calNext']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calNext][border-color]" value="<?php echo $newstyles['calNext']['border-color']; ?>" onChange="javascript:document.getElementById('calNext').style.borderColor=''+this.value+'';">
			</td>
		</tr>
	        <th scope="row">Calendar Rows:</th>
	        <td>
			<input type="hidden" name="alien_event_new_styles[tableTR][float]" value="left">
			<input type="hidden" name="alien_event_new_styles[tableTR][display]" value="block">
			<input type="hidden" name="alien_event_new_styles[tableTR][width]" value="100%">
			BG Image: <input type="text" name="alien_event_new_styles[tableTR][background-image]" value="<?php echo $newstyles['tableTR']['background-image']; ?>" onChange="javascript:get('tableTR','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			BG Color: <input type="text" name="alien_event_new_styles[tableTR][background-color]" value="<?php echo $newstyles['tableTR']['background-color']; ?>" onChange="changecss('tableTR','backgroundColor',''+this.value+'')"><br />
		        Height: <input type="text" name="alien_event_new_styles[tableTR][height]" value="<?php echo $newstyles['tableTR']['height']; ?>" onChange="changecss('tableTR','height',''+this.value+'')"><br />
			Border: <select name="alien_event_new_styles[tableTR][border]" onChange="javascript:changecss('tableTR','border',''+this.value+'');">
					<?php if ($newstyles['tableTR']['border']) { ?> <option value="<?php echo $newstyles['tableTR']['border']; ?>"><?php echo $newstyles['tableTR']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[tableTR][border-style]" onChange="changecss('tableTR','borderStyle',''+this.value+'')">
					<?php if ($newstyles['tableTR']['border-style']) { ?> <option value="<?php echo $newstyles['tableTR']['border-style']; ?>"><?php echo $newstyles['tableTR']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[tableTR][border-color]" value="<?php echo $newstyles['tableTR']['border-color']; ?>" onChange="changecss('tableTR','borderColor',''+this.value+'')">
		</td>
	        </tr>
		<tr valign="top">
			<th scope="row">Days of the Week</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calDW][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calDW][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calDW][text-align]" value="center">
			        BG Image: <input type="text" name="alien_event_new_styles[calDW][background-image]" value="<?php echo $newstyles['calDW']['background-image']; ?>" onChange="javascript:get('calDW','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calDW][background-color]" value="<?php echo $newstyles['calDW']['background-color']; ?>" onChange="changecss('calDW','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calDW][width]" value="<?php echo $newstyles['calDW']['width']; ?>" onChange="changecss('calDW','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calDW][height]" value="<?php echo $newstyles['calDW']['height']; ?>" onChange="changecss('calDW','height',''+this.value+'')"><br />
			        Day Color: <input type="text" name="alien_event_new_styles[calDW][color]" value="<?php echo $newstyles['calDW']['color']; ?>" onChange="changecss('calDW','color',''+this.value+'')"><br />
				Line Height: <select name="alien_event_new_styles[calDW][line-height]" onChange="changecss('calDW','lineHeight',''+this.value+'')">
					<?php if ($newstyles['calDW']['line-height']) { ?> <option value="<?php echo $newstyles['calDW']['line-height']; ?>"><?php echo $newstyles['calDW']['line-height']; ?></option> <?php } ?>
						<option value="50%">50%</option>
						<option value="100%">100%</option>
						<option value="150%">150%</option>
						<option value="200%">200%</option>
						<option value="250%">250%</option>
						<option value="300%">300%</option>
						<option value="350%">350%</option>
						<option value="400%">400%</option>
						<option value="450%">450%</option>
						<option value="500%">500%</option>
						</select><br />
			Border: <select name="alien_event_new_styles[calDW][border]" onChange="changecss('calDW','border',''+this.value+'')">
					<?php if ($newstyles['calDW']['border']) { ?> <option value="<?php echo $newstyles['calDW']['border']; ?>"><?php echo $newstyles['calDW']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calDW][border-style]" onChange="changecss('calDW','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calDW']['border-style']) { ?> <option value="<?php echo $newstyles['calDW']['border-style']; ?>"><?php echo $newstyles['calDW']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calDW][border-color]" value="<?php echo $newstyles['calDW']['border-color']; ?>" onChange="changecss('calDW','borderColor',''+this.value+'')">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Blank Day</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calBlank][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calBlank][display]" value="block">
			        BG Image: <input type="text" name="alien_event_new_styles[calBlank][background-image]" value="<?php echo $newstyles['calBlank']['background-image']; ?>" onChange="javascript:get('calBlank','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calBlank][background-color]" value="<?php echo $newstyles['calBlank']['background-color']; ?>" onChange="changecss('calBlank','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calBlank][width]" value="<?php echo $newstyles['calBlank']['width']; ?>" onChange="changecss('calBlank','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calBlank][height]" value="<?php echo $newstyles['calBlank']['height']; ?>" onChange="changecss('calBlank','height',''+this.value+'')"><br />
			Border: <select name="alien_event_new_styles[calBlank][border]" onChange="changecss('calBlank','border',''+this.value+'')">
					<?php if ($newstyles['calBlank']['border']) { ?> <option value="<?php echo $newstyles['calBlank']['border']; ?>"><?php echo $newstyles['calBlank']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calBlank][border-style]" onChange="changecss('calBlank','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calBlank']['border-style']) { ?> <option value="<?php echo $newstyles['calBlank']['border-style']; ?>"><?php echo $newstyles['calBlank']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calBlank][border-color]" value="<?php echo $newstyles['calBlank']['border-color']; ?>" onChange="changecss('calBlank','borderColor',''+this.value+'')">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Day with no events</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calDay][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calDay][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calDay][text-align]" value="center">
			        BG Image: <input type="text" name="alien_event_new_styles[calDay][background-image]" value="<?php echo $newstyles['calDay']['background-image']; ?>" onChange="javascript:get('calDay','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calDay][background-color]" value="<?php echo $newstyles['calDay']['background-color']; ?>" onChange="changecss('calDay','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calDay][width]" value="<?php echo $newstyles['calDay']['width']; ?>" onChange="changecss('calDay','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calDay][height]" value="<?php echo $newstyles['calDay']['height']; ?>" onChange="changecss('calDay','height',''+this.value+'')"><br />
			        Number Color: <input type="text" name="alien_event_new_styles[calDay][color]" value="<?php echo $newstyles['calDay']['color']; ?>" onChange="changecss('calDay','color',''+this.value+'')"><br />
				Line Height: <select name="alien_event_new_styles[calDay][line-height]" onChange="changecss('calDay','lineHeight',''+this.value+'')">
					<?php if ($newstyles['calDay']['line-height']) { ?> <option value="<?php echo $newstyles['calDay']['line-height']; ?>"><?php echo $newstyles['calDay']['line-height']; ?></option> <?php } ?>
						<option value="50%">50%</option>
						<option value="100%">100%</option>
						<option value="150%">150%</option>
						<option value="200%">200%</option>
						<option value="250%">250%</option>
						<option value="300%">300%</option>
						<option value="350%">350%</option>
						<option value="400%">400%</option>
						<option value="450%">450%</option>
						<option value="500%">500%</option>
						</select><br />
			Border: <select name="alien_event_new_styles[calDay][border]" onChange="changecss('calDay','border',''+this.value+'')">
					<?php if ($newstyles['calDay']['border']) { ?> <option value="<?php echo $newstyles['calDay']['border']; ?>"><?php echo $newstyles['calDay']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calDay][border-style]" onChange="changecss('calDay','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calDay']['border-style']) { ?> <option value="<?php echo $newstyles['calDay']['border-style']; ?>"><?php echo $newstyles['calDay']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calDay][border-color]" value="<?php echo $newstyles['calDay']['border-color']; ?>" onChange="changecss('calDay','borderColor',''+this.value+'')">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Day with events</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calActive][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calActive][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calActive a][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calActive][text-align]" value="center">
			        BG Image: <input type="text" name="alien_event_new_styles[calActive][background-image]" value="<?php echo $newstyles['calActive']['background-image']; ?>" onChange="javascript:get('calActive','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG: <input type="text" name="alien_event_new_styles[calActive][background-color]" value="<?php echo $newstyles['calActive']['background-color']; ?>" onChange="changecss('calActive','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calActive][width]" value="<?php echo $newstyles['calActive']['width']; ?>" onChange="changecss('calActive','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calActive][height]" value="<?php echo $newstyles['calActive']['height']; ?>" onChange="changecss('calActive','height',''+this.value+'')"><br />
			        Number Color: <input type="text" name="alien_event_new_styles[calActive a][color]" value="<?php echo $newstyles['calActive a']['color']; ?>" onChange="changecss('calActive a','color',''+this.value+'')"><br />
				Line Height: <select name="alien_event_new_styles[calActive][line-height]" onChange="changecss('calActive','lineHeight',''+this.value+'')">
					<?php if ($newstyles['calActive']['line-height']) { ?> <option value="<?php echo $newstyles['calActive']['line-height']; ?>"><?php echo $newstyles['calActive']['line-height']; ?></option> <?php } ?>
						<option value="50%">50%</option>
						<option value="100%">100%</option>
						<option value="150%">150%</option>
						<option value="200%">200%</option>
						<option value="250%">250%</option>
						<option value="300%">300%</option>
						<option value="350%">350%</option>
						<option value="400%">400%</option>
						<option value="450%">450%</option>
						<option value="500%">500%</option>
						</select><br />
			Border: <select name="alien_event_new_styles[calActive][border]" onChange="changecss('calActive','border',''+this.value+'')">
					<?php if ($newstyles['calActive']['border']) { ?> <option value="<?php echo $newstyles['calActive']['border']; ?>"><?php echo $newstyles['calActive']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calActive][border-style]" onChange="changecss('calActive','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calActive']['border-style']) { ?> <option value="<?php echo $newstyles['calActive']['border-style']; ?>"><?php echo $newstyles['calActive']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calActive][border-color]" value="<?php echo $newstyles['calActive']['border-color']; ?>" onChange="changecss('calActive','borderColor',''+this.value+'')">
			        <input type="hidden" name="alien_event_new_styles[calActive a][text-decoration]" value="none">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Current Day with no events</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calToday][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calToday][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calToday][text-align]" value="center">
			        BG Image: <input type="text" name="alien_event_new_styles[calToday][background-image]" value="<?php echo $newstyles['calToday']['background-image']; ?>" onChange="javascript:get('calToday','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calToday][background-color]" value="<?php echo $newstyles['calToday']['background-color']; ?>" onChange="changecss('calToday','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calToday][width]" value="<?php echo $newstyles['calToday']['width']; ?>" onChange="changecss('calToday','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calToday][height]" value="<?php echo $newstyles['calToday']['height']; ?>" onChange="changecss('calToday','height',''+this.value+'')"><br />
			        Number Color: <input type="text" name="alien_event_new_styles[calToday][color]" value="<?php echo $newstyles['calToday']['color']; ?>" onChange="changecss('calToday','color',''+this.value+'')"><br />
				Line Height: <select name="alien_event_new_styles[calToday][line-height]" onChange="changecss('calToday','lineHeight',''+this.value+'')">
					<?php if ($newstyles['calToday']['line-height']) { ?> <option value="<?php echo $newstyles['calToday']['line-height']; ?>"><?php echo $newstyles['calToday']['line-height']; ?></option> <?php } ?>
						<option value="50%">50%</option>
						<option value="100%">100%</option>
						<option value="150%">150%</option>
						<option value="200%">200%</option>
						<option value="250%">250%</option>
						<option value="300%">300%</option>
						<option value="350%">350%</option>
						<option value="400%">400%</option>
						<option value="450%">450%</option>
						<option value="500%">500%</option>
						</select><br />
			Border: <select name="alien_event_new_styles[calToday][border]" onChange="changecss('calToday','border',''+this.value+'')">
					<?php if ($newstyles['calToday']['border']) { ?> <option value="<?php echo $newstyles['calToday']['border']; ?>"><?php echo $newstyles['calToday']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calToday][border-style]" onChange="changecss('calToday','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calToday']['border-style']) { ?> <option value="<?php echo $newstyles['calToday']['border-style']; ?>"><?php echo $newstyles['calToday']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calToday][border-color]" value="<?php echo $newstyles['calToday']['border-color']; ?>" onChange="changecss('calToday','borderColor',''+this.value+'')">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Current Day with events</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calTActive][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calTActive][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calTActive a][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calTActive][text-align]" value="center">
			        BG Image: <input type="text" name="alien_event_new_styles[calTActive][background-image]" value="<?php echo $newstyles['calTActive']['background-image']; ?>" onChange="javascript:get('calTActive','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calTActive][background-color]" value="<?php echo $newstyles['calTActive']['background-color']; ?>" onChange="changecss('calTActive','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calTActive][width]" value="<?php echo $newstyles['calTActive']['width']; ?>" onChange="changecss('calTActive','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calTActive][height]" value="<?php echo $newstyles['calTActive']['height']; ?>" onChange="changecss('calTActive','height',''+this.value+'')"><br />
			        Number Color: <input type="text" name="alien_event_new_styles[calTActive a][color]" value="<?php echo $newstyles['calTActive a']['color']; ?>" onChange="changecss('calTActive a','color',''+this.value+'')"><br />
				<input type="hidden" name="alien_event_new_styles[calTActive a][text-decoration]" value="none">
				Line Height: <select name="alien_event_new_styles[calTActive][line-height]" onChange="changecss('calTActive','lineHeight',''+this.value+'')">
					<?php if ($newstyles['calTActive']['line-height']) { ?> <option value="<?php echo $newstyles['calTActive']['line-height']; ?>"><?php echo $newstyles['calTActive']['line-height']; ?></option> <?php } ?>
						<option value="50%">50%</option>
						<option value="100%">100%</option>
						<option value="150%">150%</option>
						<option value="200%">200%</option>
						<option value="250%">250%</option>
						<option value="300%">300%</option>
						<option value="350%">350%</option>
						<option value="400%">400%</option>
						<option value="450%">450%</option>
						<option value="500%">500%</option>
						</select><br />
			Border: <select name="alien_event_new_styles[calTActive][border]" onChange="changecss('calTActive','border',''+this.value+'')">
					<?php if ($newstyles['calTActive']['border']) { ?> <option value="<?php echo $newstyles['calTActive']['border']; ?>"><?php echo $newstyles['calTActive']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calTActive][border-style]" onChange="changecss('calTActive','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calTActive']['border-style']) { ?> <option value="<?php echo $newstyles['calTActive']['border-style']; ?>"><?php echo $newstyles['calTActive']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calTActive][border-color]" value="<?php echo $newstyles['calTActive']['border-color']; ?>" onChange="changecss('calTActive','borderColor',''+this.value+'')">
			        <input type="hidden" name="alien_event_new_styles[calTActive a][text-decoration]" value="none">
			</td>
		</tr>
	    </table>
</div>
<!-- End calwidform -->
<!-- Start fullcalform -->
<div id="fullcalform" style="display:none">
	<table class="widefat page">
		<thead>
	        <tr valign="top">
	        <th colspan="3">Main Wrapper<br><a href="javascript:togglePreview('fcWindow')">Show/Hide Live Preview</a>
		<input type="hidden" name="alien_event_new_styles[calFC][display]" value="block">
		<input type="hidden" name="alien_event_new_styles[calFC][overflow]" value="hidden">
		</th>
	        </tr>
		</thead>
	        <tr valign="top">
	        <th scope="row">Appearance</th>
		<td>
			BG Image: <input type="text" name="alien_event_new_styles[calFC][background-image]" value="<?php echo $newstyles['calFC']['background-image']; ?>" onChange="javascript:get('calFC','backgroundImage',''+this.value+'');"> <small>use full url</small><br />
	  		Width: <input type="text" name="alien_event_new_styles[calFC][width]" value="<?php echo $newstyles['calFC']['width']; ?>" onChange="javascript:document.getElementById('calFC').style.width=''+this.value+'';"> <small>in pixels, ie... 200px</small><br />
			BG Color: <input type="text" name="alien_event_new_styles[calFC][background-color]" value="<?php echo $newstyles['calFC']['background-color']; ?>" onChange="javascript:document.getElementById('calFC').style.backgroundColor=''+this.value+'';">
		</td>
	        </tr>
		<tr valign="top">
	        <th scope="row">Mouseover Popup
		<input type="hidden" name="alien_event_new_styles[popupFC][display]" value="none">
		<input type="hidden" name="alien_event_new_styles[popupFC][position]" value="absolute">
		<input type="hidden" name="alien_event_new_styles[popupFC][z-index]" value="100">
		</th>
		<td>
			BG Image: <input type="text" name="alien_event_new_styles[popupFC][background-image]" value="<?php echo $newstyles['popupFC']['background-image']; ?>" onChange="javascript:get('popupFC','backgroundImage',''+this.value+'');"> <small>use full url</small><br />
			BG Color: <input type="text" name="alien_event_new_styles[popupFC][background-color]" value="<?php echo $newstyles['popupFC']['background-color']; ?>" onChange="changecss('popupFC','backgroundColor',''+this.value+'')"><br />
			Text Color: <input type="text" name="alien_event_new_styles[popupFC][color]" value="<?php echo $newstyles['popupFC']['color']; ?>" onChange="changecss('popupFC','color',''+this.value+'')"><br />
			Border: <select name="alien_event_new_styles[popupFC][border]" onChange="changecss('popupFC','border',''+this.value+'')">
					<?php if ($newstyles['popupFC']['border']) { ?> <option value="<?php echo $newstyles['popupFC']['border']; ?>"><?php echo $newstyles['popupFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[popupFC][border-style]" onChange="changecss('popupFC','borderStyle',''+this.value+'')">
					<?php if ($newstyles['popupFC']['border-style']) { ?> <option value="<?php echo $newstyles['popupFC']['border-style']; ?>"><?php echo $newstyles['popupFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[popupFC][border-color]" value="<?php echo $newstyles['popupFC']['border-color']; ?>" onChange="changecss('popupFC','borderColor',''+this.value+'')">
			</td>
	        </tr>
	    </table>
	<table class="widefat page">
		<thead>
	        <tr valign="top">
	        <th colspan="3">Calendar Wrapper<br /><small>If you change the border size, you must change the style and color for it to show in the mini calendar</small>
		<input type="hidden" name="alien_event_new_styles[calTableFC][display]" value="block">
		<input type="hidden" name="alien_event_new_styles[calTableFC][overflow]" value="hidden">
		<input type="hidden" name="alien_event_new_styles[calTableFC][width]" value="100%">
		<input type="hidden" name="alien_event_new_styles[calTHFC][width]" value="100%">
		</th>
	        </tr>
		</thead>
	        <tr valign="top">
	        <th scope="row">Outer Table</th>
			<td>
			BG Image: <input type="text" name="alien_event_new_styles[calTableFC][background-image]" value="<?php echo $newstyles['calTableFC']['background-image']; ?>" onChange="javascript:get('calTableFC','backgroundImage',''+this.value+'');"> <small>use full url</small><br />
			BG Color: <input type="text" name="alien_event_new_styles[calTableFC][background-color]" value="<?php echo $newstyles['calTableFC']['background-color']; ?>" onChange="javascript:document.getElementById('calTableFC').style.backgroundColor=this.value;"><br />
			Border: <select name="alien_event_new_styles[calTableFC][border]" onChange="javascript:document.getElementById('calTableFC').style.border=this.value;">
					<?php if ($newstyles['calTableFC']['border']) { ?> <option value="<?php echo $newstyles['calTableFC']['border']; ?>"><?php echo $newstyles['calTableFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calTableFC][border-style]"  onChange="javascript:document.getElementById('calTableFC').style.borderStyle=this.value;">
					<?php if ($newstyles['calTableFC']['border-style']) { ?> <option value="<?php echo $newstyles['calTableFC']['border-style']; ?>"><?php echo $newstyles['calTableFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calTableFC][border-color]" value="<?php echo $newstyles['calTableFC']['border-color']; ?>" onChange="javascript:document.getElementById('calTableFC').style.borderColor=this.value;">
			</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row">Month Header:</th>
	        <td>
			<input type="hidden" name="alien_event_new_styles[calTHFC][text-align]" value="center">
			BG Image: <input type="text" name="alien_event_new_styles[calTHFC][background-image]" value="<?php echo $newstyles['calTHFC']['background-image']; ?>" onChange="javascript:get('calTHFC','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			BG Color: <input type="text" name="alien_event_new_styles[calTHFC][background-color]" value="<?php echo $newstyles['calTHFC']['background-color']; ?>" onChange="javascript:document.getElementById('calTHFC').style.backgroundColor=''+this.value+'';"><br />
		        Height: <input type="text" name="alien_event_new_styles[calTHFC][height]" value="<?php echo $newstyles['calTHFC']['height']; ?>" onChange="javascript:document.getElementById('calTHFC').style.height=''+this.value+'';"><br />
			Border: <select name="alien_event_new_styles[calTHFC][border]" onChange="javascript:document.getElementById('calTHFC').style.border=''+this.value+'';">
					<?php if ($newstyles['calTHFC']['border']) { ?> <option value="<?php echo $newstyles['calTHFC']['border']; ?>"><?php echo $newstyles['calTHFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calTHFC][border-style]" onChange="javascript:document.getElementById('calTHFC').style.borderStyle=''+this.value+'';">
					<?php if ($newstyles['calTHFC']['border-style']) { ?> <option value="<?php echo $newstyles['calTHFC']['border-style']; ?>"><?php echo $newstyles['calTHFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calTHFC][border-color]" value="<?php echo $newstyles['calTHFC']['border-color']; ?>" onChange="javascript:document.getElementById('calTHFC').style.borderColor=''+this.value+'';">
		</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row">Month Text:</th>
	        <td>
			Color: <input type="text" name="alien_event_new_styles[calMonthFC][color]" value="<?php echo $newstyles['calMonthFC']['color']; ?>" onChange="javascript:document.getElementById('calMonthFC').style.color=''+this.value+'';"> 
			Weight: <select name="alien_event_new_styles[calMonthFC][font-weight]"  onChange="javascript:document.getElementById('calMonthFC').style.fontWeight=''+this.value+'';">
				<?php if ($newstyles['calMonthFC']['font-weight']) { ?>  <option value="<?php echo $newstyles['calMonthFC']['font-weight']; ?>"><?php echo $newstyles['calMonthFC']['font-weight']; ?></option>  <?php } ?>
					<option value="normal">normal</option>
					<option value="bold">bold</option>
				</select>
		</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row">Year Text:</th>
	        <td>
			Color: <input type="text" name="alien_event_new_styles[calYearFC][color]" value="<?php echo $newstyles['calYearFC']['color']; ?>" onChange="javascript:document.getElementById('calYearFC').style.color=''+this.value+'';"> 
			Weight: <select name="alien_event_new_styles[calYearFC][font-weight]" onChange="javascript:document.getElementById('calYearFC').style.fontWeight=''+this.value+'';">
				<?php if ($newstyles['calYearFC']['font-weight']) { ?>  <option value="<?php echo $newstyles['calYearFC']['font-weight']; ?>"><?php echo $newstyles['calYearFC']['font-weight']; ?></option>  <?php } ?>
					<option value="normal">normal</option>
					<option value="bold">bold</option>
				</select>
		</td>
	        </tr>
		<tr valign="top">
			<th scope="row">Previous Button</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calPrevFC][text-align]" value="center">
				<input type="hidden" name="alien_event_new_styles[calPrevFC][text-decoration]" value="none">
				<input type="hidden" name="alien_event_new_styles[calPrevFC][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calPrevFC][display]" value="block">
			        BG Image: <input type="text" name="alien_event_new_styles[calPrevFC][background-image]" value="<?php echo $newstyles['calPrevFC']['background-image']; ?>" onChange="javascript:get('calPrevFC','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calPrevFC][background-color]" value="<?php echo $newstyles['calPrevFC']['background-color']; ?>" onChange="javascript:document.getElementById('calPrevFC').style.backgroundColor=''+this.value+'';"><br />
			        Arrow Color: <input type="text" name="alien_event_new_styles[calPrevFC][color]" value="<?php echo $newstyles['calPrevFC']['color']; ?>" onChange="javascript:document.getElementById('calPrevFC').style.color=''+this.value+'';"><br />
				<input type="hidden" name="alien_event_new_styles[calPrevFC a][text-decoration]" value="none">
			        Width: <input type="text" name="alien_event_new_styles[calPrevFC][width]" value="<?php echo $newstyles['calPrevFC']['width']; ?>" onChange="javascript:document.getElementById('calPrevFC').style.width=''+this.value+'';"><br />
			        Height: <input type="text" name="alien_event_new_styles[calPrevFC][height]" value="<?php echo $newstyles['calPrevFC']['height']; ?>" onChange="javascript:document.getElementById('calPrevFC').style.height=''+this.value+'';"><br />
			Border: <select name="alien_event_new_styles[calPrevFC][border]" onChange="javascript:document.getElementById('calPrevFC').style.border=''+this.value+'';">
					<?php if ($newstyles['calPrevFC']['border']) { ?> <option value="<?php echo $newstyles['calPrevFC']['border']; ?>"><?php echo $newstyles['calPrevFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calPrevFC][border-style]" onChange="javascript:document.getElementById('calPrevFC').style.borderStyle=''+this.value+'';">
					<?php if ($newstyles['calPrevFC']['border-style']) { ?> <option value="<?php echo $newstyles['calPrevFC']['border-style']; ?>"><?php echo $newstyles['calPrevFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calPrevFC][border-color]" value="<?php echo $newstyles['calPrevFC']['border-color']; ?>" onChange="javascript:document.getElementById('calPrevFC').style.borderColor=''+this.value+'';">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Next Button</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calNextFC][text-align]" value="center">
				<input type="hidden" name="alien_event_new_styles[calNextFC][text-decoration]" value="none">
				<input type="hidden" name="alien_event_new_styles[calNextFC][float]" value="right">
				<input type="hidden" name="alien_event_new_styles[calNextFC][display]" value="block">
			        BG Image: <input type="text" name="alien_event_new_styles[calNextFC][background-image]" value="<?php echo $newstyles['calNextFC']['background-image']; ?>" onChange="javascript:get('calNextFC','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calNextFC][background-color]" value="<?php echo $newstyles['calNextFC']['background-color']; ?>" onChange="javascript:document.getElementById('calNextFC').style.backgroundColor=''+this.value+'';"><br />
			        Arrow Color: <input type="text" name="alien_event_new_styles[calNextFC][color]" value="<?php echo $newstyles['calNextFC']['color']; ?>" onChange="javascript:document.getElementById('calNextFC').style.color=''+this.value+'';"><br />
				<input type="hidden" name="alien_event_new_styles[calNextFC a][text-decoration]" value="none">
			        Width: <input type="text" name="alien_event_new_styles[calNextFC][width]" value="<?php echo $newstyles['calNextFC']['width']; ?>" onChange="javascript:document.getElementById('calNextFC').style.width=''+this.value+'';"><br />
			        Height: <input type="text" name="alien_event_new_styles[calNextFC][height]" value="<?php echo $newstyles['calNextFC']['height']; ?>" onChange="javascript:document.getElementById('calNextFC').style.height=''+this.value+'';"><br />
			Border: <select name="alien_event_new_styles[calNextFC][border]" onChange="javascript:document.getElementById('calNextFC').style.border=''+this.value+'';">
					<?php if ($newstyles['calNextFC']['border']) { ?> <option value="<?php echo $newstyles['calNextFC']['border']; ?>"><?php echo $newstyles['calNextFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calNextFC][border-style]" onChange="javascript:document.getElementById('calNextFC').style.borderStyle=''+this.value+'';">
					<?php if ($newstyles['calNextFC']['border-style']) { ?> <option value="<?php echo $newstyles['calNextFC']['border-style']; ?>"><?php echo $newstyles['calNextFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calNextFC][border-color]" value="<?php echo $newstyles['calNextFC']['border-color']; ?>" onChange="javascript:document.getElementById('calNextFC').style.borderColor=''+this.value+'';">
			</td>
		</tr>
	        <th scope="row">Calendar Rows:</th>
	        <td>
			<input type="hidden" name="alien_event_new_styles[tableTRFC][float]" value="left">
			<input type="hidden" name="alien_event_new_styles[tableTRFC][display]" value="block">
			<input type="hidden" name="alien_event_new_styles[tableTRFC][width]" value="100%">
			BG Image: <input type="text" name="alien_event_new_styles[tableTRFC][background-image]" value="<?php echo $newstyles['tableTRFC']['background-image']; ?>" onChange="javascript:get('tableTRFC','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			BG Color: <input type="text" name="alien_event_new_styles[tableTRFC][background-color]" value="<?php echo $newstyles['tableTRFC']['background-color']; ?>" onChange="changecss('tableTRFC','backgroundColor',''+this.value+'')"><br />
		        Height: <input type="text" name="alien_event_new_styles[tableTRFC][height]" value="<?php echo $newstyles['tableTRFC']['height']; ?>" onChange="changecss('tableTRFC','height',''+this.value+'')"><br />
			Border: <select name="alien_event_new_styles[tableTRFC][border]" onChange="javascript:changecss('tableTRFC','border',''+this.value+'');">
					<?php if ($newstyles['tableTRFC']['border']) { ?> <option value="<?php echo $newstyles['tableTRFC']['border']; ?>"><?php echo $newstyles['tableTRFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[tableTRFC][border-style]" onChange="changecss('tableTRFC','borderStyle',''+this.value+'')">
					<?php if ($newstyles['tableTRFC']['border-style']) { ?> <option value="<?php echo $newstyles['tableTRFC']['border-style']; ?>"><?php echo $newstyles['tableTRFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[tableTRFC][border-color]" value="<?php echo $newstyles['tableTRFC']['border-color']; ?>" onChange="changecss('tableTRFC','borderColor',''+this.value+'')">
		</td>
	        </tr>
		<tr valign="top">
			<th scope="row">Days of the Week</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calDWFC][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calDWFC][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calDWFC][text-align]" value="center">
			        BG Image: <input type="text" name="alien_event_new_styles[calDWFC][background-image]" value="<?php echo $newstyles['calDWFC']['background-image']; ?>" onChange="javascript:get('calDWFC','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calDWFC][background-color]" value="<?php echo $newstyles['calDWFC']['background-color']; ?>" onChange="changecss('calDWFC','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calDWFC][width]" value="<?php echo $newstyles['calDWFC']['width']; ?>" onChange="changecss('calDWFC','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calDWFC][height]" value="<?php echo $newstyles['calDWFC']['height']; ?>" onChange="changecss('calDWFC','height',''+this.value+'')"><br />
			        Day Color: <input type="text" name="alien_event_new_styles[calDWFC][color]" value="<?php echo $newstyles['calDWFC']['color']; ?>" onChange="changecss('calDWFC','color',''+this.value+'')"><br />
				Line Height: <select name="alien_event_new_styles[calDWFC][line-height]" onChange="changecss('calDWFC','lineHeight',''+this.value+'')">
					<?php if ($newstyles['calDWFC']['line-height']) { ?> <option value="<?php echo $newstyles['calDWFC']['line-height']; ?>"><?php echo $newstyles['calDWFC']['line-height']; ?></option> <?php } ?>
						<option value="50%">50%</option>
						<option value="100%">100%</option>
						<option value="150%">150%</option>
						<option value="200%">200%</option>
						<option value="250%">250%</option>
						<option value="300%">300%</option>
						<option value="350%">350%</option>
						<option value="400%">400%</option>
						<option value="450%">450%</option>
						<option value="500%">500%</option>
						</select><br />
			Border: <select name="alien_event_new_styles[calDWFC][border]" onChange="changecss('calDWFC','border',''+this.value+'')">
					<?php if ($newstyles['calDWFC']['border']) { ?> <option value="<?php echo $newstyles['calDWFC']['border']; ?>"><?php echo $newstyles['calDWFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calDWFC][border-style]" onChange="changecss('calDWFC','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calDWFC']['border-style']) { ?> <option value="<?php echo $newstyles['calDWFC']['border-style']; ?>"><?php echo $newstyles['calDWFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calDWFC][border-color]" value="<?php echo $newstyles['calDWFC']['border-color']; ?>" onChange="changecss('calDWFC','borderColor',''+this.value+'')">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Blank Day</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calBlankFC][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calBlankFC][display]" value="block">
			        BG Image: <input type="text" name="alien_event_new_styles[calBlankFC][background-image]" value="<?php echo $newstyles['calBlankFC']['background-image']; ?>" onChange="javascript:get('calBlankFC','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calBlankFC][background-color]" value="<?php echo $newstyles['calBlankFC']['background-color']; ?>" onChange="changecss('calBlankFC','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calBlankFC][width]" value="<?php echo $newstyles['calBlankFC']['width']; ?>" onChange="changecss('calBlankFC','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calBlankFC][height]" value="<?php echo $newstyles['calBlankFC']['height']; ?>" onChange="changecss('calBlankFC','height',''+this.value+'')"><br />
			Border: <select name="alien_event_new_styles[calBlankFC][border]" onChange="changecss('calBlankFC','border',''+this.value+'')">
					<?php if ($newstyles['calBlankFC']['border']) { ?> <option value="<?php echo $newstyles['calBlankFC']['border']; ?>"><?php echo $newstyles['calBlankFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calBlankFC][border-style]" onChange="changecss('calBlankFC','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calBlankFC']['border-style']) { ?> <option value="<?php echo $newstyles['calBlankFC']['border-style']; ?>"><?php echo $newstyles['calBlankFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calBlankFC][border-color]" value="<?php echo $newstyles['calBlankFC']['border-color']; ?>" onChange="changecss('calBlankFC','borderColor',''+this.value+'')">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Day with no events</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calDayFC][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calDayFC][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calDayFC][text-align]" value="left">
			        BG Image: <input type="text" name="alien_event_new_styles[calDayFC][background-image]" value="<?php echo $newstyles['calDayFC']['background-image']; ?>" onChange="javascript:get('calDayFC','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calDayFC][background-color]" value="<?php echo $newstyles['calDayFC']['background-color']; ?>" onChange="changecss('calDayFC','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calDayFC][width]" value="<?php echo $newstyles['calDayFC']['width']; ?>" onChange="changecss('calDayFC','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calDayFC][height]" value="<?php echo $newstyles['calDayFC']['height']; ?>" onChange="changecss('calDayFC','height',''+this.value+'')"><br />
			        Number Color: <input type="text" name="alien_event_new_styles[calDayFC][color]" value="<?php echo $newstyles['calDayFC']['color']; ?>" onChange="changecss('calDayFC','color',''+this.value+'')"><br />
				<input type="hidden" name="alien_event_new_styles[calDayFC][line-height]" value="100%">
			Border: <select name="alien_event_new_styles[calDayFC][border]" onChange="changecss('calDayFC','border',''+this.value+'')">
					<?php if ($newstyles['calDayFC']['border']) { ?> <option value="<?php echo $newstyles['calDayFC']['border']; ?>"><?php echo $newstyles['calDayFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calDayFC][border-style]" onChange="changecss('calDayFC','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calDayFC']['border-style']) { ?> <option value="<?php echo $newstyles['calDayFC']['border-style']; ?>"><?php echo $newstyles['calDayFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calDayFC][border-color]" value="<?php echo $newstyles['calDayFC']['border-color']; ?>" onChange="changecss('calDayFC','borderColor',''+this.value+'')">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Day with events</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calActiveFC][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calActiveFC][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calActiveFC][text-align]" value="left">
			        BG Image: <input type="text" name="alien_event_new_styles[calActiveFC][background-image]" value="<?php echo $newstyles['calActiveFC']['background-image']; ?>" onChange="javascript:get('calActiveFC','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG: <input type="text" name="alien_event_new_styles[calActiveFC][background-color]" value="<?php echo $newstyles['calActiveFC']['background-color']; ?>" onChange="changecss('calActiveFC','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calActiveFC][width]" value="<?php echo $newstyles['calActiveFC']['width']; ?>" onChange="changecss('calActiveFC','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calActiveFC][height]" value="<?php echo $newstyles['calActiveFC']['height']; ?>" onChange="changecss('calActiveFC','height',''+this.value+'')"><br />
			        Number Color: <input type="text" name="alien_event_new_styles[calActiveFC a][color]" value="<?php echo $newstyles['calActiveFC a']['color']; ?>" onChange="changecss('calActiveFC a','color',''+this.value+'')"><br />
				<input type="hidden" name="alien_event_new_styles[calActiveFC][line-height]" value="100%">
			Border: <select name="alien_event_new_styles[calActiveFC][border]" onChange="changecss('calActiveFC','border',''+this.value+'')">
					<?php if ($newstyles['calActiveFC']['border']) { ?> <option value="<?php echo $newstyles['calActiveFC']['border']; ?>"><?php echo $newstyles['calActiveFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calActiveFC][border-style]" onChange="changecss('calActiveFC','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calActiveFC']['border-style']) { ?> <option value="<?php echo $newstyles['calActiveFC']['border-style']; ?>"><?php echo $newstyles['calActiveFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calActiveFC][border-color]" value="<?php echo $newstyles['calActiveFC']['border-color']; ?>" onChange="changecss('calActiveFC','borderColor',''+this.value+'')">
			        <input type="hidden" name="alien_event_new_styles[calActiveFC a][text-decoration]" value="none">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Current Day with no events</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calTodayFC][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calTodayFC][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calTodayFC][text-align]" value="left">
			        BG Image: <input type="text" name="alien_event_new_styles[calTodayFC][background-image]" value="<?php echo $newstyles['calTodayFC']['background-image']; ?>" onChange="javascript:get('calTodayFC','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calTodayFC][background-color]" value="<?php echo $newstyles['calTodayFC']['background-color']; ?>" onChange="changecss('calTodayFC','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calTodayFC][width]" value="<?php echo $newstyles['calTodayFC']['width']; ?>" onChange="changecss('calTodayFC','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calTodayFC][height]" value="<?php echo $newstyles['calTodayFC']['height']; ?>" onChange="changecss('calTodayFC','height',''+this.value+'')"><br />
			        Number Color: <input type="text" name="alien_event_new_styles[calTodayFC][color]" value="<?php echo $newstyles['calTodayFC']['color']; ?>" onChange="changecss('calTodayFC','color',''+this.value+'')"><br />
				<input type="hidden" name="alien_event_new_styles[calTodayFC][line-height]" value="100%">
			Border: <select name="alien_event_new_styles[calTodayFC][border]" onChange="changecss('calTodayFC','border',''+this.value+'')">
					<?php if ($newstyles['calTodayFC']['border']) { ?> <option value="<?php echo $newstyles['calTodayFC']['border']; ?>"><?php echo $newstyles['calTodayFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calTodayFC][border-style]" onChange="changecss('calTodayFC','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calTodayFC']['border-style']) { ?> <option value="<?php echo $newstyles['calTodayFC']['border-style']; ?>"><?php echo $newstyles['calTodayFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calTodayFC][border-color]" value="<?php echo $newstyles['calTodayFC']['border-color']; ?>" onChange="changecss('calTodayFC','borderColor',''+this.value+'')">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Current Day with events</th>
			<td>
				<input type="hidden" name="alien_event_new_styles[calTActiveFC][float]" value="left">
				<input type="hidden" name="alien_event_new_styles[calTActiveFC][display]" value="block">
				<input type="hidden" name="alien_event_new_styles[calTActiveFC][text-align]" value="left">
			        BG Image: <input type="text" name="alien_event_new_styles[calTActiveFC][background-image]" value="<?php echo $newstyles['calTActiveFC']['background-image']; ?>" onChange="javascript:get('calTActiveFC','backgroundImage',''+this.value+'');"> <small>in pixels, ie... 200px</small><br />
			        BG Color: <input type="text" name="alien_event_new_styles[calTActiveFC][background-color]" value="<?php echo $newstyles['calTActiveFC']['background-color']; ?>" onChange="changecss('calTActiveFC','backgroundColor',''+this.value+'')"><br />
			        Width: <input type="text" name="alien_event_new_styles[calTActiveFC][width]" value="<?php echo $newstyles['calTActiveFC']['width']; ?>" onChange="changecss('calTActiveFC','width',''+this.value+'')"><br />
			        Height: <input type="text" name="alien_event_new_styles[calTActiveFC][height]" value="<?php echo $newstyles['calTActiveFC']['height']; ?>" onChange="changecss('calTActiveFC','height',''+this.value+'')"><br />
			        Number Color: <input type="text" name="alien_event_new_styles[calTActiveFC a][color]" value="<?php echo $newstyles['calTActiveFC a']['color']; ?>" onChange="changecss('calTActiveFC a','color',''+this.value+'')"><br />
				<input type="hidden" name="alien_event_new_styles[calTActiveFC a][text-decoration]" value="none">
				<input type="hidden" name="alien_event_new_styles[calTActiveFC][line-height]" value="100%">
			Border: <select name="alien_event_new_styles[calTActiveFC][border]" onChange="changecss('calTActiveFC','border',''+this.value+'')">
					<?php if ($newstyles['calTActiveFC']['border']) { ?> <option value="<?php echo $newstyles['calTActiveFC']['border']; ?>"><?php echo $newstyles['calTActiveFC']['border']; ?></option> <?php } ?>
					<option value="0px">0px</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
				</select><br />
			Border Style: <select name="alien_event_new_styles[calTActiveFC][border-style]" onChange="changecss('calTActiveFC','borderStyle',''+this.value+'')">
					<?php if ($newstyles['calTActiveFC']['border-style']) { ?> <option value="<?php echo $newstyles['calTActiveFC']['border-style']; ?>"><?php echo $newstyles['calTActiveFC']['border-style']; ?></option> <?php } ?>
					<option value="none">none</option>
					<option value="hidden">hidden</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select><br />
			Border Color: <input type="text" name="alien_event_new_styles[calTActiveFC][border-color]" value="<?php echo $newstyles['calTActiveFC']['border-color']; ?>" onChange="changecss('calTActiveFC','borderColor',''+this.value+'')">
			        <input type="hidden" name="alien_event_new_styles[calTActiveFC a][text-decoration]" value="none">
			</td>
		</tr>
	    </table>
</div>
<!-- End fullcalform -->




</div>
<!-- End Picker Div -->

	<?php 
	if(get_option('alien_event_new_layout') == "picker") {
		?><div id="CSStable" style="display:none"><?php
	}
	else {
		?><div id="CSStable" style="display:block"><?php
	}
	?>
	<table class="widefat page">
		<thead>
	        <tr valign="top">
	        <th colspan="2">Customize CSS for the calendar widget, full calendar and the event pages.</th>
	        </tr>
		</thead>
	        <tr valign="top">
	        <th scope="row">Calendar Styles</th>
	        <td><textarea name="alien_cal_style" cols="60" rows="15"><?php echo get_option('alien_cal_style'); ?></textarea></td>
	        </tr>
	        <tr valign="top">
	        <th scope="row">Events Page Styles</th>
	        <td><textarea name="alien_event_style" cols="60" rows="15"><?php echo get_option('alien_event_style'); ?></textarea></td>
	        </tr>
	    </table>
	</div>
</div>    
<div id="Display" style="display:none">
    <table class="widefat page">
	<thead>
        <tr valign="top">
        <th colspan="2">Default Display Options</th>
        </tr>
	</thead>
        <tr valign="top">
	<th scope="row">Display on event page:</th>
        <td>
		Event List <input type="radio" name="alien_event_display" value="list" <?php if (get_option('alien_event_display') == "list") { echo 'checked="checked"'; } ?> /> 
		Full Calendar <input type="radio" name="alien_event_display" value="full" <?php if (get_option('alien_event_display') == "full") { echo 'checked="checked"'; } ?> />
	</td>
        </tr>
        <tr valign="top">
        <th scope="row"># Events per Full Cal Day</th>
        <td><input type="text" name="alien_fc_per" value="<?php echo get_option('alien_fc_per'); ?>" /></td>
        </tr>
        <tr valign="top">
	<th scope="row">Popup Field:</th>
        <td>
		Title <input type="radio" name="alien_event_popup_field" value="title" <?php if (get_option('alien_event_popup_field') == "title") { echo 'checked="checked"'; } ?> /> 
		Description <input type="radio" name="alien_event_popup_field" value="description" <?php if (get_option('alien_event_popup_field') == "description") { echo 'checked="checked"'; } ?> />
	</td>
        </tr>
        <tr valign="top">
	<th scope="row">Window Target for FC event links:</th>
        <td>
		<select name="alien_cal_linktar">
		<option value="_top" <?php if (get_option('alien_cal_linktar') == "_top") { echo 'selected="selected"'; } ?>>Same Window</option>
		<option value="_blank" <?php if (get_option('alien_cal_linktar') == "_blank") { echo 'selected="selected"'; } ?>>New Window</option>
		</select>
	</td>
        </tr>
        <tr valign="top">
        <th scope="row">Char Length of Full Cal Event Name</th>
        <td><input type="text" name="alien_fc_len" value="<?php echo get_option('alien_fc_len'); ?>" /></td>
        </tr>
        <tr valign="top">
	<th scope="row">Event List Page Options:</th>
        <td>
		List Date: <input type="radio" name="alien_event_list_date" value="current" <?php if (get_option('alien_event_list_date') == "current") { echo 'checked="checked"'; } ?> /> Current Date
		 <input type="radio" name="alien_event_list_date" value="all" <?php if (get_option('alien_event_list_date') == "all") { echo 'checked="checked"'; } ?> /> All Dates<br />
		# to Display: <input type="text" name="alien_event_list_number" value="<?php echo get_option('alien_event_list_number'); ?>" />
	</td>
        </tr>
        <tr valign="top">
	<th scope="row">Display Category List As:</th>
        <td>
		List <input type="radio" name="alien_event_cat_disp" value="list" <?php if (get_option('alien_event_cat_disp') == "list") { echo 'checked="checked"'; } ?> /> 
		Drop Down <input type="radio" name="alien_event_cat_disp" value="select" <?php if (get_option('alien_event_cat_disp') == "select") { echo 'checked="checked"'; } ?> />
	</td>
        </tr>
        <tr valign="top">
	<th scope="row">Start Week On:</th>
        <td>
		Sunday <input type="radio" name="alien_event_week_start" value="sunday" <?php if (get_option('alien_event_week_start') == "sunday") { echo 'checked="checked"'; } ?> /> 
		Monday <input type="radio" name="alien_event_week_start" value="monday" <?php if (get_option('alien_event_week_start') == "monday") { echo 'checked="checked"'; } ?> />
	</td>
        </tr>
        <tr valign="top">
        <th scope="row">Remove events from calendar after <?php echo get_option('alien_event_expire'); ?> days.<br /><small>Enter 0 to not remove events.</small></th>
        <td><input type="text" name="alien_event_expire" value="<?php echo get_option('alien_event_expire'); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Max Length for Event Titles</th>
        <td><input type="text" name="alien_event_title" value="<?php echo get_option('alien_event_title'); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Max Length for Event Descriptions</th>
        <td><input type="text" name="alien_event_desc" value="<?php echo get_option('alien_event_desc'); ?>" /></td>
        </tr>
</table>
</div>    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
</div>


<?php
}
?>