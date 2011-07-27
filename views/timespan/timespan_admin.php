
<h1><? echo Kohana::lang('timespan.timespan');?></h1>
<h4> 
	<br/> <? echo Kohana::lang('timespan.timespan_description');?>
<h4>
<br/>
<br/>
<?php print form::open(); ?>

	<?php if ($form_error) { ?>
	<!-- red-box -->
		<div class="red-box">
			<h3><?php echo Kohana::lang('ui_main.error');?></h3>
			<ul>
				<?php
				foreach ($errors as $error_item => $error_description)
				{
				// print "<li>" . $error_description . "</li>";
				print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
				}
				?>
			</ul>
			</div>
	<?php } ?>


	<?php  if ($form_saved) {?>
		<!-- green-box -->
		<div class="green-box">
		<h3><?php echo Kohana::lang('ui_main.configuration_saved');?></h3>
		</div>
	<?php } ?>


<div>
	<div class="row">
		<h4><? echo Kohana::lang('timespan.interval');?></h4>
		<?php print form::dropdown('interval_mode',$interval_mode, $form['interval_mode']); ?>
	</div>
	<br/>
	<div class="row">
		<h4><? echo Kohana::lang('timespan.calculate');?></h4>
		<?php print form::dropdown('mode',$mode, $form['mode']); ?>
	</div>
	<br/>
	<div class="row">
		<h4><? echo Kohana::lang('timespan.days');?></h4><? echo Kohana::lang('timespan.back');?>
	<br/>
		<?php print form::input('days_back', $form['days_back']); ?>
	</div>
	<br/>
	<div class="row">
		<h4><? echo Kohana::lang('timespan.dates');?></h4>
		<table>
			<tr>
				<td>
					<div class="date-box">
						<h4><? echo Kohana::lang('timespan.start');?></h4> <span><?php echo Kohana::lang('ui_main.date_format');?></span>
						<?php print form::input('start_date', $form['start_date'], ' class="text"'); ?>								
						<?php print $date_picker_js_start; ?>				    
					</div>
				</td>
				<td>
					<div class="date-box">
						<h4><? echo Kohana::lang('timespan.end');?></h4> <span><?php echo Kohana::lang('ui_main.date_format');?></span>
						<?php print form::input('end_date', $form['end_date'], ' class="text"'); ?>								
						<?php print $date_picker_js_end; ?>				    
					</div>
				</td>
			<tr>
		<table>
	</div>
	
</div>
<br/>

<input type="image" src="<?php echo url::base() ?>media/img/admin/btn-save-settings.gif" class="save-rep-btn" style="margin-left: 0px;" />

<?php print form::close(); ?>
