<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location:event_manage.php"); exit; }

require_once("config.php");
include('security.php');
include('rxs.php');

$errorMsg = "";
$successMsg = FALSE;

$link = mysqli_connect($db_host, $db_user, $db_pasw, $db_db);

if (@$_POST["submitted"] == "yes") {

	$item = $_POST;

	# update event
	$query = "UPDATE places SET
			ticket_needed = '" . addslashes(trim(@$item['ticket_needed'])) . "',
			ticket_price_low = " . addslashes(trim(str_replace(',', '', $item['ticket_price_low']))) . ",
			ticket_price_high = " . addslashes(trim(str_replace(',', '', $item['ticket_price_high']))) . ",
			ticket_url = '" . addslashes(trim($item['ticket_url'])) . "',
			capacity = 0" . addslashes(trim($item['capacity'])) . ",
			duration = '" . addslashes(trim($item['duration'])) . "',
			blurb = '" . addslashes(trim($item['blurb'])) . "',
			notes = '" . addslashes(trim($item['notes'])) . "'
			WHERE id = " . $item['id'] . "
			  AND userID = " . $_COOKIE['id'] . "
	";
	mysqli_query($link, $query);

	# update category joins
	# delete old
	$query = "DELETE FROM places_categories WHERE placeID = " . $item['id'] . " ";
	mysqli_query($link, $query);
	# add new
	if (isset($item['catIDs']) && count($item['catIDs']) > 0) {
		foreach ($item['catIDs'] as $catID) {
			$query = "INSERT INTO places_categories SET
					placeID = " . $item['id'] . ",
					catID = " . filter_var($catID, FILTER_SANITIZE_NUMBER_INT) . "
			";
			mysqli_query($link, $query);
		}
	}

	# update detail joins
	# delete old
	$query = "DELETE FROM places_details WHERE placeID = " . $item['id'] . " ";
	mysqli_query($link, $query);
	# add new
	if (isset($item['detailIDs']) && count($item['detailIDs']) > 0) {
		foreach ($item['detailIDs'] as $detailID) {
			$query = "INSERT INTO places_details SET
					placeID = " . $item['id'] . ",
					detailID = " . filter_var($detailID, FILTER_SANITIZE_NUMBER_INT) . "
			";
			mysqli_query($link, $query);
		}
	}

	$errorMsg = "You've updated your event.";
	$successMsg = TRUE;

	mysqli_close($link);
} else {
	# fetch event details
	$query = "SELECT *
			FROM places
			WHERE id = " . $_GET['id'] . "
			AND userID = " . $_COOKIE['id'] . "
	";
	$result = mysqli_query($link, $query);
	if (!$result || mysqli_num_rows($result) == 0) { header("Location:event_manage.php"); exit; }
	$item = mysqli_fetch_assoc($result);
}

include('header.php'); ?>

	<section class="py-1 mb-2 text-center page_header">
	    <div class="container">
		   <div class="row">
			<div class="col-lg-6 col-md-8 mx-auto">
			  <h1 class="fw-medium"><?=$item['name']?></h1>
			</div>
		   </div>
	    </div>
	</section>


	<div class="container">
		<main>
			<div class="row my-2 g-3">
				<div class="col-md-2 col-12">

					<?
					$nav = 'details';
					include('event_nav.php');
					?>

				</div>

				<div class="col-md-9 offset-md-1 col-12">

					<?
					if ($errorMsg != "") { ?>
						<div class="alert alert-success">
							<?=$errorMsg?>
						</div>
					<? }

					if ($successMsg == "") { ?>
						<form class="needs-validation" novalidate method="POST" action="?id=<?=$item['id']?>">
							<div class="row">

								<div class="col-md-12 mb-3">
									<label for="duration" class="form-label">Description</label>
									<div class="input-group has-validation">
										<textarea class="form-control" id="blurb" name="blurb" rows="10" placeholder="Event description"><?=$item['blurb']?></textarea>
									</div>
								</div>
								<div class="col-md-12 mb-3">
									<label for="duration" class="form-label">Special Instructions / Notes</label>
									<div class="input-group has-validation">
										<textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Event description"><?=$item['notes']?></textarea>
									</div>
								</div>
								<div class="col-md-6 mb-3">
									<label for="catID" class="form-label">Ticket Needed?</label>
									<div class="input-group has-validation">
										<div class="btn-group" role="group" aria-label="Genre">

												<input type="radio" name="ticket_needed" value="A"<? if ($item['ticket_needed'] == 'A') echo(' checked'); ?> class="btn-check" id="ticket_needed_A" autocomplete="off">
												<label class="btn btn-outline-primary" for="ticket_needed_A">In Advance</label>

												<input type="radio" name="ticket_needed" value="D"<? if ($item['ticket_needed'] == 'D') echo(' checked'); ?> class="btn-check" id="ticket_needed_D" autocomplete="off">
												<label class="btn btn-outline-primary" for="ticket_needed_D">At Door Only</label>

												<input type="radio" name="ticket_needed" value="N"<? if ($item['ticket_needed'] == 'N') echo(' checked'); ?> class="btn-check" id="ticket_needed_N" autocomplete="off">
												<label class="btn btn-outline-primary" for="ticket_needed_N">No</label>

										</div>
									</div>
								</div>

								<div class="col-md-6 mb-3">
									<label for="ticket_price" class="form-label">Ticket Price</label>
									<div class="input-group has-validation">
										<span class="input-group-text "><i class="fal fa-dollar-sign"></i></span>
										<input type="text" class="form-control" id="ticket_price_low" name="ticket_price_low" required placeholder="10.00" inputmode="numeric" value="<?=$item['ticket_price_low']?>" autocomplete="off">
										&nbsp; - &nbsp;
										<span class="input-group-text "><i class="fal fa-dollar-sign"></i></span>
										<input type="text" class="form-control" id="ticket_price_high" name="ticket_price_high" required placeholder="25.00" inputmode="numeric" value="<?=$item['ticket_price_high']?>" autocomplete="off">
									</div>
								</div>

								<div class="col-md-6 mb-3">
									<label for="ticket_url" class="form-label">3rd Party Ticketing / Reservation Link</label>
									<div class="input-group has-validation">
										<span class="input-group-text "><i class="fal fa-ticket"></i></span>
										<input type="text" class="form-control" id="ticket_url" name="ticket_url" placeholder="https://www.eventbrite.com/myevent" value="<?=$item['ticket_url']?>" autocomplete="off">
									</div>
								</div>

								<div class="col-md-6 mb-3">
									<label for="capacity" class="form-label">Capacity</label>
									<div class="input-group has-validation">
										<span class="input-group-text "><i class="fal fa-hashtag"></i></span>
										<input type="number" class="form-control" id="capacity" name="capacity" placeholder="250" value="<?=$item['capacity']?>" autocomplete="off" min="0" max="10000" step="1">
									</div>
								</div>

								<div class="col-md-6 mb-3">
									<label for="duration" class="form-label">Duration</label>
									<div class="input-group has-validation">
										<span class="input-group-text "><i class="fal fa-clock"></i></span>
										<input type="text" class="form-control" id="duration" name="duration" placeholder="2 hours 30 minutes" value="<?=$item['duration']?>" autocomplete="off">
									</div>
								</div>

							</div>

							<div class="row">
								<div class="col-md-6 mb-3">
									<label for="catID" class="form-label">Genre</label>
									<div class="input-group has-validation">
										<div class="btn-group" role="group" aria-label="Genre">
											<?
											$query = "SELECT c.id, c.descr, c.icon, j.id as preselected
													FROM categories c
													LEFT OUTER JOIN places_categories j ON c.id = j.catID AND j.placeID = " . $item['id'] . "
													ORDER BY c.descr
											";
											$i = 0;
											$result = mysqli_query($link, $query);
											if ($result && mysqli_num_rows($result) > 0) {

												while ($row = mysqli_fetch_assoc($result)) {
													$i++;
													?>
													<input type="checkbox" name="catIDs[]" onchange="checkCheckboxMax(this)" value="<?=$row['id']?>"<? if (!is_null($row['preselected'])) echo(' checked'); ?> class="btn-check" id="btncheck<?=$i?>" autocomplete="off">
													<label class="btn btn-outline-primary" for="btncheck<?=$i?>"><?=$row['icon']?> &nbsp; <?=$row['descr']?></label>
													<?
												}
											}
											?>
										</div>
										<div class="invalid-feedback"> Select a maximum of 3 genres </div>
									</div>
								</div>

								<?
								$query = "SELECT d.id, g.descr as `group`, g.CoR, d.descr as detail, j.id as preselected
										FROM detail_groups g
										JOIN details d ON g.id = d.groupID
										LEFT OUTER JOIN places_details j ON d.id = j.detailID AND j.placeID = " . $item['id'] . "
										WHERE g.active = 1
										  AND d.active = 1
										ORDER BY g.seqno, d.seqno
								";
								$i = 0;
								$result = mysqli_query($link, $query);
								if ($result && mysqli_num_rows($result) > 0) {

									$group = 'xxx';
									$first_group = true;
									while ($row = mysqli_fetch_assoc($result)) {
										$i++;

										if (!$first_group && $row['group'] != $group) {
											?>
													</div>
												</div>
											</div>
											<?
										} else {
											$first_group = false;
										}

										if ($row['group'] != $group) {
											?>
											<div class="col-md-6 mb-3">
												<label for="catID" class="form-label"><?=$row['group']?></label>
												<div class="input-group has-validation">
													<div class="btn-group" role="group" aria-label="<?=$row['group']?>">
											<?
											$group = $row['group'];
											$CoR = ($row['CoR'] == 'C')?'checkbox':'radio';
										}

										?>
										<input type="<?=$CoR?>" name="detailIDs[]" value="<?=$row['id']?>"<? if (!is_null($row['preselected'])) echo(' checked'); ?> class="btn-check" id="detail<?=$i?>" autocomplete="off">
										<label class="btn btn-outline-primary" for="detail<?=$i?>"><?=$row['detail']?></label>
										<?
									}
								}
								?>
										</div>
									</div>
								</div>

							</div>
							<div class="d-flex">
								<button class="flex-grow-1 btn btn-dark btn-lg mt-20 m-2" type="submit">Save</button>
								<a href="event_manage.php" class="btn btn-danger btn-lg mt-20 m-2 w-25">Cancel</a>
							</div>

							<input type="hidden" name="id" value="<?=$item['id']?>">
							<input type="hidden" name="name" value="<?=$item['name']?>">
							<input type="hidden" name="submitted" value="yes">
						</form>
					<?
					}
					?>
				</div>
			</div>
		</main>
	</div>

	<script src="/assets/third-party/input-masking/inputmask.min.js"></script>
	<script>
	(function () {
		'use strict'

		//Mask input
		/*var phone = document.getElementById("phone");
		Inputmask({"mask": "(999) 999-9999"}).mask(phone);
		var zip = document.getElementById("zip");
		Inputmask({"mask": "99999"}).mask(zip);*/

		Inputmask.extendAliases({
		  'customCurrency': {
		    alias: "currency",
			rightAlign: false
		  }
		});

		var ticket_price_low = document.getElementById("ticket_price_low");
		Inputmask("customCurrency").mask(ticket_price_low);
		var ticket_price_high = document.getElementById("ticket_price_high");
		Inputmask("customCurrency").mask(ticket_price_high);

		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.needs-validation')

		// Loop over them and prevent submission
		Array.prototype.slice.call(forms)
		.forEach(function (form) {
			form.addEventListener('submit', function (event) {
				if (!form.checkValidity()) {
					event.preventDefault()
					event.stopPropagation()
				}

				form.classList.add('was-validated')
		 	}, false)
		})
	})()

	function checkCheckboxMax(current) {

		const list = event.target.closest('.btn-group');
		var label = event.target.closest('label');
		var numberChecked = list.querySelectorAll('input[type="checkbox"]:checked').length;

		if (numberChecked > 3 && current.checked == true) {
			current.checked = false;
			current.blur();
			list.classList.remove('group-valid');
			list.classList.add('group-invalid');
		} else {
			current.blur();
			list.classList.remove('group-invalid');
			list.classList.add('group-valid');
		}
	}
	</script>

<? include('footer.php'); ?>
