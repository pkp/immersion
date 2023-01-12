(function() {
	if (!document.querySelector('.page_user.op_register')) {
		return;
	}

	const checkboxReviewerInterests = document.getElementById('checkbox-reviewer-interests');
	if (!checkboxReviewerInterests) {
		return;
	}

	/**
	 * Reveal the reviewer interests field on the registration form when a
	 * user has opted to register as a reviewer
	 *
	 * @see: /templates/frontend/pages/userRegister.tpl
	 */
	function reviewerInterestsToggle() {
		if (checkboxReviewerInterests.checked) {
			document.getElementById('reviewerInterests').classList.remove('hidden');
		} else {
			document.getElementById('reviewerInterests').classList.add('hidden');
		}
	}

	// Update interests on page load and when the toggled is toggled
	reviewerInterestsToggle();
	document.querySelector('#reviewerOptinGroup input').addEventListener('click', reviewerInterestsToggle);
})();

// Search form, wrapper for select tags

(function () {
	const searchSelects = document.querySelectorAll('.search__form .search__select');

	if (!searchSelects.length) return false;

	searchSelects.forEach((select) => {
		const wrapper = document.createElement('div');
		wrapper.classList.add('select__wrapper', 'col');
		wrapper.append(select.cloneNode(true));
		select.replaceWith(wrapper);
	});
})();

(function () {
	
	// Open login modal when nav menu links clicked
	document.querySelectorAll('.nmi_type_user_login').forEach((userLogin) => {
		userLogin.addEventListener('click', function (event) {
			event.preventDefault();
			const loginModal = new bootstrap.Modal('#loginModal');
			loginModal.show();
		});
	});
})();


// Article detail page: authors

(function () {
	const authors = document.querySelectorAll('.author-string__href');
	authors.forEach((authorString) => {
		authorString.addEventListener('click', function (event) {
			event.preventDefault();

			// Show only targeted author's affiliation on click
			let targetId = this.getAttribute('href').replace('#', '');
			const target = document.getElementById(targetId);

			document.querySelectorAll('.article-details__author').forEach((authorDetails) => {
				if (authorDetails.getAttribute('id') === targetId && authorDetails.classList.contains('hidden')) {
					authorDetails.classList.remove('hidden');
				} else {
					authorDetails.classList.add('hidden');
				}
			});

			authors.forEach((sibling) => {
				if (authorString === sibling && !sibling.classList.contains('active')) {
					sibling.classList.add('active');
					sibling.querySelector(':scope .author-plus').classList.add('hidden');
					sibling.querySelector(':scope .author-minus').classList.remove('hidden');
				} else {
					sibling.classList.remove('active');
					sibling.querySelector(':scope .author-plus').classList.remove('hidden');
					sibling.querySelector(':scope .author-minus').classList.add('hidden');
				}
			});
		});
	});
})();

// Not display the menu if all items are inaccessible

(function () {

	const navPrimary = document.getElementById('navigationPrimary');
	if (!navPrimary) {
		return;
	}

	if (!(navPrimary.childElementCount > 0)) {
		navPrimary.parentElement.parentElement.classList.add('hidden');
	}
})();

// Toggle display of consent checkboxes in site-wide registration

(function () {
	const contextOptinGroup = document.getElementById('contextOptinGroup');
	if (!contextOptinGroup) {
		return;
	}

	const privacyVisible = 'context_privacy_visible';

	document.querySelectorAll('.context').forEach((context) => {
		const roleInputs = context.querySelectorAll(':scope .registration-context__roles input[type=checkbox]');
		roleInputs.forEach((roleInput) => {
			roleInput.addEventListener('change', function () {
				const contextPrivacy = context.querySelector(':scope .context_privacy');
				if (!contextPrivacy) {
					return;
				}

				if (this.checked) {
					if (!contextPrivacy.classList.contains(privacyVisible)) {
						contextPrivacy.classList.add(privacyVisible);
						return;
					}
				}

				for (let i = 0; i < roleInputs.length; i++) {
					const sibling = roleInputs[i];
					if (sibling === roleInput) {
						continue;
					}
					if (sibling.checked) {
						return;
					}
				}

				contextPrivacy.classList.remove(privacyVisible);
			});
		});
	});
})();
