<?
	include('lib.php');

	$path = dirname(__FILE__);

	$cmd = "sudo /usr/bin/osascript $path/artwork.as 2>&1";

	$out = HtmlSpecialChars(shell_exec($cmd));


	exit_with_json(array(
		'ok'	=> 1,
	));
?>