<?
	echo "<META HTTP-EQUIV=refresh content=1;URL=./>";

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

		case "mute":
			mutev();
			echo "Muting the Volume";
			break;
	}


    function mutev()
    {
    echo "start mute function<br>";

    $data = file_get_contents("/www/volume.txt");

    $logfile = fopen("/www/volume.txt",'w');

    $oldvolume = exec("osascript -e 'tell app \"iTunes\" to sound volume'");

    echo "volume data:$data:<br>";
    if ($data == "x")
    {
    fwrite($logfile,$oldvolume);
    exec("osascript -e 'tell app \"iTunes\" to set sound volume to 0'");
    }
    else
    {
    fwrite($logfile,"x");
    exec("osascript -e 'tell app \"iTunes\" to set sound volume to $data'");
    }
    fclose($logfile);
    }


	function run_command($cmd){
		#$cmd = AddSlashes($cmd);
		$cmd = "sudo /usr/bin/osascript -e '$cmd' 2>&1";
		#echo "<pre style=\"border: 1px solid #000\">\$ $cmd</pre>";
		$out = HtmlSpecialChars(shell_exec($cmd));
		#echo "<pre style=\"border: 1px solid #000\">$out</pre>";
		return $out;
	}
?>