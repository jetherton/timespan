<?php defined('SYSPATH') or die('No direct script access allowed.');

/**
 * Timespan settings view
 *
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   John Etherton
 * @package	   Time Span
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */
?>

<div class="bg">
	<h2><?php admin::settings_subtabs("timespan");?></h2>
	<?php print form::open();?>
	<div class="report-form">
		<?php if ($form_error)
		{
		?>
		<!-- red-box -->
		<div class="red-box">
			<h3><?php echo Kohana::lang('ui_main.error');?></h3>
			<ul>
				<?php
				foreach ($errors as $error_item => $error_description)
				{
					print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
				}
				?>
			</ul>
		</div>
		<?php
		}
		if ($form_saved)
		{
		?>
		<!-- green-box -->
		<div class="green-box">
			<h3><?php echo Kohana::lang('ui_main.configuration_saved');?></h3>
		</div>
		<?php
		}
		?>
		<div class="head">
			<h3><?php echo Kohana::lang('timespan.timespan');?></h3>
			<input type="image" src="<?php echo url::file_loc('img');?>media/img/admin/btn-cancel.gif" class="cancel-btn" />
			<input type="image" src="<?php echo url::file_loc('img');?>media/img/admin/btn-save-settings.gif" class="save-rep-btn" />
		</div>
		<!-- column -->
		<div class="sms_holder">
			<div class="row" style="margin-top:20px;">
				<h4><? echo Kohana::lang('timespan.timespan_description');?></h4>
			</div>
			<div class="row">
				<h4><? echo Kohana::lang('timespan.interval');?></h4>
				<?php print form::dropdown('interval_mode', $interval_mode, $form['interval_mode']);?>
			</div>
			<div class="row">
				<h4><? echo Kohana::lang('timespan.calculate');?></h4>
				<?php print form::dropdown('mode', $mode, $form['mode']);?>
			</div>
			<div class="row">
				<h4><? echo Kohana::lang('timespan.days');?></h4><? echo Kohana::lang('timespan.back');?>
				<?php print form::input('days_back', $form['days_back']);?>
			</div>
			<div class="row">
				<h4><? echo Kohana::lang('timespan.dates');?></h4>
				<table>
					<tr>
						<td>
						<div class="date-box">
							<h4><? echo Kohana::lang('timespan.start');?><span><?php echo Kohana::lang('ui_main.date_format');?></span></h4>
							<?php print form::input('start_date', $form['start_date'], ' class="text"');?>
							<?php print $date_picker_js_start;?>
						</div></td>
						<td>
						<div class="date-box">
							<h4><? echo Kohana::lang('timespan.end');?><span><?php echo Kohana::lang('ui_main.date_format');?></span></h4>
							<?php print form::input('end_date', $form['end_date'], ' class="text"');?>
							<?php print $date_picker_js_end;?>
						</div></td>
					<tr>
				</table>
			</div>
		</div>
		<div class="simple_border"></div>
		<input type="image" src="<?php echo url::file_loc('img');?>media/img/admin/btn-save-settings.gif" class="save-rep-btn" />
		<input type="image" src="<?php echo url::file_loc('img');?>media/img/admin/btn-cancel.gif" class="cancel-btn" />
		<?php print form::close();?>
	</div>
</div>
