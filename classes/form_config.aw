<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_config.aw,v 2.2 2002/04/11 11:02:57 duke Exp $
// form_config.aw - FormGen configuration

classload("form_element");

class form_config extends form_base
{
	function form_config()
	{
		$this->form_base();
		$this->tpl_init("forms/configure");
	}

	function config()
	{
		$this->read_template("config.tpl");

		classload("config");
		$co = new config;
		$this->mk_path(0,"FormGen configuration");
		$_typs = $co->get_simple_config("form::element_types");
		$_styps = $co->get_simple_config("form::element_subtypes");
		$typs = aw_unserialize($_typs);
		$styps = aw_unserialize($_styps);

		$fo = new form_element;
		$atyps = $fo->get_all_types();
		$astyps = $fo->get_all_subtypes();

		if (!is_array($typs))
		{
			$typs = $atyps;
		}

		if (!is_array($styps))
		{
			$styps = $astyps;
		}

		foreach($atyps as $type => $typename)
		{
			$this->vars(array(
				"type" => $type,
				"type_name" => $typename,
				"type_check" => checked($typs[$type] != "")
			));
			
			$stp = "";
			// some element types don't have subtypes - duke
			if (is_array($astyps[$type]))
			{
				foreach($astyps[$type] as $st => $stname)
				{
					if ($st != "")
					{
						$this->vars(array(
							"subtype" => $st,
							"subtype_name" => $stname,
							"subtype_check" => checked($styps[$type][$st] != "")
						));
						$stp.=$this->parse("SUBTYPE");
					}
				}
			};
			$this->vars(array(
				"SUBTYPE" => $stp
			));
			$tp.=$this->parse("TYPE");
		}
		$this->vars(array(
			"TYPE" => $tp,
			"reforb" => $this->mk_reforb("submit", array())
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		classload("config");
		$co = new config;

		classload("form_element");
		$fo = new form_element;
		$all_types = $fo->get_all_types();
		$all_subtypes = $fo->get_all_subtypes();

		$ts = array();
		$sts = array();
		if (is_array($types))
		{
			foreach($types as $typ => $one)
			{
				if ($one == 1)
				{
					$ts[$typ] = $all_types[$typ];
					$sts[$typ] = array("" => "");
					if (is_array($subtypes[$typ]))
					{
						foreach($subtypes[$typ] as $st => $one)
						{
							if ($one == 1)
							{
								$sts[$typ][$st] = $all_subtypes[$typ][$st];
							}
						}
					}
				}
			}
		}

		$types = aw_serialize($ts);
		$subtypes = aw_serialize($sts);

		$types = $this->quote($types);
		$subtypes = $this->quote($subtypes);

		$co->set_simple_config("form::element_types",$types);
		$co->set_simple_config("form::element_subtypes",$subtypes);

		return $this->mk_my_orb("config", array());
	}
}

?>
