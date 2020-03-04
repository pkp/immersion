// initiating tag-it

$("#tagitInput").each(function() {
	var autocomplete_url = $(this).data("autocomplete-url");
	$(this).tagit({
		fieldName: $(this).data("field-name"),
		allowSpaces: false,
		autocomplete: {
			source: function(request, response) {
				$.ajax({
					url: autocomplete_url,
					data: {term: request.term},
					dataType: "json",
					success: function(jsonData) {
						if (jsonData.status === true) {
							response(jsonData.content);
						}
					}
				});
			}
		}
	});
});

(function () {
	/**
	 * Determine if the user has opted to register as a reviewer
	 *
	 * @see: /templates/frontend/pages/userRegister.tpl
	 */
	function isReviewerSelected() {
		var group = $("#reviewerOptinGroup").find("input");
		var is_checked = false;
		group.each(function() {
			if ($(this).is(":checked")) {
				is_checked = true;
				return false;
			}
		});
		
		return is_checked;
	}
	
	/**
	 * Reveal the reviewer interests field on the registration form when a
	 * user has opted to register as a reviewer
	 *
	 * @see: /templates/frontend/pages/userRegister.tpl
	 */
	function reviewerInterestsToggle() {
		var is_checked = isReviewerSelected();
		if (is_checked) {
			$("#reviewerInterests").removeClass("hidden");
		} else {
			$("#reviewerInterests").addClass("hidden");
		}
	}
	
	// Update interests on page load and when the toggled is toggled
	reviewerInterestsToggle();
	$("#reviewerOptinGroup input").click(reviewerInterestsToggle);
})();

// Search form, wrapper for select tags

(function () {
	var searchSelects = $('.search__form .search__select');
	
	if (!searchSelects.length) return false;
	
	searchSelects.wrap("<div class='select__wrapper col'></div>");
})();

(function($) {
	
	// Open login modal when nav menu links clicked
	$('.nmi_type_user_login').click(function() {
		$('#loginModal').modal();
		return false;
	})
})(jQuery);


// Article detail page: authors

(function ($) {
	
	// Show author affiliation under authors list (for large screen only)
	var authorString = $('.author-string__href');
	$(authorString).click(function(event) {
		event.preventDefault();
		var elementId = $(this).attr('href').replace('#', '');
		$('.article-details__author').each(function () {
			
			// Show only targeted author's affiliation on click
			if ($(this).attr('id') === elementId && $(this).hasClass('hidden')) {
				$(this).removeClass('hidden');
			} else {
				$(this).addClass('hidden');
			}
		});
		
		// Add specifiers to the clicked author's link
		$(authorString).each(function () {
			if ($(this).attr('href') === ('#' + elementId) && !$(this).hasClass('active')){
				$(this).addClass('active');
				$(this).children('.author-plus').addClass('hidden');
				$(this).children('.author-minus').removeClass('hidden');
			} else if ($(this).attr('href') !== ('#' + elementId) || $(this).hasClass('active')) {
				$(this).removeClass('active');
				$(this).children('.author-plus').removeClass('hidden');
				$(this).children('.author-minus').addClass('hidden');
			}
		});
	})
})(jQuery);

// Not display the menu if all items are inaccessible

(function ($) {
	
	var navPrimary = $('#navigationPrimary');
	
	if (!navPrimary.length) return false;
	
	if (!navPrimary.children().length > 0) {
		$('.main-header__nav').addClass('hidden');
	}
	
})(jQuery);

// Toggle display of consent checkboxes in site-wide registration

var $contextOptinGroup = $('#contextOptinGroup');
if ($contextOptinGroup.length) {
	var $roles = $contextOptinGroup.find('.registration-context__roles :checkbox');
	$roles.change(function() {
		var $thisRoles = $(this).closest('.registration-context__roles');
		if ($thisRoles.find(':checked').length) {
			$thisRoles.siblings('.context_privacy').addClass('context_privacy_visible');
		} else {
			$thisRoles.siblings('.context_privacy').removeClass('context_privacy_visible');
		}
	});
}
