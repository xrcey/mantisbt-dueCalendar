<?php
require_once( 'core.php' );

require_once( 'bug_api.php' );
require_once( 'custom_field_api.php' );
require_once( 'date_api.php' );
require_once( 'string_api.php' );
require_once( 'last_visited_api.php' );

$scriptname = plugin_page( 'calender_list.php' );
$user_id = auth_get_current_user_id();
$t_username = user_get_row($user_id);

$time = time();
$toyear = date('Y', $time);
$tomonth = date('m', $time);
$todate = date('Y-m-d', $time);
$last_month_day = date('t', mktime(0, 0, 0, $month, 1, $year));
$plugin_name = plugin_get_current();
$t_project_id = gpc_get_int( 'project_id', helper_get_current_project() );
$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );
$t_bugnote_text_table = db_get_table( 'mantis_bugnote_text_table' );
$t_bug_table = db_get_table( 'mantis_bug_table' );
$t_bugnote_order = current_user_get_pref( 'bugnote_order' );
$year = gpc_get_int( 'start_year', $toyear);
$month = gpc_get_int( 'start_month', $tomonth);
$ical_path = config_get( 'path' ) . 'plugins' . DIRECTORY_SEPARATOR . $plugin_name . DIRECTORY_SEPARATOR . 'ical.php';
$ical_auth_path = $ical_path . '?username=' . $t_username['username'] . '&key=' . rss_calculate_key( $user_id );
$ical_icon_path = config_get( 'path' ) . 'plugins' . DIRECTORY_SEPARATOR . $plugin_name . DIRECTORY_SEPARATOR . 'icon.png';

//options
if ($act = gpc_get_string('act', '')) {
    //search form
    $hidden_roadmap = gpc_get_string('hidden_roadmap', '');
    $hidden_empty = gpc_get_string('hidden_empty', '');
    session_set('plugin_'.$plugin_name.'_hidden_roadmap', $hidden_roadmap);
    session_set('plugin_'.$plugin_name.'_hidden_empty', $hidden_empty);
} else {
    $hidden_roadmap = session_get_string('plugin_'.$plugin_name.'_hidden_roadmap', '');
    $hidden_empty = session_get_string('plugin_'.$plugin_name.'_hidden_empty','');
}

$t_status_array = MantisEnum::getAssocArrayIndexedByValues( lang_get( 'status_enum_string' ));

//project
$query = 'SELECT * FROM `mantis_project_table` WHERE id ='.db_param().'';
$t_roadmap_result = db_query_bound($query, array($t_project_id));
$projects = db_fetch_array( $t_roadmap_result );

//roadmap
$roadmaps = array();
if (!$hidden_roadmap) {
    $query = 'SELECT * FROM `mantis_project_version_table` WHERE project_id ='.db_param().' AND `date_order` BETWEEN '.db_param().' AND '.db_param().';';
    $t_roadmap_result = db_query_bound($query, array($t_project_id,
                                                     mktime(0,0,0,$month, 1, $year),
                                                     mktime(24,59,59,$month, $last_month_day, $year)));
    $roadmap_count = (int) db_num_rows($t_roadmap_result);
    while( $row = db_fetch_array( $t_roadmap_result ) ) {
        $roadmaps[date('Y-m-d', $row['date_order'])][] = $row;
    }
}

//due with bug
$_bugs = array();
$query = 'SELECT * from '.db_get_table( 'mantis_bug_table' ).' WHERE project_id ='.db_param().' AND due_date BETWEEN '.db_param().' AND '.db_param().';';
$t_bug_result = db_query_bound($query, array($t_project_id,
                                             mktime(0,0,0,$month, 1, $year),
                                             mktime(24,59,59,$month, $last_month_day, $year)));
$bug_count = (int) db_num_rows($t_bug_result);
while( $row = db_fetch_array( $t_bug_result ) ) {
    $bugs[date('Y-m-d', $row['due_date'])][] = $row;
}
$user_array = array();
html_page_top( lang_get( 'bugnote' ) );

//pr($bugs);

$calendars = get_calendar_array($month, $year);
$calendarHandler = array(
    'prev'=> array('year' => date('Y',mktime(0,0,0,$month-1,1,$year)),
                   'month'=> date('m',mktime(0,0,0,$month-1,1,$year))),
    'next'=> array('year' => date('Y',mktime(0,0,0,$month+1,1,$year)),
                   'month'=> date('m',mktime(0,0,0,$month+1,1,$year))),
    'current'=> array('year' => $year, 'month' => $month,'day' => $day)
    );
//pr($calendars);
?>
<style>
table.solid{
    border-top:1px solid #663300;
    border-left:1px solid #663300;
    border-collapse:collapse;
    border-spacing:0;
    background-color:#ffffff;
    empty-cells:show;
}
table.solid td{
  height:50px;
  position:relative;
}

table.solid td > .day {
  left:1px;

}

.nom{
  color:#ccc;
}
.today{
    background-color:<?php echo plugin_config_get("today_color")?>;
}
.Sun .day{ color:#f33;}
</style>

<div class="calendar_head">
<span class="pagetitle"><?php echo $projects['name'] ?> - <?php echo date(plugin_config_get("display_date_fmt"), mktime(0,0,0,$month, 1, $year))?></span>
<span>
 [<a href="<?php echo $scriptname.'?start_year='.$calendarHandler['prev']['year'].'&start_month='.$calendarHandler['prev']['month'].''?>"><?php echo plugin_lang_get( 'link_prev_month' );?></a>] |<!--
--> [<a href="<?php echo $scriptname.'?start_year='.$calendarHandler['next']['year'].'&start_month='.$calendarHandler['next']['month'].''?>"><?php echo plugin_lang_get( 'link_next_month' );?></a>]
</span>
<span onclick="toggleDisplay('search_form_field')" style="cursor:pointer;"><img src="images/plus.png" alt="+" id="dueCalenderSearchIcon">&#160;<?php echo plugin_lang_get( 'search' );?></span>
[<a href="<?php echo $ical_auth_path; ?>"><img src="<?php echo $ical_icon_path?>" width="16" height="16" alt="iCalendar">&#160;<?php echo plugin_lang_get('ical_export');?></a>]
</div>

<div id="search_form_field" style="display:none;">
<form action="" method="get">
<input type="hidden" name="page" value="dueCalender/calender_list.php">
<input type="hidden" name="act" value="search">
<input type="hidden" name="start_year" value="<?php echo $calendarHandler['current']['year']?>">
<input type="hidden" name="start_month" value="<?php echo $calendarHandler['current']['month']?>">

<label><input type="checkbox" name="hidden_roadmap" value="on" <?php echo ($hidden_roadmap)? 'checked': '';?>><?php echo plugin_lang_get( 'search_hide_roadmap' );?></label>
<label><input type="checkbox" name="hidden_empty" value="on" <?php echo ($hidden_empty)? 'checked': '';?>><?php echo plugin_lang_get( 'search_hide_empty_tickets' );?></label>
<input type="submit" value="<?php echo plugin_lang_get( 'search_submit' );?>">
</form>
</div>

<table class="width100" cellspacing="1" style="margin:5px 0;">
<?php foreach ($calendars as $key => $value):
if ($value['month'] != $month){continue;}
$h_td_css = ($value['month'] != $month)? "nom" : '';
$today = date('Y-m-d', $value['timestamp']);
$columnColor = ($todate == $today)? plugin_config_get("today_color"): "#f0f0f0";
$week = date('D', $value['timestamp']);

//empty Columns
if (empty($roadmaps[$today]) && empty($bugs[$today]) && $hidden_empty){continue;}
?>
<tr class="bugnote">
<td width="100px"class="bugnote-public">
<div class="day"><span class="small"><?php  echo $value['day'].' ['.$week.']';?></span></div>
</td>
<td style="background-color:<?php echo $columnColor?>;">
<div >
<?php if (!empty($roadmaps[$today])):?>
<?php foreach($roadmaps[$today] as $roadmap):
$t_strike_start = $t_strike_end = '';
$t_version_id = $roadmap['id'];
$t_version_name = $roadmap['version'];
$t_filename = "roadmap_page.php";
if( $roadmap['released'] == 1) {
    $t_strike_start = '<strike>';
    $t_strike_end = '</strike>';
    $t_filename = "changelog_page.php";
}
$t_release_title = '<a href="'.$t_filename.'?version_id=' . $t_version_id . '">' . $t_strike_start.$t_version_name.$t_strike_end . '</a>: [Roadmap] ';

?>
<div> - <?php echo $t_release_title?>
<?php echo ' '. print_bracket_link( 'view_all_set.php?type=1&temporary=y&' . FILTER_PROPERTY_PROJECT_ID . '=' . $t_project_id . '&' . filter_encode_field_and_value( FILTER_PROPERTY_TARGET_VERSION, $t_version_name ), lang_get( 'view_bugs_link' ) ), '<br />';?>
</div>
<?php endforeach;?>
<?php endif;?>

<?php if (!empty($bugs[$today])):?>
<?php foreach($bugs[$today] as $bug):
$v3_bug_id = $bug['id'];
$t_bug_id_formatted = bugnote_format_id( $v3_bug_id );
$v3_bug_summary = $bug['summary'];
$handler_name = prepare_user_name( $bug['handler_id'] );
$t_strike_start = $t_strike_end = '';
$t_category_name = category_get_name( $bug['category_id'] );
if( bug_is_resolved( $v3_bug_id ) ) {
    $t_strike_start = '<strike>';
    $t_strike_end = '</strike>';
}
?>
<div> - <a href="<?php echo string_get_bug_view_url($v3_bug_id) ?>"><?php echo $t_strike_start.$t_bug_id_formatted.$t_strike_end;?></a>:
[<?php echo $t_category_name;?>] <?php echo $v3_bug_summary;?> (<?php echo prepare_user_name( $bug['handler_id'] )?>)
</div>
<?php endforeach;?>
<?php endif;?>
</div>
</td></tr>
<?php endforeach;?>
</table>
<?php html_page_bottom();?>
