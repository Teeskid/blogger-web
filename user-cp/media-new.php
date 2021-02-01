<?php
/**
 * Project: Blog Management System With Sevida-Like UI
 * Developed By: Ahmad Tukur Jikamshi
 *
 * @facebook: amaedyteeskid
 * @twitter: amaedyteeskid
 * @instagram: amaedyteeskid
 * @whatsapp: +2348145737179
 */
require( __DIR__ . '/Load.php' );

require( ABSPATH . BASE_UTIL . '/lib-apkparser.php' );
require( ABSPATH . BASE_UTIL . '/HtmlUtil.php' );
require( ABSPATH . USER_UTIL . '/media.php' );

noCacheHeaders();

$error = [];

if( isPostRequest() ) {
}
else {
	$files = [];
}
$error = implode( '<br>', $error );
initHtmlPage( 'Upload Media', 'media-upLoad.php' );
include_once( __DIR__ . '/header.php' );
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb my-3">
		<li class="breadcrumb-item"><a href="index.php">Home</a></li>
		<li class="breadcrumb-item"><a href="media.php">Media Library</a></li>
		<li class="breadcrumb-item active" aria-current="page">Upload</li>
	</ol>
</nav>
<h2>Upload</h2>
<div class="card bg-light text-dark">
	<div class="card-header">Select File(s)</div>
	<ul class="nav nav-tabs">
		<li role="presentation" class="active"><a role="tab" data-bs-toggle="tab" aria-controls="classic" href="#classic">Single</a></li>
		<li role="presentation"><a role="tab" data-bs-toggle="tab" aria-controls="ajaxon" href="#ajaxon">Multiple</a></li>
	</ul>
	<div class="card-body">
<?php
eAlert( $error, 'error' )
?>
		<div class="tab-content" style="max-width:450px;margin:0 auto">
			<div id="classic" role="tabpanel" class="tab-pane active">
				<form name="media" class="form" method="post" enctype="multipart/form-data">
					<div class="mb-3">
						<label for="files" class="form-label">Browse</label>
						<input id="files" class="form-control" type="file" name="files" />
					</div>
					<div class="mb-3">
						<button class="btn btn-primary btn-block" type="submit" name="submit">Upload</button>
					</div>
					<p class="alert alert-info">
						You are using the browser’s built-in file author. The Ajax author includes multiple file selection.
						<a href="#ajaxon">Switch to the multi-file author</a>.
					</p>
				</form>
			</div>
			<div id="ajaxon" role="tabpanel" class="tab-pane">
				<div class="progress">
					<div id="progress" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
				</div>
				<p id="message" class="alert hidden"></p>
				<div class="mb-3 text-center">
					<input type="file" class="hidden" id="media-ajax" name="files" accept="*/*" multiple="multiple" />
					<div class="mb-3">
						<button id="select-files" type="button" class="btn btn-primary btn-lg">Choose...</button/>
					</div>
				</div>
				<p class="alert alert-info">
					You are using the multi-file author. Problems? 
					<a href="#classic">Try the browser author instead</a>.
				</p>
			</div>
		</div>
	</div>
</div>
<p class="text-info">Maximum upload file size: 100 MB.</p><br><br><br>
<?php
function onPageJsCode() {
var progress, message;
$(document).ready(function(){
	progress = $("#progress"),
	message = $("#message");
	$("#select-files").click(function(){
		$("#media-ajax").trigger("click");
	});
	$("#media-ajax").change(function(e){
		var element = $(this),
			control = $("#select-files"),
			fileData = element.prop("files");
		if( fileData.length <= 0 )
			return false;
		var formData = new FormData();
		formData.append("async", "true");
		for(var index = 0; index < fileData.length; index++) {
			formData.append("files[]", fileData[index]);
		}
		delete fileData;
		$.ajax({
			xhr: function() {
				var xhr;
				try {
					if (window.XMLHttpRequest) {
						xhr = new XMLHttpRequest();
					} else {
						xhr = new ActiveXObject("MSXML2.XMLHTTP.3.0");
					}
					xhr.upload.onloadstart = function() {
						progress.addClass("progress-bar-striped active");
					};
					xhr.upload.onprogress = function(progress) {
						if(!progress.lengthComputable)
							return false;
						progress = Math.round((progress.loaded / progress.total) * 100);
						window.progress.attr("aria-valuenow", progress);
						progress += '%';
						window.progress.css("width", progress);
					};
					xhr.upload.onloadend = function() {
						progress.removeClass("active").attr("aria-valuenow", "100");
					};
					xhr.upload.onabort = function() {
						message.html("Upload failed.").removeClass("alert-success hidden").addClass("alert-danger");
						progress.removeClass("active").css("width", "0").attr("aria-valuenow", "0");
					};
					return xhr;
				} catch (error) {
					alert("Neither XHR or ActiveX are supported!");
					return false;
				} 
			},
			type: "post",
			url: "media-new.php",
			processData: false,
			contentType: false,
			cache: false,
			data: formData,
			dataType: "json",
			beforeSend: function() {
				control.attr("disabled", true).addClass("disabled");
				progress.css("width", "0").attr("aria-valuenow", "0");
				progress.parent().fadeIn();
			},
			success: function(response) {
				message.html(response.message).removeClass("alert-danger hidden").addClass("alert-success");
			},
			error: function(xhr) {
				console.log(xhr.responseText);
				message.html("An error occured !").removeClass("alert-success hidden").addClass("alert-danger");
			},
			complete: function() {
				control.attr("disabled", false).removeClass("disabled");
				document.forms.media.reset();
			}
		});
	});
});
EOS
);
include_once( __DIR__ . '/footer.php' );
