// Textarea and select clone() bug workaround | Spencer Tipping
// Licensed under the terms of the MIT source code license

// Motivation.
// jQuery's clone() method works in most cases, but it fails to copy the value of textareas and select elements. This patch replaces jQuery's clone() method with a wrapper that fills in the
// values after the fact.

// An interesting error case submitted by Piotr Przybył: If two <select> options had the same value, the clone() method would select the wrong one in the cloned box. The fix, suggested by Piotr
// and implemented here, is to use the selectedIndex property on the <select> box itself rather than relying on jQuery's value-based val().
(function(original) {
  jQuery.fn.clone = function() {
    var result = original.apply(this, arguments),
      my_textareas = this.find('textarea').add(this.filter('textarea')),
      result_textareas = result.find('textarea').add(result.filter('textarea')),
      my_selects = this.find('select').add(this.filter('select')),
      result_selects = result.find('select').add(result.filter('select'));

    for (var i = 0, l = my_textareas.length; i < l; ++i) jQuery(result_textareas[i]).val(jQuery(my_textareas[i]).val());
    for (var i = 0, l = my_selects.length; i < l; ++i) result_selects[i].selectedIndex = my_selects[i].selectedIndex;

    return result;
  };
})(jQuery.fn.clone); // via https://github.com/spencertipping/jquery.fix.clone/blob/master/jquery.fix.clone.js

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
		, $field = $this.parents('.urtak-field')
		, $site_key_container = $('#urtak-publication-key-display-container')
		, $site_key = $('#urtak-publication-key-display');

		$domains.val($this.find('option:selected').attr('data-domains'));
		if(-1 == $this.val()) {
			$dependencies.show();
			$site_key_container.hide();
			$field.addClass('urtak-field-highlighted');
		} else {
			$dependencies.hide();
			$site_key_container.show();
			$site_key.text($this.val());
			$field.removeClass('urtak-field-highlighted');
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

	$('.urtak-card-controls-icon:not(.urtak-card-controls-icon-special)[data-action]').live('click', function(event) {
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

	$('.urtak-help-content-inner-icon a').click(function(event) { event.preventDefault(); });

	$('.urtak-card-info-question textarea').keypress(function(event) {
		if(13 === event.which) {
			event.preventDefault();
		}
	});

	$('#urtak-meta-box-controls-alls a').live('click', function(event) {
		event.preventDefault();

		var $this = $(this)
		, $cards = $('.urtak-card[data-question-id]')
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
		$('#urtak-meta-box-controls-page-number').val(1);
		$('#urtak-meta-box-per-page').val($(this).text()).change();
	});

	$('.urtak-card-plot-controls li').live('click', function(event) {
		event.preventDefault();

		var $li = $(this)
		, $this = $li.find('a')
		, $parent = $this.parent()
		, $card = $this.parents('.urtak-card')
		, $clone = $card.clone()
		, $question = $clone.find('.large-text')
		, $answer = $clone.find('.urtak-adder-answer')
		, $controls = $clone.find('.urtak-card-controls')
		, answer = $parent.attr('data-answer')
		, question = $.trim($question.val());

		if($this.hasClass('activated')) {
			if(!$parent.hasClass('card-question-answers-d') && '' != question) {

				$clone.find('.urtak-card-plot-controls').hide();
				$clone.find('.urtak-card-plot-' + answer).show();
				$clone.find('.urtak-card-info-question').append(question);

				$answer.val(answer);

				$controls.show();
				$question.hide();

				$clone.css({ opacity: 0 }).insertAfter($card).animate({ opacity: 1 }, { duration: 600 });
			}

			$parent.parent().find('a').removeClass('activated').removeClass('deactivated');
			$card.find('.large-text').val('').focus();
		} else {
			$parent.parent().find('a').removeClass('activated').addClass('deactivated');
			$this.removeClass('deactivated').addClass('activated');
		}

	});

	$('.urtak-card-plot-controls li').live('mouseover', function(event) {
		var $this = $(this), $link = $this.find('a');

		$this.parent().find('a').removeClass('activated').addClass('deactivated');
		$link.removeClass('deactivated').addClass('activated');
	}).live('mouseout', function(event) {
		var $this = $(this);

		$this.parent().find('a').removeClass('activated').removeClass('deactivated');
	});



	$(document).keydown(function(event) {
		var $selected = $('.urtak-card-plot-controls li a.activated, .urtak-card-plot-controls li a:focus');
		var $last = $('.urtak-card-plot-controls li.card-question-answers-d a:focus');

		if($selected.size() > 0 && (event.which === 27 || event.which === 9)) {
			// check for escape or tab key press
			$selected.parent().parent().find('a').removeClass('activated').removeClass('deactivated');

			if(event.which === 27) {
				$selected.parents('.urtak-card').find('textarea').focus();
			}
		}

		if($last.size() > 0 && event.which === 9) {
			// user tabbed from last field

			event.preventDefault();
			$('.urtak-card-plot-controls li.card-question-answers-y a').focus();
		}
	});

	$('.urtak-card textarea').mousedown(function(event) {
		var $selected = $('.urtak-card-plot-controls li a');

		if($selected.size() > 0) {
			$selected.parent().parent().find('a').removeClass('activated').removeClass('deactivated');
		}
	});

	$('.urtak-card-controls-icon-special').live('click', function(event) {
		event.preventDefault();
		var $card = $(this).parents('.urtak-card')
		, $next = $card.nextAll('.urtak-card')
		, top = $card.outerHeight(true);

		$card.animate({ opacity: 0 }, { duration: 600, complete: function() {
			$card.remove();
			$next.css({ top: top + 'px' });
			$next.animate({ top: 0 }, { duration: 600 });
		} });
	});

	var $help = $('#urtak-meta-box-help'), $cards = $('#urtak-meta-box-cards');

	if($help.size() > 0) {
		$help.css({
			top: (parseInt($cards.outerHeight(true)) - parseInt($help.find('#urtak-meta-box-help-handle').outerHeight(true))) + 'px'
		});
	}

	$('#urtak-meta-box-help-handle').click(function(event) {
		event.preventDefault();

		var $this = $(this);

		if($this.hasClass('open')) {
			$help.animate({
				top: (parseInt($cards.outerHeight(true)) - parseInt($help.find('#urtak-meta-box-help-handle').outerHeight(true)))
			});
			$this.removeClass('open');
			$this.text(Urtak_Vars.help_text);
		} else {
			$help.animate({
				top: (parseInt($cards.outerHeight(true)) - parseInt($help.find('#urtak-meta-box-help-content').outerHeight(true)))
			});
			$this.addClass('open');
			$this.text(Urtak_Vars.help_close);
		}


	});
});

var UrtakDelegates = (function(jQuery) {
	var $ = jQuery
	, _disable_requests = false
	, _get_questions = null
	, _get_search_vars = null
	, _modify_question_status = null
	, _plot_bar_graph = null;

	_get_questions = function(vars) {
		if(!_disable_requests) {
			var $cards = $('#urtak-meta-box-cards').addClass('loading');
			$cards.find('.urtak-card[data-question-id]').remove();

			$.post(
				ajaxurl,
				vars,
				function(data, status) {
					if(data.error) {
						$cards.removeClass('loading').find('.urtak-card').remove()
						$cards.find('#urtak-meta-box-cards-holder').append('<div id="urtak-get-questions-error" class="settings-error error"><p>' + data.error_message + '</p></div>');
						_disable_requests = true;
					} else {
						$('#urtak-meta-box-controls-pager').empty().append(data.pager);
						$cards.removeClass('loading').find('.urtak-card:last').after(data.cards);
					}

					if('function' === typeof(callback)) {
						callback(data, status);
					}
				},
				'json'
			);
		}
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
				},
				yaxis: {
					min: 0
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