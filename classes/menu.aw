<?php
// $Header: /home/cvs/automatweb_dev/classes/menu.aw,v 2.8 2002/11/14 18:38:24 duke Exp $
// right now this class manages only the functios related to adding menu aliases
// to documents and tables. But I think that all functions dealing with a single
// menu should be moved here.

/*
	// stuff that goes into the objects table
	@default table=objects

	@property users_only type=checkbox field=meta method=serialize group=advanced
	@caption Ainult sisselogitud kasutajatele

	@property color type=colorpicker field=meta method=serialize group=advanced
	@caption Men�� v�rv

	@property icon type=icon field=icon group=advanced
	@caption Ikoon

	@property sort_by_name type=checkbox field=meta method=serialize group=advanced
	@caption Sorteeri nime j�rgi

	@property aip_filename type=textbox field=meta method=serialize group=advanced
	@caption Failinimi

	@property objtbl_conf type=select field=meta method=serialize group=advanced
	@caption Objektitabeli konff

	@property add_tree_conf type=select field=meta method=serialize group=advanced
	@caption Objekti lisamise puu konff

	@property cfgmanager type=select field=meta method=serialize group=advanced
	@caption Konfiguratsioonihaldur
	
	@property show_lead type=checkbox field=meta method=serialize group=advanced
	@caption N�ita ainult leadi (kasutusel N�dalas)
	
	@property grkeywords type=select size=10 multiple=1 field=meta method=serialize group=keywords
	@caption AW M�rks�nad

	@property keywords type=textbox field=meta method=serialize group=keywords
	@caption META keywords

	@property description type=textbox field=meta method=serialize group=keywords
	@caption META description
	
	@property sections type=select multiple=1 field=meta size=20 method=serialize group=relations
	@caption Vennastamine

	@property img_timing type=textbox size=3 field=meta method=serialize group=presentation
	@caption Viivitus piltide vahel (sek.)

	@property img_act type=imgupload field=meta method=serialize group=presentation
	@caption Aktiivse men�� pilt

	@property menu_images type=array field=meta method=serialize getter=callback_get_menu_image group=presentation
	@caption Men�� pildid

	// and now stuff that goes into menu table
	@default table=menu
	
	@property sss type=select multiple=1 size=15 method=serialize group=relations
	@caption Men��d, mille alt viimased dokumendid v�etakse
	
	@property pers type=select multiple=1 size=15 method=serialize group=relations
	@caption Perioodid, mille alt dokumendid v�etakse

	@property seealso type=select multiple=1 size=15 group=relations
	@caption Vali men��d, mille all see men�� on "vaata lisaks" men��

	@property seealso_order type=textbox group=relations size=3 table=objects field=meta method=serialize
	@caption J�rjekorranumber (vaata lisaks)

	@property link type=textbox group=show
	@caption Men�� link

	@property type type=select group=advanced
	@caption Men�� t��p

	@property clickable type=checkbox group=advanced
	@caption Klikitav
	
	@property no_menus type=checkbox group=advanced
	@caption Ilma men��deta

	@property target type=checkbox group=general
	@caption Uues aknas

	@property mid type=checkbox group=advanced
	@caption Paremal
	
	@property width type=textbox size=5 group=advanced
	@caption Laius
	
	@property is_shop type=checkbox group=advanced
	@caption Pood
	
	@property shop_parallel type=checkbox group=advanced
	@caption Kaubad sbs (pood)
	
	@property shop_ignoregoto type=checkbox group=advanced
	@caption Ignoreeri j�rgmist (pood)

	@default group=show

	@property left_pane type=checkbox 
	@caption Vasak paan

	@property right_pane type=checkbox
	@caption Parem paan
	
	@property tpl_dir table=objects type=select field=meta method=serialize
	@caption Template set 
	
	@property tpl_edit type=select 
	@caption Template muutmiseks
	
	@property tpl_view type=select
	@caption Template n�itamiseks (pikk)
	
	@property tpl_lead type=select
	@caption Template n�itamiseks (l�hike)
	
	@property hide_noact type=checkbox
	@caption Peida �ra, kui dokumente pole
	
	@property ndocs type=textbox size=3
	@caption Mitu viimast dokumenti

	@classinfo relationmgr=yes
	@classinfo objtable=menu
	@classinfo objtable_index=id
*/
class menu extends aw_template
{
	function menu($args = array())
	{
		$this->init(array(
			"tpldir" => "automatweb/menu",
			"clid" => CL_PSEUDO,
		));
	}
	
	function get_property($args)
	{
		$data = &$args["prop"];
		switch($data["name"])
		{
			case "objtbl_conf":
				$data["options"] = $this->list_objects(array("class" => CL_OBJ_TABLE_CONF, "addempty" => true));
				break;

			case "add_tree_conf":
				$data["options"] = $this->list_objects(array("class" => CL_ADD_TREE_CONF, "addempty" => true));
				break;

			case "cfgmanager":
				$data["options"] = $this->list_objects(array("class" => CL_CFGMANAGER, "addempty" => true));
				break;

			case "type":
				$m = get_instance("menuedit");
				$data["options"] = $m->get_type_sel();
				break;

			case "tpl_edit":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 0));
				break;
			
			case "tpl_lead":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 1));
				break;
			
			case "tpl_view":
				$tplmgr = get_instance("templatemgr");
				$data["options"] = $tplmgr->get_template_list(array("type" => 2));
				break;

			case "tpl_dir":
				$template_sets = $this->cfg["template_sets"];
				$data["options"] = array_merge(array("" => "kasuta parenti valikut"),$template_sets);
				break;
			
			case "sections":
				$ob = get_instance("objects");
				$data["options"] = $ob->get_list(false,true);
				$m = get_instance("menuedit");
				$data["selected"] = $m->get_brothers($args["obj"]["oid"]);
				break;
			
			case "sss":
				$ob = get_instance("objects");
				$data["options"] = $ob->get_list();
				break;

			case "pers":
				$dbp = get_instance("periods",$this->cfg["per_oid"]);
				$data["options"] = $dbp->period_list(false);
				break;

			case "grkeywords":
				$kwds = get_instance("keywords");
				$m = get_instance("menuedit");
				$data["options"] = $kwds->get_keyword_picker();
				$data["selected"] = $m->get_menu_keywords($args["obj"]["oid"]);
				break;

			case "seealso":
				// seealso asi on nyt nii. et esiteks on metadata[seealso_refs] - seal on
				// kirjas, mis menyyde all see menyy seealso item on
				// ja siis menu.seealso on nagu enne serializetud array menyydest mis
				// selle menyy all seealso on et n2itamisel kiirelt teada saax
				$sa = $args["obj"]["meta"]["seealso_refs"];
				$rsar = $sa[aw_global_get("lang_id")];
				$ob = get_instance("objects");
				$data["options"] = $ob->get_list();
				$data["value"] = $rsar;
				break;

			case "icon":
				$ext = $this->cfg["ext"];
				if ($args["objdata"]["icon_id"])
				{
					$icon = "<img src='$baseurl" . "/automatweb/icon.$ext" . "?id=" . $args[objdata][icon_id] . "'>";
				}
				else
				{
					$m = get_instance("menuedit");
					if ($args[objdata]["admin_feature"])
					{
						$icon = "<img src='" . $m->get_feature_icon_url($args["objdata"]["admin_feature"]) . "'>";
					}
					else
					{
						$icon = "(no icon set)";
					};
				};
				$data["value"] = $icon;
				break;

			case "img_act":
				$data["value"] = $args["obj"]["meta"]["img_act_url"] != "" ? "<img src='".$args[obj][meta][img_act_url]."'>" : "";
				break;

		};
	}

	////
	// !Callback for the edit form, returns the data for 
	// menu images (of which there can be 1..n)
	function callback_get_menu_image($args = array())
	{
		classload("image","html");
		$data = $args["prop"];
		static $i = 0;
		// each line consists of multiple elements
		// and this is where we create them
		if ($i < $this->cfg["num_menu_images"])
		{
			$tmp = array();
			$node = array();
			// do something
			$node["caption"] = "Pilt #" . ($i + 1);
			$node["items"] = array();

			$val = $data["value"][$i];
		
			// ord textbox
			$tmp = array(
				"type" => "textbox",
				"name" => "img_ord[$i]",
				"size" => 3,
				"value" => $val["ord"],
			);
			array_push($node["items"],$tmp);

			// delete checkbox
			$tmp = array(
				"type" => "checkbox",
				"name" => "img_del[$i]",
			);
			array_push($node["items"],$tmp);

			// file upload
			$tmp = array(
				"type" => "fileupload",
				"name" => "img" . $i,
			);
			array_push($node["items"],$tmp);

			// image preview
			$url = image::check_url($val["url"]);
			$tmp = array(
				"type" => "text",
				"value" => ($url) ? html::img(array("url" => $url)) : "",
			);
			array_push($node["items"],$tmp);

			$i++;
		}
		else
		{
			$node = false;
		};
		return $node;
	}

	function set_property($args = array())
        {
                $data = &$args["prop"];
		switch($data["name"])
                {
			case "grkeywords":
				$m = get_instance("menuedit");
				$m->save_menu_keywords($data["value"],$args["obj"]["oid"]);
				break;

                };
	}

	////
	// !Displays the form for adding a new menu alias
	function change_alias($args = array())
	{
		extract($args);
		$this->read_template("change_alias.tpl");
		if ($id)
		{
			$obj = $this->get_object($id);
			$this->dequote($obj);
			$title = "Muuda men�� linki";
		}
		else
		{
			$obj = array();
			$title = "Lisa men�� link";
		};
		$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / $title");
		$dbo = get_instance("objects");
		$olist = $dbo->get_list();

		$this->vars(array(
			"menu" => $this->picker($obj["last"],$olist),
			"reforb" => $this->mk_reforb("submit_alias",array("id" => $id,"parent" => $parent, "return_url" => $return_url, "alias_to" => $alias_to)),
		));
		return $this->parse();
	}

	function submit_alias($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($id)
		{
			$target = $this->get_object($menu);
			$name = $target["name"];
			$comment = $target["comment"];
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"last" => $menu,
			));
		}
		else
		{
			$id = $this->create_menu_alias($args);
		};

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}
		return $this->mk_my_orb("change_alias",array("id" => $id,"return_url" => urlencode($return_url)));
	}

	function create_menu_alias($args = array())
	{
		extract($args);
		$target = $this->get_object($menu);
		$name = $target["name"];
		$comment = $target["comment"];
		$id = $this->new_object(array(
			"parent" => $parent,
			"name" => $name,
			"comment" => $comment,
			"class_id" => CL_MENU_ALIAS,
			"last" => $menu,
		));
		$this->add_alias($parent,$id);
		return $id;
	}

	
	////
	// !Aliaste parsimine
	function parse_alias($args = array())
	{
		extract($args);
		if (!is_array($this->mcaliases))
		{
			$this->mcaliases = $this->get_aliases(array(
				"oid" => $oid,
				"type" => CL_PSEUDO,
			));
		};
		$f = $this->mcaliases[$matches[3] - 1];
		if (!$f["target"])
		{
			return "";
		}
		$target = $f;
		return sprintf("<a href='".$this->cfg["baseurl"]."/%d'>%s</a>",$target["oid"],$target["name"]);
	}

};
?>
