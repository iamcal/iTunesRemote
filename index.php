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
			ge('state').innerHTML = escapeXML(o.state);
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

</script>
</head>
<body>

<div>State: <span id="state">Loading...</span></div>
<div>Current: <span id="current">Loading...</span></div>
<div>Volume: <span id="volume">Loading...</span></div>

<p><a href="#" onclick="getState(); return false">get state</a></p>

<a href="control.php?q=play">play</a><br />
<a href="control.php?q=pause">pause</a><br />
<a href="control.php?q=playpause">playpause</a><br />
<a href="control.php?q=next">next</a><br />
<a href="control.php?q=prev">prev</a><br />
<a href="control.php?q=louder" onclick="volumeUp(); return false;">louder</a><br />
<a href="control.php?q=quieter" onclick="volumeDown(); return false;">quieter</a><br />
<a href="control.php?q=mute">mute</a><br />

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
