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

	if ($_REQUEST[q] == 'prev'){
		run_command('tell app "iTunes" to previous track');
		exit_with_json(array('ok' => 1));
	}

	if ($_REQUEST[q] == 'next'){
		run_command('tell app "iTunes" to next track');
		exit_with_json(array('ok' => 1));
	}

	if ($_REQUEST[q] == 'play_toggle'){

		run_command('tell app "iTunes" to playpause');
		$state = trim(run_command('tell app "iTunes" to player state'));

		exit_with_json(array(
			'ok' => 1,
			'state'		=> $state,
		));
	}



	# other commands:
	# play track 13 of user playlist "Sparkle and Fade"
	# http://dougscripts.com/itunes/itinfo/info02.php


	#
	# what playlist are we on?
	# get current playlist => user playlist id 86174
	# get name of user playlist id 86174 => Music
	# get playlists =>
	# library playlist id 56368, user playlist id 86174, user playlist id 110413, user playlist id 110416,
	# user playlist id 85764, user playlist id 154088, user playlist id 85708, user playlist id 153699,
	# user playlist id 80987, user playlist id 85074, user playlist id 85705, user playlist id 85106,
	# user playlist id 85078, user playlist id 140218, user playlist id 138865, user playlist id 153985,
	# user playlist id 153627, user playlist id 121568, user playlist id 110722, user playlist id 112064,
	# user playlist id 110425, user playlist id 127634, user playlist id 138892, user playlist id 153702,
	# user playlist id 153991, user playlist id 154004, user playlist id 206327, user playlist id 127612,
	# user playlist id 153612, user playlist id 153694
	#
	# get special kind of user playlist id 206327 => none / Music / TV Shows



	exit_with_json(array(
		'ok'	=> 0,
		'error'	=> 'Unknown method: '.$_REQUEST[q],
	));
?>