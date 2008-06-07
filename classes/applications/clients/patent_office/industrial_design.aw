<?php
// industrial_design.aw - T88stusdisainilahendus
/*

@classinfo syslog_type=ST_INDUSTRIAL_DESIGN relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@extends applications/clients/patent_office/intellectual_property


@default group=general
	@property applicant_reg type=chooser store=no
	@caption Taotleja on saanud t&ouml;&ouml;stusdisaini registreerimise taotlemise &otilde;iguse kui


@groupinfo author caption="Autor"
@default group=author
	@property author type=relpicker reltype=RELTYPE_AUTHOR
	@caption Autor

	@property author_disallow_disclose type=checkbox ch_value=1 store=no
	@caption Mitte avalikustada minu nime autorina


@groupinfo industrial_design caption="T&ouml;&ouml;stusdisainilahendus"
@default group=industrial_design
	@property industrial_design_name type=textbox
	@caption T&ouml;&ouml;stusdisainilahenduse nimetus

	@property industrial_design_variant type=select
	@caption T&ouml;&ouml;stusdisainilahenduse variantide arv

	@property industrial_design_variant_count type=select
	@caption Variantide arv


@default group=priority
	@property childtitle110 type=text store=no subtitle=1
	@caption Pariisi konventsiooni vm. kokkuleppe taotluse alusel
		@property prio_convention_date type=date_select
		@caption Kuup&auml;ev

		@property prio_convention_country type=textbox
		@caption Riigi kood

		@property prio_convention_nr type=textbox
		@caption Taotluse number


@groupinfo process_postpone caption="Menetluse peatamise n&otilde;ue"
@default group=process_postpone
	@property process_postpone type=select
	@caption Taotleja soovib t&ouml;&ouml;stusdisainilahenduse registrisse kandmist edasi l&uuml;kata

@groupinfo docs caption="Dokumentide loetelu"
@default group=docs

	@property doc_repro type=multifile_upload reltype=RELTYPE_DOC_REPRO form=+emb
	@caption Reproduktsioon(id)

	@property doc_warrant type=fileupload reltype=RELTYPE_DOC_WARRANT form=+emb
	@caption Volikiri

	@property doc_description type=fileupload reltype=RELTYPE_DOC_DESCRIPTION form=+emb
	@caption T&ouml;&ouml;stusdisainilahenduse kirjeldus


// RELTYPES
@reltype AUTHOR value=17 clid=CL_CRM_PERSON
@caption Autor

@reltype DOC_DESCRIPTION value=100 clid=CL_FILE
@caption Lisa kirjeldus

@reltype DOC_REPRO value=101 clid=CL_FILE
@caption Lisa reproduktsioon

@reltype DOC_WARRANT value=102 clid=CL_FILE
@caption Lisa volikiri


*/

class industrial_design extends intellectual_property
{
	public static $level_index = array(
		0 => 0,
		1 => 11,
		2 => 16,
		3 => 3,
		4 => 17,
		5 => 4,
		6 => 18,
		7 => 5
	);

	function __construct()
	{
		parent::__construct();
		$this->init(array(
			"tpldir" => "applications/patent",
			"clid" => CL_INDUSTRIAL_DESIGN
		));
		$this->info_levels = array(
			0 => "applicant_ind",
			11 => "author",
			16 => "industrial_design",
			3 => "priority_ind",
			17 => "process_postpone",
			4 => "fee_ind",
			18 => "docs",
			5 => "check"
		);
		$this->pdf_file_name = "T".chr(246).chr(246)."stusdisainiLahenduseTaotlus";
		$this->show_template = "show_ind.tpl";
		$this->date_vars = array_merge($this->date_vars, array("prio_convention_date"));
		$this->file_upload_vars = array_merge($this->file_upload_vars, array("doc_warrant", "doc_description"));
		$this->multifile_upload_vars = array_merge($this->multifile_upload_vars, array("doc_repro"));
		$this->text_vars = array_merge($this->text_vars, array("industrial_design_name", "prio_convention_country", "prio_convention_nr"));
		$this->checkbox_vars = array_merge($this->checkbox_vars, array("author_disallow_disclose"));
		$this->select_vars = array_merge($this->select_vars, array("industrial_design_variant", "industrial_design_variant_count", "process_postpone","applicant_reg"));

		//siia panev miskid muutujad mille iga ringi peal 2ra kustutab... et uuele taotlejale vana info ei j22ks
		$this->datafromobj_del_vars = array("name_value" , "email_value" , "phone_value" , "fax_value" , "code_value" ,"email_value" , "street_value" ,"index_value" ,"country_code_value","city_value","county_value","correspond_street_value", "correspond_index_value" , "correspond_country_code_value" , "correspond_county_value", "correspond_city_value", "name", "applicant_reg");
		$this->datafromobj_vars = array_merge($this->datafromobj_vars, array("prio_convention_date", "prio_convention_country", "prio_convention_nr", "doc_repro", "doc_warrant", "doc_description", "industrial_design_variant", "industrial_design_variant_count", "process_postpone", "industrial_design_name", "applicant_reg"));
	}

	public function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "process_postpone":
				$prop["post_append_text"] = " " . t("kuuks");
				$prop["options"] = $arr["obj_inst"]->get_process_postpone_options();
				break;

			case "applicant_reg":
				$prop["options"] = $arr["obj_inst"]->get_applicant_reg_options();
				break;

			case "industrial_design_variant":
				$prop["options"] = $arr["obj_inst"]->get_industrial_design_variant_options();
				break;

			case "industrial_design_variant_count":
				$prop["options"] = $arr["obj_inst"]->get_industrial_design_variant_count_options();
				break;

			default:
				$retval = parent::get_property($arr);
		}
		return $retval;
	}

	protected function save_priority($patent)
	{
		$patent->set_prop("prio_convention_date" , $_SESSION["patent"]["prio_convention_date"]);
		$patent->set_prop("prio_convention_country" , $_SESSION["patent"]["prio_convention_country"]);
		$patent->set_prop("prio_convention_nr" , $_SESSION["patent"]["prio_convention_nr"]);
		$patent->save();
	}

	protected function save_forms($patent)
	{
		$this->save_priority($patent);
		$this->save_fee($patent);
		$this->save_industrial_design($patent);
		$this->save_applicants($patent);
		$this->save_authors($patent);
		$this->fileupload_save($patent);
		$this->multifile_upload_save($patent);
		$this->save_process_postpone($patent);
		$this->final_save($patent);
	}

	protected function save_industrial_design($patent)
	{
		$patent->set_prop("industrial_design_name" , $_SESSION["patent"]["industrial_design_name"]);
		$patent->set_prop("industrial_design_variant" , $_SESSION["patent"]["industrial_design_variant"]);
		$patent->set_prop("industrial_design_variant_count" , $_SESSION["patent"]["industrial_design_variant_count"]);
		$patent->save();
	}

	protected function save_process_postpone($patent)
	{
		$patent->set_prop("process_postpone" , $_SESSION["patent"]["process_postpone"]);
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
			$patent->set_class_id(CL_INDUSTRIAL_DESIGN);
			$patent->set_parent($_SESSION["patent"]["parent"]);
			$patent->save();
			$patent->set_name(" Kinnitamata taotlus nr [".$patent->id()."]");
		}

		return $patent;
	}

	protected function get_payment_sum()
	{
		$sum = $this->get_request_fee();

		if(!empty($_SESSION["patent"]["industrial_design_variant_count"]) and 2 < $_SESSION["patent"]["industrial_design_variant_count"])
		{
			$sum += $_SESSION["patent"]["industrial_design_variant_count"] * 400;
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

		$sum = $is_corporate ? 1600 : 400;
		return $sum;
	}

	function get_vars($arr)
	{
		$data = parent::get_vars($arr);

		$_SESSION["patent"]["request_fee"]= $this->get_payment_sum();

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

		if(((int) $_POST["data_type"]) === 18)
		{
			foreach ($_FILES as $var => $file_data)
			{
				if ("file" === $var)
				{
					foreach ($file_data["error"] as $key => $e)
					{
						if (UPLOAD_ERR_OK == $e)
						{
							$fp = fopen($file_data["tmp_name"][$key], "r");
							flock($fp, LOCK_SH);
							$sig = fread($fp, 11);
							fclose($fp);

							$sig1 = substr($sig, 0, 6);
							$sig2 = substr($sig, 0, 4);
							$sig3 = substr($sig, 6, 5);
							$jpg_sig1 = chr(255) . chr(216) . chr(255) . chr(225);
							$jpg_sig2 = "EXIF" . chr(0);
							$jpg_sig4 = chr(255) . chr(216) . chr(255) . chr(224);
							$jpg_sig3 = "JFIF" . chr(0);
							if(
								"GIF87a" !== $sig1 and
								"GIF89a" !== $sig1 and
								($sig2 !== $jpg_sig1 and $sig3 !== $jpg_sig2 or $sig2 === $jpg_sig1 xor $sig3 === $jpg_sig2) and
								($sig2 !== $jpg_sig4 and $sig3 !== $jpg_sig3 or $sig2 === $jpg_sig4 xor $sig3 === $jpg_sig3)
							)
							{
								unset($_FILES[$var]["tmp_name"][$key]);
								$err.= t("Reproduktsioon peab olema GIF v&otilde;i JPEG formaadis")."\n<br>";
							}
						}
					}
				}

				if (is_uploaded_file($file_data["tmp_name"]))
				{
					if ("doc_description" === $var)
					{
						$fp = fopen($file_data["tmp_name"], "r");
						flock($fp, LOCK_SH);
						$sig = fread($fp, 4);
						fclose($fp);
						if("%PDF" !== $sig)
						{
							unset($_FILES[$var]["tmp_name"]);
							$err.= t("Kirjeldusel ainult pdf formaadis failid lubatud")."\n<br>";
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
				if($this->can("view" , $address_obj->prop("maakond")))
				{
					$county = obj($address_obj->prop("maakond"));
					$_SESSION["patent"]["authors"][$key]["county"] = $county->name();
				}
				$_SESSION["patent"]["authors"][$key]["country_code"] = $address_inst->get_country_code($address_obj->prop("riik"));
			}
		}
	}
}

?>
