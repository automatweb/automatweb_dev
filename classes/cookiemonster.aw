<?php

// a class to aid in debugging - you can set cookies in your browser with this

class cookiemonster extends aw_template
{
	function cookiemonster()
	{
		$this->init("cookiemonster");
	}

	////
	// !generates a list of cookies in the user's browser
	function gen_list($arr)
	{
		extract($arr);
		$this->read_template("list.tpl");
		global $HTTP_COOKIE_VARS;
		foreach($HTTP_COOKIE_VARS as $k => $v)
		{
			$this->vars(array(
				"name" => $k,
				"value" => $v
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
			"reforb" => $this->mk_reforb("munch")
		));
		return $this->parse();
	}

	////
	// !saves changes
	function munch($arr)
	{
		extract($arr);

		$domain = substr($this->cfg["baseurl"],strlen("http://"));
		if (is_array($del))
		{
			foreach($del as $nm => $ddd)
			{
				if ($ddd == 1)
				{
					setcookie($nm,"",time(),"/",$domain,0);
				}
			}
		}

		if (is_array($val))
		{
			foreach($val as $nm => $vl)
			{
				if ($GLOBALS["HTTP_COOKIE_VARS"][$nm] != $vl)
				{
					setcookie($nm,$vl,time()+3600*24*100,"/",$domain,0);
				}
			}
		}

		if ($new_name != "" && $new_value != "")
		{
			setcookie($new_name,$new_value,time()+3600*24*100,"/",$domain,0);
		}

		return $this->mk_my_orb("list");
	}
}
?>