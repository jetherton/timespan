<?php
/**
 * Time Span - Install
 *
 * @author	   John Etherton
 * @package	   Time Span
 */

class Timespan_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the actionable plugin
	 */
	public function run_install()
	{
		// Create the database tables.
		// Also include table_prefix in name
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'timespan` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `mode` int(10) unsigned NOT NULL,
				  `interval_mode` int(10) unsigned NOT NULL,
				  `days_back` int(10) unsigned,
				  `start_date` datetime default NULL,
				  `end_date` datetime default NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
		
		$num_settings = ORM::factory('timespan')
				->where('id', 1)
				->count_all();
		if($num_settings == 0)
		{
			$settings = ORM::factory('timespan');
			$settings->id = 1;
			$settings->interval_mode = 1;
			$settings->mode = 1;
			$settings->days_back = 60;
			$settings->save();
		}
		
	}

	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
		
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'timespan`');
	}
}