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
?>
.post {
	border: 1px solid <?=$strokeAccent?>;
	margin: 10px 0;
}
.post-head {
	display: table;
	border-bottom: 1px solid <?=$strokeAccent?>;
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
	min-height: 50px;
	padding: 0 10px;
	width: 100%;
}
.post-head .feed-date {
	display: table-cell;
	padding: 0 2px;
	text-align: center;
	vertical-align: top;
}
.post-head .feed-date span.dd {
	margin-top: 10px;
}
.post-head .feed-date span.yy {
	margin-bottom: 5px;
}
.post-head h2 {
	display: table-cell;
	margin: 0;
	padding: 0;
	padding-left: 5px;
	width: auto;
	vertical-align: middle;
}
div#postBar {
	display: block;
	text-align: right;
}
.post-content {
	line-height: 1.4em;
}
.post-content p {
	margin: 5px 10px;
	padding: 0;
	text-align: justify;
}
.post-content code,
.comm-content code {
	font-family: consolas;
}
.post-content .image {
	display: block;
	margin: 10px auto;
	min-width: 200px;
	width: 90%;
	max-width: 400px;
	text-align: center;
}
.post-content .image img {
	border: 1px solid <?=$strokeAccent?>;
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
	width: 100%;
	padding: 5px;
}
.post-content .media {
	display: block;
	border: 1px solid <?=$strokeAccent?>;
	margin: 10px;
	vertical-align: top;
}
.post-content .media:before {
	display: table;
	content: 'Attachment';
	clear: both;
	background-color: <?=$bgAccent?>;
	border-bottom: 1px solid <?=$strokeAccent?>;
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
	padding: 5px;
	width: 100%;
}
.post-content .media:after {
	display: table;
	clear: both;
	content: '';
}
.post-content .media > .fas,
.post-content .media > img {
	display: inline-block;
	float: left;
	margin: 5px;
}
.post-content .media > .fas {
	font-size: 48px;
}
.post-content .media p {
	display: inline-block;
	float: left;
	padding: 0;
	margin: 0;
}
.post-content .media h4,
.post-content .media h5 {
	font-size: 16px;
	padding: 0;
	margin: 0;
}
.post-foot {
	background-color: <?=$bgAccent?>;
	border-top: 1px solid <?=$strokeAccent?>;
	padding: 10px;
}
.comments-outer {
	border: 1px solid <?=$strokeAccent?>;
	margin: 10px 0;
}
.comments .comment {
	display: block;
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
	width: 100%;
	padding: 10px;
	vertical-align: top;
}
.comments .comment .image {
	display: table-cell;
	border-radius: 50%;
	float: left;
	height: 50px;
	width: 50px;
	margin-right: 10px;
}
.comments .comment .image.holder {
	background-image: url(../images/picture-holder.png);
	background-repeat: no-repeat;
	background-position: center;
	background-size: 50px auto;
}
.comments .comment .meta {
	line-height: 1.6em;
	margin: 0;
}
.comments .comment h2 {
	font-weight: bold;
}
.comments .comment .replies {
	border-left: 1px solid <?=$strokeAccent?>;
	margin-left: 20px;
}
.comment-form {
	border: 1px solid <?=$strokeAccent?>;
	margin: 10px;
}
.comment-form input#comment-parent[value="0"] + div {
	display: none;
}
.comment-form .input-field {
	padding: 10px;
}
.comment-form > .input-field.select {
	display: table;
	height: 32px;
	width: auto;
}
.comment-form > .input-field.select::before {
	background-image: url(../images/select-profile.png);
	background-repeat: no-repeat;
	background-position: center;
	background-size: cover;
	content: "";
	width: 32px;
}
.comment-form > .input-field.select::before,
.comment-form > .input-field.select > label,
.comment-form > .input-field.select > select {
	display: table-cell;
	vertical-align: middle;
	height: 100%;
}
.comment-form > .input-field.select > select {
	background-color: <?=$bgAccent?>;
	border: 1px solid <?=$strokeAccent?>;
	border-radius: 4px;
	margin-left: 5px;
}
.comment-form > .input-field.submit {
	background-color: <?=$bgAccent?>;
	border-top: 1px solid <?=$strokeAccent?>;
}
.comment-form p {
	display: block;
	margin: 5px 10px;
	text-align: center;
}
.comment-form .input-field label {
	font-weight: bold;
}
.comment-form .input-field input,
.comment-form .input-field textarea {
	display: block;
	border: 1px solid <?=$strokeAccent?>;
	border-radius: 4px;
	-webkit-box-shadow: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
	padding-left: 5px;
	padding-right: 5px;
	width: 100%;
}
.comment-form .input-field ::placeholder {

}
.comment-form .input-field label + input,
.comment-form .input-field label + textarea {
	margin-top: 8px;
}
.comment-form .input-field input {
	height: 32px;
}
.comment-form .input-field textarea {
	height: 100px;
	width: 100% !important;
	padding-bottom: 5px;
	padding-top: 5px;
	max-width: 100% !important;
}
@media only screen and (min-width: 481px) {
	.post-content .image {
		max-width: 80%;
	}
}
@media only screen and (min-width:768px) {
	.post-content .image {
		max-width: 60%;
	}
}
@media only screen and (min-width:980px) {
	.post-content .image {
		max-width: 50%;
	}
}