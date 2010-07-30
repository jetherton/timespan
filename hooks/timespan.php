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
		
		Event::add('ushahidi_filter.startDate', array($this, '_set_slider_start'));
		Event::add('ushahidi_filter.endDate', array($this, '_set_slider_end'));
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
			
			$this->active_startDate = $startDate;
			Event::$data = $startDate;
			
		}
		elseif($mode == 2) //From date N to date M
		{
			$startDate = strtotime($this->settings->start_date);
			$this->active_startDate = $startDate;
			Event::$data = $startDate;
		}
		elseif($mode == 3) //Make the time span encompass all events
		{
			$db = new Database();
			$query = $db->query('SELECT incident_date FROM incident WHERE incident_active = 1 ORDER BY incident_date ASC LIMIT 1');
			$startDate = "";
			foreach ($query as $query_active)
			{
				//if the slider's increments are set in terms of months we'll need to subtract another month
				//from the start date because the timeline rounds up to th nearest month
				if($this->settings->interval_mode == 1)
				{
					$startDate = strtotime($query_active->incident_date) - (31 * 24 * 60 * 60); //subtract out a month to make sure it's all included.				
				}
				elseif($this->settings->interval_mode==2)
				{
					$startDate = strtotime($query_active->incident_date) - (24 * 60 * 60); //subtract out a day
				}
			}
			
			$this->active_startDate = $startDate;
			Event::$data = $startDate;
		}
		elseif($mode == 4) //Most active month
		{
			$this->active_startDate = Event::$data;
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
			
			$this->active_endDate = $endDate;
			Event::$data = $endDate;
			
		}
		elseif($mode == 2) //From date N to date M
		{
			$endDate = strtotime($this->settings->end_date);
			$this->active_endDate = $endDate;
			Event::$data = $endDate;
		}
		elseif($mode == 3) //Make the time span encompass all events
		{
			$db = new Database();
			$query = $db->query('SELECT incident_date FROM incident WHERE incident_active = 1 ORDER BY incident_date DESC LIMIT 1');
			$endDate = "";
			foreach ($query as $query_active)
			{
				//if the slider's increments are set in terms of months we'll need to add another month
				//to the end date because the timeline rounds up to th nearest month
				if($this->settings->interval_mode == 1)
				{
					$endDate = strtotime($query_active->incident_date) + (31 * 24 * 60 * 60);
				}
				elseif($this->settings->interval_mode==2) //if it's a day just add a day
				{
					$endDate = strtotime($query_active->incident_date) + (24 * 60 * 60); 
				}		
			}
			$this->active_endDate = $endDate;
			Event::$data = $endDate;
		}
		elseif($mode == 4) //Most active month
		{
			$this->active_endDate = Event::$data;
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
			$this->month = Event::$data;
		}
		elseif($mode == 2) //From date N to date M
		{
			$month = date("m",strtotime($this->settings->end_date));
			Event::$data = $month;
			$this->month = Event::$data;
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
			$this->month = Event::$data;
		}
		elseif($mode == 4) //Most active month
		{
			$this->month = Event::$data;
		}
		
	}//end method


	/**
	* Used to set the increments of the slider
	* This should set the startDate variable
	*/ 
	public function _set_slider_start()
	{
	
		//first get all the date information out of our nifty parameter passing array
		$startDate = Event::$data['startDate'];

		//if the interval_mode is set to 1, then just leave the interval at months
		//but if interval_mode is set to 2 then rewrite things as days
		if($this->settings->interval_mode == 1) //leave the interval at months
		{
			$this->startDate = $startDate;
		}
		elseif($this->settings->interval_mode == 2) //switch intervals to days
		{

			/**************************************************
			A special thanks to the Ushahidi Hait people.
			I just copy and pasted this code from their instance
			and only made minor changes
			***************************************************/
		
			$timeframe_stop = $this->active_endDate;
			$timeframe_start = $this->active_startDate;
			
			$start_lastMonth = date("F", $timeframe_start);
			$end_lastMonth =  date("F", $timeframe_start+86399);
			//now start making some changes to things
			//We'll be focusedon changing the things in $startDate and $endDate
			$days = floor(($timeframe_stop - $timeframe_start) / 86400);
			//figure out the 4 digit year of the activeStart Date
			$startDate = "<optgroup label=\"".date("F Y", $timeframe_start)."\">";
			$endDate = "<optgroup label=\"".date("F Y", $timeframe_start)."\">";
			for ($i=0; $i <= $days; $i++)
			{
				$startDate .= "<option value=\"".$timeframe_start."\"";
				if ($i==0)
				{
					$startDate .= " selected=\"selected\" ";
				}
				$startDate .= ">" . date('M j Y', $timeframe_start) . "</option>";

				$timeframe_stop = $timeframe_start+86399;
				
				//check to see if we need a new option group
				if($end_lastMonth != date("F", $timeframe_stop))
				{
					$end_lastMonth = date("F", $timeframe_stop);
					$endDate .= "</optgroup>";
					$endDate .= "<optgroup label=\"".date("F Y", $timeframe_stop)."\">";
				}
				
				$endDate .= "<option value=\"".$timeframe_stop."\"";
				
				if ($i==$days) 
				{
					$endDate .= " selected=\"selected\" ";
				}

				$endDate .= ">" . date('M j Y', $timeframe_stop) . "</option>";
				$timeframe_start = $timeframe_start + 86400;
				
				//check to see if we need a new option group
				if($start_lastMonth != date("F", $timeframe_start))
				{
					$start_lastMonth = date("F", $timeframe_start);
					$startDate .= "</optgroup>";
					$startDate .= "<optgroup label=\"".date("F Y", $timeframe_start)."\">";
				}

			}
			$startDate .= "</optgroup>";
			$endDate .= "</optgroup>";
			
			$this->startDate = $startDate;
			$this->endDate = $endDate;
			Event::$data = $startDate;
		}

	} //end method _set_slider_start
	
	
	/**
	* Used to set the increments of the slider
	* This should set the endDate variable
	* all the processing was done in the above function
	* but to set two seperate variables we need two seperate
	* filters
	*/ 
	public function _set_slider_end()
	{
			//if the interval_mode is set to 1, then just leave the interval at months
		//but if interval_mode is set to 2 then rewrite things as days
		if($this->settings->interval_mode == 1) //leave the interval at months
		{
			$this->endDate = Event::$data;
		}
		else if($this->settings->interval_mode == 2)
		{
			Event::$data = $this->endDate;
		}
	} //end method _set_slider_end
	
}//end class

new timespan;