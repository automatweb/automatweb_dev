<?php
// $Header: /home/cvs/automatweb_dev/vcl/Attic/xmlmenu.aw,v 2.0 2001/06/08 23:45:49 duke Exp $
// xmlmenu.aw - xml-i ja aw_template abil menüüde genereerimise skript
class xmlmenu {
	// konstruktor
	// argumendid
	// xmldef(text) - menüü definitsioon, XML-is
	// template(text) - template, mida kasutada menüü joonistamiseks
	// vars(array) - array muuutujatest, mis xml-i sees asendada oleks vaja
	function xmlmenu($args = array())
	{
		extract($args);
		$this->tpl = new aw_template();
		$this->vars(is_array($vars) ? $vars : array());
		$this->xmldef = $this->tpl->localparse($xmldef,$vars);
		$this->tpl->use_template($template);
	}

	function vars($args = array())
	{
		$this->tpl->vars($args);
	}

	////
	// !Loob menüü
	// argumendid:
	// activelist(array) - array aktiivsetest elementidest
	function create($args = array())
	{
		$nil = array();
		$nil[] = "";
		$this->activelist = array_merge($nil,$args["activelist"]);
		$this->level = 1;

		// genereerime menüü
		classload("xml");
		$xml = new xml();
                $menudefs = $xml->xml_unserialize(array(
                                        "source" => $this->xmldef,
		));
		$this->_gen_menu($menudefs);

		return $this->tpl->parse();
	}

	////
	// !Kutsutakse välja create seest, ning kutsub iseennast rekursiivselt välja
	function _gen_menu($menudefs = array())
	{
		// foreach-i ei saa rekursiivsete funktsioonide sees kasutada, sest see loob iga
		// kord uue koopia arrayst
		while(list($key,$val) = each($menudefs))
		{
			if ($this->activelist[$this->level] == $key)
			{
				$tpl = "level" . $this->level . "_act";
			}
			else
			{
				$tpl = "level" . $this->level;
			};
			$var = "level" . $this->level;
			$this->tpl->vars(array(
					"link" => $val["link"],
					"caption" => $val["caption"],
				));
			$this->tpl->vars_merge(array($var => $this->tpl->parse($tpl)));
			if (is_array($val["sublinks"]) && ($key == $this->activelist[$this->level]))
			{
				$this->level++;
				$this->_gen_menu($val["sublinks"]);
				$this->level--;
			};
		};
	}
};
?>
