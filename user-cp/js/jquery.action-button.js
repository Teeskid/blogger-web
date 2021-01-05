(function($){
	$.fn.actionBtn = function(options){
		options = $.extend({modify: false, unlink: false}, options);
		this.click(function(event){
			event.preventDefault();
			var action = this.dataset.action,
				itemId = this.closest("[data-id]").dataset.id;
			if(!action || !itemId)
				return false;
			if(action == "unlink") {
				if(confirm("Do you want to delete this item ? It can not be recovered.")){
					$.ajax({
						url: options.unlink,
						data: {action: action, postId: itemId},
						success: function(response){
							if(response.success)
								window.location = "";
						}
					});
				}
			} else if(action == "modify") {
				if(typeof options.modify == "function")
					options.modify(itemId);
				else if(typeof options.modify == "string")
					window.location = options.modify.replace("[id]", itemId);
			}
		});
		return this;
	};
})(jQuery);