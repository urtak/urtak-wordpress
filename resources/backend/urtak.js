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

	$('#urtak-credentials-publication-key').change(function(event) {
		var $this = $(this)
		, $dependencies = $('#urtak-new-site-dependencies')
		, $domains = $('#urtak-publication-domains')
		, $site_key_container = $('#urtak-publication-key-display-container')
		, $site_key = $('#urtak-publication-key-display');

		$domains.val($this.find('option:selected').attr('data-domains'));
		if(-1 == $this.val()) {
			$dependencies.show();
			$site_key_container.hide();
		} else {
			$dependencies.hide();
			$site_key_container.show();
			$site_key.text($this.val());
		}
	}).change();

	$('.urtak-tabbed-control a').live('click', function(event) {
		event.preventDefault();
		
		var $this = $(this)
		, $control = $this.parents('ul')
		, $item = $this.parents('li');

		$control.find('li.active').removeClass('active');
		$item.addClass('active');

		$('[data-tabbed-depend-on="' + $control.attr('id') + '"]').hide().filter($this.attr('href')).show();
	}).parent().filter(':first-child').find('a').click();

	$('.urtak-ajax-loader[data-action]').each(function(index, element) {
		var $element = $(element), action = $element.attr('data-action');

		$.post(
			ajaxurl,
			{ action: action },
			function(data, status) {
				$element.replaceWith(data);
			},
			'html'
		);
	});
});

