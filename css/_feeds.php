<?php
/**
 * Project: Blog Management System With Sevida-Like UI
 * Developed By: Ahmad Tukur Jikamshi
 *
 * @facebook: amaedyteeskid
 * @twitter: amaedyteeskid
 * @instagram: amaedyteeskid.feed-image
 * @whatsapp: +2348145737179
 */
?>
.feed article {
	position: relative;
	z-index: 1;
}
.feed article ul {
	list-style: none;
}
.feed-image {
	background-color: <?=$bgLight?>;
	border: 1px solid <?=$strokeAccent?>;
	border-radius: 5px;
	padding: 5px;
	position: relative;
}
.feed-image::before,
.feed-image::after {
	-wekit-transform: rotate(-3deg);
	-moz-transform: rotate(-3deg);
	-ms-transform: rotate(-3deg);
	-o-transform: rotate(-3deg);
	transform: rotate(-3deg);
	-webkit-box-shadow: 0 15px 10px rgba(0, 0, 0, 0.7);
	-moz-box-shadow: 0 15px 10px rgba(0, 0, 0, 0.7);
	box-shadow: 0 15px 10px rgba(0, 0, 0, 0.7);
	bottom: 15px;
	content: "";
	height: 20%;
	left: 5px;
	max-width: 300px;
	position: absolute;
	width: 50%;
	z-index: -1;
}
.feed-image::after {
	left: auto;
	right: 5px;
	-wekit-transform: rotate(3deg);
	-moz-transform: rotate(3deg);
	-ms-transform: rotate(3deg);
	-o-transform: rotate(3deg);
	transform: rotate(3deg);
}
.feed-image img {
	display: block;
	height: auto;
	width: 100%;
}
.feed-date {
	background-color: <?=$colorDark?>;
	font-family: "Open Sans", serif, sans-serif;
	color: <?=$textLight?>;
	text-align: center;
	width: auto;
}
.feed-date span {
	display: block;
	padding: 0 2px;
	vertical-align: middle;
}
.feed-date span.dd {
	font-weight: 900;
	font-size: 16px;
}
.feed-date span.mm,
.feed-date span.yy {
	font-size: 11px;
	font-weight: 400;
	margin-top: 2px;
}
.feed-date span.yy {
	margin-bottom: 2px;
}
.feed-image .feed-date {
	display: block;
	top: 0;
	left: 8px;
	right: auto;
	bottom: auto;
	position: absolute;
	text-align: center;
	transition: background-color 300ms ease-in;
}
.feed-image:hover .feed-date {
	background-color: <?=$bgDark?>;
	transition-timing-function: ease-out;
}
.feed-share {
	background-color: <?=$colorLight?>;
	clear: both;
	color: <?=$textLight?>;
	width: auto;
	text-align: center;
}
.feed-share a {
	display: inline-block;
	float: left;
}
.feed-share a:hover {
	background-color: <?=$bgLight?>;
}
.feed-image .feed-share {
	font-size: 18px;
	top: auto;
	left: auto;
	right: 8px;
	bottom: 60px;
	opacity: 0;
	position: absolute;
	transition-property: bottom,opacity;
	transition-duration: 200ms;
	transition-timing-function: ease-in;
}
.feed-share a {
	width: 18px;
}
.feed-image:hover .feed-share {
	bottom: 8px;
	opacity: 1;
	transition-timing-function: ease-out;
}
.feed h3, .feed p {
	display: block;
	margin: 0;
}
.feed h3 {
	line-height: 1.2;
}
.feed h3 a:hover {
	color: <?=$colorLight?>;
}
.feed .clear {
	display: none;
}
.feed-list {
	vertical-align: top;
}
.feed-list.feed-sm {
	border: 1px solid <?=$strokeAccent?>;
	padding: 0 5px;
}
.feed-list article {
	margin-bottom: 20px;
	padding: 0;
	vertical-align: top;
}
.feed-list.feed-sm article {
	margin-top: 10px;
}
.feed-list article::after {
	display: table;
	content: "";
	clear: both;
}
.feed-list .feed-image {
	display: inline-block;
	float: left;
	margin-right: 5px;
	min-width: 120px;
	max-width: 300px;
	width: 40%;
}
.feed-list.feed-sm .feed-image {
	min-width: 80px !important;
	width: 80px !important;
}
.feed-list .feed-image img {
	transition-property: transform, left, top, right, bottom;
	transition-duration: 300ms;
	transition-timing-function: ease-in;
}
.feed-list .feed-image:hover img {
	filter: opacity(20);
	transition-timing-function: ease-out;
}
.feed-list h3 {
	font-size: 16px;
	font-weight: 400;
	color: <?=$textDark?>;
}
.feed-list.feed-sm h3 {
	font-size: 13px;
}
.feed-list h3:hover {
	color: <?=$textAccent?>;
}
.feed-list p {
}
.feed-list .excerpt {
	font-size: 13px;
	color: <?=$textAccent?>;
	line-height: 1.4em;
	text-align: justify;
}
.feed-list span.fas {
	color: <?=$textDark?>;
}
.feed-list .btn {
	float: right;
}
.feed-grid {
	width: 100%;
	vertical-align: top;
}
.feed-grid article {
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
	margin: 0;
	padding: 0 5px 5px 5px;
	position: relative;
	z-index: 1;
}
.feed-grid h3 {
	color: <?=$textAccent?>;
	font-family: "Open Sans", serif, sans-serif;
	font-size: 14px;
	font-weight: 600;
	margin-top: 5px;
	word-wrap: break-word;
}
.feed-grid h3:hover {
	color: <?=$textAccent?>;
}
.feed-grid p {
	display: none;
}

.feed-slide {
	border: 1px solid <?=$strokeAccent?>;
	margin: auto 0;
}
.feed-slide article {
	padding: 10px 0;
	position: relative;
	vertical-align: top;
	z-index: 1;
}
.feed-slide .feed-image {
	position: relative;
	margin: 5px 0;
}
.feed-slide h3 {
	display: block;
	color: <?=$textDark?>;
	font-family: "Open Sans", serif, sans-serif;
	font-size: 14px;
	font-weight: 600;
	line-height: 16px;
	margin: 0;
	padding: 0;
	word-wrap: break-word;
}
.feed-slide .owl-stage-outer {
}
.feed-slide .owl-nav {
	-webkit-tap-highlight-color: transparent;
}
.feed-slide .owl-nav [class*='owl-'] {
	display: block;
    border-radius:3px;
    cursor:pointer;
    color:<?=$textLight?>;
    font-size:20px;
	font-weight: bold;
    margin: 5px;
    padding: 4px 7px;
	background-color: <?=$colorDark?>;
}
.feed-slide .owl-nav button.owl-prev,
.feed-slide .owl-nav button.owl-next {
	background-color: <?=$colorDark?>;
	color: <?=$textLight?>;
	padding: 5px;
	max-height: 40px;
	line-height: 40px;
	width: 20px;
	top: -555px;
	bottom: -555px;
	margin: auto;
	position: absolute;
}
.feed-slide .owl-nav button.owl-prev {
	left: 0;
	right: auto;
}
.feed-slide .owl-nav button.owl-next {
	left: auto;
	right: 0;
}
.feed-slide .owl-nav [class*='owl-']:hover {
	background-color: <?=$colorLight?>;
	color: <?=$textLight?>;
	text-decoration: none;
}
.feed-slide .owl-nav .disabled {
	opacity: 0.5;
	cursor: default;
}
.feed-slide .owl-nav.disabled + .owl-dots {
	max-height: 50px;
	margin-top: 10px;
}
.feed-slide .owl-dots {
	text-align: center;
	-webkit-tap-highlight-color: transparent;
}
.feed-slide .owl-dots .owl-dot {
	display: inline-block;
	display: inline;
}
.feed-slide .owl-dots .owl-dot span {
	display: block;
	background-color: <?=$textAccent?>;
	border-radius: 30px;
	width: 10px;
	height: 10px;
	margin: 5px 7px;
	-webkit-backface-visibility: visible;
	transition: opacity 200ms ease;
}
.feed-slide .owl-dots .owl-dot.active span, .owl-theme .owl-dots .owl-dot:hover span {
	background-color: <?=$colorDark?>;
}

/* COMPAT */
.feed-compat-wrapper {

}
.feed-compat {
	border: 1px solid  <?=$strokeAccent?>;
	margin: 0;
}
.feed-compat article {
}
.feed-compat h3 {
	font-family: "Asap Condensed";
	color: <?=$textDark?>;
}
.feed-compat article.legend {
	padding: 0 10px;
	position: relative;
	z-index: 1;
}
.feed-compat .legend .feed-image {
	max-width: 320px;
	margin: 10px auto;
	height: 100px;
}
.feed-compat .legend h3 {
	display: block;
	padding: 0;
	margin: 0;
	text-align: center;
}
.feed-compat .legend h3 {
	font-family: "Asap Condensed";
	font-size: 18px;
	font-weight: 500;
	word-wrap: break-word;
	color: <?=$textDark?>;
}
/* COMPAT X-LEGEND */
.feed-compat article.x-legend {
	border-top: 1px solid  <?=$strokeAccent?>;
	background-color: <?=$bgAccent?>;
	margin: 0;
	padding: 5px;
}
.feed-compat .x-legend:after {
	display: table;
	content: '';
	clear: both;
}
.feed-compat .x-legend .feed-image {
	float: left;
	margin: 0 5px 0 0;
	padding: 2px;
	width: 80px;
	min-height: 80px;
	height: 80px;
	max-height: 80px;
}
.feed-compat .x-legend .meta {
	margin: 0 5px;
}
.feed-compat .x-legend h3 {
	margin: 0;
	padding: 0;
}
@media only screen and (min-width:320px) {
	.feed-list .feed-image {
		width: 120px;
	}
	.feed-grid {
		display: table;
		clear: both;
	}
	.feed-grid article {
		display: table-cell;
		float: left;
		margin: 0;
		min-width: 50%;
		width: 50%;
		max-width: 50%;
	}
	.feed-grid .clear:nth-of-type(2n) {
		display: table-column;
		clear: both;
	}
	.feed-grid p {
		text-align: left;
	}
	.feed-grid h3 {
		font-size: 13px;
	}
	.feed-compat .legend .feed-image {
		height: 120px;
	}
}
@media only screen and (min-width:481px) {
	.feed-image .feed-share {
		right: 12px;
	}
	.feed-image > a:hover .feed-share {
		bottom: 12px;
		top: auto;
	}
	.feed-list .feed-image {
		width: 150px;
	}
	.feed-list h3  {
		font-size: 18px;
	}
	.feed-list .excerpt {
		font-size: 13px;
	}
	.feed-grid h3 {
		font-size: 14px;
	}
	.feed-compat .legend .feed-image {
		height: 140px;
	}
}
@media only screen and (min-width:768px) {
	.feed-date .dd {
		font-size: 22px;
	}
	.feed-image .feed-date {
		left: 10px;
	}
	.feed-list h3 {
		font-size: 18px;
	}
	.feed-list .feed-image {
		width: 180px;
	}
	.feed-compat {
		border: 1px solid  <?=$strokeAccent?>;
		margin-bottom: 10px;
	}
}
@media only screen and (min-width:980px) {
	.feed-grid article {
		min-width: 33.333333333%;
		width: 33.333333333%;
		max-width: 33.333333333%;
	}
	.feed-list .feed-image {
		width: 220px;
		max-width: 33.333333%;
	}
	.feed-list h3 {
		font-size: 20px;
	}
	.feed-list .excerpt {
		font-size: 14px;
	}
	.feed-grid h3 {
		font-size: 14px;
	}
	.feed-grid .clear:nth-of-type(2n) {
		display: none;
	}
	.feed-grid .clear:nth-of-type(3n) {
		display: table-column;
		clear: both;
	}
}
