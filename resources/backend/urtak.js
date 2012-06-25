jQuery(document).ready(function($) {
	$('.urtak-login-signup-settings h2 a').click(function(event) {
		event.preventDefault();

		$(this).parents('form').hide().siblings('.urtak-login-signup-settings').show();
	});

	$('#urtak-placement-manual-tag').focus(function(event) {
		$(this).select();
	});

	var $posts_without_urtaks = $('#urtak-posts-without-urtaks-list')
	, $lis = $posts_without_urtaks.find('li')
	, $link = $('<a href="#"></a>').text(Urtak_Vars.see_all)
	, $more = $('<li></li>').append($link);

	if($lis.size() > 5) {
		$lis.filter(':gt(4)').hide();	
		$posts_without_urtaks.append($more);

		$link.click(function(event) {
			event.preventDefault();

			$lis.show();
			$more.hide();
		});
	}
});
