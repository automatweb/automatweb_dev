<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_packet.aw,v 1.9 2005/01/28 14:23:34 kristo Exp $
// shop_packet.aw - Pakett 
/*

@classinfo syslog_type=ST_SHOP_PACKET relationmgr=yes no_status=1 

@default table=objects
@default group=general

@property item_count type=hidden table=aw_shop_packets field=aw_count
@caption Mitu laos

@property separate_items type=checkbox ch_value=1 table=aw_shop_packets field=separate_items
@caption Kas tooted on eraldi

@property price type=textbox table=aw_shop_packets field=aw_price
@caption Hind

@groupinfo packet caption="Paketi sisu"

@property packet group=packet field=meta method=serialize type=table no_caption=1

@groupinfo data caption="Toote info"

@property user1 type=textbox table=aw_shop_packets field=user1 group=data
@caption User-defined 1

@property user2 type=textbox table=aw_shop_packets field=user2 group=data
@caption User-defined 2

@property user3 type=textbox table=aw_shop_packets field=user3 group=data
@caption User-defined 3

@property user4 type=textbox table=aw_shop_packets field=user4 group=data
@caption User-defined 4

@property user5 type=textbox table=aw_shop_packets field=user5 group=data
@caption User-defined 5

@property userta1 type=textarea table=aw_shop_packets field=tauser1 group=data
@caption User-defined ta 1

@property userta2 type=textarea table=aw_shop_packets field=tauser2 group=data
@caption User-defined ta 2

@property userta3 type=textarea table=aw_shop_packets field=tauser3 group=data
@caption User-defined ta 3

@property userta4 type=textarea table=aw_shop_packets field=tauser4 group=data
@caption User-defined ta 4

@property userta5 type=textarea table=aw_shop_packets field=tauser5 group=data
@caption User-defined ta 5

@property uservar1 type=classificator table=aw_shop_packets field=varuser1 group=data
@caption User-defined var 1

@property uservar2 type=classificator table=aw_shop_packets field=varuser2 group=data
@caption User-defined var 2

@property uservar3 type=classificator table=aw_shop_packets field=varuser3 group=data
@caption User-defined var 3

@property uservar4 type=classificator table=aw_shop_packets field=varuser4 group=data
@caption User-defined var 4

@property uservar5 type=classificator table=aw_shop_packets field=varuser5 group=data
@caption User-defined var 5

@groupinfo img caption="Pildid"

@property images type=releditor reltype=RELTYPE_IMAGE field=meta method=serialize mode=manager props=name,ord,status,file,file2,new_w,new_h group=img table_fields=name,ord table_edit_fields=ord
@caption Pildid

@tableinfo aw_shop_packets index=aw_oid master_table=objects master_index=brother_of
@reltype PRODUCT value=1 clid=CL_SHOP_PRODUCT
@caption paketi toode

@reltype IMAGE value=2 clid=CL_IMAGE
@caption pilt

*/

class shop_packet extends class_base
{
	function shop_packet()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_packet",
			"clid" => CL_SHOP_PACKET
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "packet":
				$this->do_packet_table($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "packet":
				$this->save_packet_table($arr);
				break;
		}
		return $retval;
	}	

	function _init_packet_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi"
		));

		$t->define_field(array(
			"name" => "count",
			"caption" => "Mitu paketis",
			"align" => "center"
		));
	}

	function do_packet_table(&$arr)
	{
		$pd = $arr["obj_inst"]->meta("packet_content");

		$this->_init_packet_table($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_PRODUCT)) as $c)
		{
			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"count" => html::textbox(array(
					"name" => "pd[".$c->prop("to")."]",
					"value" => $pd[$c->prop("to")],
					"size" => 5
				))
			));
		}
	}

	function save_packet_table(&$arr)
	{
		$arr["obj_inst"]->set_meta("packet_content", $arr["request"]["pd"]);
	}

	function get_price($o)
	{
		return number_format($o->prop("price"), 2);
	}

	/** returns the html for the product

		@comment

			uses the $layout object to draw the product $prod
			from the layout reads the template and inserts correct vars
			optionally you can give the $quantity parameter
	**/
	function do_draw_product($arr)
	{
		extract($arr);

		if (!$oc_obj)
		{
			$oc_obj_id = NULL;
		}
		else
		{
			$oc_obj_id = $oc_obj->id();
		}

		$sct = get_instance(CL_SHOP_ORDER_CART);

		$l_inst = $layout->instance();
		$l_inst->read_template($layout->prop("template"));
		$l_inst->vars(array(
			"name" => $prod->name(),
			"price" => $prod->prop("price"),
			"id" => $prod->id(),
			"quantity" => (int)($arr["quantity"]),
			"view_link" => obj_link($prod->id().":".$oc_obj->id())
		));

		$l_inst->vars(array(
			"printlink" => aw_global_get("REQUEST_URI")."&print=1"
		));

		$h_s_p = "";

		$prods = "";
		$pisets = array();
		$first = true;
		$p_cnt = 1;
		$pager = array();
		foreach($prod->connections_from(array("type" => "RELTYPE_PRODUCT")) as $c)
		{
			$w = $c->to();
			$w_i = $w->instance();
			$l_inst->vars(array(
				"prod_name" => $w->name(),
				"prod_price" => $w_i->get_price($w),
				"prod_link" => obj_link($prod->id().":".$oc_obj->id()),
				"prod_in_packet_link" => obj_link($prod->id().":".$oc_obj->id())."?prod=".$w->id(),
				"prod_num" => $p_cnt,
			));

			if ($GLOBALS["prod"] == $w->id() || (!$GLOBALS["prod"] && $first))
			{
				$itemd = $sct->get_item_in_cart($w->id());
				$clssf = get_instance(CL_CLASSIFICATOR);
				for ($i = 1; $i < 11; $i++)
				{
					if ($l_inst->template_has_var("sel_prod_uservar".$i."_edit"))
					{
						$html = html::select(array(
							"name" => "order_data[".$w->id()."][uservar".$i."]",
							"options" => $clssf->get_options_for(array(
								"clid" => $w->class_id(),
								"name" => "uservar".$i,
								"obj_inst" => $w,
							)),
							"selected" => $itemd["data"]["uservar".$i]
						));
						$l_inst->vars(array(
							"sel_prod_uservar".$i."_edit" => $html
						));
					}
				}

				for ($i = 1; $i < 6; $i++)
				{
					if ($l_inst->template_has_var("sel_prod_uservarm".$i."_edit"))
					{
						$tmp = $clssf->get_options_for(array(
							"clid" => $w->class_id(),
							"name" => "uservarm".$i,
							"obj_inst" => $w,
						));
						$options = array();
						$awa = new aw_array($w->prop("uservarm".$i));
						foreach($awa->get() as $v)
						{
							$options[$v] = $tmp[$v];
						}
						$html = html::select(array(
							"name" => "order_data[".$w->id()."][uservarm".$i."]",
							"options" => $options,
							"selected" => $itemd["data"]["uservarm".$i]
						));
						$l_inst->vars(array(
							"sel_prod_uservarm".$i."_edit" => $html,
						));
					}
				}

				$l_inst->vars(array(
					"sel_prod_id" => $w->id(),
					"sel_prod_name" => $w->name(),
					"sel_prod_quantity" => $itemd["quant"],
					"sel_prod_price" => $w_i->get_price($w),
					"sel_prod_userta2" => $w->prop("userta2")
				));
			}

			// insert images
			$l_inst->vars($pisets);
			$i = get_instance("image");
			$cnt = 1;
			$imgc = $w->connections_from(array("type" => "RELTYPE_IMAGE"));
			usort($imgc, create_function('$a,$b', 'return ($a->prop("to.jrk") == $b->prop("to.jrk") ? 0 : ($a->prop("to.jrk") > $b->prop("to.jrk") ? 1 : -1));'));
			foreach($imgc as $c)
			{
				$u = $i->get_url_by_id($c->prop("to"));
				$l_inst->vars(array(
					"prod_image".$cnt => image::make_img_tag($u, $c->prop("to.name")),
					"prod_image".$cnt."_url" => $u,
				));

				if ($GLOBALS["prod"] == $w->id() || (!$GLOBALS["prod"] && $first))
				{
					$l_inst->vars(array(
						"sel_prod_image".$cnt => image::make_img_tag($u, $c->prop("to.name")),
						"sel_prod_image".$cnt."_url" => $u,
						"sel_prod_image".$cnt."_onclick" => image::get_on_click_js($c->prop("to")),
						"sel_prod_more_img_url" => aw_url_change_var("view", 2)
					));
					$tstr = "SEL_PROD_HAS_OVER_".($cnt-1)."_IMAGES";
					$l_inst->vars(array(
						$tstr => $l_inst->parse($tstr)
					));
				}

				if ($u != "")
				{
					$l_inst->vars(array(
						"PROD_HAS_IMAGE_".$cnt => $l_inst->parse("PROD_HAS_IMAGE_".$cnt)
					));
					$pisets["PROD_HAS_IMAGE_".$cnt] = "";
					if ($GLOBALS["prod"] == $w->id() || (!$GLOBALS["prod"] && $first))
					{
						$l_inst->vars(array(
							"SEL_PROD_HAS_IMAGE_".$cnt => $l_inst->parse("SEL_PROD_HAS_IMAGE_".$cnt)
						));
					}
				}
				else
				{
					$l_inst->vars(array(
						"PROD_HAS_IMAGE_".$cnt => ""
					));
					if ($GLOBALS["prod"] == $w->id() || (!$GLOBALS["prod"] && $first))
					{
						$l_inst->vars(array(
							"SEL_PROD_HAS_IMAGE_".$cnt => ""
						));
					}
				}
				$cnt++;
			}

			if ($GLOBALS["prod"] == $w->id() || (!$GLOBALS["prod"] && $first))
			{
				$h_s_p = $l_inst->parse("HAS_SEL_PROD");
				$pager[] = $l_inst->parse("PROD_PAGER_SEL");
			}
			else
			{
				$pager[] = $l_inst->parse("PROD_PAGER");
			}

			$prods .= $l_inst->parse("PRODUCT");
			$first = false;
			$p_cnt++;

		}

		$l_inst->vars(array(
			"PRODUCT" => $prods,
			"reforb" => $this->mk_reforb("submit_add_cart", array("section" => aw_global_get("section"), "oc" => $oc_obj_id, "return_url" => aw_global_get("REQUEST_URI")), "shop_order_cart"),
			"PROD_PAGER" => join($l_inst->parse("PROD_PAGER_SEP"), $pager),
			"PROD_PAGER_SEL" => "",
			"PROD_PAGER_SEP" => ""
		));

		// insert images
		$i = get_instance("image");
		$cnt = 1;
		$imgc = $prod->connections_from(array("type" => "RELTYPE_IMAGE"));
		usort($imgc, create_function('$a,$b', 'return ($a->prop("to.jrk") == $b->prop("to.jrk") ? 0 : ($a->prop("to.jrk") > $b->prop("to.jrk") ? 1 : -1));'));
		foreach($imgc as $c)
		{
			$u = $i->get_url_by_id($c->prop("to"));
			$onc = image::get_on_click_js($c->prop("to"));

			$l_inst->vars(array(
				"image".$cnt => image::make_img_tag_wl($c->prop("to")),
				"image".$cnt."_url" => $u,
				"image".$cnt."_onclick" => $onc
			));
			
			if ($onc != "")
			{
				$l_inst->vars(array(
					"IMAGE".$cnt."_HAS_BIG" => $l_inst->parse("IMAGE".$cnt."_HAS_BIG")
				));
			}

			$l_inst->vars(array(
				"HAS_IMAGE_".$cnt => $l_inst->parse("HAS_IMAGE_".$cnt)
			));
			$cnt++;
		}

		if ($h_s_p != "")
		{
			$l_inst->vars(array(
				"HAS_SEL_PROD" => $h_s_p
			));
		}
		else
		{
			$l_inst->vars(array(
				"NO_SEL_PROD" => $l_inst->parse("NO_SEL_PROD")
			));
		}

		return $l_inst->parse();
	}

	function get_contained_products($o)
	{
		return array($o);
	}

	function request_execute($obj)
	{
		list($prod_id, $oc_id) = explode(":", aw_global_get("section"));
		$prod = obj($prod_id);

		// get layout from soc.
		$soc_o = obj($oc_id);
		$soc_i = $soc_o->instance();

		$layout = $soc_i->get_long_layout_for_prod(array(
			"soc" => $soc_o,
			"prod" => $prod
		));

		return $this->do_draw_product(array(
			"layout" => $layout,
			"prod" => $prod,
			"oc_obj" => $soc_o
		));
	}

	function get_must_order_num($o)
	{
		return 0;
	}
}
?>
