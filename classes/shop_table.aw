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
				"parent_checked" => checked($st["table"]["rows"][$i]["parent"] == 1),
				"price_checked" => checked($st["table"]["rows"][$i]["price"] == 1),
				"bron_checked" => checked($st["table"]["rows"][$i]["bron"] == 1),
				"f_percent_checked" => checked($st["table"]["rows"][$i]["f_percent"] == 1),
				"money_checked" => checked($st["table"]["rows"][$i]["money"] == 1),
				"name_checked" => checked($st["table"]["rows"][$i]["name"] == 1),
				"i_id_checked" => checked($st["table"]["rows"][$i]["i_id"] == 1),
				"view_checked" => checked($st["table"]["rows"][$i]["view"] == 1),
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

		$by_item_type = $arr["type_id"] ? true : false;
		$by_item_id = !$by_item_type;

		if ($arr["item_id"])
		{
			$sht["table"]["item"] = $arr["item_id"];
		}

		if ($sht["table"]["item"])
		{
			$it = $this->get_item($sht["table"]["item"]);
		}

		if ($it || $type_id)
		{
			if (!$type_id)
			{
				$type_id = $it["type_id"];
			}
			$typ = $this->get_item_type($type_id);
			if ($it["cnt_form"])
			{
				$typ["cnt_form"] = $it["cnt_form"];
			}
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

		for ($i=0; $i < $sht["table"]["nnum_cols"]; $i++)
		{
			$this->vars(array(
				"title" => $sht["table"]["titles"][$i],
			));
			$he.=$this->parse("TITLE");
		}
		$this->vars(array("TITLE" => $he));

		// k6igepealt loeme kauba sisestuse sisse ja liidame selle iga kord andmetega kokku
		if ($by_item_id)
		{
			// kui teeme kauga kohta, siis loeme aint yhe
			$this->db_query("SELECT * FROM form_".$typ["form_id"]."_entries WHERE id = ".$it["entry_id"]);
			$itdata[$sht["table"]["item"]] = $this->db_next();
		}
		else
		{
			// a kui teeme kauba tyybi kohta, siis loeme k6ik selle tyybi sisestused
			$this->db_query("SELECT form_".$typ["form_id"]."_entries.*,shop_items.id as item_id FROM shop_items LEFT JOIN form_".$typ["form_id"]."_entries ON form_".$typ["form_id"]."_entries.id = shop_items.entry_id WHERE shop_items.type_id = $type_id");
			while ($row = $this->db_next())
			{
				$itdata[$row["item_id"]] = $row;
			}
		}

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

		if (!$group_by)
		{
			$group_by = "period";
		}

		if (!$clause)
		{
			// otsime itemi id j2rgi by default
			$clause = "item_id = ".$sht["table"]["item"];
		}

		if ($from && $to)
		{
			$time_clause = "AND (period >= $from AND period <= $to)";
			$time_clause2 = "AND (tto >= $from)";
		}

		if ($by_item_id)
		{
			// kui teeme yhe kauba kohta, siis t2hendab et perioodid on eraldi
			// leiame perioodide j2rgi kui palju kasutatud on

			$avail = array();
			$this->db_query("SELECT * FROM shop_item_period_avail WHERE item_id = ".$sht["table"]["item"]." $time_clause");
			while ($row = $this->db_next())
			{
				$avail[$row["period"]]["used"] = $row["num_sold"];
				$avail[$row["period"]]["total"] = $it["max_items"];
			}

			// ja nyt kui palju mis perioodil on
			$this->db_query("SELECT * FROM shop_item2per_prices WHERE item_id = ".$sht["table"]["item"]." AND per_type = ".PRICE_PER_WEEK." $time_clause2");
			while ($row = $this->db_next())
			{
				if ($row["max_items"] > 0)
				{
					$avail[$row["tfrom"]]["total"] = $row["max_items"];
				}
			}
		}
		else
		{
			// kui teeme kaubagrupi j2rgi, siis summeerime asjad iga kauba kohta kokku yle k6ikide perioodide
			$avail = array();
			$this->db_query("SELECT SUM(num_sold) as num_sold,item_id FROM shop_item_period_avail LEFT JOIN shop_items ON shop_items.id = shop_item_period_avail.item_id WHERE type_id = $type_id $time_clause GROUP BY $group_by");
			while ($row = $this->db_next())
			{
				$avail[$row["item_id"]]["used"] = $row["num_sold"];
			}

			// ja nyt kui palju mis perioodil on
			// oh puts. siin tuleb ju k6ikide itemite k6ik perioodid l2bi k2ia ja k2sici kokku liita sest
			// kui pole m22ratud perioodile kogust siis on see default kogus. 
			// siin tuleb liita ka need perioodid, kus pole miskit myydud mdx, kuna siis on lihtsalt t2ituvus 0 exole
			$this->db_query("SELECT shop_item2per_prices.*,shop_items.max_items as item_max_items FROM shop_item2per_prices LEFT JOIN shop_items ON shop_items.id = shop_item2per_prices.item_id WHERE type_id = $type_id $time_clause2 AND per_type = ".PRICE_PER_WEEK);
			while ($row = $this->db_next())
			{
				if ($row["max_items"] > 0)
				{
					$avail[$row["item_id"]]["total"] +=$row["max_items"];
				}
				else
				{
					$avail[$row["item_id"]]["total"] +=$row["item_max_items"];
				}
			}
		}

		$tos = join(",",$tosum);
		if ($tos != "")
		{
			$tos=",".$tos;
		}

		if ($from && $to)
		{
			$time_clause = "AND (order2item.period >= $from AND order2item.period <= $to)";
		}
		$q = "SELECT parent_object.name as parent_name,objects.name as item_name, order2item.*,AVG(order2item.price) as avg_price,SUM(order2item.price) as sum_price $tos FROM order2item LEFT JOIN form_".$typ["cnt_form"]."_entries ON form_".$typ["cnt_form"]."_entries.id = order2item.cnt_entry LEFT JOIN objects ON objects.oid = order2item.item_id LEFT JOIN objects as parent_object ON parent_object.oid = objects.parent WHERE $clause $time_clause AND parent_object.status != 0 AND objects.status != 0 GROUP BY ".$group_by;
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
								if ($by_item_id)
								{
//									echo "by iid period = ",$this->time2date($row["period"],5)," total = ", $avail[$row["period"]]["total"],"<br>";
									$i_total = $avail[$row["period"]]["total"];
									$i_used = $avail[$row["period"]]["used"];
								}
								else
								{
									$i_total = $avail[$row["item_id"]]["total"];
									$i_used = $avail[$row["item_id"]]["used"];
								}
								if ($elid == "used")
								{
									$vals[] = $i_total - $i_used;
								}
								else
								if ($elid == "total")
								{
									$vals[] = $i_total;
								}
								else
								if ($elid == "parent")
								{
									$vals[] = $row["parent_name"];
								}
								else
								if ($elid == "price")
								{
									$vals[] = $row["avg_price"];
								}
								else
								if ($elid == "bron")
								{
									$vals[] = $i_used;
								}
								else
								if ($elid == "f_percent")
								{
									if ($i_total < 1)
									{
										$vals[] = "0";
									}
									else
									{
										$vals[] = floor(((100.0 * $i_used) / $i_total)*100.0)/100.0;
									}
								}
								else
								if ($elid == "money")
								{
									$vals[] = $row["sum_price"];
								}
								else
								if ($elid == "name")
								{
									$vals[] = $row["item_name"];
								}
								else
								if ($elid == "i_id")
								{
									$vals[] = $row["item_id"];
								}
								else
								if (isset($row["ev_".$elid]))
								{
									$vals[] = $row["ev_".$elid];
								}
								else
								{
									$vals[] = $itdata[$row["item_id"]]["ev_".$elid];
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