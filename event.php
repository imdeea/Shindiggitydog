<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location:index.php"); exit; }

require_once("security.php");
require_once("config.php");
require_once("rxs.php");

$link = mysqli_connect($db_host, $db_user, $db_pasw, $db_db);

# which event?
$query = "SELECT p.*, u.profile_pic as user_image, v.profile_pic as venue_image, v.name as venue_name, v.address as venue_address, v.city as venue_city, vs.descr as venue_state, v.zip as venue_zip, s.descr as state, f.id as favorite, 0 as offer -- , if(DATE(NOW()) >= p.offer_start AND DATE(NOW()) <= p.offer_end, 1, 0) as offer
		FROM places p
		JOIN users u ON u.id = p.userID
		LEFT OUTER JOIN users v ON v.id = p.venueID
		LEFT OUTER JOIN states s ON p.stateID = s.id
		LEFT OUTER JOIN states vs ON v.stateID = vs.id
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

		<div class="row mb-3">

			<div class="col-lg-6" >
				<div class="event-image-holder">
					<a href="#" class="favorite <?=!is_null($place['favorite'])?'active':''?>" id="f<?=$place['id'] ?>" place="<?=$place['id']?>" value="<?=!is_null($place['favorite'])?1:0?>">
						<i class="fa<?=!is_null($place['favorite'])?'s':'l'?> fa-<?=LIKE_SYMBOL?> fa-2x fa-fw"></i>
					</a>
				<div id="placeImages" class="carousel slide h-100" data-bs-ride="carousel">
					<div class="carousel-inner h-100">
				<?php
				foreach ($images as $key => $image) { ?>
					<div class="carousel-item h-100 <?= ($key == 0 ) ? 'active': '' ?> " style="background: url(<?=$image?>) no-repeat center center; background-size: cover;">
				    </div>
				<?php
				}
				?>
				  </div>
				  <button class="carousel-control-prev" type="button" data-bs-target="#placeImages" data-bs-slide="prev">
				    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
				    <span class="visually-hidden">Previous</span>
				  </button>
				  <button class="carousel-control-next" type="button" data-bs-target="#placeImages" data-bs-slide="next">
				    <span class="carousel-control-next-icon" aria-hidden="true"></span>
				    <span class="visually-hidden">Next</span>
				  </button>
				</div>

				</div>
			</div>

			<div class="col-lg-6">

				<section class="py-1 mt-3 page_header">
				    <div class="container px-3">
					   <div class="d-flex justify-content-between">
						<div class="col-12">
							<h1 class="fun-font"><?=strtoupper($place['name'])?></h1>
						</div>
					   </div>
				    </div>
				</section>

				<main class="container">
					<div class="d-flex justify-content-between align-items-center pt-3">
						<p class="pb-3 mb-0  w-100 text-justify">
							<?= $place['blurb'] ?>
						</p>
					</div>

					<div class="d-flex justify-content-between">
						<div class="action-group ms-0">
							<?
							# categories
							$query = "SELECT c.id, c.descr
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
						<div class="row pt-3 event-dates">
							<div class="col-2 text-start icon-col">
								<i class="fal fa-calendar-day fa-fw fa-2x pt-1"></i>
							</div>
							<div class="col-10">
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

											if (!is_null($row['starts']) || !is_null($row['ends'])) echo(' ');
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


					if (!empty($place['ticket_needed'])) { ?>
				 		<div class="row pt-3">
							<div class="col-2 text-start icon-col">
								<i class="fal fa-ticket fa-fw fa-2x pt-1 fa-rotate-by" style="--fa-rotate-angle: 45deg;"></i>
							</div>
							<div class="col-10">
								<?php if (!is_null($place['ticket_price_low']) && $place['ticket_needed'] != "N") { ?>

											<p class="pb-0 mb-0 lh-1 w-100 fun-font info-fontsize">
												<?
												if ($place['ticket_price_low'] != $place['ticket_price_high']) {
													 ?>$<?=number_format($place['ticket_price_low'], 2)?> &ndash; $<?=number_format($place['ticket_price_high'], 2)?><?
												} else {
													if ($place['ticket_price_low'] == 0) {
														 ?>FREE<?
													} else {
														 ?>$<?=number_format($place['ticket_price_low'], 2)?><?
													}
												}
												?>
											</p>
									<?
								} ?>
								<p class="lh-sm w-100 mb-0">
									<?
									if ($place['ticket_needed'] == "A") {
										?>Advance tickets available.<?
									} elseif ($place['ticket_needed'] == "D") {
										?>Ticketing at the door.<?
									} elseif ($place['ticket_needed'] == "N") {
										?>No ticket necessary.<?
									}
									if (!empty($place['ticket_url'])) {
										?><a href="<?=$place['ticket_url']?>" class="text-decoration-none ps-2 link-arrow" target="_blank"><i class="fas fa-arrow-up-right"></i></a><?
									}
									?>
								</p>

							</div>
						</div>
						<?
					}

					if ($place['venueID'] > 0) {
						$derived['venue'] = $place['venue_name'];
						$derived['address'] = $place['venue_address'];
						$derived['city'] = $place['venue_city'];
						$derived['state'] = $place['venue_state'];
						$derived['zip'] = $place['venue_zip'];
					} else {
						$derived['venue'] = $place['venue'];
						$derived['address'] = $place['address'];
						$derived['city'] = $place['city'];
						$derived['state'] = $place['state'];
						$derived['zip'] = $place['zip'];
					}
					?>
					<div class="row pt-3">
						<div class="col-2 text-start icon-col">
							<i class="fal fa-location-smile fa-fw fa-2x pt-1"></i>
						</div>
						<div class="col-8 align-items-center location-info">
							<div class="location-name fun-font info-fontsize lh-1"><?=$derived['venue']?></div>
							<? if (!empty($derived['address'])) { ?>
								<p class="lh-sm w-100 mb-0">
									<?=$derived['address']?><br>
									<?=$derived['city']?>, <?=$derived['state']?> <?=$derived['zip']?>
								</p>
							<? } ?>
						</div>
						<div class="col-2 text-end">
							<? if ($place['venueID'] > 0) {
								if (is_null($place['venue_image'])) $place['venue_image'] = '/assets/images/placeholder.jpg';
								else $place['venue_image'] = 'https://cdn.filestackcontent.com/resize=w:50/' . $place['venue_image'];
								?>
								<a href="profile.php?id=<?=$place['venueID']?>"><img src="<?=$place['venue_image']?>" class="profile-icon"></a>
							<? } ?>
						</div>
						<? if (!empty($place['notes'])) { ?>
							<div class="col-12">
								<div class="location-tip mt-3"><?=$place['notes']?></span></div>
							</div>
						<? } ?>
						<? if (!empty($derived['address'])) { ?>
							<div class="col-12">
								<div class="location-map mt-3">
									<iframe src="https://www.google.com/maps/embed/v1/place?key=<?=GOOGLE_MAPS_API_KEY?>&q=<?=urlencode($derived['address'])?>,+<?=urlencode($derived['city'])?>,+<?=urlencode($derived['state'])?>+<?=urlencode($derived['zip'])?>,+USA" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
								</div>
							</div>
						<? } ?>
					</div>
					<div class="row d-flex justify-content-end align-items-end pt-3 details">
						<?
						$query = "SELECT d.descr, d.icon
								FROM places_details pd
								JOIN details d ON pd.detailID = d.id AND pd.placeID = " . $place['id'] . "
						";
						$result = mysqli_query($link, $query);
						if ($result && mysqli_num_rows($result) > 0) {
							while ($row = mysqli_fetch_assoc($result)) {
								?>
								<div class="col-auto" style="padding:0 5px;" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" data-bs-animation="false" title="<?=$row['descr']?>"><?=$row['icon']?></div>
								<?
							}
						}
						?>
					</div>

					<?
					# capacity
					if (!empty($place['capacity'])) { ?>
						<div class="row pt-3">
							<div class="col-2 text-start icon-col">
								<i class="fal fa-hashtag fa-fw pt-1"></i>
							</div>
							<div class="col-10 d-flex align-items-center">
								Capacity: <?=$place['capacity']?>
							</div>
						</div>
						<?
					}
					# duration
					if (!empty($place['duration'])) { ?>
						<div class="row pt-3">
							<div class="col-2 text-start icon-col">
								<i class="fal fa-clock-eleven-thirty fa-fw pt-1"></i>
							</div>
							<div class="col-10 d-flex align-items-center">
								<?=$place['duration']?>
							</div>
						</div>
						<?
					}
					# contact info
					if (!empty($place['contact_name']) || !empty($place['phone']) || !empty($place['email'])) {
						?>
						<div class="row pt-3">
							<div class="col-2 text-start icon-col">
								<i class="fal fa-address-card fa-fw pt-1"></i>
							</div>
							<div class="col-10 location-info">
								<?
								if (!empty($place['contact_name'])) { ?>
									<?=$place['contact_name']?><br>
									<?
								}
								if (!empty($place['phone'])) { ?>
									<a href="tel:+1<?=$place['phone']?>" class="text-decoration-none dark-link"><?=mask_phone($place['phone'])?></a><br>
									<?
								}
								if (!empty($place['email'])) { ?>
									<a href="mailto:<?=$place['email']?>" class="text-decoration-none dark-link"><?=$place['email']?></a><br>
									<?
								}
								?>
							</div>
						</div>
						<?
					}

					# collaborators
					$query = "SELECT c.collaboratorID, u.profile_pic as user_image, u.name, u.handle
							FROM places_collaborators c
							JOIN users u ON u.id = c.collaboratorID
							WHERE c.placeID = " . $place['id'] . "
					";
					$result = mysqli_query($link, $query);
					if ($result && mysqli_num_rows($result) > 0) {?>
						<h3 class="fun-font info-fontsize mt-5">Collaborators</h3>
						<?php
						while ($row = mysqli_fetch_assoc($result)) {
							?>
					 		<div class="row pt-3">
								<div class="col-2 text-start icon-col">
									<?
									if (is_null($row['user_image'])) $row['user_image'] = '/assets/images/placeholder.jpg';
									else $row['user_image'] = 'https://cdn.filestackcontent.com/resize=w:50/' . $row['user_image'];
									?>
									<a href="profile.php?id=<?=$row['collaboratorID']?>"><img src="<?=$row['user_image']?>" class="profile-icon" width="50" height="50"></a>
								</div>
								<div class="col-10">
									<p class="lh-1 pb-0 mb-0 w-100 fun-font handle-fontsize"><?=$row['name']?></p>
									<p class="lh-1 w-100 mb-0"><a href="profile.php?id=<?=$row['collaboratorID']?>" class="dark-link">@<?=$row['handle']?></a></p>
								</div>
							</div>
							<?
						}
					}
					?>

					<div class="row d-flex justify-content-end align-items-end mt-3 details">
						<?
						# url
						if (!empty($place['url'])) { ?>
							<div class="col-auto"><a href="<?=$place['url']?>" class="dark-link"><i class="fal fa-spider-web pt-1"></i></a></div>
							<?
						}

						# socials
						$query = "SELECT s.icon, s.preface, s.link, ps.value
								FROM places_socials ps
								JOIN socials s ON ps.socialID = s.id
								WHERE ps.placeID = " . $place['id'] . "
						";
						$result = mysqli_query($link, $query);
						if ($result && mysqli_num_rows($result) > 0) {
							while ($row = mysqli_fetch_assoc($result)) {
								?>
								<div class="col-auto"><a href="<?=$row['link']?><?=$row['value']?>" class="dark-link"><i class="<?=$row['icon']?> pt-1"></i></a></div>
								<?
							}
						}
						?>
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
	.details { font-size: 1.5em; }
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
