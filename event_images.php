<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location:event_manage.php"); exit; }

require_once("config.php");
include('security.php');
include('rxs.php');

require_once("vendor/autoload.php");
use Filestack\FilestackClient;
use Filestack\FilestackSecurity;

$errorMsg = "";
$successMsg = FALSE;

$link = mysqli_connect($db_host, $db_user, $db_pasw, $db_db);

if (@$_POST["submitted"] == "yes") {


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

	<section class="py-1 mb-2 text-center page_header btn-warning">
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
				<div class="col-12">

					<?
					$nav = 'images';
					include('event_nav.php');

					if ($errorMsg != "") { ?>
						<div class="alert alert-success">
							<?=$errorMsg?>
						</div>
					<? }

					if ($successMsg == "") {
						?>
						<script src="//static.filestackapi.com/filestack-js/2.x.x/filestack.min.js"></script>
						<script type="text/javascript">
						document.addEventListener("DOMContentLoaded", function(event) {

							const client = filestack.init('<?=FILESTACK_KEY?>');

							let options = {
								"displayMode": "inline", /* dropPane */
								"container": "#add_pane",
								"accept": [
									"image/*",
									"video/*"
								],
								"fromSources": [
									'local_file_system', 'instagram', 'googledrive', 'facebook', 'onedrive'
								],
								"uploadInBackground": false,
								"onFileUploadFinished": (res) => {
									console.log(res.handle);

									_eventID = <?=$item['id']?>;
									$.ajax({
										url: "event_images_recorder.php",
										type: "POST",
										data: { placeID: _eventID, handle:res.handle },
										dataType: "json",
										success: function(result) {
											console.log(result);
											$("#add_pane").hide();
											$("#list_pane").show();
										},
										error: function (xhr, ajaxOptions, thrownError) {
									        console.log(xhr.status);
									        console.log(thrownError);
										},										
									});
								},
								"maxFiles": 5,
								"imageMax": [1920,1920],
								"videoResolution": '1280x720',
							};

							picker = client.picker(options);
							picker.open();

						});
						</script>
						<div id="add_pane"></div>
						<?

						/*
						response looks like:

						{
						    "filesUploaded": [
						        {
						            "filename": "20211129_111607.jpg",
						            "handle": "eDAhK884TV27XZLUt3AY",
						            "mimetype": "image/jpeg",
						            "originalPath": "20211129_111607.jpg",
						            "size": 1919130,
						            "source": "local_file_system",
						            "url": "https://cdn.filestackcontent.com/eDAhK884TV27XZLUt3AY",
						            "uploadId": "cf211791138d7267b127ee3b5df3e398c",
						            "originalFile": {
						                "name": "20211129_111607.jpg",
						                "type": "image/jpeg",
						                "size": 1919130
						            },
						            "status": "Stored"
						        }
						    ],
						    "filesFailed": []
						}
						*/
						?>


						<div id="list_pane" class="table-responsive mt-2">
							<table class="table">
							<thead>
								<tr>
									<th scope="col">#</th>
									<th scope="col"></th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody class="table-striped">
								<tr>
									<td colspan="2"></td>
									<td align="center">
										<a href="#" onclick="return false;" id="show_add_pane"><i class="fal fa-plus-square"></i></button>
									</td>
								</tr>
								<?
								$query = "SELECT id, handle, seqno
										FROM places_images
										WHERE placeID = " . $item['id'] . "
										ORDER BY seqno
								";
								$result = mysqli_query($link, $query);
								$j = 0;
								if ($result && mysqli_num_rows($result) > 0) {
									while ($row = mysqli_fetch_assoc($result)) {
										$j++;
										?>
										<tr>
											<th scope="row"><?=$j++?></th>
											<td><?=$row['handle']?></td>
											<td align="center">
												<a href="#" onclick="return false;" id="show_add_pane"><i class="fal fa-id-badge"></i></button>
											</td>
										</tr>
										<?
									}
								}
								?>
							</tbody>
							</table>
						</div>


						</div>
						<?
					}
					?>
				</div>
			</div>
		</main>
	</div>

	<style>
	#add_pane { height:50vh; display:none; }
	.fsp-picker--inline { min-width: initial; min-height: initial; }
	</style>

	<script>
	$(document).ready(function(){
		$("#show_add_pane").click(function(){
			$("#list_pane").hide();
			$("#add_pane").show();
		});
	});
	</script>

<? include('footer.php'); ?>
