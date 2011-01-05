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
				
		// Set Table Prefix
		$this->table_prefix = Kohana::config('database.default.table_prefix');		

		//sets whether we're looking at frontend or backend stuff. Used to
		//decide if we show unapproved reports or not.
		$this->backend = false;
		if( substr(url::current(),0,5) == "admin")
		{
			$this->backend = true;
		}
		
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
			$startDate = $this->start_last_n_days();
			$this->active_startDate = $startDate;
			Event::$data = $startDate;
		}
		elseif($mode == 2) //From date N to date M
		{
			$startDate = $this->start_from_n_to_m();
			$this->active_startDate = $startDate;
			Event::$data = $startDate;
		}
		elseif($mode == 3) //Make the time span encompass all events
		{
			$startDate = $this->start_all_reports();
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
			$endDate = $this->end_last_n_days();
			
			$this->active_endDate = $endDate;
			Event::$data = $endDate;
			
		}
		elseif($mode == 2) //From date N to date M
		{
			$endDate = $this->end_from_n_to_m();
			$this->active_endDate = $endDate;
			Event::$data = $endDate;
		}
		elseif($mode == 3) //Make the time span encompass all events
		{
			$endDate = $this->end_all_reports();
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
			$query_text = 'SELECT incident_date FROM incident WHERE incident_active = 1 ORDER BY incident_date DESC LIMIT 1';
			if($this->backend)
			{
				$query_text = 'SELECT incident_date FROM incident ORDER BY incident_date DESC LIMIT 1';
			}
			$query = $db->query($query_text);
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
	
		$startDate = "";
		$endDate = "";

		//if the interval_mode is set to 1, then just leave the interval at months
		//but if interval_mode is set to 2 then rewrite things as days
		if($this->settings->interval_mode == 1) //leave the interval at months
		{
			$startMonth = date('n', $this->active_startDate);
			$startYear =  date('Y', $this->active_startDate);
			$endMonth =  date('n', $this->active_endDate);
			$endYear = date('Y', $this->active_endDate);
			
									
			for($years = $startYear; $years <= $endYear; $years++)
			{
				$startDate .= "<optgroup label=\"" . $years . "\">";
				for ( $i=1; $i <= 12; $i++ ) {
					
					//calculate the working date
					$startWorkingDate = mktime(0, 0, 0, $i, 1, $years);
					
					$startDate .= "<option value=\"" . $startWorkingDate. "\"";
					if ( $startMonth && ( (int) $i == ( $startMonth - 0)) && ($years == $startYear) )
					{
						$startDate .= " selected=\"selected\" ";
					}
					$startDate .= ">" . date('M', $startWorkingDate) . " " . $years . "</option>";
				}
				$startDate .= "</optgroup>";

				$endDate .= "<optgroup label=\"" . $years . "\">";
				for ( $i=1; $i <= 12; $i++ )
				{
					//calculate the working date
					$endWorkingDate = mktime(23, 59, 59, $i+1, 0, $years);
				
					$endDate .= "<option value=\"" . $endWorkingDate . "\"";
					// Focus on the end Month
					if ( $endMonth && ( ( (int) $i == ( $endMonth + 0)) ) && ($years == $endYear))
					{
						$endDate .= " selected=\"selected\" ";
					}
					$endDate .= ">" . date('M', $endWorkingDate) . " " . $years . "</option>";
				}
				$endDate .= "</optgroup>";
			}
			
			$this->startDate = $startDate;
			$this->endDate = $endDate;
			Event::$data = $startDate;

		}
		////////////////////////////////////////////////////////END MONTHS ///////// START DAYS
		elseif($this->settings->interval_mode == 2) //switch intervals to days
		{

			/**************************************************
			A special thanks to the Ushahidi Hait people.
			I just copy and pasted this code from their instance
			and only made minor changes
			***************************************************/
		
			//regardless of the default timespan we want to give the user
			//the option to see all of the events in the system so we use the
			//X_all_reports() methods to get the date range of al reports
			$timeframe_stop = $this->end_all_reports();
			$timeframe_start = $this->start_all_reports();
			
			//check and see which set of dates is greater, all reports or active_dates
			if($this->active_endDate > $timeframe_stop)
			{
				$timeframe_stop = $this->active_endDate;
			}
			if($this->active_startDate < $timeframe_start)
			{
				$timeframe_start = $this->active_startDate;
			}
			$timeframe_start = $timeframe_start - (86400 * 15); //gives us a 15 day margin at the start
			$timeframe_stop = $timeframe_stop + (86400 *15); //gives us a 15 day margin at the end
			
			$start_lastMonth = date("F", $timeframe_start);
			$end_lastMonth =  date("F", $timeframe_start+86399);
			//now start making some changes to things
			//We'll be focusedon changing the things in $startDate and $endDate
			$days = floor(($timeframe_stop - $timeframe_start) / 86400);
			$startDay = floor(($this->active_startDate - $timeframe_start) / 86400);
			$endDay = floor(($this->active_endDate - $timeframe_start) / 86400);

			//echo "days = ".$days." startDay = ".$startDay. " endDay = ". $endDay;
			//figure out the 4 digit year of the activeStart Date
			$startDate = "<optgroup label=\"".date("F Y", $timeframe_start)."\">";
			$endDate = "<optgroup label=\"".date("F Y", $timeframe_start)."\">";
			for ($i=0; $i <= $days; $i++)
			{
				$startDate .= "<option value=\"".$timeframe_start."\"";
				if ($i==$startDay)
				{
					$startDate .= " selected=\"selected\" ";
				}
				$startDate .= ">" . date('M j Y', $timeframe_start) . "</option>";
				//echo "<br/> i = ".$i." startDate = ".date('M j Y', $timeframe_start);

				$timeframe_stop = $timeframe_start+86399;
				
				//check to see if we need a new option group
				if($end_lastMonth != date("F", $timeframe_stop))
				{
					$end_lastMonth = date("F", $timeframe_stop);
					$endDate .= "</optgroup>";
					$endDate .= "<optgroup label=\"".date("F Y", $timeframe_stop)."\">";
				}
				
				$endDate .= "<option value=\"".$timeframe_stop."\"";
				
				if ($i==$endDay) 
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
			Event::$data = $this->endDate;
		}
		else if($this->settings->interval_mode == 2)
		{
			Event::$data = $this->endDate;
		}
	} //end method _set_slider_end
	
	
	/**
	* returns the start date when the mode is last N days
	**/
	private function start_last_n_days()
	{
		//find out how many days ago we should go
		$n_days = $this->settings->days_back;
			
		//get the time N days ago
		$startDate = time() - ($n_days * 24 * 60 * 60);
		return $startDate;
	}
	
	
	/**
	* returns the start date when the mode is from date N to date M
	**/
	private function start_from_n_to_m()
	{
		$startDate = strtotime($this->settings->start_date);
		return $startDate;
	}
	
	/**
	* Return the start date when the mode is set to show all reports
	**/
	private function start_all_reports()
	{
		$db = new Database();
		$query_text = 'SELECT incident_date FROM incident WHERE incident_active = 1 ORDER BY incident_date ASC LIMIT 1';
		if($this->backend)
		{
			$query_text = 'SELECT incident_date FROM incident ORDER BY incident_date ASC LIMIT 1';
		}
		$query = $db->query($query_text);
		$startDate = "";
		foreach ($query as $query_active)
		{
			//if the slider's increments are set in terms of months we'll need to subtract another month
			//from the start date because the timeline rounds up to th nearest month
			if($this->settings->interval_mode == 1)
			{
				$startDate = strtotime($query_active->incident_date); //round down to the start of the month				
				$roundedDate = mktime(0, 0, 0, date("n", $startDate)-1, 1, date("Y", $startDate));
				$startDate = $roundedDate;
			}
			elseif($this->settings->interval_mode==2)
			{
				$startDate = strtotime($query_active->incident_date); //round down to the start of the day
				$roundedDate = mktime(0, 0, 0, date("n", $startDate), date("j", $startDate)-1, date("Y", $startDate));
				$startDate = $roundedDate;
			}
		}

		return $startDate;
	}

	/**
	* returns the end date when the mode is last N days
	**/
	private function end_last_n_days()
	{
		//get the current date
		$endDate = time();
		return $endDate;
	}
	
	
	/**
	* returns the end date when the mode is from date N to date M
	**/
	private function end_from_n_to_m()
	{
		$endDate = strtotime($this->settings->end_date);
		return $endDate;
	}
	
	/**
	* Return the end date when the mode is set to show all reports
	**/
	private function end_all_reports()
	{
		$db = new Database();
		$query_text = "SELECT incident_date FROM incident WHERE incident_active = 1 ORDER BY incident_date DESC LIMIT 1";
		if($this->backend)
		{
			$query_text = "SELECT incident_date FROM incident ORDER BY incident_date DESC LIMIT 1";
		}
		$query = $db->query($query_text);
		$endDate = "";
		foreach ($query as $query_active)
		{
			//if the slider's increments are set in terms of months add the rest of the days to the end of the month
			if($this->settings->interval_mode == 1)
			{
				$endDate = strtotime($query_active->incident_date); //rounds to the end of the month
				$roundedDate = mktime(0, 0, 0, date("n", $endDate)+1, 0, date("Y", $endDate));
				$endDate = $roundedDate;
			}
			elseif($this->settings->interval_mode==2) //if it's a day just add more hours/minutes till midnight
			{
				$endDate = strtotime($query_active->incident_date); 
				$roundedDate = mktime(23, 59, 59, date("n", $endDate), date("j", $endDate)+1, date("Y", $endDate));
				$endDate = $roundedDate;
			}		
		}
		return $endDate;
	}


	
}//end class

new timespan;