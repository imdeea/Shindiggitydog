<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once("config.php");
include('security.php');
include('rxs.php');

$errorMsg = $successMsg = '';
$showForm = true;

$link = mysqli_connect($db_host, $db_user, $db_pasw, $db_db);

if (isset($_POST['submitted'])) {

	if (empty($_POST['handle']) || empty($_POST['password'])) {
		$errorMsg = 'Some required fields are missing.';
	} else {

		# check for exising handle
		$query = "SELECT u.id
				FROM users u
				WHERE u.handle = '" . addslashes(trim($_POST["handle"])) . "'
				  AND u.id != " . $_COOKIE['id'] . "
		";
		$result = mysqli_query($link, $query);

		if (!$result) $errorMsg = 'Please try again';
		elseif (mysqli_num_rows($result) > 0) $errorMsg = 'That handle is already registered.';
		else {

			# update person
			if ($_POST['submitted'] == 'person') {
				$query = "UPDATE users SET
						name = '" . addslashes(trim($_POST['name'])) . "',
						email = '" . addslashes(trim($_POST['email'])) . "',
						handle = '" . addslashes(trim($_POST['handle'])) . "',
						password = '" . addslashes(trim($_POST['password'])) . "',
						phone = '" . strip_phone(trim($_POST['phone'])) . "',
						pronoun = '" . $_POST['pronoun'] . "',
						age = " . addslashes(trim($_POST['age'])) . ",
						age_on = NOW(),
						Latitude = NULL,
						Longitude = NULL
						WHERE id = " . $_COOKIE['id'] . "
				";
				mysqli_query($link, $query);

				# store data
				setcookie('handle', $_POST['handle'], time() + (86400 * 30)); # 86400 = 1 day
			}

			# update venue
			if ($_POST['submitted'] == 'venue') {
				$query = "UPDATE users SET
						name = '" . addslashes(trim($_POST['name'])) . "',
						email = '" . addslashes(trim($_POST['email'])) . "',
						handle = '" . addslashes(trim($_POST['handle'])) . "',
						password = '" . addslashes(trim($_POST['password'])) . "',
						address = '" . addslashes(trim($_POST['address'])) . "',
						city = '" . addslashes(trim($_POST['city'])) . "',
						stateID = " . $_POST['stateID'] . ",
						zip = '" . addslashes(trim($_POST['zip'])) . "',
						neighborhood = '" . addslashes(trim($_POST['neighborhood'])) . "',
						phone = '" . strip_phone(trim($_POST['phone'])) . "',
						Latitude = NULL,
						Longitude = NULL
						WHERE id = " . $_COOKIE['id'] . "
				";
				mysqli_query($link, $query);

				# store data
				setcookie('handle', $_POST['handle'], time() + (86400 * 30)); # 86400 = 1 day

				# get lat & long
				$query = "SELECT Latitude, Longitude FROM ZIPCodes WHERE ZipCode = '" . substr(trim($_POST['zip']), 0, 5) . "' ";
				$result = mysqli_query($link, $query);
				if ($result && mysqli_num_rows($result) > 0) {
					$zip = mysqli_fetch_assoc($result);

					# get city
					$cityID = 0;
					$query = "SELECT id, (((acos(sin((".$zip['Latitude']."*pi()/180)) * sin((latitude*pi()/180)) + cos((".$zip['Latitude']."*pi()/180)) * cos((latitude*pi()/180)) * cos(((".$zip['Longitude']." - longitude)*pi()/180)))) * 180/pi()) * 60 * 1.1515) as distance
							FROM cities
							WHERE shindig = 1
							ORDER BY distance ASC
							LIMIT 1
					";
					$result = mysqli_query($link, $query);
					if ($result && mysqli_num_rows($result) > 0) {
						$row = mysqli_fetch_assoc($result);
						$cityID = $row['id'];
					}

					# update me
					$query = "UPDATE users SET
							Latitude = " . $zip['Latitude'] . ",
							Longitude = " . $zip['Longitude'] . ",
							cityID = " . $cityID . "
							WHERE id = " . $_COOKIE['id'] . "
					";
					mysqli_query($link, $query);

					# update my events
					$query = "UPDATE places SET
							neighborhood = '" . addslashes(trim(empty($_POST['neighborhood'])?$_POST['city']:$_POST['neighborhood'])) . "',
							Latitude = " . $zip['Latitude'] . ",
							Longitude = " . $zip['Longitude'] . ",
							cityID = " . $cityID . "
							WHERE venueID = " . $_COOKIE['id'] . "
					";
					mysqli_query($link, $query);

				}
			}



			$successMsg = 'Your profile has been updated successfully.';
			$showForm = false;
		}
	}
} else {
	$query = "SELECT * FROM users WHERE id = " . $_COOKIE['id'] . " ";
	$result = mysqli_query($link, $query);
	$item = mysqli_fetch_assoc($result);
}

$top_nav = 'user';
include('header.php'); ?>

	<section class="py-1 mb-2 text-center page_header">
	    <div class="container">
		   <div class="row">
			<div class="col-lg-6 col-md-8 mx-auto">
			  <h1 class="fw-medium">Coordinates</h1>
			</div>
		   </div>
	    </div>
	</section>


	<div class="container">
		<main>
			<div class="row my-2 g-3">
				<div class="col-md-2 col-12">

					<? 
					$nav = 'edit';
					include('user_nav.php');
					?>

				</div>

				<div class="col-md-9 offset-md-1 col-12">

					<? 
					if (!empty($successMsg)) { ?>
						<div class="alert alert-success" role="alert">
							<?=$successMsg?>
						</div>
					<? } ?>

					<? if (!empty($errorMsg)) { ?>
						<div class="alert alert-danger" role="alert">
							<?=$errorMsg?>
						</div>
					<? } ?>

					<? if ($showForm) { ?>
						<? if ($item['PoV'] == 'P') { ?>
							<form class="needs-validation" method="POST" action="">
								<div class="row">

									<div class="col-md-6 mb-3">
										<label for="handle" class="form-label">Handle</label>
										<div class="input-group has-validation">
											<span class="input-group-text "><i class="fal fa-at"></i></span>
											<input type="text" class="form-control" id="handle" name="handle" placeholder="Handle" onfocusout="CheckHandle()" value="<?=$item['handle']?>" maxlength="50" required autocomplete="off">
											<div class="invalid-feedback">unavailable</div>
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="name" class="form-label">Name</label>
										<div class="input-group has-validation">
											<input type="text" class="form-control" id="name" name="name" placeholder="Name" value="<?=$item['name']?>" required autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="email" class="form-label">Email</label>
										<div class="input-group has-validation">
											<span class="input-group-text "><i class="fas fa-envelope"></i></span>
											<input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" value="<?=$item['email']?>" required autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="phone" class="form-label">Phone</label>
										<div class="input-group has-validation">
											<span class="input-group-text "><i class="fas fa-phone"></i></span>
											<input type="tel" class="form-control" name="phone" id="phone" placeholder="(212) 555-1212" value="<?=mask_phone($item['phone'])?>" required autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="password" class="form-label">Password</label>
										<div class="input-group has-validation">
											<input type="password" class="form-control" name="password" placeholder="" value="<?=$item['password']?>" required autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="email" class="form-label">Pronouns</label>
										<div class="input-group has-validation">
											<div class="btn-group bg-body w-100" role="group" aria-label="Basic radio toggle button group">
												<input type="radio" class="btn-check" name="pronoun" id="btnradio1"<? if ($item['pronoun'] == 'H') echo(' checked'); ?> value="H">
												<label class="btn btn-outline-secondary" for="btnradio1">he him his</label>

												<input type="radio" class="btn-check" name="pronoun" id="btnradio2"<? if ($item['pronoun'] == 'S') echo(' checked'); ?> value="S">
												<label class="btn btn-outline-secondary" for="btnradio2">she her hers</label>

												<input type="radio" class="btn-check" name="pronoun" id="btnradio3"<? if ($item['pronoun'] == 'T') echo(' checked'); ?> value="T">
												<label class="btn btn-outline-secondary" for="btnradio3">they them theirs</label>

												<input type="radio" class="btn-check" name="pronoun" id="btnradio4"<? if ($item['pronoun'] == 'O') echo(' checked'); ?> value="O">
												<label class="btn btn-outline-secondary" for="btnradio4">other</label>
											</div>
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="age" class="form-label">Age</label>
										<div class="row">
											<div class="col ">
												<input type="range" class="form-range" min="12" max="110" id="age" name="age" value="<?=$item['age']?>" oninput="this.parentElement.nextElementSibling.firstElementChild.innerHTML = this.value">
											</div>
											<div class="col-auto ps-0">
												<span class="range-slider__value"><?=$item['age']?></div>
											</div>
										</div>
									</div>

								</div>

								<button class="w-100 btn btn-dark btn-lg mt-20" type="submit">Update</button>
								<input type="hidden" name="submitted" value="person">
							</form>
						<? } ?>

						<? if ($item['PoV'] == 'V') { ?>
							<form class="needs-validation" method="POST" action="">
								<div class="row">

									<div class="col-md-6 mb-3">
										<label for="handle" class="form-label">Handle</label>
										<div class="input-group has-validation">
											<span class="input-group-text "><i class="fal fa-at"></i></span>
											<input type="text" class="form-control" id="handle" name="handle" placeholder="Handle" onfocusout="CheckHandle()" value="<?=$item['handle']?>" maxlength="50" required autocomplete="off">
											<div class="invalid-feedback">unavailable</div>
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="venueName" class="form-label">Venue Name</label>
										<div class="input-group has-validation">
											<input type="text" class="form-control" id="venueName" name="name" placeholder="Venue Name" value="<?=$item['name']?>" required autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="address" class="form-label">Address</label>
										<div class="input-group has-validation">
											<span class="input-group-text "><i class="fas fa-map-pin"></i></span>
											<input type="text" class="form-control" id="address" name="address" placeholder="Address" value="<?=$item['address']?>" required autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="city" class="form-label">City</label>
										<div class="input-group has-validation">
											<input type="text" class="form-control" id="city" name="city" placeholder="City" value="<?=$item['city']?>" autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="stateID" class="form-label">State</label>
										<div class="input-group has-validation">
											<select class="form-control" id="stateID" name="stateID" size="1" required>
												<option value="">State</option>
												<?
												$query = "SELECT id, descr FROM states ORDER BY descr ";
												$result = mysqli_query($link, $query);
												if ($result && mysqli_num_rows($result) > 0) {
													while ($row = mysqli_fetch_assoc($result)) {
														?><option value="<?=$row['id']?>"<? if ($item['stateID'] == $row['id']) echo(' selected'); ?>><?=$row['descr']?></option><?
													}
												}
												?>
											</select>
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="firstName" class="form-label">Zip</label>
										<div class="input-group has-validation">
											<input type="text" class="form-control" id="zip" name="zip" placeholder="" value="<?=$item['zip']?>" autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="neighborhood" class="form-label">Neighborhood</label>
										<div class="input-group has-validation">
											<input type="text" class="form-control" id="neighborhood" name="neighborhood" placeholder="SoHo" value="<?=$item['neighborhood']?>" autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="email" class="form-label">Email</label>
										<div class="input-group has-validation">
											<span class="input-group-text "><i class="fas fa-envelope"></i></span>
											<input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" value="<?=$item['email']?>" required autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="phone" class="form-label">Phone</label>
										<div class="input-group has-validation">
											<span class="input-group-text "><i class="fas fa-phone"></i></span>
											<input type="tel" class="form-control" name="phone" id="phone" placeholder="(212) 555-1212" value="<?=$item['phone']?>" required autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="password" class="form-label">Password</label>
										<div class="input-group has-validation">
											<input type="password" class="form-control" name="password" placeholder="" value="<?=$item['password']?>" required autocomplete="off">
										</div>
									</div>
								</div>

								<button class="w-100 btn btn-dark btn-lg mt-20" type="submit">Update</button>
								<input type="hidden" name="submitted" value="venue">
							</form>
						<? } ?>
					<? } ?>

				</div>
			</div>
		</main>
	</div>

	<script src="/assets/third-party/input-masking/inputmask.min.js"></script>
	<script>
	(function () {
		'use strict'

		//Mask input
		var phone = document.getElementById("phone");
		Inputmask({"mask": "(999) 999-9999"}).mask(phone);
		var zip = document.getElementById("zip");
		Inputmask({"mask": "99999"}).mask(zip);


		// Initialize range slider value
		var sliders = document.querySelectorAll('.form-range')
		Array.prototype.slice.call(sliders)
		.forEach(function (slider) {
			slider.parentElement.nextElementSibling.firstElementChild.innerHTML = slider.value;
		});


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
	function CheckHandle(){
		// Creating the XMLHttpRequest object
		var request = new XMLHttpRequest();
		var handle = document.getElementById("handle").value;
		// Instantiating the request object
		request.open("GET", "handle_handler.php?handle="+handle, true);

		// Defining event listener for readystatechange event
		request.onreadystatechange = function() {

			// Check if the request is compete and was successful
			if(this.readyState === 4 && this.status === 200) {
				// Inserting the response from server into the HTML element
				var el = document.getElementById("handle");
				if(this.responseText == "false"){
					el.classList.remove('is-valid');
					el.classList.add('is-invalid');
					el.setCustomValidity("invalid");
					el.focus();
				} else {
					el.classList.add('is-valid');
					el.classList.remove('is-invalid');
					el.setCustomValidity("");
				}
			}
		};

		// Sending the request to the server
		request.send();
	}
	</script>

	<style>
	.btn-group label { font-size:.9rem; }
	</style>

<? include('footer.php'); ?>
