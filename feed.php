<?
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once("security.php");
require_once("config.php");

$link = mysqli_connect($db_host, $db_user, $db_pasw, $db_db);

$top_nav = 'feed';
include('header.php'); ?>

	<div class="container content">
		<div class="row">
			<div class="col-lg-10">
				<div class="container px-0">
					<div class="row row-cols-md-2 row-cols-1 g-3">
						<?
						$query = "SELECT p.id, p.name, p.neighborhood, p.venueID, f.id as favorite, o.id as follow, v.profile_pic as venue_image,
									GROUP_CONCAT(DISTINCT t.descr SEPARATOR '</button> &nbsp;<button class=\"btn categories\">') as genres,
									i.handle
								FROM places p
								JOIN places_categories c ON p.id = c.placeID
								LEFT OUTER JOIN favorites f ON f.userID = " . $_COOKIE['id'] . " AND f.placeID = p.id
								LEFT OUTER JOIN places_categories j ON p.id = j.placeID
								LEFT OUTER JOIN categories t ON j.catID = t.id
								LEFT OUTER JOIN places_images i ON i.id = (SELECT id FROM places_images WHERE placeID = p.id ORDER BY seqno DESC LIMIT 1)
								LEFT OUTER JOIN users v ON v.id = p.venueID
								LEFT OUTER JOIN follows o ON o.userID = " . $_COOKIE['id'] . " AND o.followID = v.id
								WHERE p.status != 'I'
								  AND c.catID IN (" . $_COOKIE['catIDs'] . ")
								  AND p.cityID IN (" . $_COOKIE['cityIDs'] . ")
								GROUP BY p.id
								-- ORDER BY ??
						";
						$result = mysqli_query($link, $query);

						if ($result) {
							while ($row = mysqli_fetch_assoc($result)) {
								$row['offer'] = $row['preferred'] = 0;
								include('place_card.php');
							}
						}
						?>
					</div>
				</div>
			</div>

			<div class="col-lg-2">

				<? if ($_COOKIE['cities'] != 'none') { ?>
					<ul class="nav flex-column sub-nav mb-3">
						<?
						$cities = explode(',', $_COOKIE['cities']);
						foreach ($cities as $city) {
							?><li class="nav-item text-end"><span class="cities"><?=$city?></span></li><?
						}
						?>
					</ul>
				<? } else { ?>
					<br>You should select some cities!
				<? } ?>

				<? if ($_COOKIE['genres'] != 'none') { ?>
					<ul class="nav flex-column sub-nav mb-3">
						<?
						$genres = explode(',', $_COOKIE['genres']);
						foreach ($genres as $genre) {
							?><li class="nav-item text-end"><span class="btn categories"><?=$genre?></span></li><?
						}
						?>
					</ul>
				<? } else { ?>
					<br>You should select some genres!
				<? } ?>

				<a href="user_preferences.php" class="float-end" style="padding-right: 8px; color: black; font-size: 1.5rem;"><i class="fa-light fa-sliders"></i></a>

			</div>
		</div>
	</div>

<? include('footer.php'); ?>
