(function($){
	$.fn.termEdit = function(){
		var form = this;
		var formHd = this.find(".panel-heading"),
			action = this.find("input#action"),
			formId = this.find("input#id"),
			formPr = this.find("select#master"),
			formTt = this.find("input#title"),
			formDc = this.find("textarea#about"),
			formSb = this.find("button#submit"),
			formCn = this.find("button#cancel");
		var onResponse = function(data){
			formHd.text('Editing "' + data.title + '":');
			action.val("modify");
			formId.val(data.id);
			formTt.val(data.title);
			formPr.find("[selected]").attr("selected", false);
			formPr.find("[disabled]").attr("disabled", false);
			formPr.find("[value='" + data.id + "']").attr("disabled", true);
			formPr.find("[value='" + data.master + "']").attr("selected", true);
			formPr.trigger("resize");
			formDc.text(data.about);
			formSb.text("Edit");
			formCn.removeClass("hide");
			formTt.focus();
		};
		var onCancel = function() {
			formHd.text("Create New");
			formId.val("0");
			action.val("create");
			formPr.find("[selected]").attr("selected", false);
			formPr.find("[disabled]").attr("disabled", false);
			formSb.text("Create");
			formCn.addClass("hide");
		};
		formCn.click(function(e){
			e.preventDefault();
			onCancel();
		});
		form.on("async.loaded", function(e, id){
			$.ajax({
				url: "../api/term.php",
				data: {id: id},
				success: onResponse
			})
		});
		this.asyncForm({url: "../api/term-edit.php", target: ""});
		this.data("instance", this);
		return this;
	};
})(jQuery);