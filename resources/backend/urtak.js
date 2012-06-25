jQuery(document).ready(function($) {
	$('.urtak-login-signup-settings h2 a').click(function(event) {
		event.preventDefault();

		$(this).parents('form').hide().siblings('.urtak-login-signup-settings').show();
	});
});
