<?
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once("config.php");

$link = mysqli_connect($db_host, $db_user, $db_pasw, $db_db);

include('header.php'); ?>
<div class="container-fluid">
<div class="px-4 py-4 text-center">
	<div class="col-lg-6 mx-auto">
		<p class="lead mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit, magna aliqua.</p>
		<div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
			<?
			$query = "SELECT id, descr FROM categories WHERE active = 1 ORDER BY seqno ";
			$result = mysqli_query($link, $query);
			if ($result && mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_assoc($result)) {
					?>
					<a href="category.php?id=<?=$row['id']?>" type="button" class="btn btn-warning btn-lg px-4"><?=$row['descr']?></a>
					<?
				}
			}

			if (isset($_COOKIE['id']) && is_numeric($_COOKIE['id'])) {
				?>
				<a href="favorites.php" type="button" class="btn btn-dark btn-lg px-4">Favorites</a>
				<?
			}
			?>
		</div>
		<? if (!(isset($_COOKIE['id']) && is_numeric($_COOKIE['id']))) { ?>

			<div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
				<a href="login.php" type="button" class="btn btn-dark btn-lg px-4">Sign In</a>
				<a href="register.php" type="button" class="btn btn-dark btn-lg px-4">Create Account</a>
			</div>
		<? } ?>

		<?
		$query = "SELECT p.id, p.name
				FROM places p
				WHERE p.status != 'I'
				  AND p.featured_start <= DATE(NOW()) AND DATE(NOW()) <= p.featured_end
				ORDER BY rand()
				LIMIT 1
		";
		$result = mysqli_query($link, $query);
		if ($result && mysqli_num_rows($result) == 1) {
			$featured = mysqli_fetch_assoc($result);
			?>
				<div class="featured-title">
					<h4> <i class="fas fa-star"></i> Featured Listing <i class="fas fa-star"></i> </h4>
				</div>
				<div class="featured-home container py-2 g-2">
					<div class="row">

						<div class="col-12 fh-video">
							<div class="ratio ratio-16x9">
							  <iframe src="https://www.youtube.com/embed/pu7xw4dlYYg?autoplay=1&showinfo=0&controls=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope"></iframe>
							</div>
						</div>
					</div>
					<div class="row m-3 mb-2 g-0">
						<div class="col-sm-8 fh-title d-flex flex-wrap justify-content-center justify-content-sm-start mb-2">
							<h2><?= $featured['name'] ?></h2>
						</div>
						<div class="col-sm-4 fh-info d-flex flex-wrap justify-content-center justify-content-sm-end mb-2">
							<a class="btn btn-secondary " href="place.php?id=<?= $featured['id'] ?>"> Get Directions  <i class="fas fa-location-arrow"></i> </a>
						</div>
					</div>

				</div>

			<?
		}
		?>


	</div>
</div>
</div>
<style>
html, body{
	min-height: 100%;
	width: 100%;
	padding: 0;
}
body {
	background: url(/assets/images/background/line.png) no-repeat 55% center;
	background-size: cover;
	color: white;
}
</style>


<? include('footer.php'); ?>
