<?php
// patent_patent.aw - Patent
/*

@classinfo syslog_type=ST_PATENT_PATENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@extends applications/clients/patent_office/intellectual_property
@tableinfo aw_trademark index=aw_oid master_table=objects master_index=brother_of

@groupinfo author caption="Autor"
@default group=author
	@property author type=relpicker reltype=RELTYPE_AUTHOR
	@caption Autor

	@property author_disallow_disclose type=checkbox ch_value=1 store=no
	@caption Mitte avalikustada minu nime autorina


@groupinfo invention caption="Leiutise nimetus"
@default group=invention
	@property invention_name_et type=textbox
	@caption Leiutise nimetus (eesti keeles)

	@property invention_name_en type=textbox
	@caption Leiutise nimetus (inglise keeles)


@default group=priority
	@property childtitle110 type=text store=no subtitle=1
	@caption Pariisi konventsiooni vm. kokkuleppe taotluse alusel
		@property prio_convention_date type=date_select
		@caption Kuup&auml;ev

		@property prio_convention_country type=textbox
		@caption Riigi kood

		@property prio_convention_nr type=textbox
		@caption Taotluse number

	@property childtitle111 type=text store=no subtitle=1
	@caption Varasema patenditaotluse alusel sellest eraldatud patenditaotluse puhul
		@property prio_prevapplicationsep_date type=date_select
		@caption Kuup&auml;ev

		@property prio_prevapplicationsep_nr type=textbox
		@caption Taotluse number

	@property childtitle112 type=text store=no subtitle=1
	@caption Varasema patenditaotluse paranduste ja t&auml;ienduste alusel
		@property prio_prevapplicationadd_date type=date_select
		@caption Kuup&auml;ev

		@property prio_prevapplicationadd_nr type=textbox
		@caption Taotluse number

	@property childtitle113 type=text store=no subtitle=1
	@caption Varasema taotluse alusel
		@property prio_prevapplication_date type=date_select
		@caption Kuup&auml;ev

		@property prio_prevapplication_nr type=textbox
		@caption Taotluse number


@groupinfo other_data caption="Muud andmed"
@default group=other_data
	@property childtitle114 type=text store=no subtitle=1
	@caption Esmase taotluse andmed (seaduse &#0167;21 l&otilde;ige 2)
		@property other_first_application_data_date type=date_select
		@caption Kuup&auml;ev

		@property other_first_application_data_country type=textbox
		@caption Riik

		@property other_first_application_data_nr type=textbox
		@caption Taotluse number

	@property childtitle115 type=text store=no subtitle=1
	@caption Bioloogilise aine, sealhulgas mikroorganismi deponeerimise andmed:
		@property other_bio_nr type=textbox
		@caption Deponeerimise nr.

		@property other_bio_date type=date_select
		@caption Deponeerimise kuup&auml;ev

		@property other_bio_inst type=textbox
		@caption Deponeerimise asutuse nimi

	@property childtitle116 type=text store=no subtitle=1
	@caption Patendiseaduse &#0167;8 l&otilde;ikes 3 nimetatud teabe avalikustamise kuup&auml;ev (23) ja andmed:
		@property other_datapub_date type=date_select
		@property other_datapub_data type=textarea


@groupinfo attachments caption="Lisad"
@default group=attachments
 	@property attachment_invention_description type=fileupload reltype=RELTYPE_ATTACHMENT_INVENTION_DESCRIPTION form=+emb
	@caption Leiutiskirjeldus

 	@property attachment_seq type=fileupload reltype=RELTYPE_ATTACHMENT_SEQ form=+emb
	@caption J&auml;rjestuse loetelu

 	@property attachment_demand type=fileupload reltype=RELTYPE_ATTACHMENT_DEMAND form=+emb
	@caption Patendin&otilde;udlus

 	@property attachment_demand_points type=textbox size=3
	@caption Patendin&otilde;udlus, n&otilde;udluspunkti

 	@property attachment_summary_et type=fileupload reltype=RELTYPE_ATTACHMENT_SUMMARY_ET form=+emb
	@caption Leiutise olemuse l&uuml;hikokkuv&otilde;te eesti keeles

 	@property attachment_summary_en type=fileupload reltype=RELTYPE_ATTACHMENT_SUMMARY_EN form=+emb
	@caption Leiutise olemuse l&uuml;hikokkuv&otilde;te inglise keeles

 	@property attachment_dwgs type=fileupload reltype=RELTYPE_ATTACHMENT_DWGS form=+emb
	@caption Joonised ja muu illustreeriv materjal

 	@property attachment_fee type=fileupload reltype=RELTYPE_ATTACHMENT_FEE form=+emb
	@caption Riigil&otilde;ivu tasumist t&otilde;endav dokument

 	@property attachment_warrant type=fileupload reltype=RELTYPE_ATTACHMENT_WARRANT form=+emb
	@caption Volikiri

 	@property attachment_prio type=fileupload reltype=RELTYPE_ATTACHMENT_PRIO form=+emb
	@caption Prioriteedin&otilde;uet t&otilde;endavad dokumendid

 	@property attachment_bio type=fileupload reltype=RELTYPE_ATTACHMENT_BIO form=+emb
	@caption Bioloogilise aine, sealhulgas mikroorganismi deponeerimist t&otilde;endav dokument

@default group=fee
 	@property fee_copies type=checkbox ch_value=1
	@caption Patendidokumentide v&otilde;i muude tr&uuml;kiste koopiate v&auml;ljastamise l&otilde;iv

// RELTYPES
@reltype AUTHOR value=17 clid=CL_CRM_PERSON
@caption Autor

@reltype ATTACHMENT_INVENTION_DESCRIPTION value=100 clid=CL_FILE
@caption Lisa kirjeldus

@reltype ATTACHMENT_SEQ value=101 clid=CL_FILE
@caption Lisa jarjestuse loetelu

@reltype ATTACHMENT_DEMAND value=102 clid=CL_FILE
@caption Lisa pat. noudlus

@reltype ATTACHMENT_SUMMARY_ET value=103 clid=CL_FILE
@caption Lisa kokkuvote est

@reltype ATTACHMENT_SUMMARY_EN value=104 clid=CL_FILE
@caption Lisa kokkuvote eng

@reltype ATTACHMENT_DWGS value=105 clid=CL_FILE
@caption Lisa joonised

@reltype ATTACHMENT_FEE value=106 clid=CL_FILE
@caption Lisa tasumisdok

@reltype ATTACHMENT_WARRANT value=107 clid=CL_FILE
@caption Lisa volikiri

@reltype ATTACHMENT_PRIO value=108 clid=CL_FILE
@caption Lisa prioriteeditoend

@reltype ATTACHMENT_BIO value=109 clid=CL_FILE
@caption Lisa biol. depon. toend

*/

class patent_patent extends intellectual_property
{
	function __construct()
	{
		parent::__construct();
		$this->init(array(
			"tpldir" => "applications/patent",
			"clid" => CL_PATENT_PATENT
		));
		$this->info_levels = array(
			0 => "applicant",
			11 => "author",
			12 => "invention_pat",
			3 => "priority_pat",
			13 => "other_data",
			14 => "attachments_pat",
			4 => "fee_pat",
			5 => "check"
		);
		$this->pdf_file_name = "Patenditaotlus";
		$this->show_template = "show_pat.tpl";
		$this->date_vars = array_merge($this->date_vars, array("prio_convention_date","prio_prevapplicationsep_date","prio_prevapplicationadd_date","prio_prevapplication_date","other_first_application_data_date","other_bio_date","other_datapub_date"));
		$this->file_upload_vars = array_merge($this->file_upload_vars, array("attachment_invention_description", "attachment_seq", "attachment_demand", "attachment_summary_et", "attachment_summary_en", "attachment_dwgs", "attachment_fee", "attachment_warrant", "attachment_prio", "attachment_bio"));
		$this->text_area_vars = array_merge($this->text_area_vars, array("other_datapub_data"));
		$this->text_vars = array_merge($this->text_vars, array("invention_name_et","invention_name_en","prio_convention_country","prio_convention_nr","prio_prevapplicationsep_nr","prio_prevapplicationadd_nr","prio_prevapplication_nr","other_first_application_data_country","other_first_application_data_nr","other_bio_nr","other_bio_inst", "attachment_demand_points"));
		$this->checkbox_vars = array_merge($this->checkbox_vars, array("author_disallow_disclose", "fee_copies"));
		$this->save_fee_vars = array_merge($this->save_fee_vars, array("fee_copies"));

		//siia panev miskid muutujad mille iga ringi peal 2ra kustutab... et uuele taotlejale vana info ei j22ks
		$this->datafromobj_del_vars = array("name_value" , "email_value" , "phone_value" , "fax_value" , "code_value" ,"email_value" , "street_value" ,"index_value" ,"country_code_value","city_value","correspond_street_value", "correspond_index_value" , "correspond_country_code_value" , "correspond_city_value", "name");
		$this->datafromobj_vars = array_merge($this->datafromobj_vars, array("invention_name_et", "invention_name_en", "prio_convention_date", "prio_convention_country", "prio_convention_nr", "prio_prevapplicationsep_date", "prio_prevapplicationsep_nr", "prio_prevapplicationadd_date", "prio_prevapplicationadd_nr", "prio_prevapplication_date", "prio_prevapplication_nr", "other_first_application_data_date", "other_first_application_data_country", "other_first_application_data_nr", "other_bio_nr", "other_bio_date", "other_bio_inst", "other_datapub_date", "other_datapub_data", "attachment_invention_description", "attachment_seq", "attachment_demand", "attachment_demand_points", "attachment_summary_et", "attachment_summary_en", "attachment_dwgs", "attachment_fee", "attachment_warrant", "attachment_prio", "attachment_bio", "fee_copies"));
	}

	protected function save_priority($patent)
	{
		$patent->set_prop("prio_convention_date" , $_SESSION["patent"]["prio_convention_date"]);
		$patent->set_prop("prio_convention_country" , $_SESSION["patent"]["prio_convention_country"]);
		$patent->set_prop("prio_convention_nr" , $_SESSION["patent"]["prio_convention_nr"]);
		$patent->set_prop("prio_prevapplicationsep_date" , $_SESSION["patent"]["prio_prevapplicationsep_date"]);
		$patent->set_prop("prio_prevapplicationsep_nr" , $_SESSION["patent"]["prio_prevapplicationsep_nr"]);
		$patent->set_prop("prio_prevapplicationadd_date" , $_SESSION["patent"]["prio_prevapplicationadd_date"]);
		$patent->set_prop("prio_prevapplicationadd_nr" , $_SESSION["patent"]["prio_prevapplicationadd_nr"]);
		$patent->set_prop("prio_prevapplication_date" , $_SESSION["patent"]["prio_prevapplication_date"]);
		$patent->set_prop("prio_prevapplication_nr" , $_SESSION["patent"]["prio_prevapplication_nr"]);
		$patent->save();
	}

	protected function save_forms($patent)
	{
		$this->save_priority($patent);
		$this->save_fee($patent);
		$this->save_invention($patent);
		$this->save_applicants($patent);
		$this->save_authors($patent);
		$this->fileupload_save($patent);
		$this->save_attachments($patent);
		$this->save_other_data($patent);
		$this->final_save($patent);
		$patent->set_meta("products" , $_SESSION["patent"]["products"]);
	}

	protected function save_invention($patent)
	{
		$patent->set_prop("invention_name_et" , $_SESSION["patent"]["invention_name_et"]);
		$patent->set_prop("invention_name_en" , $_SESSION["patent"]["invention_name_en"]);
		$patent->save();
	}

	protected function save_attachments($patent)
	{
		$patent->set_prop("attachment_demand_points" , $_SESSION["patent"]["attachment_demand_points"]);
		$patent->save();
	}

	protected function save_other_data($patent)
	{
		$patent->set_prop("other_first_application_data_date" , $_SESSION["patent"]["other_first_application_data_date"]);
		$patent->set_prop("other_first_application_data_country" , $_SESSION["patent"]["other_first_application_data_country"]);
		$patent->set_prop("other_first_application_data_nr" , $_SESSION["patent"]["other_first_application_data_nr"]);
		$patent->set_prop("other_bio_nr" , $_SESSION["patent"]["other_bio_nr"]);
		$patent->set_prop("other_bio_date" , $_SESSION["patent"]["other_bio_date"]);
		$patent->set_prop("other_bio_inst" , $_SESSION["patent"]["other_bio_inst"]);
		$patent->set_prop("other_datapub_date" , $_SESSION["patent"]["other_datapub_date"]);
		$patent->set_prop("other_datapub_data" , $_SESSION["patent"]["other_datapub_data"]);
		$patent->save();
	}

	protected function get_object()
	{
		if(is_oid($_SESSION["patent"]["id"]))
		{
			$patent = obj($_SESSION["patent"]["id"]);
		}
		else
		{
			$patent = new object();
			$patent->set_class_id(CL_PATENT_PATENT);
			$patent->set_parent($_SESSION["patent"]["parent"]);
			$patent->save();
			$patent->set_name(" Kinnitamata taotlus nr [".$patent->id()."]");
		}

		return $patent;
	}

	protected function get_payment_sum()
	{
		$sum = $this->get_request_fee();

		if (!empty($_SESSION["patent"]["fee_copies"]))
		{
			$sum += 150;
		}

		if(!empty($_SESSION["patent"]["attachment_demand_points"]) and 10 < $_SESSION["patent"]["attachment_demand_points"])
		{
			$sum += ($_SESSION["patent"]["attachment_demand_points"] - 10) * 200;
		}

		return $sum;
	}

	private function get_request_fee()
	{
		$is_corporate = false;

		foreach($_SESSION["patent"]["applicants"] as $key => $val)
		{
			if($val["applicant_type"])
			{
				$is_corporate = true;
				break;
			}
		}

		$sum = $is_corporate ? 3500 : 875;
		return $sum;
	}

	function get_vars($arr)
	{
		$data = parent::get_vars($arr);

		$_SESSION["patent"]["request_fee"]= $this->get_request_fee();

		if(isset($_SESSION["patent"]["delete_author"]))
		{
			unset($_SESSION["patent"]["authors"][$_SESSION["patent"]["delete_author"]]);
			unset($_SESSION["patent"]["delete_author"]);
		}

		if($_SESSION["patent"]["add_new_author"])
		{
			$_SESSION["patent"]["add_new_author"] = null;
			$_SESSION["patent"]["change_author"] = null;
			$_SESSION["patent"]["author_id"] = null;
		}
		elseif(strlen(trim(($_SESSION["patent"]["author_id"]))))
		{
			$this->_get_author_data();
			$data["change_author"] = $_SESSION["patent"]["author_id"];
			$_SESSION["patent"]["change_author"] = null;
			$_SESSION["patent"]["author_id"] = null;
		}
		else
		{
			$data["author_no"] = sizeof($_SESSION["patent"]["authors"]) + 1;
		}
		//nendesse ka siis see tingumus, et muuta ei saa

		$data["P_ADDRESS"] = $this->parse("P_ADDRESS");

		$data["add_new_author"] = html::radiobutton(array(
				"value" => 1,
				"checked" => 0,
				"name" => "add_new_author",
		));

		if(is_array($_SESSION["patent"]["authors"]) && sizeof($_SESSION["patent"]["authors"]))
		{
			$data["authors_table"] = $this->_get_authors_table();
		}

		return $data;
	}

	function check_fields()
	{
		$err = parent::check_fields();

		if(((int) $_POST["data_type"]) === 14)
		{
			if(empty($_FILES["attachment_invention_description_upload"]["tmp_name"]))
			{
				$err.= t("Leiutiskirjeldus peab olema lisatud")."\n<br>";
			}

			if($err)
			{
				$_SESSION["patent"]["checked"] = 3;
			}
		}

		return $err;
	}

	function fill_session($id)
	{
		$address_inst = get_instance(CL_CRM_ADDRESS);
		$patent = obj($id);
		parent::fill_session($id);
		$author_disallow_disclose = (array) $patent->meta("author_disallow_disclose");

		foreach($patent->connections_from(array("type" => "RELTYPE_AUTHOR")) as $key => $c)
		{
			$o = $c->to();
			$key = $o->id();
			$_SESSION["patent"]["authors"][$key]["name"] = $o->name();
			$_SESSION["patent"]["authors"][$key]["firstname"] = $o->prop("firstname");
			$_SESSION["patent"]["authors"][$key]["lastname"] = $o->prop("lastname");
			$_SESSION["patent"]["authors"][$key]["author_disallow_disclose"] = $author_disallow_disclose[$o->id()];
			$address = $o->prop("address");

			if($this->can("view" , $address))
			{
				$address_obj = obj($address);
				$_SESSION["patent"]["authors"][$key]["street"] = $address_obj->prop("aadress");
				$_SESSION["patent"]["authors"][$key]["index"] = $address_obj->prop("postiindeks");
				if(is_oid($address_obj->prop("linn")) && $this->can("view" , $address_obj->prop("linn")))
				{
					$city = obj($address_obj->prop("linn"));
					$_SESSION["patent"]["authors"][$key]["city"] = $city->name();
				}
				$_SESSION["patent"]["authors"][$key]["country_code"] = $address_inst->get_country_code($address_obj->prop("riik"));
			}
		}
	}
}

?>
