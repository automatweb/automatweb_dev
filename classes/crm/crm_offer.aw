<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_offer.aw,v 1.26 2005/01/28 14:14:13 ahti Exp $
// pakkumine.aw - Pakkumine 
/*

@classinfo syslog_type=ST_CRM_OFFER relationmgr=yes no_status=1

@default table=objects
@default group=general

@property orderer type=select table=aw_crm_offer datatype=int
@caption Tellija

@property start1 type=datetime_select field=start table=planner
@caption Algus

@property preformer type=hidden table=aw_crm_offer datatype=int

@property preformer_cap type=text store=no
@caption Täitja

@property salesman type=select table=aw_crm_offer datatype=int
@caption Pakkumise koostaja

@property offer_status type=select table=aw_crm_offer datatype=int
@caption Staatus

@property content type=textarea cols=60 rows=20 table=planner field=description
@caption Sisu

@property prev_status type=hidden store=no

@property sum type=textbox table=aw_crm_offer size=7 datatype=int
@caption Hind (ilma KM)

@property is_done type=checkbox table=objects field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@tableinfo planner index=id master_table=objects master_index=brother_of

@default method=serialize

@property calendar_selector type=calendar_selector store=no group=calendars
@caption Kalendrid

@property project_selector type=project_selector store=no group=projects
@caption Projektid

------- Paketid --------

@property package_toolbar type=toolbar no_caption=1 store=no group=packages_show,products_show
@property package_table type=table no_caption=1 store=no group=packages_show

@property products_table type=table no_caption=1 store=no group=products_show

@property packages_search type=callback callback=do_packages_search_form no_caption=1 store=no group=packages_show,products_show
@property packages_search_results type=table no_caption=1 store=no group=packages_show,products_show



-------PAKKUMISE AJALUGU---------
@default group=history
@property offer_history type=table no_caption=1 store=no group=history
------- Tooted --------

@groupinfo recurrence caption=Kordumine
@groupinfo calendars caption=Kalendrid
@groupinfo projects caption=Projektid
@groupinfo packages_show caption=Paketid submit=no
@groupinfo products_show caption=Tooted submit=no
@groupinfo history caption=Ajalugu submit=no


@tableinfo planner index=id master_table=objects master_index=brother_of
@tableinfo aw_crm_offer index=aw_oid master_table=objects master_index=oid

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

@reltype ORDERER value=2 clid=CL_CRM_COMPANY
@caption Tellija

@reltype PREFORMER value=3 clid=CL_CRM_COMPANY
@caption Täitja

@reltype SALESMAN value=4 clid=CL_CRM_PERSON
@caption Pakkumise koostaja

@reltype PRODUCT value=5 clid=CL_SHOP_PRODUCT
@caption Toode

@reltype PACKAGE value=6 clid=CL_SHOP_PACKET
@caption Pakett
*/

/*
CREATE TABLE `aw_crm_offer` (
`aw_oid` INT UNSIGNED NOT NULL ,
`preformer` INT UNSIGNED NOT NULL ,
`orderer` INT UNSIGNED NOT NULL ,
`salesman` INT UNSIGNED NOT NULL ,
`sum` INT NOT NULL ,
`offer_status` TINYINT NOT NULL ,
PRIMARY KEY ( `aw_oid` )
);
*/


define("OFFER_ON_PROCESS",1);
define("OFFER_IS_SENT",2);
define("OFFER_IS_PREFORMED",3);
define("OFFER_IS_DECLINED",4);
define("OFFER_IS_POSITIVE",4);
class crm_offer extends class_base
{		
	var $u_i;
	var $statuses;
	function crm_offer()
	{
		$this->init(array(
			"clid" => CL_CRM_OFFER
		));
		$this->u_i = get_instance("core/users/user");
		$this->statuses =  array(
			t("Koostamisel"), 
			t("Saadetud"), 
			t("Esitletud"), 
			t("Tagasilükatud"), 
			t("Positiivelt lõppenud")
		);		
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		
		switch($prop["name"])
		{
			case "start1":
			//	return PROP_IGNORE;
			break;
		
			case "orderer":
				if(!($arr["new"] == 1))
				{
					$my_org = $arr["obj_inst"]->get_first_obj_by_reltype('RELTYPE_PREFORMER');
				}
				else
				{
					$my_org = $this->u_i->get_current_company();
					$my_org = &obj($my_org);
				}
				$data = array();
				if($my_org)
				{
					$org_inst = get_instance(CL_CRM_COMPANY);
					$org_inst->get_customers_for_company($my_org, &$data);
				
				}
				foreach ($data as $key)
				{
					$obj = &obj($key);
					$options[$key] = $obj->name();
				}
				
				$prop["options"] = $options;
				
						
				if($arr["new"] == 1)
				{
					$prop["value"] = $arr["request"]["alias_to_org"];
				}
				elseif($arr["obj_inst"]->prop("orderer"))
				{
					$prop["value"] = $arr["obj_inst"]->prop("orderer");
				}
			break;
			
			case "offer_history":
				$this->do_offer_history($arr);
			break;
			
			case "packages_search_toolbar":
				$arr["request"]["search_package_name"]?$this->do_packages_search_toolbar($arr):$retval=PROP_IGNORE;
			break;
			
			case "is_done":
				return PROP_IGNORE;
			break;
			
			case "preformer":
				$org_id = $this->u_i->get_current_company();
				$org = &obj($org_id);
				$prop["value"] = $org->id();
			break;
			
			case "preformer_cap":
				$org_id = $this->u_i->get_current_company();
				$org = &obj($org_id);
				$prop["value"] = $org->name();
			break;
					
			case "salesman":
				$my_company = $this->u_i->get_current_company();
				$org = &obj($my_company);
				$workers = $org->connections_from(array("type" => "RELTYPE_WORKERS"));
				
				foreach ($workers as $worker)
				{
					$options[$worker->prop("to")] = $worker->prop("to.name");
				}
				$prop["options"] = $options;
				
				if(!$prop["value"])
				{
					$person_id = $this->u_i->get_current_person();
					$person_obj = &obj($person_id);
					$prop["value"] = $person_obj->id();
				}
			break;
			
			case "package_table":
				$this->do_package_table($arr);
			break;
			
			case "products_table":
				$this->do_products_table($arr);
			break;
			
			case "package_toolbar":
				$this->do_package_toolbar($arr);
			break;
			
			case "packages_search_results":
				if($arr["request"]["search_package_name"])
				{
					$this->do_packages_search_results($arr);
				}
			break;
			
			case "offer_status":
				$prop["options"] = $this->statuses;
			break;
			
			case "prev_status":
				if(is_object($arr["obj_inst"]))
				{
					$prop["value"] = $arr["obj_inst"]->prop("offer_status");
				}
			break;
			
		};
		return $retval;
	}
	
	function do_packages_search_toolbar($arr)
	{
		$toolbar = &$arr["prop"]["vcl_inst"];
		
		$toolbar->add_button(array(
			'name' => 'save',
			'img' => 'save.gif',
			'tooltip' => t('Salvesta valitud paketid pakkumisse'),
			'action' => 'connect_selected_packages_to_offer',
		));	
	}
	/**
		@attrib name=connect_selected_packages_to_offer
	**/
	function connect_selected_packages_to_offer($arr)
	{
		extract($arr);
		$obj = &obj($id);
		
		foreach ($sel as $pack)
		{
			$obj->connect(array(
				"to" => $pack,
				"reltype" => $group == "products_show" ? RELTYPE_PRODUCT:RELTYPE_PACKAGE,
			));
		}
		
		return  $this->mk_my_orb("change", array(
			"id" => $id,
			"group" => $group,
			), 
			CL_CRM_OFFER
		);
	}
	
	function get_packages_total_sum($obj_id)
	{
		$obj = &obj($obj_id);
		foreach ($obj->connections_from(array("type" => "RELTYPE_PACKAGE")) as $conn)
		{
			$packet = $conn->to();
			$sum = $sum+ $packet->prop("price");
		}
		return $sum;
	}

	function get_products_total_sum($obj_id)
	{
		$obj = &obj($obj_id);
		foreach ($obj->connections_from(array("type" => "RELTYPE_PRODUCT")) as $conn)
		{
			$product = $conn->to();
			$sum = $sum + $product->prop("price");
		}
		return $sum;
	}
	
	function total_sum($obj_id)
	{
		return $this->get_products_total_sum($obj_id) + $this->get_packages_total_sum($obj_id);
	}
	
	function do_packages_search_results($arr)
	{
		$ol  = new object_list(array(
			"class_id" => $arr['request']['group'] == "products_show" ? CL_SHOP_PRODUCT:CL_SHOP_PACKET,
			"name" => "%".$arr['request']["search_package_name"]."%",
		));
		
		//If there is no search results
		if(!($ol->count() > 0))
		{
			$this->go_no_search_results();	
		}	
		
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "package",
		 	"caption" => t("Pakett"),
			"sortable" => 1,
		));
		 
		$table->define_field(array(
			"name" => "price",
		 	"caption" => t("Hind"),
		 	"sortable" => 1,
		 	"align" => "center",
		 	"width" => 50,
		));
		
		$table->define_chooser(array(
			"name" => "sel",
		 	"field" => "package_id",
		 	"caption" => "X",
		));
		
		
		foreach ($ol->arr() as $obj)
		{
			$table->define_data(array(
				"package" => $obj->name(),
				"price" => $obj->prop("price"),
				"package_id" => $obj->id(),
			));
		}
		
	}
	
	function do_packages_search_form($arr)
	{
		if($arr["request"]["search"] == 1)
		{
			extract($arr);
			
			$retval[] = array(
				"name" => "package_search_label", 
				"type" => "text", 
				"subtitle" => 1,
				"caption" => t("Otsing"),
			);
			
			$retval["search_package_name"] = array(
				"name" => "search_package_name",
				"type" => "textbox",
				"caption" => t("Nimetus"),
			);
			
			$retval[] = array(
				"name" => "search_offer_submit",
				"type" => "submit",
				"caption" => t("Otsi"),
				"action" => "get_search_packets_url", //"get_search_offers_url",
			);	
			return $retval;
		}
		if($arr["request"]["search_package_name"])
		{
			$retval[] = array(
				"name" => "package_search_label", 
				"type" => "text", 
				"subtitle" => 1,
				"caption" => t("Otsingutulemused"),
			);	
			return $retval;
		}
	}
	
	/**	
		@attrib name=get_search_packets_url
	**/	
	function get_search_packets_url($arr)
	{
		return $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"group" => $arr["group"],
			"search_package_name" => $arr["search_package_name"], 
		), $arr["class"]);
	}
	
	function do_package_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud paketid'),
			'action' => 'delete_selected_packages',
			'confirm' => t("Soovid kustutada valitud paketid?")
		));
		
		$tb->add_button(array(
			'name' => 'search',
			'img' => 'search.gif',
			'tooltip' => t('Otsi pakette'),
			'url' => aw_url_change_var(array("search" => 1)),
		));
		
		if($arr["request"]["search_package_name"])
		{
			$tb->add_button(array(
				'name' => 'save',
				'img' => 'save.gif',
				'tooltip' => t('Liida paketid pakkumisse'),
				'action' => 'connect_selected_packages_to_offer',
			));
		}	
	}
	
	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "start1":
				//$data["value"] = $arr["obj_inst"]->created();
			break;
			case "salesman":
				if($data["value"])
				{
					$arr["obj_inst"]->connect(array(
						"to" => $data["value"],
						"reltype" => RELTYPE_SALESMAN,
					));
				}
			break;
			
			case "orderer":
				if($data["value"])
				{
					$arr["obj_inst"]->connect(array(
						"to" => $data["value"],
						"reltype" => RELTYPE_ORDERER,
					));
				}
			break;
					
			case "preformer":
				if($data["value"])
				{
					$arr["obj_inst"]->connect(array(
						"to" => $data["value"],
						"reltype" => RELTYPE_PREFORMER,
					));
				}
			break;
		};
		return $retval;
	}
	
	/**
		Returns offers ids made for company
	**/
	function get_offers_for_company($orderer_id, $preformer_id = false)
	{
		if($orderer_id)
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_OFFER,
				"orderer" => $orderer_id,
				"preformer" => $preformer_id,
			));
			return $ol;
		}
	}
	
	function do_products_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "product",
			"caption" => t("Toode"),
			"sortable" => "1",
		));
		$table->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"sortable" => "1",
		));
		
		$table->define_chooser(array(
			"name" => "select",
			"field" => "product_id",
			"caption" => "X",
			"align" => "center"
		));
		
		$table->set_sortable(false);
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_PRODUCT")) as $conn)
		{
			$obj = $conn->to();
			$table->define_data(array(
				"product" => html::get_change_url($obj->id(), array(), $obj->name()),
				"price" => $obj->prop("price"),
				"product_id" => $conn->id(),
			));
		}

		$table->define_data(array(
			"product" => "<b>Kokku:</b>",
			"price" => $this->get_products_total_sum($arr["obj_inst"]->id()),
		));
	}
	
	function callback_pre_save($arr)
	{
		if($arr["request"]["offer_status"] == 3 || $arr["request"]["offer_status"] == 4)
		{
			$arr["obj_inst"]->set_prop("is_done", OBJ_IS_DONE);
		}
		else 
		{
			$arr["obj_inst"]->set_prop("is_done", 0);
		}
		//If offer status has been changed then lets write to log about it.
		if($arr["request"]["prev_status"] != $arr["request"]["offer_status"])
		{
			$status_data = $arr["obj_inst"]->meta("statuslog");
			$status_data[time()] = array(
				"prev_status" => $arr["request"]["prev_status"],
				"new_status" => $arr["request"]["offer_status"], 
				"uid" => aw_global_get("uid"),
			);
			$arr["obj_inst"]->set_meta("statuslog", $status_data);
		}
	}
	
	function callback_post_save($arr)
	{
		if($arr["new"]==1)
		{
			$users = get_instance("users");
			$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
			$conns = $user->connections_to(array(
				"type" => 8, //RELTYPE_CALENDAR_OWNERSHIP
			));
			if(count($conns))
			{
				$conn = current($conns);
				$calender = &obj($conn->prop("from"));
				$parent = $calender->prop("event_folder");
				if($parent)
				{
					$arr["obj_inst"]->create_brother($parent);
				}
			}
			//$arr["obj_inst"]->set_prop("start1", $arr["obj_inst"]->created());
			//$arr["obj_inst"]->save();
		}
	}
	
	function do_offer_history(&$arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "prev",
			"caption" => t("Algstaatus"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "next",
			"caption" => t("Lõppstaatus"),
			"sortable" => "1",
		));
		
		$table->define_field(array(
			"name" => "time",
			"caption" => t("Muutuse aeg"),
			"sortable" => "1",
		));
	
		$table->define_field(array(
			"name" => "who",
			"caption" => t("Muutja"),
			"sortable" => "1",
		));
		
		$user = get_instance("users");
		if(!is_array($arr["obj_inst"]->meta("statuslog")))
		{
			return;
		}
		foreach ($arr["obj_inst"]->meta("statuslog") as $key => $logitem)
		{
			$uid = $user->get_oid_for_uid($logitem["uid"]);
			$user_obj = &obj($uid);
			$person_id = $this->u_i->get_person_for_user($user_obj);
			$person_obj = &obj($person_id);
			

			$table->define_data(array(
				"prev" => $this->statuses[$logitem['prev_status']],
				"next" => $this->statuses[$logitem['new_status']],
				"who" => $person_obj->name(),
				"time" => get_lc_date($key)." - kell: " .date("G:i", $key),
			));
		}
	}
	
	function do_package_table($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Pakett"),
			"sortable" => "1",
			"width" => "90%"
		));
		
		$table->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"sortable" => "1",
			"width" => "5%",
			"align" => "center",
		));
		
		$table->define_chooser(array(
			"name" => "select",
			"field" => "product_id",
			"caption" => "X",
			"align" => "center"
		));
		
		
		$table->set_sortable(false);
		
		foreach ($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_PACKAGE")) as $conn)
		{
			$obj = $conn->to();
			$table->define_data(array(
				"name" => html::get_change_url($obj->id(), array(), $obj->name()),
				"price" => $obj->prop("price"),
				"product_id" => $conn->id(),
			));
			
			
			foreach ($obj->connections_from(array("type" => "RELTYPE_PRODUCT")) as $product_conn)
			{
				$product_obj = $product_conn->to();
				$table->define_data(array(
					"name" => "- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;". html::get_change_url($product_obj->id(), array(), $product_obj->name()),
					"price" => $product_obj->prop("price"),
					//"product_id" => $product_obj->id(),
				));
			}
		}
		
		$table->define_data(array(
			"name" => "<b>".t("Kokku")."</b>",
			"price" => "<b>".$this->get_packages_total_sum($arr["obj_inst"]->id())."</b>",
		));
	}
	
	/**
		@attrib name=delete_selected_packages
	**/
	function delete_selected_packages($arr)
	{
		if(is_array($arr["select"]))
		{
			foreach ($arr["select"] as $item)
			{
				$conn = new connection($item);
				$conn->delete();
			}
		}
		return html::get_change_url($arr["id"], array("group" => $arr["group"]));
	}
}
?>
