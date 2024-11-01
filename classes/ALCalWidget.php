<?php
/*
    AlieneilA Event Calendar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

add_action("widgets_init", array('eCalList', 'register'));
class eCalList {
	function control(){
		$options = get_option('alien_event_widget');
		?>
		<p><label>Categories: <br />
		<?php 
	
		$cats = $options['cats'];
	
		$categories = get_categories('parent=0&hide_empty=0');
		foreach ($categories as $cat) {
			?>
			<input type="checkbox" name="pickcats[]" value="<?php echo $cat->term_id; ?>" <?php if ($cats && in_array($cat->term_id, $cats)) { echo 'checked="checked"'; } ?> /> <?php echo $cat->cat_name; ?> <br />
			<?php
		}
		?>
	
		</label>
	
		</p>
		<p>
	
		<label>How Many Events? <input name="many" type="text" value="<?php echo $options['many']; ?>" /></label>
	
		</p>
	
		<?php
		if (isset($_POST['pickcats'])){
			$data['cats'] = $_POST['pickcats'];
			$data['many'] = attribute_escape($_POST['many']);
			update_option('alien_event_widget', $data);
		}
	}
	function widget($args){
		global $wpdb;

		?>
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

		$event_table = $wpdb->prefix . "alevents";

		echo $args['before_widget'];
		echo $args['before_title'] . 'Event List' . $args['after_title'];
	
		$options = get_option('alien_event_widget');
		$cats = $options['cats'];
		$limit = $options['many'];

		$getthese = "(";
		$i = 0;
		if ($cats) {
			foreach ($cats as $catid) {
				$subcats = get_categories('parent='.$catid.'&hide_empty=0');
				foreach ($subcats as $subvalue) {
					$getthese .= "event_cat = '" . $subvalue->term_id . "' ||";
					$gcats = get_categories('parent='.$subvalue->term_id.'&hide_empty=0');
					foreach ($gcats as $gvalue) {
						$getthese .= "event_cat = '" . $gvalue->term_id . "' ||";
					}
				}
	
				$i++;
				if ($i == count($cats)) {
					$getthese .= "event_cat = '".$catid."'";
				}
				else {
					$getthese .= "event_cat = '".$catid."' || ";
				}
			}
		}	
		$getthese .= ") && active = '1'";
		if ($listQuery = mysql_query("SELECT * FROM " . $event_table . " WHERE " . $getthese . " ORDER BY event_date, event_start LIMIT 50")) {
			if (mysql_num_rows($listQuery)) {
				while ($event = mysql_fetch_array($listQuery)) {
					if ($event['event_repeat'] == "once") {
						if ($event['event_date'] >= strtotime(date('d F Y'))) {
							$dates[$event['event_date']][$event['event_id']] = $event['event_name'];
						}
					}
					else if ($event['event_repeat'] == "daily") {
						if (!$event['end_date'] || $event['end_date'] >= strtotime(date('d F Y'))) {
							$date = strtotime(date('d F Y'));
							$dates['$date'][$event['event_id']] = $event['event_name'];
						}
					}
					else if ($event['event_repeat'] == "weekly") {
						if (!$event['end_date'] || $event['end_date'] >= strtotime(date('d F Y'))) {
							if (date('l') == date('l', $event['event_date'])) {
								$nextday = strtotime(date('d F Y'));
							}
							else {
								$nextday = strtotime("next " . date('l', $event['event_date']));
							}
							$dates[$nextday][$event['event_id']] = $event['event_name'];
						}
					}
					else if ($event['event_repeat'] == "monthly") {
						if (!$event['end_date'] || $event['end_date'] >= strtotime(date('d F Y'))) {
							if (strtotime(date('d F Y')) > $event['event_date']) {
								$nextday = mktime(0, 0, 0, date('m')+1, date('d', $event['event_date']), date('Y'));
							}
							else {
								$nextday = $event['event_date'];
							}
							$dates[$nextday][$event['event_id']] = $event['event_name'];
						}
					}
					else if ($event['event_repeat'] == "yearly") {
						if (!$event['end_date'] || $event['end_date'] >= strtotime(date('d F Y'))) {
							if (strtotime(date('d F Y')) > $event['event_date']) {
								$nextday = mktime(0, 0, 0, date('m'), date('d', $event['event_date']), date('Y')+1);
							}
							else {
								$nextday = $event['event_date'];
							}
							$dates[$nextday][$event['event_id']] = $event['event_name'];
						}
					}
					else {
						if (!$event['end_date'] || $event['end_date'] >= strtotime(date('d F Y'))) {
							$date = strtotime(date('d F Y'));
							$first = date('d', mktime(0, 0, 0, date('m', $date), 0, date('Y', $date)));
							if ($first > 1) {
								$nextdate = strtotime("".$event['event_repeat']."" , mktime(0, 0, 0, date('m', $date), 1, date('Y', $date)));
							}
							else {
								$nextdate = strtotime("".$event['event_repeat']."" , mktime(0, 0, 0, date('m', $date), 0, date('Y', $date)));
							}
		
							if ($nextdate < $date) {
								$first = date('d', mktime(0, 0, 0, date('m', $date)+1, 0, date('Y', $date)));
								if ($first > 1) {
									$nextdate = strtotime("".$event['event_repeat']."" , mktime(0, 0, 0, date('m', $date)+1, 1, date('Y', $date)));
								}
								else {
									$nextdate = strtotime("".$event['event_repeat']."" , mktime(0, 0, 0, date('m', $date)+1, 0, date('Y', $date)));
								}
							}
		
							$dates[$nextdate][$event['event_id']] = $event['event_name'];
						}
					}
				}
			}
		}

		echo '<ul class="evWidgetUL">';
		if ($dates) {
			ksort($dates);
		}
		$i = 0;
		if ($dates) {
			foreach ($dates as $key=>$value) {
				if ($i < $limit) {
					if ($showdate != $key) {
						$showdate = $key;
						echo '<span class="evWidgetDate">' . date('d', 0+$key) . ' ' . __(date('M', 0+$key),'AEC') . ' ' . date('Y', 0+$key) . '</span>';
					}
					echo '<li class="evWidgetLI"><ul>';
					foreach ($value as $id=>$event) {
						if ($i < $limit) {
							$i++;
							echo '<li><a href="' . get_option('alien_cal_wpurl') . '/?page_id='.get_option('alien_cal_page_id').'&e=' . $id . '">' . $event . '</a></li>';
						}
					}
					echo '</ul><li>';
				}
			}
		}
		echo '</ul>';

		echo $args['after_widget'];
	  }
	function register(){
		wp_register_sidebar_widget('Event List', 'Event List', array('eCalList', 'widget'));
		wp_register_widget_control('Event List', 'Event List', array('eCalList', 'control'));
	}
}


function widget_EventsCalendar() {
	global $wpdb;

	$events_table = $wpdb->prefix . "alevents";

	?>
	<style>

<?php

	if(get_option('alien_event_new_layout') == "picker") {
		$newstyles = get_option('alien_event_new_styles');

		foreach ($newstyles AS $k => $v) {
			echo '#'.str_replace("'", "", stripslashes($k)).' {';

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
	echo '<div style="clear: both"></div>';
	echo '<div class="cal" name="cal" id="cal">';

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
	$active = array();

	$getactive = mysql_query("SELECT event_date, event_repeat, end_date FROM " . $events_table . " WHERE active='1' ORDER BY event_date");
	while ($activerow = mysql_fetch_array($getactive)) {
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
					$start++;
				}
			}
		}
		elseif ($activerow['event_repeat'] == "monthly") {
			if ($activerow['end_date'] > $date || $activerow['end_date'] == 0) {
				$active[] = strtotime(date('d', $activerow['event_date'])." ".date('F Y', $date));
			}
		}
		elseif ($activerow['event_repeat'] == "yearly") {
			if ($activerow['end_date'] > $date || $activerow['end_date'] == 0) {
				$active[] = strtotime(date('d', $activerow['event_date'])." ".date('F', $activerow['event_date'])." ".date('Y', $date));
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
						if ($ndate < $activerow['end_date']) { $active[] = $ndate; }
					}
					else {
						$active[] = $ndate;
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
					$start++;
				}
			}
		}
		elseif ($activerow['event_repeat'] == "once") {
			if ($activerow['event_date'] >= $date && $activerow['end_date'] < $nextmonth) {
				$active[] = $activerow['event_date'];
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
echo '<div class="calTable" id="calTable">';
echo '<div class="calTH" id="calTH">';

if ($_GET) {
//print_r($_GET);
	$url = "";
	$firstq = 0;
	foreach($_GET AS $key => $value) {
		if ($firstq == 0) {
			$firstq++;
			$url .= "&";
			if ($key != "month" && $key != "page_id") {
			      	$url .= $key."=".$value;
			}
		}
		else {
			if ($key != "month" && $key != "page_id") {
				$url .= "&";
			      	$url .= $key."=".$value;
			}
		}
	}
	if ($firstq > 0) {
		$url .= "&month=";
	}
	else {
		$url .= "&month=";
	}
	if ($mo_up > 0) { ?><a href="<?php echo get_option('alien_cal_wpurl'); ?>/?page_id=<?php echo get_option( 'alien_cal_page_id' ); ?><?php echo $url; ?><?php echo $prevmonth; ?>#cal" class="calPrev" id="calPrev"><</a> <?php }
	echo " <span class=\"calMonth\" id=\"calMonth\">".__($utitle,'AEC')."</span> <span class=\"calYear\" id=\"calYear\">$year</span>"; ?><a href="<?php echo get_option( 'alien_cal_wpurl' ); ?>/?page_id=<?php echo get_option( 'alien_cal_page_id' ); ?><?php echo $url; ?><?php echo $nextmonth; ?>#cal" class="calNext" id="calNext">></a></div><?php
}
else {
	if ($mo_up > 0) { ?><a href="<?php echo get_option( 'alien_cal_wpurl' ); ?>/?page_id=<?php echo get_option( 'alien_cal_page_id' ); ?>&month=<?php echo $prevmonth; ?>#cal" class="calPrev" id="calPrev"><</a> <?php }
	echo " <span class=\"calMonth\" id=\"calMonth\">".__($utitle,'AEC')."</span> <span class=\"calYear\" id=\"calYear\">$year</span>"; ?><a href="<?php echo get_option( 'alien_cal_wpurl' ); ?>/?page_id=<?php echo get_option( 'alien_cal_page_id' ); ?>&month=<?php echo $nextmonth; ?>#cal" class="calNext" id="calNext">></a></div><?php
}
echo '<div style="clear: both"></div>';
echo "<div class=\"tableTR\" id=\"tableTR\">";
	if (get_option('alien_event_week_start') == "sunday") {
		echo "
		<div class=\"calDW\" id=\"calDW\">".__('SU','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('MO','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('TU','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('WE','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('TH','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('FR','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('SA','AEC')."</div>";
	}
	else if (get_option('alien_event_week_start') == "monday") {
		echo "
		<div class=\"calDW\" id=\"calDW\">".__('MO','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('TU','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('WE','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('TH','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('FR','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('SA','AEC')."</div>
		<div class=\"calDW\" id=\"calDW\">".__('SU','AEC')."</div>";
	}

echo "</div>";
echo '<div style="clear: both"></div>';
//This counts the days in the week, up to 7
$day_count = 1;

echo "<div class=\"tableTR\" id=\"tableTR\">";
//first we take care of those blank days
while ( $blank > 0 )
{
echo "<div class=\"calBlank\" id=\"calBlank\">&nbsp;</div>";
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
			echo "<div class=\"calTActive\" id=\"calTActive\"><a href=\"". get_option('alien_cal_wpurl') . "/?page_id=" . get_option('alien_cal_page_id')."&d=$thisdate&month=$mo_up\">$day_num</a></div>";
		}
		else {
			echo "<div class=\"calActive\" id=\"calActive\"><a href=\"". get_option('alien_cal_wpurl') . "/?page_id=" . get_option('alien_cal_page_id')."&d=$thisdate&month=$mo_up\">$day_num</a></div>";
		}
	}
	else {
		if ($day_num == $day && $title == $mot) {
			echo "<div class=\"calToday\" id=\"calToday\">$day_num</div>";
		}
		else {
			echo "<div class=\"calDay\" id=\"calDay\">$day_num</div>";
		}
	}
$day_num++;
$day_count++;

//Make sure we start a new row every week
if ($day_count > 7)
{

echo "</div>";
echo '<div style="clear: both"></div>';
echo "<div class=\"tableTR\" id=\"tableTR\">";
$day_count = 1;
}
} 

//Finaly we finish out the table with some blank details if needed
while ( $day_count >1 && $day_count <=7 )
{
echo "<div class=\"calBlank\" id=\"calBlank\">&nbsp;</div>";
$day_count++;
}

echo "</div>";
echo '<div style="clear: both"></div>';
echo "</div>"; 

echo "</div>";

}

function EventsCalendar_init()
{
  wp_register_sidebar_widget(__('Events Calendar'), __('Events Calendar'), 'widget_EventsCalendar', '');
}

add_action("plugins_loaded", "EventsCalendar_init");

?>