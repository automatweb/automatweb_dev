<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/promo.aw,v 2.22 2003/01/23 14:11:13 duke Exp $
// promo.aw - promokastid.

/*
	@default group=general
	
	@property caption type=textbox table=objects field=meta method=serialize
	@caption Pealkiri

	@property tpl_lead type=select table=menu group=show
	@caption Template näitamiseks
	
	@property preview type=text group=preview table=objects field=meta method=serialize
	@caption Eelvaade

	@property tpl_edit type=select table=menu group=show
	@caption Template muutmiseks
	
	@property type type=select table=objects field=meta method=serialize
	@caption Kasti tüüp

	@property link type=textbox table=menu
	@caption Link
	
	@property link_caption type=textbox table=objects field=meta method=serialize
	@caption Lingi kirjeldus
	
	@default table=objects
	@default field=meta
	@default method=serialize

	@property all_menus type=checkbox ch_value=1 value=1 group=menus
	@caption Näita igal pool

	@property no_title type=checkbox ch_value=1 value=1 group=show
	@caption Ilma pealkirjata

	@property groups type=select multiple=1 size=15 group=show
	@caption Grupid, kellele kasti näidata
	
	@property section type=select multiple=1 size=15 group=menus
	@caption Vali menüüd, mille all kasti näidata
	
	@property last_menus type=select multiple=1 size=15 group=menus
	@caption Vali menüüd, mille alt viimaseid dokumente võetakse

	@property ndocs type=textbox size=4 group=menus
	@caption Mitu viimast dokumenti

	@classinfo objtable=menu
	@classinfo objtable_index=id

	@classinfo corefields=name,comment,status

	@tableinfo menu index=id master_table=objects master_index=oid

	@groupinfo general caption=Üldine default=1
	@groupinfo menus caption=Menüüd
	@groupinfo show caption=Näitamine
	@groupinfo preview caption=Eelvaade
			
*/
class promo extends class_base
{
	function promo()
	{
		$this->init(array(
			"clid" => CL_PROMO,
			"tpldir" => "promo",
		));
		lc_load("definition");
		$this->lc_load("promo","lc_promo");
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK; 
		switch($data["name"])
		{
			case "tpl_edit":
				// kysime infot adminnitemplatede kohta
				$q = "SELECT * FROM template WHERE type = 0 ORDER BY id";
				$this->db_query($q);
				$edit_templates = array();
				while($tpl = $this->db_fetch_row()) 
				{
					$edit_templates[$tpl["id"]] = $tpl["name"];
				};
				$data["options"] = $edit_templates;
				break;

			case "tpl_lead":
				// kysime infot lyhikeste templatede kohta
				$q = "SELECT * FROM template WHERE type = 1 ORDER BY id";
				$this->db_query($q);
				$short_templates = array();
				while($tpl = $this->db_fetch_row()) 
				{
					$short_templates[$tpl["id"]] = $tpl["name"];
				};
				$data["options"] = $short_templates;
				break;
	
			case "type":
				$data["options"] = array(
					"0" => "Vasakul",
					"1" => "Paremal",
					"2" => "Üleval",
					"3" => "All",
					"scroll" => "Skrolliv",
				);
				break;

			case "groups":
				$u = get_instance("users");
				$data["options"] = $u->get_group_picker(array(
					"type" => array(GRP_REGULAR,GRP_DYNAMIC),
				));
				break;

			case "last_menus":
				$ob = get_instance("objects");
				$menu = $ob->get_list();
				$menu[0] = "";
				$menu[$this->cfg["frontpage"]] = LC_PROMO_FRONTPAGE;
				$data["options"] = $menu;
				break;

			case "section":
				$ob = get_instance("objects");
				$menu = $ob->get_list();
				$menu[0] = "";
				$menu[$this->cfg["frontpage"]] = LC_PROMO_FRONTPAGE;
				$data["options"] = $menu;
				break;

			case "preview":
				// first, figure out which main.tpl to use
				// what a sorry way to do that
				$tpldir = $this->cfg["site_basedir"] . "/templates/automatweb/menuedit";
				// god, I hate myself
				$this->template_dir = $tpldir;
				$this->read_template("main.tpl");
				$templates = array(
					"" => "LEFT_PROMO",
					"0" => "LEFT_PROMO",
					"1" => "RIGHT_PROMO",
					"2" => "UP_PROMO",
					"3" => "DOWN_PROMO",
					"scroll" => "SCROLL_PROMO",
				);
				$use_tpl = $templates[$args["obj"]["meta"]["type"]];
				$tplsrc = $this->templates[$use_tpl];
				$m = get_instance("menuedit");
				$defs = new aw_array($m->get_default_document($args["obj"]["oid"],true));
				$pr_c = "";
				$menu = $this->get_menu($args["obj"]["oid"]);
				$tpl_lead = $menu["tpl_lead"];
				$tplmgr = get_instance("templatemgr");
				$tpl_filename = $tplmgr->get_template_file_by_id($tpl_lead);
				$data["value"] = "siia tuleb promokasti eelvaade, kui AW seda ükskord võimaldab";
				break;

		}
		return $retval;
	}

	function callback_pre_edit($args = array())
	{
		$id = $args["object"]["oid"];
		$menu = $this->get_menu($id);
		$check1 = aw_unserialize($menu["comment"]);
		$check2 = aw_unserialize($menu["sss"]);
		if (is_array($check1) || is_array($check2))
		{
			$convert_url = $this->mk_my_orb("convert",array());
			print "See objekt on vanas formaadis. Enne kui seda muuta saab, tuleb kõik süsteemis olevad promokastis uude formaati konvertida. <a href='$convert_url'>Kliki siia</a> konversiooni alustamiseks";
			exit;
		};
	}

	function callback_pre_save($args = array())
	{
		$objdata = &$args["objdata"];
		if (!$objdata["type"])
		{
			$objdata["type"] = MN_PROMO_BOX;
		};
	}

	function convert($args = array())
	{
		$q = "SELECT oid,name,comment,metadata,menu.sss FROM objects LEFT JOIN menu ON (objects.oid = menu.id) WHERE class_id = " . CL_PROMO;
		$this->db_query($q);
		// so, basically, if I load a CL_PROMO object and discover that it's
		// comment field is serialized - I will have to convert all promo
		// boxes in the system.

		// menu.sss tuleb ka unserialiseerida, saadud asjad annavad meile
		// last_menus sisu

		// so, how on earth do i make a callback into this class

		$convert = false;

		while($row = $this->db_next())
		{
			print "doing $row[oid]<br>";
			$this->save_handle();
			$meta_add = aw_unserialize($row["comment"]);
			$last_menus = aw_unserialize($row["sss"]);
			$meta = aw_unserialize($row["metadata"]);
			if (is_array($last_menus) || is_array($meta_add))
			{
				$convert = true;
			};
			$meta["last_menus"] = $last_menus;
			$meta["section"] = $meta_add["section"];
			if ($meta_add["right"])
			{
				$meta["type"] = 1;
			}
			elseif ($meta_add["up"])
			{
				$meta["type"] = 2;
			}
			elseif ($meta_add["down"])
			{
				$meta["type"] = 3;
			}
			elseif ($meta_add["scroll"])
			{
				$meta["type"] = "scroll";
			}
			else
			{
				$meta["type"] = 0;
			};
			$meta["all_menus"] = $meta_add["all_menus"];
			$comment = $meta_add["comment"];
			// reset sss field of menu table
			if ($convert)
			{
				$q = "UPDATE menu SET sss = '' WHERE id = '$row[oid]'";
				$this->db_query($q);

				$this->upd_object(array(
					"oid" => $row["oid"],
					"comment" => $comment,
					"metadata" => $meta,
				));
			};
			print "<pre>";
			print_r($meta);
			print "</pre>";
			$this->restore_handle();
			print "done<br>";
			sleep(1);
			flush();
		};
	}

	function parse_alias($args = array())
	{
		$alias = $args["alias"];
                $this->read_template("default.tpl");
                $me = get_instance("menuedit");
                $def = new aw_array($me->get_default_document($alias["target"],true));
                $content = "";
                $doc = get_instance("document");
                $mn = $this->get_menu($alias["target"]);
                $q = "SELECT filename FROM template WHERE id = '$mn[tpl_lead]'";
                $this->db_query($q);
                $row = $this->db_next();
                foreach($def->get() as $key => $val)
                {
                        $content .= $doc->gen_preview(array(
                                "docid" => $val,
                                "leadonly" => 1,
                                "tpl" => $row["filename"],
                                "showlead" => 1,
                                "boldlead" => 1,
                                "no_strip_lead" => 1,
                        ));
                };

                $this->vars(array(
                        "title" => $alias["name"],
                        "content" => $content,
                ));
                return $this->parse();

	}
}
?>
