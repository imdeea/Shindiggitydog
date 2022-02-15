<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location:index.php"); exit; }

require_once("security.php");
require_once("config.php");
require_once("rxs.php");

$link = mysqli_connect($db_host, $db_user, $db_pasw, $db_db);

# which event?
$query = "SELECT p.*, u.handle, s.descr as state, f.id as favorite, 0 as offer -- , if(DATE(NOW()) >= p.offer_start AND DATE(NOW()) <= p.offer_end, 1, 0) as offer
		FROM places p
		JOIN users u ON p.userID = u.id
		LEFT OUTER JOIN states s ON p.stateID = s.id
		LEFT OUTER JOIN favorites f ON p.id = f.placeID AND f.userID = " . $_COOKIE['id'] . "
		WHERE p.status = 'A'
		AND p.id = " . $_GET['id'] . "
";
$result = mysqli_query($link, $query);
if (!$result) { header("Location:index.php"); exit; }
$place = mysqli_fetch_assoc($result);

# get the images
$query = "SELECT handle FROM places_images WHERE placeID = " . $place['id'] . " ORDER BY seqno DESC ";
$result = mysqli_query($link, $query);
if (!$result  || mysqli_num_rows($result) == 0) $images = array('/assets/images/placeholder.jpg');
else {
	while ($row = mysqli_fetch_assoc($result)) {
		$images[] = 'https://cdn.filestackcontent.com/' . $row['handle'];
	}
}

# record view and start the timer
$query = "INSERT INTO places_views SET
		placeID = " . $_GET['id'] . ",
		userID = " . $_COOKIE['id'] . "
";
mysqli_query($link, $query);
$viewID = mysqli_insert_id($link);

include('header.php'); ?>

	<div class="container">

		<div class="row mb-3 p-3">

			<div class="col-lg-6">
				<div class="event-image-holder">
					<img src="<?=$images[0]?>" class="event_main w-100">
					<a href="#" class="favorite <?=!is_null($place['favorite'])?'active':''?>" id="f<?=$place['id'] ?>" place="<?=$place['id']?>" value="<?=!is_null($place['favorite'])?1:0?>">
						<i class="fa<?=!is_null($place['favorite'])?'s':'l'?> fa-<?=LIKE_SYMBOL?> fa-2x fa-fw"></i>
					</a>
				</div>
			</div>

			<div class="col-lg-6">

				<section class="py-1 mt-3 page_header">
				    <div class="container">
					   <div class="d-flex justify-content-between">
						<div class="col-12">
							<h1 class="fun-font"><?=strtoupper($place['name'])?></h1>
						</div>
					   </div>
				    </div>
				</section>

				<main class="container pb-3 px-3">
					<div class="d-flex justify-content-between align-items-center pt-3">
						<p class="pb-3 mb-0  w-100 text-justify">
							<?= $place['blurb'] ?>
						</p>
					</div>

					<div class="d-flex justify-content-between">
						<div class="action-group px-1 ms-0">
							<?
							$query = "SELECT c.id, c.descr, c.icon
									FROM categories c
									JOIN places_categories j ON c.id = j.catID AND j.placeID = " . $place['id'] . "
									ORDER BY c.descr
							";
							$i = 0;
							$result = mysqli_query($link, $query);
							if ($result && mysqli_num_rows($result) > 0) {
								while ($row = mysqli_fetch_assoc($result)) {
									$i++;
									?>
									<button class="btn categories"><?=$row['descr']?></button> &nbsp;
									<?
								}
							}
							?>
						</div>
					</div>

					<?
					# get the occurrance information
					$query = "SELECT *
							FROM places_schedules
							WHERE placeID = " . $place['id'] . "
							  AND ((end IS NOT NULL AND end >= '" . date('Y-m-d') . "') OR (end IS NULL AND begin >= '" . date('Y-m-d') . "'))
							ORDER BY begin, end
					";
					$result = mysqli_query($link, $query);
					if ($result && mysqli_num_rows($result) > 0) {

						# how many actual date ranges do we have?
						$ranges = array();
						while ($row = mysqli_fetch_assoc($result)) {
							$key = $row['begin'] . '-' . $row['end'];
							$ranges[$key][] = $row;
						}
						?>
						<div class="row text-sharp pt-3 event-dates">
							<div class="col-md-2 text-center icon-col">
								<i class="fal fa-calendar-day fa-fw fa-2x me-2 pt-1"></i>
							</div>
							<div class="col-md-10">
								<ul class="list-unstyled mb-0 w-100">
									<?
									$first_main = true;
									foreach ($ranges as $variations) {
										# spacer
										if ($first_main) $first_main = false;
										else echo('<br>');

										# dates
										echo(pretty_date_range($variations[0]['begin'], $variations[0]['end']));

										foreach ($variations as $row) {

											if (!is_null($row['dow'])) {
												echo('<br>each ');
												$first = true;
												foreach (explode(',', $row['dow']) as $dow) {
													if ($first) $first = false;
													else echo(', ');
													echo($dows[$dow]);
												}
											}

											if (!is_null($row['dom'])) {
												echo('<br>on the ');
												$first = true;
												foreach (explode(',', $row['dom']) as $dom) {
													if ($first) $first = false;
													else echo(', ');
													echo(ordinal($dom));
												}
												echo(' of each month');
											}

											if (!is_null($row['starts']) || !is_null($row['ends'])) echo('<br>');
											if (!is_null($row['starts']) && is_null($row['ends'])) echo('<i class="fal fa-at"></i> ');
											if (!is_null($row['starts'])) echo(date('g:ia', strtotime($row['starts'])));
											if (!is_null($row['starts']) && !is_null($row['ends'])) echo(' - ');
											if (!is_null($row['ends'])) echo(date('g:ia', strtotime($row['ends'])));

										}
									}
									?>
								</ul>
							</div>
						</div>
						<?
					}
					?>





					<div class="mb-3 py-3">

						<?
						if (!empty($place['ticket_needed'])) { ?>
					 		<div class="row text-sharp pt-3">
								<div class="col-md-2 text-center icon-col">
									<i class="fal fa-ticket fa-fw fa-2x me-2 pt-1 fa-rotate-by" style="--fa-rotate-angle: 45deg;"></i>
								</div>
								<div class="col-md-10 align-items-center">
									<?php if (!is_null($place['ticket_price_low']) && $place['ticket_needed'] != "N") { ?>

												<p class="pb-0 mb-0 lh-sm w-100 fun-font info-fontsize">
													<?
													if ($place['ticket_price_low'] != $place['ticket_price_high']) {
														 ?>$<?=$place['ticket_price_low']?> &ndash; $<?=$place['ticket_price_high']?><?
													} else {
														if ($place['ticket_price_low'] == 0) {
															 ?>FREE<?
														} else {
															 ?>$<?=$place['ticket_price_low']?><?
														}
													}
													?>
												</p>
										<?
									} ?>
									<p class="lh-sm w-100 text-sharp mb-0">
										<?
										if ($place['ticket_needed'] == "A") {
											?>Advance tickets available.<?
										} elseif ($place['ticket_needed'] == "D") {
											?>Ticketing at the door.<?
										} elseif ($place['ticket_needed'] == "N") {
											?>No ticket necessary.<?
										}
										if (!empty($place['ticket_url'])) {
											?><a href="<?=$place['ticket_url']?>" class="text-decoration-none ps-2 link-arrow"><i class="fas fa-arrow-up-right"></i></a><?
										}
										?>
									</p>

								</div>
							</div>
							<?
						}

						if (!empty($place['address'])) { ?>
					 		<div class="row text-sharp pt-3">
								<div class="col-md-2 text-center icon-col">
									<i class="fal fa-location-smile fa-fw fa-2x me-2 pt-1"></i>
								</div>
								<div class="col-md-10 align-items-center location-info">
									<div class="location-name fun-font info-fontsize"> Lorem Ipsum Dolor sit Amet</div>
									<p class="lh-sm w-100 mb-0">
										<a href="https://www.google.com/maps/dir/?api=1&destination=<?=urlencode($place['address'])?>,+<?=urlencode($place['city'])?>,+<?=urlencode($place['state'])?>+<?=urlencode($place['zip'])?>,+USA" class="text-decoration-none text-sharp">
											<?=$place['address']?><br>
											<?=$place['city']?>, <?=$place['state']?> <?=$place['zip']?>
										</a>
									</p>
									<div class="location-tip mt-3"> Special instructions for this venue: <span>The door looks like a wearhouse door. Knock on it and someone will let you in.</span></div>
								</div>
								<div class="col-md-12">
									<div class="location-map mt-3">
										<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d377636.5758638698!2d-71.36014211344579!3d42.31510366846641!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89e37a76a6e59fb3%3A0x681acded12b72b1!2sJacques&#39;%20Cabaret!5e0!3m2!1sen!2sro!4v1644918294391!5m2!1sen!2sro" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
									</div>
								</div>
							</div>
							<?
						}
						?>

				 		<div class="d-flex text-sharp pt-3">
							<i class="fal fa-user fa-fw fa-2x me-2 pt-1"></i>
							<p class="pb-3 mb-0 lh-sm w-100 text-end">
								<a href="profile.php?id=<?=$place['userID']?>">@<?=$place['handle']?></a>
							</p>
						</div>
						<?

						if (!empty($place['phone'])) { ?>
					 		<div class="d-flex text-sharp pt-3">
								<i class="fal fa-phone fa-fw fa-2x me-2 pt-1"></i>
								<p class="pb-3 mb-0 lh-sm w-100 text-end">
									<a href="tel:+1<?=$place['phone']?>" class="text-decoration-none text-sharp"><?=mask_phone($place['phone'])?></a>
								</p>
							</div>
							<?
						}
						if (!empty($place['email'])) { ?>
					 		<div class="d-flex text-sharp pt-3">
								<i class="fal fa-envelope fa-fw fa-2x me-2 pt-1"></i>
								<p class="pb-3 mb-0 lh-sm w-100 text-end">
									<a href="mailto:<?=$place['email']?>" class="text-decoration-none text-sharp"><?=$place['email']?></a>
								</p>
							</div>
							<?
						}
						if (!empty($place['url'])) { ?>
					 		<div class="d-flex text-sharp pt-3">
								<i class="fal fa-spider-web fa-fw fa-2x me-2 pt-1"></i>
								<p class="pb-3 mb-0 lh-sm w-100 text-end">
									<a href="<?=$place['url']?>" class="text-decoration-none text-sharp"><?=$place['url']?></a>
								</p>
							</div>
							<?
						}

						?>

						<div class="row d-flex justify-content-center align-items-center mt-3">
								<? if (!empty($place['capacity'])) { ?>
										<div class="col-auto"> Capacity: <?= $place['capacity'] ?> </div>
								<?php } ?>
								<? if (!empty($place['duration'])) { ?>
										<div class="col-auto"> Duration: <?= $place['duration'] ?> </div>
								<?php } ?>
						</div>

						<div class="row d-flex justify-content-center align-items-center mt-3">
							<?
							$query = "SELECT d.descr
									FROM places_details pd
									JOIN details d ON pd.detailID = d.id AND pd.placeID = " . $place['id'] . "
							";
							$result = mysqli_query($link, $query);
							if ($result && mysqli_num_rows($result) > 0) {
								while ($row = mysqli_fetch_assoc($result)) {
									?>
									<div class="col-auto"> <?=$row['descr']?></div>
									<?
								}
							}
							?>
						</div>

						<div class="row d-flex justify-content-center align-items-center mt-5">
							<?php
							$query = "SELECT s.icon, s.link, ps.value
									FROM places_socials ps
									JOIN socials s ON ps.socialID = s.id AND ps.placeID = " . $place['id'] . "
							";
							$result = mysqli_query($link, $query);
							if ($result && mysqli_num_rows($result) > 0) {
								while ($row = mysqli_fetch_assoc($result)) {
									?>
									<div class="col-auto"><a href="<?=$row['link']?><?=$row['value']?>"><i class="<?=$row['icon']?> fa-2x"></i></a></div>
									<?
								}
							}
							?>
						</div>
					</div>
				</main>

				<div class="modal fun-font" tabindex="-1" id="offer_modal">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								<h6 class="modal-title" id="staticBackdropLabel">An offer from <?=$place['name']?></h6>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<p><?=$place['offer_text']?>.</p>
							</div>
							<div class="modal-footer">
								To get this offer, find and scan the QR code posted at <?=$place['name']?> and show the coupon to your server.
							</div>
						</div>
					</div>
				</div>

				<? /*
				<div class="container py-3 px-3 fun-font">
					<div class="row row-cols-1">
						<div class="d-flex justify-content-between align-items-center">
							<div class="btn-group" role="group">
								<a href="category.php?id=<?=$place['catID']?>" class="btn btn-outline-secondary bg-body">Other <?=$place['descr']?> Nearby</a>
							</div>
						</div>
					</div>
				</div>
				*/ ?>

			</div>

		</div>
	</div>

	<style>
	@media (max-width: 600px) {
		main { font-size: calc(3vw); }
	}
	button .fad { font-size: 2em; }
	</style>

	<script src="/assets/js/screentime.js"></script>
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

		// https://screentime.parsnip.io/
		$.screentime({
			fields: [
				{ selector: '#feed', name: 'feed' }
			],
		 	callback: function(results) {
				console.log(results);
				$.ajax({
					url: "view_place_handler.php",
					type: "GET",
					data: { viewID:<?=$viewID?>, json:results }
				});
			}
		});
	});
	</script>

<? include('footer.php'); ?>
