<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/css.aw,v 2.1 2001/08/03 03:18:27 duke Exp $
// css.aw - CSS (Cascaded Style Sheets) haldus
// I decided to make it a separate class, because I think the style.aw 
// class is too cluttered.

// General idea on selline:
// Saidi juures / Autom@tWeb-is on defineeritud mingit kindlaksmääratud nimedega CSS stiilid,
// css_list kuvab nende nimekirja, ning lubab sul igale stiilile vastavusse mõne enda defineeritud
// stiili.

// Ja üldiselt peaks see jagunema kaheks, peaksid olema süsteemi stiilid, ning "Minu stiilid"...
// systeemi stiile kasutatakse by default, kuid, kui kasutajal on need endal defineeritud, siis
// kasutame neid.

// Stiilifaile peab saama ka importida/exportida

// Tööle hakkab see nii, et tehakse cachesse fail, mis siis html-i alguses sisse loetakse

// font-family: serif|sans-serif|monospace|cursive
// font-style: normal|italic
// font-weight: normal|bold|bolder
// font-size: XX px|pt|in|em|cm|mm
// color: #RRGGBB
// background: #RRGGBB

global $orb_defs;
$orb_defs["css"] = "xml";

class css extends aw_template {
	function css ($args = array())
	{
		$this->db_init();
		// kuidas ma seda edimisvormi alati automawebi juurest lugeda saan?
		$this->tpl_init("css");

		// fondifamilyd, do not change the order
		$this->font_families = array(
				"0" => "Verdana,Helvetica,sans-serif",
				"1" => "Arial,Helvetica,sans-serif",
				"2" => "Tahoma,sans-serif",
				"3" => "serif",
				"4" => "sans-serif",
				"5" => "monospace",
				"6" => "cursive",
		);

		$this->units = array(
				"px" => "pikslit",
				"pt" => "punkti (1pt=1/72in)",
				"in" => "tolli",
				"em" => "ems (suhteline)",
				"cm" => "sentimeetrit",
				"mm" => "millimeetrit",
		);

		$this->styles = array(
			"0" => "ftitle",
			"1" => "fcaption",
			"2" => "title",
			"3" => "plain",
			"4" => "header1",
			"5" => "fgtitle",
			"6" => "fgtext",
		);


	}


	////
	// !Kuvab nimekirja mille abil saad süsteemi ja oma stiilid vastavusse seada
	function css_list($args = array())
	{

		classload("config","xml");
		$conf = new config();
		$xml = new xml();

		$custom_css_xml = $conf->get_simple_config("custom_css");
		$custom_css = $xml->xml_unserialize(array("source" => $custom_css_xml));
		
		$this->read_template("list.tpl");
		$styles = $this->_get_my_styles(array("active" => true));
		$my_styles = array("0" => "default");
		if (is_array($my_styles))
		{
			foreach($styles as $key => $val)
			{
				$my_styles[$key] = $val["name"];
			};
		};

		// kaherealine tabel, vasakul kuvatakse koik predefined stiilide nimed,
		// paremal on dropdownid, kust saab valida enda defineeritud nimedega stiilid
		// ja need enda stilid on siis objektid, mis asuvad kasutaja kodukataloogi all
		$c = "";
		$cnt = 0;
		foreach($this->styles as $key => $val)
		{
			$cnt++;

			$this->vars(array(
				"cnt" => $cnt,
				"sys_style" => $val,
				"my_styles" => $this->picker($custom_css[$val],$my_styles),
			));

			$c .= $this->parse("line");
		};
		
		$this->vars(array(
			"line" => $c,
			"link_sys_styles" => $this->mk_my_orb("list",array()),
			"link_my_styles" => $this->mk_my_orb("my_list",array()),
			"reforb" => $this->mk_reforb("submit_list",array()),
		));

		return $this->parse();
	}

	function css_sync($args = array())
	{
		// loeme koigepealt koik css stiilid sisse
		classload("config","xml","file");
		$conf = new config();
		$xml = new xml();

		$custom_css_xml = $conf->get_simple_config("custom_css");
		$custom_css = $xml->xml_unserialize(array("source" => $custom_css_xml));

		$css_file = "";

		foreach($custom_css as $key => $val)
		{
			if ($val)
			{
				$css_info = $this->get_obj_meta($val);
				$css_file .= $this->_gen_css_style($key,$css_info["meta"]["css"]);
			};
		}
		
		$awf = new file();
		$success =$awf->put_special_file(array(
			"name" => "custom.css",
			"content" => $css_file,
			"sys" => true,
		));
	}
		
	
	////
	// !Submitib CSS nimekirja
	function submit_css_list($args = array())
	{
		extract($args);
		classload("xml","config","file");
		$xml = new xml();
		
		// salvestame ära ka 
		$conf = new config();

		$custom_css = $xml->xml_serialize($style);
		$this->quote($custom_css);
		$conf->set_simple_config("custom_css",$custom_css);

		$styles = $this->_get_my_styles();
		$css_data = array();

		foreach($styles as $key => $val)
		{
			$tmp = $xml->xml_unserialize(array("source" => $val["metadata"]));
			$css_data[$key] = $tmp["css"];
		};

		$css_info = "";

		foreach($style as $key => $val)
		{
			// kui on määratud stiil, siis
			if ($val)
			{
				$css_info .= $this->_gen_css_style($key,$css_data[$val]);
			};
		};


		$awf = new file();
		$success =$awf->put_special_file(array(
			"name" => "custom.css",
			"content" => $css_info,
			"sys" => true,
		));
		if (not($success))
		{
			$this->raise_error("CSS faili kirjutamine ei õnnestunud, kontrollige kataloogi oigusi",true);
		};
		return $this->mk_my_orb("list",array());
	}

	////
	// !gets "my styles"
	function _get_my_styles($args = array())
	{
		extract($args);

		if (not($parent))
		{
			global $rootmenu;
			$parent = $rootmenu;
		};
		
		$styles = $this->get_objects_below(array(
				"parent" => $rootmenu,
				"class" => CL_CSS,
				"active" => $active,
		));

		return $styles;
	}

	////
	// !genereerib arrayst tegeliku stiili
	// name - stiili nimi, data, array css atribuutidest
	function _gen_css_style($name,$data = array())
	{
		$retval = ".$name {\n";
		foreach($data as $key => $val)
		{
			switch($key)
			{
				case "ffamily":
					$mask = "font-family: %s;\n";
					break;

				case "fstyle":
					$mask = "font-style: %s;\n";
					break;

				case "fweight":
					$mask = "font-weight: %s;\n";
					break;
				
				case "fgcolor":
					$mask = "color: %s;\n";
					break;

				case "bgcolor":
					$mask = "background: %s;\n";
					break;

				case "textdecoration":
					$mask = "text-decoration: %s;\n";
					break;
			};

			if ($key == "size")
			{
				$retval .= "\tfont-size: $val" . $data["units"] . ";\n";
			}
			elseif ($key != "units")
			{
				$retval .= sprintf("\t" . $mask,$val);
			};

		}

		$retval .= "}\n";
		return $retval;
	}

	////
	// !Kuvab "Minu stiilide" nimekirja, ehk selle, kust sa saad neid endale juurde teha,
	// ja olemasolevaid muuta
	function css_my_list($args = array())
	{
		$this->read_template("my_list.tpl");

		$styles = $this->_get_my_styles();

		if (is_array($styles))
		{
			$c = "";
			$cnt = 0;
			foreach($styles as $key => $val)
			{
				$cnt++;
				$this->vars(array(
					"cnt" => $cnt,
					"oid" => $key,
					"name" => ($val["name"]) ? $val["name"] : "(nimetu)",
					"modified" => $this->time2date($val["modified"],4),
					"modifiedby" => $val["modifiedby"],
					"checked" => ($val["status"] == 2) ? "checked" : "",
					"link_edit" => $this->mk_my_orb("edit",array("oid" => $key)),
					"link_delete" => $this->mk_my_orb("delete",array("oid" => $key)),
				));
				$c .= $this->parse("line");
			};
		};

		$this->vars(array(
			"line" => $c,
			"link_sys_styles" => $this->mk_my_orb("list",array()),
			"link_add_style" => $this->mk_my_orb("add",array()),
			"reforb" => $this->mk_reforb("submit_my_list",array()),
		));

		return $this->parse();
	}

	////
	// !submitib "minu stiilid"
	function css_submit_my_list($args = array())
	{
		extract($args);
		$oidlist = join(",",$check);
		// koigepealt setime koik stiiliobjektid mitteaktiivseks
		$q = "UPDATE objects SET status = 1 WHERE oid IN ($oidlist)";
		$this->db_query($q);
		if (is_array($act))
		{
			$oidlist = join(",",$act);
			$q = "UPDATE objects SET status = 2 WHERE oid IN ($oidlist)";
			$this->db_query($q);
		};
		return $this->mk_my_orb("my_list",array());
	}

	////
	// !Kuvab CSS objekti lisamis/muutmisvormi
	// argumendid:
	// oid(int) - muudetava stiiliobjekti ID
	function css_edit($args = array())
	{
		$this->read_template("edit.tpl");
		extract($args);	
		if ($oid)
		{
			$obj = $this->get_obj_meta($oid);
			$css_data = $obj["meta"]["css"];
		}
		else
		{
			$css_data = array();
		};
	
		$c = "";
		foreach($this->font_families as $key => $val)
		{
			$this->vars(array(
				"family" => $val,
				"ffamily" => $key,
				"fchecked" => ($val == $css_data["ffamily"]) ? "checked" : "",
			));

			$c .= $this->parse("family");
		};
			

		$this->vars(array(
			"name" => $obj["name"],
			"family" => $c,
			"italic" => ($css_data["fstyle"] == "italic") ? "checked" : "",
			"bold" => ($css_data["fweight"] == "bold") ? "checked" : "",
			"underline" => ($css_data["textdecoration"] == "underline") ? "checked" : "",
			"fgcolor" => $css_data["fgcolor"],
			"bgcolor" => $css_data["bgcolor"],
			"size" => $css_data["size"],
			"units" => $this->picker($css_data["units"],$this->units),
			"link_sys_styles" => $this->mk_my_orb("list",array()),
			"link_my_styles" => $this->mk_my_orb("my_list",array()),
			"reforb" => $this->mk_reforb("submit",array("oid" => $oid)),
		));
		return $this->parse();
	}


	////
	// !Salvestab CSS stiili
	function css_submit($args = array())
	{
		// by default lisame uue stiili saidi root objecti alla
		global $rootmenu;
		
		$block = array();

		$block["ffamily"] = $this->font_families[$args["ffamily"]];
		$block["fstyle"] = isset($args["italic"]) ? "italic" : "normal";
		$block["fweight"] = isset($args["bold"]) ? "bold" : "normal";
		$block["textdecoration"] = isset($args["underline"]) ? "underline" : "none";
		$block["size"] = $args["size"];
		$block["units"] = $args["units"];
		$block["fgcolor"] = $args["fgcolor"];
		$block["bgcolor"] = $args["bgcolor"];

		$oid = $args["oid"];

		if (not($oid))
		{
			$oid = $this->new_object(array(
				"parent" => $rootmenu,
				"name" => $args["name"],
				"class_id" => CL_CSS,
				),false);
		}
		else
		{
			$this->upd_object(array(
				"oid" => $oid,
				"name" => $args["name"],
			));
		};

		$this->set_object_metadata(array(
				"oid" => $oid,
				"key" => "css",
				"value" => $block,
		));

		$this->css_sync();

		return $this->mk_my_orb("edit",array("oid" => $oid));
	}

	////
	// !Kustutab mingi eelnevalt defineeritud stiili
	function css_delete($args = array())
	{
		extract($args);
		$q = "DELETE FROM objects WHERE oid = '$oid' AND class_id = " . CL_CSS;
		$this->db_query($q);
		
		return $this->mk_my_orb("my_list",array());
	}
	
};
