(function(window, document) {
	window.AsyncForm = function(formElem, options) {
		var instance = {
			formElem: formElem,
			xhRequest: buildHttpRequest(),
			isSending: false,
			options: options || {},
			getButton: function() {
				return this.formElem.querySelector("#submit");
			},
			getBtnIcon: function() {
				return this.formElem.querySelector("#submit span.fas");
			}
		};
		instance.formElem.addEventListener("submit", function(event) {
			event.preventDefault();
			event.stopPropagation();
			if(instance.isSending)
				return;
			var payLoad = JSON.stringify(serializeForm(this)); 
			instance.xhRequest.onreadystatechange = function() {
				if(this.readyState === 1) {
					instance.isSending = true;
					onRequest.call(instance);
				}
				if(this.readyState === 4) {
					var response;
					try {
						response = JSON.parse(this.responseText);
						instance.xhRequest.responseJson = response;
					} catch(e) {
						delete response, instance.xhRequest.responseJson;
					}
					setTimeout(function(instance){
						if(instance.xhRequest.status === 200)
							onSuccess.call(instance);
						else
							onFailure.call(instance);
						onComplete.call(instance);
						instance.isSending = false;
					}, 1500, instance);
				}
			};
			instance.xhRequest.open("POST", instance.options.url);
			if(window.localStorage && localStorage.authToken)
				instance.xhRequest.setRequestHeader("Authorization", "Token " + localStorage.authToken);
			instance.xhRequest.send(payLoad);
		});
		var setFeedBack = function(isValid) {
			if(isValid) {
				this.classList.remove("is-invalid");
				this.classList.add("is-valid");
			} else {
				this.classList.remove("is-valid");
				this.classList.add("is-invalid");
			}
		};
		var onRequest = function() {
			var mButton = this.getButton(),
				btnIcon = this.getBtnIcon();
			btnIcon.classList.remove("fa-check","d-hide");
			btnIcon.classList.add("fa-sync","fa-spin");
			mButton.disabled = true;
			mButton.classList.add("disabled");
		};
		var onSuccess = function() {
			var response = this.xhRequest.responseJson;
			if(!response)
				return false;
			if((typeof response.feedBack) === "object") {
				for(var index in response.feedBack) {
					var element = document.getElementById(index);
					if(!element)
						continue;
					setFeedBack.call(element, response.feedBack[index]);
				}
				delete index, element;
			}
			if((typeof response.success) === "boolean" ) {
				if(response.success) {
					if(typeof this.options.success === "function") {
						if(true === this.options.success.call(this, response))
							return false;
					}
				}
				if((typeof response.message) === "Array")
					response.message = response.message.join(", ");
				else if(typeof response.message === "undefined")
					response.message = "Unknown error";
				var element = this.formElem.querySelector(".alert");
				if(element) {
					element.className = response.success ? 'alert alert-success' : 'alert alert-danger';
					element.innerHTML = response.message;
				} else {
					alert(response.message);
				}
				delete  element;
			} else {
				onFailure.call(this);
			}
			delete response;
		};
		var onFailure = function() {
			console.log(this.xhRequest.responseText);
		};
		var onComplete = function() {
			var mButton = this.getButton(),
				btnIcon = this.getBtnIcon();
			btnIcon.classList.remove("fa-sync","fa-spin");
			btnIcon.classList.add("fa-check");
			mButton.disabled = false;
			mButton.classList.remove("disabled");
		};
		return instance;
	};
	window.AsyncForm.get = function(formElem) {
		return formElem.asyncForm;
	};
})(window, document);
