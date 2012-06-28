jQuery(document).ready(function($) {
	$('.urtak-login-signup-settings h2 a').click(function(event) {
		event.preventDefault();

		$(this).parents('form').hide().siblings('.urtak-login-signup-settings').show();
	});

	$('#urtak-placement-manual-tag').focus(function(event) {
		$(this).select();
	});

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

	$('.urtak-card-controls-icon').live('click', function(event) {
		event.preventDefault();

		var $this = $(this)
		, $card = $this.parents('.urtak-card')
		, vars = {
			action: 'urtak_modify_question_status',
			questions: [
				{
					post_id: $card.attr('data-post-id'),
					question_id: $card.attr('data-question-id'),
					action: $this.attr('data-action')
				}
			] 
		};

		$(this).addClass('active').siblings('a').removeClass('active');

		UrtakDelegates.modify_question_status(vars);
	});

	$('#urtak-meta-box-controls-alls a').live('click', function(event) {
		event.preventDefault();

		var $this = $(this)
		, $cards = $('.urtak-card')
		, action = $this.attr('data-action')
		, vars = {
			action: 'urtak_modify_question_status',
			questions: []
		};

		$cards.each(function(index, element) {
			var $card = $(element);
			
			vars.questions.push({
				post_id: $card.attr('data-post-id'),
				question_id: $card.attr('data-question-id'),
				action: action
			});

			$card.find('[data-action="' + action + '"]').addClass('active').siblings('a.active').removeClass('active');
		});

		UrtakDelegates.modify_question_status(vars);
	});

	$('[data-urtak-attribute]').live('change', function(event) {
		UrtakDelegates.get_questions(UrtakDelegates.get_search_vars());
	}).filter(':first').change();

	$('#urtak-meta-box-controls-pager a').live('click', function(event) {
		event.preventDefault();

		if('disabled' == $(this).attr('data-disabled')) {
			return false;
		}

		$('#urtak-meta-box-controls-page-number').val($(this).attr('data-value')).change();
	});

	$('.urtak-meta-box-controls-per-page-link').live('click', function(event) {
		event.preventDefault();

		$(this).addClass('active').siblings('a').removeClass('active');
		$('#urtak-meta-box-per-page').val($(this).text()).change();
	});
});

var UrtakDelegates = (function(jQuery) {
	var $ = jQuery
	, _get_questions = null
	, _get_search_vars = null
	, _modify_question_status = null
	, _plot_bar_graph = null;

	_get_questions = function(vars) {
		var $cards = $('#urtak-meta-box-cards').addClass('loading');
		$cards.find('.urtak-card').remove();

		$.post(
			ajaxurl,
			vars,
			function(data, status) {
				$('#urtak-meta-box-controls-pager').empty().append(data.pager);
				$cards.removeClass('loading').append(data.cards);

				if('function' === typeof(callback)) {
					callback(data, status);
				}
			},
			'json'
		);
	};

	_get_search_vars = function() {
		var vars = {};

		$('[data-urtak-attribute]').each(function(index, element) {
			var $element = $(element);

			vars[$element.attr('data-urtak-attribute')] = $element.val();
		});

		return vars;
	};

	_modify_question_status = function(vars) {
		$.post(
			ajaxurl,
			vars,
			function(data, status) {
				console.log(data);
			},
			'json'
		);
	};

	_plot_bar_graph = function(selector, data, ticks) {
		$.plot(
			$(selector), 
			[
				{ 
					data: data, 
		    		bars: { 
		    			align: 'center', 
		    			fill: '#00aef0',
		    			fillColor: '#00aef0',
		    			show: true,
		    			showNumbers: true
		    		},
		    		color: '#00aef0'
		    	}
		    ], 
			{
				grid: {
					borderWidth: 0,
					color: '#666666',
					show: true
				},
				xaxis: {
					max: (ticks.length * 2) - 1,
					min: -1, 
					tickLength: 0,
					ticks: ticks 
				}
			}
		);
	};

	return {
		get_questions: _get_questions,
		get_search_vars: _get_search_vars,
		modify_question_status: _modify_question_status,
		plot_bar_graph: _plot_bar_graph
	};
})(jQuery);