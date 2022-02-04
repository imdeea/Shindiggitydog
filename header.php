<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Bootstrap core CSS -->
		<link href="/assets/css/bootstrap.min.css" rel="stylesheet">
		<script src="https://kit.fontawesome.com/181b72cb91.js" crossorigin="anonymous"></script>
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@500&display=swap" rel="stylesheet">
		<meta name="theme-color" content="#ffc266">
		<!-- Bootstrap core CSS -->
		<link href="/assets/css/custom.css" rel="stylesheet">
	</head>
	<body>
		<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
	  <a class="navbar-brand" href="/">
       <img src="/assets/images/logo.svg" alt="" height="30" class="d-inline-block align-text-top">
       <span class="d-none d-lg-inline-block">Shin Diggity Dog</span>
     </a>
	 <div class="ms-auto me-2">

		 <a href="login.php" class="btn btn-primary border-width-2 d-inline-block d-lg-none"><i class="fas fa-lock"></i></a>
		 <a href="account.php" class="btn btn-primary border-width-2 d-inline-block d-lg-none"><i class="fas fa-user-circle"></i></a>
		 <a href="logout.php" class="btn btn-secondary border-width-2 d-inline-block d-lg-none"><i class="fas fa-door-open"></i></a>

	 </div>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">

        <div class="main-navigation me-auto mb-2 mb-lg-0">
			<ul class="navbar-nav">
	          <li class="nav-item">
	            <a class="nav-link active" aria-current="page" href="/">Home</a>
	          </li>
	          <li class="nav-item">
	            <a class="nav-link" href="most_views.php">Popular</a>
	          </li>
	          <li class="nav-item">
	            <a class="nav-link" href="favorites.php">Favorites</a>
	          </li>
	        </ul>
		</div>
		<form class="d-flex ms-auto">
			<input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
			<button class="btn btn-outline-success" type="submit">Search</button>
		</form>
		<div class="ms-2">

			<a href="login.php" class="btn btn-primary border-width-2 d-none d-lg-inline-block"><i class="fas fa-lock"></i><span class="ms-2 d-inline-block">Log In</span></a>
			<a href="account.php" class="btn btn-primary border-width-2 d-none d-lg-inline-block"><i class="fas fa-user-circle"></i><span class="ms-2 d-inline-block">Account</span></a>
			<a href="logout.php" class="btn btn-secondary border-width-2 d-none d-lg-inline-block"><i class="fas fa-door-open"></i><span class="ms-2 d-inline-block">Log Out</span></a>

		</div>

    </div>
    </div>
  </div>
</nav>
