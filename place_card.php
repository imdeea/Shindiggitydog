<? 
$live = false; 
if ($live) $image = 'https://cdn.filestackcontent.com/resize=w:' . (isset($small_card)?'380':'650') . '/' . $row['handle'] . '';
else $image = '/assets/images/placeholder.jpg'; // 'https://picsum.photos/650/800';
?>

<div class="col event-card section" id="<?=$row['venueID']?>" data-section-name="<?=$row['name']?>">
	<div class="ratio ratio-3x4" style="background-image:url('<?=$image?>'); background-size: cover; background-position: center center;">
		<a href="/event/<?=base64_encode($row['id'])?>" class="placecard-link"></a>
		<div class="placecard-info-holder">
			<div class="placecard-info-container<? if (isset($small_card)) echo(' small-card'); ?>">
				<div class="placecard-title fun-font"><?=$row['name']?></div>
				<div class="placecard-details"><?=$row['neighborhood']?></div>
				<button class="btn categories"><?=$row['genres']?></button>
			</div>
		</div>

		<div class="placecard-image-holder<? if (isset($small_card)) echo(' small-card'); ?>">
			<? if ($row['venueID'] > 0) {
				if (is_null($row['venue_image'])) $row['venue_image'] = '/assets/images/placeholder.jpg';
				else $row['venue_image'] = 'https://cdn.filestackcontent.com/resize=w:50/' . $row['venue_image'];
				?>
				<? if ($logged_in) { ?>
					<a href="/profile/<?=$row['venue_handle']?>" class="venue"><img src="<?=$row['venue_image']?>" class="profile-icon"></a>
					<span class="fa-layers fa-fw follow-holder">
						<i class="fa-solid fa-<?=FOLLOW_SYMBOL?> fa-1p5x"></i>
						<a href="#" class="follow <?=!is_null($row['follow'])?'active':''?>" id="o<?=$row['venueID'] ?>" user="<?=$row['venueID']?>" value="<?=!is_null($row['follow'])?1:0?>"><i class="fa-<?=!is_null($row['follow'])?'solid':'light'?> fa-<?=FOLLOW_SYMBOL?>"></i></a>
					</span>
				<? } else { ?>
					<a href="/profile/<?=$row['venue_handle']?>" class="venue<? if (!$logged_in) echo('_alone'); ?>"><img src="<?=$row['venue_image']?>" class="profile-icon"></a>
				<? } ?>
			<? } ?>

			<? if ($logged_in) { ?>
				<a href="#" class="favorite <?=!is_null($row['favorite'])?'active':''?>" id="a<?=$row['id'] ?>" place="<?=$row['id']?>" value="<?=!is_null($row['favorite'])?1:0?>">
					<i class="fa-<?=!is_null($row['favorite'])?'solid':'light'?> fa-<?=LIKE_SYMBOL?> fa-1p5x"></i>
				</a>
			<? } ?>
		</div>
	</div>
</div>
