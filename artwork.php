<?
	include('lib.php');

	$path = dirname(__FILE__);

	$out = shell_exec("sudo $GLOBALS[oascript_path] $path/artwork.as $path 2>&1");

	exit_with_json(array(
		'ok'	=> 1,
		'out'	=> $out,
	));
?>