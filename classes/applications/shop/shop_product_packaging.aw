<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_product_packaging.aw,v 1.16 2005/05/09 13:31:53 kristo Exp $
// shop_product_packaging.aw - Toote pakend 
/*

@classinfo syslog_type=ST_SHOP_PRODUCT_PACKAGING relationmgr=yes

@default table=objects
@default group=general

@tableinfo aw_shop_packaging index=id master_table=objects master_index=brother_of
@default table=aw_shop_packaging

@property jrk type=textbox size=5 table=objects field=jrk
@caption J&auml;rjekord

@property price type=textbox size=5 field=aw_price
@caption Hind

@groupinfo data caption="Andmed"
@default group=data

@property user1 type=textbox field=user1 group=data
@caption User-defined 1

@property user2 type=textbox field=user2 group=data
@caption User-defined 2

@property user3 type=textbox field=user3 group=data
@caption User-defined 3

@property user4 type=textbox field=user4 group=data
@caption User-defined 4

@property user5 type=textbox field=user5 group=data
@caption User-defined 5

@property user5 type=textbox field=user5 group=data
@caption User-defined 5

@property user6 type=textbox field=user6 group=data
@caption User-defined 6

@property user7 type=textbox field=user7 group=data
@caption User-defined 7

@property user8 type=textbox field=user8 group=data
@caption User-defined 8

@property user9 type=textbox field=user9 group=data
@caption User-defined 9

@property user10 type=textbox field=user10 group=data
@caption User-defined 10

@property user11 type=textbox field=user11 group=data
@caption User-defined 11

@property user12 type=textbox field=user12 group=data
@caption User-defined 12

@property user13 type=textbox field=user13 group=data
@caption User-defined 13

@property user14 type=textbox field=user14 group=data
@caption User-defined 14

@property user15 type=textbox field=user15 group=data
@caption User-defined 15

@property userta1 type=textarea field=userta1 group=data
@caption User-defined ta 1

@property userta2 type=textarea field=userta2 group=data
@caption User-defined ta 2

@property userta3 type=textarea field=userta3 group=data
@caption User-defined ta 3

@property userta4 type=textarea field=userta4 group=data
@caption User-defined ta 4

@property userta5 type=textarea field=userta5 group=data
@caption User-defined ta 5


@property uservar1 type=classificator field=uservar1 group=data
@caption User-defined var 1

@property uservar2 type=classificator field=uservar2 group=data
@caption User-defined var 2

@property uservar3 type=classificator field=uservar3 group=data
@caption User-defined var 3

@property uservar4 type=classificator field=uservar4 group=data
@caption User-defined var 4

@property uservar5 type=classificator field=uservar5 group=data
@caption User-defined var 5

@property images type=releditor reltype=RELTYPE_IMAGE table=objects field=meta method=serialize mode=manager props=name,ord,status,file group=img
@caption Pildid

@property userch1 type=checkbox ch_value=1  field=userch1 group=data datatype=int
@caption User-defined checkbox 1

@property userch2 type=checkbox ch_value=1  field=userch2 group=data datatype=int
@caption User-defined checkbox 2

@property userch3 type=checkbox ch_value=1  field=userch3 group=data datatype=int
@caption User-defined checkbox 3

@property userch4 type=checkbox ch_value=1  field=userch4 group=data datatype=int
@caption User-defined checkbox 4

@property userch5 type=checkbox ch_value=1  field=userch5 group=data datatype=int
@caption User-defined checkbox 5


@reltype IMAGE value=1 clid=CL_IMAGE
@caption pilt 

*/

class shop_product_packaging extends class_base
{
	function shop_product_packaging()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_product_packaging",
			"clid" => CL_SHOP_PRODUCT_PACKAGING
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	/** returns the html for the product

		@comment

			uses the $layout object to draw the product packaging $prod
			from the layout reads the template and inserts correct vars
			optionally you can give the $quantity parameter
			$oc_obj must be the order center object via what the product is drawn
			
	**/
	function do_draw_product($arr)
	{
		extract($arr);
		$pr_i = get_instance(CL_SHOP_PRODUCT);

		$pi = $prod;
		$prod = reset($pi->connections_to(array(
			"from.class_id" => CL_SHOP_PRODUCT,
		)));
		$prod = $prod->from();
		$l_inst = $layout->instance();
		$l_inst->read_template($layout->prop("template"));

		$parent_fld = $pi;
		do
		{
			$parent_fld = obj($parent_fld->parent());
		}
		while($parent_fld->class_id() != CL_MENU && $parent_fld->parent());

		$soc = get_instance(CL_SHOP_ORDER_CART);
		$soc->get_cart($oc_obj);
		$inf = $soc->get_item_in_cart($pi->id());
		$ivs = array(
			"packaging_name" => $pi->name(),
			"packaging_price" => $this->get_price($pi),
			"packaging_id" => $pi->id(),
			"packaging_quantity" => (int)($arr["quantity"]),
			"packaging_view_link" => obj_link($pi->id().":".$oc_obj->id()),
			"name" => $prod->name(),
			"price" => $this->get_price($prod),
			"tot_price" => number_format(((int)($arr["quantity"]) * $this->get_calc_price($prod)), 2),
			"obj_price" => $this->get_price($pi),
			"obj_tot_price" => number_format(((int)($arr["quantity"]) * $this->get_calc_price($pi)), 2),
			"read_price_total" => number_format(((int)($arr["quantity"]) * str_replace(",", "", $inf["data"]["read_price"])), 2),
			"id" => $prod->id(),
			"trow_id" => "trow".$prod->id(),
			"err_class" => ($arr["is_err"] ? "class='selprod'" : ""),
			"quantity" => (int)($arr["quantity"]),
			"view_link" => obj_link($prod->id().":".$oc_obj->id()),
			"edit_link" => $this->mk_my_orb("change", array("id" => $prod->id()), $prod->class_id(), true),
			"obj_id" => $pi->id(),
			"obj_parent" => $parent_fld->id()
		);
		$l_inst->vars($ivs);
		$proc_ivs = $ivs;

		// insert images
		$i = get_instance("image");
		$cnt = 1;
		$imgc = $prod->connections_from(array("type" => "RELTYPE_IMAGE"));
		usort($imgc, create_function('$a,$b', 'return ($a->prop("to.jrk") == $b->prop("to.jrk") ? 0 : ($a->prop("to.jrk") > $b->prop("to.jrk") ? 1 : -1));'));
		foreach($imgc as $c)
		{
			$u = $i->get_url_by_id($c->prop("to"));
			$l_inst->vars(array(
				"image".$cnt => image::make_img_tag($u, $c->prop("to.name")),
				"image".$cnt."_url" => $u
			));
			$cnt++;
		}
		$imgc = $pi->connections_from(array("type" => "RELTYPE_IMAGE"));
		usort($imgc, create_function('$a,$b', 'return ($a->prop("to.jrk") == $b->prop("to.jrk") ? 0 : ($a->prop("to.jrk") > $b->prop("to.jrk") ? 1 : -1));'));
		foreach($imgc as $c)
		{
			$u = $i->get_url_by_id($c->prop("to"));
			$l_inst->vars(array(
				"packaging_image".$cnt => image::make_img_tag($u, $c->prop("to.name")),
				"packaging_image".$cnt."_url" => $u
			));
			$cnt++;
		}
		
		for($i = 1; $i < 21; $i++)
		{
			$tmp = $prod->prop("uservar".$i);
			if ($tmp)
			{
				$tmp = obj($tmp);
				$tmp = $tmp->name();
			}
			else
			{
				$tmp = "";
			}
			$tmp2 = $pi->prop("uservar".$i);
			if ($tmp2)
			{
				$tmp2 = obj($tmp2);
				$tmp2 = $tmp2->name();
			}
			else
			{
				$tmp2 = "";
			}

			$ui = $prod->prop("user".$i);
			if ($i == 16 && aw_ini_get("site_id") == 139 && $prod->prop("userch5"))
			{
				$ui = $pi->prop("user3");
			}

			$ivar = array(
				"user".$i => $ui,
				"userta".$i => nl2br($prod->prop("userta".$i)),
				"uservar".$i => $tmp,
				"packaging_user".$i => $pi->prop("user".$i),
				"packaging_userta".$i => nl2br($pi->prop("userta".$i)),
				"packaging_uservar".$i => $tmp2
			);

			if ($i < 6)
			{
				$ivar["userch".$i] = $prod->prop("userch".$i);
			}

			$l_inst->vars($ivar);
			$proc_ivs += $ivar;
		}
		$pr_i->_int_proc_ivs($proc_ivs, $l_inst);

		// order data
		$awa = new aw_array($inf["data"]);
		foreach($awa->get() as $datan => $datav)
		{
			if ($datan == "url")
			{
				$datav =str_replace("afto=1", "",$datav);
			}
			$vs = array(
				"order_data_".$datan => $datav
			);
			$l_inst->vars($vs);
			$proc_ivs += $vs;
		}
		$pr_i->_int_proc_ivs($proc_ivs, $l_inst);

		$tmp = $awa->get();
		if ($tmp["url"] != "")
		{
			$l_inst->vars(Array(
				"URL_IN_DATA" => $l_inst->parse("URL_IN_DATA")
			));
		}
		else
		{
			$l_inst->vars(Array(
				"NO_URL_IN_DATA" => $l_inst->parse("NO_URL_IN_DATA")
			));
		}

		$l_inst->vars(array(
			"logged" => (aw_global_get("uid") == "" ? "" : $l_inst->parse("logged"))
		));

		return $l_inst->parse();
	}

	function get_price($o)
	{
		return number_format($o->prop("price"),2);
	}

	function get_calc_price($o)
	{
		return $o->prop("price");
	}

	function get_prod_calc_price($o)
	{
		$c = reset($o->connections_to(array(
			"from.class_id" => CL_SHOP_PRODUCT,
			"type" => 2 // RELTYPE_PACKAGING
		)));
		if ($c)
		{
			$o = $c->from();
			return $o->prop("price");
		}
		return 0;
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
		$prod = reset($o->connections_to(array(
			"from.class_id" => CL_SHOP_PRODUCT,
		)));
		$prod = $prod->from();
		$prod_i = $prod->instance();
		return $prod_i->get_must_order_num($prod);
	}
}
?>
