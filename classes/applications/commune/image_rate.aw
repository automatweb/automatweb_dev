<?php
// selle me teeme täpselt nii nagu kliendibaasi otsingu. ehk, see lihtsalt genereerib ühe vormi
// and that's it. and that's that!
/*
@default group=general
@default form=rate

@property name type=text 
@caption Nimi

@property image type=text
@caption Pilt

@property image_comment type=text
@caption Pildi allkiri

@property current type=text
@caption Hinne

@property rate type=chooser
@caption Hinda

@property vsbt type=submit
@caption Hinda

@property comments type=comments
@caption Kommentaarium

@property vsbt2 type=submit
@caption Kirjuta

@property prof_id type=hidden
@caption Profiili id

@property img_id type=hidden
@caption Pildi id

@forminfo rate onload=init_rate onsubmit=test method=post
*/

class image_rate extends class_base
{
	function image_rate()
	{
		$this->init();
	}

	function init_rate($arr)
	{
		$this->inst->kala = "tursk";
		$q = "SELECT profile2image.* FROM profile2image LEFT JOIN objects ON (profile2image.img_id = objects.oid) WHERE objects.status = 2 ORDER BY rand()";
                $this->db_query($q);
                $row = $this->db_next();

		$this->inst->profile_data = new object($row["prof_id"]);

		// figure out person data
		$conns = $this->inst->profile_data->connections_to(array(
			"type" => 14,
		));

		if (sizeof($conns) > 0)
		{
			$c1 = reset($conns);
			$this->inst->person_data = $c1->from();
		}
		else
		{
			$this->inst_person_data = new object();
		};

		$this->inst->image_data = new object($row["img_id"]);

		$rt = get_instance(CL_RATE);

		$this->inst->current = $rt->get_rating_for_object($row["img_id"]);

	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		switch($prop["name"])
		{
			case "name":
				// this is profile object, I need the person object
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("show_profile", array("id" => $this->person_data->id()), "commune"),
					"caption" => $this->person_data->prop("firstname") . " " . $this->person_data->prop("lastname")
				));
				break;

			case "image":
				$i = get_instance(CL_IMAGE);
				$imgdata = $i->get_image_by_id($this->image_data->id());

				$prop["value"] .= html::img(array(
					"url" => $imgdata["url"],
				));

				break;

			case "image_comment":
				$prop["value"] = $this->image_data->comment();
				break;

			case "rate":
				$prop["options"] = array(
					"1" => "1",
					"2" => "2",
					"3" => "3",
					"4" => "4",
					"5" => "5",
				);
				break;

			case "prof_id":
				$prop["value"] = $this->profile_data->id();
				break;

			case "img_id":
				$prop["value"] = $this->image_data->id();
				break;

			case "current":
				$prop["value"] = $this->current;
				break;

			case "comments":
				$prop["use_parent"] = $this->image_data->id();
				break;


		};
		return PROP_OK;
	}

};
?>
