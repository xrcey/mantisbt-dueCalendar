<?php

form_security_validate( 'plugin_graph_config_edit' );

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );


$f_today_color = gpc_get_int( 'today_color', 'ffffcc' );
$f_display_date_fmt = gpc_get_int( 'display_date_fmt', 'Y/m/d' );


if ( plugin_config_get( 'today_color' ) != $f_today_color ) {
	plugin_config_set( 'today_color', $f_today_color );
}

if ( plugin_config_get( 'display_date_fmt' ) != $f_display_date_fmt ) {
	plugin_config_set( 'display_date_fmt', $f_display_date_fmt );
}

//form_security_purge( 'plugin_duecalendar_config_edit' );

print_successful_redirect( plugin_page( 'config', true ) );
