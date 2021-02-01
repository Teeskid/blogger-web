/**
 * Post Edit Form Controller
 * 
 * Contains javascript functions to help the post editor do it's job
 * 
 * @package Sevida
 * @subpackage Administration
 */
(function($) {
	var TagItem = function(label) {
		label.id = parseInt(label.id);
		label.title = document.createTextNode(label.title).nodeValue;
		return [
			'<div class="form-check">',
				'<input class="form-check-input" id="pt_' + label.id + '" type="checkbox" name="labels[]" value="' + label.id + '" checked />',
				'<label class="form-check-label" for="pt_' + label.id + '">' + label.title + '</label>',
			'</div>'
		]
		.join("\r\n");
	};
	var CatItem = function(label) {
		label.id = parseInt(label.id);
		label.title = document.createTextNode(label.title).nodeValue;
		return (
			'<option value="' + label.id + '">' + label.title + '</option>'
		);
	};
	var noEnterClick = function(event) {
		if(event.which == 13) {
			event.preventDefault();
			event.stopPropagation();
		}
	};
	window.TermButton = function() {
		var button = this,
			roType = button.dataset.rowtype,
			mInput = document.getElementById(this.dataset.target);
		mInput.addEventListener("keyup", noEnterClick);
		mInput.addEventListener("keydown", function(event) {
			if(event.key == 13) {
				event.preventDefault();
				event.stopPropagation();
				button.click();
			}
		});
		button.addEventListener("click", function(event){
			event.preventDefault();
			if( mInput.value.length < 3 )
				return false;
			var payLoad = { title: mInput.value, action: "create", rowType: roType };
			$.ajax({
				url: "../api/term-edit.php",
				data: payLoad,
				beforeSend: function(){
					button.disabled = true;
					button.classList.add("disabled");
					button.tabIndex = -1;
					mInput.disabled = true;
					mInput.tabIndex = -1;
				},
				success: function(response) {
					if(!response.success)
						return false;
					for(var index in response.message)
						if(roType === "tag") 
							document.getElementById("postLabels").innerHTML += TagItem(response.message[index]);
						else if(roType === "cat")
							document.getElementById("category").innerHTML += CatItem(response.message[index]);
					mInput.value = '';
				},
				complete: function() {
					button.disabled = false;
					button.classList.remove("disabled");
					button.tabIndex = 0;
					mInput.disabled = false;
					mInput.tabIndex = 0;
					delete payLoad;
				}
			});
		});
	};
	$.fn.postForm = function(){
		var postForm = this,
			gallery = postForm.siblings("div#mediaDialog");
		var onDialogDone = function(action, data){
			if(action == "thumbnail") {
				postForm.find("input#thumbnail").val(data.id);
				postForm.find("img#imageSrc").attr("src", data.thumbnail);
				postForm.find("#imageName").text(data.fileName);
			} else if(action == "attach") {
				var postContent = postForm.find("textarea#content");
				for(var media in data) {
					media = data[media];
					postContent.append("\n[media=" + media.id + ']' + media.title + "[/media]");
				}
				postContent.focus();
			}
		};
		var onDialogLoad = function(event) {
			event.preventDefault();
			var button = $(event.relatedTarget);
			gallery.empty().load("media-dialog.html", function() {
				$.getScript("js/jquery.media-dialog.js", function() {
					gallery.off("show.bs.modal", onDialogLoad);
					gallery.mediaDialog({ onDialogDone: onDialogDone });
					button.trigger("click");
					delete onDialogLoad;
				});
			});
			delete button;
		};
		gallery.on("show.bs.modal", onDialogLoad);
		postForm.find("input#title").change(function(event) {
			var postTitle = event.target.value;
				postName = postForm.find("input#permalink");
			if(postTitle.length == 0) {
				postName.val("");
				return;
			}
			$.ajax({
				url: "../api/make-name.php",
				data: { text: postTitle },
				success: function(response) {
					if(response.success)
						postName.val(response.text);
					else
						postName.val("");
				}
			});
		});
		window.TermButton.call(document.getElementById("addCatBtn"));
		window.TermButton.call(document.getElementById("addLabel"));
		window.TermLoader.call(document.getElementById("loadTags"));
		window.LockToggle.call(document.getElementById("postLock"));
		return postForm;
	};
	window.TermLoader = function() {
		this.addEventListener("click", function(event) {
			event.preventDefault();
			var postTags = document.getElementById("postLabels");
			$.ajax({
				url: "../api/term.php",
				data: { rowType: "tag", maximum: 5, page: 1 },
				beforeSend: function(xhr) {
					postTags.innerHTML = '';
					postTags.classList.add('d-none');
				},
				success: function(response) {
					if(!response.success)
						return false;
					for(var index in response.message) {
						postTags.innerHTML += TagItem(response.message[index]);
					}
				},
				complete: function() {
					postTags.classList.remove('d-none');
				}
			});
		});
	}
	window.LockToggle = function() {
		lockElem = document.getElementById("password"),
		this.addEventListener("change", function(){
			if(this.checked) {
				lockElem.parentElement.classList.remove("d-none");
			} else {
				lockElem.parentElement.classList.add("d-none");
			}
			lockElem.disabled = !this.checked;
		});
	};
})(jQuery);
