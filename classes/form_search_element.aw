<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_search_element.aw,v 2.6 2001/07/12 04:23:45 kristo Exp $

	class form_search_element extends form_element
	{
		function form_search_element()
		{
			$this->tpl_init("forms");
			$this->db_init();

			$this->entry_id = 0;
			$this->sub_merge = 1;
		}

		function gen_admin_html()
		{
			$this->read_template("admin_element.tpl");

			global $elements_created;

			if (!$elements_created)
			{
				// make javascript arrays for form elements
				$formcache = array(0 => "");
				$tarr = $this->form->get_search_targets();
				$tarstr = join(",",$this->map2("%s",$tarr));
				if ($tarstr != "")
				{
					$this->db_query("SELECT name, oid FROM objects WHERE status != 0 AND oid IN ($tarstr)");
					while ($row = $this->db_next())
					{
						$formcache[$row["oid"]] = $row["name"];
					}
				
					$el_num = 0;
					$this->db_query("SELECT objects.name as el_name, element2form.el_id as el_id,element2form.form_id as form_id FROM element2form LEFT JOIN objects ON objects.oid = element2form.el_id WHERE element2form.form_id IN ($tarstr)");
					while ($row = $this->db_next())
					{
						$this->vars(array(
							"el_num" => $el_num++,
							"el_id" => $row["el_id"],
							"el_text" => $row["el_name"],
							"form_id" => $row["form_id"]
						));
						$this->parse("ELDEFS");
					}
				}
				$this->vars(array("SEARCH_SCRIPT" => $this->parse("SEARCH_SCRIPT")));
				$GLOBALS["elements_created"] = true;
				$GLOBALS["formcache"] = $formcache;
			}

			$this->do_core_admin();

			// teeme formide listboxi ka
			global $formcache;
			$this->vars(array(
				"forms" => $this->picker($this->arr["linked_form"], $formcache),
				"linked_el" => $this->arr["linked_element"]
			));

			$this->vars(array("SEARCH_LB" => $this->parse("SEARCH_LB")));
			return $this->parse();
		}

		function save(&$arr)
		{
			$this->quote(&$arr);
			extract($arr);

			$ret = $this->do_core_save(&$arr);

			$base = "element_".$this->id;
			
			$var=$base."_form";
			$this->arr["linked_form"] = $$var;

			$var=$base."_element";
			$this->arr["linked_element"] = $$var;

			$this->arr["ver2"] = true;

			return $ret;
		}

		function gen_user_html_not($prefix = "",$elvalues = array(),$no_submit = false)		// function that doesn't use templates
		{
			if ($this->arr["ver2"])	// backward compatibility sucks ass, but whut can I do...
			{
				return $this->do_core_userhtml($prefix,$elvalues,$no_submit);
			}
			else
			{
				if ($this->arr["linked_element"] > 0)
				{
					$form = &$this->get_cached_form();

					$t = $form->get_element_by_id($this->arr["linked_element"]);

					if ($t)
					{
						$t->entry = $this->entry;
						$t->entry_id = $this->entry_id;
						if ($t->get_type() == 'listbox')
						{
							// add an empty element to the listbox so we can tell the difference, 
							// if nothing was selected and we can then ignore the lb in the search
							$t->arr["listbox_items"][$t->arr["listbox_count"]] = "";
							$t->arr["listbox_default"] = $t->arr["listbox_count"];
							$t->arr["listbox_count"]++;
						}
						if ($this->arr["text"] != "")
							$t->arr["text"] = $this->arr["text"];

						if (!($t->get_type() == 'file' || $t->get_type() == 'link'))
							return $t->gen_user_html_not(&$images);
						else
							return "";
					}
					else
					{
						return "";
					}
				}
				else
					return "";
			}
		}

		function process_entry(&$entry, $id)
		{
			if ($this->arr["ver2"])	// backward compatibility is a bitch
			{
				return $this->core_process_entry(&$entry,$id);
			}
			else
			{
				if (!$this->arr["linked_element"])
				{
					return;
				}

				$form = &$this->get_cached_form();
				$t = $form->get_element_by_id($this->arr["linked_element"]);
				if ($t->get_type() != "listbox")
				{
					$te = array();
					$t->process_entry(&$te, $id);
					$entry[$this->id] = $te[$this->arr["linked_element"]];
				}
				else
				{
					// check if the empty element that we added was selected and if it was, don't write anything to the db, 
					// so we can easily ignore the element in the search
					$var = $t->get_id();
					global $$var;

					if ($$var == "element_".$this->arr["linked_element"]."_lbopt_".$t->arr["listbox_count"])
					{
						$entry[$this->id] = "";
					}
					else
					{
						$entry[$this->id] = $$var;
					}
				}
				$this->entry = $entry[$this->id];
				$this->entry_id = $id;
			}
		}

		function gen_show_html()
		{
			if (!$this->entry_id)
				return "";

			$form = &$this->get_cached_form();
			$t = $form->get_element_by_id($this->arr["linked_element"]);
			$t->entry = $this->entry;
			$t->entry_id = $this->entry_id;
			return $t->gen_show_html();
		}

		function &get_cached_form()
		{
			global $formcache;
			if (!isset($formcache[$this->arr["linked_form"]]))
			{
				$formcache[$this->arr["linked_form"]] = new form;
				$formcache[$this->arr["linked_form"]]->load($this->arr["linked_form"]);
			}
			return $formcache[$this->arr["linked_form"]];
		}
	function get_type()		{	return $this->arr["type"]; }
	}
?>
