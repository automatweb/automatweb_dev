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

	@property author_disallow_disclose type=checkbox ch_value=1
	@caption Mitte avalikustada minu nime autorina


@groupinfo invention caption="Leiutise nimetus"
@default group=invention
	@property invention_name_et type=textbox
	@caption Leiutise nimetus (eesti keeles)

	@property invention_name_en type=textbox
	@caption Leiutise nimetus (eesti inglise)


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
	@property other_first_application_data type=checkbox ch_value=1
	@caption Esmase taotluse andmed (seaduse &#0167;21 l&otilde;ige 2)
		@property other_first_application_data_date type=date_select
		@caption Kuup&auml;ev

		@property other_first_application_data_country type=textbox
		@caption Riik

		@property other_first_application_data_nr type=textbox
		@caption Taotluse number

	@property other_bio type=checkbox ch_value=1
	@caption Bioloogilise aine, sealhulgas mikroorganismi deponeerimise andmed:
		@property other_bio_nr type=textbox
		@caption Deponeerimise nr.

		@property other_bio_date type=date_select
		@caption Deponeerimise kuup&auml;ev

		@property other_bio_inst type=textbox
		@caption Deponeerimise asutuse nimi

	@property other_datapub type=checkbox ch_value=1
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
		$this->info_levels[11] = "author";
		$this->info_levels[12] = "invention_pat";
		$this->info_levels[13] = "other_data";
		$this->info_levels[14] = "attachments_pat";
		$this->pdf_file_name = "Patenditaotlus";
		$this->show_template = "show_pat.tpl";
		$this->file_upload_vars = array_merge($this->file_upload_vars, array("attachment_invention_description", "attachment_seq", "attachment_demand", "attachment_summary_et", "attachment_summary_en", "attachment_dwgs", "attachment_fee", "attachment_warrant", "attachment_prio", "attachment_bio"));
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
	}

	protected function get_payment_sum($arr)
	{
		$sum = 0;
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

	function get_vars($arr)
	{
		$data = parent::get_vars($arr);
		return $data;
	}

	/**
		@attrib name=remove_author is_public="1"  all_args=1
		@param key optional type=int
	**/
	function remove_author($arr)
	{
		unset($_SESSION["patent"]["authors"][$arr["key"]]);
		die('<script type="text/javascript">
			window.opener.location.reload();
			window.close();
			</script>'
		);
	}

	function fill_session($id)
	{
		$this->fill_session_property_vars = array("authorized_codes" , "job" , "procurator" , "additional_info", "type","undefended_parts", "word_mark" , "colors" , "trademark_character", "element_translation", "trademark_type", "priority" , "convention_nr" , "convention_country" , "exhibition_name", "exhibition_country", "exhibition" , "request_fee" , "classes_fee", "payer" , "doc_nr" , "warrant" , "reproduction" , "payment_order", "g_statues","c_statues");
		parent::fill_session($id);
	}
}

?>