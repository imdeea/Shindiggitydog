<? if (!isset($top_nav)) $top_nav = NULL; ?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Bootstrap core CSS -->
		<link href="/assets/css/bootstrap.min.css" rel="stylesheet">
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<!--<link href="https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@300;700&display=swap" rel="stylesheet">-->
		<link rel="stylesheet" href="https://use.typekit.net/ilo5uba.css">
		<script src="https://kit.fontawesome.com/181b72cb91.js" crossorigin="anonymous"></script>
		<meta name="theme-color" content="#ffc266">
		<!-- Bootstrap core CSS -->
		<link href="/assets/css/custom.css" rel="stylesheet">
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
		<title>Shindig</title>
	</head>
	<body>
		<? if (!isset($nonav)) { ?>
			<nav class="navbar navbar-expand-lg navbar-light sticky-top">
				<div class="container">

					<a class="navbar-brand order-md-1" href="/"><span class="d-inline-block">SHINDIG</span></a>

					<div class="ms-2 right-navigation order-md-3">
						<a href="feed.php" class="btn"><i class="fa-<?=($top_nav=='feed')?'solid':'light'?> fa-house"></i></a>
						<a href="#search" class="btn d-inline-block d-md-none" data-bs-toggle="collapse"><i class="fa-<?=($top_nav=='search')?'solid':'light'?> fa-magnifying-glass"></i></a>
						<? if (!isset($_COOKIE['id']) || ($_COOKIE['id'] == NULL) || !is_numeric($_COOKIE['id'])) { ?>
							<a href="login.php" class="btn"><i class="fa-<?=($top_nav=='login')?'solid':'light'?> fa-sign-in"></i></a>
						<? } ?>
						<? if (isset($_COOKIE['id']) && ($_COOKIE['id'] != NULL) && is_numeric($_COOKIE['id'])) { ?>
							<a href="index.php" class="btn"><i class="fa-<?=($top_nav=='discover')?'solid':'light'?> fa-compass"></i></a>
							<a href="favorites.php" class="btn"><i class="fa-<?=($top_nav=='favorites')?'solid':'light'?> fa-star"></i></a>
							<a href="event_manage.php" class="btn"><i class="fa-<?=($top_nav=='events')?'solid':'light'?> fa-calendar-day"></i></a>
							<a href="profile.php?id=<?=$_COOKIE['id']?>" class="btn"><i class="fa-<?=($top_nav=='user')?'solid':'light'?> fa-user"></i></i></a>
						<? } ?>
					</div>

					<form id="search" class="d-md-flex mx-auto order-md-2 search-form-element collapse" action="search.php" method="POST">
						<div class="search-container">
							<span><i class="fa-regular fa-magnifying-glass"></i></span>
							<input class="form-control search" type="search" aria-label="Search" name="search" autocomplete="off">
						</div>
					</form>
				</div>
			</nav>
		<? } ?>
