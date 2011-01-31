<?php

require_once( config_get( 'class_path' ) . 'MantisPlugin.class.php' );

class dueCalenderPlugin extends MantisPlugin  {

	/**
	 *  A method that populates the plugin information and minimum requirements.
	 */
	function register( ) {
		$this->name = "Due Calender";
		$this->description = "due Calender description";
		$this->page = 'config';

		$this->version = '1.0';
		$this->requires = array(
			'MantisCore' => '1.2.0',
		);

		$this->author = 'priereluna';
		$this->contact = 'priereluna@gmail.com';
        $this->url = 'http://app.priereluna.net/mantis/';
	}

	/**
	 * Default plugin configuration.
	 */
	function config() {
		return array(
            'display_date_fmt' => "Y年 m月",
            'today_color' => "#ffffcc",
		);
	}
	
	function init() {
		//mantisgraph_autoload();
		spl_autoload_register( array( 'dueCalenderPlugin', 'autoload' ) );
		
		$t_path = config_get_global('plugin_path' ). plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR;

		set_include_path(get_include_path() . PATH_SEPARATOR . $t_path);
	}
	
	public static function autoload( $className ) {
		if (class_exists( 'ezcBase' ) ) {
			ezcBase::autoload( $className );
		}
	}
	
    function hooks() {
        $hooks = array(
            'EVENT_MENU_MAIN' => 'add_menu',
        );
        return $hooks;
    }

    // フックした箇所に表示するリンクを指定
    function add_menu() {
        return array('<a href="'. plugin_page( 'calender_list.php' ) . '">Calender</a>' );
    }
	
}

function duecalender_autoload() {
}
