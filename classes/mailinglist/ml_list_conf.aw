<?php
/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general
	
	@property user_form type=select multiple=1 size=10
	@caption Kasutaja lisamise vorm

	@property user_search_form type=select
	@caption Kasutaja otsimise vorm

	@property folders type=select multiple=1 size=20
	@caption Root kataloogid

	@property form_jrk type=callback callback=callback_gen_form_jrk_list editonly=1
	@caption Vormide järjekord listi liikme lisamisel

	@property name_els type=callback callback=callback_gen_name_list editonly=1
	@caption Vali elemendid, mille väärtus pannakse listi liikme objekti nimeks

	@property mailto_el type=select editonly=1
	@caption Element, kus on meiliaadress

*/

class ml_list_conf extends class_base
{
	function ml_list_conf()
	{
		$this->init(array(
			"tpldir" => "mailinglist/ml_list_conf",
			"clid" => CL_ML_LIST_CONF,
		));

		$this->finst = get_instance("formgen/form");
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "user_form":
				$data["options"] = $this->finst->get_flist(array(
					"type" => FTYPE_ENTRY,
					 "addempty" => true,
					 "addfolders" => true,
					 "sort" => true,
				));
				break;
			
			case "user_search_form":
				$data["options"] = $this->finst->get_flist(array(
					"type" => FTYPE_SEARCH,
					"addempty" => true,
					"addfolders" => true,
					"sort" => true,
				));
				break;

			case "folders":
				$data["options"] = $this->get_menu_list();
				break;

			case "mailto_el":
				$data["options"] = $this->_get_user_form_elements($args["obj"]["meta"]["user_form"]);
				break;


		}
		return $retval;
	}

	function set_property($args = array())
	{
                $data = &$args["prop"];
                $retval = PROP_OK;
                switch($data["name"])
                {
			case "name_els":
				$metadata = &$args["metadata"];
				$metadata["name_els_ord"] = $args["form_data"]["name_els_ord"];
				$metadata["name_els_post"] = $args["form_data"]["name_els_post"];
				break;

			case "mailto_el":
				$mailto_el = $args["prop"]["value"];
				$mailto_el_form = "";
				foreach($args["form_data"]["user_form"] as $ufid)
				{
					$this->finst->load($ufid);
					if (is_object($this->finst->get_element_by_id($mailto_el)))
					{
						$mailto_el_form = $ufid;
						break;
					}
				}
				if ($mailto_el_form)
				{
					$metadata = &$args["metadata"];
					$metadata["mailto_el_form"] = $mailto_el_form;
				};
				break;

		};
		return $retval;
	}



	////
	// !Since at least 2 different properties need this, I made it a separate function
	// $arr - the contents of $obj["meta"]["user_form"]
	function _get_user_form_elements($arr)
	{
		if (!is_array($this->user_form_elements))
		{
			$this->user_form_elements = array();
			$ar = new aw_array($arr);
			foreach($ar->get() as $fid)
			{
				$ml = $this->finst->get_form_elements(array(
					"id" => $fid,
					"key" => "id",
					"all_data" => false,
				));

				foreach($ml as $k => $v)
				{
					$this->user_form_elements[$k] = $v;
				}
			}
		};
		return $this->user_form_elements;
	}

	function callback_gen_form_jrk_list($args = array())
	{
		$flist = $this->finst->get_flist(array(
			"type" => FTYPE_ENTRY,
			"addempty" => true,
			"addfolders" => true,
			"sort" => true,
		));
		// yah, I know this is not very portable (to other output clients)
		// but right now I have no means to create those rather simple tables
		$this->read_template("pieces.tpl");
		$retval = array();
		$ar = new aw_array($args["obj"]["meta"]["user_form"]);
		$clist = "";
		foreach($ar->get() as $fid)
		{
			$this->vars(array(
				"form" => $flist[$fid],
				"jrkbox" => html::textbox(array(
					"name" => "form_jrk[$fid]",
					"value" => $args["obj"]["meta"]["form_jrk"][$fid],
					"size" => 2,
					"maxlength" => 2,
				)),
			));
			$clist .= $this->parse("FORM");
		};

		$this->vars(array(
			"FORM" => $clist,
		));

		$tmp = array(
			"type" => "text",
			"caption" => $args["prop"]["caption"],
			"group" => $args["prop"]["group"],
			"value" => $this->parse("FORMS"),
		);
		return array($args["prop"]["name"] => $tmp);
	}
	
	function callback_gen_name_list($args = array())
	{
		$clist = "";
		$this->read_template("pieces.tpl");
		$meta = $args["obj"]["meta"];
		foreach($this->_get_user_form_elements($meta["user_form"]) as $elid => $eln)
		{
			$ordbox = $sepbox = "";
			if (isset($meta["name_els"][$elid]))
			{
				$ordbox = html::textbox(array(
					"name" => "name_els_ord[$elid]",
					"value" => $meta["name_els_ord"][$elid],
					"size" => 5,
					"maxlength" => 5,
				));

				$sepbox = html::textbox(array(
					"name" => "name_els_post[$elid]",
					"value" => $meta["name_els_post"][$elid],
					"size" => 5,
					"maxlength" => 5,
				));

				
			};

			$checkbox = html::checkbox(array(
				"name" => "name_els[$elid]",
				"value" => $elid,
				"checked" => isset($meta["name_els"][$elid]),
			));

			$this->vars(array(
				"elname" => $eln,
				"elid" => $elid,
				"is_name_el" => checked($meta["name_els"][$elid]),
				"checkbox" => $checkbox,
				"ordbox" => $ordbox,
				"sepbox" => $sepbox,
			));
			$clist .= $this->parse("ELEMENT");
		}
		$this->vars(array(
			"ELEMENT" => $clist,
		));
		$tmp = array(
			"type" => "text",
			"caption" => $args["prop"]["caption"],
			"group" => $args["prop"]["group"],
			"value" => $this->parse("ELEMENTS"),
		);
		return array($args["prop"]["name"] => $tmp);
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row["parent"] = $parent;
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	////
	// !returns a list of form id's that the user selected as forms for list config object $id
	function get_forms_by_id($id)
	{
		$this->ob = $this->get_object($id);
		// here we gots to sort the forms as well
		$ar = new aw_array($this->ob["meta"]["user_form"]);
		uasort($ar->get(), array($this, "__uf_cmp"));
		return $this->ob["meta"]["user_form"];
	}

	function __uf_cmp($a, $b)
	{
		if ($this->ob["meta"]["form_jrk"][$a] > $this->ob["meta"]["form_jrk"][$b])
		{
			return -1;
		}
		else
		if ($this->ob["meta"]["form_jrk"][$a] < $this->ob["meta"]["form_jrk"][$b])
		{
			return 1;
		}
		return 0;
	}

	function get_folders_by_id($id)
	{
		$ret = array();
		$ob = $this->get_object($id);

		$ol = $this->get_menu_list();

		$ar = new aw_array($ob["meta"]["folders"]);
		foreach($ar->get() as $fid)
		{
			$ret+= array($fid => $ol[$fid]);
			$ml = $this->get_menu_list(false, false, $fid);
			foreach($ml as $mk => $mv)
			{
				$ret+= array($mk => $ol[$fid]."/".$mv);
			}
		}
		asort($ret);
		return $ret;
	}

	////
	// !returns an array of element id's whose values make up the name for the list member
	function get_name_els_by_id($id)
	{
		$ob = $this->get_object($id);
		$ar = new aw_array($ob["meta"]["name_els"]);
		return $ar->get();
	}

	function get_name_els_order($id)
	{
		$ob = $this->get_object($id);
		$ar = new aw_array($ob["meta"]["name_els_ord"]);
		return $ar->get();
	}

	function get_name_els_seps($id)
	{
		$ob = $this->get_object($id);
		$ar = new aw_array($ob["meta"]["name_els_post"]);
		return $ar->get();
	}

	function get_mailto_element($id)
	{
		$ob = $this->get_object($id);
		return $ob["meta"]["mailto_el"];
	}

	function get_user_search_form($id)
	{
		$ob = $this->get_object($id);
		return $ob["meta"]["user_search_form"];
	}

	function get_form_for_email_element($id)
	{
		$ob = $this->get_object($id);
		return $ob["meta"]["mailto_el_form"];	
	}
}
?>
