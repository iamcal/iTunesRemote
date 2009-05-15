<?
	include('lib.php');
?>
<html>
<head>
<title>iTunes Remote</title>
<script>

var g_interval = null;
var g_state_at = 0;
var g_song_pos = 0;
var g_song_dur = 0;
var g_song_name = '';
var g_current_id = 0;

function ge(x){
	return document.getElementById(x);
}

function escapeXML(s){
	s = ""+s;
	return s.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
}

function ajaxify(url, args, handler){

	var req = new XMLHttpRequest();
	req.onreadystatechange = function(){

		var l_f = handler;

		if (req.readyState == 4){
			if (req.status == 200){

				this.onreadystatechange = null;
				eval('var obj = '+req.responseText);
				l_f(obj);
			}else{
				l_f({
					'ok'	: 0,
					'error'	: "Non-200 HTTP status: "+req.status,
					'debug'	: req.responseText
				});
			}
		}
	}

	req.open('POST', url, 1);
	//req.setRequestHeader("Method", "POST "+url+" HTTP/1.1");
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

	var args2 = [];
	for (i in args){
		args2[args2.length] = escape(i)+'='+escape(args[i]);
	}

	req.send(args2.join('&'));
}

function getState(){
	ajaxify('ajax.php', {'q': 'get_state'}, function(o){

		if (o.ok){
			updatePlayState(o);
			ge('current').innerHTML = escapeXML(o.current);

			if (o.current != g_song_name){

				g_song_name = o.current;
				getPlaylist();
				updateArtwork();
			}
		}
	});
}

function doPrev(){
	ajaxify('ajax.php', {'q': 'prev'}, function(o){ getState(); });
}

function doNext(){
	ajaxify('ajax.php', {'q': 'next'}, function(o){ getState(); });
}

function doPlay(){
	ajaxify('ajax.php', {'q': 'play_toggle'}, function(o){
		if (o.ok){
			updatePlayState(o);
		}
	});
}

function updatePlayState(o){

	ge('playbtnimg').src = (o.state == 'playing') ? 'images/btn_pause.gif' : 'images/btn_play.gif';

	updatePlaybackHead(o.pos, o.dur);

	if (o.state == 'playing'){
		var remain = o.dur - o.pos;

		g_state_at = new Date().getTime();
		g_song_pos = o.pos;
		g_song_dur = o.dur;

		if (!g_interval){
			g_interval = window.setInterval('playbackInterval()', 1000);
		}
	}else{
		if (g_interval){
			window.clearInterval(g_interval);
			g_interval = null;
		}
	}

	ge('volumeknob').style.left = ((14 - 6) + (74 * o.volume / 100)) + 'px';
}

function updateArtwork(){
	ajaxify('artwork.php', {}, function(o){
		if (o.ok){
			var d = new Date();
			ge('artwork').src = 'artwork.png?cb='+d.getTime();
		}
	});
}

function playbackInterval(){

	var now = new Date().getTime();

	var elapsed = Math.round((now - g_state_at) / 1000);

	var pos = g_song_pos + elapsed;

	if (pos > g_song_dur){

		getState();
		window.clearInterval(g_interval);
		g_interval = null;
	}else{
		updatePlaybackHead(pos, g_song_dur);
	}
}

function updatePlaybackHead(pos, dur){

	ge('position-done').innerHTML = format_ms(pos);
	ge('position-todo').innerHTML = '-'+format_ms(dur - pos);

	ge('position-inner').style.width = (100 * pos / dur) + '%';
}

function format_ms(s){

	var m = Math.floor(s / 60);
	s -= m * 60;

	s = ''+s;
	if (s.length == 1) s = '0'+s;

	return m+':'+s;
}

function posClicked(e){
	if (!e) var e = window.event;

	var frac = e.layerX / 300;

	var pos = Math.round(frac * g_song_dur);

	ajaxify('ajax.php', {'q': 'seek' ,'pos': pos}, function(o){
		if (o.ok){
			updatePlayState(o);
		}
	});
}

function volClicked(e){
	if (!e) var e = window.event;

	var frac = 0;

	if (e.layerX > 14){

		var frac = (e.layerX - 14) / 74;
		if (frac > 1) frac = 1;
	}

	var vol = Math.round(100 * frac);

	ajaxify('ajax.php', {'q': 'set_volume' ,'v': vol}, function(o){
		if (o.ok){
			updatePlayState(o);
		}
	});
}

function getPlaylist(){

	ajaxify('ajax.php', {'q': 'playlist'}, function(o){
		if (o.ok){

			var html = '';
			var r = 1;

			html += "<tr>\n";
			html += "<th>&nbsp;</th>\n";
			html += "<th>ID</th>\n";
			html += "<th width=\"33%\">Name</th>\n";
			html += "<th width=\"33%\">Artist</th>\n";
			html += "<th width=\"33%\">Album</th>\n";
			html += "</tr>\n";

			var keys = [];
			for (var id in o.tracks){ keys[keys.length] = id; }
			keys.sort();

			for (var i=0; i<keys.length; i++){
				var id = keys[i];

				var class = (r % 2) ? 'row-1' : 'row-2';
				r++;
				if (o.tracks[id].current) class += ' current';

				html += "<tr class=\""+class+"\" ondblclick=\"playback('"+id+"'); return false\" onmousedown=\"return false\" onselectstart=\"return false\">\n";

				if (o.tracks[id].current){
					g_current_id = id;
					html += "<td><img src=\"images/playing.gif\" width=\"13\" height=\"12\" /></td>\n";
				}else{
					html += "<td>&nbsp;</td>\n";
				}

				html += "<td>"+id+"</td>\n";
				html += "<td>"+escapeXML(o.tracks[id].name)+"</td>\n";
				html += "<td>"+escapeXML(o.tracks[id].artist)+"</td>\n";
				html += "<td>"+escapeXML(o.tracks[id].album)+"</td>\n";
				html += "</tr>\n";
			}


			ge('playlist').innerHTML = html;
		}
	});
}

function playback(id){
	if (g_current_id == id) return;

	ajaxify('ajax.php', {'q': 'jump_to', 'index': id}, function(o){
		if (o.ok){
			getState();
		}
	});
}

</script>
<style>

#topbar {
	position: relative;
	background-image: url(images/top_bg.gif);
	background-repeat: repeat-x;
	background-color: #969696;
	border-left: 1px solid #606060;
	border-right: 1px solid #606060;
	border-top: 1px solid #606060;
	border-bottom: 1px solid #404040;
	-moz-border-radius-topleft: 3px;
	-moz-border-radius-topright: 3px;
	-webkit-border-top-left-radius: 3px;
	-webkit-border-top-right-radius: 3px;
	border-top-left-radius: 3px;
	border-top-right-radius: 3px;
	height: 75px;
}

#play {
	position: absolute;
	left: 60px;
	top: 27px;
}

#prev {
	position: absolute;
	left: 23px;
	top: 30px;
}

#next {
	position: absolute;
	left: 103px;
	top: 30px;
}

a img {
	border: 0;
}

#volumebg {
	position: absolute;
	left: 155px;
	top: 40px;
	width: 107px;
	height: 11px;
	background-image: url(images/volume_bg.gif);
}

#volumeknob {
	position: absolute;
	top: 0px;
	left: 10px;
	background-image: url(images/volume_knob.gif);
	width: 12px;
	height: 12px;
}

#title {
	position: absolute;
	top: 2px;
	left: 10px;
	right: 10px;
	text-align: center;
	font-family: Helvetica, Arial, sans-serif;
	font-weight: bold;
	font-size: 12px;
}

#textbox {
	position: absolute;
	background-color: pink;
	left: 315px;
	top: 22px;
	bottom: 7px;
	right: 100px;
	background-image: url(images/status_bg.gif);
	background-repeat: repeat-x;
	background-color: #DEE1CA;

	/* outer */
	border-left: 1px solid #A6A897;
	border-top: 1px solid #696B5E;
	border-right: 1px solid #A6A997;
	border-bottom: 1px solid #CFCFCF;

	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;

	font-family: Helvetica, Arial, sans-serif;
	font-size: 10px;

}

#midblock {
	position: relative;
	border-left: 1px solid #606060;
	border-right: 1px solid #606060;
	height: 600px;
}

#btmbar {
	position: relative;
	background-image: url(images/btm_bg.gif);
	background-repeat: repeat-x;
	background-color: #979797;
	border-left: 1px solid #606060;
	border-right: 1px solid #606060;
	border-top: 1px solid #404040;
	border-bottom: 1px solid #606060;
	-moz-border-radius-bottomleft: 3px;
	-moz-border-radius-bottomright: 3px;
	-webkit-border-bottom-left-radius: 3px;
	-webkit-border-bottom-right-radius: 3px;
	border-bottom-left-radius: 3px;
	border-bottom-right-radius: 3px;
	height: 27px;
}

#sidebar {
	position: absolute;
	left: 0px;
	width: 200px;
	top: 0px;
	bottom: 0px;
	background-color: #D1D7E2;
	border-right: 1px solid #404040;
}

#artwork {
	position: absolute;
	width: 180px;
	left: 10px;
	top: 10px;
}

#content {
	position: absolute;
	left: 201px;
	right: 0px;
	top: 0px;
	bottom: 0px;
	background-color: #fff;
	overflow: auto;
}

#current {
	position: absolute;
	top: 6px;
	left: 0px;
	width: 100%;
	text-align: center;
	font-size: 14px;
}

#position {
	position: absolute;
	top: 31px;
	left: 50%;
}

#position-done {
	position: absolute;
	top: -2px;
	left: -180px;
	width: 40px;
}

#position-todo {
	position: absolute;
	top: -2px;
	left: 155px;
	width: 40px;
}

#position-outer {
	position: absolute;
	top: 0px;
	left: -150px;
	width: 300px;
	border: 1px solid black;
}

#position-inner {
	width: 0%;
	height: 7px;
	background-image: url(images/progress.gif);
}





table#playlist {
	border-collapse: collapse;
	font-size: 11px;
	font-family: Helvetica, Arial, sans-serif;
	width: 100%;
}

table#playlist td {
	cursor: default;
}

td, th {
	vertical-align: top;
	padding: 2px;
}

th {
	font-weight: bold;
	background-image: url(images/th_bg.gif);
	background-color: #C7C7C7;
	background-repeat: repeat-x;
	border-bottom: 1px solid #555555;
	text-align: left;
	border-right: 1px solid #B2B2B2;
}

td {
	border-right: 1px solid #D9D9D9;
}

tr.row-1 td {
	background: #F1F5FA;
	border-bottom: 1px solid #F1F5FA;
	color: #000000;
}

tr.row-2 td {
	background: #ffffff;
	border-bottom: 1px solid #ffffff;
	color: #000000;
}

tr.current td {
	color: #fff;
	background: #3D80DF;
	border-bottom: 1px solid #7DAAEA;
	border-right: 1px solid #346DBE;
}



</style>
</head>
<body>

<div id="topbar">
	<div id="title">iTunes Remote</div>

	<div id="prev"><a href="#" onclick="doPrev(); return false;"><img src="images/btn_prev.gif" width="31" height="32" /></a></div>
	<div id="play"><a href="#" onclick="doPlay(); return false;"><img src="images/btn_play.gif" width="37" height="38" id="playbtnimg" /></a></div>
	<div id="next"><a href="#" onclick="doNext(); return false;"><img src="images/btn_next.gif" width="31" height="32" /></a></div>

	<div id="volumebg" onclick="volClicked(event)">
		<div id="volumeknob"></div>
	</div>

	<div id="textbox">
		<div id="position">
			<div id="position-done">0:00</div>
			<div id="position-outer" onclick="posClicked(event)"><div id="position-inner"></div></div>
			<div id="position-todo">0:00</div>
		</div>
		<div id="current">...</div>
	</div>
</div>
<div id="midblock">
	<div id="sidebar">

		<a href="#" onclick="updateArtwork(); return false;"><img src="artwork.png" id="artwork" /></a>

<!-- ********************************************** -->
		<div style="padding: 200px 20px 20px 20px;">

<a href="#" onclick="getState(); return false">manually update state</a><br />
<hr />

<form action="./" method="post">
<input type="text" name="cmd" value="<?=HtmlSpecialChars($_POST[cmd])?>" /> <input type="submit" value="Run Command" />
</form>

<? if ($_POST[cmd]){ ?>

<pre>
$ tell application "iTunes" to <?=HtmlSpecialChars($_POST[cmd])?>:
<?=nl2br(HtmlSpecialChars(run_command('tell app "iTunes" to '.$_POST[cmd])))?>
</pre>

<? } ?>
		</div>
<!-- ********************************************** -->

	</div>
	<div id="content">

		<table id="playlist">
		</table>

	</div>
</div>
<div id="btmbar">
</div>



<script>
getState();
</script>

</body>
</html>
