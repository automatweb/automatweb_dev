<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/replicator.aw,v 2.8 2002/06/10 15:50:54 kristo Exp $

classload("db");
class replicator_host extends db_connector
{
	//key on host key ja table on ülekannete tabel
	function replicator_host($key,$table)
	{
		$this->key=$key;
		$this->table=$table;
		$this->db_init();
		lc_load("definition");
	}

	function parse_request($arr)
	{
		global $REMOTE_ADDR;
		$trans=unserialize(stripslashes($arr["transaction"]));
		
		extract(is_array($trans)?$trans:array());
		unset($error);
		// kui on sessiooni avamise käsk
		if ($func=="OPEN")
		{
			// no prize for anyone who can tell why the next line is not * 65536
			srand((double)microtime() * 131337);
			$rand=md5(uniqid(rand()));
			$q = "INSERT INTO $this->table (ip,tm,rand) VALUES ('$REMOTE_ADDR','".time()."','$rand')";
			if (!$this->db_query("INSERT INTO $this->table (ip,tm,rand) values ('$REMOTE_ADDR','".time()."','$rand')",false))
			{
				$error="can't allocate tid";
			};
				

			$tid=$this->db_last_insert_id();
			$this->respond(array("error"=>$error,"tid"=>$tid,"rand"=>$rand));
			return array("nop"=>1,"error"=>$error);
		}

		$q=$this->db_query("SELECT * FROM $this->table where tid=$tid",true);

		if (!$q || $this->num_rows() < 1)
		{
			$error="nonexisting tid";
		} else
		{
			$rec=$this->db_next();
			
			// vaata, kas kliendil on õige host_key & kas on sama klient kes tegi OPEN

			
			$correcthash=md5($rec["IP"].$rec["RAND"].$this->key.$rec["RAND"]);

			//echo("ip=".$rec["IP"]." rand=".$rec["RAND"]." key=".$this->key." <br>,thus, hash=$correcthash<br> client sent=$hash<br><br>");//dbg
			if ($hash!=$correcthash || $rec["IP"]!=$REMOTE_ADDR)
			{
				$error="wrong hash"/*."ip=".$rec["IP"]."rand=".$rec["RAND"]." key=".$this->key." <br>,thus,hash=$correcthash<br> client sent=$hash<br><br>"*/;
			} else
			{
				if ($func=="CLOSE")
				{
					 $this->db_query("DELETE FROM $this->table where tid=$tid",false);
					 $this->respond(array("error"=>""));
					 return array("nop"=>1);
				}
				if ($replicator_close==1)
				{
					$this->db_query("DELETE FROM $this->table where tid=$tid",false);
				}
			}
		}
		
		return array_merge($trans,array("error"=>$error));

	}


	function respond($arr)
	{
		echo(serialize($arr));
	}
}




class replicator_client extends db_connector
{
	function replicator_client($url,$key)
	{
		global $SERVER_ADDR;

		$jobu=strlen($url);

		$this->url=(substr($url,0,7)=="http://")?substr($url,7,strlen($url)-7):$url;
		$this->url="http://"."bugtrack:turvaauk@".$this->url;
		$open_result=$this->_query(array("func"=>"OPEN"));

		if (!$open_result["tid"] || !$open_result["rand"])
			$open_result["error"]="open() failure on server";

		if ($open_result["error"])
		{
			$this->error=$open_result["error"];
			return;
		} 
		$this->tid=$open_result["tid"];
		//echo("key=*$key*");
		//echo
//"<br>makinghash:md5($SERVER_ADDR.{$open_result["rand"]}.$key.{$open_result["rand"]})";//DBG
		$this->hash=md5($SERVER_ADDR.$open_result["rand"].$key.$open_result["rand"]);


	}

	function _query($arr)
	{
		$q=$this->url."?transaction=".urlencode(serialize($arr));
		//echo "<br>query=$q<br>";//DBG
		$ret=@file($q);

		//echo("<br>ret=".join("",$ret).",");print_r(unserialize(join("",$ret)));echo("<br>");//DBG

		if (!is_array($ret))
			return array("error"=>"httpquery failed ".urldecode($q));
		return unserialize(join("",$ret));
	}

	function close()
	{
		$this->_query(array("func"=>"CLOSE","tid"=>$this->tid,"hash"=>$this->hash));
	}

	function query($func,$arr,$rclose=0)
	{
		return $this->_query(array_merge($arr,array("func"=>$func,"tid"=>$this->tid,"hash"=>$this->hash,"replicator_close"=>$rclose,"r_uid" => aw_global_get("uid"))));
	}
}
?>
