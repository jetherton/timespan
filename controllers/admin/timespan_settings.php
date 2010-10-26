<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Time Span - Administrative Controller
 *
 * @author	   John Etherton
 * @package	   Time Span
 */

class Timespan_settings_Controller extends Admin_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'settings';

		// If this is not a super-user account, redirect to dashboard
		if(!$this->auth->logged_in('admin') && !$this->auth->logged_in('superadmin'))
		{
			url::redirect('admin/dashboard');
		}
	}
	
	public function index()
	{
		
		$this->template->content = new View('timespan/timespan_admin');
		
		//create the form array
		$form = array
		(
		        'mode' => "",
			'interval_mode' => "",
			'days_back' => "",
			'start_date' => "",
			'end_date' => "",
		);
		$form['start_date'] = date("m/d/Y",time());
		$form['end_date'] = date("m/d/Y",time());
		
		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;
				
		// check, has the form been submitted if so check the input values and save them
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST
			// fields with our own things
			$post = new Validation($_POST);
			
			// Add some filters
			$post->pre_filter('trim', TRUE);
			
			$post->add_rules('days_back', 'numeric', 'length[1,100]');
			$post->add_rules('start_date','required','date_mmddyyyy');
			$post->add_rules('end_date','required','date_mmddyyyy');
			
			 if ($post->validate())
			{
			
				$settings = ORM::factory('timespan')
					->where('id', 1)
					->find();
				$settings->mode = $post->mode;
				$settings->interval_mode = $post->interval_mode;
				$settings->days_back = $post->days_back;				
				//start date stuff
				$start_date=explode("/",$post->start_date);
				$start_date=$start_date[2]."-".$start_date[0]."-".$start_date[1];
				$settings->start_date = date( "Y-m-d", strtotime($start_date) );		
				//end date stuff
				$end_date=explode("/",$post->end_date);
				$end_date=$end_date[2]."-".$end_date[0]."-".$end_date[1];
				$settings->end_date = date( "Y-m-d", strtotime($end_date) );		
				
				
				$settings->save();
				$form_saved = TRUE;
				$form = arr::overwrite($form, $post->as_array());
			}
			
			// No! We have validation errors, we need to show the form again,
			// with the errors
			else
			{
				// repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

				// populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('settings'));
				$form_error = TRUE;
			}
		}
		else
		{
			//get settings from the database
			$settings = ORM::factory('timespan')
				->where('id', 1)
				->find();
			$form['mode'] = $settings->mode;
			$form['interval_mode'] = $settings->interval_mode;
			$form['days_back'] = $settings->days_back;
			if($settings->start_date != null)
			{
				$form['start_date'] = date('m/d/Y', strtotime($settings->start_date));
			}
			if($settings->end_date != null)
			{
				$form['end_date'] = date('m/d/Y', strtotime($settings->end_date));
			}
		}
		
		//get the list of modes
		$mode = array
		(
			"1" => "Show all from now till N days back",
			"2" => "Show all from date A to date B",
			"3" => "Show all reports" ,
			"4" => "Show most active month" 
		);
		
		//get list of interval modes
		$interval_mode = array
		(
			"1" => "Months",
			"2" => "Days"
		);
		
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form = $form;
		$this->template->content->mode = $mode;
		$this->template->content->interval_mode = $interval_mode;
		$this->template->content->form_error = $form_error;
		$this->template->content->date_picker_js_start = $this->_date_picker_js("start_date");
		$this->template->content->date_picker_js_end = $this->_date_picker_js("end_date");
		$this->template->content->errors = $errors;
		
	}//end index method
	
	
	private function _date_picker_js($id) 
	{
		return "<script type=\"text/javascript\">
				$(document).ready(function() {
				$(\"#".$id."\").datepicker({ 
				showOn: \"both\", 
				buttonImage: \"" . url::base() . "media/img/icon-calendar.gif\", 
				buttonImageOnly: true 
				});
				});
			</script>";	
	}
	
}