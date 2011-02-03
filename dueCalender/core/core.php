<?php
function pr($a){
    echo "<pre>";
    print_r($a);
    echo '</pre>';
}

function get_calendar_array($month = 0, $year = 0){
    $month = ($month >= 1 && $month <= 12) ? (int)$month: date('m', time());
    $year = (1970 <= $year && $year <= 2037)? (int)$year: date('Y', time());

    $current_oneday = mktime(0, 0, 0, $month, 1, $year);

    $nowFinalmon = date('t', $current_oneday); //月末日
    $nowStartday = date('w', $current_oneday); //月初曜日
    $nowFinalday = date('w', mktime(0, 0, 0, $month, $nowFinalmon, $year)); //月末曜日
    $month_diff = 7 - $nowFinalday - 1; //当月末の余りの日数
    $Array_month = array();
    
    // 月初を埋める
    for ($i = $nowStartday ; $i > 0 ; --$i) {
        $index = $nowStartday - $i;
        $timestamp = mktime(0, 0, 0, $month, 1 - $i, $year);
        $current = getdate( $timestamp );
        $Array_month[$index] = array(
            'year'  => $current['year'], 
            'month' => $current['mon'],
            'day'   => $current['mday'],
            'week'  => $current['wday'],
            'now'   => -1,
            'timestamp' => $timestamp,
            );
    } 
    // 今月を埋める
    $index = $nowStartday;
    $current = array('year'  => date('Y', $current_oneday), 'month' => date('n', $current_oneday));
    for($i = 1 ; $i <= $nowFinalmon ; ++$i) {
        $timestamp = mktime(0, 0, 0, $month, $i, $year);
        $Array_month[$index] = array(
            'year'  => $current['year'],
            'month' => $current['month'],
            'day'   => $i,
            'week'  => date('w', $timestamp),
            'now'   => 0,
            'timestamp' => $timestamp,
            );
        ++$index;
    } 
    // 月末を埋める    $n = 1;
    $next_month = $month + 1;
    $current = array('year'  => date('Y', mktime(0,0,0,$next_month, 1, $year)),
                      'month' => date('n', mktime(0,0,0,$next_month, 1, $year))
                      );
    for($i = 1;$i <= $month_diff ; ++$i) {
        $timestamp = mktime(0, 0, 0, $next_month, $i, $year);
        $Array_month[$index] = array(
            'year'  => $current['year'], 
            'month' => $current['month'],
            'day'   => $i,
            'week'  => date('w', $timestamp),
            'now'   => 1,
            'timestamp' => $timestamp,
            );
        ++$index;
    }
    return $Array_month;
} 