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
 	@property attachment_invention_description type=checkbox ch_value=1
	@caption Leiutiskirjeldus

 	@property attachment_seq type=checkbox ch_value=1
	@caption J&auml;rjestuse loetelu

 	@property attachment_demand type=checkbox ch_value=1
	@caption Kasuliku mudeli n&otilde;udlus, ............ n&otilde;udluspunkti

 	@property attachment_summary type=checkbox ch_value=1
	@caption Leiutise olemuse l&uuml;hikokkuv&otilde;te

 	@property attachment_dwgs type=checkbox ch_value=1
	@caption Illustratsioonid

 	@property attachment_fee type=checkbox ch_value=1
	@caption Riigil&otilde;ivu tasumist t&otilde;endav dokument

 	@property attachment_warrant type=checkbox ch_value=1
	@caption Volikiri

 	@property attachment_prio type=checkbox ch_value=1
	@caption Prioriteedin&otilde;uet t&otilde;endavad dokumendid

 	@property attachment_prio_trans type=checkbox ch_value=1
	@caption Prioriteedin&otilde;uet t&otilde;endavate dokumentide t&otilde;lked


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
		$classes = array();
		$sum = 0;
		if(is_array($_SESSION["patent"]["products"]) && sizeof($_SESSION["patent"]["products"]))
		{
			$classes_fee = (sizeof($_SESSION["patent"]["products"]) - 1 )*700;
			if($_SESSION["patent"]["co_trademark"] || $_SESSION["patent"]["guaranty_trademark"])
			{
				$sum = 3000;
			}
			else
			{
				$sum = 2200;
			}
			$sum = $sum + $classes_fee;
			$_SESSION["patent"]["classes_fee"] = $classes_fee;
		}
		return $sum;
	}
}

?>
