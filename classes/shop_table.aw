<?php

global $orb_defs;
$orb_defs["shop_table"] = "xml";

classload("shop_base");
classload("shop_item");
class shop_table extends shop_base
{
	function shop_table()
	{
		$this->db_init();
		$this->tpl_init("shop");
	}

	function get_item_picker()
	{
		classload("objects");
		$ob = new objects;
		$menus = $ob->get_list();
		$items = $this->listall_items(ALL_PROPS);
		$ret = array();
		foreach($items as $iid => $irow)
		{
			if (isset($menus[$irow["parent"]]))
			{
				$ret[$iid] = $menus[$irow["parent"]]."/".$irow["name"];
			}
		}
		return $ret;
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add_table.tpl");
		$this->mk_path($parent, "Lisa poe tabel");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"items" => $this->picker(0,$this->get_item_picker())
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
			$sht = $this->get_table($id);
			$sht["table"]["item"] = $item;
			$sht["table"]["nnum_cols"] = $num_cols;
			$sht["table"]["rows"] = $els;
			$sht["table"]["titles"] = $title;
			$sht["table"]["start_el"] = $start_el;
			$this->save_table($sht);
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "name" => $name, "comment" => $comment, "class_id" => CL_SHOP_TABLE));
			$sht = array();
			$sht["item"] = $item;
			$sht["nnum_cols"] = $num_cols;
			$sht["titles"] = $title;
			classload("xml");
			$x = new xml;
			$co = $x->xml_serialize($sht);
			$this->quote(&$co);
			$this->db_query("INSERT INTO shop_tables(id,content) VALUES($id,'$co')");
		}

		return $this->mk_my_orb("change", array("id" => $id));
	}

	function get_table($id)
	{
		$this->db_query("SELECT objects.*,shop_tables.* FROM objects LEFT JOIN shop_tables ON shop_tables.id = objects.oid WHERE oid = $id");
		$ret = $this->db_next();
		classload("xml");
		$x = new xml;
		$ret["table"] = $x->xml_unserialize(array("source" => $ret["content"]));
		return $ret;
	}

	function save_table($sht)
	{
		$x = new xml;
		$co = $x->xml_serialize($sht["table"]);
		$this->quote(&$co);
		$this->db_query("UPDATE shop_tables SET content = '$co' WHERE id = ".$sht["id"]);
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add_table.tpl");
		$st = $this->get_table($id);
		$this->mk_path($st["parent"], "Muuda poe tabelit");

		$els = array();
		if ($st["table"]["item"])
		{
			$it = $this->get_item($st["table"]["item"]);
			if ($it)
			{
				$typ = $this->get_item_type($it["type_id"]);
				$f1 = new form;
				$f1->load($typ["form_id"]);
				$f2 = new form;
				$f2->load($typ["cnt_form"]);
				$els = $f1->get_all_elements();
				$tels = $f2->get_all_elements();
				foreach($tels as $telid => $telname)
				{
					$els[$telid] = $telname;
				}
			}
		}

		foreach($els as $elid => $elname)
		{
			$this->vars(array(
				"el_name" => $elname,
			));
			$he.=$this->parse("H_EL");
		}
		$this->vars(array("H_EL" => $he));

		for ($i=0; $i < $st["table"]["nnum_cols"]; $i++)
		{
			$el = "";
			$this->vars(array(
				"line_num" => $i,
				"title" => $st["table"]["titles"][$i]
			));
			foreach($els as $elid => $elname)
			{
				$this->vars(array(
					"el_id" => $elid,
					"el_name" => $elname,
					"checked" => checked($st["table"]["rows"][$i][$elid] == 1)
				));
				$el.=$this->parse("EL");
			};
			$this->vars(array(
				"EL" => $el,
				"tot_checked" => checked($st["table"]["rows"][$i]["total"] == 1),
				"used_checked" => checked($st["table"]["rows"][$i]["used"] == 1),
			));
			$li.=$this->parse("LINE");
		}
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"name" => $st["name"],
			"comment" => $st["comment"],
			"items" => $this->picker($st["table"]["item"],$this->get_item_picker()),
			"LINE" => $li,
			"num_cols" => $st["table"]["nnum_cols"],
			"els" => $this->picker($st["table"]["start_el"], $els),
			"view" => $this->mk_my_orb("show", array("id" => $id))
		));
		return $this->parse();
	}

	function show($arr)
	{
		extract($arr);
		$sht = $this->get_table($id);
		$this->read_template("show_table.tpl");
		$this->mk_path($sht["parent"], "Vaata tabelit");

		if ($sht["table"]["item"])
		{
			$it = $this->get_item($sht["table"]["item"]);
			if ($it)
			{
				$typ = $this->get_item_type($it["type_id"]);
				$f1 = new form;
				$f1->load($typ["form_id"]);
				$f2 = new form;
				$f2->load($typ["cnt_form"]);
				$els = $f1->get_all_elements();
				$tels = $f2->get_all_elements();
				foreach($tels as $telid => $telname)
				{
					$els[$telid] = $telname;
				}
			}
		}
		
		for ($i=0; $i < $sht["table"]["nnum_cols"]; $i++)
		{
			$this->vars(array(
				"title" => $sht["table"]["titles"][$i],
			));
			$he.=$this->parse("TITLE");
		}
		$this->vars(array("TITLE" => $he));

		// k6igepealt loeme kauba sisestuse sisse ja liidame selle iga kord andmetega kokku
		$this->db_query("SELECT * FROM form_".$typ["form_id"]."_entries WHERE id = ".$it["entry_id"]);
		$itdata = $this->db_next();

		// ysnaga, p2ring on order2item tabelisse, kus on kirjas itemite tellimused
		// filtreerime sealt v2lja aint 6ige itemi
		// grupeerime selle itemi ostmise kuup2erva j2rgi
		// edasi joinime kylge vajaliku tellimisformi sisestuste tabeli order2item.cnt_entry - seal on sisestuse id - j2rgi
		// ja sealt summeerime kokku vajalikud v2ljad 
		// lihtne exole :p
	
		// leiame elemendid, mida on vaja p2rida
		// k2ime kogu tabla l2bi ja tshekime v2lja
		$tosum = array();
		for ($i=0; $i < $sht["table"]["nnum_cols"]; $i++)
		{
			if (is_array($sht["table"]["rows"][$i]))
			{
				foreach($sht["table"]["rows"][$i] as $elid => $one)
				{
					if ($f2->get_element_by_id($elid))
					{
						$tosum[] = "SUM(form_".$typ["cnt_form"]."_entries.ev_".$elid.") AS ev_".$elid;
					}
				}
			}
		}

		// leiame perioodide j2rgi kui palju kasutatud on
		$avail = array();
		$this->db_query("SELECT * FROM shop_item_period_avail WHERE item_id = ".$sht["table"]["item"]);
		while ($row = $this->db_next())
		{
			$avail[$row["period"]]["used"] = $row["num_sold"];
			$avail[$row["period"]]["total"] = $it["max_items"];
		}

		// ja nyt kui palju mis perioodil on
		$this->db_query("SELECT * FROM shop_item2per_prices WHERE item_id = ".$sht["table"]["item"]." AND per_type = ".PRICE_PER_WEEK);
		while ($row = $this->db_next())
		{
			if ($row["max_items"] > 0)
			{
				$avail[$row["tfrom"]]["total"] = $row["max_items"];
			}
		}

		$q = "SELECT order2item.*,".join(",",$tosum)." FROM order2item LEFT JOIN form_".$typ["cnt_form"]."_entries ON form_".$typ["cnt_form"]."_entries.id = order2item.cnt_entry WHERE item_id = ".$sht["table"]["item"]." GROUP BY period";
//		echo "q = $q <Br>\n";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$col = "";
			for ($i=0; $i < $sht["table"]["nnum_cols"]; $i++)
			{
				$vals = array();
				if (is_array($sht["table"]["rows"][$i]))
				{
					foreach($sht["table"]["rows"][$i] as $elid => $one)
					{
						if ($one == 1)
						{
							if ($elid == $sht["table"]["start_el"])
							{
								$vals[] = $this->time2date($row["period"], 5);
							}
							else
							{
								if ($elid == "used")
								{
									$vals[] = $avail[$row["period"]]["total"] - $avail[$row["period"]]["used"];
								}
								else
								if ($elid == "total")
								{
									$vals[] = $avail[$row["period"]]["total"];
								}
								else
								if (isset($row["ev_".$elid]))
								{
									$vals[] = $row["ev_".$elid];
								}
								else
								{
									$vals[] = $itdata["ev_".$elid];
								}
							}
						}
					}
				}
				$this->vars(array(
					"content" => join(",",$vals)
				));
				$col.=$this->parse("COL");
			}
			$this->vars(array(
				"COL" => $col
			));
			$li.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $li));
		return $this->parse();
	}
}
?>