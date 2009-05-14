<?
	#
	# $Id$
	#
	# Inspired by http://www.whatsmyip.org/itunesremote/
	#

	include('lib.php');

	echo "<META HTTP-EQUIV=refresh content=10;URL=./>";

	$q = $_GET['q'];

	switch ($q){

		case "":
		echo "You need to send me a command, then I shall execute it";
		break;

		case "play":
			run_command('tell app "iTunes" to play');
			echo "Playing";
			break;

		case "pause":
 			run_command('tell app "iTunes" to pause');
			echo "Pausing";
			break;

		case "playpause":
			run_command('tell app "iTunes" to playpause');
			echo "Toggling Play";
			break;

		case "next":
			run_command('tell app "iTunes" to next track');
			echo "Next Track";
			break;

		case "prev":
			run_command('tell app "iTunes" to previous track');
			echo "Previous Track";
			break;

		case "louder":
			run_command('tell app "iTunes" to set sound volume to sound volume + 5');
			echo "Turning Up the Volume";
			break;

		case "quieter":
			run_command('tell app "iTunes" to set sound volume to sound volume - 5');
			echo "Turning Down the Volume";
			break;
	}
?>