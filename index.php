<?
	include('lib.php');
?>
<html>
<head>
<title>iTunes Remote</title>
<script>

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

		console.log(o);
		if (o.ok){
			updatePlayState(o.state);
			ge('current').innerHTML = escapeXML(o.current);
			ge('volume').innerHTML = escapeXML(o.volume);
		}
	});
}

function volumeUp(){
	ajaxify('ajax.php', {'q': 'volume_up'}, function(o){
		if (o.ok){
			ge('volume').innerHTML = escapeXML(o.volume);
		}
	});
}

function volumeDown(){
	ajaxify('ajax.php', {'q': 'volume_down'}, function(o){
		if (o.ok){
			ge('volume').innerHTML = escapeXML(o.volume);
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
			updatePlayState(o.state);
		}
	});
}

function updatePlayState(state){
	ge('playbtnimg').src = (state == 'playing') ? 'images/btn_pause.gif' : 'images/btn_play.gif';
}

function updateArtwork(){
	ajaxify('artwork.php', {}, function(o){
		if (o.ok){
			var d = new Date();
			ge('artwork').src = 'artwork.png?cb='+d.getTime();
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
	border: 1px solid #606060;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
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
}

</style>
</head>
<body>

<div id="topbar">
	<div id="title">iTunes Remote</div>

	<div id="prev"><a href="#" onclick="doPrev(); return false;"><img src="images/btn_prev.gif" width="31" height="32" /></a></div>
	<div id="play"><a href="#" onclick="doPlay(); return false;"><img src="images/btn_play.gif" width="37" height="38" id="playbtnimg" /></a></div>
	<div id="next"><a href="#" onclick="doNext(); return false;"><img src="images/btn_next.gif" width="31" height="32" /></a></div>
	<div id="volumebg"></div>

	<div id="textbox">
		<div style="padding: 0 10px;">Current: <span id="current">Loading...</span></div>
		<div style="padding: 0 10px;">Volume: <span id="volume">Loading...</span></div>
	</div>

</div>

<p><a href="#" onclick="getState(); return false">get state</a></p>

<a href="control.php?q=louder" onclick="volumeUp(); return false;">louder</a><br />
<a href="control.php?q=quieter" onclick="volumeDown(); return false;">quieter</a><br />
<a href="control.php?q=mute">mute</a><br />

<hr />

<a href="#" onclick="updateArtwork(); return false;"><img src="artwork.png" id="artwork" /></a>

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

<script>
getState();
</script>

</body>
</html>
