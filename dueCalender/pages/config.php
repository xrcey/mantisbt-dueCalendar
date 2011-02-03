<?php

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( plugin_lang_get( 'title' ) );

print_manage_menu( );

?>

<br />
<form action="<?php echo plugin_page( 'config_edit' )?>" method="post">
<?php echo form_security_field( 'plugin_duecalendar_config_edit' ) ?>
<table align="center" class="width75" cellspacing="1">

<tr><td class="form-title" colspan="3">
<?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' )?>
</td></tr>

<tr class="spacer"><td></td></tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'today_color' )?>
	</td>
	<td class="center" colspan="2">
		<input type="text" name="today_color" value="<?php echo plugin_config_get( 'today_color' )?>" />
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'display_date_fmt' )?>
	</td>
	<td class="center" colspan="2">
		<input type="text" name="display_date_fmt" value="<?php echo plugin_config_get( 'display_date_fmt' )?>" />
	</td>
</tr>

<tr>
	<td class="center" colspan="3">
		<input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' )?>" />
	</td>
</tr>

</table>
<form>

<?php
html_page_bottom();
