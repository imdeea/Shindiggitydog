<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once("security.php");
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

	# genres
	if (isset($_POST['catIDs'])) {
		foreach ($_POST['catIDs'] as $catID) if (sanitize_int($catID, 0)) $catIDs[] = sanitize_int($catID, 0);
	}
}

$top_nav = 'discover';
include('header.php'); ?>

	<div class="container">
		<div class="row">
			<div class="col-lg-10 order-2 order-lg-1">
				<div class="container px-0">
					<div id="event-cards" class="row row-cols-md-2 row-cols-1 g-lg-3">
						<?
						$query = "SELECT p.id, p.name, p.neighborhood, p.venueID, f.id as favorite, o.id as follow, v.profile_pic as venue_image,
									GROUP_CONCAT(DISTINCT t.descr SEPARATOR '</button> &nbsp;<button class=\"btn categories\">') as genres,
									i.handle
								FROM places p
						";
						# age restriction
						if (isset($age)) $query .= "JOIN places_details d ON p.id = d.placeID AND d.detailID = " . $age . " ";
						# genres
						if (isset($catIDs)) $query .= "JOIN places_categories c ON p.id = c.placeID AND c.catID IN (" . implode(',', $catIDs) . ") ";
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
						//echo($query);
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
			<div id="filters-wrapper" class="col-lg-2 order-1 order-lg-2">
				<div id="filters-button" class="col-12 d-block d-lg-none text-end ">
					<button class="btn btn-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#filters" aria-expanded="false" aria-controls="filters"><i class="fa-light fa-sliders"></i></button>
				</div>
				<div id="filters" class="collapse d-lg-block">

					<form method="POST" action="">

						<h5 class="fun-font">City</h5>
						<div class="input-group">
							<input type="text" name="city_search" id="city_search" placeholder="Boise" class="form-control ui-autocomplete-input" style="display:inline-block;" autocomplete="off" value="<?=@$_POST['city_search']?>">
							<input type="hidden" name="cityID" id="cityID" value="<?=(isset($_POST['cityID']) && sanitize_int($_POST['cityID'], 0))?$_POST['cityID']:''?>">
						</div>

						<h5 for="date" class="fun-font mt-4">Dates</h5>
						<div id="range">
							<input type="hidden" name="date" id="date" value="<?=(isset($from) && isset($to))?($from . ' - ' . $to):''?>">
						</div>

						<h5 for="catIDs[]" class="fun-font mt-4">Genres</h5>
						<div class="input-group btn-group">
							<?
							$query = "SELECT id, descr
									FROM categories
									ORDER BY descr
							";
							$i = 0;
							$result = mysqli_query($link, $query);
							if ($result && mysqli_num_rows($result) > 0) {

								while ($row = mysqli_fetch_assoc($result)) {
									$i++;
									?>
									<input type="checkbox" name="catIDs[]" value="<?=$row['id']?>"<? if (isset($catIDs) && in_array($row['id'], $catIDs)) echo(' checked'); ?> class="btn-check" id="btncheck<?=$i?>" autocomplete="off">
									<label class="btn btn-outline-primary" for="btncheck<?=$i?>"><?=$row['descr']?></label>
									<?
								}
							}
							?>
						</div>

						<h5 for="ticket_price" class="fun-font mt-4">Ticket Price</h5>
						<div id="ticket_price">
							<div id="custom-handle" class="ui-slider-handle"></div>
							<input type="hidden" name="max_price" id="max_price" value="<?=(isset($_POST['max_price']) && sanitize_int($_POST['max_price'], 0))?$_POST['max_price']:'250'?>">
						</div>

						<h5 for="detailIDs" class="fun-font mt-4">Age Restriction</h5>
						<div class="input-group btn-group">
							<input type="radio" name="age" value="28" id="age_28" class="btn-check" autocomplete="off"<?=(isset($age) && $age == '28')?' checked':''?>>
							<label class="btn btn-outline-primary" for="age_28">18+</label>
							<input type="radio" name="age" value="29" id="age_29" class="btn-check" autocomplete="off"<?=(isset($age) && $age == '29')?' checked':''?>>
							<label class="btn btn-outline-primary" for="age_29">21+</label>
							<input type="radio" name="age" value="30" id="age_30" class="btn-check" autocomplete="off"<?=(@$age != '28' && @$age != '29')?' checked':''?>>
							<label class="btn btn-outline-primary" for="age_30">No Restriction</label>
						</div>


						<button class="flex-grow-1 btn btn-dark btn-lg mt-20 mt-4" type="submit">Search</button>
						<input type="hidden" name="submitted" value="yes">
					</form>

				</div>
			</div>
		</div>
	</div>

<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/plugins/mobilefriendly.js"></script>
<script>
let vh = window.innerHeight * 0.01;
document.documentElement.style.setProperty('--vh', `${vh}px`);

// date selector
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

/*
$(() => {
  const stuckClass = 'is-stuck';
  const $stickyTopElements = $('.sticky-top');

  const determineSticky = () => {
    $stickyTopElements.each((i, el) => {
      const $el = $(el);
      const stickPoint = parseInt($el.css('top'), 0);
      const currTop = el.getBoundingClientRect().top;
      const isStuck = currTop <= stickPoint;
      $el.toggleClass(stuckClass, isStuck);
    });

  };

  //run immediately
  determineSticky();

  //Run when the browser is resized or scrolled
  //Uncomment below to run less frequently with a debounce
  //let debounce = null;
  $(window).on('resize scroll', () => {
    //clearTimeout(debounce);
    //debounce = setTimeout(determineSticky, 100);

    determineSticky();
  });

});

var myCollapsible = document.getElementById('filters');
myCollapsible.addEventListener('show.bs.collapse', function () {
  	document.querySelector("body").classList.toggle("filters-shown");
});
myCollapsible.addEventListener('hide.bs.collapse', function () {
  	document.querySelector("body").classList.toggle("filters-shown");
});
*/

</script>

<?
$onready_more = <<<EOT
	$(window).resize(function() {
		var width = $(this).width();
		var height = $(this).height();
		console.log('width -> ' + width)
		console.log('height -> ' + height)
		if (width <= 990) {
			var offsetSize =  calculateHeights();
			$('#event-cards').fullpage({
				//options here
				responsiveWidth: 0,
				responsiveHeight: 0,
				//verticalCentered: true,
				paddingTop: offsetSize+'px',
				afterRender: function(){
				   var pluginContainer = this;
				   console.log(pluginContainer);
				   $('#event-cards').css('margin-top', '-'+offsetSize+'px');
			   }
			});
			$('#filters-wrapper').addClass('sticky-top');
		}
	});
	function calculateHeights(){
		const headerHeight = $('nav').outerHeight();
		const filterHeight = $('#filters-button').outerHeight();
		console.log('header -> ' + headerHeight);
		console.log('settings -> ' + filterHeight);
		bothHeight = headerHeight + 16 + filterHeight;
		//$('.event-card:first-child').height('calc(var(--vh, 1vh) * 100 - '+bothHeight+'px - 1rem)');
		return bothHeight - convertRemToPixels(1);
	}
	function convertRemToPixels(rem) {
	    return rem * parseFloat(getComputedStyle(document.documentElement).fontSize);
	}

	$(window).trigger('resize');

	// city selector
     $("#city_search").autocomplete({
		appendTo: '#filters',
		source: 'cities_search.php',
		select: function(event, ui) {
			var selectedObj = ui.item;
			$(this).val(selectedObj.label);
			$('#cityID').val(selectedObj.value);
			return false;
		}
     });

     // ticket price selector
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
