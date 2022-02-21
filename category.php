<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location:index.php"); exit; }

require_once("security.php");
require_once("config.php");

$link = mysqli_connect($db_host, $db_user, $db_pasw, $db_db);

# if distance was changed
if (isset($_GET['distance']) && is_numeric($_GET['distance'])) {
	setcookie('distance', $_GET['distance'], time() + (86400 * 30)); # 86400 = 1 day
	$_COOKIE['distance'] = $_GET['distance'];
# if distance has not been set yet
} elseif (!isset($_COOKIE['distance'])) {
	setcookie('distance', 5, time() + (86400 * 30)); # 86400 = 1 day
	$_COOKIE['distance'] = 5;
}

# if zip was entered
if (isset($_POST['location']) && is_numeric($_POST['location'])) {
	setcookie('zip', $_POST['location'], time() + (86400 * 30)); # 86400 = 1 day
	$_COOKIE['zip'] = $_POST['location'];

	$query = "SELECT Latitude, Longitude  FROM ZIPCodes WHERE ZipCode = '" . $_POST['location'] . "' ";
	$result = mysqli_query($link, $query);
	if ($result && mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
		setcookie('Latitude', $row['Latitude'], time() + (86400 * 30)); # 86400 = 1 day
		setcookie('Longitude', $row['Longitude'], time() + (86400 * 30)); # 86400 = 1 day
		$_COOKIE['Latitude'] = $row['Latitude'];
		$_COOKIE['Longitude'] = $row['Longitude'];
	}
}

# if current location was pressed
if (isset($_GET['location']) && $_GET['location'] == 'get') {



}

# which category?
$query = "SELECT descr, icon FROM categories WHERE active = 1 AND id = " . $_GET['id'] . " ";
$result = mysqli_query($link, $query);
if (!$result) { header("Location:index.php"); exit; }
$category = mysqli_fetch_assoc($result);

include('header.php'); ?>

	<section class="py-1 mb-2 text-center page_header">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-8 mx-auto">
					<h1 class="fw-medium"><?=$category['icon']?> &nbsp; <?=$category['descr']?></h1>
				</div>
		   </div>
	    </div>
	</section>

	<div class="container px-3">
		<div class="row">
			<div class="col-lg-10">
				<div class="container px-0">
					<div class="row row-cols-md-2 row-cols-1 g-3">
						<?
						$query = "SELECT p.id, p.name, f.id as favorite,
									0 as preferred, -- if(DATE(NOW()) >= p.preferred_start AND DATE(NOW()) <= p.preferred_end, 1, 0) as preferred,
									0 as offer, -- if(DATE(NOW()) >= p.offer_start AND DATE(NOW()) <= p.offer_end, 1, 0) as offer,
									(((acos(sin((".$_COOKIE['Latitude']."*pi()/180)) * sin((p.latitude*pi()/180)) + cos((".$_COOKIE['Latitude']."*pi()/180)) * cos((p.latitude*pi()/180)) * cos(((".$_COOKIE['Longitude']."- p.longitude)*pi()/180)))) * 180/pi()) * 60 * 1.1515) as distance,
									GROUP_CONCAT(t.descr SEPARATOR '</button> &nbsp;<button class=\"btn categories\">') as genres,
									i.handle
								FROM places p
								JOIN places_categories c ON p.id = c.placeID
								LEFT OUTER JOIN favorites f ON f.userID = " . $_COOKIE['id'] . " AND f.placeID = p.id
								LEFT OUTER JOIN places_categories j ON p.id = j.placeID
								LEFT OUTER JOIN categories t ON j.catID = t.id
								LEFT OUTER JOIN places_images i ON i.id = (SELECT id FROM places_images WHERE placeID = p.id ORDER BY seqno DESC LIMIT 1)
								WHERE p.status != 'I'
								  AND c.catID = " . $_GET['id'] . "
								GROUP BY p.id
								-- HAVING distance <= " . $_COOKIE['distance'] . "
								ORDER BY distance ASC -- preferred DESC, distance ASC
						";
						$result = mysqli_query($link, $query);

						if ($result) {
							while ($row = mysqli_fetch_assoc($result)) {
								include('place_card.php');
							}
						}
						?>
					</div>
				</div>
			</div>

			<div class="col-lg-2">
				<div class="d-flex justify-content-between align-items-center">
					<div class="btn-toolbar mb-md-0">
						<div class="btn-group fun-font">
							<button class="btn btn-sm btn-light"><i class="fas fa-arrows-alt"></i></button>
							<a type="button" href="?id=<?=$_GET['id']?>&distance=1" class="btn btn-sm btn<?=$_COOKIE['distance']==1?'':'-outline'?>-light">1</a>
							<a type="button" href="?id=<?=$_GET['id']?>&distance=5" class="btn btn-sm btn<?=$_COOKIE['distance']==5?'':'-outline'?>-light">5</a>
							<a type="button" href="?id=<?=$_GET['id']?>&distance=10" class="btn btn-sm btn<?=$_COOKIE['distance']==10?'':'-outline'?>-light">10</a>
							<a type="button" href="?id=<?=$_GET['id']?>&distance=25" class="btn btn-sm btn<?=$_COOKIE['distance']==25?'':'-outline'?>-light">25</a>
						</div>
					</div>
				</div>
				<div class="d-flex justify-content-between align-items-center">
					<form class="row row-cols-lg-automb-md-0 needs-validation" novalidate method="POST" action="?id=<?=$_GET['id']?>">
						<div class="col-12">
							<label class="visually-hidden" for="inlineFormInputGroupZip">Zipcode</label>
							<div class="input-group has-validation">
								<input type="text" class="form-control fun-font" name="location" id="inlineFormInputGroupZip" placeholder="Zipcode" value="<?=$_COOKIE['zip']?>" style="border: 1px solid #6c757d; border-radius: .25rem 0 0 .25rem; padding: .15rem .375rem;">
								<button type="submit" class="input-group-text btn btn-warning" style="border: 1px solid #6c757d; border-radius: 0 .25rem .25rem 0; padding: .15rem .375rem;"><i class="fas fa-chevron-double-right"></i></button>
							</div>
						</div>
					</form>
					<a href="?id=<?=$_GET['id']?>&location=get" class="text-secondary text-wrap" style="font-size:.75rem; margin-left:.5rem; line-height:0.75rem;">Current Location</a>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	$(function() {
		$(".favorite").click(function(e) {
			e.preventDefault();

			var icon = $(this).find('i');
			if ($(this).attr('value') == 1) {
				_newValue = 0;
				_oldIcon = 'fas';
				_newIcon = 'fal';
			} else {
				_newValue = 1;
				_oldIcon = 'fal';
				_newIcon = 'fas';
			}
			_placeID = $(this).attr('place');
			_thisFavoriteButton = $(this);
			$.ajax({
				url: "favorite_place_handler.php",
				type: "POST",
				data: { placeID:_placeID, newValue:_newValue },
				success: function(result) {
					icon.removeClass(_oldIcon).addClass(_newIcon);
					_thisFavoriteButton.attr('value', _newValue);
				}
			});
		});
	});
	</script>

<? include('footer.php'); ?>
