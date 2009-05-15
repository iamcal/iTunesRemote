<?
	$GLOBALS[oascript_path] = '/usr/bin/osascript';

	function run_command($cmd){
		$cmd = "sudo $GLOBALS[oascript_path] -e '$cmd' 2>&1";
		return shell_exec($cmd);
	}

	function exit_with_json($o){
		$json = jsonify($o);
		header("Content-Type: text/plain; charset=utf-8");
		echo $json;
		exit;
	}

	function jsonify($o){

		$p = new JSON_Thinger();
		return $p->serialize_json_value($o);
	}


	class JSON_Thinger {

		function serialize_json_value($obj, $indent=''){

			if (is_bool($obj)){
				return $obj ? 'true' : 'false';
			}

			if (is_int($obj)){
				return $obj;
			}

			if (is_float($obj)){
				return $obj;
			}

			if (is_null($obj)){
				return "null";
			}

			if (is_string($obj)){
				return $this->escape_json_string($obj);
			}

			if (is_array($obj)){

				$out = "{\n";
				$num = count($obj);
				$c = 0;

				if (!$num) return '{}';

				#
				# line up all the keys to tab boundaries
				#

				$max_key_len = 0;
				$keys = array();
				foreach ($obj as $k => $v){
					$keys[$k] = $this->escape_json_string($k);
					$max_key_len = max($max_key_len, strlen($keys[$k]));
				}
				$max_key_len++;
				$max_key_len = ceil($max_key_len / 8) * 8;
				foreach ($keys as $k => $v){
					$tabs = ceil(($max_key_len - strlen($v)) / 8);
					$keys[$k] = $v.str_repeat("\t", $tabs);
				}


				#
				# output values
				#

				foreach ($obj as $k => $v){
					$c++;
					$end = ($c == $num) ? "\n" : ",\n";
					$out .= "\t$indent".$keys[$k].": ".$this->serialize_json_value($v, "\t$indent").$end;
				}
				$out .= "$indent}";
				return  $out;
			}

			die("Unknown variable type to serialize : ".gettype($obj));
		}

		function escape_json_string($s){

			$map = array(
				'\\'	=> '\\\\',
				'"'	=> '\"',
				'/'	=> '\\/',
				"\n"	=> "\\n",
				"\r"	=> "\\r",
				"\t"	=> "\\t",
			);

			$s = str_replace(array_keys($map), $map, $s);

			return '"'.preg_replace_callback('![\x00-\x1f]!', array($this, 'unicodeify'), $s).'"';
		}

		function unicodeify($m){

			return '\\u'.sprintf('%04x', ord($m[0]));
		}
	}

	########################################################################################

	function execute_script_itunes($script){

		return execute_script("tell application \"iTunes\"\n$script\nend tell\n");
	}

	function execute_script($script){

		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w"),
		);

		$cwd = dirname(__FILE__);
		$env = array();

		$cmd = "sudo $GLOBALS[oascript_path]";

		$process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);
		if (is_resource($process)){

			fwrite($pipes[0], $script);
			fclose($pipes[0]);

			$stdout = stream_get_contents($pipes[1]);
			fclose($pipes[1]);

			$stderr = stream_get_contents($pipes[2]);
			fclose($pipes[2]);

			$exit = proc_close($process);

			return array(
				'ok'		=> $exit == 0 ? 1 : 0,
				'error'		=> trim($stderr),
				'output'	=> trim($stdout),
				'exit_code'	=> $exit,
			);
		}

		return array(
			'ok'	=> 0,
			'error' => "Can't fork",
		);
	}

	########################################################################################
?>