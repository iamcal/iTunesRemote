<?

	function run_command($cmd){
		#$cmd = AddSlashes($cmd);
		$cmd = "sudo /usr/bin/osascript -e '$cmd' 2>&1";
		#echo "<pre style=\"border: 1px solid #000\">\$ $cmd</pre>";
		$out = HtmlSpecialChars(shell_exec($cmd));
		#echo "<pre style=\"border: 1px solid #000\">$out</pre>";
		return $out;
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

			$s = str_replace(array_keys($map), $map, HtmlSpecialChars($s));

			return '"'.preg_replace_callback('![\x00-\x1f]!', array($this, 'unicodeify'), $s).'"';
		}

		function unicodeify($m){

			return '\\u'.sprintf('%04x', ord($m[0]));
		}
	}
?>