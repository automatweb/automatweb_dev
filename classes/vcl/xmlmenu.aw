<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/Attic/xmlmenu.aw,v 1.1 2005/01/28 12:48:35 ahti Exp $
// xmlmenu.aw - xml-i ja aw_template abil menüüde genereerimise skript
classload("defs");
class xmlmenu
{
	// konstruktor
	// argumendid
	// xmldef(text) - menüü definitsioon, XML-is
	// template(text) - template, mida kasutada menüü joonistamiseks
	// vars(array) - array muuutujatest, mis xml-i sees asendada oleks vaja
	function xmlmenu($args = array())
	{
		extract($args);
		$this->tpl = new aw_template();
		$this->xmldef = localparse($xmldef,$vars);
	}

	//// Ehitab menüü
	// argumendid:
	// vars(array) - muutujad
	// xml(string) - path to xml definiton
	// tpl(string) - path to template
	// activelist(array) - list of active items
	function build_menu($args = array())
	{
		extract($args);
		$this->vars($vars);
		$this->load_from_files(array(
			"xml" => $xml,
			"tpl" => $tpl,
		));
		return $this->create(array(
			"activelist" => $activelist,
		));
	}

	
	//// Impordib muutujad, mida kas template voi xml defi sees kasutakse
	function vars($args = array())
	{
		if (is_array($args))
		{
			foreach($args as $key => $val)
			{
				$this->vars[$key] = str_replace("&","&amp;",$val);
			};
		};
		$this->tpl->vars($this->vars);
	}

	////
	// !Laeb vajalikud definitsioonid failist
	// argumendid:
	// xml - xml faili full path
	// tpl - template faili full path
	function load_from_files($args = array())
	{
		extract($args);
		$menudef = $this->tpl->get_file(array(
			"file" => $xml,
		));

		$template = $this->tpl->get_file(array(
			"file" => $tpl,
		));

		$this->load_from_memory(array(
			"template" => $template,
			"xmldef" => $menudef,
		));
	}

	////
	// !Laeb vajalikud definitsioonid mälust
	// argumendid:
	// xmldef(text) - menüü definitsioon, XML-is
	// template(text) - template, mida kasutada menüü joonistamiseks
	function load_from_memory($args = array())
	{
		$this->tpl->use_template($args["template"]);
		$this->xmldef = localparse($args["xmldef"],$this->vars);
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
