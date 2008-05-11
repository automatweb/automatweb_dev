<?php
// utility_model.aw - Kasulik mudel
/*

@classinfo syslog_type=ST_UTILITY_MODEL relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@extends applications/clients/patent_office/intellectual_property
@tableinfo aw_trademark index=aw_oid master_table=objects master_index=brother_of

@groupinfo author caption="Autor"
@default group=author
	@property author_first_name type=textbox
	@caption Eesnimi

	@property author_last_name type=textbox
	@caption Perekonnanimi

	@property author_address type=textbox
	@caption Aadress

	@property author_country_code type=textbox
	@caption Riigi kood

	@property author_disallow_disclose type=checkbox ch_value=1
	@caption Mitte avalikustada minu nime autorina


@groupinfo invention caption="Leiutise nimetus"
@default group=invention
	@property invention_name type=textbox
	@caption Leiutise nimetus


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
	@caption Esitatud patenditaotluse p&otilde;hjal
		@property prio_prevapplicationsep_date type=date_select
		@caption Kuup&auml;ev

		@property prio_prevapplicationsep_nr type=textbox
		@caption Taotluse number

	@property childtitle112 type=text store=no subtitle=1
	@caption Varasema taotluse alusel (seaduse &#0167;10 l&otilde;ige 3)
		@property prio_prevapplication_date type=date_select
		@caption Kuup&auml;ev

		@property prio_prevapplication_nr type=textbox
		@caption Taotluse number

@groupinfo attachments caption="Lisad"
@default group=attachments
 	@property attachment_invention_description type=fileupload reltype=RELTYPE_ATTACHMENT_INVENTION_DESCRIPTION form=+emb
	@caption Leiutiskirjeldus

 	@property attachment_seq type=fileupload reltype=RELTYPE_ATTACHMENT_SEQ form=+emb
	@caption J&auml;rjestuse loetelu

 	@property attachment_demand type=fileupload reltype=RELTYPE_ATTACHMENT_DEMAND form=+emb
	@caption Kasuliku mudeli n&otilde;udlus

 	@property attachment_demand_points type=textbox size=3
	@caption Kasuliku mudeli n&otilde;udlus, n&otilde;udluspunkti

 	@property attachment_summary_et type=fileupload reltype=RELTYPE_ATTACHMENT_SUMMARY_ET form=+emb
	@caption Leiutise olemuse l&uuml;hikokkuv&otilde;te

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

 	@property attachment_prio_trans type=fileupload reltype=RELTYPE_ATTACHMENT_PRIO_TRANS form=+emb
	@caption Prioriteedin&otilde;uet t&otilde;endavate dokumentide t&otilde;lked


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

@reltype ATTACHMENT_PRIO_TRANS value=110 clid=CL_FILE
@caption Lisa prioriteeditoendi tolked

*/

class utility_model extends intellectual_property
{
	function __construct()
	{
		parent::__construct();
		$this->init(array(
			"tpldir" => "applications/patent",
			"clid" => CL_UTILITY_MODEL
		));
		$this->info_levels[11] = "author";
		$this->info_levels[12] = "invention_um";
		$this->info_levels[14] = "attachments_um";
		$this->pdf_file_name = "KasulikuMudeliTaotlus";
		$this->show_template = "show_um.tpl";
		$this->file_upload_vars = array_merge($this->file_upload_vars, array("attachment_invention_description", "attachment_seq", "attachment_demand", "attachment_summary_et", "attachment_dwgs", "attachment_fee", "attachment_warrant", "attachment_prio", "attachment_prio_trans"));
	}

	protected function save_priority($patent)
	{
		$patent->set_prop("prio_convention_date" , $_SESSION["patent"]["prio_convention_date"]);
		$patent->set_prop("prio_convention_country" , $_SESSION["patent"]["prio_convention_country"]);
		$patent->set_prop("prio_convention_nr" , $_SESSION["patent"]["prio_convention_nr"]);
		$patent->set_prop("prio_prevapplicationsep_date" , $_SESSION["patent"]["prio_prevapplicationsep_date"]);
		$patent->set_prop("prio_prevapplicationsep_nr" , $_SESSION["patent"]["prio_prevapplicationsep_nr"]);
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
			$patent->set_class_id(CL_UTILITY_MODEL);
			$patent->set_parent($_SESSION["patent"]["parent"]);
			$patent->save();
			$patent->set_name(" Kinnitamata taotlus nr [".$patent->id()."]");
		}
	}

	protected function get_payment_sum($arr)
	{
		$sum = 0;
		$is_corporate = false;

		foreach($_SESSION["patent"]["applicants"] as $val)
		{
			if($val["applicant_type"])
			{
				$is_corporate = true;
				break;
			}
		}

		$sum = $is_corporate ? 1600 : 400;
		return $sum;
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

	function get_vars($arr)
	{
		$data = parent::get_vars($arr);
		return $data;
	}


	function fill_session($id)
	{
		$this->fill_session_property_vars = array("authorized_codes" , "job" , "procurator" , "additional_info", "type","undefended_parts", "word_mark" , "colors" , "trademark_character", "element_translation", "trademark_type", "priority" , "convention_nr" , "convention_country" , "exhibition_name", "exhibition_country", "exhibition" , "request_fee" , "classes_fee", "payer" , "doc_nr" , "warrant" , "reproduction" , "payment_order", "g_statues","c_statues");
		parent::fill_session($id);
	}
}

?>
