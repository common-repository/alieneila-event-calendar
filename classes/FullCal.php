<?php

	global $wpdb, $divs, $shortcode_tags;

	$events_table = $wpdb->prefix . "alevents";

	?>


<script type="text/javascript" language="JavaScript">
<!-- Copyright 2006,2007 Bontrager Connection, LLC
// http://bontragerconnection.com/ and http://willmaster.com/
// Version: July 28, 2007
var cX = 0; var cY = 0; var rX = 0; var rY = 0;
function UpdateCursorPosition(e){ cX = e.pageX; cY = e.pageY;}
function UpdateCursorPositionDocAll(e){ cX = event.clientX; cY = event.clientY;}
if(document.all) { document.onmousemove = UpdateCursorPositionDocAll; }
else { document.onmousemove = UpdateCursorPosition; }
function AssignPosition(d) {
if(self.pageYOffset) {
	rX = self.pageXOffset;
	rY = self.pageYOffset;
	}
else if(document.documentElement && document.documentElement.scrollTop) {
	rX = document.documentElement.scrollLeft;
	rY = document.documentElement.scrollTop;
	}
else if(document.body) {
	rX = document.body.scrollLeft;
	rY = document.body.scrollTop;
	}
if(document.all) {
	cX += rX; 
	cY += rY;
	}
d.style.left = (cX+10) + "px";
d.style.top = (cY+10) + "px";
}
function HideContent(d) {
if(d.length < 1) { return; }
document.getElementById(d).style.display = "none";
}
function ShowContent(d) {
if(d.length < 1) { return; }
var dd = document.getElementById(d);
AssignPosition(dd);
dd.style.display = "block";
}
function ReverseContentDisplay(d) {
if(d.length < 1) { return; }
var dd = document.getElementById(d);
AssignPosition(dd);
if(dd.style.display == "none") { dd.style.display = "block"; }
else { dd.style.display = "none"; }
}
//-->
</script>




	<style>
<?php

	if(get_option('alien_event_new_layout') == "picker") {

		$theids = array('evWidgetUL','evWidgetDate','evWidgetLI','cal','calTable','tableTR','calTH','calMonth','calYear','calNext','calPrev','calTActive','calTActive a','calToday','calFC','calTableFC','tableTRFC','calTHFC','calMonthFC','calYearFC','calNextFC','calPrevFC','calTActiveFC','calTodayFC','eventCatList','eventDateHead');
		$newstyles = get_option('alien_event_new_styles');

		foreach ($newstyles AS $k => $v) {
			$k = str_replace("'", "", stripslashes($k));
			if (in_array($k, $theids)) {
				echo '#'.$k.' {';
			}
			else {
				echo '.'.$k.' {';
			}

			foreach($v AS $s => $set) {
				$s = str_replace("'", "", stripslashes($s));
				$set = str_replace("'", "", stripslashes($set));
				if ($s == "background-image") {
					if (!$set || $set == "none") {
						echo $s.':none;';
					}
					else {
						echo $s.':url('.$set.');';
					}
				}
				else {
					echo $s.':'.$set.';';
				}
			}
		
			echo '} ';
		}
	}
	else {
		echo get_option('alien_cal_style');
	}
?>
	</style>
	<?php

				$url = "?page_id=".get_option('alien_cal_page_id');
				$url .= "&fccatid=";


				echo '<div class="eventCatList">';
				$includes = get_option( "alien_event_cats" );
				if ($includes) {
					foreach ($includes as $value) {
						$findme .= $value.',';		
					}
					$categories = get_categories('include=' . $findme . '&parent=0&hide_empty=0');

					if (get_option(alien_event_cat_disp) == "list") {
						foreach ($categories as $cat) {
							echo '<ul class="catMainUL">';
							echo '<li class="catMainList"><a href="' . get_option( 'alien_cal_wpurl' ) . '/'.$url.''.$cat->term_id .'"> '.$cat->cat_name.'</a></li>';
						
							$subcats = get_categories('parent='.$cat->term_id.'&hide_empty=0');
							foreach ($subcats as $subs) {
								echo '<li><ul class="catSubUL">';
								echo '<li class="catSubList"><a href="' . get_option( 'alien_cal_wpurl' ) . '/'.$url.''.$subs->term_id .'"> '.$subs->cat_name.'</a></li>';
					
								$grands = get_categories('parent='.$subs->term_id.'&hide_empty=0');
								foreach ($grands as $child) {
									echo '<li><ul class="catChildUL">';
									echo '<li class="catChildList"><a href="' . get_option( 'alien_cal_wpurl' ) . '/'.$url.''.$child->term_id .'"> '.$child->cat_name.'</a></li>';
									echo '</ul></li>';
								}
								echo '</ul></li>';
							}
							echo '</ul>';
						}
					}
					else if (get_option(alien_event_cat_disp) == "select") {
						echo '<form method="get" action="">';
						echo '<input type="hidden" name="pagename" value="cal-events">';
						echo '<input type="hidden" name="month" value="'.$_GET['month'].'">';
						echo '<input type="hidden" name="fc" value="1">';
						echo '<select name="fccatid" onchange="this.form.submit()"><option>'.__("Select Category","AEC").'</option>';
						echo '<option value="">All</option>';
						foreach ($categories as $cat) {
							echo '<option value="'.$cat->term_id.'">'.$cat->cat_name.'</option>';
						
							$subcats = get_categories('parent='.$cat->term_id.'&hide_empty=0');
							foreach ($subcats as $subs) {
								echo '<option value="'.$subs->term_id .'">- '.$subs->cat_name.'</option>';
					
								$grands = get_categories('parent='.$subs->term_id.'&hide_empty=0');
								foreach ($grands as $child) {
									echo '<option value="'.$child->term_id .'">-- '.$child->cat_name.'</option>';
								}
							}
						}
						echo '</select>';
						echo '</form>';
					}
				}
				echo '<div style="clear: both"></div>';
			
				echo ' </div>';


	echo "<div class=\"calFC\">";

	?>
	<?php

	$mo_up = $_GET['month'];
	if (!$mo_up) {
		$date = gmmktime() + get_option('gmt_offset');
		$date = strtotime("1 ".date('F Y', $date));
		$mo_up = 0;
		$nextmonth = mktime(0,0,0, date('n', $date)+1,1, date('Y', $date));
	}
	else {
		$date = gmmktime() + get_option('gmt_offset');
		$date = strtotime("1 ".date('F Y', $date));
		$date = strtotime("+".$mo_up." months", $date);
		$date = strtotime("1 ".date('F Y', $date));
		$nextmonth = mktime(0,0,0,date('n', $date)+1,1,date('Y', $date));
	}
	$active = array('0');
	$eventarray = array('0');
	$idarray = array('0');

	if ($_GET['fccatid']) {
		$getthese = "event_cat = '".$_GET['fccatid']."' && active='1'";
	}
	else {
		$getthese = "active='1'";
	}

	$divs = "";
	$pattern = get_shortcode_regex();

	$getactive = mysql_query("SELECT * FROM " . $events_table . " WHERE " . $getthese . " ORDER BY event_date, event_start");
	while ($activerow = mysql_fetch_array($getactive)) {
		$divs .= '<div id="'.$activerow['event_id'].'" class="popupFC">';
					if (get_option('alien_event_popup_field') == "title") {
						$divs .= $activerow['event_name'];
					}
					if (get_option('alien_event_popup_field') == "description") {
						$divs .= do_shortcode($activerow['description']);
					}
			$divs .= '</div>';
		if ($activerow['event_repeat'] == "daily") {
			if ($activerow['event_date'] < $date && $activerow['end_date'] < $nextmonth) {
				if ($activerow['end_date'] == 0) {
					$numdays = $nextmonth - $date;
				}
				else {
					$numdays = $activerow['end_date'] - $date;
				}
				$numdays = $numdays / (60*60*24);
				$start = 0;
				$ndate = 0;
				while ($start <= $numdays) {
					$ndate = mktime(0,0,0, date("n", $date), date("d", $date)+$start, date("Y", $date));
					$active[] = $ndate;
					$eventarray[] = $activerow['event_name'];
					$idarray[] = $activerow['event_id'];
					$start++;
				}
			}
			else if ($activerow['event_date'] < $nextmonth && $activerow['end_date'] < $nextmonth) {
				$numdays = $activerow['end_date'] - $activerow['event_date'];
				$numdays = round($numdays / (60*60*24));
				$start = 0;
				$ndate = 0;
				while ($start <= $numdays) {
					$ndate = mktime(0,0,0, date("n", $activerow['event_date']), date("d", $activerow['event_date'])+$start, date("Y", $activerow['event_date']));
					$active[] = $ndate;
					$eventarray[] = $activerow['event_name'];
					$idarray[] = $activerow['event_id'];
					$start++;
				}
			}
			elseif ($activerow['event_date'] < $nextmonth) {
				$numdays = $nextmonth - $activerow['event_date'];
				$numdays = round($numdays / (60*60*24));
				$start = 0;
				$ndate = 0;
				while ($start <= $numdays) {
					$ndate = mktime(0,0,0, date("n", $activerow['event_date']), date("d", $activerow['event_date'])+$start, date("Y", $activerow['event_date']));
					$active[] = $ndate;
					$eventarray[] = $activerow['event_name'];
					$idarray[] = $activerow['event_id'];
					$start++;
				}
			}
			else {
				$numdays = $nextmonth - $activerow['event_date'];
				$numdays = round($numdays / (60*60*24));
	
				$start = 0;
				$ndate = 0;
				while ($start <= $numdays) {
					$ndate = mktime(0,0,0, date("n", $activerow['event_date']), date("d", $activerow['event_date'])+$start, date("Y", $activerow['event_date']));
					$active[] = $ndate;
					$eventarray[] = $activerow['event_name'];
					$idarray[] = $activerow['event_id'];
					$start++;
				}
			}
		}
		elseif ($activerow['event_repeat'] == "monthly") {
			if ($activerow['end_date'] > $date || $activerow['end_date'] == 0) {
				$active[] = strtotime(date('d', $activerow['event_date'])." ".date('F Y', $date));
				$eventarray[] = $activerow['event_name'];
				$idarray[] = $activerow['event_id'];
			}
		}
		elseif ($activerow['event_repeat'] == "yearly") {
			if ($activerow['end_date'] > $date || $activerow['end_date'] == 0) {
				$active[] = strtotime(date('d', $activerow['event_date'])." ".date('F', $activerow['event_date'])." ".date('Y', $date));
				$eventarray[] = $activerow['event_name'];
				$idarray[] = $activerow['event_id'];
			}
		}
		elseif ($activerow['event_repeat'] == "weekly") {
			if ($activerow['event_date'] < $date && $activerow['end_date'] < $nextmonth) {
				if ($activerow['end_date'] == 0) {
					$onthisday = date('l', $activerow['event_date']);
					$onthisday = strtotime("first ".$onthisday."", mktime(0, 0, 0, date('m', $date), 0, date('Y', $date)));
					$numdays = $nextmonth - $onthisday;
				}
				else {
					$onthisday = date('l', $activerow['event_date']);
					$onthisday = strtotime("first ".$onthisday."", mktime(0, 0, 0, date('m', $date), 0, date('Y', $date)));
					$numdays = $activerow['end_date'] - $onthisday;
				}
				$numdays = round($numdays / (60*60*24*7));
				$start = 0;
				$ndate = 0;
				while ($start <= $numdays) {
					$ndate = strtotime("+".$start." weeks", $onthisday);
					if ($activerow['end_date'] > 0) {
						if ($ndate < $activerow['end_date']) {
							$active[] = $ndate;
							$eventarray[] = $activerow['event_name'];
							$idarray[] = $activerow['event_id'];
						}
					}
					else {
						$active[] = $ndate;
						$eventarray[] = $activerow['event_name'];
						$idarray[] = $activerow['event_id'];
					}
					$start++;
				}
			}
			elseif ($activerow['event_date'] < $nextmonth && $activerow['end_date'] < $nextmonth) {
				$onthisday = date('l', $activerow['event_date']);
				$onthisday = strtotime("first ".$onthisday."", mktime(0, 0, 0, date('m', $date), 0, date('Y', $date)));

				if ($activerow['end_date'] > 0) {
					$numdays = $activerow['end_date'] - $onthisday;
					$numdays = $numdays / (60*60*24*7);
				}
				else {
					$numdays = 5;
				}
	
				$start = 0;
				$ndate = 0;
				while ($start <= $numdays) {
					$ndate = strtotime("+".$start." weeks", $onthisday);
					$active[] = $ndate;
					$eventarray[] = $activerow['event_name'];
					$idarray[] = $activerow['event_id'];
					$start++;
				}
	
			}
			elseif ($activerow['event_date'] < $date) {
				$onthisday = date('D', $activerow['event_date']);

				$onthisday = strtotime("first ".$onthisday."", mktime(0, 0, 0, date('m', $date), 0, date('Y', $date)));

				$numdays = $activerow['end_date'] - $onthisday;
				$numdays = $numdays / (60*60*24*7);
	
				$start = 0;
				$ndate = 0;
				while ($start <= $numdays) {
					$ndate = strtotime("+".$start." weeks", $onthisday);
					$active[] = $ndate;
					$eventarray[] = $activerow['event_name'];
					$idarray[] = $activerow['event_id'];
					$start++;
				}
	
			}
			else {
				$numdays = $nextmonth - $activerow['event_date'];
				$numdays = $numdays / (60*60*24*7);
	
				$start = 0;
				$ndate = 0;
				while ($start <= $numdays) {
					$ndate = mktime(0,0,0, date("n", $activerow['event_date']), date("d", $activerow['event_date'])+$start*7, date("Y", $activerow['event_date']));
					$active[] = $ndate;
					$eventarray[] = $activerow['event_name'];
					$idarray[] = $activerow['event_id'];
					$start++;
				}
			}
		}
		elseif ($activerow['event_repeat'] == "once") {
			if ($activerow['event_date'] >= $date && $activerow['end_date'] < $nextmonth) {
				$active[] = $activerow['event_date'];
				$eventarray[] = $activerow['event_name'];
				$idarray[] = $activerow['event_id'];
			}
		}
		else {
			if ($activerow['end_date'] > $date || $activerow['end_date'] == 0) {

				$first = date('d', mktime(0, 0, 0, date('m', $date), 0, date('Y', $date)));
				if ($first > 1) {
					$thisdate = strtotime("".$activerow['event_repeat']."" , mktime(0, 0, 0, date('m', $date), 1, date('Y', $date)));
				}
				else {
					$thisdate = strtotime("".$activerow['event_repeat']."" , mktime(0, 0, 0, date('m', $date), 0, date('Y', $date)));
				}

				$ndate = strtotime(date('d F Y'));

				if ($thisdate >= $ndate) {
					$active[] = $thisdate;
					$eventarray[] = $activerow['event_name'];
					$idarray[] = $activerow['event_id'];
				}
			}
		}
	}

$mo_up = $_GET['month'];
if (!$mo_up) {
	$date = gmmktime() + get_option('gmt_offset');
	$mo_up = 0;
}
else {
	$date = gmmktime() + get_option('gmt_offset');
	$date = strtotime("1 ".date('F Y', $date));
	$date = strtotime("+".$mo_up." months", $date);
}


//This puts the day, month, and year in seperate variables
$day = date('d', $date) ;
$month = date('m', $date) ;
$year = date('Y', $date) ;

//Here we generate the first day of the month
$first_day = mktime(0,0,0,$month, 1, $year) ;

//This gets us the month name
$title = date('F', $first_day) ; 

//Here we find out what day of the week the first day of the month falls on
$day_of_week = date('D', $first_day) ;

//Once we know what day of the week it falls on, we know how many blank days occure before it. If the first day of the week is a Sunday then it would be zero
	if (get_option('alien_event_week_start') == "sunday") {
		if ($day_of_week == "Sun") { $blank = 0; }
		else if ($day_of_week == "Mon") { $blank = 1; }
		else if ($day_of_week == "Tue") { $blank = 2; }
		else if ($day_of_week == "Wed") { $blank = 3; }
		else if ($day_of_week == "Thu") { $blank = 4; }
		else if ($day_of_week == "Fri") { $blank = 5; }
		else if ($day_of_week == "Sat") { $blank = 6; }
	}
	else if (get_option('alien_event_week_start') == "monday") {
		if ($day_of_week == "Mon") { $blank = 0; }
		else if ($day_of_week == "Tue") { $blank = 1; }
		else if ($day_of_week == "Wed") { $blank = 2; }
		else if ($day_of_week == "Thu") { $blank = 3; }
		else if ($day_of_week == "Fri") { $blank = 4; }
		else if ($day_of_week == "Sat") { $blank = 5; }
		else if ($day_of_week == "Sun") { $blank = 6; }
	}
$utitle = strtoupper(__($title,'AEC'));
//We then determine how many days are in the current month
$days_in_month = date('t', $date) ; 
$nextmonth=$mo_up+1;
$prevmonth=$mo_up-1;
//Here we start building the table heads
echo "<div class=\"calTableFC\" id=\"calTableFC\">";
echo "<div class=\"calTHFC\" id=\"calTHFC\">";

$url = "?page_id=".get_option('alien_cal_page_id');

if ($_GET['fcatid']) {
	$url .= "&fccatid=".$_GET['fccatid'];
}

if ($_GET['fc']) {
	$url .= "&fc=".$_GET['fc'];
}

$url .= "&month=";

if ($mo_up > 0) { ?><a href="<?php echo get_option( 'alien_cal_wpurl' ); ?>/<?php echo $url; ?><?php echo $prevmonth; ?>#calFC" class="calPrevFC" id="calPrevFC"><</a> <?php }
echo " <span class=\"calMonthFC\" id=\"calMonthFC\">".__($utitle)."</span> <span class=\"calYearFC\" id=\"calYearFC\">$year</span>"; ?><a href="<?php echo get_option( 'alien_cal_wpurl' ); ?>/<?php echo $url; ?><?php echo $nextmonth; ?>#calFC" class="calNextFC" id="calNextFC">></a></div><?php

echo '<div style="clear: both"></div>';
echo "<div class=\"tableTRFC\" id=\"tableTRFC\">";
	if (get_option('alien_event_week_start') == "sunday") {
		echo "
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('SU','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('MO','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('TU','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('WE','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('TH','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('FR','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('SA','AEC')."</div>";
	}
	else if (get_option('alien_event_week_start') == "monday") {
		echo "
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('MO','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('TU','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('WE','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('TH','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('FR','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('SA','AEC')."</div>
		<div class=\"calDWFC\"  id=\"calDWFC\">".__('SU','AEC')."</div>";
	}

echo "</div>";
echo '<div style="clear: both"></div>';
//This counts the days in the week, up to 7
$day_count = 1;

echo "<div class=\"tableTRFC\" id=\"tableTRFC\">";
//first we take care of those blank days
while ( $blank > 0 )
{
echo "<div class=\"calBlankFC\" id=\"calBlankFC\">&nbsp;</div>";
$blank = $blank-1;
$day_count++;
} 

//sets the first day of the month to 1
$day_num = 1;

//count up the days, untill we've done all of them in the month
$newmonth = 1;
while ( $day_num <= $days_in_month )
{
	$mot = date('F', gmmktime() + get_option('gmt_offset'));
//	echo $title.":".$mot;
	$thisdate = strtotime("".$day_num." ".$title." ".$year."");
	if (in_array($thisdate, $active)) {
		if ($day_num == $day && $title == $mot) {
			echo "<div class=\"calTActiveFC\" id=\"calTActiveFC\"><a href=\"" . get_option( 'alien_cal_wpurl' ) . "/?page_id=".get_option('alien_cal_page_id')."&d=$thisdate&month=$mo_up\">$day_num</a><br />";
			$n = 0;
			while ($n < get_option( "alien_fc_per" )) {
				$n++;
				$i = array_search($thisdate, $active);
				if ($i > 0) {
					?><a target="<?php echo get_option( 'alien_cal_linktar' ); ?>" href="<?php echo get_option( 'alien_cal_wpurl' ); ?>/?page_id=<?php echo get_option('alien_cal_page_id'); ?>&e=<?php echo $idarray[$i]; ?>" onmouseover="ShowContent('<?php echo $idarray[$i]; ?>'); return true;" onmouseout="HideContent('<?php echo $idarray[$i]; ?>'); return true;"><?php echo substr($eventarray[$i], 0, get_option( "alien_fc_len" )); ?></a><br /><?php
					unset($eventarray[$i]);
					unset($active[$i]);
					unset($idarray[$i]);
				}
			}
			echo "</div>";
		}
		else {
			echo "<div class=\"calActiveFC\" id=\"calActiveFC\"><a href=\"" . get_option( 'alien_cal_wpurl' ) . "/?page_id=".get_option('alien_cal_page_id')."&d=$thisdate&month=$mo_up\">$day_num</a><br />";
			$n = 0;
			while ($n < get_option( "alien_fc_per" )) {
				$n++;
				$i = array_search($thisdate, $active);
				if ($i > 0) {
					?><a target="<?php echo get_option( 'alien_cal_linktar' ); ?>" href="<?php echo get_option( 'alien_cal_wpurl' ); ?>/?page_id=<?php echo get_option('alien_cal_page_id'); ?>&e=<?php echo $idarray[$i]; ?>" onmouseover="ShowContent('<?php echo $idarray[$i]; ?>'); return true;" onmouseout="HideContent('<?php echo $idarray[$i]; ?>'); return true;"><?php echo substr($eventarray[$i], 0, get_option( "alien_fc_len" )); ?></a><br /><?php
					unset($eventarray[$i]);
					unset($active[$i]);
					unset($idarray[$i]);
				}
			}
			echo "</div>";
		}
	}
	else {
		if ($day_num == $day && $title == $mot) {
			echo "<div class=\"calTodayFC\" id=\"calTodayFC\">$day_num <br />";
			$n = 0;
			while ($n < get_option( "alien_fc_per" )) {
				$n++;
				$i = array_search($thisdate, $active);
				if ($i > 0) {
					?><a target="<?php echo get_option( 'alien_cal_linktar' ); ?>" href="<?php echo get_option( 'alien_cal_wpurl' ); ?>/?page_id=<?php echo get_option('alien_cal_page_id'); ?>&e=<?php echo $idarray[$i]; ?>" onmouseover="ShowContent('<?php echo $idarray[$i]; ?>'); return true;" onmouseout="HideContent('<?php echo $idarray[$i]; ?>'); return true;"><?php echo substr($eventarray[$i], 0, get_option( "alien_fc_len" )); ?></a><br /><?php
					unset($eventarray[$i]);
					unset($active[$i]);
					unset($idarray[$i]);
				}
			}

			echo "</div>";
		}
		else {
			echo "<div class=\"calDayFC\" id=\"calDayFC\">$day_num <br />";
			$n = 0;
			while ($n < get_option( "alien_fc_per" )) {
				$n++;
				$i = array_search($thisdate, $active);
				if ($i > 0) {
					?><a target="<?php echo get_option( 'alien_cal_linktar' ); ?>" href="<?php echo get_option( 'alien_cal_wpurl' ); ?>/?page_id=<?php echo get_option('alien_cal_page_id'); ?>&e=<?php echo $idarray[$i]; ?>" onmouseover="ShowContent('<?php echo $idarray[$i]; ?>'); return true;" onmouseout="HideContent('<?php echo $idarray[$i]; ?>'); return true;"><?php echo substr($eventarray[$i], 0, get_option( "alien_fc_len" )); ?></a><br /><?php
					unset($eventarray[$i]);
					unset($active[$i]);
					unset($idarray[$i]);
				}
			}

			echo "</div>";
		}
	}
$day_num++;
$day_count++;

//Make sure we start a new row every week
if ($day_count > 7)
{
echo "</div>";
echo '<div style="clear: both"></div>';
echo "<div class=\"tableTRFC\" id=\"tableTRFC\">";
$day_count = 1;
}
} 

//Finaly we finish out the table with some blank details if needed
while ( $day_count >1 && $day_count <=7 )
{
echo "<div class=\"calBlankFC\" id=\"calBlankFC\">&nbsp;</div>";
$day_count++;
}

echo "</div>";
echo '<div style="clear: both"></div>';
echo "</div>"; 

echo "</div>";

add_action('shutdown', 'showdivs');

function showdivs() {
	global $divs;
	$thedivs = stripslashes($divs);
	echo $thedivs;
}

function proc_short($filtertext) {
	$thetext = apply_filters('the_content', $filtertext);
	return $thetext;
}
?>