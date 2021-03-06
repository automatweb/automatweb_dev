<?php
// toolbar.aw - drawing toolbars
/*
@classinfo  maintainer=kristo
*/
class toolbar extends aw_template
{
	function toolbar($args = array())
	{
		$this->init("toolbar");
		$this->matrix = array();
		$this->custom_data = "";

		extract($args);
		if (empty($imgbase))
		{
			$imgbase = "/automatweb/images/icons";
		};

		$this->imgbase = $this->cfg["baseurl"] . $imgbase;
		$this->vars(array(
			"imgbase" => $this->cfg["baseurl"] . $imgbase,
		));

		$this->menus = array();

		$this->end_sep = array();
	}

	function _init_menu()
	{
		$this->menu_inited = true;
		$this->read_template("js_popup_menu.tpl");
	}
	/**
		@attrib params=name api=1

		@param name required type=string
			Name for the button
		@param img optional type=string
			An image location for the button
		@param tooltip optional type=string
			A text which is displayed while hovering over the button
		@param side optional type=bool
			If set to true, button is displayed on right side(by default its on left).
		@param load_on_demand_url optional type=string
			If set, the popup is loaded on demand from the given url
		@comment
			Adds a menu button, under where one can add menu items
		@examples
			$toolbar->add_menu_button(array(
				"name" => "delete",
				"tooltip" => t("Kustuta"),
				"img" => "delete.gif",
			));
			//Whitout img, new (green document) icon is used
	**/
	function add_menu_button($arr)
	{
		if (empty($this->menu_inited))
		{
			$this->_init_menu();
		};
		$name = $arr["name"];
		$arr["onClick"] = "return buttonClick(event, '${name}');";
		$arr["class"] = "menuButton";
		$arr["url"] = "";
		$arr["type"] = "button";
		if (empty($arr["img"]))
		{
			if(!empty($arr["text"]))
			{
				$arr["type"] = "text_button";
			}
			else
			{
				$arr["img"] = "new.gif";
			}
		};
		$arr["ismenu"] = 1;
		$arr["id"] = $name;
		$this->matrix[$arr["name"]] = $arr;
	}
	/**
		@attrib params=name api=1
		@param parent required type=string
			Name of the parent menu button under what the item is added
		@param name required type=string
			Name of the menu item
		@param text optional type=string
			Capture of the menu item.
		@param title
			Title of the menu item.
		@param link
			An URL, to where the item links to.
		@param action
			A action name which item shold trigger.
		@param onClick
			An onclick action.(javascript etc)
		@comment
			Adds menu item under specified menu button.
		@examples
			$toolbar->add_menu_button(array(
				"name" => "delete",
				"tooltip" => t("Kustuta"),
				"img" => "delete.gif",
			));
			$tmp->add_menu_item(array(
				"parent" => "delete",
				"text" => t("fail"),
				"title" => t("kustuta fail"),
				"link" => "http://www.www.www",
				"action" => "delete_file", // generates submit_changeform('delete_file')
			));
			// when both link and action are set, action overwrites link.
	**/
	function add_menu_item($arr)
	{
		global $mc_counter;
		$mc_counter++;

		$id = empty($arr["href_id"]) ? "" : " id=\"".$arr["href_id"]."\"";
		$onclick = empty($arr["onClick"]) ? "" : " onclick=\"{$arr["onClick"]}\"";

		if (!empty($arr["link"]))
		{
			$arr["url"] = $arr["link"];
		}

		if (isset($arr["action"]))
		{
			$arr["url"] = "javascript:submit_changeform('{$arr["action"]}');";
		}

		if (empty($arr["disabled"]))
		{
			$rv = "<a class=\"menuItem\" href=\"{$arr["url"]}\"{$id}{$onclick}>{$arr["text"]}</a>\n";
		}
		else
		{
			$rv = "<a class=\"menuItem\" href=\"\" title=\"{$arr["title"]}\"{$id} onclick=\"return false;\" style=\"color:gray\">{$arr["text"]}</a>\n";
		}

		if (isset($this->menus[$arr["parent"]]))
		{
			$this->menus[$arr["parent"]] .= $rv;
		}
		else
		{
			$this->menus[$arr["parent"]] = $rv;
		}
	}

	/**
		@attrib params=name api=1
		@param parent required type=string
			Menu button name, to where the separator should be added
		@comment
			Adds an separator for to specified menu button
		@examples
			$toolbar->add_menu_button(array(
				"name" => "delete",
				"tooltip" => t("Kustuta"),
				"img" => "delete.gif",
			));
			$tmp->add_menu_separator(array("parent"=>"tmp"));
			// adds just one lonely separator to the delete menu button

	**/
	function add_menu_separator($arr)
	{
		$this->menus[$arr["parent"]] .= '<div class="menuItemSep"></div>'."\n";
	}

	/**
		@attrib params=name api=1
		@param name required type=string
			Name of the submenu
		@param text optional type=string
			Text to display for the item
		@param parent required type=string
			Name of the item under what to add the submenu
		@param link optional type=string
			An URL, to where the item links to.
		@comment
			Adds an submenu to the toolbar menu item. Basically you can go to infinite depths(i think so).
		@examples
			$tmp->add_menu_button(array(
				"name" => "tmp",
				//"tooltip" => t("Kustuta"),
				//"img" => "delete.gif",
			));
			$tmp->add_menu_item(array(
				"parent" => "tmp",
				"text" => t("text"),
				"title" => t("tiitel"),
			));
			$tmp->add_sub_menu(array(
				"parent" => "tmp",
				"name" => "uu",
				"text" => t("suubmenuuu"),
			));
			$tmp->add_menu_item(array(
				"parent" => "uu",
				"text" => t("teine tekst"),
				"title" => t("teine tiitel"),
			));

	**/
	function add_sub_menu($arr)
	{
		$arr["sub_menu_id"] = $arr["name"];
		$baseurl = $this->cfg["baseurl"];
		$link = isset($arr["link"]) ? $arr["link"] : "";
		$rv = '<a class="menuItem menuItem_sub" href="'.$link.'" '.($link ? "" : 'onclick="return false;"').'
			        onmouseover="menuItemMouseover(event, \''.$arr["sub_menu_id"].'\');">
				<span class="menuItemText">'.$arr["text"].'</span>
				</a>';

		if (isset($this->menus[$arr["parent"]]))
		{
			$this->menus[$arr["parent"]] .= $rv;
		}
		else
		{
			$this->menus[$arr["parent"]] = $rv;
		}
	}

	function build_menus()
	{
		static $init_done = false;
		foreach($this->menus as $parent => $menudata)
		{
			if (false == $init_done)
			{
				$this->custom_data .= $this->parse("MENU_HEADER");
				$init_done = true;
			};
			$cdata = '<div id="'.$parent.'" class="menu" onmouseover="menuMouseover(event)">'."\n${menudata}</div>\n";
			// we add the toolbar html before </body> only in admin
			// actually the only toolbar that needs this is the aw object toolbar
			// because there's just too many elements for browser to handle.
			if ($_SERVER["PHP_SELF"]=="/automatweb/orb.aw")
			{
				$cache = get_instance("cache");
				$cache->file_set("aw_toolbars_".aw_global_get("uid"),$cache->file_get("aw_toolbars_".aw_global_get("uid")).$cdata );
			}
			else // for site use the old method
			{
				$this->custom_data .= $cdata;
			}
		};
	}

	/**
		@attribs params=name api=1
		@param name required type=string
			Name of the button
		@param tooltip required type=string
			Text to be displayed for the button
		@param caption optional type=string
			Text for button without icon, = tooltip, if not set
		@param img optional type=string
			Icon url to display.
		@param action optional type=string
			A action name which item shold trigger.
		@param url optional type=string
			An URL to where button should link to.
		@param target optional type=string
			Sets the links target.
		@param confirm optional type=string
			If is set, asks for confirmation displaying given text as question.
		@param onClick optional type=string
			If set, this javascript code etc is triggered on click of the button:)
		@comment
			Adds button to toolbar.
		@examples
			$tmp = get_instance("vcl/toolbar");
			$tmp->add_button(array(
				"name" => "neti",
				"url" => "http://www.neti.ee",
				"tooltip" => t("neti.ee"),
				"confirm" => t("Oled sa kindel et tahad ikka sinna saidile minna?"),
			));
			// adds a button what links after confirmation dialog to neti.ee. Because img isn't set, tooltip is shown instead.
	**/
	function add_button($args = array())
	{
		if(!isset($args["name"]))
		{
			$e = new awex_awtlb_btn_cfg("Missing required parameter 'name'!");
			throw $e;
		}
		$args["type"] = "button";
		if (empty($args["img"]))
		{
			$args["type"] = "text_button";
		};
		if (isset($args["action"]))
		{
			$args["url"] = "javascript:submit_changeform('$args[action]');";
		};
		if (empty($args["target"]) && !empty($this->button_target))
		{
			$args["target"] = $this->button_target;
		};
		if (isset($args["confirm"]))
		{
			$args["onClick"] = "if(!confirm('$args[confirm]')) { return false; };".(isset($args["onClick"]) ? $args["onClick"] : "");
		};
		if (isset($args["href_id"]))
		{
			$args["href_id"] = "id=\"".$args["href_id"]."\"";
		};

		$this->matrix[$args["name"]] = $args;
	}

	/**
		@attrib params=pos api=1
		@param nm required type=string
			Item name/id to be removed from toolbar. Buttons have string names, separators and other items numeric id-s.
		@comment
			Removes given item (button, separator, etc.) from toolbar
	**/
	function remove_button($nm)
	{
		unset($this->matrix[$nm]);
	}

	/**
		@attrib params=name api=1
		@param side optional type=bool
			If set to true, shows the separator on the right. By default on left.
		@comment
			Adds separator to the toolbar.
	**/
	function add_separator($args = array())
	{
		$args["type"] = "separator";
		$this->matrix[] = $args;
	}

	/**
		@attrib params=pos api=1
		@param content required type=string
			Text to be displayed.
		@param side optional type=bool
			If set to true, text will be displayed on the right. By default on left.
		@comment
			Adds a simple text to the toolbar.
	**/
	function add_cdata($content,$side = "")
	{
		$args = array(
			"type" => "cdata",
			"data" => $content,
			"side" => $side,
		);
		$this->matrix[] = $args;
	}

	/**
		@attrib params=name api=1
		@param id optional type=string
			If set, the value if this is added to the names of all elements. This allows us to have multiple toolbars on a page (names won't repeat).
		@param target optional
			Sets target for menu item links (overrides previously set targets).
		@param no_target optional
			If set, 'target' param will be ignored(whats the point?).
		@comment
			Generetes and finalizes the toolbar
		@returns
			Returns code of the toolbar
		@examples
			$tmp = get_instance("vcl/toolbar");
			$tmp->add_button(array(
				"name" => "uus",
				"url" => "http://www.neti.ee",
				"tooltip" => t("Kustuta"),
			));
			print $tmp->get_toolbar(array(
				"no_target" => true,
				"target" => "a",
			));
			// prints a toolbar with one item on it called kustuta, which openes neti.ee in new window(or in a frame if there is one named 'a').
	**/
	function get_toolbar($args = array())
	{
		if (!empty($this->menu_inited))
		{
			$this->build_menus();
		}
		$matrix = new aw_array($this->matrix);
		$tpl = "buttons.tpl";
		$this->read_template($tpl);
		$this->vars(array('align' => isset($this->align) ? $this->align : 'left'));
		$result = $this->parse("start");
		$right_side_content = "";
		foreach($matrix->get() as $val)
		{
			$side = !empty($val["side"]) ? "right" : "left";
			switch($val["type"])
			{
				case "button":
				case "text_button":
					if (isset($args["id"]))
					{
						$val["name"] .= $args["id"];
					};
					if (empty($args["no_target"]))
					{
						$val["target"] = isset($args["target"]) ? $args["target"] : (isset($val["target"]) ? $val["target"] : null);
					}

					if (empty($val["onClick"]))
					{
						$val["onClick"] = "";
					};

					if (empty($val["tooltip"]))
					{
						$val["tooltip"] = "";
					}

					if(empty($val["caption"]))
					{
						$val["caption"] = $val["tooltip"];
					}

					$val["url_q"] = str_replace("'", "\\'", ifset($val, "url"));
					$disabled = !empty($val["disabled"]) ? "_disabled" : "";

					if (!empty($val["img"]))
					{
						$val["img_url"] = substr($val["img"], 0, 4) === "http" ? $val["img"] : $this->imgbase."/".$val["img"];
					}

					if (!empty($val["load_on_demand_url"]))
					{
						static $tb_lod_num;
						$tb_lod_num++;
						$val["lod_name"] = $val["name"];
						$val["tb_lod_num"] = $tb_lod_num;
					}

					$this->vars($val);
					$tpl = $val["type"] . $disabled;

					if (!empty($val["ismenu"]))
					{
						if($val["type"] === "text_button")
						{
							$tpl = "text_menu_button";
						}
						else
						{
							$tpl = "menu_button";
						}
					}
					if (!empty($val["load_on_demand_url"]))
					{
						$tpl = "menu_button_lod";
					}

					if ($side === "left")
					{
						$result .= $this->parse($tpl);
					}
					else
					{
						$right_side_content .= $this->parse($tpl);
					};
					break;

				case "separator":
					$this->vars($val);
					if ($side === "left")
					{
						$result .= $this->parse("separator");
					}
					else
					{
						$right_side_content .= $this->parse("separator");
					};
					break;

				case "cdata":
					$this->vars($val);
					if ($side === "left")
					{
						$result .= $this->parse("cdata");
					}
					else
					{
						$right_side_content .= $this->parse("cdata");
					};
					break;
			};
		};

		$result .= $this->parse("end");
		if (count($this->end_sep) > 0)
		{
			foreach($this->end_sep as $ese)
			{
				$this->vars($ese);
				$result .= $this->parse("end_sep");
			}
		}

		if (!empty($right_side_content))
		{
			$this->vars(array(
				"right_side_content" => $right_side_content,
			));
			$result .= $this->parse("right_side");
		}

		$result .= $this->parse("real_end") . $this->custom_data;
		return $result;
	}

	function init_vcl_property($arr)
	{
		$name = $arr["property"]["name"];
		$vcl_inst = $this;
		$res = $arr["property"];
		$res["vcl_inst"] = &$vcl_inst;
			// for backwards compatibility
		$res["toolbar"] = &$vcl_inst;
		// if quicksearch is set, add that
		if (!empty($arr["property"]["quicksearch"]))
		{
			$clss = aw_ini_get("classes");
			$clp = array();
			foreach((array)$arr["property"]["quicksearch"] as $cldef)
			{
				$clid = @constant($cldef);
				$clp[$clid] = $clss[$clid]["name"];
			}
			$clss = $this->picker("", $clp);

			$url = $this->mk_my_orb("redir_search", array("url" => get_ru(), "MAX_FILE_SIZE" => 100000), "aw_object_search");
			$sb = "<input type=text size=10 name=tb_quicksearch> <select name=tb_qs_clid>".$clss."</select> <input type=button onClick='changed=0;window.location=\"".$url."&s_name=\"+document.changeform.tb_quicksearch.value+\"&s_clid=\"+document.changeform.tb_qs_clid.options[document.changeform.tb_qs_clid.selectedIndex].value' value='".t("Otsi")."'>";

			$sb = '<div nowrap class="tb_but" onMouseOver="this.className=\'tb_but_ov\'" onMouseOut="this.className=\'tb_but\'" onMouseDown="this.className=\'tb_but_ov\'" onMouseUp="this.className=\'tb_but\'">'.$sb.'</div>';

			$vcl_inst->add_cdata($sb, true);
		}

		$rv = array($name => $res);
		return $rv;
	}

	/** Adds a button to the toolbar for adding objects
		@attrib api=1 params=pos

		@param clids required type=array
			Array of class_id's that can be added via the button

		@param pt required type=oid
			Parent where to add the objects to

		@param rt optional type=int
			The relation type to connect the new object with. currently myst be integer :(

		@param params optional type=array
			If set, these will get added to the new object links
	**/
	function add_new_button($clids, $pt, $rt = null, $params = null)
	{
		if (!is_array($params))
		{
			$params = array();
		}
		$params["return_url"] = get_ru();
		$clss = aw_ini_get("classes");

		if ($rt)
		{
			$params["alias_to"] = $pt;
			$params["reltype"] = $rt;
		}
		if (count($clids) == 1)
		{
			$clid = reset($clids);
			$this->add_button(array(
				"name" => "new",
				"img" => "new.gif",
				"url" => html::get_new_url($clid, $pt, $params),
				"tooltip" => sprintf(t("Lisa %s"), $clss[$clid]["name"]),
			));
		}
		else
		{
			$this->add_menu_button(array(
				"name" => "new",
				"img" => "new.gif",
				"tooltip" => t("Lisa")
			));
			foreach($clids as $clid)
			{
				$this->add_menu_item(array(
					"parent" => "new",
					"text" => $clss[$clid]["name"],
					"url" => html::get_new_url($clid, $pt, $params)
				));
			}
		}
	}

//eksperimendi m6ttes praegu... m6nes kohas kasutan sellist, kuid eks n2is kas 6igustab ennast
	/** Adds a javascript button to the toolbar for adding objects
		@attrib api=1 params=pos
		@param clid required type=int
			Class_id's that can be added via the button
		@param parent optional type=oid
			Parent where to add the objects to. parent_var or parent or both must be set. If both, then parent is default
		@param parent_var optional type=string
			Variable name
		@param refresh optional type=array
			Properties to refresh after adding new object
		@param refresh_layout optional type=array
			Layouts to refresh after adding new object
		@param promts optional type=array
			Properties to ask for new object
			array("Name" => t("Sisesta uue objekti nimi"))
		@param tooltip optional type=string
			Tooltip for the button
		@param connect optional type=int
			Connection id from parent to new object
		@example
			$tb->add_js_new_button(array(
				"parent" => $arr["obj_inst"]->id(),
				"clid" => CL_PRODUCT_BRAND,
				"refresh" => array("brand_list"),
				"promts" => array("name" => t("Sisesta uue objekti nimi")),
			));
	**/
	function add_js_new_button($arr)
	{
		load_javascript('reload_properties_layouts.js');
		$js = "";
		foreach($arr["promts"] as $prop => $text)
		{
			$js.= "var ".$prop." = prompt('".$text."');\n";
		}

		if(!empty($arr["parent"]))
		{
			$js.= "var parent='".$arr["parent"]."';";
		}

		if(!empty($arr["parent_var"]))
		{
			$js.= "if(get_property_data['".$arr["parent_var"]."'] > 1)
			{
				var parent = get_property_data['".$arr["parent_var"]."'];
			}";
		}

		$js.= "$.get('/automatweb/orb.aw', {
			class: 'menu',
			action: 'create_new_object',
			parent: parent,
			clid: '".$arr["clid"]."'";

		if(!empty($arr["connect"]))
		{
			$js.= ", connect : '".$arr["connect"]."'\n";
		}


		foreach($arr["promts"] as $prop => $text)
		{
			$js.= ", ".$prop." : ".$prop."\n";
		}

		$js.= "}, function (html) {\n";
			if(isset($arr["refresh"]) && is_array($arr["refresh"]))
			{
				$js.= "reload_property(['".join("','" , $arr["refresh"])."']);\n";
			}
			
			if(isset($arr["refresh_layout"]) && is_array($arr["refresh_layout"]))
			{
				$js.= "reload_layout(['".join("','" , $arr["refresh_layout"])."']);\n";
			}
		$js.= "}
		);
		";

		$this->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"url" => "javascript:;",
			"onClick" => $js,
			"tooltip" => !empty($arr["tooltip"]) ? $arr["tooltip"] : null,
		));
	}

	/** Adds a delete objects button to the toolbar
		@attrib api=1
		@comment
			Objects to be deleted are passed by array of id-s in 'sel' or 'check' request variable
	**/
	function add_delete_button()
	{
		$this->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_objects",
			"tooltip" => t("Kustuta valitud objektid"),
			"confirm" => t("Oled kindel et soovit valitud objektid kustutada?")
		));

	}

	/** Adds a delete relations button to the toolbar
		@attrib api=1
		@comment
			Objects to be disconnected are passed by array of id-s in 'sel' or 'check' request variable
	**/
	function add_delete_rels_button()
	{
		$this->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_rels",
			"tooltip" => t("Kustuta valitud seosed"),
			"confirm" => t("Oled kindel et soovid valitud seosed kustutada?")
		));
	}

	/** Adds a delete relations button to the toolbar
		@attrib api=1
	**/
	function add_delete_rels_button_rel_id()
	{
		$this->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "delete_rels_id",
			"tooltip" => t("Kustuta valitud seosed"),
			"confirm" => t("Oled kindel et soovid valitud seosed kustutada?")
		));
	}

	/** Adds the save button to the toolbar
		@attrib api=1
	**/
	function add_save_button($arr = array())
	{
		$this->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"action" => "",
			"tooltip" => t("Salvesta")
		));
	}

	/** Adds search button to the toolbar
		@attrib api=1

		@param name optional type=string
			Name for button. Required if you want many of those on one toolbar

		@param tooltip optional type=string default=Otsi
			Custom tooltip for button. Defaults to "Otsi"

		@param pn required type=string
			The html element name to stick the search results to

		@param multiple optional type=bool
			If the element is a multiple select

		@param clid optional type=array
			The class id to search

		@param confirm optional type=string
			javascript confirmation popup caption

		@examples
			function _get_my_toolbar($arr)
			{
				$tb = &$arr["prop"]["vcl_inst"];
				$tb->add_search_button(array(
					'name' => 'something',
					'pn' => 'add_something',
					'clid' => CL_SOME_CLASS_ID
				));
			}

			function _set_my_toolbar($arr)
			{
				if($add = $arr["request"]["add_something"])
				{
					$tmp = explode(",", $add);
					foreach($tmp as $oid)
					{
						if(!$arr["obj_inst"]->is_connected_to(array("to" => $oid)))
						{
							$arr["obj_inst"]->connect(array(
								"type" => "RELTYPE_SOME_RELTYPE",
								"to" => $oid,
							));
						}
					}
				}
			}

			function callback_mod_reforb($arr)
			{
				$arr["add_something"] = 0;
			}
	**/
	function add_search_button($arr)
	{
		$url = $this->mk_my_orb("do_search", $arr, "popup_search");
		$s = !empty($arr['tooltip']) ? $arr['tooltip'] : t("Otsi");
		$this->add_button(array(
			"name" => !empty($arr["name"]) ? $arr["name"] : "search",
			"img" => "search.gif",
			"url" => "javascript:aw_popup_scroll('$url','$s',".popup_search::PS_WIDTH.",".popup_search::PS_HEIGHT.")",
			"tooltip" => $s
		));
		/* We can't use this, cuz the HTML that popup_search::get_popup_search_link provides, differs from the one we get from toolbar::add_button.
		$i = get_instance("vcl/popup_search");
		$this->add_cdata($i->get_popup_search_link($arr));
		*/
	}

	/** Adds a generic cut button to the toolbar that moves selected objects parents around
		@attrib api=1 params=name

		@param var required type=string
			An unique name for the cut buffer

	**/
	function add_cut_button($ar)
	{
		$this->add_button(array(
			"name" => "cut",
			"img" => "cut.gif",
			"action" => "generic_cut",
			"tooltip" => t("L&otilde;ika")
		));
		$GLOBALS["tb"]["_add_var"] = $ar["var"];
	}

	/** Adds a generic paste button, that only supports pasting from generic cut button
		@attrib api=1 params=pos

		@param var required type=string
			The cut buffer name

		@param folder_var required type=string
			The name of the variable from the request that contains the folder to paste to
	**/
	function add_paste_button($ar)
	{
		if (is_array($_SESSION["tb_cuts"][$ar["var"]]) && count($_SESSION["tb_cuts"][$ar["var"]]))
		{
			$this->add_button(array(
				"name" => "paste",
				"img" => "paste.gif",
				"action" => "generic_paste",
				"tooltip" => t("Kleebi")
			));
			$GLOBALS["tb"]["_paste_var"] = $ar["folder_var"];
		}
	}

	function callback_mod_reforb($arr)
	{
		if (!empty($GLOBALS["tb"]["_add_var"]))
		{
			$arr["tb_cut_var"] = $GLOBALS["tb"]["_add_var"];
		}

		if (!empty($GLOBALS["tb"]["_paste_var"]))
		{
			$arr["tb_paste_var"] = $GLOBALS["tb"]["_paste_var"];
		}
	}
}

/* Generic toolbar exception */
class awex_awtlb extends aw_exception {}

/* Indicates button configuration errors */
class awex_awtlb_btn_cfg extends awex_awtlb {}

?>
