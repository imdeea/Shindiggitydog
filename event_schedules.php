<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location:event_manage.php"); exit; }
if (isset($_GET['action']) && in_array($_GET['action'], array('list', 'add', 'edit', 'delete'))) $action = $_GET['action'];
else $action = 'list';

require_once("config.php");
include('security.php');
include('rxs.php');

$errorMsg = "";
$successMsg = "";

$link = mysqli_connect($db_host, $db_user, $db_pasw, $db_db);

if (@$_POST["adding"] == "yes") {

	$item['id'] = $_GET['id'];
	$item['name'] = $_POST['name'];

	# does not repeat
	if (isset($_POST['begin_dnr']) && is_date($_POST['begin_dnr'])) {
		$query = "INSERT INTO places_schedules SET
				placeID = " . $item['id'] . ",
				begin = '" . date('Y-m-d', strtotime($_POST['begin_dnr'])) . "',
				starts = " . (!empty($_POST['starts'])?"'" . $_POST['starts'] . "'":'NULL') . ",
				ends = " . (!empty($_POST['ends'])?"'" . $_POST['ends'] . "'":'NULL') . "
		";
		mysqli_query($link, $query);
	}

	if (isset($_POST['begin_w']) && is_date($_POST['begin_w']) && is_date($_POST['end_w'])) {
		$query = "INSERT INTO places_schedules SET
				placeID = " . $item['id'] . ",
				begin = '" . date('Y-m-d', strtotime($_POST['begin_w'])) . "',
				end = '" . date('Y-m-d', strtotime($_POST['end_w'])) . "',
				dow = '" . implode(',', $_POST['dow']) . "',
				starts = " . (!empty($_POST['starts'])?"'" . $_POST['starts'] . "'":'NULL') . ",
				ends = " . (!empty($_POST['ends'])?"'" . $_POST['ends'] . "'":'NULL') . "
		";
		mysqli_query($link, $query);
	}

	if (isset($_POST['begin_m']) && is_date($_POST['begin_m']) && is_date($_POST['end_m'])) {
		$query = "INSERT INTO places_schedules SET
				placeID = " . $item['id'] . ",
				begin = '" . date('Y-m-d', strtotime($_POST['begin_m'])) . "',
				end = '" . date('Y-m-d', strtotime($_POST['end_m'])) . "',
				dom = '" . implode(',', $_POST['dom']) . "',
				starts = " . (!empty($_POST['starts'])?"'" . $_POST['starts'] . "'":'NULL') . ",
				ends = " . (!empty($_POST['ends'])?"'" . $_POST['ends'] . "'":'NULL') . "
		";
		mysqli_query($link, $query);
	}

	$successMsg = "You're new schedule has been added.";

} elseif (@$_POST["editing"] == "yes") {

	$item['id'] = $_GET['id'];
	$item['name'] = $_POST['name'];

	# does not repeat
	if (isset($_POST['begin_dnr']) && is_date($_POST['begin_dnr'])) {
		$query = "UPDATE places_schedules SET
				begin = '" . date('Y-m-d', strtotime($_POST['begin_dnr'])) . "',
				starts = " . (!empty($_POST['starts'])?"'" . $_POST['starts'] . "'":'NULL') . ",
				ends = " . (!empty($_POST['ends'])?"'" . $_POST['ends'] . "'":'NULL') . ",
				updated = NOW()
				WHERE placeID = " . $item['id'] . "
				  AND id = " . $_POST['scheduleID'] . "
		";
		mysqli_query($link, $query);
	}

	if (isset($_POST['begin_w']) && is_date($_POST['begin_w']) && is_date($_POST['end_w'])) {
		$query = "UPDATE places_schedules SET
				begin = '" . date('Y-m-d', strtotime($_POST['begin_w'])) . "',
				end = '" . date('Y-m-d', strtotime($_POST['end_w'])) . "',
				dow = '" . implode(',', $_POST['dow']) . "',
				starts = " . (!empty($_POST['starts'])?"'" . $_POST['starts'] . "'":'NULL') . ",
				ends = " . (!empty($_POST['ends'])?"'" . $_POST['ends'] . "'":'NULL') . ",
				updated = NOW()
				WHERE placeID = " . $item['id'] . "
				  AND id = " . $_POST['scheduleID'] . "
		";
		mysqli_query($link, $query);
	}

	if (isset($_POST['begin_m']) && is_date($_POST['begin_m']) && is_date($_POST['end_m'])) {
		$query = "UPDATE places_schedules SET
				begin = '" . date('Y-m-d', strtotime($_POST['begin_m'])) . "',
				end = '" . date('Y-m-d', strtotime($_POST['end_m'])) . "',
				dom = '" . implode(',', $_POST['dom']) . "',
				starts = " . (!empty($_POST['starts'])?"'" . $_POST['starts'] . "'":'NULL') . ",
				ends = " . (!empty($_POST['ends'])?"'" . $_POST['ends'] . "'":'NULL') . ",
				updated = NOW()
				WHERE placeID = " . $item['id'] . "
				  AND id = " . $_POST['scheduleID'] . "
		";
		mysqli_query($link, $query);
	}

	$successMsg = "You're new schedule has been updated.";

} elseif ($action == 'delete' && isset($_GET['scheduleID']) && is_numeric($_GET['scheduleID'])) {

	$item['id'] = $_GET['id'];
	$item['name'] = $_GET['name'];
	$action = 'list';

	$query = "DELETE FROM places_schedules WHERE placeID = " . $item['id'] . " AND id = " . $_GET['scheduleID'] . " ";
	mysqli_query($link, $query);

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
					$nav = 'schedules';
					include('event_nav.php');
					?>

				</div>

				<div class="col-md-9 offset-md-1 col-12">

					<?
					if ($successMsg != "") { ?>
						<div class="alert alert-success">
							<?=$successMsg?>
						</div>
					<? }

					if ($errorMsg != "") { ?>
						<div class="alert alert-danger">
							<?=$errorMsg?>
						</div>
					<? }

					switch ($action) {
						case 'add':
							?>
							<h5>Add Schedule</h5>
							<form class="needs-validation" novalidate method="POST" action="?id=<?=$item['id']?>">

								<ul class="nav nav-tabs" id="addTabs" role="tablist">
									<li class="nav-item" role="presentation">
										<button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Does Not Repeat</button>
									</li>
									<li class="nav-item" role="presentation">
										<button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Recurs Weekly</button>
									</li>
									<li class="nav-item" role="presentation">
										<button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Recurs Monthly</button>
									</li>
								</ul>

								<div class="tab-content" id="addTabsContent">
									<? # does not repeat ?>
									<div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
										<div class="col-md-6 mb-3">
											<div class="input-group has-validation">
												<input type="date" class="form-control" id="begin_dnr" name="begin_dnr" value="" autocomplete="off">
											</div>
										</div>
									</div>

									<? # repeats weekly ?>
									<div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
										<div class="row">
											<div class="col-md-6 mb-3">
												<label for="begin_w" class="form-label">Begins</label>
												<div class="input-group has-validation">
													<input type="date" class="form-control" id="begin_w" name="begin_w" value="" autocomplete="off">
												</div>
											</div>

											<div class="col-md-6 mb-3">
												<label for="end_w" class="form-label">Ends</label>
												<div class="input-group has-validation">
													<input type="date" class="form-control" id="end_w" name="end_w" value="" autocomplete="off">
												</div>
											</div>

											<div class="col-md-6 mb-3">
												<label for="dow" class="form-label">On Which Days?</label>
												<div class="input-group has-validation">
													<div class="btn-group" role="group" aria-label="Genre">
														<?
														foreach ($dows as $key => $value) {
															?>
															<input type="checkbox" name="dow[]" value="<?=$key?>" class="btn-check" id="dow_set<?=$key?>" autocomplete="off">
															<label class="btn btn-outline-primary" for="dow_set<?=$key?>"><?=$value?></label>
															<?
														}
														?>
													</div>
												</div>
											</div>
										</div>
									</div>

									<? # repeats monthly ?>
									<div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
										<div class="row">
											<div class="col-md-6 mb-3">
												<label for="begin_m" class="form-label">Begins</label>
												<div class="input-group has-validation">
													<input type="date" class="form-control" id="begin_m" name="begin_m" value="" autocomplete="off">
												</div>
											</div>

											<div class="col-md-6 mb-3">
												<label for="end_m" class="form-label">Ends</label>
												<div class="input-group has-validation">
													<input type="date" class="form-control" id="end_m" name="end_m" value="" autocomplete="off">
												</div>
											</div>

											<div class="col-md-6 mb-3">
												<label for="dom" class="form-label">On Which Days?</label>
												<div class="input-group has-validation">
													<div class="btn-group monthly" role="group" aria-label="Genre">
														<?
														for ($i = 1; $i <= 31; $i++) {
															?>
															<input type="checkbox" name="dom[]" value="<?=$i?>" class="btn-check" id="dom_set<?=$i?>" autocomplete="off">
															<label class="btn btn-outline-primary" for="dom_set<?=$i?>"><?=$i?></label>
															<?
														}
														?>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-6 mb-3">
										<label for="starts" class="form-label">Starts</label>
										<div class="input-group has-validation">
											<input type="time" class="form-control" id="starts" name="starts" value="" autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="ends" class="form-label">Ends</label>
										<div class="input-group has-validation">
											<input type="time" class="form-control" id="ends" name="ends" value="" autocomplete="off">
										</div>
									</div>
								</div>

								<div class="d-flex">
									<button class="flex-grow-1 btn btn-dark btn-lg mt-20 m-2" type="submit">Save</button>
									<a href="event_manage.php" class="btn btn-danger btn-lg mt-20 m-2 w-25">Cancel</a>
								</div>

								<input type="hidden" name="id" value="<?=$item['id']?>">
								<input type="hidden" name="name" value="<?=$item['name']?>">
								<input type="hidden" name="adding" value="yes">
							</form>
							<?
							break;

						case 'edit':
							$query = "SELECT * FROM places_schedules WHERE placeID = " . $item['id'] . " AND id = " . $_GET['scheduleID'] . " ";
							$result = mysqli_query($link, $query);
							if (!$result || mysqli_num_rows($result) == 0) { /* do something */ }
							$schedule = mysqli_fetch_assoc($result);
							?>
							<h5>Edit Schedule</h5>
							<form class="needs-validation" novalidate method="POST" action="?id=<?=$item['id']?>">

								<ul class="nav nav-tabs" id="editTabs" role="tablist">
									<li class="nav-item" role="presentation">
										<button class="nav-link<? if (is_null($schedule['dow']) && is_null($schedule['dom'])) echo(' active'); else echo('  disabled'); ?>" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Does Not Repeat</button>
									</li>
									<li class="nav-item" role="presentation">
										<button class="nav-link<? if (!is_null($schedule['dow'])) echo(' active'); else echo('  disabled'); ?>" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Recurs Weekly</button>
									</li>
									<li class="nav-item" role="presentation">
										<button class="nav-link<? if (!is_null($schedule['dom'])) echo(' active'); else echo('  disabled'); ?>" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Recurs Monthly</button>
									</li>
								</ul>

								<div class="tab-content" id="editTabContent">
									<?
									# does not repeat
									if (is_null($schedule['dow']) && is_null($schedule['dom'])) {
										?>
										<div class="tab-pane fade <? if (is_null($schedule['dow']) && is_null($schedule['dom'])) echo(' show active'); ?>" id="home" role="tabpanel" aria-labelledby="home-tab">
											<div class="col-md-6 mb-3">
												<div class="input-group has-validation">
													<input type="date" class="form-control" id="begin_dnr" name="begin_dnr" value="<?=$schedule['begin']?>" autocomplete="off">
												</div>
											</div>
										</div>
										<?
									}

									# repeats weekly
									if (!is_null($schedule['dow'])) {
										?>
										<div class="tab-pane fade<? if (!is_null($schedule['dow'])) echo(' show active'); ?>" id="profile" role="tabpanel" aria-labelledby="profile-tab">
											<div class="row">
												<div class="col-md-6 mb-3">
													<label for="begin_w" class="form-label">Begins</label>
													<div class="input-group has-validation">
														<input type="date" class="form-control" id="begin_w" name="begin_w" value="<?=$schedule['begin']?>" autocomplete="off">
													</div>
												</div>

												<div class="col-md-6 mb-3">
													<label for="end_w" class="form-label">Ends</label>
													<div class="input-group has-validation">
														<input type="date" class="form-control" id="end_w" name="end_w" value="<?=$schedule['end']?>" autocomplete="off">
													</div>
												</div>

												<div class="col-12 mb-3">
													<label for="dow" class="form-label">On Which Days?</label>
													<div class="input-group has-validation">
														<div class="btn-group" role="group" aria-label="Genre">
															<?
															$this_dow = explode(',', $schedule['dow']);
															foreach ($dows as $key => $value) {
																?>
																<input type="checkbox" name="dow[]" value="<?=$key?>"<? if (in_array($key, $this_dow)) echo(' checked'); ?> class="btn-check" id="dow_set<?=$key?>" autocomplete="off">
																<label class="btn btn-outline-primary" for="dow_set<?=$key?>"><?=$value?></label>
																<?
															}
															?>
														</div>
													</div>
												</div>
											</div>
										</div>
										<?
									}

									# repeats monthly
									if (!is_null($schedule['dom'])) {
										?>
										<div class="tab-pane fade<? if (!is_null($schedule['dom'])) echo(' show active'); ?>" id="contact" role="tabpanel" aria-labelledby="contact-tab">
											<div class="row">
												<div class="col-md-6 mb-3">
													<label for="begin_m" class="form-label">Begins</label>
													<div class="input-group has-validation">
														<input type="date" class="form-control" id="begin_m" name="begin_m" value="<?=$schedule['begin']?>" autocomplete="off">
													</div>
												</div>

												<div class="col-md-6 mb-3">
													<label for="end_m" class="form-label">Ends</label>
													<div class="input-group has-validation">
														<input type="date" class="form-control" id="end_m" name="end_m" value="<?=$schedule['end']?>" autocomplete="off">
													</div>
												</div>

												<div class="col-12 mb-3">
													<label for="dom" class="form-label">On Which Days?</label>
													<div class="input-group has-validation">
														<div class="btn-group monthly" role="group" aria-label="Genre">
															<?
															$this_dom = explode(',', $schedule['dom']);
															for ($i = 1; $i <= 31; $i++) {
																?>
																<input type="checkbox" name="dom[]" value="<?=$i?>"<? if (in_array($i, $this_dom)) echo(' checked'); ?> class="btn-check" id="dom_set<?=$i?>" autocomplete="off">
																<label class="btn btn-outline-primary" for="dom_set<?=$i?>"><?=$i?></label>
																<?
															}
															?>
														</div>
													</div>
												</div>
											</div>
										</div>
										<?
									}
									?>
								</div>

								<div class="row">
									<div class="col-md-6 mb-3">
										<label for="starts" class="form-label">Starts</label>
										<div class="input-group has-validation">
											<input type="time" class="form-control" id="starts" name="starts" value="<?=$schedule['starts']?>" autocomplete="off">
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="ends" class="form-label">Ends</label>
										<div class="input-group has-validation">
											<input type="time" class="form-control" id="ends" name="ends" value="<?=$schedule['ends']?>" autocomplete="off">
										</div>
									</div>
								</div>

								<div class="d-flex">
									<button class="flex-grow-1 btn btn-dark btn-lg mt-20 m-2" type="submit">Save</button>
									<a href="event_manage.php" class="btn btn-danger btn-lg mt-20 m-2 w-25">Cancel</a>
								</div>

								<input type="hidden" name="id" value="<?=$item['id']?>">
								<input type="hidden" name="name" value="<?=$item['name']?>">
								<input type="hidden" name="scheduleID" value="<?=$schedule['id']?>">
								<input type="hidden" name="editing" value="yes">
							</form>
							<?
							break;

						case 'list':
							?>
							<div class="table-responsive mt-2">
								<table class="table">
								<thead>
									<tr>
										<th scope="col">#</th>
										<th scope="col">Dates</th>
										<th scope="col">Repeats</th>
										<th scope="col">Time</th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody class="table-striped">
									<tr>
										<td colspan="4"></td>
										<td align="center">
											<a href="?action=add&id=<?=$item['id']?>"><i class="fal fa-plus-square"></i></a>
										</td>
									</tr>
									<?
									$query = "SELECT * FROM places_schedules WHERE placeID = " . $item['id'] . " ORDER BY begin ";
									$result = mysqli_query($link, $query);
									if ($result && mysqli_num_rows($result) > 0) {
										$j = 1;
										while ($row = mysqli_fetch_assoc($result)) {
											?>
											<tr>
												<th scope="row"><?=$j++?></th>
												<td><?=pretty_date_range($row['begin'], $row['end'])?></td>
												<td>
													<?
													if (!is_null($row['dow'])) {
														echo('weekly on ');
														$first = true;
														foreach (explode(',', $row['dow']) as $dow) {
															if ($first) $first = false;
															else echo(',');
															echo($dowsab[$dow]);
														}
													}
													if (!is_null($row['dom'])) {
														echo('monthly on the ' . $row['dom']);
													}
													?>
												</td>
												<td>
													<?
													if (!empty($row['starts'])) echo(date('g:ia', strtotime($row['starts'])));
													if (!empty($row['starts']) || !empty($row['ends'])) echo(' - ');
													if (!empty($row['ends'])) echo(date('g:ia', strtotime($row['ends'])));
													?>
												</td>
												<td align="center">
													<a href="?action=edit&id=<?=$item['id']?>&scheduleID=<?=$row['id']?>"><i class="fal fa-edit"></i></a>
													<a href="?action=delete&id=<?=$item['id']?>&scheduleID=<?=$row['id']?>&name=<?=urlencode($item['name'])?>"><i class="fal fa-trash"></i></a>
												</td>
											</tr>
											<?
										}
									}
									?>
								</tbody>
								</table>
							</div>
							<?
							break;
					}
					?>

				</div>
			</div>
		</main>
	</div>
	<style>
	.btn-group, .btn-group-vertical {
		display: flex;
		flex-wrap: wrap;
		justify-content: start;
	}
	.btn-group>.btn{
		margin-top: 5px;
		max-width: 100px;
	}
	.btn-group.monthly>.btn{
		max-width: 50px;
	}
	</style>
	<script src="/assets/third-party/input-masking/inputmask.min.js"></script>
	<script>
	(function () {
		'use strict'

		//Mask input
		var phone = document.getElementById("contact_phone");
		Inputmask({"mask": "(999) 999-9999"}).mask(phone);
		var zip = document.getElementById("zip");
		Inputmask({"mask": "99999"}).mask(zip);

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
	</script>

	<style>
	.btn-group label { font-size:.9rem; }
	</style>

<? include('footer.php'); ?>
