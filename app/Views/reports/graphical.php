<?php echo view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<div id="page_title"><?php echo $title ?></div>

<div id="page_subtitle"><?php echo $subtitle ?></div>

<div class="ct-chart ct-golden-section" id="chart1"></div>

<?php echo view($chart_type); ?>

<div id="chart_report_summary">
	<?php
	foreach($summary_data_1 as $name=>$value)
	{
	?>
		<div class="summary_row"><?php echo lang('reports_'.$name). ': ' . to_currency($value); ?></div>
	<?php
	}
	?>
</div>

<?php echo view("partial/footer"); ?>