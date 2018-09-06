// initiating tag-it

$(document).ready(function() {
	$("#tagitInput").tagit();
});

(function () {
	var checkbox = document.getElementById("checkbox-reviewer-interests");
	if (checkbox != null) {
		checkbox.onclick = function () {
			var tagitInput = document.getElementById("reviewerInterests");
			if (checkbox.checked == true) {
				tagitInput.classList.remove("hidden");
			} else {
				tagitInput.classList.add("hidden");
			}
		}
	}
})();


// Search form, wrapper for select tags

(function () {
	var searchSelects = $('.search__form .search__select');
	
	if (!searchSelects.length) return false;
	
	searchSelects.wrap("<div class='select__wrapper col'></div>");
})();