<?php
$basename = dirname(dirname(dirname(__FILE__)));
require_once( $basename . DIRECTORY_SEPARATOR . 'core.php' );
error_reporting(E_ALL);

$time = time();
$toyear = date('Y', $time);
$tomonth = date('m', $time);
$todate = date('Y-m-d', $time);
$last_month_day = date('t', mktime(0, 0, 0, $month, 1, $year));


$f_project_id = gpc_get_int( 'project_id', ALL_PROJECTS );
$f_username = gpc_get_string( 'username', null );
$f_key = gpc_get_string( 'key', null );

$t_project_id = 3;
$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );
$t_bugnote_text_table = db_get_table( 'mantis_bugnote_text_table' );
$t_bug_table = db_get_table( 'mantis_bug_table' );
$year = gpc_get_int( 'start_year', $toyear);
$month = gpc_get_int( 'start_month', $tomonth);

//roadmap
$roadmaps = array();
if (!$hidden_roadmap) {
    $query = 'SELECT * FROM `mantis_project_version_table` WHERE project_id ='.db_param().' AND `released` = 0;';
    $t_roadmap_result = db_query_bound($query, array($t_project_id));
    $roadmap_count = (int) db_num_rows($t_roadmap_result);
    while( $row = db_fetch_array( $t_roadmap_result ) ) {
        $row['summary'] = $row['version'];
        $row['due_date'] = $row['date_order'];
        $row['content'] = $row['description'];
        $roadmaps[] = $row;
    }
}

//due with bug
$_bugs = array();
$query = 'SELECT * from '.db_get_table( 'mantis_bug_table' ).' WHERE project_id ='.db_param().' AND due_date BETWEEN '.db_param().' AND '.db_param().';';

$t_bug_result = db_query_bound($query, array($t_project_id,
                                             mktime(0,0,0,$month, 1, $year),
                                             mktime(24,59,59,$month, $last_month_day, $year)));
$bug_count = (int) db_num_rows($t_bug_result);

$etag = 'etag-by' . $t_project_id;
while( $bug = db_fetch_array( $t_bug_result ) ) {
    $user = user_get_row($bug['handler_id']);
    $bug['category_name'] = category_get_name( $bug['category_id'] );
    $bug['content'] = $bug['id'] . ':' . '['. $bug['category_name'] .'] ';
    $bug['content'] .= '('. $user['username'] . ')' . ' - ' . get_enum_element( 'status', $bug['status'] );
   // $done = '';
   // if( bug_is_resolved( $bug['id'] )) {
   //     $done = '';
   // }
    $bugs[] = $bug;
    $etag = md5($etag . $bug['last_updated']);
}
$tpl = "BEGIN:VEVENT
DTSTART;VALUE=DATE:%DTSTART%
DTEND;VALUE=DATE:%DTEND%
DTSTAMP:%DTSTAMP%
UID:%UID%
CREATED:%CREATED%
DESCRIPTION:%DESCRIPTION%
LAST-MODIFIED:%MODIFIED%
LOCATION:
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:%SUMMARY%
TRANSP:TRANSPARENT
END:VEVENT\n";
header('Content-Type: text/calendar; charset=utf-8');
?>
BEGIN:VCALENDAR
PRODID:-//Google Inc//Google Calendar 70.9054//EN
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:kurosu@hitbit-inc.net
X-WR-TIMEZONE:Asia/Tokyo
<?php
foreach($roadmaps as $key => $roadmap){
    $_tpl = $tpl;
    $_tpl = str_replace('%UID%', 'hitbit-mantis/roadmap/' . $roadmap['project_id'].'/'.$roadmap['id'], $_tpl);
    $_tpl = str_replace('%SUMMARY%', $roadmap['summary'], $_tpl);
    $_tpl = str_replace('%DTSTART%', date('Ymd', $roadmap['due_date']), $_tpl);
    $_tpl = str_replace('%DTEND%', date('Ymd', strtotime('+1 Day',$roadmap['due_date'])), $_tpl);
    $_tpl = str_replace('%DTSTAMP%', date('Ymd\THis\Z', time()), $_tpl);
    $_tpl = str_replace('%CREATED%', date('Ymd\THis\Z', $roadmap['due_date']), $_tpl);
    $_tpl = str_replace('%DESCRIPTION%', $roadmap['content'], $_tpl);
    $_tpl = str_replace('%MODIFIED%', date('Ymd\THis\Z', $roadmap['due_date']), $_tpl);
    $_tpl = str_replace('%SUMMARY%', $roadmap['summary'], $_tpl);
    echo $_tpl;
}

foreach($bugs as $key => $bug){
    $_tpl = $tpl;
    $_tpl = str_replace('%UID%', 'hitbit-mantis/issue/' . $bug['project_id'].'/'.$bug['id'], $_tpl);
    $_tpl = str_replace('%SUMMARY%', $bug['summary'], $_tpl);
    $_tpl = str_replace('%DTSTART%', date('Ymd', $bug['due_date']), $_tpl);
    $_tpl = str_replace('%DTEND%', date('Ymd', strtotime('+1 Day',$bug['due_date'])), $_tpl);
    $_tpl = str_replace('%DTSTAMP%', date('Ymd\THis\Z', time()), $_tpl);
    $_tpl = str_replace('%CREATED%', date('Ymd\THis\Z', $bug['date_submitted']), $_tpl);
    $_tpl = str_replace('%DESCRIPTION%', $bug['content'], $_tpl);
    $_tpl = str_replace('%MODIFIED%', date('Ymd\THis\Z', $bug['last_updated']), $_tpl);
    $_tpl = str_replace('%SUMMARY%', $bug['summary'], $_tpl);
    echo $_tpl;
}

/*
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111125
DTEND;VALUE=DATE:20111126
DTSTAMP:20111108T091320Z
UID:e0a15c9ou71rq01p5qeer53nsc@google.com
CREATED:20111102T002450Z
DESCRIPTION:
LAST-MODIFIED:20111102T002450Z
LOCATION:
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:お休み
TRANSP:TRANSPARENT
END:VEVENT
*/
?>
BEGIN:VTIMEZONE
TZID:Asia/Tokyo
BEGIN:STANDARD
DTSTART:19700101T000000
TZOFFSETFROM:+0900
TZOFFSETTO:+0900
END:STANDARD
END:VTIMEZONE
END:VCALENDAR
