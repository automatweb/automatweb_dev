<?php
// euro_patent_et_desc.aw - Patent
/*

@classinfo syslog_type=ST_EURO_PATENT_ET_DESC relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@extends applications/clients/patent_office/intellectual_property


@default group=general
	@property applicant type=relpicker reltype=RELTYPE_APPLICANT
	@caption Patendiomanik

	@property signed type=text store=no editonly=1
	@caption Allkirja staatus

	@property signatures type=text store=no editonly=1
	@caption Allkirjastajad

	@property procurator type=relpicker reltype=RELTYPE_PROCURATOR
	@caption Volinik

	@property verified type=checkbox
	@caption Kinnitatud

	@property exported type=checkbox caption=no
	@caption Eksporditud

	@property export_date type=date_select
	@caption Ekspordi kuup&auml;ev

	@property nr type=textbox
	@caption Taotluse number


@groupinfo invention caption="Leiutis"
@default group=invention
	@property epat_nr type=textbox
	@caption Euroopa patendi number

	@property epat_date type=date_select year_from=2002
	@caption Euroopa patenditaotluse esitamise kuup&auml;ev

	@property invention_name_et type=textbox
	@caption Leiutise nimetus (eesti keeles)

 	@property epat_desc_trans type=fileupload reltype=RELTYPE_INVENTION_DESCRIPTION_TRANS form=+emb
	@caption Patendikirjelduse t&otilde;lge


// RELTYPES
@reltype INVENTION_DESCRIPTION_TRANS value=100 clid=CL_FILE
@caption Patendikirjelduse t6lge

*/

class euro_patent_et_desc extends intellectual_property
{
	public static $level_index = array(
		0 => 0,
		1 => 12,
		2 => 4,
		3 => 5
	);

	function __construct()
	{
		parent::__construct();
		$this->init(array(
			"tpldir" => "applications/patent",
			"clid" => CL_EURO_PATENT_ET_DESC
		));
		$this->info_levels = array(
			0 => "owner",
			12 => "invention_epat",
			4 => "fee_epat",
			5 => "check"
		);
		$this->pdf_file_name = "EuroopaPatendiT6lkeTaotlus";
		$this->show_template = "show_epat.tpl";
		$this->date_vars = array_merge($this->date_vars, array("epat_date"));
		$this->file_upload_vars = array_merge($this->file_upload_vars, array("epat_desc_trans"));
		$this->text_vars = array_merge($this->text_vars, array("invention_name_et", "epat_nr"));
		$this->checkbox_vars = array_merge($this->checkbox_vars, array("author_disallow_disclose"));

		//siia panev miskid muutujad mille iga ringi peal 2ra kustutab... et uuele taotlejale vana info ei j22ks
		$this->datafromobj_del_vars = array("name_value" , "email_value" , "phone_value" , "fax_value" , "code_value" ,"email_value" , "street_value" ,"index_value" ,"country_code_value","city_value","county_value","correspond_street_value", "correspond_index_value" , "correspond_country_code_value" , "correspond_city_value","correspond_county_value", "name");
		$this->datafromobj_vars = array_merge($this->datafromobj_vars, array("invention_name_et", "epat_date", "epat_desc_trans", "epat_nr"));
	}

	protected function save_forms($patent)
	{
		$this->save_applicants($patent);
		$this->save_fee($patent);
		$this->save_invention($patent);
		$this->fileupload_save($patent);
		$this->final_save($patent);
	}

	protected function save_invention($patent)
	{
		$patent->set_prop("invention_name_et" , $_SESSION["patent"]["invention_name_et"]);
		$patent->set_prop("epat_date" , $_SESSION["patent"]["epat_date"]);
		$patent->set_prop("epat_nr" , $_SESSION["patent"]["epat_nr"]);
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
			$patent->set_class_id(CL_EURO_PATENT_ET_DESC);
			$patent->set_parent($_SESSION["patent"]["parent"]);
			$patent->save();
			$patent->set_name(" Kinnitamata taotlus nr [".$patent->id()."]");
		}

		return $patent;
	}

	protected function get_payment_sum()
	{
		$sum = $this->get_request_fee();
		return $sum;
	}

	private function get_request_fee()
	{
		$sum = 700;
		return $sum;
	}

	function get_vars($arr)
	{
		$data = parent::get_vars($arr);
		$_SESSION["patent"]["request_fee"]= $this->get_request_fee();
		return $data;
	}

	function check_fields()
	{
		$err = parent::check_fields();

		if(((int) $_POST["data_type"]) === 12)
		{
			foreach ($_FILES as $var => $file_data)
			{
				if (is_uploaded_file($file_data["tmp_name"]))
				{
					if ("epat_desc_trans_upload" === $var)
					{
						$fp = fopen($file_data["tmp_name"], "r");
						flock($fp, LOCK_SH);
						$sig = fread($fp, 4);
						fclose($fp);
						if("%PDF" !== $sig)
						{
							unset($_FILES[$var]["tmp_name"]);
							$err.= t("Ainult pdf formaadis fail lubatud")."\n<br>";
						}
					}
				}
			}

			if(empty($err))
			{
				$_SESSION["patent"]["checked"][] = 18;
			}
		}

		return $err;
	}

	function fill_session($id)
	{
		parent::fill_session($id);
	}
}

?>
