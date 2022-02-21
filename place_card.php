
	<div class="col">

		<div class="ratio ratio-3x4" style="background-image:url('https://cdn.filestackcontent.com/resize=w:310/<?=$row['handle']?>'); background-size: cover; background-position: center center;">
			<a href="event.php?id=<?=$row['id']?>" class="placecard-link"></a>
			<div class="placecard-info-holder">
				<div class="placecard-info-container">
					<div class="placecard-title fun-font stretched-link"><?=$row['name']?></div>
					<button class="btn categories"><?=$row['genres']?></button>
					<? if ($row['offer']) { ?><button class="btn btn-sm btn-outline-secondary btn-outline-secondary-icon"><i class="fas fa-ticket fa-fw"></i></button><? } ?>
				</div>
			</div>

			<div class="placecard-image-holder">
				<a href="#" class="favorite <?=!is_null($row['favorite'])?'active':''?>" id="f<?=$row['id'] ?>" place="<?=$row['id']?>" value="<?=!is_null($row['favorite'])?1:0?>">
					<i class="fa<?=!is_null($row['favorite'])?'s':'l'?> fa-<?=LIKE_SYMBOL?> fa-2x fa-fw"></i>
				</a>
			</div>
		</div>
	</div>

<? /*
remaining pieces

	if ($row['preferred']) echo(' preferred');
	<small class="text-muted"><?=number_format($row['distance'], 0)?> mi</small>
*/ ?>
