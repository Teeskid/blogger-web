/**
 * Project: Blog Management System With Sevida-Like UI
 * Developed By: Ahmad Tukur Jikamshi
 *
 * @facebook: amaedyteeskid
 * @twitter: amaedyteeskid
 * @instagram: amaedyteeskid
 * @whatsapp: +2348145737179
 */
(function($){
	var addTermToList = function(term) {
		term.title = term.title.toUpperCase();
		if(term.subject == 'cat') {
			var select = $("select#category");
			if(select.children("option[value=" + term.id + "]").length)
				return;
			select.append($("<option>").val(term.id).text(term.title).attr("selected", true));
			delete select;
		} else if(term.subject == 'tag') {
			var postTags = $("div#postTags");
			if(postTags.find("input[value=" + term.id + "]").length)
				return;
			var checkbox = $('<p class="checkbox">');
			checkbox.append($("<input>").attr("type", "checkbox").attr("name", "tags[]").val(term.id).prop("checked", true));
			checkbox.append($("<span>").text(term.title));
			checkbox.wrapInner($("<label>"));
			postTags.append(checkbox);
			delete checkbox, postTags;
		}
	};
	var noEnterClick = function(event) {
		if(event.which == 13) {
			event.preventDefault();
			event.stopPropagation();
		}
	};
	$.fn.termButton = function() {
		return this.each(function(){
			var elemBtn = $(this),
				elemTxt = $(elemBtn.attr("href")),
				elemSbj = elemBtn.data("subject");
			elemTxt.on({keyup: noEnterClick, keydown: function(event){
				if(event.which == 13) {
					event.preventDefault();
					event.stopPropagation();
					elemBtn.trigger("click");
				}
			}});
			elemBtn.click(function(event){
				event.preventDefault();
				var payLoad = elemTxt.val();
				if(payLoad.length < 3 )
					return false;
				var payLoad = { action: "create", title: payLoad, subject: elemSbj };
				$.ajax({
					url: "../api/term-edit.php",
					data: payLoad,
					beforeSend: function(){
						elemBtn.attr("disabled", true).addClass("disabled");
						elemTxt.blur().attr("disabled", true);
					},
					success: function(response) {
						if(response.success){
							payLoad.id = response.id;
							addTermToList(payLoad);
							delete payLoad;
						}
						if(response.message)
							alert(response.message);
					},
					complete: function(response){
						elemTxt.val("").attr("disabled", false);
						elemBtn.attr("disabled", false).removeClass("disabled");
					}
				});
			});
		});
	};
	$.fn.lockToggle = function(){
		var isLocked = this.prop("checked"),
			locker = $("#password"),
			lockPa = locker.closest("div.form-group");
		this.on("change", function(){
			locker.attr("disabled", !isLocked);
			lockPa.toggleClass("hide", !isLocked);
			locker.val(isLocked?locker.val():"");
		}).trigger("change");
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
		postForm.find("a[data-subject]").termButton();
		postForm.find("a#loadUsedTags").click(function(event) {
			event.preventDefault();
			var postTags = postForm.find("div#postTags");
			$.ajax({
				url: "../api/term.php",
				data: { subject: "tag", maximum: 5, page: 1 },
				beforeSend: function(xhr) {
					postTags.slideUp("fast", function(){
						postTags.html('');
						postTags.slideDown();
					});
				},
				success: function(response){
					setTimeout(function(){
						postTags.slideUp(function(){
							postTags.empty();
							for(var index = 0; index < response.length; index++) {
								addTermToList(response[index]);
							}
							postTags.slideDown();
						});
					}, 2000);
				}
			});
		});
		postForm.find("input#lockToggle").lockToggle();
		
		return postForm;
	};
})(jQuery);
