<?
	include('lib.php');

	if ($_REQUEST[q] == 'get_state'){

		$volume = intval(run_command('tell app "iTunes" to sound volume'));
		$state = trim(run_command('tell app "iTunes" to player state'));
		$current = trim(run_command('tell app "iTunes" to get (name, artist, album) of current track'));

		exit_with_json(array(
			'ok' => 1,
			'volume'	=> $volume,
			'state'		=> $state,
			'current'	=> $current,
		));
	}

	if ($_REQUEST[q] == 'set_volume'){
		run_command('tell app "iTunes" to set sound volume to '.intval($_REQUEST[v]));
		exit_with_json(array(
			'ok' => 1,
		));
	}

	if ($_REQUEST[q] == 'volume_up'){
		run_command('tell app "iTunes" to set sound volume to sound volume + 5');
		$volume = intval(run_command('tell app "iTunes" to sound volume'));
		exit_with_json(array(
			'ok' => 1,
			'volume' => $volume,
		));
	}

	if ($_REQUEST[q] == 'volume_down'){
		run_command('tell app "iTunes" to set sound volume to sound volume - 5');
		$volume = intval(run_command('tell app "iTunes" to sound volume'));
		exit_with_json(array(
			'ok' => 1,
			'volume' => $volume,
		));
	}

	# other commands:
	# play track 13 of user playlist "Sparkle and Fade"
	# http://dougscripts.com/itunes/itinfo/info02.php


	exit_with_json(array(
		'ok'	=> 0,
		'error'	=> 'Unknown method: '.$_REQUEST[q],
	));
?>