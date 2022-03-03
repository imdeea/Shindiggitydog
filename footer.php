		<?
		$debug = false;
		if ($debug) { ?>
			<center><pre style="background-color:black;"><small style="color: white;"><? var_dump($_COOKIE); ?></small></pre></center>
		<? } ?>

		<script src="/assets/js/bootstrap.bundle.min.js"></script>
		<script src="/rxs.js"></script>
		<script type="text/javascript" src="assets/js/jquery.scrollify.js"></script>
		<script>
		$(function () {
			<?=@$onready_more?>
		});
		</script>
	</body>
</html>
