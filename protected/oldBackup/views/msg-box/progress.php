<style>
    body {
        padding-top: 0px;
    }
</style>
<meta http-equiv="refresh" content="300">
<?php if (0 != $progress): ?>
	<div class="row">
		<div class="col-md-4 ml-3 mt-5 mr-3">
			<div class="progress themed-progress">
				<div class="progress-bar" role="progressbar" style="width: <?php echo $progress ?>%;"><?php echo $progress ?>%</div>
			</div>
		</div>
	</div>
<?php else: ?>
	<div class="row">
		<div class="col-md-4 ml-3 mt-3" style="width:500px">
			<div class="alert alert-warning">
				<?php echo $msg; ?>
			</div>
		</div>
	</div>
<?php endif ?>