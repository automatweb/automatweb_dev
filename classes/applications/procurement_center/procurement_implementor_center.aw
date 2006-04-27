<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/procurement_implementor_center.aw,v 1.1 2006/04/27 08:14:37 kristo Exp $
// procurement_implementor_center.aw - Hanngete keskkond pakkujale 
/*

@classinfo syslog_type=ST_PROCUREMENT_IMPLEMENTOR_CENTER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_procurement_implementor_centers index=aw_oid master_index=brother_of master_table=objects

@default table=objects
@default group=general

@default group=p

	@property p_tb type=toolbar no_caption=1 store=no
	
	@layout p_l type=hbox width=30%:70%
		
		@property p_tr type=treeview no_caption=1 store=no parent=p_l

		@property p_tbl type=table no_caption=1 store=no parent=p_l

@groupinfo p caption="Hanked" submit=no

*/

class procurement_implementor_center extends class_base
{
	function procurement_implementor_center()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/procurement_implementor_center",
			"clid" => CL_PROCUREMENT_IMPLEMENTOR_CENTER
		));

		$this->model = get_instance("applications/procurement_center/procurements_model");
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "p_tb":
				$this->_p_tb($arr);
				break;

			case "p_tr":
				$this->_p_tr($arr);
				break;

			case "p_tbl":
				$this->_p_tbl($arr);
				break;
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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function _p_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$parent = $arr["request"]["p_id"];
		$po = obj($parent);
		if ($parent && $po->prop("state") == PROCUREMENT_PUBLIC)
		{
			$tb->add_menu_button(array(
				'name'=>'add_item',
				'tooltip'=> t('Uus')
			));
	
			$tb->add_menu_item(array(
				'parent'=>'add_item',
				'text'=> t('Pakkumine'),
				'link'=> html::get_new_url(CL_PROCUREMENT_OFFER, $parent, array("return_url" => get_ru(), "proc" => $parent))
			));
		}

		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud pakkumised'),
			'action' => 'delete_procurement_offers',
			'confirm' => t("Kas oled kindel et soovid valitud pakkumised kustudada?")
		));
	}

	function _p_tr($arr)
	{
		classload("core/icons");

		$ol = $this->model->get_my_procurements();
		foreach($ol->arr() as $o)
		{
			$arr["prop"]["vcl_inst"]->add_item(0, array(
				"id" => $o->id(),
				"name" => $arr["request"]["p_id"] == $o->id() ? "<b>".$o->name()."</b>" : $o->name(),
				"url" => aw_url_change_var("p_id", $o->id()),
			));
		}
	}

	function _init_p_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _p_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_p_tbl($t);

		$parent = $arr["request"]["p_id"];
		if (!$parent)
		{
			return;
		}

		$ol = new object_list(array(
			"class_id" => CL_PROCUREMENT_OFFER,
			"procurement" => $parent,
			"lang_id" => array(),
			"site_id" => array()
		));
		$t->data_from_ol($ol, array("change_col" => "name"));
	}
}
?>
