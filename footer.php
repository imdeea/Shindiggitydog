		<?
		$debug = false;
		if ($debug) { ?>
			<center><pre style="background-color:black;"><small style="color: white;"><? var_dump($_COOKIE); ?></small></pre></center>
		<? } ?>

		<script src="/assets/js/bootstrap.bundle.min.js"></script>
		<script>
			$(function () {
	            $('[data-bs-toggle="tooltip"]').tooltip();

				$(".placecard-link").on("mouseenter", function(){
					toggleActiveClass($(this));
				});
				$(".placecard-link").on("mouseleave", function(){
					toggleActiveClass($(this));
				});

				function toggleActiveClass($el){
					$el.parent().toggleClass('active-card');
				}
	        });
		</script>
	</body>
</html>
