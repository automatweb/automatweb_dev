<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/purchase.aw,v 1.2 2006/08/22 15:33:58 markop Exp $
// purchase.aw - Ost 
/*

@classinfo syslog_type=ST_PURCHASE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta

@property date type=date_select
@caption Kuupäev

@property buyer type=relpicker reltype=RELTYPE_BUYER
@caption Ostja

@property bargainer type=relpicker reltype=RELTYPE_BUYER
@caption Hankija

@property stat type=select 
@caption Staatus

@groupinfo offers caption=Pakkumised 
@default group=offers

@property offers_add type=toolbar no_caption=1 store=no
@property offers type=table no_caption=1 store=no reltype=RELTYPE_OFFER

@groupinfo files caption=Failid 
@default group=files

@property files type=fileupload no_caption=1 store=yes table=objects field=meta method=serialize form=+emb
@caption Failid 

@groupinfo purchases caption=Ostud 
@default group=purchases

@property purchases type=table no_caption=1


Ost on selline objektitüüp, mis seob omavahel Ostja (reeglina meie ise), Hankija ning mingid pakkumised. Esialgu piisab järgmistest propertytest (edaspidi on võimalik Ostu juures Pakkumise ridade kuvamine ja kuupäevade valimine, millal mingi Ostu osa täideti):
Nimetus (siia kirjutatakse Ostu number)
Kuupäev
Ostja (vaikimisi minu organisatsioon, kuid saab otsida ka teisi)
Hankija (seos tarne teinud organisatsiooniga) 
Staatus (aktiivne, arhiveeritud)
Pakkumised (eraldi TAB, mille all on näha kõik pakkumised, mis on selle Ostuga seotud). Tabelis pakkumise kuupäev, pakkumise failid, vali. Toolbaril otsing, mille abil saab seostada teisi sama Hankija pakkumisi selle Ostu juurde. Samuti kustutamine (saab juba seostatud pakkumise eemaldada Ostu küljest).
Failid (eraldi TAB) ? saab uploadida samamoodi faile nagu Pakkumise juurde
Ostud (kuvatakse pakkumise read, mis on selle ostuga seotud)

@reltype BUYER value=1 clid=CL_CRM_PERSON,CL_CRM_COMPANY
@caption Ostja 

@reltype OFFER value=2 clid=CL_PROCUREMENT_OFFER
@caption Pakkumine 

*/
class purchase extends class_base
{
	function purchase()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/purchase",
			"clid" => CL_PURCHASE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "buyer":
				if(!$arr["obj_inst"] -> prop("buyer"))
				{
					$u = get_instance(CL_USER);
					$co = obj($u->get_current_company());
					$prop["options"][$co->id()] = $co->name();
					$prop["value"] = $co->id();
				}
				break;
			case "stat":
				$prop["options"] = array(0 => "aktiivne" , 1 => "arhiveeritud");
				break;
			case "files":
				$file_inst = get_instance(CL_FILE);
				//$url = $file_inst->get_url($arr["obj_inst"]->prop($prop["name"]));
				$prop["value"] = "";
				foreach($arr["obj_inst"]->prop("files") as $id)
				{
					$caption = "";
					if(is_oid($id))
					{
						$file_obj = obj($id);
						$caption = $file_obj->name();
						$prop["value"].= html::href(array("url" => $file_inst->get_url($id,$name), "caption" => $caption))."<br>";
					}
				}
				break;
			case "offers_add":
				$tb =&$arr["prop"]["vcl_inst"];
				$pps = get_instance("vcl/popup_search");

				$search_url = $this->mk_my_orb("do_search", array(
						"id" => $arr["obj_inst"]->id(),
						"pn" => "offers",
						"clid" => array(CL_PROCUREMENT_OFFER),
						"multiple" => "",
					), "popup_search");

				$tb->add_button(array(
					'name' => 'search',
					'img' => 'search.gif',
					'tooltip' => t('Otsi'),
					//"action" => "find_offers"
					'url' => 'javascript:void(0)',
					"onClick" => "aw_popup_scroll('".$search_url."','_spop',600,500)",

				));

				$tb->add_button(array(
					'name' => 'delete',
					'img' => 'delete.gif',
					'tooltip' => t('Kustuta'),
					"action" => "remove_offers",
				));
				break;
				
			case "offers":
				$t =& $arr["prop"]["vcl_inst"];
				$t->define_field(array(
					"name" => "date",
					"caption" => t("Kuup&auml;ev"),
					"align" => "center",
					"sortable" => 1
				));
				$t->define_field(array(
					"name" => "files",
					"caption" => t("Pakkumise failid"),
					"align" => "center",
				));
/*				$t->define_field(array(
					'name' => 'pick',
					'caption' => t('Vali'),
				));
*/				$t->define_chooser(array(
					"name" => "sel",
					"field" => "oid",
					"caption" => t("Vali"),
				));
				
				$conns = $arr["obj_inst"]->connections_from(array(
					'type' => "RELTYPE_OFFER",
				));
				foreach($conns as $conn)
				{
					if(is_oid($conn->prop("to")))$row = obj($conn->prop("to"));
					else continue;
					$offer_obj = obj($conn->prop("to"));
					$t->define_data(array(
						"date" => $offer_obj->prop("accept_date"),
						"files" => $offer_obj->prop("files"),
/*						"pick" => html::checkbox(array(
							"name" => "offer[".$offer_obj->id()."]",
							)),
*/						"oid" => $offer_obj->id(),
					));
				}
				break;
			case "purchases":
				$prop["value"] = $this->rows_table(&$arr["prop"]["vcl_inst"],$arr["obj_inst"]);
				break;
		};
		return $retval;
	}

	
	function rows_table(&$t , $this_obj)
	{
		$t->define_field(array(
			"name" => "product",
			"caption" => t("Toode"),
		));
		$t->define_field(array(
			'name' => 'amount',
			'caption' => t('Kogus'),
		));
		$t->define_field(array(
			'name' => 'unit',
			'caption' => t('&Uuml;hik'),
		));
		$t->define_field(array(
        		'name' => 'price',
			'caption' => t('Hind'),
		));
		$t->define_field(array(
			'name' => 'currency',
			'caption' => t('Valuuta'),
		));
		$t->define_field(array(
			'name' => 'shipment',
			'caption' => t('Tarneaeg'),
		));

		$unit_list = new object_list(array(
			"class_id" => CL_UNIT
		));
		$unit_opts = array();
		foreach($unit_list->arr() as $unit)
		{
			$unit_opts[$unit->id()] = $unit->prop("unit_code");
		}
		
		$curr_list = new object_list(array(
			"class_id" => CL_CURRENCY
		));
		$curr_opts = $curr_list->names();
		
		$offers = $this_obj->connections_from(array(
			'type' => "RELTYPE_OFFER",
		));
		foreach($offers as $offer_conn)
		{
		$offer_obj = obj($offer_conn->prop("to"));
		$conns = $offer_obj->connections_to(array(
			'reltype' => 1,
			'class' => CL_PROCUREMENT_OFFER_ROW,
		));
		foreach($conns as $conn)
		{
			if(is_oid($conn->prop("from")))$row = obj($conn->prop("from"));
			else continue;
			if(!$row->prop("accept")) continue;
			$unit = ""; $currency = "";
			if(is_oid($row->prop("unit")))
			{
				$unit_obj = obj($row->prop("unit"));
				$unit = $unit_obj->prop("unit_code");
			}
			if(is_oid($row->prop("currency")))
			{
				$currency = obj($row->prop("currency"));
				$currency = $currency->name();
			}
			$t->define_data(array(
				"row_id" 	=> $row->id(),
				"product"	=> $row->prop("product"),
				"amount"	=> $row->prop("amount"),
				'unit'		=> $unit,
				'price'		=> $row->prop("price"),
				'currency'	=> $currency,
				'shipment'	=> $row->prop("shipment"),
			));
		}
		}
	}

	/**
		@attrib name=remove_offers
	**/
	function remove_offers($arr)
	{arr($arr);
	//	$this->disconnect($arr["sel"]);
		
		$this_obj = obj($arr["id"]);
		foreach($arr["sel"] as $offer)
		{
			$off_obj = obj($offer);
			$this_obj->disconnect(array("from" => $offer));
		}
	//	object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "files":
					$prop["value"] = $arr["obj_inst"]->prop("files");
					if (isset($_FILES["files"]["tmp_name"]))
					{
						$src_file = $_FILES["files"]["tmp_name"];
						$ftype = $_FILES["files"]["type"];
					}
					else
					if (isset($prop["value"]["tmp_name"]))
					{
						$src_file = $prop["value"]["tmp_name"];
						$ftype = $prop["value"]["type"];
					};
					if (is_uploaded_file($src_file))
					{
						$_fi = get_instance(CL_FILE);
						$file_data = $_fi->add_upload_image("files" , $arr["obj_inst"]->id());
						$prop["value"][] = $file_data["id"];
					}
				break;
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
