<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once("config.php");
include('rxs.php');

$link = mysqli_connect($db_host, $db_user, $db_pasw, $db_db);

if (@$_POST["submitted"] == "yes") {

	# city
	if (isset($_POST['cityID']) && !empty($_POST['city_search'])) {
		$cityID = sanitize_int($_POST['cityID'], 0);
		if (!$cityID) unset($cityID);
	}

	# date
	if (isset($_POST['date']) && !empty($_POST['date'])) {
		# 2022-03-15 - 2022-03-19
		list($from, $to) = explode(' - ', $_POST['date']);
		if (!is_date($from)) unset($from);
		if (!is_date($to)) unset($to);
	}

	# price
	if (isset($_POST['max_price'])) {
		$max_price = sanitize_int($_POST['max_price'], NULL);
		if (is_null($max_price)) unset($max_price);
		if (isset($max_price) && $max_price == 250) unset($max_price);
	}

	# age
	if (isset($_POST['age'])) {
		$age = sanitize_int($_POST['age'], 0);
		if (!in_array($age, array(28, 29, 30))) unset($age);
	}
}

$top_nav = 'discover';
include('header.php'); ?>

	<div class="container content">
		<div class="row">
			<div class="col-12 d-block d-lg-none text-end">
				<button class="btn btn-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#filters" aria-expanded="false" aria-controls="filters"><i class="fa-light fa-sliders"></i></button>
			</div>
			<div class="col-lg-10 order-2 order-lg-1">
				<div class="container px-0">
					<div class="row row-cols-md-2 row-cols-1 g-3">
						<?
						$query = "SELECT p.id, p.name, p.neighborhood, p.venueID, f.id as favorite, o.id as follow, v.profile_pic as venue_image,
									GROUP_CONCAT(DISTINCT t.descr SEPARATOR '</button> &nbsp;<button class=\"btn categories\">') as genres,
									i.handle
								FROM places p
						";
						# age restriction
						if (isset($age)) $query .= "JOIN places_details d ON p.id = d.placeID AND d.detailID = " . $age . " ";
						$query .= "LEFT OUTER JOIN favorites f ON f.userID = " . $_COOKIE['id'] . " AND f.placeID = p.id
								 LEFT OUTER JOIN places_categories j ON p.id = j.placeID
								 LEFT OUTER JOIN categories t ON j.catID = t.id
								 LEFT OUTER JOIN places_images i ON i.id = (SELECT id FROM places_images WHERE placeID = p.id ORDER BY seqno DESC LIMIT 1)
								 LEFT OUTER JOIN users v ON v.id = p.venueID
								 LEFT OUTER JOIN follows o ON o.userID = " . $_COOKIE['id'] . " AND o.followID = v.id
								 WHERE p.status != 'I'
						";
						# city search
						if (isset($cityID)) $query .= "AND p.cityID = " . $cityID . " ";
						# date search
						if (isset($from) && isset($to)) $query .= "AND p.id IN (SELECT DISTINCT placeID FROM places_calendar WHERE date BETWEEN '" . $from . "' AND '" . $to . "') ";
						# ticket price max
						if (isset($max_price)) $query .= "AND ticket_price_low <= " . $max_price . " ";
						$query .= "GROUP BY p.id
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

			<div id="filters" class="col-lg-2 order-1 order-lg-2 collapse d-lg-block">

				<form method="POST" action="">

					<h5 class="fun-font">City</h5>
					<div class="input-group has-validation">
						<input type="text" name="city_search" id="city_search" placeholder="Boise" class="form-control ui-autocomplete-input" style="display:inline-block;" autocomplete="off" value="<?=@$_POST['city_search']?>">
						<input type="hidden" name="cityID" id="cityID" value="<?=(isset($_POST['cityID']) && sanitize_int($_POST['cityID'], 0))?$_POST['cityID']:''?>">
					</div>

					<h5 for="date" class="fun-font mt-4">Dates</h5>
					<div id="range">
						<input type="hidden" name="date" id="date" value="<?=(isset($from) && isset($to))?($from . ' - ' . $to):''?>">
					</div>

					<h5 for="ticket_price" class="fun-font mt-4">Ticket Price</h5>
					<div id="ticket_price">
						<div id="custom-handle" class="ui-slider-handle"></div>
						<input type="hidden" name="max_price" id="max_price" value="<?=(isset($_POST['max_price']) && sanitize_int($_POST['max_price'], 0))?$_POST['max_price']:'250'?>">
					</div>

					<h5 for="detailIDs" class="fun-font mt-4">Age Restriction</h5>
					<div class="input-group">
						<div class="btn-group" role="group" aria-label="Age Restrictions">
							<input type="radio" name="age" value="28" id="age_28" class="btn-check" autocomplete="off"<?=(isset($_POST['age']) && $_POST['age'] == '28')?' checked':''?>>
							<label class="btn btn-outline-primary" for="age_28">18+</label>
							<input type="radio" name="age" value="29" id="age_29" class="btn-check" autocomplete="off"<?=(isset($_POST['age']) && $_POST['age'] == '29')?' checked':''?>>
							<label class="btn btn-outline-primary" for="age_29">21+</label>
							<input type="radio" name="age" value="30" id="age_30" class="btn-check" autocomplete="off"<?=(@$_POST['age'] != '28' && @$_POST['age'] != '29')?' checked':''?>>
							<label class="btn btn-outline-primary" for="age_30">No Restriction</label>
						</div>
					</div>


					<button class="flex-grow-1 btn btn-dark btn-lg mt-20 mt-4" type="submit">Search</button>
					<input type="hidden" name="submitted" value="yes">
				</form>

			</div>
		</div>
	</div>

<style>
:root {
	--litepicker-day-width: calc(100% / 7);
}
.litepicker {
    font-family: inherit;
    width: 100%;
}
.litepicker .container__months {
	background-color: transparent;
	-webkit-box-shadow: inherit;
	box-shadow: inherit;
}
.litepicker .container__months .month-item-header {
    padding: 0 0 10px;
}
.litepicker .container__months .month-item {
    padding: 0;
}
.litepicker .container__days .day-item:hover {
	background-color: #ffdb99;
	-webkit-box-shadow: inherit;
	box-shadow: inherit;
}
#ticket_price {
	width: calc(100% - 3.5em);
	margin-left: .5em;
}
#custom-handle {
    width: 4em;
    height: 1.6em;
    top: 50%;
    margin-top: -.8em;
    text-align: center;
    line-height: 1.6em;
    font-size: .9rem;
	font-family: realist,sans-serif;
	font-weight: 400;
	font-style: normal;
    color: #fff;
    background-color: rgb(0,0,0,.8);
    border-color: #1a1e21;
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" />
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/plugins/mobilefriendly.js"></script>
<script>
const picker = new Litepicker({
	element: document.getElementById('date'),
	parentEl: document.getElementById('range'),
	singleMode: false,
	inlineMode: true,
	format: 'YYYY-MM-DD',
	plugins: ['mobilefriendly'],
	minDate: "<?=date('Y-m-d')?>",
	maxDate: "<?=date('Y-m-d', strtotime('+1 year'))?>",
	dropdowns: {
	    minYear: 2022,
	    maxYear: 2022,
	    months: false,
	    years: false
	}
});


</script>

<?
$onready_more = <<<EOT
     $("#city_search").autocomplete({
		source: 'cities_search.php',
		select: function(event, ui) {
			var selectedObj = ui.item;
			$(this).val(selectedObj.label);
			$('#cityID').val(selectedObj.value);
			return false;
		}
     });

	var handle = $("#custom-handle");
	$("#ticket_price").slider({
		min: 0,
		max: 250,
		step: 25,
		value:
EOT;
if (isset($_POST['max_price'])) $onready_more .= sanitize_int($_POST['max_price'], 0);
else $onready_more .= '250';
$onready_more .= <<<EOT
		,
		create: function() {
			if ($( this ).slider("value") == 0) { handle.text('FREE'); }
			else if ($( this ).slider("value") == 250) { handle.text('ANY'); }
			else { handle.text($( this ).slider("value")); }
		},
		slide: function( event, ui ) {
			if (ui.value == 0) { handle.text('FREE'); }
			else if (ui.value == 250) { handle.text('ANY'); }
			else { handle.text(ui.value); }
			$("#max_price").val( ui.value );
		}
	});
EOT;
?>

<? include('footer.php'); ?>
