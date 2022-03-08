		<?
		$debug = false;
		if ($debug) { ?>
			<center><pre style="background-color:black;"><small style="color: white;"><? var_dump($_COOKIE); ?></small></pre></center>
		<? } ?>

		<script src="/assets/js/bootstrap.bundle.min.js"></script>
		<script src="/rxs.js"></script>
		<script type="text/javascript" src="assets/js/jquery.scrollify.js"></script>
		<script type="text/javascript" src="assets/js/fullpage.js"></script>
		<link rel="stylesheet" href="assets/css/fullpage.min.css" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" />
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
		<script>
		$(function () {
			<?=@$onready_more?>
		});
		</script>
	</body>
</html>
