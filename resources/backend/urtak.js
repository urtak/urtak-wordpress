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

		window.questions_vm.get_post_data();

		$editor.parents('form').submit(function(event) {
			$editor.find('#urtak-serialized').val(ko.toJSON(window.questions_vm));
		});
	}


	/* End Post Editing VM */



	/* Moderation VM */

	/* End Moderation VM */



	/* Results VM */

	var $results = $('.urtak-results');

	if($results.size() > 0) {
		var hash = document.location.hash.substring(1),
			parts = hash.split('-'),
			post_id = 0,
			question_id = 0;

		if(2 === parts.length) {
			post_id = parts[0];
			question_id = parts[1];
		}

		window.urtak_results_vm = new UrtakResultsVM(post_id, question_id);

		ko.applyBindings(window.urtak_results_vm, $results.get(0));

		window.urtak_results_vm.fetch_questions();
		window.urtak_results_vm.fetch_urtaks();
	}

	/* End Results VM */
});

var UrtakQuestionsVM = function(post_id) {
	var self = this;

	self.page = ko.observable(1);
	self.pages = ko.observable(1);
	self.per_page = ko.observable(10);
	self.post_id = ko.observable(post_id);
	self.loading = ko.observable(false);
	self.questions = ko.observableArray();
	self.urtak = ko.observable(false);

	self.has_pages = ko.computed(function() {
		return self.pages() > 1;
	});

	self.has_previous_page = ko.computed(function() {
		return self.page() - 1 >= 1;
	});

	self.previous_page = function() {
		self.page(self.page() - 1);
		self.get_post_data();
	};

	self.has_next_page = ko.computed(function() {
		return self.page() + 1 <= self.pages();
	});

	self.next_page = function() {
		self.page(self.page() + 1);
		self.get_post_data();
	};

	self.has_question = ko.computed(function() {
		return self.questions().length > 0;
	});
	self.no_questions = ko.computed(function() {
		return self.questions().length === 0;
	});

	self.has_urtak = ko.computed(function() {
		return false !== self.urtak();
	});
	self.pending_questions_count = ko.computed(function() {
		return self.has_urtak() ? self.urtak().pending_questions_count : 0;
	});
	self.responses_count = ko.computed(function() {
		return self.has_urtak() ? self.urtak().responses_count : 0;
	});

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

	self.get_post_data = function() {
		self.loading(true);

		self.questions.removeAll();

		jQuery.get(
			ajaxurl,
			{
				action: 'urtak_get_post_data',
				page: self.page(),
				per_page: self.per_page(),
				post_id: self.post_id
			},
			function(data, status) {
				console.log(data);

				self.pages(data.questions.pages);

				ko.utils.arrayForEach(data.questions.question, function(question) {
					self.add_question(question);
				});

				self.urtak(data.questions.urtak);

				self.loading(false);
			},
			'json'
		);
	};
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
			console.log(is_first);

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

var UrtakResultsVM = function(post_id, question_id) {
	var self = this;

	self.urtaks_page = ko.observable(1);
	self.urtaks_pages = ko.observable(1);
	self.urtaks_per_page = ko.observable(20);

	self.questions_page = ko.observable(1);
	self.questions_pages = ko.observable(1);
	self.questions_per_page = ko.observable(20);

	self.urtak_search_query = ko.observable('');
	self.question_search_query

	self.post_id = ko.observable(post_id);
	self.question_id = ko.observable(question_id);

	self.questions = ko.observableArray();
	self.add_question = function(question) {
		self.questions.push(new UrtakQuestionVM(question));
	};

	self.urtaks = ko.observableArray();
	self.add_urtak = function(urtak) {
		self.urtaks.push(urtak);
	};

	/// AJAX
	self.questions_loading = ko.observable(false);
	self.no_questions_found = ko.computed(function() {
		return !self.questions_loading() && self.questions.length > 0;
	});

	self.fetch_questions = function() {
		self.questions_loading(true);

		jQuery.get(
			ajaxurl,
			{
				action: 'urtak_get_questions',
				post_id: self.post_id()
			},
			function(data, status) {
				self.questions_loading(false);

				if(data.error) {

				} else {
					console.log(data);
					for(var i = 0; i < data.questions.length; i++) {
						self.add_question(data.questions[i]);
					}
				}
			},
			'json'
		);
	};

	/// AJAX
	self.urtaks_loading = ko.observable(false);
	self.no_urtaks_found = ko.computed(function() {
		return !self.urtaks_loading() && self.urtaks.length > 0;
	});

	self.fetch_urtaks = function() {
		self.urtaks_loading(true);

		jQuery.get(
			ajaxurl,
			{
				action: 'urtak_get_urtaks',
				post_id: self.post_id
			},
			function(data, status) {
				self.urtaks_loading(false);

				if(data.error) {

				} else {
					for(var i = 0; i < data.length; i++) {
						self.add_urtak(data[i]);
					}
				}
			},
			'json'
		);
	};
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