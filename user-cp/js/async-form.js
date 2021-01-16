(function(window, document) {
	var setFeedBack = function(isValid) {
		if(isValid) {
			this.classList.remove("is-invalid");
			this.classList.add("is-valid");
		} else {
			this.classList.remove("is-valid");
			this.classList.add("is-invalid");
		}
	};
	window.SeForm = function(options) {
		var theForm = this, shouldNotSubmit = false,
			mButton = theForm.querySelector("[type='submit']");
		this.addEventListener("submit", function(event) {
			event.preventDefault();
			event.stopPropagation();
			if(shouldNotSubmit)
				return false;
			var payLoad = $(theForm).serializeArray();
			$.ajax({
				url: options.url,
				data: payLoad,
				beforeSend: function() {
					// theForm.classList.remove("was-validated");
					mButton.disabled = true;
					mButton.classList.add("disabled");
					shouldNotSubmit = true;
				},
				context: theForm,
				success: function(response) {
					if(typeof response.feedBack == "object") {
						for(var index in response.feedBack) {
							var element = document.getElementById(index);
							if(!element)
								continue;
							setFeedBack.call(element, response.feedBack[index]);
						}
						delete index, element;
					}
					if(response.success) {
						if(typeof options.success == "function")
							options.success.call(theForm, response);
						if(typeof options.target == "string")
							window.location = options.target;
					} else {
						if(response.message.length === 0)
							return;
						response.message = response.message.join(", ");
						var element = theForm.querySelector("div.alert");
						if(element) {
							element.innerHTML = response.message;
							element.classList.remove("d-none");
						} else {
							alert(response.message);
						}
						delete element;
					}
					delete response;
				},
				complete: function(){
					shouldNotSubmit = false;
					mButton.disabled = false;
					mButton.classList.remove("disabled");
					delete mButton, payLoad;
				}
			});
		});
		if(theForm.noValidate) {
			theForm.querySelectorAll(".has-validation .form-control").forEach(function(element){
				element.addEventListener("focus", function() {
					this.classList.remove("is-valid");
					this.classList.remove("is-invalid");
				});
			});
		}
	};
})(window, document);
