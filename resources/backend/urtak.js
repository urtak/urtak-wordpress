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

		window.questions_vm.add_question({}, true);
		window.questions_vm.get_post_data();

		$editor.parents('form').submit(function(event) {
			$editor.find('#urtak-serialized').val(ko.toJSON(window.questions_vm));
		});
	}

	$('#urtak-force-hide-urtak').change(function(event) {
		if($(this).is(':checked')) {
			$editor.find('table,.tablenav-pages').hide();
		} else {
			$editor.find('table,.tablenav-pages').show();
		}
	}).change();


	/* End Post Editing VM */



	/* Moderation VM */

	var $moderation = $('.urtak-moderation');

	if($moderation.size() > 0) {
		var hash = document.location.hash.substring(1),
			parts = hash.split('-'),
			post_id = 0,
			question_id = 0;

		if(2 === parts.length) {
			post_id = parts[0];
			question_id = parts[1];
		}

		window.urtak_moderation_vm = new UrtakReviewVM(post_id, question_id);

		ko.applyBindings(window.urtak_moderation_vm, $moderation.get(0));

		window.urtak_moderation_vm.fetch_questions();
		window.urtak_moderation_vm.fetch_flags();
		window.urtak_moderation_vm.fetch_urtaks();
	}

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

		window.urtak_results_vm = new UrtakReviewVM(post_id, question_id);

		ko.applyBindings(window.urtak_results_vm, $results.get(0));

		window.urtak_results_vm.questions_filter('st|aa');
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
	self.total = ko.observable(0);
	self.urtak = ko.observable(false);

	self.has_pages = ko.computed(function() {
		return !self.loading() && self.pages() > 1;
	});

	self.has_previous_page = ko.computed(function() {
		return self.page() - 1 >= 1;
	});

	self.previous_page = function() {
		if(self.has_previous_page()) {
			self.page(self.page() - 1);
			self.get_post_data();
		}
	};

	self.has_next_page = ko.computed(function() {
		return self.page() + 1 <= self.pages();
	});

	self.next_page = function() {
		if(self.has_next_page()) {
			self.page(self.page() + 1);
			self.get_post_data();
		}
	};

	self.has_question = ko.computed(function() {
		return !self.loading() && self.questions().length > 0;
	});
	self.no_questions = ko.computed(function() {
		return !self.loading() && self.questions().length === 0;
	});

	self.is_focused = ko.computed(function() {
		for(var i = 0; i < self.questions().length; i++) {
			if(self.questions()[i].has_focus()) {
				return true;
			}
		}

		return false;
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

	self.add_question = function(question, beginning) {
		question.post_id = self.post_id;

		var question_vm = new UrtakQuestionVM(question);

		if(beginning) {
			self.questions.unshift(question_vm);
		} else {
			self.questions.push(question_vm);
		}

		return question_vm;
	};

	self.add_new_question = function() {
		return self.add_question({}, true);
	};

	self.check_for_add = function(data, event) {
		if(13 === event.keyCode || 13 === event.which) {
			var question = self.add_new_question();

			question.has_focus(true);
		} else {
			return true;
		}
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
		if(!self.loading()) {
			self.loading(true);

			var questions_to_remove = ko.utils.arrayFilter(self.questions(), function(question) {
				return question.existing();
			});

			ko.utils.arrayForEach(questions_to_remove, function(question) {
				self.questions.remove(question);
			});

			jQuery.get(
				ajaxurl,
				{
					action: 'urtak_get_post_data',
					page: self.page(),
					per_page: self.per_page(),
					post_id: self.post_id
				},
				function(data, status) {
					self.pages(data.questions.pages);
					self.total(data.questions.entries);

					ko.utils.arrayForEach(data.questions.question, function(question) {
						self.add_question(question);
					});

					self.urtak(data.questions.urtak);

					self.loading(false);
				},
				'json'
			);
		}
	};

	self.has_new_questions = ko.computed(function() {
		var dirty = false;

		ko.utils.arrayForEach(self.questions(), function(question) {
			if(!question.existing() && '' !== question.text()) {
				dirty = true;
			}
		});

		return dirty;
	});
};

var UrtakQuestionVM = function(question) {
	var self = this;

	self.change_status = function(action) {
		if(self.existing()) {
			var status;
			switch(action) {
				case 'approve':
					status = 'approved';
					break;
				case 'reject':
					status = 'rejected';
					break;
				case 'archive':
					status = 'archived';
					break;
			}
			self.status(status);

			jQuery.get(
				ajaxurl,
				{
					action: 'urtak_modify_question_status',
					post_id: self.post_id,
					question_id: self.id,
					status: action
				},
				function(data, status) {

				},
				'json'
			);
		}
	};

	self.approve = function() {
		self.change_status('approve');
	};

	self.archive = function() {
		self.change_status('archive');
	};

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

				},
				'json'
			);


			self.first_question(is_first);
		}
	};

	self.has_focus = ko.observable(false);
	self.first_question = ko.observable(question.first_question || false);
	self.not_first_question = ko.computed(function() { return !self.first_question(); });

	self.data = question;

	self.created_at = question.created_at || Math.round(new Date().getTime() / 1000);
	self.id = question.id || 0;
	self.nicedate = question.nicedate;
	self.post_id = question.post_id;
	self.responses = question.responses || { counts: { total: 0 } };
	self.status = ko.observable(question.status || 'pending');
	self.text = ko.observable(question.text || '');
	self.user_generated = question.user_generated || false;

	self.nicestatus = ko.computed(function() {
		if('approved' == self.status()) {
			return 'Approved';
		} else if('pending' == self.status()) {
			return 'Pending';
		} else if('rejected' == self.status()) {
			return 'Rejected';
		} else if('archived' == self.status()) {
			return 'Archived';
		}
	});

	self.is_approved = ko.computed(function() {
		return 'approved' == self.status();
	});

	self.is_archived = ko.computed(function() {
		return 'archived' == self.status();
	});

	self.is_rejected = ko.computed(function() {
		return 'rejected' == self.status();
	});

	self.existing = ko.computed(function() {
		return self.id != 0;
	});

	self.number_responses = ko.computed(function() {
		return self.responses && self.responses.counts && self.responses && self.responses.counts.total ? self.responses.counts.total : 0;
	});

	self.yes_percent = ko.computed(function() {
		return self.responses && self.responses.percents && self.responses.percents.yes ? self.responses.percents.yes : 0;
	});

	self.no_percent = ko.computed(function() {
		return self.responses && self.responses.percents && self.responses.percents.no ? self.responses.percents.no : 0;
	});

	self.care_percent = ko.computed(function() {
		return self.responses && self.responses.percents && self.responses.percents.care ? self.responses.percents.care : 0;
	});
};

var UrtakFlaggedQuestionVM = function(flag) {
	var self = this;

	self.change_status = function(status) {
		jQuery.get(
			ajaxurl,
			{
				action: 'urtak_modify_flag',
				flag_id: self.id,
				status: status
			},
			function(data, status) {
				if(data.error) {
					console.log(data);
				}
			},
			'json'
		);
	};

	self.id = flag.id || 0;
	self.count = flag.count || 0;
	self.question = flag.question || '';
};

var UrtakReviewVM = function(post_id, question_id) {
	var self = this;

	self.post_id = ko.observable(post_id);
	self.question_id = ko.observable(question_id);

	var post_titles = {};

	self.post_title_accessor = ko.observable('');
	self.post_title = ko.computed(function() {
		var post_id = self.post_id(),
			post_title = self.post_title_accessor();

		if(post_titles[post_id]) {
			self.post_title_accessor(post_titles[post_id]);

			return post_titles[post_id];
		} else {
			jQuery.get(
				ajaxurl,
				{
					action: 'urtak_get_post_title',
					post_id: post_id
				},
				function(data, status) {
					if(data.post_title) {
						post_titles[data.post_id] = data.post_title;

						self.post_title_accessor(data.post_title);
					}
				},
				'json'
			);

			return 'Post ID ' + post_id;
		}
	});

	// Urtaks
	self.urtaks_order = ko.observable('n_responses|DESC');
	self.urtaks_page = ko.observable(1);
	self.urtaks_pages = ko.observable(0);
	self.urtaks_per_page = ko.observable(10);
	self.urtaks_total = ko.observable(0);

	self.urtaks_loading = ko.observable(false);

	self.urtaks = ko.observableArray();

	self.has_urtaks_pages = ko.computed(function() {
		return !self.urtaks_loading() && self.urtaks_pages() > 1;
	});

	self.has_previous_urtaks_page = ko.computed(function() {
		return self.urtaks_page() - 1 >= 1;
	});

	self.previous_urtaks_page = function() {
		if(self.has_previous_urtaks_page()) {
			self.urtaks_page(self.urtaks_page() - 1);
			self.fetch_urtaks();
		}
	};

	self.has_next_urtaks_page = ko.computed(function() {
		return self.urtaks_page() + 1 <= self.urtaks_pages();
	});

	self.next_urtaks_page = function() {
		if(self.has_next_urtaks_page()) {
			self.urtaks_page(self.urtaks_page() + 1);
			self.fetch_urtaks();
		}
	};

	self.add_urtak = function(urtak) {
		self.urtaks.push(urtak);
	};

	self.has_urtaks = ko.computed(function() {
		return !self.urtaks_loading() && self.urtaks().length > 0;
	});

	self.no_urtaks = ko.computed(function() {
		return !self.urtaks_loading() && self.urtaks().length === 0;
	});

	self.fetch_urtaks = function() {
		if(!self.urtaks_loading()) {
			self.urtaks_loading(true);

			self.urtaks.removeAll();

			jQuery.get(
				ajaxurl,
				{
					action: 'urtak_get_urtaks',
					order: self.urtaks_order(),
					page: self.urtaks_page(),
					per_page: self.urtaks_per_page(),
					post_id: self.post_id()
				},
				function(data, status) {
					self.urtaks_loading(false);

					if(data.error) {
						console.log(data);
					} else {
						self.urtaks_page(Math.floor(data.urtaks.offset / self.urtaks_per_page()) + 1);
						self.urtaks_pages(data.urtaks.pages);
						self.urtaks_total(data.urtaks.entries);

						for(var i = 0; i < data.urtaks.urtak.length; i++) {
							self.add_urtak(data.urtaks.urtak[i]);
						}
					}
				},
				'json'
			);
		}
	};

	self.filter_and_fetch_urtaks = function() {
		self.urtaks_page(1);

		self.fetch_urtaks();
	};

	self.reset_and_fetch_urtaks = function() {
		self.urtaks_page(1);
		self.urtaks_order('n_responses|DESC');

		self.fetch_urtaks();
	};


	self.load_questions_for_urtak = function(urtak) {
		self.questions_page(1);

		self.post_id(urtak.post_id);

		self.fetch_questions();
	};

	// Questions
	self.questions_filter = ko.observable('st|pe');
	self.questions_order = ko.observable('time');
	self.questions_page = ko.observable(1);
	self.questions_pages = ko.observable(0);
	self.questions_per_page = ko.observable(10);
	self.questions_search_query = ko.observable('');
	self.questions_total = ko.observable(0);

	self.questions_loading = ko.observable(false);

	self.questions = ko.observableArray();

	self.has_questions_pages = ko.computed(function() {
		return !self.questions_loading() && self.questions_pages() > 1;
	});

	self.has_previous_questions_page = ko.computed(function() {
		return self.questions_page() - 1 >= 1;
	});

	self.previous_questions_page = function() {
		if(self.has_previous_questions_page()) {
			self.questions_page(self.questions_page() - 1);
			self.fetch_questions();
		}
	};

	self.has_next_questions_page = ko.computed(function() {
		return self.questions_page() + 1 <= self.questions_pages();
	});

	self.next_questions_page = function() {
		if(self.has_next_questions_page()) {
			self.questions_page(self.questions_page() + 1);
			self.fetch_questions();
		}
	};

	self.add_question = function(question) {
		self.questions.push(new UrtakQuestionVM(question));
	};

	self.has_questions = ko.computed(function() {
		return !self.questions_loading() && self.questions().length > 0;
	});

	self.no_questions = ko.computed(function() {
		return !self.questions_loading() && self.questions().length === 0;
	});

	self.fetch_questions = function() {
		if(!self.questions_loading()) {
			self.questions_loading(true);

			self.questions.removeAll();

			jQuery.get(
				ajaxurl,
				{
					action: 'urtak_get_questions',
					order: self.questions_order(),
					page: self.questions_page(),
					per_page: self.questions_per_page(),
					post_id: self.post_id(),
					search: self.questions_search_query(),
					show: self.questions_filter()
				},
				function(data, status) {
					self.questions_loading(false);

					if(data.error) {
						console.log(data);
					} else {
						self.questions_page(Math.floor(data.questions.offset / self.questions_per_page())  + 1);
						self.questions_pages(data.questions.pages);
						self.questions_total(data.questions.entries);

						for(var i = 0; i < data.questions.question.length; i++) {
							self.add_question(data.questions.question[i]);
						}
					}
				},
				'json'
			);
		}
	};

	self.filter_and_fetch_questions = function() {
		self.questions_page(1);

		self.fetch_questions();
	};

	self.reset_and_fetch_questions = function() {
		self.post_id(0);

		self.questions_filter('st|pe');
		self.questions_page(1);
		self.questions_order('time');
		self.questions_search_query('');

		self.fetch_questions();
	};

	// Flagged questions
	self.flags_page = ko.observable(1);
	self.flags_pages = ko.observable(0);
	self.flags_per_page = ko.observable(10);
	self.flags_total = ko.observable(0);

	self.flags_loading = ko.observable(false);

	self.flags = ko.observableArray();

	self.has_flags_pages = ko.computed(function() {
		return !self.flags_loading() && self.flags_pages() > 1;
	});

	self.has_previous_flags_page = ko.computed(function() {
		return self.flags_page() - 1 >= 1;
	});

	self.previous_flags_page = function() {
		if(self.has_previous_flags_page()) {
			self.flags_page(self.flags_page() - 1);
			self.fetch_flags();
		}
	};

	self.has_next_flags_page = ko.computed(function() {
		return self.flags_page() + 1 <= self.flags_pages();
	});

	self.next_flags_page = function() {
		if(self.has_next_flags_page()) {
			self.flags_page(self.flags_page() + 1);
			self.fetch_flags();
		}
	};

	self.add_flag = function(flag) {
		self.flags.push(new UrtakFlaggedQuestionVM(flag));
	};

	self.has_flags = ko.computed(function() {
		return !self.flags_loading() && self.flags().length > 0;
	});

	self.no_flags = ko.computed(function() {
		return !self.flags_loading() && self.flags().length === 0;
	});

	self.fetch_flags = function() {
		if(!self.flags_loading()) {
			self.flags_loading(true);

			self.flags.removeAll();

			jQuery.get(
				ajaxurl,
				{
					action: 'urtak_get_flags',
					page: self.flags_page(),
					per_page: self.flags_per_page()
				},
				function(data, status) {
					self.flags_loading(false);

					if(data.error) {
						console.log(data);
					} else {
						self.flags_page(Math.floor(data.flags.offset / self.flags_per_page()) + 1);
						self.flags_pages(data.flags.pages);
						self.flags_total(data.flags.entries);

						for(var i = 0; i < data.flags.flag.length; i++) {
							self.add_flag(data.flags.flag[i]);
						}
					}
				},
				'json'
			);
		}
	};

	self.change_flag_status = function(flag, status) {
		flag.change_status(status);

		self.flags.remove(flag);
	}
}

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