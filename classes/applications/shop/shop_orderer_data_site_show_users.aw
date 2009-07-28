<?php
/*
@classinfo syslog_type=ST_SHOP_ORDERER_DATA_SITE_SHOW_USERS relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@tableinfo aw_shop_orderer_data_site_show master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_orderer_data_site_show
@default group=general

@property template type=select
@caption Template
*/

class shop_orderer_data_site_show_users extends shop_orderer_data_site_show
{
	function shop_orderer_data_site_show_users()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_orderer_data_site_show",
			"clid" => CL_SHOP_ORDERER_DATA_SITE_SHOW_USERS
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

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

	function show($arr)
	{
		$data = $this->get_html($arr["id"]);
		return html::div(array(
			"content" => $data,
			"id" => "shop_orderer_data_site_show",
		));
	}

	public function get_html($id)
	{
		$ob = obj($id);
		$template = "show.tpl";
		if($ob->prop("template"))
		{
			$template = $ob->prop("template");
		}
		;
		if(!$this->read_template($template , 1))
		{
			 $this->read_site_template($template);
		}
		$person = get_current_person();

		$add_new = $this->get_add_new($id);
		$users_table = $this->get_users_table($id);

		$this->vars(array(
			"name" => $ob->prop("name"),
			"uid" => aw_global_get("uid"),
			"user_name" => $person->name(),
			"add_new" => $add_new,
			"users_table" => $users_table,
		));

		return $this->parse();
	}
	
	/**
		@attrib name=add_slave is_public="1" caption="Change" nologin=1 all_args=1
	**/
	public function add_slave($arr)
	{
		$user_inst = get_instance(CL_USER);
		$user_inst->add_user(array(
			"parent" => aw_global_get("uid_oid"),
			"uid" => $this->get_slave_name(),
			"email" => $arr["email"],
			"password" => $arr["password"],
			"real_name" => $arr["name"],
		));
		$this->update_html($arr["id"]); 
	}

	/**
		@attrib name=remove_slave is_public="1" caption="Change" nologin=1 all_args=1
	**/
	public function remove_slave($arr)
	{
		arr($arr);
		$this->update_html($arr["id"]);
	}
	
	/**
		@attrib name=update_html is_public="1" caption="Change" nologin=1 all_args=1
	**/
	public function update_html($id)
	{
		die($this->get_html($id));
	}

	private function get_slave_name()
	{
		$user = obj(aw_global_get("uid_oid"));
		$slave_name = $user->get_new_slave_name();
		return $slave_name;
	}

	private function get_add_new($id)
	{

		classload("cfg/htmlclient");
		$htmlc = new htmlclient(array(
			'template' => "default",
		));
		$htmlc->start_output();

		$htmlc->add_property(array(
			"name" => "new_user_name",
			"type" => "text",
			"caption" => t("Uus kasutajanimi"),
			"value" => $this->get_slave_name(),
		));

		$htmlc->add_property(array(
			"name" => "person_name",
			"type" => "textbox",
			"value" => "",
			"caption" => t("Isiku nimi"),
		));

		$htmlc->add_property(array(
			"name" => "phone",
			"type" => "textbox",
			"value" => "",
			"caption" => t("Telefoni number"),
		));

		$htmlc->add_property(array(
			"name" => "email",
			"type" => "textbox",
			"value" => "",
			"caption" => t("E-post"),
		));

		$htmlc->add_property(array(
			"name" => "password",
			"type" => "password",
			"value" => "",
			"caption" => t("Parool"),
		));

/*		$htmlc->add_property(array(
			"name" => "password_again",
			"type" => "password",
			"value" => "",
			"caption" => t("Parool uuesti"),
		));
*/
		$htmlc->add_property(array(
			"name" => "submitb",
			"type" => "button",
			"value" => t("Salvesta uus kasutaja"),
			"class" => "sbtbutton",
			"onclick" => "
				$.post('/automatweb/orb.aw?class=shop_orderer_data_site_show_users&action=add_slave', {
					id: ".$id."
					,name: document.getElementsByName('person_name')[0].value
					, phone: document.getElementsByName('phone')[0].value
					, email: document.getElementsByName('email')[0].value
					, password: document.getElementsByName('password')[0].value
					, password_again: document.getElementsByName('password_again')[0].value
					},function(html){x=document.getElementById('shop_orderer_data_site_show');
								x.innerHTML=html;});",
			"caption" => t("Lisa uus kasutaja"),
		));

		$htmlc->finish_output(array(
			"submit" => "no",
			"action" => "submit_post_message",
			"method" => "POST",
			"data" => array(
				"id" => $id,
				"mfrom" => $mfrom,
				"orb_class" => "ml_list",
				"reforb" => 1
			)
		));
		$html = $htmlc->get_result();
		return $html;
	}


	private function get_users_table($arr)
	{
		$user = obj(aw_global_get("uid_oid"));
		$slaves = $user->get_slaves();
		$result = "";
		if($slaves->count())
		{
			classload("vcl/table");
			classload("vcl/toolbar");
			$t = new vcl_table();
			$t->define_chooser(array(
				"field" => "oid",
				"name" => "sel"
			));
			$t->define_field(array(
 				"name" => "uid",
				"caption" => t("Kasutajanimi")
			));
			$t->define_field(array(
 				"name" => "name",
				"caption" => t("Isiku nimi")
			));
			foreach($slaves->arr() as $slave)
			{
				$t->define_data(array(
					"oid" => $slave->id(),
					"uid" => $slave->name(),
					"name" => $slave->get_user_name()
				));
			}

			$tb = new toolbar();
			$tb->add_button(array(
				"name" => "delete",
				"tooltip" => t("Kustuta"),
				"url" => "javascript:;",
				"onClick" => "$.post('/automatweb/orb.aw?class=shop_orderer_data_site_show_users&action=remove_slave', {
					id: ".$id."
					,name: document.getElementsByName('person_name')[0].value
					},function(html){x=document.getElementById('shop_orderer_data_site_show');
								x.innerHTML=html;});",
				"img" => "delete.gif",
				"confirm" => t("Oled kindel, et soovid valitud kasutajad kustutada?"),
			));


			$result =  $tb->get_toolbar()."<br>".$t->draw();
		}
		else
		{
			$result =  t("Pole &uuml;htegi alamkasutajat");
		}


		return $result;
	}

}

?>
