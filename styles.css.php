<?
$BASE = ".";
include_once("$BASE/_data/styles.inc");

header("Content-Type: text/css");
?>

body {
	color: <? echo $styles['color']; ?>;
	background: <? echo $styles['bgcolor']; ?>;
	margin: 10px;
	font-family: Arial, Helvetica, sans-serif;
}

h2 {
	font-size: 1.2em;
	font-weight: bold;
	text-align: left;
}

h3 {
	font-size: 1.1em;
	font-weight: bold;
	text-align: left;
}

li {
	padding-bottom: 5px;	
}

hr {
	border: 0px;
	border-top: 1px solid <? echo $styles['hr_color']; ?>;
}

a:link {
	color: <? echo $styles['a_link']; ?>;
}

a:visited {
	color: <? echo $styles['a_visited']; ?>;
}

a:active, a:hover {
	color: <? echo $styles['a_hover']; ?>;
}

#event-list tr {
	background: #ffffff;
}

#event-list td {
	padding: 5px;
	border-top: solid 1px <? echo $styles['bgcolor']; ?>;
	border-bottom: solid 1px <? echo $styles['bgcolor']; ?>;
}

#event-list tr.selected, #event tr.selected {
	background:#fafafa url("<? echo $dojo_path; ?>dijit/themes/tundra/images/titleBarBg.gif") repeat-x bottom left;
}

#event-list tr.selected td, #event tr.selected td {
	border-top: 1px solid #bfbfbf;
	border-bottom: 1px solid #bfbfbf;
}

#event-list h2 {
	line-height: 0.5ex;
}

#event-list .updated {
	color: #366dba;
	font-weight: bold;
}

#event-total h2 {
	line-height: 0.5ex;
}

#event-list tr.selected a:visited, #event-list tr.selected a:link {
	color: #366dba;
}

#myevents tr {
	background: #ffffff;
}

#myevents td {
	padding: 5px;
	border-top: solid 1px <? echo $styles['bgcolor']; ?>;
	border-bottom: solid 1px <? echo $styles['bgcolor']; ?>;
}

#myevents tr.selected, #event tr.selected {
	background:#fafafa url("<? echo $dojo_path; ?>dijit/themes/tundra/images/titleBarBg.gif") repeat-x bottom left;
}

#myevents tr.selected td, #event tr.selected td {
	border-top: 1px solid #bfbfbf;
	border-bottom: 1px solid #bfbfbf;
}

#myevents h2 {
	line-height: 0.5ex;
}

#myevents .updated {
	color: #366dba;
	font-weight: bold;
}

#event-list tr.selected a:visited, #event-list tr.selected a:link {
	color: #366dba;
}

#private-details-box {
	width: 100%;
	height: 150px;
}

tr.deleted {
	color: #CCCCCC;
	text-decoration: line-through;
}

tr.added {
	color: #666666;
	font-style: italic;
}

tr.deleted td.date, tr.deleted td.time {
	color: <? echo $styles['color'] ?>;
	text-decoration: none;
}

tr.canceled, tr.canceled a {
	color: <? echo $styles['a_canceled']; ?>;
}

tr.tentative, tr.tentative a {
	color: <? echo $styles['a_tentative']; ?>;
}

#attendeeTable td {
  padding: 5px;
}

a.action {
	font-size: 0.8em;
}

.status {
	font-weight: bold;
	font-size: 0.9em;
	width: 7.5em;
}

.guests {
	font-weight: bold;
	font-size: 0.8em;
	width: 8em;
}

.dijitMenu {
	font-size: 0.9em !important;
}

.status .dijitComboBox {
	width: 7em;
}

.status .dijitComboBox td,
.status .dijitReset {
	border-top: none;
	border-bottom: none;
	padding: 0px;
}

.guests .dijitComboBox {
	width: 4em;
}


div#banner {
	padding: 5px;
	text-align: center;
}

div#event-list {
	padding: 5px;
}

div#event {
	padding: 5px;
}

div#event-total {
	padding: 5px;
	border-left: 1px solid #bfbfbf;
	border-bottom: 1px solid #bfbfbf;
}

div#member-list {
	padding: 5px;
}

.member-listing {
	padding: 5px;
	width: 15em;
	height: 6em;
	float: left;
	border: 1px solid #ffffff;
	background: #ffffff;
}

.member-listing .name {
	font-size: 1.1em;
}

.member-listing .email, 
.member-listing .phone, 
.member-listing .address,
.member-listing .not-available,
.member-listing .inactive  {
	padding-left: 10px;
}

.member-listing .address {
  font-size: 0.9em;
}

.member-listing .not-available,
.member-listing .inactive {
	font-style: italic;
	color: #999999;
}

.member-listing.inactive div {
  color: #999999;
}

div#member-list .selected {
	background: #fafafa url("<? echo $dojo_path; ?>dijit/themes/tundra/images/titleBarBg.gif") repeat-x bottom left;
	border: 1px solid #bfbfbf;
}


div#loading {
	position: absolute;
	top: 0px;
	right: 0px;
	
	padding: 5px;
	color: #FFFFCC;
	background: #FF6666;
	z-index: 10;
}

.tundra .dijitTitlePane .dijitTitlePaneTitle {
	font-size: 1.2em;
	font-weight: bold;
}

#loginForm {
	border: 1px solid #bfbfbf;
}

#loginForm .titleBar {
	font-weight: bold;
	padding: 2px 4px 2px 4px;
	text-align: center;
	background: #cccccc;
	background:#fafafa url("<? echo $dojo_path; ?>dijit/themes/tundra/images/titleBarBg.gif") repeat-x bottom left;
	border-bottom:1px solid #bfbfbf;
}

#loginForm .button {
	color: <? echo $styles['color']; ?>;
	border: 1px solid #9b9b9b;
	vertical-align: middle;
	padding: 0.2em 0.2em;
	background:#e9e9e9 url("<? echo $dojo_path; ?>dijit/themes/tundra/images/buttonEnabled.png") repeat-x top;
	cursor: pointer;
}

#loginForm .failed-reason {
	font-size: 0.9em;
	color: #ff0000;
}

#loginForm table td {
  padding: 5px;
}