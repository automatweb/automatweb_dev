<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/root.aw,v 2.5 2001/07/26 16:49:57 duke Exp $
/*
	AW Foundation Classes
	(C) StruktuurMeedia 2000,2001
*/
class root
{
	// siin asuvad mõned sagedamini kasutataivamad funktsioonid
	var $errorlevel;
	var $stacks = array("root" => array("subcount" => 0)); // siia me salvestame erinevad stackid
	function root()
	{
		$this->errorlevel = 0;
		$this->stacks = array();
		lc_load("definition");
	}

	//  siit algavad pinu funktsioonid
	//-----------------------------------------------------
	//	kood voimaldab kasutada mitut pinu,
	//	kui pinufunktsioonile pinu ID-d ette ei anta
	//	siis, kasutatakse meelevaldselt välja mõeldud nime
	//			'root'

	// vbla pole neid funktsioone yldse vaja enam? aw_template ei kasuta neid
	// enam.
	function _push($item,$stack = "root")
	{
		if (!isset($this->stacks[$stack]))
		{
			$this->stacks[$stack]["subcount"] = 0;
		}
		$subcount = $this->stacks[$stack]["subcount"];
		$subcount++;
		$this->stacks[$stack]["subcount"] = $subcount;
		// don't you just love those 3 dimensional arrays? ;)
		$this->stacks[$stack]["items"][$subcount] = $item;
	}

	function _pop($stack = "root")
	{
		$subcount = $this->stacks[$stack]["subcount"];
		$ret = $this->stacks[$stack]["items"][$subcount];
                unset($this->stacks[$stack]["items"][$subcount]);
		$subcount--;
		$this->stacks[$stack]["subcount"] = $subcount;
                return $ret;
	}

	function _last($stack = "root")
	{
		$subcount = $this->stacks[$stack]["subcount"];
		$ret = $this->stacks[$stack]["items"][$subcount];			
		return $ret;
	}

	function _get_all($stack = "root")
	{
		return $this->stacks[$stack]["items"];
  }


	function _reset($stack = "root")
	{
		unset($this->stacks[$stack]["items"]);
		unset($this->stacks[$stack]["subcount"]);
	}
	//-----------------------------------------------------
	// ja siit nad lõpevad

	// järgmine funktsioon on inspireeritud perlist ;)
	// kasutusnäide:
	//       print $object->map("--- %s ---\n",array("1","2","3"));
	// tulemus:
	//      --- 1 ---
	//      --- 2 ---
	//      --- 3 ---
	// Ma ei näe ühtegi pohjust miks see funktsioon siin peaks olema.
	// defs.aw-sse sobib ta palju paremini
		
	function map($format,$array)
	{
		$retval = array();
		if (is_array($array))
		{
			while(list(,$val) = each($array))
			{
				$retval[]= sprintf($format,$val,$val);
			};
		}
		else
		{
			$retval[]= sprintf($format,$val,$val);
		};
		return $retval;
	}
	// sama, mis eelmine, ainult et moodustuvad paarid
  // array iga elemendi indeksist ja väärtusest
  // format peab siis sisaldama vähemalt kahte kohta muutujate jaoks

	// kui $type != 0, siis pööratakse array nö ringi ... key ja val vahetatakse ära	
	// TODO: viia defs.aw-sse
	function map2($format,$array,$type = 0)
	{
		$retval = array();
		if (is_array($array))
		{
			while(list($key,$val) = each($array))
			{
				if ($type == 0)
				{
					$v1 = $key;
					$v2 = $val;
				}
				else
				{
					$v1 = $val;
					$v2 = $key;
				};
				if ((strlen($v1) > 0) && (strlen($v2) > 0) )
				{
					$retval[] = sprintf($format,$v1,$v2);
				};
			};
		}
		else
		{
			$retval[] = sprintf($format,$val);
		};
		return $retval;
	}

	// TODO: viia defs.aw-sse
	function gen_uniq_id($param = "")
	{
		// genereerib md5 checksumi kas siis parameetrist voi 
		// juhuslikust arvust
		//  md5sum on alati 32 märki pikk
		// selle funktsiooni peaks siit välja liigutama
		if (strlen($param) > 0)
		{
			$result = md5($param);
		}
		else
		{
			$result = md5(uniqid(rand()));
		};
		return $result;
	}

	function error()
	{
		// tagastab true, kui on tekkinud viga
		return ($this->errorlevel > 0);
	}

	// formeerib xml andmestruktuuri identifikaator
	// TODO: viia defs.aw-sse
	function gen_xml_header($version = "1.0") 
	{
		return "<" . "?xml version='$version'?" . ">\n";
	}


	// formeerib xml tagi nimega $name ja parameetritega arrayst data
	// TODO: viia defs.aw-sse
	function gen_xml_tag($name,$data) 
	{
		if (is_array($data)) 
		{
			$params = join(" ",$this->map2(" %s='%s'",$data));
		}
		else 
		{
			$params = "";
		};
		$retval = sprintf("<%s%s/>\n",$name,$params);
		return $retval;
	}

	// TODO: viia defs.aw-sse
	function make_url($arr)
	{
		global $HTTP_GET_VARS,$PHP_SELF;
		$ura = $HTTP_GET_VARS;
		reset($arr);
		while (list($k,$v) = each($arr))
		{
			$ura[$k] = $v;
		};
		$urs = join("&",$this->map2("%s=%s",$ura));
		return $PHP_SELF."?".$urs;
	}
};
?>
