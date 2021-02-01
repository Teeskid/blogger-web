(function($){
	$.fn.mediaDialog = function(options){
		var mediaDialog = this,
			progressDiv = mediaDialog.find("div#upload div.progress"),
			progressBar = progressDiv.children().get(0),
			inputButton = mediaDialog.find("div#upload button.btn-lg"),
			submitBtn = mediaDialog.find("button.modal-submit"),
			inputFile = mediaDialog.find("div#upload input[type=file]"),
			responseTxt = mediaDialog.find("div#upload div.messagebar"),
			libraryPa = mediaDialog.find("div#library"),
			isLoading = false;
		options = $.extend({}, options);
		var onDialogShow = function(event) {
			var button = event.relatedTarget;
			options.action = button.dataset.action;
			options.choice = button.dataset.choice;
			options.format = button.dataset.format;
			inputFile.attr("accept", options.format == 'images' ? 'image/jpeg,image/png' : "*/*");
			inputFile.attr("multiple", options.choice == "multiple");
		};
		var onDialogHide = function(event) {
			responseTxt.empty();
			libraryPa.data("library", false);
		};
		var loadLibrary = function() {
			if(isLoading)
				return;
			$.ajax({
				type: "get",
				url: "../api/media.php",
				context: mediaDialog,
				delay: 1000,
				data: { format: options.format, page: 1, limit: 20 },
				beforeSend: function() {
					isLoading = true;
				},
				success: function(response) {
					if(!response.success) {
						console.error(response.message);
						return false;
					}
					var elemType = options.choice == "multiple" ? "checkbox" : "radio",
						media;
					for(var index in response.data) {
						media = response.data[index];
						libraryPa.append('<div class="col-xs-6 col-sm-4 col-md-3"><label><input type="'+elemType+'" name="index" data-index="'+index+'" /><img src="'+media.thumbnail+'" /><span class="fas fa-check-circle fa-3x" aria-hidden="true"></span></label></div>');
					}
					libraryPa.data("library", response.data);
					mediaDialog.modal("handleUpdate");
				},
				complete: function(xhr){
					isLoading = false;
				}
			});
		};
		var onTabChanged = function(event){
			var controls = $(event.target).attr("aria-controls");
			if(controls == "library") {
				submitBtn.removeClass("hide");
				loadLibrary();
			} else {
				submitBtn.addClass("hide");
			} 
		};
		var uploadFiles = function(files) {
			var formData = new FormData();
			formData.append("action", "upload");
			$.each(files, function(i, file){
				formData.append("files[]", file);
			});
			$.ajax({
				url: "../api/media-edit.php",
				processData: false,
				contentType: false,
				data: formData,
				context: mediaDialog,
				timeout: 60000,
				xhr: function() {
					var mXHR;
					if (window.XMLHttpRequest) {
						mXHR =  new XMLHttpRequest();
					} else {
						try {
							mXHR = new ActiveXObject("MSXML2.XMLHTTP.3.0");
						} catch (error) {
							console.error("Neither XHR or ActiveX are supported!");
							return false;
						}
					}
					mXHR.upload.onloadstart = function() {
						progressBar.classList.remove("active");
					};
					mXHR.upload.onprogress = function(progressInt) {
						if(progressInt.lengthComputable) {
							progressInt = Math.round((progressInt.loaded / progressInt.total) * 100);
							progressBar.ariaValueNow = progressInt;
							progressInt += '%';
							progressBar.style.width = progressInt;
							progressBar.innerHTML = progressInt;
						}
					};
					mXHR.upload.onloadend = function() {
						progressBar.classList.add("active");
					};
					mXHR.upload.onabort = function() {
						responseTxt.text("Upload canceled.");
					};
					return mXHR;
				},
				beforeSend: function() {
					progressDiv.removeClass("hide");
					responseTxt.removeClass("alert-success", "alert-danger").addClass("hide");
					inputButton.attr("disabled", true);
				},
				success: function(response) {
					if(response.success) {
						responseTxt.removeClass("alert-danger").addClass("alert-success");
					} else {
						responseTxt.removeClass("alert-success").addClass("alert-danger");
					}
					responseTxt.text(response.message);
				},
				progress: function(x) {
					console.log(x);
				},
				complete: function() {
					inputFile.prop("files", null).val(null);
					setTimeout(function(){
						progressDiv.addClass("hide");
						progressBar.style.width = "0%";
					}, 1000);
					progressBar.classList.remove("active");
					responseTxt.removeClass("hide");
					inputButton.attr("disabled", false);
				}
			});
			delete files, formData;
		};
		var onFileChosen = function(event) {
			event.preventDefault();
			var files = inputFile.prop("files");
			if(files.length == 0)
				return;
			uploadFiles(files);
		};
		var onChooseFile = function() {
			inputFile.trigger("click");
		};
		var onSubmitModal = function() {
			var library = libraryPa.data("library");
				checked = [];
			libraryPa.find("input:checked").each(function(){
				checked.push(library[parseInt(this.dataset.index)]);
				this.checked = false;
			});
			if(checked.length == 0)
				return false;
			if(options.choice == "single")
				checked = checked[0];
			options.onDialogDone.call(mediaDialog, options.action, checked);
			mediaDialog.modal("hide");
			delete library, checked;
		};
		mediaDialog.on("show.bs.modal", onDialogShow);
		mediaDialog.on("hide.bs.modal", onDialogHide);
		mediaDialog.find("a[data-toggle='tab']").on("shown.bs.tab", onTabChanged);
		inputButton.on("click", onChooseFile);
		inputFile.on("change", onFileChosen);
		submitBtn.on("click", onSubmitModal);
	};
})(jQuery);