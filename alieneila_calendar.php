<?php
/*
Plugin Name: AlieneilA Event Calendar
Plugin URI: http://area51.alieneila.net
Description: AlieneilA Events Calendar
Version: 1.9.91b
Author: Joshua Segatto
Author URI: http://blog.alieneila.net
*/

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

// Hook for adding admin menus
add_action('admin_menu', 'AL_Calendar_Menu');

require_once 'classes/ALCalWidget.php';
include 'classes/ALMailer.php';
include 'alieneila_settings.php';

add_action('loop_end', 'AL_Send_Mail');

define("ALIEN_CAL_VER", "1.9.91b" );
$siteurl = get_option('siteurl');
define('AL_CAL_FOLDER', dirname(plugin_basename(__FILE__)));
define('AL_CAL_URL', get_option('siteurl').'/wp-content/plugins/' . AL_CAL_FOLDER);
define('WP_ADM_URL', get_option('siteurl').'/wp-admin/');

$days = array(__('Sunday'),__('Monday'),__('Tuesday'),__('Wednesday'),__('Thursday'),__('Friday'),__('Saturday'),__('SU'),__('MO'),__('TU'),__('WE'),__('TH'),__('FR'),__('SA'),);
$months = array(__('January'),__('February'),__('March'),__('April'),__('May'),__('June'),__('July'),__('August'),__('September'),__('October'),__('November'),__('December'));

function AEC_lang_init() {
	load_plugin_textdomain('AEC', '', 'alieneila-event-calendar/language');
}
add_action('init', 'AEC_lang_init');

function AL_Calendar_Menu() {
	global $wpdb, $current_user;

 	$ctbl     = $wpdb->prefix . 'capabilities';
 	$userRole = $current_user->data->$ctbl;
	$role = key($userRole);
	$allowedRoles = get_option('alien_event_roles');
	if (!is_array($allowedRoles)) { $allowedRoles[] = 'administrator'; }
	if (!in_array('administrator', $allowedRoles)) { $allowedRoles[] = 'administrator'; }
	if (in_array($role, $allowedRoles)) {	
		add_menu_page('Events', 'Events', $role, 'al-event-admin', 'al_event_admin');
		add_submenu_page('al-event-admin', 'Settings', 'Settings', 'administrator', 'al-event-setting', 'al_event_setting');
		add_submenu_page('al-event-admin', 'Add Event', 'Add Event', $role, 'al-add-event', 'al_add_event');
		add_action( 'admin_init', 'register_cal_settings' );
	}
}

function register_cal_settings() {
	register_setting( 'alien-cal-group', 'alien_cal_style' );
	register_setting( 'alien-cal-group', 'alien_event_style' );
	register_setting( 'alien-cal-group', 'alien_event_title' );
	register_setting( 'alien-cal-group', 'alien_event_desc' );
	register_setting( 'alien-cal-group', 'alien_event_cats' );
	register_setting( 'alien-cal-group', 'alien_event_roles' );
	register_setting( 'alien-cal-group', 'alien_event_email_time' );
	register_setting( 'alien-cal-group', 'alien_event_email_per' );
	register_setting( 'alien-cal-group', 'alien_event_email_me' );
	register_setting( 'alien-cal-group', 'alien_event_email_mel' );
	register_setting( 'alien-cal-group', 'alien_fc_per' );
	register_setting( 'alien-cal-group', 'alien_fc_len' );
	register_setting( 'alien-cal-group', 'alien_event_expire' );
	register_setting( 'alien-cal-group', 'alien_event_display' );
	register_setting( 'alien-cal-group', 'alien_event_international' );
	register_setting( 'alien-cal-group', 'alien_event_provinces' );
	register_setting( 'alien-cal-group', 'alien_event_countries' );
	register_setting( 'alien-cal-group', 'alien_event_cat_disp' );
	register_setting( 'alien-cal-group', 'alien_event_week_start' );
	register_setting( 'alien-cal-group', 'alien_event_list_date' );
	register_setting( 'alien-cal-group', 'alien_event_list_number' );
	register_setting( 'alien-cal-group', 'alien_event_popup_field' );
	register_setting( 'alien-cal-group', 'alien_event_new_layout' );
	register_setting( 'alien-cal-group', 'alien_event_new_styles' );
	register_setting( 'alien-cal-group', 'alien_event_autophone' );
	register_setting( 'alien-cal-group', 'alien_cal_wpurl' );
	register_setting( 'alien-cal-group', 'alien_cal_page_id' );
	register_setting( 'alien-cal-group', 'alien_cal_page_update' );
	register_setting( 'alien-cal-group', 'alien_cal_money' );
	register_setting( 'alien-cal-group', 'alien_cal_linktar' );
}



/////////////////////////////////////////////////////////////


/* Use the admin_menu action to define the custom boxes */
add_action('admin_menu', 'al_event_box');

/* Use the save_post action to do something with the data entered */
add_action('save_post', 'al_event_save', 1, 2);

/* Adds a custom section to the "advanced" Post and Page edit screens */
function al_event_box() {
  if( function_exists( 'add_meta_box' )) {
    add_meta_box( 'al_post_box_id', __( 'AlieneilA Event Calendar' ), 
                'al_event_custom_box', 'post', 'advanced' );
   }
}
   
/* Prints the inner fields for the custom post/page section */
function al_event_custom_box() {
	global $wpdb;
	$events_table = $wpdb->prefix . "alevents";
	$mail_table = $wpdb->prefix . "almailer";

	$ecatcount = get_option( "alien_event_cats" );

	if (!$ecatcount) {
		?>You must choose post categories in the settings before adding events.<?php
	}
	else {
		echo '<input type="hidden" name="al_event_nonce" id="al_event_nonce" value="' . 
		    wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
		?>
		<SCRIPT LANGUAGE="JAVASCRIPT" TYPE="TEXT/JAVASCRIPT">
			function limitText(limitField, limitCount, limitNum) {
				if (limitField.value.length > limitNum) {
					limitField.value = limitField.value.substring(0, limitNum);
				} 
				else {
					limitCount.value = limitNum - limitField.value.length;
				}
			}

			<!-- Begin
			var n;
			var p;
			var p1;
			function ValidatePhone(){
				p=p1.value
				if(p.length==3){
					//d10=p.indexOf('(')
					pp=p;
					d4=p.indexOf('(')
					d5=p.indexOf(')')
					if(d4==-1){
						pp="("+pp;
					}
					if(d5==-1){
						pp=pp+")";
					}
					document.form.elements[n].value="";
					document.form.elements[n].value=pp;
				}
				if(p.length>3){
					d1=p.indexOf('(')
					d2=p.indexOf(')')
					if (d2==-1){
						l30=p.length;
						p30=p.substring(0,4);
						p30=p30+")"
						p31=p.substring(4,l30);
						pp=p30+p31;
						document.form.elements[n].value="";
						document.form.elements[n].value=pp;
					}
				}
				if(p.length>5){
					p11=p.substring(d1+1,d2);
					if(p11.length>3){
						p12=p11;
						l12=p12.length;
						l15=p.length
						p13=p11.substring(0,3);
						p14=p11.substring(3,l12);
						p15=p.substring(d2+1,l15);
						document.form.elements[n].value="";
						pp="("+p13+")"+p14+p15;
						document.form.elements[n].value=pp;
					}
					l16=p.length;
					p16=p.substring(d2+1,l16);
					l17=p16.length;
					if(l17>3&&p16.indexOf('-')==-1){
						p17=p.substring(d2+1,d2+4);
						p18=p.substring(d2+4,l16);
						p19=p.substring(0,d2+1);
						pp=p19+p17+"-"+p18;
						document.form.elements[n].value="";
						document.form.elements[n].value=pp;
					}
				}
				setTimeout(ValidatePhone,100)
			}
		
			function getIt(m){
				n=m.name;
				p1=m
				ValidatePhone()
			}
		
			function testphone(obj1){
				p=obj1.value
				p=p.replace("(","")
				p=p.replace(")","")
				p=p.replace("-","")
				p=p.replace("-","")
				if (isNaN(p)==true){
					alert("Check phone");
					return false;
				}
			}
		//  End -->
		
			function formatCurrency(num) {
				num = num.toString().replace(/\$|\,/g,'');
				if(isNaN(num))
					num = "0";
					sign = (num == (num = Math.abs(num)));
					num = Math.floor(num*100+0.50000000001);
					cents = num%100;
					num = Math.floor(num/100).toString();
					if(cents<10)
						cents = "0" + cents;
						for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
						num = num.substring(0,num.length-(4*i+3))+','+
						num.substring(num.length-(4*i+3));
						return (((sign)?'':'-') + '<?php echo get_option('alien_cal_money'); ?>' + num + '.' + cents);
			}
		
		</script>
		<?php

		    global $post;
		    $post_id = $post;
		    if (is_object($post_id)) {
		    	$post_id = $post_id->ID;
		    }

		$getevent = mysql_query("SELECT * FROM ". $events_table ." WHERE post_id ='$post_id' && post_id != '' && post_id != '0' LIMIT 1");
		$eventrow = mysql_fetch_array($getevent);
		?>
		<table class="widefat page">
			<input type="hidden" name="eventid" value="<?php echo $eventrow['event_id']; ?>" />
			<input type="hidden" name="oldpostid" value="<?php echo $post_id; ?>" />
			<?php
				if (!$eventrow['event_id']) {
					?><input type="checkbox" name="addevent" value="yes"> Check to add event to this post.<?php
				}
			?>
		</table>
		<table class="widefat page">
			<thead><tr><th colspan="3">Category</th></tr></thead>
	
			<tr>
				<td width="3">
				</td><td NOWRAP>Event Category:
				</td>
				<td>
					<select name="event_cat"> 
						<?php 
						$includes = get_option( "alien_event_cats" );
						foreach ($includes as $value) {
							$findme .= $value.',';
							$subcats = get_categories('parent='. $value . '&hide_empty=0');
							foreach ($subcats as $subvalue) {
								$findme .= $subvalue->term_id.',';
								$gsubcats = get_categories('parent='. $subvalue->term_id . '&hide_empty=0');
								foreach ($gsubcats as $gsubvalue) {
									$findme .= $gsubvalue->term_id.',';
								}
							}
						}
						$categories = get_categories('include=' . $findme . '&hide_empty=0');
						foreach ($categories as $cat) {
							if ($cat->term_id == $eventrow['event_cat']) {
								$option = '<option value="'.$cat->term_id.'" selected="selected">';
							}
							else {
								$option = '<option value="'.$cat->term_id.'">';
							}
							$option .= $cat->cat_name;
							$option .= '</option>';
							echo $option;
						}
						?>
					</select> (Uses Post Categories)
				</td>
			</tr>
		</table>
		<table class="widefat page">
			<thead>
				<tr>
					<th colspan="2">
						Event Details
					</th>
				</tr>
			</thead>
	
			<?php
	
			if ($eventrow['area'] == "Online") {
				?>
				<tr>
					<td>
						Online?:
					</td>
					<td>
						<input type="checkbox" name="area" value="Online" checked="checked" />
					</td>
				</tr>
				<?php
			}
			else {
				?>
				<tr>
					<td>
						Online?:
					</td>
					<td>
						<input type="checkbox" name="area" value="Online" />
					</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td>
					URL:
				</td>
				<td>
					<input type="text" name="url" value="<?php echo $eventrow['url']; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Location:
				</td>
				<td>
					<input type="text" name="location" value="<?php echo $eventrow['location']; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Contact Name:
				</td>
				<td>
					<input type="text" name="contact_name"  value="<?php echo $eventrow['contact_name']; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Contact Email:
				</td>
				<td>
					<input type="text" name="contact_email" value="<?php echo $eventrow['contact_email']; ?>" />
				</td>
			</tr>	
			<tr>
				<td>
					Address:
				</td>
				<td>
					<input type="text" name="address" value="<?php echo $eventrow['address']; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					City:
				</td>
				<td>
					<input type="text" name="city" value="<?php echo $eventrow['city']; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					State/Province:
				</td>
				<td>
					<select name="state">
						<option value="<?php echo $eventrow['state']; ?>"><?php echo $eventrow['state']; ?></option>

					<?php
						if (get_option('alien_event_international') == "us") {
							state_options();
						}
						else {
							$x = explode(",", get_option('alien_event_provinces'));
							foreach ($x as $province) {
								echo '<option value="'.$province.'">'.$province.'</option>';
							}
						}
					?>

					</select>
				</td>
			</tr>
			<?php
				if (get_option('alien_event_international') == "us") {
					echo '<input type="hidden" name="country" value="United States" />';
				}
				else {
					?>
					<tr>
						<td>
							Country:
						</td>
						<td>
							<select name="country">
								<option value="<?php echo $eventrow['country']; ?>"><?php echo $eventrow['country']; ?></option>
								<?php
								$x = explode(",", get_option('alien_event_countries'));
								foreach ($x as $country) {
									echo '<option value="'.$country.'">'.$country.'</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<?php
				}
				?>
			<tr>
				<td>
					Zip:
				</td>
				<td>
					<input type="text" name="zip" value="<?php echo $eventrow['zip']; ?>" maxlength="10" size="5" />
				</td>
			</tr>
			<tr>
				<td>
					Phone:
				</td>
				<td>
					<?php
					if (get_option('alien_event_autophone') == "yes") {
						?>
							<input type="text" id="phone" name="phone" value="<?php echo $eventrow['phone']; ?>" maxlength="13" onKeyUp="javascript:getIt(this)" />
							<span style="font-size:smaller">
								(Numbers only, field will auto format)
							</span>
						<?php
					}
					else {
						?>
							<input type="text" id="phone" name="phone" value="<?php echo $eventrow['phone']; ?>" />
						<?php
					}
					?>
				</td>
			</tr>
	
			<!-- <tr><td>Attendees:</td><td><input type="text" name="attendees" value="$eventrow['attendees']"></td></tr> -->
	
			<tr>
				<td>
					Price:
				</td>
				<td>
					<input type="text" name="price" value="<?php echo $eventrow['price']; ?>" onBlur="this.value=formatCurrency(this.value);" />
					<span style="font-size:smaller">
						(Price will auto format to &#36;xx.00)
					</span>
				</td>
			</tr>
		</table>
		<table class="widefat page">
			<thead>
				<tr>
					<th colspan="2">
						Schedule
					</th>
				</tr>
			</thead>
			<tr>
				<td valign="top">
					Reoccur:
				</td>
				<td>
					<?php
					$options = array("once", "daily", "weekly", "monthly", "yearly");
					if (in_array($eventrow['event_repeat'], $options) || !$eventrow['event_repeat']) {
						?><input type="radio" name="repeaton" value="noday" checked="checked" /><?php
					}
					else {
						?><input type="radio" name="repeaton" value="noday" /><?php
					}
					?>
					<select name="repeat">
						<?php
						if (in_array($eventrow['event_repeat'], $options)) {
							if ($eventrow['event_repeat'] == "once") {
								?><option value="<?php echo $eventrow['event_repeat']; ?>">Never</option><?php
							}
							else {
								?>
								<option value="<?php echo $eventrow['event_repeat']; ?>">
									<?php echo ucfirst($eventrow['event_repeat']); ?>
								</option>
								<?php
							}
						}
						?>
						<option value="once">Never</option>
						<option value="daily">Daily</option>
						<option value="weekly">Weekly</option>
						<option value="monthly">Monthly</option>
						<option value="yearly">Yearly</option>
					</select>
					<br />
					<?php
					if (in_array($eventrow['event_repeat'], $options) || !$eventrow['event_repeat']) {
						?><input type="radio" name="repeaton" value="thisday"><?php
					}
					else {
						?><input type="radio" name="repeaton" value="thisday" checked="checked"><?php
					}
					?>
					On the <select name="repeatwhat">
						<?php
						$x = array();
						if (!in_array($eventrow['event_repeat'], $options)) {
							$x = explode(" ", $eventrow['event_repeat']);
							if ($x[0]) {
								echo '<option value="' . $x[0] . '" selected="selected">' . ucfirst($x[0]) . '</option>';
							}
						}
						?>
						<option value="first">First</option>
						<option value="second">Second</option>
						<option value="third">Third</option>
						<option value="fourth">Fourth</option>
						<option value="last">Last</option>
					</select> 
					<select name="repeatday">
						<?php
						if ($x[1]) {
							echo '<option value="' . $x[1] . '" selected="selected">' . ucfirst($x[1]) . '</option>';
						}
						?> 
						<option value="sunday">Sunday</option>
						<option value="monday">Monday</option>
						<option value="tuesday">Tuesday</option>
						<option value="wednesday">Wednesday</option>
						<option value="thursday">Thursday</option>
						<option value="friday">Friday</option>
						<option value="saturday">Saturday</option>
					</select> of every month.
				</td>
			</tr>
			<?php
			if (!$eventrow['event_date']) {
				$eventrow['event_date'] = strtotime(date('d F Y', strtotime("NOW")));
			}
			$day = date('d', $eventrow['event_date']) ;
			$month = date('F', $eventrow['event_date']) ;
			$year = date('Y', $eventrow['event_date']) ;
			?>
			<tr>
				<td>
					Start Date:
				</td>
				<td>
					<select name="emonth">
						<option value="<?php echo $month; ?>"><?php echo $month; ?></option>
						<option value="January">January</option>
						<option value="February">February</option>
						<option value="March">March</option>
						<option value="April">April</option>
						<option value="May">May</option>
						<option value="June">June</option>
						<option value="July">July</option>
						<option value="August">August</option>
						<option value="September">September</option>
						<option value="October">October</option>
						<option value="November">November</option>
						<option value="December">December</option>
					</select>
					<select name="eday">
						<option value="<?php echo $day; ?>"><?php echo $day; ?></option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
						<option value="24">24</option>
						<option value="25">25</option>
						<option value="26">26</option>
						<option value="27">27</option>
						<option value="28">28</option>
						<option value="29">29</option>
						<option value="30">30</option>
						<option value="31">31</option>
					</select>
					<select name="eyear">
						<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
						<?php
						$years = 0;
						$thisyear = date('Y', strtotime("now"));
						while ($years < 7) {
							?><option value="<?php echo $thisyear; ?>"><?php echo $thisyear; ?></option><?php
							$thisyear++;
							$years++;
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top">
					End Date:
				</td>
				<td>
					<select name="endmonth">
						<?php
						if ($eventrow['end_date'] > 0) {
							?>
							<option value="<?php echo date('F', $eventrow['end_date']); ?>" selected="selected">
								<?php echo date('F', $eventrow['end_date']); ?>
							</option>
							<?php
						}
						?>
						<option></option>
						<option value="January">January</option>
						<option value="February">February</option>
						<option value="March">March</option>
						<option value="April">April</option>
						<option value="May">May</option>
						<option value="June">June</option>
						<option value="July">July</option>
						<option value="August">August</option>
						<option value="September">September</option>
						<option value="October">October</option>
						<option value="November">November</option>
						<option value="December">December</option>
					</select>
					<select name="endday">
						<?php
						if ($eventrow['end_date'] > 0) {
							?><option value="<?php echo date('j', $eventrow['end_date']); ?>"><?php echo date('j', $eventrow['end_date']); ?></option><?php
						}
						?>
						echo "<option></option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
						<option value="24">24</option>
						<option value="25">25</option>
						<option value="26">26</option>
						<option value="27">27</option>
						<option value="28">28</option>
						<option value="29">29</option>
						<option value="30">30</option>
						<option value="31">31</option>
					</select>
					<select name="endyear">
						<?php
							if ($eventrow['end_date'] > 0) {
								?><option value="<?php echo date('Y', $eventrow['end_date']); ?>"><?php echo date('Y', $eventrow['end_date']); ?></option><?php
							}
						?>
						<option></option>
						<?php
						$years = 0;
						$thisyear = date('Y', strtotime("now"));
						while ($years < 7) {
							?><option value="<?php echo $thisyear; ?>"><?php echo $thisyear; ?></option><?php
							$thisyear++;
							$years++;
						}
						?>
					</select>
					<br />
						<span style="font-size:smaller">
							(Leave end date blank for non-ending or non-reoccurring events)
						</span>
				</td>
			</tr>
			<tr>
				<td valign="top">
					Time:
				</td>
				<td>
					<table border=0>
						<tr>
							<td width="10">
								<?php
								if ($eventrow['event_end'] == "All Day") {
									?><input type="radio" name="allday" value="yes" checked="checked" />
									</td>
									<td colspan="2">
										All Day Event
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" name="allday" value="no" />
									</td>
								<?php
								}
								else {
								?>
									<input type="radio" name="allday" value="yes" />
									</td>
									<td colspan="2">
										All Day Event
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" name="allday" value="no" checked="checked" />
									</td>
								<?php
								}
								?>
							<td NOWRAP>
								Starts at
							</td>
							<td>
								<select name="start_hour">
									<?php
									$eventrow['event_start'] = $eventrow['event_start'] + $eventrow['event_date'];
									$hours = date('h', $eventrow['event_start']);
									?>
									<option value="<?php echo $hours; ?>"><?php echo $hours; ?></option>
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
								</select>
								<select name="start_minute">
									<?php
									$mins = date('i', $eventrow['event_start']);
									?>
									<option value="<?php echo $mins; ?>"><?php echo $mins; ?></option>
									echo "<option value="00">00</option>
									<option value="15">15</option>
									<option value="30">30</option>
									<option value="45">45</option>
								</select>
								<select name="start_ap">
									<?php
									$mins = date('A', $eventrow['event_start']);
									?>
									<option value="<?php echo $mins; ?>"><?php echo $mins; ?></option>
									<option value="AM">AM</option>
									<option value="PM">PM</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td width="60">
								Duration
							</td>
							<td>
								<?php
									$durs = explode(":",$eventrow['event_end']);
								?>
								<select name="end_hour">
									<option value="<?php echo $durs[0]; ?>"><?php echo $durs[0]; ?></option>
									<option value="0">0 hr</option>
									<option value="1">1 hr</option>
									<option value="2">2 hrs</option>
									<option value="3">3 hrs</option>
									<option value="4">4 hrs</option>
									<option value="5">5 hrs</option>
									<option value="6">6 hrs</option>
									<option value="7">7 hrs</option>
									<option value="8">8 hrs</option>
									<option value="9">9 hrs</option>
									<option value="10">10 hrs</option>
									<option value="11">11 hrs</option>
									<option value="12">12 hrs</option>
								</select>
								<select name="end_minute">
									<option value="<?php echo $durs[1]; ?>"><?php echo $durs[1]; ?></option>
									<option value="00">0 mins</option>
									<option value="15">15 mins</option>
									<option value="30">30 mins</option>
									<option value="45">45 mins</option>
								</select>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<?php

		if (!$eventrow['event_id']) {
		?>
		<table class="widefat page">
			<thead>
				<tr>
					<th colspan="3">
						Notification Options
					</th>
				</tr>
			</thead>
			<tr>
				<td>
					RSVP?: <input type="radio" name="sendtype" value="1" /> 
					RSVP With Guest Limit?: <input type="radio" name="sendtype" value="2" /> 
					Invitation: <input type="radio" name="sendtype" value="3" checked="checked" />
				</td>
			</tr>
			<tr>
				<td>
					Guest Limit?: <input type="text" name="attendees" />
				</td>
			</tr>
			<tr>
				<td>
					Send To The Following Members: <input type="checkbox" name="sendtoall" value="1" /> ALL 
				</td>
			</tr>
			<tr>
				<td>
					<div style="width: 100%; height:200px; overflow: auto">
						<?php
							$UserIDs = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY user_nicename ASC"));
							foreach ( $UserIDs as $userid ) {
								$user = get_userdata( $userid );
								//print_r($user);
								echo '<input type="checkbox" name="sendto[]" value="' . $user->ID . '" /> ' . $user->display_name . ' ';
							}
						?>
					</div>
				</td>
			</tr>
		</table>

		<?php
		}
	}
}

/* When the post is saved, saves our custom data */
function al_event_save( $post_id, $post ) {
	global $wpdb, $user_ID;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['al_event_nonce'], plugin_basename(__FILE__) )) {
    return $post_id;
  }

  // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
  // to do anything
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
    return $post_id;
  
  // Check permissions
    if ( !current_user_can( 'edit_post', $post_id ) ) {
      return $post_id;
  }
	if ($post->post_type == 'revision') {
		return;
	}

  // OK, we're authenticated: we need to find and save the data

	$events_table = $wpdb->prefix . "alevents";
	$mail_table = $wpdb->prefix . "almailer";

	$ecatcount = get_option( "alien_event_cats" );

	if (!$ecatcount) {
		return $post_id;
	}
	else {
		if ($_POST['repeaton'] == "noday") {
			$eventrepeat = $_POST['repeat'];
		}
		else {
			$eventrepeat = $_POST['repeatwhat']." ".$_POST['repeatday'];
		}
		$eventdate = strtotime("".$_POST['eday']." ".$_POST['emonth']." ".$_POST['eyear']."");
		if ($_POST['allday'] == "yes") {
			$duration = "All Day";
			$event_start = "000000";
		}
		else {
			$event_start = strtotime($_POST['eday']." ".$_POST['emonth']." ".$_POST['eyear']." ".$_POST['start_hour'].":".$_POST['start_minute']." ".$_POST['start_ap']);
			$event_start = $event_start - $eventdate;
			$duration = $_POST['end_hour'].":".$_POST['end_minute'];
		}
		if ($_POST['endmonth'] && $_POST['endday'] && $_POST['endyear']) {
			$enddate = strtotime("".$_POST['endday']." ".$_POST['endmonth']." ".$_POST['endyear']."");
		}
		else {
			$enddate = 0;
		}

		$area = $_POST['area'];
		$location = $_POST['location'];

		$active = "1";

		if ($_POST['eventid']) {
			$event_id = $_POST['eventid'];
			$wpdb->update($events_table, array('member_id'=>$user_ID, 'event_cat'=>$_POST['event_cat'], 'event_name'=>$post->post_title, 'event_date'=>$eventdate, 'event_start'=>$event_start, 'event_end'=>$duration, 'description'=>$post->post_content, 'attendees'=>$_POST['attendees'], 'price'=>$_POST['price'], 'contact_name'=>$_POST['contact_name'], 'contact_email'=>$_POST['contact_email'], 'area'=>$area, 'url'=>$_POST['url'], 'location'=>$location, 'address'=>$_POST['address'], 'city'=>$_POST['city'], 'state'=>$_POST['state'], 'country'=>$_POST['country'], 'zip'=>$_POST['zip'], 'phone'=>$_POST['phone'], 'event_repeat'=>$eventrepeat, 'end_date'=>$enddate), array('event_id'=>$event_id));
		}
		else if ($_POST['addevent'] == "yes") {
			$wpdb->insert($events_table, array('event_id'=>0, 'member_id'=>$user_ID, 'event_cat'=>$_POST['event_cat'], 'event_name'=>$post->post_title, 'event_date'=>$eventdate, 'event_start'=>$event_start, 'event_end'=>$duration, 'description'=>$post->post_content, 'attendees'=>$_POST['attendees'], 'rsvp_type'=>$_POST['sendtype'], 'price'=>$_POST['price'], 'contact_name'=>$_POST['contact_name'], 'contact_email'=>$_POST['contact_email'], 'area'=>$area, 'url'=>$_POST['url'], 'location'=>$location, 'address'=>$_POST['address'], 'city'=>$_POST['city'], 'state'=>$_POST['state'], 'country'=>$_POST['country'], 'zip'=>$_POST['zip'], 'phone'=>$_POST['phone'], 'event_repeat'=>$eventrepeat, 'end_date'=>$enddate, 'post_id'=>$post_id, 'media_url'=>'', 'media_type'=>'', 'media_path'=>'', 'active'=>$active));
			$event_id = $wpdb->insert_id;
		}

		if ($_POST['sendtoall']) {
			$UserIDs = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY user_nicename ASC"));
			foreach ( $UserIDs as $userid ) {
				$user = get_userdata( $userid );
				//print_r($user);
				if ($_POST['sendtype'] == 1) {
					$message = "A new event has been posted at ". get_bloginfo( 'name' ) .". Please RSVP at the link below.\r\n";
					if (get_option('alien_event_email_me')) {
						if (get_option('alien_event_email_mel') == 1) {
							$message .= "<p>".$post->post_content."</p>\r\n";
						}
						else {
							$excerpt = str_split($post->post_content, get_option('alien_event_email_mel'));
							$message .= "<p>".$excerpt[0]."</p>\r\n";
						}
					}
					$message .= '<a href="' . get_option( 'alien_cal_wpurl' ) . '/?page_id='.get_option('alien_cal_page_id').'&e='. $event_id .'&rsvp=1">View Event and RSVP</a>\r\n';
				}
				else if ($_POST['sendtype'] == 2) {
					$message = "A new event has been posted at ". get_bloginfo( 'name' ) .". This event has a limited number of seats open, Reserve your seat today!\r\n";
					if (get_option('alien_event_email_me')) {
						if (get_option('alien_event_email_mel') == 1) {
							$message .= "<p>".$post->post_content."</p>\r\n";
						}
						else {
							$excerpt = str_split($post->post_content, get_option('alien_event_email_mel'));
							$message .= "<p>".$excerpt[0]."</p>\r\n";
						}
					}
					$message .= '<a href="' . get_option( 'alien_cal_wpurl' ) . '/?page_id='.get_option('alien_cal_page_id').'&e='. $event_id .'&rsvp=1">View Event and RSVP</a>\r\n';
				}
				else {
					$message = "A new event has been posted at ". get_bloginfo( 'name' ) .". Click below for details.\r\n";
					if (get_option('alien_event_email_me')) {
						if (get_option('alien_event_email_mel') == 1) {
							$message .= "<p>".$post->post_content."</p>\r\n";
						}
						else {
							$excerpt = str_split($post->post_content, get_option('alien_event_email_mel'));
							$message .= "<p>".$excerpt[0]."</p>\r\n";
						}
					}
					$message .= '<a href="' . get_option( 'alien_cal_wpurl' ) . '/?page_id='.get_option('alien_cal_page_id').'&e='. $event_id .'">View Event Details</a>\r\n';
				}
				$mailing = "INSERT INTO ". $mail_table ." SET member_id='" . $user->ID . "', email='" . $user->user_email . "', subject='A new event has been posted at ". get_bloginfo( 'name' ) ."', message='" . $message . "', event_id='$event_id', sent='0'";
				$add_mailer = mysql_query($mailing) or die(mysql_error());
			}
		}
		else {
			if ($_POST['sendto']) {
				foreach ($_POST['sendto'] as $userid) {
					$user = get_userdata( $userid );
					//print_r($user);
					$message = "A new event has been posted at ". get_bloginfo( 'name' ) .". Click below for details.\r\n";
					if (get_option('alien_event_email_me')) {
						if (get_option('alien_event_email_mel') == 1) {
							$message .= "<p>".$post->post_content."</p>\r\n";
						}
						else {
							$excerpt = str_split($post->post_content, get_option('alien_event_email_mel'));
							$message .= "<p>".$excerpt[0]."</p>\r\n";
						}
					}
					$message .= '<a href="' . get_option( 'alien_cal_wpurl' ) . '/?page_id='.get_option('alien_cal_page_id').'&e='. $event_id .'">View Event Details</a>\r\n';
					$mailing = "INSERT INTO ". $mail_table ." SET member_id='" . $user->ID . "', email='" . $user->user_email . "', subject='A new event has been posted at ". get_bloginfo( 'name' ) ."', message='" . $message . "', event_id='$event_id', sent='0'";
					$add_mailer = mysql_query($mailing) or die(mysql_error());
				}
			}
		}
	}
   return;
}



/////////////////////////////////////////////////////////////

function AL_Send_Mail() {
	global $wpdb;

	$events_table = $wpdb->prefix . "alevents";

	$stitch = strtotime("NOW");
	$todaydate = strtotime(date('d F Y', $stitch));
	$first = strtotime("1 ". date('F Y', $todaydate));
	$last = strtotime(date('t F Y', $todaydate));

	$query = mysql_query("SELECT * FROM " . $events_table . " WHERE event_date < $todaydate");
	while ($event = mysql_fetch_array($query)) {
		$nextdate = 0;
		$updated = 0;
		if ($event[end_date] < $todaydate && $event[end_date] != 0) {
//			mysql_query("DELETE FROM events WHERE event_id='".$event[event_id]."'");
//			mysql_query("DELETE FROM event_track WHERE page_id='".$event[event_id]."'");
		}
		else if ($event[event_repeat] == "once") {
//			mysql_query("DELETE FROM events WHERE event_id='".$event[event_id]."'");
//			mysql_query("DELETE FROM event_track WHERE page_id='".$event[event_id]."'");
		}
		else if ($event[event_repeat] == "daily") {
			if ($event[end_date] == 0 || $event[end_date] >= $todaydate) {
				mysql_query("UPDATE " . $events_table . " SET event_date='".$todaydate."' WHERE event_id='".$event[event_id]."'");
			}
		}
		else if ($event[event_repeat] == "weekly") {
			if ($event[end_date] == 0 || $event[end_date] >= $todaydate) {
				if (date('N', $event[event_date]) < date('N', $todaydate) && date('W', $event[event_date]) <= date('W', $todaydate)) {

					$nextdate = strtotime("next ".date('l', $event[event_date]), $todaydate);
				}
				else {
					$nextdate = $event[event_date];
				}
				if ($nextdate < $todaydate) {
//					echo $nextdate.' '.date('d F Y h:i:s', $nextdate).' - ' . $todaydate . ' ' . date('d F Y h:i:s', $todaydate);
					if (date('l', $event[event_date]) == date('l', $todaydate)) {
						$nextdate = $todaydate;
					}
					else {
						$nextdate = strtotime("next ".date('l', $event[event_date]), $todaydate);
					}
					mysql_query("UPDATE " . $events_table . " SET event_date='".$nextdate."' WHERE event_id='".$event[event_id]."'");
				}
				else {
					mysql_query("UPDATE " . $events_table . " SET event_date='".$nextdate."' WHERE event_id='".$event[event_id]."'");
				}
			}
		}
		else if ($event[event_repeat] == "monthly") {
			if (strtotime(date('d', $event[event_date])." ".date('F Y', $todaydate)) > strtotime(date('d F Y', $todaydate))) {
				$nextdate = strtotime (date('d F Y', $event[event_date]));
			}
			else {
				$nextdate = strtotime("+1 month", $first);
				$nextdate = strtotime(date('d', $event[event_date])." ".date('F Y', $nextdate));
			}
			mysql_query("UPDATE " . $events_table . " SET event_date='".$nextdate."' WHERE event_id='".$event[event_id]."'");
		}
		else if ($event[event_repeat] == "yearly") {
			if ($event[end_date] == 0 || $event[end_date] > $todaydate) {
				$nextdate = mktime(0,0,0,date('n', $event[event_date]),date('d', $event[event_date]),date('Y', $event[event_date])+1);
				mysql_query("UPDATE " . $events_table . " SET event_date='".$nextdate."' WHERE event_id='".$event[event_id]."'");
			}
		}
		else {
			$options = array("once", "daily", "weekly", "monthly", "yearly");
			$earlydate = 99999999999;
			$g = explode(",", $event[event_repeat]);
			foreach ($g AS $k => $repeat) {
				if ($repeat) {
					if (!in_array($repeat, $options)) {
						$x = explode(" ", $repeat);
						if (date('l', strtotime("1 ".date('F Y', $todaydate))) == ucfirst($x[1])) {
							if (strtotime(date('d F Y', strtotime($repeat.' '.date('F Y', $todaydate))-604800)) > $last) {
							}
							else {
								if ($x[0] == "last") {
									$nextdate = strtotime($repeat, strtotime("+1 month", $first));
								}
								else {
									$nextdate = strtotime($repeat.' '.date('F Y', $todaydate))-604800;
								}
								if ($nextdate < $todaydate) {
									$nextdate = strtotime($repeat, strtotime("+1 month", $first));

									if ($nextdate < $earlydate) {
										$earlydate = $nextdate;
										if (date('l', strtotime("1 ".date('F Y', $earlydate))) == ucfirst($x[1])) {
											$earlydate = $earlydate - 604800;
										}
									}

								}
								else {
									if ($nextdate < $earlydate) {
										$earlydate = $nextdate;
									}
								}
							}
						}
						else {
							if (strtotime(date('d F Y', strtotime($repeat.' '.date('F Y', $todaydate)))) > $last) {
							}
							else {
								if ($x[0] == "last") {
									$nextdate = strtotime($repeat, strtotime("+1 month", $first));
								}
								else {
									$nextdate = strtotime($repeat.' '.date('F Y', $todaydate));
								}
								if ($nextdate < $todaydate) {
									$nextdate = strtotime($repeat, strtotime("+1 month", $first));
									if ($nextdate < $earlydate) {
										$earlydate = $nextdate;
										if (date('l', strtotime("1 ".date('F Y', $earlydate))) == ucfirst($x[1])) {
											$earlydate = $earlydate - 604800;
										}
									}

								}
								else {
									if ($nextdate < $earlydate) {
										$earlydate = $nextdate;
									}
								}
							}
						}
					}
				}
			}
			if ($earlydate != 99999999999) {
				mysql_query("UPDATE " . $events_table . " SET event_date='".$earlydate."' WHERE event_id='".$event[event_id]."'");
			}
		}
	}

	$expire = get_option( "alien_event_expire" );
	$expiredate = strtotime("NOW") - (86400 * $expire);
	if ($expire) {
		$eventq = mysql_query("DELETE FROM ".$events_table." WHERE (end_date != '0' && end_date < '".$expiredate."') || (event_date < $expiredate && end_date < '".$expiredate."' && event_repeat = 'once')") or die(mysql_error());
//		while ($erow = mysql_fetch_array($eventq)) {
//			mysql_query("DELETE FROM " . $events_table . " WHERE event_id='" . $erow['event_id'] . "'");
//		}
	}

	$mailer_table = $wpdb->prefix . "almailer";

	if (!get_option( "alien_cal_sent" )) {
		add_option( "alien_cal_sent", strtotime("NOW") );
		update_option( "alien_cal_sent", strtotime("NOW") );
	}

	$tosend = get_option( "alien_event_email_per" );
	$sendtime = get_option( "alien_event_email_time" );
	$lastsent = get_option( "alien_cal_sent" );
	$adminEmail = get_option( "admin_email" );
	$adminName = "Admin";


	if ((strtotime("NOW") - $lastsent) > $sendtime) {

		update_option( "alien_cal_sent", strtotime("NOW") );

		if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$mailer_table.""))) {

			$send_query = "SELECT * FROM " . $mailer_table . " WHERE sent='0' LIMIT ".$tosend."";
			$results = $wpdb->get_results( $send_query );
			foreach ($results as $row) {

				$mailer = new AlienCalMailer();
	        
				$mailer->to         = isset($row->email) ? $row->email : "";
				$mailer->fromName   = isset($adminName) ? $adminName : "";
				$mailer->fromEmail  = isset($adminEmail) ? $adminEmail : "";
				$mailer->replyEmail = isset($adminEmail) ? $adminEmail : "";
				$mailer->subject    = isset($row->subject) ? $row->subject : "";
				$mailer->message    = isset($row->message) ? $row->message : "";
		
				if ($mailer->send()) {
					$update = "UPDATE " . $mailer_table . " SET sent='1' WHERE send_id='".$row->send_id."'";
					$doupdate = $wpdb->query( $update );
				}
				else {
					$update = "UPDATE " . $mailer_table . " SET sent='3' WHERE send_id='".$row->send_id."'";
					$doupdate = $wpdb->query( $update );
				}

			}
		}
	}
}

function calImgUpload() {
    $newinput = array();
    if ($_FILES['calMedia']) {
        $overrides = array('test_form' => false); 
        $file = wp_handle_upload($_FILES['calMedia'], $overrides);
        $newinput = $file;
    }
    return $newinput;
}


function al_event_admin() {
	?>
	<div class="icon32" id="icon-options-general"><br></div>
	<h2>Events</h2>
	<?php
	al_event_menu();

	global $wpdb;

	$events_table = $wpdb->prefix . "alevents";
	$mail_table = $wpdb->prefix . "almailer";
//	$event_track = $wpdb->prefix . "alevent_track";
	if ($_POST['doaction'] == "deleteevent") {
		mysql_query("DELETE FROM " . $events_table . " WHERE event_id='".$_POST['eventid']."'") or die(mysql_error());
		mysql_query("DELETE FROM " . $mail_table . " WHERE event_id='".$_POST['eventid']."'") or die(mysql_error());
//		mysql_query("DELETE FROM " . $event_track . " WHERE page_id='".$_POST['eventid']."'") or die(mysql_error());
		if ($_POST['media_id']) {
			wp_delete_attachment( $_POST['media_id'] );
		}
		if ($_POST['media_url']) {
			$path = parse_url($_POST['media_url']);
			$uploads = wp_upload_dir();
			$path['path'] = str_replace("wp-content/uploads/", "", $path['path']);
			if (file_exists($uploads['basedir'].''.$path['path'])) {
				unlink($uploads['basedir'].''.$path['path']);
			}
		}
		if ($_POST['post_id']) {
			wp_delete_post( $_POST['post_id'] );
		}
	}

	if ($_POST['doaction'] == "updateevent") {

		if ($_POST['repeaton'] == "noday") {
			$eventrepeat = $_POST['repeat'];
		}
		else {
			$eventrepeat = $_POST['repeatwhat']." ".$_POST['repeatday'];
		}

		$eventdate = strtotime("".$_POST['eday']." ".$_POST['emonth']." ".$_POST['eyear']."");

		if ($_POST['allday'] == "yes") {
			$duration = "All Day";
			$event_start = "000000";
		}
		else {
			$event_start = strtotime($_POST['eday']." ".$_POST['emonth']." ".$_POST['eyear']." ".$_POST['start_hour'].":".$_POST['start_minute']." ".$_POST['start_ap']);

			$event_start = $event_start - $eventdate;

			$duration = $_POST['end_hour'].":".$_POST['end_minute'];
		}

		if ($_POST['endmonth'] && $_POST['endday'] && $_POST['endyear']) {
			$enddate = strtotime("".$_POST['endday']." ".$_POST['endmonth']." ".$_POST['endyear']."");
		}
		else {
			$enddate = 0;
		}

		$area = $_POST['area'];
		$location = $_POST['location'];


		$wpdb->update($events_table, array('member_id'=>$user->ID, 'event_cat'=>$_POST['event_cat'], 'event_name'=>$_POST['event_name'], 'event_date'=>$eventdate, 'event_start'=>$event_start, 'event_end'=>$duration, 'description'=>$_POST['description'], 'attendees'=>$_POST['attendees'], 'price'=>$_POST['price'], 'contact_name'=>$_POST['contact_name'], 'contact_email'=>$_POST['contact_email'], 'area'=>$area, 'url'=>$_POST['url'], 'location'=>$location, 'address'=>$_POST['address'], 'city'=>$_POST['city'], 'state'=>$_POST['state'], 'country'=>$_POST['country'], 'zip'=>$_POST['zip'], 'phone'=>$_POST['phone'], 'event_repeat'=>$eventrepeat, 'end_date'=>$enddate), array('event_id'=>$_POST['eventid']));

		if ($_FILES[calMedia]['error'] == 4 || !$_FILES['calMedia']) {  }
		else {
			if ($_POST['media_id']) {
				wp_delete_attachment( $_POST['media_id'] );
			}
			if ($_POST['media_url']) {
				$path = parse_url($_POST['media_url']);
				$uploads = wp_upload_dir();
				$path['path'] = str_replace("wp-content/uploads/", "", $path['path']);
				if (file_exists($uploads['basedir'].''.$path['path'])) {
					unlink($uploads['basedir'].''.$path['path']);
				}
			}

			$calMedia = calImgUpload();

			$wpdb->update($events_table, array('media_url'=>$calMedia['url'], 'media_type'=>$calMedia['type'], 'media_path'=>$calMedia['file']), array('event_id'=>$_POST['eventid']));

		}

		if ($_POST['updatePost']) {
			$post = array(
			  'ID' => $_POST['post_id'],
			  'comment_status' => 'open',
			  'ping_status' => get_option('default_ping_status'),
			  'post_author' => $user_ID,
			  'post_category' => array($_POST['event_cat']), //Add some categories.
			  'post_content' => '<p><a href="' . get_option( 'alien_cal_wpurl' ) . '/?page_id='.get_option('alien_cal_page_id').'&e='.$_POST['eventid'].'">Event Details</a></p>' . $_POST['description'],
			  'post_excerpt' => '',
			  'post_parent' => 0,
			  'post_status' => 'publish',
			  'post_title' => $_POST['event_name'],
			  'post_type' => 'post',
			  'to_ping' => ''
			);

			$post_id = wp_update_post( $post );

			if ($calMedia) {
				$attachment = array(
					'post_title' => $_POST['event_name'],
					'post_content' => $_POST['description'],
					'post_excerpt' => '',
					'post_status' => 'publish',
					'post_mime_type' => $calMedia['type'],
					'image_alt' => $_POST['event_name']
				);
				$attach_id = wp_insert_attachment( $attachment, $calMedia['file'], $_POST['post_id'] );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $calMedia['file'] );
				wp_update_attachment_metadata( $attach_id,  $attach_data );

				$wpdb->update($events_table, array('media_id'=>$attach_id), array('event_id'=>$_POST['eventid']));
			}
		}


		if ($_POST['publish']) {
			$post = array(
			  'ID' => 0,
			  'comment_status' => 'open',
			  'ping_status' => get_option('default_ping_status'),
			  'post_author' => $user_ID,
			  'post_category' => array($_POST['event_cat']), //Add some categories.
			  'post_content' => '<p><a href="' . get_option( 'alien_cal_wpurl' ) . '/?page_id='.get_option('alien_cal_page_id').'&e='.$_POST['eventid'].'">Event Details</a></p>' . $_POST['description'],
			  'post_excerpt' => '',
			  'post_parent' => 0,
			  'post_status' => 'publish',
			  'post_title' => $_POST['event_name'],
			  'post_type' => 'post',
			  'to_ping' => ''
			);

			$post_id = wp_insert_post( $post );

			if ($calMedia) {
				$attachment = array(
					'post_title' => $_POST['event_name'],
					'post_content' => $_POST['description'],
					'post_excerpt' => '',
					'post_status' => 'publish',
					'post_mime_type' => $calMedia['type'],
					'image_alt' => $_POST['event_name']
				);
				$attach_id = wp_insert_attachment( $attachment, $calMedia['file'], $post_id );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $calMedia['file'] );
				wp_update_attachment_metadata( $attach_id,  $attach_data );

			}
			$wpdb->update($events_table, array('post_id'=>$post_id, 'media_id'=>$attach_id), array('event_id'=>$_POST['eventid']));
		}
	}

	if ($_POST['doaction'] == "editevent") {
		?>
		<SCRIPT LANGUAGE="JAVASCRIPT" TYPE="TEXT/JAVASCRIPT">
			function limitText(limitField, limitCount, limitNum) {
				if (limitField.value.length > limitNum) {
					limitField.value = limitField.value.substring(0, limitNum);
				} 
				else {
					limitCount.value = limitNum - limitField.value.length;
				}
			}

			<!-- Begin
			var n;
			var p;
			var p1;
			function ValidatePhone(){
				p=p1.value
				if(p.length==3){
					//d10=p.indexOf('(')
					pp=p;
					d4=p.indexOf('(')
					d5=p.indexOf(')')
					if(d4==-1){
						pp="("+pp;
					}
					if(d5==-1){
						pp=pp+")";
					}
					document.form.elements[n].value="";
					document.form.elements[n].value=pp;
				}
				if(p.length>3){
					d1=p.indexOf('(')
					d2=p.indexOf(')')
					if (d2==-1){
						l30=p.length;
						p30=p.substring(0,4);
						p30=p30+")"
						p31=p.substring(4,l30);
						pp=p30+p31;
						document.form.elements[n].value="";
						document.form.elements[n].value=pp;
					}
				}
				if(p.length>5){
					p11=p.substring(d1+1,d2);
					if(p11.length>3){
						p12=p11;
						l12=p12.length;
						l15=p.length
						p13=p11.substring(0,3);
						p14=p11.substring(3,l12);
						p15=p.substring(d2+1,l15);
						document.form.elements[n].value="";
						pp="("+p13+")"+p14+p15;
						document.form.elements[n].value=pp;
					}
					l16=p.length;
					p16=p.substring(d2+1,l16);
					l17=p16.length;
					if(l17>3&&p16.indexOf('-')==-1){
						p17=p.substring(d2+1,d2+4);
						p18=p.substring(d2+4,l16);
						p19=p.substring(0,d2+1);
						pp=p19+p17+"-"+p18;
						document.form.elements[n].value="";
						document.form.elements[n].value=pp;
					}
				}
				setTimeout(ValidatePhone,100)
			}
		
			function getIt(m){
				n=m.name;
				p1=m
				ValidatePhone()
			}
		
			function testphone(obj1){
				p=obj1.value
				p=p.replace("(","")
				p=p.replace(")","")
				p=p.replace("-","")
				p=p.replace("-","")
				if (isNaN(p)==true){
					alert("Check phone");
					return false;
				}
			}
		//  End -->
		
			function formatCurrency(num) {
				num = num.toString().replace(/\$|\,/g,'');
				if(isNaN(num))
					num = "0";
					sign = (num == (num = Math.abs(num)));
					num = Math.floor(num*100+0.50000000001);
					cents = num%100;
					num = Math.floor(num/100).toString();
					if(cents<10)
						cents = "0" + cents;
						for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
						num = num.substring(0,num.length-(4*i+3))+','+
						num.substring(num.length-(4*i+3));
						return (((sign)?'':'-') + '<?php echo get_option('alien_cal_money'); ?>' + num + '.' + cents);
			}
		
		<!-- Validate that required fields are not blank -->
			function checkform() {

				if (document.form.event_name.value == '') {
					alert('You Must Provide a Title for your event.');
					return false;
				}
				else if (document.form.description.value == '') {
					alert('You Must Provide a Description for your event.');
					return false;
				}
				return true;
			}
		</script>
		<?php

		$getevent = mysql_query("SELECT * FROM ". $events_table ." WHERE event_id='".$_POST['eventid']."'");
		$eventrow = mysql_fetch_array($getevent);
		?>
		<span style="color:red">*</span> denotes a required field.
		<table class="widefat page">
			<form name="form" method="post" action="" onSubmit="return checkform()" enctype="multipart/form-data">
				<input type="hidden" name="doaction" value="updateevent" />
				<input type="hidden" name="eventid" value="<?php echo $_POST['eventid']; ?>" />
				<input type="hidden" name="media_url" value="<?php echo $eventrow['media_url']; ?>" />
				<?php
				if ($eventrow['media_id']) {
					?><input type="hidden" name="media_id" value="<?php echo $eventrow['media_id']; ?>" /><?php
				}
			?><thead><tr><th>Publishing Options</th></tr></thead><?php

			if ($eventrow['post_id']) {
				?>
				<tr><td>Update Post?: <input type="checkbox" name="updatePost" value="1" checked="checked" />
				<input type="hidden" name="post_id" value="<?php echo $eventrow['post_id']; ?>" />
				<?php
			}
			else {
				?>
				<tr><td>Add Post?: <input type="checkbox" name="publish" value="1" />
				<?php
			}
	
			?>
		</table>
		<table class="widefat page">
			<thead><tr><th colspan="3">Title, Category, Description</th></tr></thead>
	
			<tr>
				<td>
					<span style="color:red">*</span></td><td colspan="2">Title: You have <input readonly type="text" name="countdowntitle" size="1" value="<?php echo get_option( "alien_event_title" ); ?>" style="background:transparent; border:none; font-size:100%"> characters left.</font>
						<br /><input type="text" name="event_name"   value='<?php echo htmlspecialchars(stripslashes($eventrow['event_name']), ENT_QUOTES); ?>' style="width: 75%;" onKeyDown="limitText(this.form.event_name,this.form.countdowntitle,<?php echo get_option( "alien_event_title" ); ?>);" onKeyUp="limitText(this.form.event_name,this.form.countdowntitle,<?php echo get_option( "alien_event_title" ); ?>);" maxlength="<?php echo get_option( "alien_event_title" ); ?>">
				</td>
			</tr>
			<tr>
				<td width="3">
					<span style="color:red">*</span></td><td NOWRAP>Event Category:
				</td>
				<td>
					<select name="event_cat"> 
						<?php 
						$includes = get_option( "alien_event_cats" );
						foreach ($includes as $value) {
							$findme .= $value.',';
							$subcats = get_categories('parent='. $value . '&hide_empty=0');
							foreach ($subcats as $subvalue) {
								$findme .= $subvalue->term_id.',';
								$gsubcats = get_categories('parent='. $subvalue->term_id . '&hide_empty=0');
								foreach ($gsubcats as $gsubvalue) {
									$findme .= $gsubvalue->term_id.',';
								}
							}
						}
						$categories = get_categories('include=' . $findme . '&hide_empty=0');
						foreach ($categories as $cat) {
							if ($cat->term_id == $eventrow['event_cat']) {
								$option = '<option value="'.$cat->term_id.'" selected="selected">';
							}
							else {
								$option = '<option value="'.$cat->term_id.'">';
							}
							$option .= $cat->cat_name;
							$option .= '</option>';
							echo $option;
						}
						?>
					</select> (Uses Post Categories)
				</td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2">
					<span style="color:red">*</span>Description <font size=\"1\">
						You have <input readonly type="text" name="countdowndes" size="1" value="<?php echo get_option( "alien_event_desc" ); ?>" style="background:transparent; border:none; font-size:100%"> characters left.</font>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<textarea name="description" style="width: 100%" onKeyDown="limitText(this.form.description,this.form.countdowndes,<?php echo get_option( "alien_event_desc" ); ?>);" onKeyUp="limitText(this.form.description,this.form.countdowndes,<?php echo get_option( "alien_event_desc" ); ?>);" rows="5"><?php echo stripslashes($eventrow['description']); ?></textarea>
				</td>
			</tr>
		</table>
		<table class="widefat page">
			<thead>
				<tr>
					<th colspan="2">
						Event Details
					</th>
				</tr>
			</thead>
	
			<?php
	
			if ($eventrow['area'] == "Online") {
				?>
				<tr>
					<td>
						Online?:
					</td>
					<td>
						<input type="checkbox" name="area" value="Online" checked="checked" />
					</td>
				</tr>
				<?php
			}
			else {
				?>
				<tr>
					<td>
						Online?:
					</td>
					<td>
						<input type="checkbox" name="area" value="Online" />
					</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td>
					URL:
				</td>
				<td>
					<input type="text" name="url" value="<?php echo $eventrow['url']; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Location:
				</td>
				<td>
					<input type="text" name="location" value="<?php echo $eventrow['location']; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Contact Name:
				</td>
				<td>
					<input type="text" name="contact_name"  value="<?php echo $eventrow['contact_name']; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Contact Email:
				</td>
				<td>
					<input type="text" name="contact_email" value="<?php echo $eventrow['contact_email']; ?>" />
				</td>
			</tr>	
			<tr>
				<td>
					Address:
				</td>
				<td>
					<input type="text" name="address" value="<?php echo $eventrow['address']; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					City:
				</td>
				<td>
					<input type="text" name="city" value="<?php echo $eventrow['city']; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					State/Province:
				</td>
				<td>
					<select name="state">
						<option value="<?php echo $eventrow['state']; ?>"><?php echo $eventrow['state']; ?></option>

					<?php
						if (get_option('alien_event_international') == "us") {
							state_options();
						}
						else {
							$x = explode(",", get_option('alien_event_provinces'));
							foreach ($x as $province) {
								echo '<option value="'.$province.'">'.$province.'</option>';
							}
						}
					?>

					</select>
				</td>
			</tr>
			<?php
				if (get_option('alien_event_international') == "us") {
					echo '<input type="hidden" name="country" value="United States" />';
				}
				else {
					?>
					<tr>
						<td>
							Country:
						</td>
						<td>
							<select name="country">
								<option value="<?php echo $eventrow['country']; ?>"><?php echo $eventrow['country']; ?></option>
								<?php
								$x = explode(",", get_option('alien_event_countries'));
								foreach ($x as $country) {
									echo '<option value="'.$country.'">'.$country.'</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<?php
				}
				?>
			<tr>
				<td>
					Zip:
				</td>
				<td>
					<input type="text" name="zip" value="<?php echo $eventrow['zip']; ?>" maxlength="10" size="5" />
				</td>
			</tr>
			<tr>
				<td>
					Phone:
				</td>
				<td>
					<?php
					if (get_option('alien_event_autophone') == "yes") {
						?>
							<input type="text" id="phone" name="phone" value="<?php echo $eventrow['phone']; ?>" maxlength="13" onKeyUp="javascript:getIt(this)" />
						<?php
					}
					else {
						?>
							<input type="text" id="phone" name="phone" value="<?php echo $eventrow['phone']; ?>" />
						<?php
					}
					?>
					<span style="font-size:smaller">
						(Numbers only, field will auto format)
					</span>
				</td>
			</tr>
	
			<!-- <tr><td>Attendees:</td><td><input type="text" name="attendees" value="$eventrow['attendees']"></td></tr> -->
	
			<tr>
				<td>
					Price:
				</td>
				<td>
					<input type="text" name="price" value="<?php echo $eventrow['price']; ?>" onBlur="this.value=formatCurrency(this.value);" />
					<span style="font-size:smaller">
						(Price will auto format to &#36;xx.00)
					</span>
				</td>
			</tr>
		</table>
		<table class="widefat page">
			<thead>
				<tr>
					<th>
						Media
					</th>
				</tr>
			</thead>
	
			<input type="hidden" name="MAX_FILE_SIZE" value="200000" />
			<tr>
				<td>
					Attach/Replace Media: <input id="calMedia" type="file" name="calMedia" />
				</td>
			</tr>
		</table>
		<table class="widefat page">
			<thead>
				<tr>
					<th colspan="2">
						Schedule
					</th>
				</tr>
			</thead>
			<tr>
				<td valign="top">
					Reoccur:
				</td>
				<td>
					<?php
					$options = array("once", "daily", "weekly", "monthly", "yearly");
					if (in_array($eventrow['event_repeat'], $options)) {
						?><input type="radio" name="repeaton" value="noday" checked="checked" /><?php
					}
					else {
						?><input type="radio" name="repeaton" value="noday" /><?php
					}
					?>
					<select name="repeat">
						<?php
						if (in_array($eventrow['event_repeat'], $options)) {
							if ($eventrow['event_repeat'] == "once") {
								?><option value="<?php echo $eventrow['event_repeat']; ?>">Never</option><?php
							}
							else {
								?>
								<option value="<?php echo $eventrow['event_repeat']; ?>">
									<?php echo ucfirst($eventrow['event_repeat']); ?>
								</option>
								<?php
							}
						}
						?>
						<option value="once">Never</option>
						<option value="daily">Daily</option>
						<option value="weekly">Weekly</option>
						<option value="monthly">Monthly</option>
						<option value="yearly">Yearly</option>
					</select>
					<br />
					<?php
					if (in_array($eventrow['event_repeat'], $options)) {
						?><input type="radio" name="repeaton" value="thisday"><?php
					}
					else {
						?><input type="radio" name="repeaton" value="thisday" checked="checked"><?php
					}
					?>
					On the <select name="repeatwhat">
						<?php
						if (!in_array($eventrow['event_repeat'], $options)) {
							$x = explode(" ", $eventrow['event_repeat']);
							echo '<option value="' . $x[0] . '" selected="selected">' . ucfirst($x[0]) . '</option>';
						}
						?>
						<option value="first">First</option>
						<option value="second">Second</option>
						<option value="third">Third</option>
						<option value="fourth">Fourth</option>
						<option value="last">Last</option>
					</select> 
					<select name="repeatday">
						<?php
						if ($x[1]) {
							echo '<option value="' . $x[1] . '" selected="selected">' . ucfirst($x[1]) . '</option>';
						}
						?> 
						<option value="sunday">Sunday</option>
						<option value="monday">Monday</option>
						<option value="tuesday">Tuesday</option>
						<option value="wednesday">Wednesday</option>
						<option value="thursday">Thursday</option>
						<option value="friday">Friday</option>
						<option value="saturday">Saturday</option>
					</select> of every month.
				</td>
			</tr>
			<?php
			$day = date('d', $eventrow['event_date']) ;
			$month = date('F', $eventrow['event_date']) ;
			$year = date('Y', $eventrow['event_date']) ;
			?>
			<tr>
				<td>
					Start Date:
				</td>
				<td>
					<select name="emonth">
						<option value="<?php echo $month; ?>"><?php echo $month; ?></option>
						<option value="January">January</option>
						<option value="February">February</option>
						<option value="March">March</option>
						<option value="April">April</option>
						<option value="May">May</option>
						<option value="June">June</option>
						<option value="July">July</option>
						<option value="August">August</option>
						<option value="September">September</option>
						<option value="October">October</option>
						<option value="November">November</option>
						<option value="December">December</option>
					</select>
					<select name="eday">
						<option value="<?php echo $day; ?>"><?php echo $day; ?></option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
						<option value="24">24</option>
						<option value="25">25</option>
						<option value="26">26</option>
						<option value="27">27</option>
						<option value="28">28</option>
						<option value="29">29</option>
						<option value="30">30</option>
						<option value="31">31</option>
					</select>
					<select name="eyear">
						<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
						<?php
						$years = 0;
						$thisyear = date('Y', strtotime("now"));
						while ($years < 7) {
							?><option value="<?php echo $thisyear; ?>"><?php echo $thisyear; ?></option><?php
							$thisyear++;
							$years++;
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top">
					End Date:
				</td>
				<td>
					<select name="endmonth">
						<?php
						if ($eventrow['end_date'] > 0) {
							?>
							<option value="<?php echo date('F', $eventrow['end_date']); ?>" selected="selected">
								<?php echo date('F', $eventrow['end_date']); ?>
							</option>
							<?php
						}
						?>
						<option></option>
						<option value="January">January</option>
						<option value="February">February</option>
						<option value="March">March</option>
						<option value="April">April</option>
						<option value="May">May</option>
						<option value="June">June</option>
						<option value="July">July</option>
						<option value="August">August</option>
						<option value="September">September</option>
						<option value="October">October</option>
						<option value="November">November</option>
						<option value="December">December</option>
					</select>
					<select name="endday">
						<?php
						if ($eventrow['end_date'] > 0) {
							?><option value="<?php echo date('j', $eventrow['end_date']); ?>"><?php echo date('j', $eventrow['end_date']); ?></option><?php
						}
						?>
						echo "<option></option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
						<option value="24">24</option>
						<option value="25">25</option>
						<option value="26">26</option>
						<option value="27">27</option>
						<option value="28">28</option>
						<option value="29">29</option>
						<option value="30">30</option>
						<option value="31">31</option>
					</select>
					<select name="endyear">
						<?php
							if ($eventrow['end_date'] > 0) {
								?><option value="<?php echo date('Y', $eventrow['end_date']); ?>"><?php echo date('Y', $eventrow['end_date']); ?></option><?php
							}
						?>
						<option></option>
						<?php
						$years = 0;
						$thisyear = date('Y', strtotime("now"));
						while ($years < 7) {
							?><option value="<?php echo $thisyear; ?>"><?php echo $thisyear; ?></option><?php
							$thisyear++;
							$years++;
						}
						?>
					</select>
					<br />
						<span style="font-size:smaller">
							(Leave end date blank for non-ending or non-reoccurring events)
						</span>
				</td>
			</tr>
			<tr>
				<td valign="top">
					Time:
				</td>
				<td>
					<table border=0>
						<tr>
							<td width="10">
								<?php
								if ($eventrow['event_end'] == "All Day") {
									?><input type="radio" name="allday" value="yes" checked="checked" />
									</td>
									<td colspan="2">
										All Day Event
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" name="allday" value="no" />
									</td>
								<?php
								}
								else {
								?>
									<input type="radio" name="allday" value="yes" />
									</td>
									<td colspan="2">
										All Day Event
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" name="allday" value="no" checked="checked" />
									</td>
								<?php
								}
								?>
							<td NOWRAP>
								Starts at
							</td>
							<td>
								<select name="start_hour">
									<?php
									$eventrow['event_start'] = $eventrow['event_start'] + $eventrow['event_date'];
									$hours = date('h', $eventrow['event_start']);
									?>
									<option value="<?php echo $hours; ?>"><?php echo $hours; ?></option>
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
								</select>
								<select name="start_minute">
									<?php
									$mins = date('i', $eventrow['event_start']);
									?>
									<option value="<?php echo $mins; ?>"><?php echo $mins; ?></option>
									echo "<option value="00">00</option>
									<option value="15">15</option>
									<option value="30">30</option>
									<option value="45">45</option>
								</select>
								<select name="start_ap">
									<?php
									$mins = date('A', $eventrow['event_start']);
									?>
									<option value="<?php echo $mins; ?>"><?php echo $mins; ?></option>
									<option value="AM">AM</option>
									<option value="PM">PM</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td width="60">
								Duration
							</td>
							<td>
								<?php
									$durs = explode(":",$eventrow['event_end']);
								?>
								<select name="end_hour">
									<option value="<?php echo $durs[0]; ?>"><?php echo $durs[0]; ?></option>
									<option value="0">0 hr</option>
									<option value="1">1 hr</option>
									<option value="2">2 hrs</option>
									<option value="3">3 hrs</option>
									<option value="4">4 hrs</option>
									<option value="5">5 hrs</option>
									<option value="6">6 hrs</option>
									<option value="7">7 hrs</option>
									<option value="8">8 hrs</option>
									<option value="9">9 hrs</option>
									<option value="10">10 hrs</option>
									<option value="11">11 hrs</option>
									<option value="12">12 hrs</option>
								</select>
								<select name="end_minute">
									<option value="<?php echo $durs[1]; ?>"><?php echo $durs[1]; ?></option>
									<option value="00">0 mins</option>
									<option value="15">15 mins</option>
									<option value="30">30 mins</option>
									<option value="45">45 mins</option>
								</select>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input class="button-primary" type="submit" value="Update Event">
				</td>
			</tr>
		</table>
		</form>
		<?php
	}

	if ($_POST['doaction'] != "editevent") {


		?>
		<script language="javascript" type="text/javascript">
			function toggleLayer( whichLayer )
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
		</script>
		<?php wp_enqueue_script('jquery'); ?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				// hides the slickbox as soon as the DOM is ready
				<?php
				
				$ecat = get_option( "alien_event_cats" );

				$thecats = "";
				$start = 0;
				
				foreach ($ecat AS $ecatk => $ecatv) {
					$babycats = get_categories('child_of='.$ecatv.'&hide_empty=0');
					foreach ($babycats AS $tinycat) {
						if ($start == 0) {
							$thecats .= $tinycat->term_id;
							$start = 1;
						}
						else {
							$thecats .= ",".$tinycat->term_id;
						}
					}
					if ($start == 0) {
						$thecats .= $ecatv;
						$start = 1;
					}
					else {
						$thecats .= ",".$ecatv;
					}
				}

				$categories = get_categories('include='.$thecats.'&hide_empty=0&heirarchial=1');

				foreach ($categories as $cat) {
					$thecats .= ','.$cat->term_id;
					?>
					jQuery('#<?php echo str_replace(" ", "", $cat->slug); ?>').hide();
					jQuery('a#<?php echo str_replace(" ", "", $cat->slug) . '-link'; ?>').click(function() {
					jQuery('#<?php echo str_replace(" ", "", $cat->slug); ?>').slideToggle(400);
					return false;
					});
					<?php
				}

				?>
			});
		</script>

		<?php
		$categories = get_categories('include='.$thecats.'&hide_empty=0');

		foreach ($categories as $cat) {
			$eventcount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$events_table." WHERE event_cat='".$cat->term_id."'"));
			?>
			<table class="widefat page">
				<thead>
					<tr>
						<th>
							<a href="javascript:toggleLayer('<?php echo str_replace(" ", "", $cat->slug); ?>');" style="display:block">
							<img src="<?php echo AL_CAL_URL; ?>/icons/glasses-icon.jpg"> <?php echo $cat->cat_name; ?> ( <?php echo $eventcount; ?> )</a>
						</th>
					</tr>
				</thead>
			</table>
			<?php if ($eventcount) { ?>
			<table class="widefat page">
				<tr>
					<td>
						<div id="<?php echo str_replace(" ", "", $cat->slug); ?>" style="display:none">
							<?php
							$event_list = mysql_query("SELECT * FROM ". $events_table ." WHERE (event_cat='$cat->term_id') ORDER BY event_date, event_start ASC") or die(mysql_error());
							while ($event_row = mysql_fetch_array($event_list)) {
								?>
								<table width="100%">
									<tr>
										<td width="80%">
											<?php echo $event_row['event_name'].' <div style="float:right">Views ('.$event_row['counter'].')</div>'; ?>
										</td>
										<td width="20%">
											<form method="post" action="" style="margin:0; padding:0">
												<input type="hidden" name="doaction" value="editevent">
												<input type="hidden" name="eventid" value="<?php echo $event_row['event_id']; ?>">
												<a href="javascript:toggleLayer('<?php echo $event_row['event_id']; ?>');">
													<img src="<?php echo AL_CAL_URL; ?>/icons/glasses-icon.jpg">
												</a>
												<input type="submit" name="submit" value="Edit" style="color:transparent; cursor:hand; cursor:pointer; background-image: url(<?php echo AL_CAL_URL; ?>/icons/edit-icon.jpg); border: 0; width: 16px; height: 16px">
												<a href="javascript:toggleLayer('delete<?php echo $event_row['event_id']; ?>');">
													<img src="<?php echo AL_CAL_URL; ?>/icons/delete-icon.jpg">
												</a>
											</form>
										</td>
									</tr>
								</table>
								<!-- Delete Div -->
								<div id="delete<?php echo $event_row['event_id']; ?>" style="display:none; background:white; padding: 10px; border: 1px solid #900; margin: 0 auto 10px auto">
									<a href="javascript:toggleLayer('delete<?php echo $event_row['event_id']; ?>');" style="float:right">
										Close
									</a>
									<p style="color:red">
										Warning: By deleting this event, you are perminantly removing it from the database. If this is a repeating event, all instances will also be deleted.
									</p>
									<table border="0" width="100%">
										<?php
										if ($event_row['active'] == "0") {
											?><tr><td width="20%">Status:</td><td>Inactive</td></tr><?php
										}
										else if ($event_row['active'] == "Private") {
											?><tr><td width="20%">Status:</td><td>Private</td></tr><?php
										}
										else {
											?><tr><td width="20%">Status:</td><td>Active</td></tr><?php
										}
										?>
										<tr>
											<td>
												Event Title:
											</td>
											<td>
												<?php echo $event_row['event_name']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Event Date:
											</td>
											<td>
												<?php echo date('d F Y', $event_row['event_date']); ?>
											</td>
										</tr>
										<?php $evtime = $event_row['event_date'] + $event_row['event_start']; ?>
										<tr>
											<td>
												Event Start:
											</td>
											<td>
												<?php echo date('h:i A', $evtime); ?>
											</td>
										</tr>
										<tr>
											<td>
												Duration:
											</td>
											<td>
												<?php echo $event_row['event_end']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Online?:
											</td>
											<td>
												<?php echo $event_row['area']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Url:
											</td>
											<td>
												<?php echo $event_row['url']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Location:
											</td>
											<td>
												<?php echo $event_row['location']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Contact:
											</td>
											<td>
												<?php echo $event_row['contact_name']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Email:
											</td>
											<td>
												<?php echo $event_row['contact_email']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Address:
											</td>
											<td>
												<?php echo $event_row['address'].' '.$event_row['city'].' '.$event_row['state'].' '.$event_row['zip']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Phone:
											</td>
											<td>
												<?php echo $event_row['phone']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Max RSVP:
											</td>
											<td>
												<?php echo $event_row['attendees']; ?>
											</td>
										</tr>
										<tr>
											<td>
												RSVPs:
											</td>
											<td>
												<?php echo $event_row['rsvp_count']; ?>
											</td>
										</tr>
										<?php
										if ($event_row['price'] == "$0.00") {
											?><tr>
												<td>
													Price:
												</td>
												<td>
													Free
												</td>
											</tr><?php
										}
										else if ($event_row['price']) {
											?><tr>
												<td>
													Price:
												</td>
												<td>
													<?php echo $event_row['price']; ?>
												</td>
											</tr><?php
										}
										?>
										<tr>
											<td>
												Event Repeat:
											</td>
											<td>
												<?php echo $event_row['event_repeat']; ?>
											</td>
										</tr>
										<?php
										if ($event_row['end_date']) {
											?><tr>
												<td>
													Repeat Until:
												</td>
												<td>
													<?php echo date('j F Y', $event_row['end_date']); ?>
												</td>
											</tr><?php
										}
										?>
										<tr>
											<td colspan="2">
												Description:
											</td>
										</tr>
										<tr>
											<td colspan="2">
													<?php echo $event_row['description']; ?>
											</td>
										</tr>
									</table>
									<form method="post" action="">
										<input type="hidden" name="doaction" value="deleteevent" />
										<input type="hidden" name="eventid" value="<?php echo $event_row['event_id']; ?>" />
										<input type="hidden" name="media_url" value="<?php echo $event_row['media_url']; ?>" />
										<input type="hidden" name="media_id" value="<?php echo $event_row['media_id']; ?>" />
										<input type="hidden" name="post_id" value="<?php echo $event_row['post_id']; ?>" />
										<input type="submit" value="Confirm Deletion" />
									</form>
								</div>
								<!-- End Delete Div -->
								<!-- View Div -->
								<div id="<?php echo $event_row['event_id']; ?>" style="display:none; background:white; padding: 10px; border: 1px solid #900; margin: 0 auto 10px auto">
									<a href="javascript:toggleLayer('<?php echo $event_row['event_id']; ?>');" style="float:right">
										Close
									</a>
									<table border="0" width="100%">
										<?php
										if ($event_row['active'] == "0") {
											?><tr><td width="20%">Status:</td><td>Inactive</td></tr><?php
										}
										else if ($event_row['active'] == "Private") {
											?><tr><td width="20%">Status:</td><td>Private</td></tr><?php
										}
										else {
											?><tr><td width="20%">Status:</td><td>Active</td></tr><?php
										}
										?>
										<tr>
											<td>
												Event Title:
											</td>
											<td>
												<?php echo $event_row['event_name']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Event Date:
											</td>
											<td>
												<?php echo date('d F Y', $event_row['event_date']); ?>
											</td>
										</tr>
										<?php $evtime = $event_row['event_date'] + $event_row['event_start']; ?>
										<tr>
											<td>
												Event Start:
											</td>
											<td>
												<?php echo date('h:i A', $evtime); ?>
											</td>
										</tr>
										<tr>
											<td>
												Duration:
											</td>
											<td>
												<?php echo $event_row['event_end']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Online?:
											</td>
											<td>
												<?php echo $event_row['area']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Url:
											</td>
											<td>
												<?php echo $event_row['url']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Location:
											</td>
											<td>
												<?php echo $event_row['location']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Contact:
											</td>
											<td>
												<?php echo $event_row['contact_name']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Email:
											</td>
											<td>
												<?php echo $event_row['contact_email']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Address:
											</td>
											<td>
												<?php echo $event_row['address'].' '.$event_row['city'].' '.$event_row['state'].' '.$event_row['zip']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Phone:
											</td>
											<td>
												<?php echo $event_row['phone']; ?>
											</td>
										</tr>
										<tr>
											<td>
												Max RSVP:
											</td>
											<td>
												<?php echo $event_row['attendees']; ?>
											</td>
										</tr>
										<tr>
											<td>
												RSVPs:
											</td>
											<td>
												<?php echo $event_row['rsvp_count']; ?> 
												<?php
												$rsvpq = mysql_query("SELECT member_id FROM ".$mail_table." WHERE event_id='".$event_row['event_id']."' && sent='4'");
												if (mysql_num_rows($rsvpq)) {
												?>
												(
												<a href="javascript:toggleLayer('rsvp_<?php echo $event_row['event_id']; ?>');">
													View
												</a>
												)
												<div id="rsvp_<?php echo $event_row['event_id']; ?>" style="display:none; background:white; padding: 10px; border: 1px solid #900; margin: 0 auto 10px auto">
													<?php
														echo '<p>'.__('Members who have RSVPd').'</p>';
														while ($rsvp = mysql_fetch_array($rsvpq)) {
															$user = get_userdata( $rsvp['member_id'] );
															echo '' . $user->display_name . ' - ';
														}
													?>													
												</div>
												<?php } ?>
											</td>
										</tr>
										<?php
										if ($event_row['price'] == "$0.00") {
											?><tr>
												<td>
													Price:
												</td>
												<td>
													Free
												</td>
											</tr><?php
										}
										else if ($event_row['price']) {
											?><tr>
												<td>
													Price:
												</td>
												<td>
													<?php echo $event_row['price']; ?>
												</td>
											</tr><?php
										}
										?>
										<tr>
											<td>
												Event Repeat:
											</td>
											<td>
												<?php echo $event_row['event_repeat']; ?>
											</td>
										</tr>
										<?php
										if ($event_row['end_date']) {
											?><tr>
												<td>
													Repeat Until:
												</td>
												<td>
													<?php echo date('j F Y', $event_row['end_date']); ?>
												</td>
											</tr><?php
										}
										?>
										<tr>
											<td colspan="2">
												Description:
											</td>
										</tr>
										<tr>
											<td colspan="2">
													<?php echo $event_row['description']; ?>
											</td>
										</tr>
									</table>
								</div>
								<!-- End View Div -->
							<?php
							}
							?>
						</div>
					</td>
				</tr>
			</table>
			<?php } ?>
	  <?php }
	}
}

function al_event_menu() {
	echo '<a href="admin.php?page=al-event-admin">Events</a>';
	echo ' | <a href="admin.php?page=al-add-event">Add Event</a>';
	echo ' | <a href="admin.php?page=al-event-setting">Settings</a>';

?><br /><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="DXK4N8EQJSUG6">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"> Please donate to keep our plugins free, active and supported.
</form><?php
	echo '<hr />';

}

function al_add_event() {
	global $wpdb, $user_ID;

	?>
	<div class="icon32" id="icon-options-general"><br></div>
	<h2>Add Event</h2>

	<?php 
	al_event_menu();

echo '<p>'.date('d F Y h:i:s').'</p>';

	$events_table = $wpdb->prefix . "alevents";
	$mail_table = $wpdb->prefix . "almailer";

	$ecatcount = get_option( "alien_event_cats" );

	if (!$ecatcount) {
		?>You must choose post categories in the settings before adding events.<?php
	}
	else {
		?>
		<script language="javascript" type="text/javascript">
			function limitText(limitField, limitCount, limitNum) {
				if (limitField.value.length > limitNum) {
					limitField.value = limitField.value.substring(0, limitNum);
				} 
				else {
					limitCount.value = limitNum - limitField.value.length;
				}
			}
		
			<!-- Begin
			var n;
			var p;
			var p1;
			function ValidatePhone(){
				p=p1.value
				if(p.length==3){
					//d10=p.indexOf('(')
					pp=p;
					d4=p.indexOf('(')
					d5=p.indexOf(')')
					if(d4==-1){
						pp="("+pp;
					}
					if(d5==-1){
						pp=pp+")";
					}
					document.form.elements[n].value="";
					document.form.elements[n].value=pp;
				}
				if(p.length>3){
					d1=p.indexOf('(')
					d2=p.indexOf(')')
					if (d2==-1){
						l30=p.length;
						p30=p.substring(0,4);
						p30=p30+")"
						p31=p.substring(4,l30);
						pp=p30+p31;
						document.form.elements[n].value="";
						document.form.elements[n].value=pp;
					}
				}
				if(p.length>5){
					p11=p.substring(d1+1,d2);
					if(p11.length>3){
						p12=p11;
						l12=p12.length;
						l15=p.length
						p13=p11.substring(0,3);
						p14=p11.substring(3,l12);
						p15=p.substring(d2+1,l15);
						document.form.elements[n].value="";
						pp="("+p13+")"+p14+p15;
						document.form.elements[n].value=pp;
					}
					l16=p.length;
					p16=p.substring(d2+1,l16);
					l17=p16.length;
					if(l17>3&&p16.indexOf('-')==-1){
						p17=p.substring(d2+1,d2+4);
						p18=p.substring(d2+4,l16);
						p19=p.substring(0,d2+1);
						pp=p19+p17+"-"+p18;
						document.form.elements[n].value="";
						document.form.elements[n].value=pp;
					}
				}
				setTimeout(ValidatePhone,100)
			}
		
			function getIt(m){
				n=m.name;
				p1=m
				ValidatePhone()
			}
		
			function testphone(obj1){
				p=obj1.value
				p=p.replace("(","")
				p=p.replace(")","")
				p=p.replace("-","")
				p=p.replace("-","")
				if (isNaN(p)==true){
					alert("Check phone");
					return false;
				}
			}
		//  End -->
		
			function formatCurrency(num) {
				num = num.toString().replace(/\$|\,/g,'');
				if(isNaN(num))
					num = "0";
					sign = (num == (num = Math.abs(num)));
					num = Math.floor(num*100+0.50000000001);
					cents = num%100;
					num = Math.floor(num/100).toString();
					if(cents<10)
						cents = "0" + cents;
						for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
						num = num.substring(0,num.length-(4*i+3))+','+
						num.substring(num.length-(4*i+3));
						return (((sign)?'':'-') + '<?php echo get_option('alien_cal_money'); ?>' + num + '.' + cents);
			}
		
		
		<!-- Validate that required fields are not blank -->
		
		function checkform() {
			if (document.form.event_name.value == '') {
				alert('You Must Provide a Title for your event.');
				return false;
			}
			else if (document.form.description.value == '') {
				alert('You Must Provide a Description for your event.');
				return false;
			}
			return true;
		}
		
		
		</script>
		<?php
	
		if ($_POST['doaction'] == "postnew") {
			if ($_POST['repeaton'] == "noday") {
				$eventrepeat = $_POST['repeat'];
			}
			else {
				$eventrepeat = $_POST['repeatwhat']." ".$_POST['repeatday'];
			}
			$eventdate = strtotime("".$_POST['eday']." ".$_POST['emonth']." ".$_POST['eyear']."");
			if ($_POST['allday'] == "yes") {
				$duration = "All Day";
				$event_start = "000000";
			}
			else {
				$event_start = strtotime($_POST['eday']." ".$_POST['emonth']." ".$_POST['eyear']." ".$_POST['start_hour'].":".$_POST['start_minute']." ".$_POST['start_ap']);
				$event_start = $event_start - $eventdate;
				$duration = $_POST['end_hour'].":".$_POST['end_minute'];
			}
			if ($_POST['endmonth'] && $_POST['endday'] && $_POST['endyear']) {
				$enddate = strtotime("".$_POST['endday']." ".$_POST['endmonth']." ".$_POST['endyear']."");
			}
			else {
				$enddate = 0;
			}
	
			$area = $_POST['area'];
			$location = $_POST['location'];
	
			if ($_POST['activate'] == "yes") {
				$active = "1";
			}

			$wpdb->insert($events_table, array('event_id'=>0, 'member_id'=>$user_ID, 'event_cat'=>$_POST['event_cat'], 'event_name'=>$_POST['event_name'], 'event_date'=>$eventdate, 'event_start'=>$event_start, 'event_end'=>$duration, 'description'=>$_POST['description'], 'attendees'=>$_POST['attendees'], 'rsvp_type'=>$_POST['sendtype'], 'price'=>$_POST['price'], 'contact_name'=>$_POST['contact_name'], 'contact_email'=>$_POST['contact_email'], 'area'=>$area, 'url'=>$_POST['url'], 'location'=>$location, 'address'=>$_POST['address'], 'city'=>$_POST['city'], 'state'=>$_POST['state'], 'country'=>$_POST['country'], 'zip'=>$_POST['zip'], 'phone'=>$_POST['phone'], 'event_repeat'=>$eventrepeat, 'end_date'=>$enddate, 'post_id'=>0, 'media_url'=>'', 'media_type'=>'', 'media_path'=>'', 'active'=>$active));
			$event_id = $wpdb->insert_id;
	
			echo '<p style="display: block; background: green">Your Event has been added.</p>';
	
	
			if ($_POST['sendtoall']) {
				$UserIDs = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY user_nicename ASC"));
				foreach ( $UserIDs as $userid ) {
					$user = get_userdata( $userid );
					//print_r($user);
					if ($_POST['sendtype'] == 1) {
						$message = "A new event has been posted at ". get_bloginfo( 'name' ) .". Please RSVP at the link below.\r\n";
						if (get_option('alien_event_email_me')) {
							if (get_option('alien_event_email_mel') == 1) {
								$message .= "<p>".$_POST['description']."</p>\r\n";
							}
							else {
								$excerpt = str_split($_POST['description'], get_option('alien_event_email_mel'));
								$message .= "<p>".$excerpt[0]."</p>\r\n";
							}
						}
						$message .= '<a href="' . get_option( 'alien_cal_wpurl' ) . '/?page_id='.get_option('alien_cal_page_id').'&e='. $event_id .'&rsvp=1">View Event and RSVP</a>\r\n';
					}
					else if ($_POST['sendtype'] == 2) {
						$message = "A new event has been posted at ". get_bloginfo( 'name' ) .". This event has a limited number of seats open, Reserve your seat today!\r\n";
						if (get_option('alien_event_email_me')) {
							if (get_option('alien_event_email_mel') == 1) {
								$message .= "<p>".$_POST['description']."</p>\r\n";
							}
							else {
								$excerpt = str_split($_POST['description'], get_option('alien_event_email_mel'));
								$message .= "<p>".$excerpt[0]."</p>\r\n";
							}
						}
						$message .= '<a href="' . get_option( 'alien_cal_wpurl' ) . '/?page_id='.get_option('alien_cal_page_id').'&e='. $event_id .'&rsvp=1">View Event and RSVP</a>\r\n';
					}
					else {
						$message = "A new event has been posted at ". get_bloginfo( 'name' ) .". Click below for details.\r\n";
						if (get_option('alien_event_email_me')) {
							if (get_option('alien_event_email_mel') == 1) {
								$message .= "<p>".$_POST['description']."</p>\r\n";
							}
							else {
								$excerpt = str_split($_POST['description'], get_option('alien_event_email_mel'));
								$message .= "<p>".$excerpt[0]."</p>\r\n";
							}
						}
						$message .= '<a href="' . get_option( 'alien_cal_wpurl' ) . '/?page_id='.get_option('alien_cal_page_id').'&e='. $event_id .'">View Event Details</a>\r\n';
					}
					$mailing = "INSERT INTO ". $mail_table ." SET member_id='" . $user->ID . "', email='" . $user->user_email . "', subject='A new event has been posted at ". get_bloginfo( 'name' ) ."', message='" . $message . "', event_id='$event_id', sent='0'";
					$add_mailer = mysql_query($mailing) or die(mysql_error());
				}
			}
			else {
				if ($_POST['sendto']) {
					foreach ($_POST['sendto'] as $userid) {
						$user = get_userdata( $userid );
						//print_r($user);
						$message = "A new event has been posted at ". get_bloginfo( 'name' ) .". Click below for details.\r\n";
						if (get_option('alien_event_email_me')) {
							if (get_option('alien_event_email_mel') == 1) {
								$message .= "<p>".$_POST['description']."</p>\r\n";
							}
							else {
								$excerpt = str_split($_POST['description'], get_option('alien_event_email_mel'));
								$message .= "<p>".$excerpt[0]."</p>\r\n";
							}
						}
						$message .= '<a href="' . get_option( 'alien_cal_wpurl' ) . '/?page_id='.get_option('alien_cal_page_id').'&e='. $event_id .'">View Event Details</a>\r\n';
						$mailing = "INSERT INTO ". $mail_table ." SET member_id='" . $user->ID . "', email='" . $user->user_email . "', subject='A new event has been posted at ". get_bloginfo( 'name' ) ."', message='" . $message . "', event_id='$event_id', sent='0'";
						$add_mailer = mysql_query($mailing) or die(mysql_error());
					}
				}
			}
	
			if ($_FILES['calMedia']['error'] == 4 || !$_FILES['calMedia']) {  }
			else {
				$calMedia = calImgUpload();
				//print_r($calMedia);
				$wpdb->update($events_table, array('media_url'=>$calMedia['url'], 'media_type'=>$calMedia['type'], 'media_path'=>$calMedia['file']), array('event_id'=>$event_id));
			}
			
			if ($_POST['publish'] == "yes") {
				$post = array(
				  'comment_status' => 'open',
				  'ping_status' => get_option('default_ping_status'),
				  'post_author' => $user_ID,
				  'post_category' => array($_POST['event_cat']), //Add some categories.
				  'post_content' => '<p><a href="' . get_option( 'alien_cal_wpurl' ) . '/?page_id='.get_option('alien_cal_page_id').'&e='.$event_id.'">Event Details</a></p>' . $_POST['description'],
				  'post_excerpt' => '',
				  'post_parent' => 0,
				  'post_status' => 'publish',
				  'post_title' => $_POST['event_name'],
				  'post_type' => 'post',
				  'to_ping' => ''
				);  
	
				$post_id = wp_insert_post( $post );
	
				if ($calMedia) {
					$attachment = array(
						'post_title' => $_POST['event_name'],
						'post_content' => $_POST['description'],
						'post_excerpt' => '',
						'post_status' => 'publish',
						'post_mime_type' => $calMedia['type'],
						'image_alt' => $_POST['event_name']
					);
					$attach_id = wp_insert_attachment( $attachment, $calMedia['file'], $post_id );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $calMedia['file'] );
					wp_update_attachment_metadata( $attach_id,  $attach_data );
				}
				$wpdb->update($events_table, array('post_id'=>$post_id, 'media_id'=>$attach_id), array('event_id'=>$event_id));
			}
		}
		?>	
		<span style="color:red">*</span> denotes a required field.<br /><br />
	
		<table class="widefat page">
			<form name="form" method="post" action="" onSubmit="return checkform()" enctype="multipart/form-data">
			<input type="hidden" name="doaction" value="postnew">
			<thead>
				<tr>
					<th>
						Publishing Options
					</th>
				</tr>
			</thead>
			<input type="hidden" name="activate" value="yes">
			<tr>
				<td>
					Publish as Post?: <input type="checkbox" name="publish" value="yes" />
				</td>
			</tr>
		</table>
	
		<table class="widefat page">
			<thead>
				<tr>
					<th colspan="3">
						Title, Category, Description
					</th>
				</tr>
			</thead>
	
			<tr>
				<td width="3" valign="top">
					<span style="color:red">*</span>
				</td>
				<td colspan="2">
					Title: <span style="font-size: 8px">You have <input readonly type="text" name="countdowntitle" size="1" value="<?php echo get_option( "alien_event_title" ); ?>" style="background:transparent; border:none; font-size:100%" /> characters left.</span>
					<br />
					<input type="text" name="event_name" style="width: 75%;" onKeyDown="limitText(this.form.event_name,this.form.countdowntitle,<?php echo get_option( "alien_event_title" ); ?>);" onKeyUp="limitText(this.form.event_name,this.form.countdowntitle,<?php echo get_option( "alien_event_title" ); ?>);" maxlength="<?php echo get_option( "alien_event_title" ); ?>" />
				</td>
			</tr>
			<tr>
				<td width="3">
					<span style="color:red">*</span>
				</td>
				<td NOWRAP>
					Event Category:
				</td>
				<td>
					<select name="event_cat"> 
						 <?php
							$includes = get_option( "alien_event_cats" );
							foreach ($includes as $value) {
								$findme .= $value.',';
								$subcats = get_categories('parent='. $value . '&hide_empty=0');
								foreach ($subcats as $subvalue) {
									$findme .= $subvalue->term_id.',';
									$gsubcats = get_categories('parent='. $subvalue->term_id . '&hide_empty=0');
									foreach ($gsubcats as $gsubvalue) {
										$findme .= $gsubvalue->term_id.',';
									}
								}
							}
							$categories = get_categories('include=' . $findme . '&hide_empty=0');
							foreach ($categories as $cat) {
							  	$option = '<option value="'.$cat->term_id.'">';
								$option .= $cat->cat_name;
								$option .= '</option>';
								echo $option;
							  }
						 ?>
					</select> (Uses Post Categories)
				</td>
			</tr>
			<tr>
				<td>
					<span style="color:red">*</span>
				</td>
				<td colspan="2">
					Description <span style="font-size: 8px"> You have <input readonly type="text" name="countdowndes" size="1" value="<?php echo get_option( "alien_event_desc" ); ?>" style="background:transparent; border:none; font-size:100%"> characters left.</span>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<textarea name="description" style="width: 100%;" onKeyDown="limitText(this.form.description,this.form.countdowndes,<?php echo get_option( "alien_event_desc" ); ?>);" onKeyUp="limitText(this.form.description,this.form.countdowndes,<?php echo get_option( "alien_event_desc" ); ?>);" rows="5"></textarea>
				</td>
			</tr>
		</table>
		<table class="widefat page">
			<thead>
				<tr>
					<th colspan="3">
						Event Details
					</th>
				</tr>
			</thead>
			<tr>
				<td>
					Online Event?:
				</td>
				<td>
					<input type="checkbox" name="area" value="Online" />
				</td>
			</tr>
			<tr>
				<td>
					URL:
				</td>
				<td>
					<input type="text" name="url" />
				</td>
			</tr>
			<tr>
				<td>
					Location:
				</td>
				<td>
					<input type="text" name="location" />
				</td>
			</tr>
			<tr>
				<td>
					Contact Name:
				</td>
				<td>
					<input type="text" name="contact_name" />
				</td>
			</tr>
			<tr>
				<td>
					Contact Email:
				</td>
				<td>
					<input type="text" name="contact_email" />
				</td>
			</tr>
			<tr>
				<td>
					Address:
				</td>
				<td>
					<input type="text" name="address" />
				</td>
			</tr>
			<tr>
				<td>
					City:
				</td>
				<td>
					<input type="text" name="city" />
				</td>
			</tr>
			<tr>
				<td>
					State/Province:
				</td>
				<td>
					<select name="state">
					<?php
						if (get_option('alien_event_international') == "us") {
							state_options();
						}
						else {
							$x = explode(",", get_option('alien_event_provinces'));
							foreach ($x as $province) {
								echo '<option value="'.$province.'">'.$province.'</option>';
							}
						}
					?>
					</select>
				</td>
			</tr>
			<?php
				if (get_option('alien_event_international') == "us") {
					echo '<input type="hidden" name="country" value="United States" />';
				}
				else {
					?>
					<tr>
						<td>
							Country:
						</td>
						<td>
							<select name="country">
								<?php
								$x = explode(",", get_option('alien_event_countries'));
								foreach ($x as $country) {
									echo '<option value="'.$country.'">'.$country.'</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<?php
				}
				?>
			<tr>
				<td>
					Zip:
				</td>
				<td>
					<input type="text" name="zip" maxlength="10" size="5" />
				</td>
			</tr>
			<tr>
				<td>
					Phone:
				</td>
				<td>
					<?php
					if (get_option('alien_event_autophone') == "yes") {
						?>
							<input type="text" id="phone" name="phone" maxlength="13" onKeyUp="javascript:getIt(this)" />
						<?php
					}
					else {
						?>
							<input type="text" id="phone" name="phone" />
						<?php
					}
					?>
					<span style="font-size: smaller"> (Numbers only, field will auto format) </span>
				</td>
			</tr>
			<tr>
				<td>
					Price:
				</td>
				<td>
					<input type="text" name="price" onBlur="this.value=formatCurrency(this.value);" /> 
					<span style="font-size:smaller"> (Price will auto format to &#36;xx.00) </span>
				</td>
			</tr>
		</table>
		<table class="widefat page">
			<thead>
				<tr>
					<th colspan="3">
						Media
					</th>
				</tr>
			</thead>
	
			<input type="hidden" name="MAX_FILE_SIZE" value="200000">
			<tr>
				<td>
					Attach Media: 
				</td>
				<td>
					<input id="calMedia" type="file" name="calMedia" />
				</td>
			</tr>
		</table>
		<table class="widefat page">
			<thead>
				<tr>
					<th colspan="3">
						Schedule
					</th>
				</tr>
			</thead>
			<tr>
				<td valign="top">
					Reoccur:
				</td>
				<td>
					<input type="radio" name="repeaton" value="noday" checked="checked" /> 
					<select name="repeat">
						<option value="once">Never</option>
						<option value="daily">Daily</option>
						<option value="weekly">Weekly</option>
						<option value="monthly">Monthly</option>
						<option value="yearly">Yearly</option>
					</select>
					<br />
					<input type="radio" name="repeaton" value="thisday" /> 
					On the 
					<select name="repeatwhat">
						<option value="first">First</option>
						<option value="second">Second</option>
						<option value="third">Third</option>
						<option value="fourth">Fourth</option>
						<option value="last">Last</option>
					</select> 
					<select name="repeatday">
						<option value="sunday">Sun</option>
						<option value="monday">Mon</option>
						<option value="tuesday">Tue</option>
						<option value="wednesday">Wed</option>
						<option value="thursday">Thu</option>
						<option value="friday">Fri</option>
						<option value="saturday">Sat</option>
					</select> of every month.
				</td>
			</tr>
			<tr>
				<td>
					Start Date:
				</td>
				<td>
					<select name="emonth">
						<?php
						$month = date('F');
						?>
						<option value="<?php echo $month; ?>"><?php echo $month; ?></option>
						<option value="January">January</option>
						<option value="February">February</option>
						<option value="March">March</option>
						<option value="April">April</option>
						<option value="May">May</option>
						<option value="June">June</option>
						<option value="July">July</option>
						<option value="August">August</option>
						<option value="September">September</option>
						<option value="October">October</option>
						<option value="November">November</option>
						<option value="December">December</option>
					</select>
					<select name="eday">
						<?php
						$day = date('j'); 
						?>
						echo "<option value="<?php echo $day; ?>"><?php echo $day; ?></option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
						<option value="24">24</option>
						<option value="25">25</option>
						<option value="26">26</option>
						<option value="27">27</option>
						<option value="28">28</option>
						<option value="29">29</option>
						<option value="30">30</option>
						<option value="31">31</option>
					</select>
					<select name="eyear">
						<?php
						$years = 0;
						$thisyear = date('Y', strtotime("now"));
		
						while ($years < 7) {
							echo "<option value=\"$thisyear\">$thisyear</option>";
							$thisyear++;
							$years++;
						}
						?>
		
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top">
					End Date: 
				</td>
				<td>
					<select name="endmonth">
						<option value="0"></option>
						<option value="January">January</option>
						<option value="February">February</option>
						<option value="March">March</option>
						<option value="April">April</option>
						<option value="May">May</option>
						<option value="June">June</option>
						<option value="July">July</option>
						<option value="August">August</option>
						<option value="September">September</option>
						<option value="October">October</option>
						<option value="November">November</option>
						<option value="December">December</option>
					</select>
					<select name="endday">
						<option></option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
						<option value="24">24</option>
						<option value="25">25</option>
						<option value="26">26</option>
						<option value="27">27</option>
						<option value="28">28</option>
						<option value="29">29</option>
						<option value="30">30</option>
						<option value="31">31</option>
					</select>
					<select name="endyear">
						<option></option>
						<?php
						$years = 0;
						$thisyear = date('Y', strtotime("now"));
						while ($years < 7) {
							echo "<option value=\"$thisyear\">$thisyear</option>";
							$thisyear++;
							$years++;
						}
						?>
					</select>
					<br /><span style="font-size: smaller">(Leave end date blank for non-ending or non-reoccurring events)</span>
				</td>
			</tr>
			<tr>
				<td valign="top">
					Time:
				</td>
				<td>
					<table border=0>
						<tr>
							<td width="10">
								<input type="radio" name="allday" value="yes" />
							</td>
							<td colspan="2">
								All Day Event
							</td>
						</tr>
						<tr>
							<td>
								<input type="radio" name="allday" value="no" checked="checked" />
							</td>
							<td NOWRAP>
								Starts at
							</td>
							<td>
								<select name="start_hour">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
								</select>
								<select name="start_minute">
									<option value="00">00</option>
									<option value="15">15</option>
									<option value="30">30</option>
									<option value="45">45</option>
								</select>
								<select name="start_ap">
									<option value="AM">AM</option>
									<option value="PM">PM</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td width="60">
								Duration:
							</td>
							<td>
								<select name="end_hour">
									<option value="0">0 hr</option>
									<option value="1">1 hr</option>
									<option value="2">2 hrs</option>
									<option value="3">3 hrs</option>
									<option value="4">4 hrs</option>
									<option value="5">5 hrs</option>
									<option value="6">6 hrs</option>
									<option value="7">7 hrs</option>
									<option value="8">8 hrs</option>
									<option value="9">9 hrs</option>
									<option value="10">10 hrs</option>
									<option value="11">11 hrs</option>
									<option value="12">12 hrs</option>
								</select>
								<select name="end_minute">
									<option value="00">0 mins</option>
									<option value="15">15 mins</option>
									<option value="30">30 mins</option>
									<option value="45">45 mins</option>
								</select>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<table class="widefat page">
			<thead>
				<tr>
					<th colspan="3">
						Notification Options
					</th>
				</tr>
			</thead>
			<tr>
				<td>
					RSVP?: <input type="radio" name="sendtype" value="1" /> 
					RSVP With Guest Limit?: <input type="radio" name="sendtype" value="2" /> 
					Invitation: <input type="radio" name="sendtype" value="3" checked="checked" />
				</td>
			</tr>
			<tr>
				<td>
					Guest Limit?: <input type="text" name="attendees" />
				</td>
			</tr>
			<tr>
				<td>
					Send To The Following Members: <input type="checkbox" name="sendtoall" value="1" /> ALL 
				</td>
			</tr>
			<tr>
				<td>
					<div style="width: 100%; height:200px; overflow: auto">
						<?php
							$UserIDs = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY user_nicename ASC"));
							foreach ( $UserIDs as $userid ) {
								$user = get_userdata( $userid );
								//print_r($user);
								echo '<input type="checkbox" name="sendto[]" value="' . $user->ID . '" /> ' . $user->display_name . ' ';
							}
						?>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="submit" value="Post Event" />
				</td>
			</tr>
		</table>
		</form>
	<?php
	}
}


function state_options() {
	echo "
	<option value=\"AK\">Alaska</option>
	<option value=\"AL\">Alabama</option>
	<option value=\"AR\">Arkansas</option>
	<option value=\"AZ\">Arizona</option>
	<option value=\"CA\">California</option>
	<option value=\"CO\">Colorado</option>
	<option value=\"CT\">Connecticut</option>
	<option value=\"DC\">District of Columbia</option>
	<option value=\"DE\">Delaware</option>
	<option value=\"FL\">Florida</option>
	<option value=\"GA\">Georgia</option>
	<option value=\"HI\">Hawaii</option>
	<option value=\"IA\">Iowa</option>
	<option value=\"ID\">Idaho</option>
	<option value=\"IL\">Illinois</option>
	<option value=\"IN\">Indiana</option>
	<option value=\"KS\">Kansas</option>
	<option value=\"KY\">Kentucky</option>
	<option value=\"LA\">Louisiana</option>
	<option value=\"MA\">Massachusetts</option>
	<option value=\"MD\">Maryland</option>
	<option value=\"ME\">Maine</option>
	<option value=\"MI\">Michigan</option>
	<option value=\"MN\">Minnesota</option>
	<option value=\"MO\">Missouri</option>
	<option value=\"MS\">Mississippi</option>
	<option value=\"MT\">Montana</option>
	<option value=\"NC\">North Carolina</option>
	<option value=\"ND\">North Dakota</option>
	<option value=\"NE\">Nebraska</option>
	<option value=\"NH\">New Hampshire</option>
	<option value=\"NJ\">New Jersey</option>
	<option value=\"NM\">New Mexico</option>
	<option value=\"NV\">Nevada</option>
	<option value=\"NY\">New York</option>
	<option value=\"OH\">Ohio</option>
	<option value=\"OK\">Oklahoma</option>
	<option value=\"OR\">Oregon</option>
	<option value=\"PA\">Pennsylvania</option>
	<option value=\"PR\">Puerto Rico</option>
	<option value=\"RI\">Rhode Island</option>
	<option value=\"SC\">South Carolina</option>
	<option value=\"SD\">South Dakota</option>
	<option value=\"TN\">Tennessee</option>
	<option value=\"TX\">Texas</option>
	<option value=\"UT\">Utah</option>
	<option value=\"VA\">Virginia</option>
	<option value=\"VT\">Vermont</option>
	<option value=\"WA\">Washington</option>
	<option value=\"WI\">Wisconsin</option>
	<option value=\"WV\">West Virginia</option>
	<option value=\"WY\">Wyoming</option>";
}

register_activation_hook( __FILE__, 'cal_install');

register_deactivation_hook( __FILE__, 'cal_deactivate');

register_uninstall_hook( __FILE__, 'cal_uninstall');

add_shortcode('event-cal-page', 'event_cal_page');
add_shortcode('event-cal-mini', 'widget_EventsCalendar');

        
function event_cal_page() {
	global $wpdb, $post;

	$event_table = $wpdb->prefix . "alevents";
	$mail_table = $wpdb->prefix . "almailer";
	?>
	<style>
	<?php

	if(get_option('alien_event_new_layout') == "picker") {
		$newstyles = get_option('alien_event_new_styles');

		foreach ($newstyles AS $k => $v) {
			$k = str_replace("'", "", stripslashes($k));
			echo '#'.$k.' {';

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
	$date = $_GET['d'];
	$eventid = $_GET['e'];
	if (!$_GET['fc'] && !$_GET['e']) {
		$pagev = get_option('alien_event_display');
	}
	else {
		$pagev = $_GET['fc'];
	}
	$user = wp_get_current_user();

	if ($_GET['t'] == "rsvp") {
		if ( is_user_logged_in() ) {
			$getby = $_GET['eid'];
			$rsvpq = mysql_query("SELECT sent FROM " . $mail_table . " WHERE member_id='" . $user->ID . "' && event_id='" . $getby . "'");
			$rsvpcount = mysql_num_rows($rsvpq);
			if ($rsvpcount) {
				$rsvpinfo = mysql_fetch_array($rsvpq);
				if ($rsvpinfo['sent'] != "4") {
					mysql_query("UPDATE " . $mail_table . " SET sent='4' WHERE member_id='" . $user->ID . "' && event_id='" . $getby . "'");
					mysql_query("UPDATE " . $event_table . " SET rsvp_count=(rsvp_count+1) WHERE event_id='" . $getby . "'");
				}
				else {
					echo "".__('You have already RSVPd for this event','AEC').".";
				}
			}
			else {
				echo "".__('You were not invited to this event','AEC').".";
			}
		}
		else {
			echo "".__('You must be logged in to RSVP','AEC').".";
		}
	}

	if (($pagev == 1 || $pagev == "full") && !$_GET['d']) {
		include 'classes/FullCal.php';
	}
	else if (!$eventid) {
		if ($_GET) {
			$url = "";
			$firstq = 0;
			foreach($_GET AS $key => $value) {
				if ($firstq == 0) {
					$firstq++;
					$url .= "?";
					if ($key != "evcatid" && $key != "p") {
					      	$url .= $key."=".$value;
					}
				}
				else {
					if ($key != "evcatid" && $key != "p") {
						$url .= "&";
					      	$url .= $key."=".$value;
					}
				}
			}
			if ($firstq > 0) {
				$url .= "&page_id=".get_option('alien_cal_page_id')."&evcatid=";
			}
			else {
				$url .= "?page_id=".get_option('alien_cal_page_id')."&evcatid=";
			}
		}
		else {
			$url = "?evcatid=";
		}
		?>
		<div id="fullCalLink"><a href="<?php echo get_option( 'alien_cal_wpurl' ); ?>/?page_id=<?php echo get_option('alien_cal_page_id'); ?>&fc=1"><?php echo __('Full Calendar','AEC'); ?></a></div>
		<?php

		echo '<div id="eventCatList">';
		$includes = get_option( "alien_event_cats" );
		if ($includes) {
			foreach ($includes as $value) {
				$findme .= $value.',';		
			}
			$categories = get_categories('include=' . $findme . '&parent=0&hide_empty=0');
			if (get_option(alien_event_cat_disp) == "list") {
				foreach ($categories as $cat) {
					echo '<ul id="catMainUL">';
					echo '<li id="catMainList"><a href="' . get_option( 'alien_cal_wpurl' ) . '/'.$url.''.$cat->term_id .'"> '.$cat->cat_name.'</a></li>';
					$subcats = get_categories('parent='.$cat->term_id.'&hide_empty=0');
					foreach ($subcats as $subs) {
						echo '<li><ul id="catSubUL">';
						echo '<li id="catSubList"><a href="' . get_option( 'alien_cal_wpurl' ) . '/'.$url.''.$subs->term_id .'"> '.$subs->cat_name.'</a></li>';
						$grands = get_categories('parent='.$subs->term_id.'&hide_empty=0');
						foreach ($grands as $child) {
							echo '<li><ul id="catChildUL">';
							echo '<li id="catChildList"><a href="' . get_option( 'alien_cal_wpurl' ) . '/'.$url.''.$child->term_id .'"> '.$child->cat_name.'</a></li>';
							echo '</ul></li>';
						}
						echo '</ul></li>';
					}
					echo '</ul>';
				}
			}
			else if (get_option(alien_event_cat_disp) == "select") {
				echo '<form method="get" action="">';
				echo '<input type="hidden" name="p" value="'.get_option('alien_cal_page_id').'">';
				echo '<input type="hidden" name="month" value="'.$_GET['month'].'">';
				echo '<select name="evcatid" onchange="this.form.submit()"><option>'.__("Select Category","AEC").'</option>';
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
	
		$date = strtotime(date('d F Y', strtotime("NOW")));
		if ($_GET['d']) {
			$date = $_GET['d'];
		}
	
		$nextmonth = strtotime(date('d F Y', strtotime("+1 month", $date)));
	
		if ($_GET['evcatid']) {
			$event_list = mysql_query("SELECT * FROM ".$event_table." WHERE (event_cat = '".$_GET['evcatid']."' && (active='1' || (active='Private' && member_id='$user->ID'))) ORDER BY event_date, event_start ASC");
		}
		else {
			$event_list = mysql_query("SELECT * FROM ".$event_table." WHERE (active='1' || (active='Private' && member_id='$user->ID')) ORDER BY event_date, event_start ASC");
		}
				
		$todaycount = mysql_num_rows($event_list);
				
		if (get_option('alien_event_list_date') == "current" || $_GET['d']) {
			$today = date('d', $date)." ".__(date('F', $date), 'AEC')." ".date('Y', $date);
			echo '<div id="eventDateHead">' . $today . '</div>';
		}
		else {
			$today = "";
		}
	
		$shownumber = get_option('alien_event_list_number');
		$eventcount = 0;
	
		if (!$todaycount) {
			echo "".__('There are no events scheduled for today','AEC')."";
		}
		else {
			$none = 0;
			$options = array("once", "daily", "weekly", "monthly", "yearly");
			echo "<div id=\"eventsList\">";
							
			while ($event_row = mysql_fetch_array($event_list)) {
				$display = 0;
	
				if (get_option('alien_event_list_date') == "current" || $_GET['d']) {
					if ($event_row['event_repeat'] == "daily" && ($event_row['end_date'] >= $date || $event_row['end_date'] == 0) && $event_row['event_date'] <= $date) {
						$display = 1; $none = 1;
					}
					elseif ($event_row['event_repeat'] == "weekly" && date('D', $event_row['event_date']) == date('D', $date) && ($event_row['end_date'] >= $date || $event_row['end_date'] == 0)) {
						$display = 1; $none = 1;
					}
					elseif ($event_row['event_repeat'] == "monthly" && date('d', $event_row['event_date']) == date('d', $date) && ($event_row['end_date'] >= $date || $event_row['end_date'] == 0)) {
						$display = 1; $none = 1;
					}
					elseif ($event_row['event_repeat'] == "yearly" && date('d F', $event_row['event_date']) == date('d F', $date) && ($event_row['end_date'] >= $date || $event_row['end_date'] == 0)) {
						$display = 1; $none = 1;
					}
					elseif ($event_row['event_date'] == $date && in_array($event_row['event_repeat'], $options)) {
						$display = 1; $none = 1;
					}
					else {
						if (!in_array($event_row['event_repeat'], $options)) {
							$first = date('d', mktime(0, 0, 0, date('m', $date), 0, date('Y', $date)));
							if ($first > 1) {
								$thisdate = strtotime("".$event_row['event_repeat']."" , mktime(0, 0, 0, date('m', $date), 1, date('Y', $date)));
							}
							else {
								$thisdate = strtotime("".$event_row['event_repeat']."" , mktime(0, 0, 0, date('m', $date), 0, date('Y', $date)));
							}
							if ($thisdate == $date) {
								$display = 1; $none = 1;
							}
						}
					}
				}
				else {
					if ($eventcount <= $shownumber) {
						$display = 1;
						$none = 1;
						if (get_option('alien_event_list_date') == "all") {
							if ($today != date('d F Y', $event_row['event_date'])) {
								$today = date('d F Y', $event_row['event_date']);
								echo '<div id="eventDateHead">' . $today . '</div>';
							}
						}
					}
				}
				if ($display) {
					++$eventcount;
					echo '<div id="eventBlock">';
					echo '<div id="eventOptions">';
					echo '<span><a id="btnLink" href="'.get_option('alien_cal_wpurl').'/?page_id='.get_option('alien_cal_page_id').'&e='.$event_row['event_id'].'">'.__('More','AEC').'</a></span>';
					echo "</div>";
					echo '<div id="eventDetails">';
					echo "<span id=\"myEventTitle\">".$event_row['event_name']."</span> ";
					echo "<span id=\"myEventLocationTitle\">".__('Location','AEC').":</span> <span id=\"myEventLocation\">".$event_row['location']."</span> ";
					if ($event_row['event_end'] == "All Day") {
						echo "<span id=\"myEventTimeTitle\">".__('Time','AEC').":</span> <span id=\"myEventTime\">".$event_row['event_end']."</span>";
					}
					else {
						$starttime = $event_row['event_date'] + $event_row['event_start'];
						echo "<span id=\"myEventTimeTitle\">".__('Time','AEC').":</span> <span id=\"myEventTime\">".date('h:i A', $starttime)."</span> ";
					}
					$options = array("once", "daily", "weekly", "monthly", "yearly");
					if (!in_array($event_row['event_repeat'], $options)) {
						echo "<br><span id=\"myEventTimeTitle\">".__('Repeats','AEC').":</span> <span id=\"myEventTime\"> ".__($event_row['event_repeat'],'AEC')." ".__('of every month','AEC')."</span>";
						if ($event_row['end_date']) {
							echo" <span id=\"myEventTimeTitle\">".__('Until','AEC').":</span> <span id=\"myEventTime\"> ".date('d F Y', $event_row['end_date'])." </span>";
						}
					}
					else if ($event_row['event_repeat'] == "once") {}
						else if ($event_row['event_repeat'] == "daily") {
						echo "<br><span id=\"myEventTimeTitle\">".__('Repeats','AEC').":</span> <span id=\"myEventTime\"> ".__('Daily','AEC')." </span>";
						if ($event_row['end_date']) {
							echo" <span id=\"myEventTimeTitle\">".__('Until','AEC').":</span> <span id=\"myEventTime\"> ".date('d', $event_row['end_date'])." ".__(date('F', $event_row['end_date']),'AEC')." ".date('Y', $event_row['end_date'])." </span>";
						}
					}
					else if ($event_row['event_repeat'] == "weekly") {
						echo "<br><span id=\"myEventTimeTitle\">".__('Repeats','AEC').":</span> <span id=\"myEventTime\"> ".__('Weekly','AEC')." </span>";
						if ($event_row['end_date']) {
							echo" <span id=\"myEventTimeTitle\">".__('Until','AEC').":</span> <span id=\"myEventTime\"> ".date('d', $event_row['end_date'])." ".__(date('F', $event_row['end_date']),'AEC')." ".date('Y', $event_row['end_date'])." </span>";
						}
					}
					else if ($event_row['event_repeat'] == "monthly") {
						echo "<br><span id=\"myEventTimeTitle\">".__('Repeats','AEC').":</span> <span id=\"myEventTime\"> ".__('Monthly','AEC')." </span>";
						if ($event_row['end_date']) {
							echo" <span id=\"myEventTimeTitle\">".__('Until','AEC').":</span> <span id=\"myEventTime\"> ".date('d', $event_row['end_date'])." ".__(date('F', $event_row['end_date']),'AEC')." ".date('Y', $event_row['end_date'])." </span>";
						}
					}
					else if ($event_row['event_repeat'] == "yearly") {
						echo "<br><span id=\"myEventTimeTitle\">".__('Repeats','AEC').":</span> <span id=\"myEventTime\"> ".__('Yearly','AEC')." </span>";
						if ($event_row['end_date']) {
							echo" <span id=\"myEventTimeTitle\">".__('Until','AEC').":</span> <span id=\"myEventTime\"> ".date('d', $event_row['end_date'])." ".__(date('F', $event_row['end_date']),'AEC')." ".date('Y', $event_row['end_date'])." </span>";
						}
					}
					echo "</div>";
					echo "<div style=\"clear:both\"></div>";
					echo "</div>";
				}
			}
			if (!$none) {
				$none = 1;
				echo "".__('There are no events scheduled for today','AEC')."";
			}
			echo "</div>";
		}
	}
	else {
		$user = wp_get_current_user();
		$query = mysql_query("SELECT * FROM ".$event_table." WHERE event_id ='".$eventid."' LIMIT 1");
		$event_row = mysql_fetch_array($query);
		if ($event_row['active'] == "Private" && $event_row['member_id'] != "$user->ID") {
			echo "This is a private event.";
		}
		else {
			$counter = $event_row['counter'];
			++$counter;
			mysql_query("UPDATE ".$event_table." SET counter='".$counter."' WHERE event_id='".$eventid."' LIMIT 1");
				echo '<div id="eventMedia">';
				if ($event_row['media_url']) {
					echo event_cal_media($event_row['media_url'], $event_row['media_type']);
				}
			echo '</div>';
				echo "<div id=\"eventHeader\"><h1><a name=\"sub\">";
				echo htmlspecialchars(stripslashes($event_row['event_name']), ENT_QUOTES);
			echo "</a></h1></div>";
	
			echo "<div id=\"eventBlock\">";

			$category = get_categories('include='.$event_row['event_cat'].'');
			foreach ($category as $cat) {
				echo "<h3>$cat->cat_name</h3>";
			}
			if ( is_user_logged_in() ) {
				echo '<div id="eventOptions">';
				if ($event_row['post_id']) {
					echo "<span><a id=\"btnLink\" href=\"".get_option('alien_cal_wpurl')."/?p=".$event_row['post_id']."\">".__('Comments','AEC')."</a></span>";
				}
				if ($event_row['rsvp_type'] != "3") {
					$rsvpq = mysql_query("SELECT sent FROM ".$mail_table." WHERE member_id='" . $user->ID . "' && event_id='" . $eventid . "'") or die(mysql_error());
					$rsvpcount = mysql_num_rows($rsvpq);
					if ($rsvpcount) {
						$rsvpinfo = mysql_fetch_array($rsvpq);
						if ($rsvpinfo['sent'] != "4") {
							if ($event_row['rsvp_type'] == "2" && $event_row['rsvp_count'] < $event_row['attendees']) {
								echo "<span><a id=\"btnLink\" href=\"".get_option('alien_cal_wpurl')."/?page_id=".get_option('alien_cal_page_id')."&e=$eventid&eid=$eventid&t=rsvp\">".__('RSVP','AEC')."</a></span>";
							}
							else {
								echo "<span><a id=\"btnLink\" href=\"".get_option('alien_cal_wpurl')."/?page_id=".get_option('alien_cal_page_id')."&e=$eventid&eid=$eventid&t=rsvp\">".__('RSVP','AEC')."</a></span>";
							}
						}
					}
				}
				echo '</div>';
			}
			echo "<p>$eday ";
			if ($event_row['active'] != "Private") {
				if ($event_row['area'] == "Online") { echo "".__('Online Event','AEC')."<br>"; }
				if ($event_row['url']) { echo '<span id="myEventTitle" class="myEventTitle">'.__('Website','AEC').':</span> <a href="'.$event_row['url'].'" target="_blank">'.$event_row['url'].' </a><br>'; }
				if ($event_row['location']) { 

					$pos = strpos($event_row['location'], 'http:');

					if($pos === false) {
						echo "<span id=\"myEventTitle\" class=\"myEventTitle\">" . __('Location','AEC') . ":</span> " . $event_row['location'] . " <br>";
					}
					else {
						echo "<a href=\"" . $event_row['location'] . "\" target=\"_blank\">" . __('Location','AEC') . ": " . $event_row['location'] . " </a><br>"; 
					}

				}
			}
			if ($event_row['event_end'] != "All Day") {
				$starttime = $event_row['event_date'] + $event_row['event_start'];
				echo '<span id="myEventTitle" class="myEventTitle">'.__('Start','AEC').':</span> '.date('h:i A', $starttime).' ';
			}
	
			echo '<span id="myEventTitle" class="myEventTitle">Duration:</span> '.$event_row['event_end'].'</p>';
			echo '<span id="myEventTitle" class="myEventTitle">';
			echo '<span id="myEventTitle" class="myEventTitle">'.__('Repeats','AEC').':</span> ';
			$options = array("once", "daily", "weekly", "monthly", "yearly");
			if (!in_array($event_row['event_repeat'], $options)) {
				echo " ".__('On the','AEC')." ".__($event_row['event_repeat'],'AEC')." ".__('of every month','AEC')."";
			}
			else {
				echo __($event_row['event_repeat'],'AEC');
			}
			if ($event_row['end_date']) {
				echo " ".__('until','AEC')." ".date('d', $event_row['end_date'])." ".__(date('F', $event_row['end_date']),'AEC')." ".date('Y', $event_row['end_date']);
			}
			echo "</span> ";
	
			$filtertext = stripslashes($event_row['description']);

			$eventtext = apply_filters('the_content', $filtertext);
			
			echo "<p>$eventtext</p>";
	
			if ($event_row['active'] != "Private") {
				if ($event_row['rsvp_type'] != "3") {
					if ($event_row['rsvp_type'] == "2") {
						echo '<p><span id="myEventTitle" class="myEventTitle">'.__("RSVPs","AEC").':</span> '.$event_row['rsvp_count'].' '.__("out of","AEC").' '.$event_row['attendees'].' '.__("available","AEC").'.</p>';
					}
					else {
						echo '<p><span id="myEventTitle" class="myEventTitle">'.__("RSVPs",'AEC').':</span> '.$event_row['rsvp_count'].'</p>';
					}					
				}
				if ($event_row['price']) {
					if ($event_row['price'] == "$0.00") {
						echo '<p><span  id="myEventTitle" class="myEventTitle">'.__('Price','AEC').':</span> '.__('Free','AEC').'</p>';
					}
					else {
						echo '<p><span  id="myEventTitle" class="myEventTitle">'.__('Price','AEC').':</span> '.$event_row['price'].'</p>';
					}
				}
			}
			if ($event_row['address'] || $event_row['city'] || $event_row['zip'] ) {
				echo '<p><span  id="myEventTitle" class="myEventTitle">'.__('Address','AEC').':</span>';
				if ($event_row['address']) {
					echo " ".$event_row['address']."";
				}
				if ($event_row['city']) {
					echo " ".$event_row['city']."";
				}
				if ($event_row['state']) {
					echo " ".$event_row['state']."";
				}
				if ($event_row['zip']) {
					echo " ".$event_row['zip']."";
				}
				if ($event_row['country']) {
					echo " ".$event_row['country']."";
				}
				echo "</p>";
			}
			echo '<p><span  id="myEventTitle" class="myEventTitle">'.__('Contact','AEC').':</span> '.$event_row['contact_name'].' '.$event_row['contact_email'].' '.$event_row['phone'].'</p>';
			if ($event_row['address'] || $event_row['city'] || $event_row['zip']) {
				$address = str_replace(" ", "+", $event_row['address']);
				$city = str_replace(" ", "+", $event_row['city']);
				$state = str_replace(" ", "+", $event_row['state']);
				$zip = str_replace(" ", "+", $event_row['zip']);
				$country = str_replace(" ", "+", $event_row['country']);
				echo '<iframe width="300" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;q='.$address.',+'.$city.',+'.$state.'+'.$zip.',+'.$country.'&amp;ie=UTF8&amp;cd=1&amp;geocode=&amp;split=0&amp;hq=&amp;hnear='.$address.',+'.$city.',+'.$state.'+'.$zip.',+'.$country.'&amp;output=embed"></iframe><br /><small><a href="http://maps.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;q='.$address.',+'.$city.',+'.$state.'+'.$zip.',+'.$country.'&amp;ie=UTF8&amp;cd=1&amp;geocode=&amp;split=0&amp;hq=&amp;hnear='.$address.',+'.$city.',+'.$state.'+'.$zip.',+'.$country.'" style="color:#0000FF;text-align:left">View Larger Map</a></small>';
			}
		echo "</div>";
		}
	}
}

function event_cal_media($url, $type) {
	if ($type == "application/x-shockwave-flash") {
		list($width, $height, $type, $attr) = getimagesize($url);
		$media .= '
		<!--[if IE]><!-->
		<object data="'.$url.'" type="application/x-shockwave-flash"
		classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
		codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,115,0"
		width="'.$width.'" height="'.$height.'">
		<!--<! [endif]-->
		<!--[if !IE]><!-->
		<object data="'.$url.'" type="application/x-shockwave-flash"
		width="'.$width.'" height="'.$height.'">
		<!--<! [endif]-->
		<param name="movie" value="'.$url.'">
		<param name="quality" value="high">
		<param name="menu" value="false">
		</object>			';
	}
	else {
		$media .= '<img src="'.$url.'">';
	}
	return $media;
}


	function cal_install() {
		global $wpdb, $user_ID;
	
		add_option('alien_cal_wpurl', get_bloginfo('wpurl'));
		add_option('alien_cal_page_update', '1');

		$pageexists = mysql_query("SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_name LIKE 'cal-events%' LIMIT 1");

		if (mysql_num_rows($pageexists) == 0) {
			$post = array(
			  'comment_status' => 'open',
			  'ping_status' => 'open',
			  'post_author' => $user_ID,
			  'post_content' => '[event-cal-page]',
			  'post_name' => 'cal-events',
			  'post_status' => 'publish',
			  'post_title' => __('Event Calendar', 'cal-events'),
			  'post_excerpt' => '',
			  'post_type' => 'page',
			);  
			$pageid = wp_insert_post( $post );
		}
		else {
			$pageid = mysql_result($pageexists, 0);
		}
		if (!get_option('alien_cal_page_id')) {
			add_option('alien_cal_page_id', $pageid);
		}
		else {
			if (get_option('alien_cal_page_update') == 1) {
				update_option('alien_cal_page_id', $pageid);
			}
		}

		$alien_cal_ver = ALIEN_CAL_VER;
		add_option("alien_cal_sent", strtotime("NOW"));
		$defaultCalStyle = '#evWidgetUL {
		}
		#evWidgetDate {
		}
		
		#evWidgetLI {
		}
		.popupFC {
		    display: none;
		    position:absolute;
		    border-style: solid;
		    background-color: white;
		    color: black;
		    padding: 2px
		}
		#cal {
		     display: block;
		     width: 260px;
		     background: black;
		     overflow: hidden
		}
		#calTable {
		     display: block;
		     width: 260px;
		     background: black
		}
		#tableTR {
		     display: block;
		     width: 260px;
		     float: left
		}
		.calBlank {
		     border: 1px solid black;
		     display: block;
		     float: left;
		     width: 35px;
		     height: 35px;
		}
		#calTH {
		     display: block;
		     width:260px;
		     text-align: center;
		     background: #404040
		}
		#calMonth {
		     color: white;
		     padding: 0px 5px 0px 5px
		}
		#calYear {
		     color: yellow;
		     padding: 0px 5px 0px 5px
		}
		#calNext {
		     border: 1px solid white;
		     display: block;
		     float: right;
		     background: black;
		     width: 20px;
		     text-align: center
		}
		
		#calPrev {
		     border: 1px solid white;
		     display: block;
		     float: left;
		     background: black;
		     width: 20px;
		     text-align: center
		}
		
		.calDW {
		     border: 1px solid black;
		     display: block;
		     width: 35px;
		     height: 35px;
		     line-height: 300%;
		     float: left;
		     text-align: center;
		     font-weight: bold
		}
		
		.calDay {
		     border: 1px solid black;
		     display: block;
		     width: 35px;
		     height: 35px;
		     line-height: 300%;
		     float: left;
		     text-align: center;
		     background: #303030
		}
		
		#calTActive {
		     border: 1px solid black;
		     display: block;
		     width: 35px;
		     height: 35px;
		     line-height: 300%;
		     float: left;
		     text-align: center;
		     background: black
		}
		
		#calTActive a {
		     display: block;
		     width: 35px;
		     height: 35px;
		     line-height: 300%;
		     float: left;
		     font-weight: bold;
		     text-align: center;
		     color: yellow
		}
			
		.calActive {
		     border: 1px solid black;
		     display: block;
		     width: 35px;
		     height: 35px;
		     line-height: 300%;
		     float: left;
		     text-align: center;
		     background: #c0c0c0
		}
		
		.calActive a {
		     display: block;
		     width: 35px;
		     height: 35px;
		     line-height: 300%;
		     float: left;
		     text-align: center;
		     color: black;
		     font-weight: bold
		}
		
		#calToday {
		     border: 1px solid black;
		     display: block;
		     width: 35px;
		     height: 35px;
		     line-height: 300%;
		     float: left;
		     text-align: center;
		     background: #999999
		}
		.fullCalLink {
		}
		#calFC {
		     display: block;
		     width: 644px;
		     background: black;
		     overflow: hidden
		}
		
		#calTableFC {
		     display: block;
		     width: 644px;
		     background: black
		}
		
		#tableTRFC {
		     display: block;
		     width: 644px;
		     float: left
		}
			
		.calBlankFC {
		     border: 1px solid black;
		     display: block;
		     float: left;
		     width: 90px;
		     height: 90px;
		}
			
		#calTHFC {
		     display: block;
		     width:644px;
		     text-align: center;
		     background: #404040
		}
		
		#calMonthFC {
		     color: white;
		     padding: 0px 5px 0px 5px
		}
		
		#calYearFC {
		     color: yellow;
		     padding: 0px 5px 0px 5px
		}
		
		#calNextFC {
		     border: 1px solid white;
		     display: block;
		     float: right;
		     background: black;
		     width: 20px;
		     text-align: center
		}
		
		#calPrevFC {
		     border: 1px solid white;
		     display: block;
		     float: left;
		     background: black;
		     width: 20px;
		     text-align: center
		}
		
		.calDWFC {
		     border: 1px solid black;
		     display: block;
		     width: 90px;
		     height: 45px;
		     line-height: 300%;
		     float: left;
		     text-align: center;
		     font-weight: bold
		}
		
		.calDayFC {
		     border: 1px solid black;
		     display: block;
		     width: 90px;
		     height: 90px;
		     line-height: 100%;
		     float: left;
		     text-align: left;
		     background: #303030
		}
		
		#calTActiveFC {
		     border: 1px solid black;
		     display: block;
		     width: 90px;
		     height: 90px;
		     line-height: 100%;
		     float: left;
		     text-align: left;
		     background: black
		}		
		.calActiveFC {
		     border: 1px solid black;
		     display: block;
		     width: 90px;
		     height: 90px;
		     line-height: 100%;
		     float: left;
		     text-align: left;
		     background: #c0c0c0
		}
		
		#calTodayFC {
		     border: 1px solid black;
		     display: block;
		     width: 90px;
		     height: 90px;
		     line-height: 100%;
		     float: left;
		     text-align: left;
		     background: #999999
		}';
	
		$defaultEStyle = '#eventCatList {
		     display: block;
		     width: 100%
		}
			
		.catMainUL {
		   list-style: none;
		   float: left;
		}
		
		.catSubUL {
		   list-style: none;
		}
		.catChildUL {
		   list-style: none;
		}
		
		.catMainList {
		}
		
		.catSubList {
		}
		
		.catChildList {
		}
		
		.catMainList a {
		}
		
		.catSubList a {
		}
		
		.catChildList a {
		}
		
		#eventDateHead {
		     display: block;
		     font-size: 17px
		}
		
		.eventsList {
		    display: block
		}
		
		.eventBlock {
		    display: block;
		    border-bottom: 1px solid white
		}
		.eventDetails {
		     float: left
		}
		
		.eventOptions {
		     float: right
		}
		
		.myEventTitle {
		    text-transform: uppercase
		}
		
		.myEventLocationTitle {
		}
		
		.myEventLocation {
		}
		
		.myEventTimeTitle {
		}
		
		.myEventTime {
		}
	
		.eventOptions span {
		    display: block;
		    float: left;
		    margin: 2px;
		    background: yellow
		}
		.eventOptions span a:hover {
		    color: white;
		    background: black;
		    border: 1px solid yellow
		}
		
		.btnLink {
		    display: block;
		    font-size: 12px;
		    color: black;
		    padding: 2px 5px 2px 5px;
		    margin: 0px;
		    border: 1px black solid;
		    line-height: 110%
		}';
		
		add_option("alien_cal_style", $defaultCalStyle);
		add_option("alien_event_style", $defaultEStyle);
		add_option("alien_event_desc", '400');
		add_option("alien_event_email_per", '100');
		add_option("alien_event_email_time", '3600');
		add_option("alien_event_expire", '7');
		add_option("alien_event_title", '50');
		add_option("alien_fc_len", '15');
		add_option("alien_fc_per", '5');
		add_option("alien_event_international", 'us');
		add_option("alien_event_autophone", 'yes');
		add_option("alien_event_provinces", 'none');
		add_option("alien_event_countries", 'none');
		add_option("alien_event_week_start", 'sunday');
		add_option("alien_event_cat_disp", 'list');
		add_option("alien_event_list_date", 'current');
		add_option("alien_event_list_number", '20');
		add_option("alien_cal_version", $alien_cal_ver);
		add_option("alien_event_popup_field", 'description');
		add_option("alien_event_new_layout", 'css');
		add_option("alien_cal_money", '&#36;');
		add_option("alien_event_roles", array('administrator'));
		add_option("alien_event_email_me", 1);
		add_option("alien_event_email_mel", 100);
	
		$installed_ver = get_option( 'alien_cal_version' );
		if( $installed_ver != $alien_cal_ver ) {
			update_option("alien_cal_version", $alien_cal_ver);
		}
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$table_name = $wpdb->prefix . "almailer";
		$sql = "CREATE TABLE " . $table_name . " (
		  send_id int(10) unsigned NOT NULL auto_increment,
		  member_id int(11) default NULL,
		  email varchar(100) default NULL,
		  subject varchar(100) default NULL,
		  message text,
		  event_id int(11) default NULL,
		  sent int(4) default NULL,
		  PRIMARY KEY  (send_id)
		);";
		dbDelta($sql);

		$table_name = $wpdb->prefix . "alevents";
		$sql = "CREATE TABLE " . $table_name . " (
		  event_id int(10) unsigned NOT NULL auto_increment,
		  member_id int(11) default NULL,
		  event_cat varchar(100) default NULL,
		  event_name varchar(200) default NULL,
		  event_date varchar(100) default NULL,
		  event_start int(10) unsigned zerofill default NULL,
		  event_end varchar(100) default NULL,
		  description text,
		  attendees varchar(100) default NULL,
		  rsvp_count int(11) default '0',
		  rsvp_type int(4) default NULL,
		  price varchar(20) default NULL,
		  contact_name varchar(100) default NULL,
		  contact_email varchar(100) default NULL,
		  area varchar(100) default NULL,
		  location varchar(200) default NULL,
		  url varchar(200) default NULL,
		  address varchar(100) default NULL,
		  city varchar(100) default NULL,
		  state varchar(100) default NULL,
		  country varchar(100) default NULL,
		  zip varchar(7) default NULL,
		  phone varchar(16) default NULL,
		  event_repeat varchar(20) default NULL,
		  end_date varchar(50) default NULL,
		  post_id int(11) default NULL,
		  media_url text,
		  media_type varchar(50) default NULL,
		  media_path text,
		  media_id int(11) default NULL,
		  counter int(11) default 0,
		  active varchar(10) default NULL,
		  PRIMARY KEY  (event_id)
		);";

		dbDelta($sql);
	}

	function cal_deactivate() {
	}

	function cal_uninstall() {
	}
?>