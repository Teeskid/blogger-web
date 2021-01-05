(function($){
	$.fn.noFeedBack = function(){
		this.removeClass("has-error", "has-success")
			.children("span.form-control-feedback")
			.removeClass("fa-check", "fa-times")
			.addClass("sr-only");
		return this;
	};
	$.fn.setFeedBack = function(hasSuccess){
		this.closest("div.has-feedback")
			.removeClass(hasSuccess ? "has-error" : "has-success")
			.addClass(hasSuccess ? "has-success" : "has-error")
				.children("span.form-control-feedback")
				.removeClass(hasSuccess ? "fa-times" : "fa-check")
				.addClass(hasSuccess ? "fa-check" : "fa-times")
				.removeClass("sr-only");
		return this;
	};
	$.fn.asyncForm = function(options){
		var theForm = this, shouldNotSubmit = false;
		options = $.extend({}, options);
		this.on("submit", function(event){
			event.preventDefault();
			event.stopPropagation();
			if(shouldNotSubmit)
				return false;
			var button = $(event.originalEvent.submitter),
				payLoad = theForm.serializeArray();
			$.ajax({
				url: options.url,
				data: payLoad,
				beforeSend: function(){
					button.attr("disabled", true).addClass("disabled");
					shouldNotSubmit = true;
				},
				context: theForm,
				success: function(response) {
					if(typeof response.uiValid == "object") {
						for(var element in response.uiValid)
							$("input#" + element).setFeedBack(response.uiValid[element]);
						delete element;
					}
					if(typeof response.message == "string") {
						var element = theForm.find("div.alert").eq(0);
						if(element.length) {
							element.text(response.message);
							element.prepend('<span class="fas fa-' + (response.success ? 'check' : 'exclamation-triangle') + '" aria-hidden="true"></span>  ');
							element.append('<span class="close">&times;</span>');
							element.removeClass(response.success ? "alert-error" : "alert-success");
							element.addClass(response.success ? "alert-success" : "alert-danger");
							element.removeClass("hide");
						} else {
							alert(response.message);
						}
					}
					if(response.success) {
						if(typeof options.success == "function")
							options.success.call(theForm, response);
						if(typeof options.target == "string")
							window.location = options.target;
					}
				},
				complete: function(){
					shouldNotSubmit = false;
					button.attr("disabled", false).removeClass("disabled");
					delete button;
				}
			});
		});
		this.find("div.has-feedback .form-control").on("focus", function(){
			$(this).closest("div.has-feedback").noFeedBack();
		});
		return this;
	};
})(jQuery);