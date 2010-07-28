<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Time Span - sets up the hooks
 *
 * @author	   John Etherton
 * @package	   Time Span
 */

class timespan {
	
	/**
	 * Registers the main event add method
	 */
	 
	 
	public function __construct()
	{
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
		
		$this->settings = ORM::factory('timespan')
				->where('id', 1)
				->find();
		
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		Event::add('ushahidi_filter.active_startDate', array($this, '_set_startDate'));		
		Event::add('ushahidi_filter.active_endDate', array($this, '_set_endDate'));		
		Event::add('ushahidi_filter.active_month', array($this, '_set_month'));		
	}
	
	/**
	 * Figure out what the start date should be
	 */
	public function _set_startDate()
	{
		//What method are we use to set the date?
		$mode = $this->settings->mode; 
		
		if($mode == 1) //the last N days
		{
			//find out how many days ago we should go
			$n_days = $this->settings->days_back;
			
			//get the time N days ago
			$startDate = time() - ($n_days * 24 * 60 * 60);
			
			Event::$data = $startDate;
			
		}
		elseif($mode == 2) //From date N to date M
		{
			$startDate = strtotime($this->settings->start_date);
			Event::$data = $startDate;
		}
		elseif($mode == 3) //Make the time span encompass all events
		{
			$db = new Database();
			$query = $db->query('SELECT incident_date FROM incident WHERE incident_active = 1 ORDER BY incident_date ASC LIMIT 1');
			$startDate = "";
			foreach ($query as $query_active)
			{
				$startDate = strtotime($query_active->incident_date) - (31 * 24 * 60 * 60); //subtract out a month to make sure it's all included.				
			}
			
			Event::$data = $startDate;
		}
		elseif($mode == 4) //Most active month
		{
			//don't do anything
		}
		
	}//end method


	/**
	 * Figure out what the end date should be
	 */
	public function _set_endDate()
	{
		//What method are we use to set the date?
		$mode = $this->settings->mode; 
		
		if($mode == 1) //the last N months
		{
			//get the current date
			$endDate = time();
			
			Event::$data = $endDate;
			
		}
		elseif($mode == 2) //From date N to date M
		{
			$endDate = strtotime($this->settings->end_date);
			Event::$data = $endDate;
		}
		elseif($mode == 3) //Make the time span encompass all events
		{
			$db = new Database();
			$query = $db->query('SELECT incident_date FROM incident WHERE incident_active = 1 ORDER BY incident_date DESC LIMIT 1');
			$endDate = "";
			foreach ($query as $query_active)
			{
				//add in an extra month so it's inclusive
				$endDate = strtotime($query_active->incident_date) + (31*24 * 60 * 60);				
			}
			
			Event::$data = $endDate;
		}
		elseif($mode == 4) //Most active month
		{
		}
		
	}//end method


	/**
	 * Figure out what the active month should be
	 */
	public function _set_month()
	{
		//What method are we use to set the date?
		$mode = $this->settings->mode;
		
		if($mode == 1) //the last N months
		{
			//get the current month
			$month = date("m");
			
			Event::$data = $month;
		}
		elseif($mode == 2) //From date N to date M
		{
			$month = date("m",strtotime($this->settings->end_date));
			Event::$data = $month;
		}
		elseif($mode == 3) //Make the time span encompass all events
		{
			$db = new Database();
			$query = $db->query('SELECT incident_date FROM incident WHERE incident_active = 1 ORDER BY incident_date DESC LIMIT 1');
			$month = "";
			foreach ($query as $query_active)
			{
				$month = date("m", strtotime($query_active->incident_date));				
			}
			
			Event::$data = $month;
		}
		elseif($mode == 4) //Most active month
		{
		}
		
	}//end method


	
}//end class

new timespan;