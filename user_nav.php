<ul class="nav flex-column sub-nav">
	<li class="nav-item text-start">
		<a class="btn side-nav<?=($nav=='edit'?' active':'')?>" aria-current="page" href="user_edit.php">Edit</a>
	</li>
	<li class="nav-item text-start">
		<a class="btn side-nav<?=($nav=='socials'?' active':'')?>" aria-current="page" href="user_socials.php">Socials</a>
	</li>
	<li class="nav-item text-start">
		<a class="btn side-nav<?=($nav=='images'?' active':'')?>" aria-current="page" href="user_images.php">Profile Pic</a>
	</li>
	<? if ($_COOKIE['PoV'] == 'P') { ?>
		<li class="nav-item text-start">
			<a class="btn side-nav<?=($nav=='collaborators'?' active':'')?>" aria-current="page" href="user_collaborators.php">Collaborators</a>
		</li>
		<li class="nav-item text-start">
			<a class="btn side-nav<?=($nav=='preferences'?' active':'')?>" aria-current="page" href="user_preferences.php">Search Preferences</a>
		</li>
	<? } ?>
	<li class="nav-item text-start">
		<a class="btn side-nav" aria-current="page" href="profile.php?id=<?=$_COOKIE['id']?>">Profile</a>
	</li>
	<li class="nav-item text-start">
		<a class="btn side-nav" aria-current="page" href="logout.php">Sign Out</a>
	</li>
</ul>
