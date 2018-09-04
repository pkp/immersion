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