<?php

class http 
{
	function get($url)
	{
		enter_function("http::get");
		$data = parse_url($url);

		$host = !empty($data["host"]) ? $data["host"] : aw_ini_get("baseurl");

		$port = (!empty($data["port"]) ? $data["port"] : 80);

		$y_url = $data["path"].($data["query"] != "" ? "?".$data["query"] : "").($data["fragment"] != "" ? "#".$data["fragment"] : "");

		$req  = "GET $y_url HTTP/1.0\r\n";
		$req .= "Host: ".$host.($port != 80 ? ":".$port : "")."\r\n";
		$req .= "User-agent: AW-http-fetch\r\n";
		$req .= "\r\n";
		classload("socket");
		$socket = new socket(array(
			"host" => $host,
			"port" => $port,
		));
		//echo "req = ".dbg::dump($req)." <br>";
		$socket->write($req);
		$ipd = "";
		while($data = $socket->read(10000000))
		{
			$ipd .= $data;
		};
		list($headers,$data) = explode("\r\n\r\n",$ipd,2);
		$this->last_request_headers = $headers;
		//echo htmlentities($headers)."<br>".htmlentities($data);

		exit_function("http::get");
		return $data;
	}

	function get_type()
	{
		$headers = explode("\n", $this->last_request_headers);

		$ct = "text/html";
		foreach($headers as $hd)
		{
			if (preg_match("/Content\-Type\: (.*)/", $hd, $mt))
			{
				$ct = $mt[1];
			}
		}

		return $ct;
	}
}
?>
