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
	/* Settings Page */

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

	$('input[name="urtak[has_first_question]"]').change(function(event) {
		var $this = $(this),
			$container = $('#urtak-help-has_first_question-yes'),
			value = $this.val();

		if($this.is(':checked')) {
			switch($this.val()) {
				case 'yes':
					$container.show().find('input').focus();
					break;
				case 'no':
					$container.hide();
					break;
			}
		}
	}).change();

	$('#urtak-blacklisting').change(function(event) {
		var $this = $(this),
			$container = $('.urtak-blacklisting-dependent'),
			value = $this.val();

		if($this.is(':checked')) {
			$container.show();
		} else {
			$container.find('input[type="checkbox"]').removeAttr('checked').change();
			$container.hide();
		}
	}).change();

	$('#urtak-blacklist_override').change(function(event) {
		var $this = $(this),
			$container = $('.urtak-blacklist_override-dependent'),
			value = $this.val();

		if($this.is(':checked')) {
			$container.show().find('textarea').focus();
		} else {
			$container.hide();
		}
	}).change();

	/* End Settings Page */


	/* Ajax Meta Boxes */

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

	/* End Ajax Meta Boxes */


	/* Tabs (i.e. At a Glance) */

	$('.urtak-tabbed-control a').live('click', function(event) {
		event.preventDefault();

		var $this = $(this)
		, $control = $this.parents('ul')
		, $item = $this.parents('li');

		$control.find('li.active').removeClass('active');
		$item.addClass('active');

		$('[data-tabbed-depend-on="' + $control.attr('id') + '"]').hide().filter($this.attr('href')).show();
	}).parent().filter(':first-child').find('a').click();

	/* End Tabs */



	/* Post Editing VM */

	var $editor = $('.urtak-questions-editor');
	if($editor.size() > 0) {
		window.questions_vm = new UrtakQuestionsVM($('#post_ID').val());

		ko.applyBindings(window.questions_vm, $editor.get(0));

		window.questions_vm.fetch_questions();

		$editor.parents('form').submit(function(event) {
			$editor.find('#urtak-serialized').val(ko.toJSON(window.questions_vm));
		});
	}


	/* End Post Editing VM */



	/* Moderation VM */

	/* End Moderation VM */



	/* Reports VM */

	/* End Reports VM */
});

var UrtakQuestionsVM = function(post_id) {
	var self = this;

	self.page = ko.observable(1);
	self.pages = ko.observable(1);
	self.per_page = ko.observable(100);
	self.post_id = ko.observable(post_id);
	self.loading = ko.observable(false);
	self.questions = ko.observableArray();

	self.has_question = ko.computed(function() {
		return self.questions().length > 0;
	});
	self.no_questions = ko.computed(function() {
		return self.questions().length === 0;
	})

	self.add_question = function(question) {
		question.post_id = self.post_id;

		self.questions.unshift(new UrtakQuestionVM(question));
	};

	self.add_new_question = function() {
		self.add_question({});
	};

	self.remove_question = function() {
		if(confirm(Urtak_Vars.remove_question)) {
			this.reject();

			self.questions.remove(this);
		}
	};

	self.set_first = function() {
		self.unset_first();

		this.toggle_first(true);
	};

	self.unset_first = function() {
		ko.utils.arrayForEach(self.questions(), function(question) {
			question.toggle_first(false);
		});
	};

	self.fetch_questions = function() {
		self.loading(true);

		jQuery.get(
			ajaxurl,
			{
				action: 'urtak_get_questions',
				page: self.page(),
				per_page: self.per_page(),
				post_id: self.post_id
			},
			function(data, status) {
				self.pages(data.questions.pages);

				ko.utils.arrayForEach(data.questions.question, function(question) {
					self.add_question(question);
				});

				self.loading(false);
			},
			'json'
		);
	};
};

var UrtakUrtakVM = function(urtak) {

};

var UrtakQuestionVM = function(question) {
	var self = this;

	self.change_status = function(status) {
		if(self.existing()) {
			jQuery.get(
				ajaxurl,
				{
					action: 'urtak_modify_question_status',
					post_id: self.post_id,
					question_id: self.id,
					status: status
				},
				function(data, status) {
					console.log(data);
				},
				'json'
			);
		}
	};

	self.approve = function() {
		self.change_status('approve');
	}

	self.archive = function() {
		self.change_status('archive');
	}

	self.reject = function() {
		self.change_status('reject');
	};

	self.toggle_first = function(is_first) {
		if(is_first != self.first_question()) {
			// Send request to backend and then update local status
			jQuery.get(
				ajaxurl,
				{
					action: 'urtak_modify_question_first',
					first_question: is_first,
					post_id: self.post_id,
					question_id: self.id
				},
				function(data, status) {
					console.log(data);
				},
				'json'
			);


			self.first_question(is_first);
		}
	};

	self.first_question = ko.observable(question.first_question || false);
	self.not_first_question = ko.computed(function() { return !self.first_question(); });

	self.created_at = question.created_at || Math.round(new Date().getTime() / 1000);
	self.id = question.id || 0;
	self.post_id = question.post_id;
	self.responses = question.responses || { counts: { total: 0 } };
	self.status = ko.observable(question.status || 'pending');
	self.text = ko.observable(question.text || '');
	self.user_generated = question.user_generated || false;

	self.existing = ko.computed(function() {
		return self.id != 0;
	});

	self.number_responses = ko.computed(function() {
		return self.responses && self.responses.counts ? self.responses.counts.total : 0;
	});
};

var UrtakModerationVM = function() {
	var self = this;

};

var UrtakResultsVM = function() {
	var self = this;


};

var UrtakPlot = function(selector, data, ticks) {
	var $ = jQuery;

	if($.plot) {
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
	}
};