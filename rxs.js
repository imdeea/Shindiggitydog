$(function () {
	// tooltips
	$('[data-bs-toggle="tooltip"]').tooltip();

	// placecards
	$(".placecard-link").on("mouseenter", function(){
		toggleActiveClass($(this));
	});
	$(".placecard-link").on("mouseleave", function(){
		toggleActiveClass($(this));
	});

	function toggleActiveClass($el){
		$el.parent().toggleClass('active-card');
	}

	// favorites
	$(".favorite").click(function(e) {
		e.preventDefault();

		// do the animation
		_right = $(this).css("right");
		$( this ).animate({
				right: "-61px"
			}, {
				duration: 200,
				easing: "swing",
				complete: function() {
					// figure out the icons
					var icon = $(this).find('i');
					if ($(this).attr('value') == 1) {
						_newValue = 0;
						_oldIcon = 'fa-solid';
						_newIcon = 'fa-light';
					} else {
						_newValue = 1;
						_oldIcon = 'fa-light';
						_newIcon = 'fa-solid';
					}
					// send the ajax call to change the data
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

					$( this ).hide(0).css( "right", _right ).fadeIn(4000);
					//$( this ).css( "color", "white" );
					//$( this ).animate({
					//	right: "11px"
					//}, {
					//	duration: 200,
					//	easing: "swing"
					//});
				}
			}
		);
	});

	// follows
	$(".follow").click(function(e) {
		e.preventDefault();

		// do the animation
		$( this ).animate({
				right: "-50px"
			}, {
				duration: 200,
				easing: "swing",
				complete: function() {

					// figure out the icons
					var icon = $(this).find('i');
					if ($(this).attr('value') == 1) {
						_newValue = 0;
						_oldIcon = 'fa-solid';
						_newIcon = 'fa-light';
					} else {
						_newValue = 1;
						_oldIcon = 'fa-light';
						_newIcon = 'fa-solid';
					}
					// send the ajax call to change the data
					_followID = $(this).attr('user');
					_thisFavoriteButton = $(this);
					$.ajax({
						url: "favorite_user_handler.php",
						type: "POST",
						data: { followID:_followID, newValue:_newValue },
						success: function(result) {
							icon.removeClass(_oldIcon).addClass(_newIcon);
							_thisFavoriteButton.attr('value', _newValue);
						}
					});

					$( this ).hide(0).css( "right", "0" ).fadeIn(4000);
					//$( this ).css( "color", "white" );
					//$( this ).animate({
					//	right: "0px"
					//}, {
					//	duration: 200,
					//	easing: "swing"
					//});
				}
			}
		);
	});
	/* 
	$(".follow").hover(
		function() {
			$( this ).css( "color", "red" );
		}, 
		function() {
			$( this ).css( "color", "white" );
		}
	);
	*/

});
