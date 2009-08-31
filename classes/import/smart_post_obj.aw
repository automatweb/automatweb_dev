<?php

class smart_post_obj extends _int_object
{
	// This is for smart_post CB class only!
	public function get_data_by_cities()
	{
		$raw_data = file_get_contents($this->prop("xml_source"));
		$p = xml_parser_create();
		xml_parse_into_struct($p, $raw_data, $datas);
		xml_parser_free($p);
		$ret = array();
		foreach($datas as $data)
		{
			if($data["tag"] === "PLACE" && $data["type"] == "open")
			{
				$tmp = array();
			}
			elseif($data["tag"] === "PLACE" && $data["type"] == "close" && $this->prop("show_inactive") + $tmp["ACTIVE"] > 0)
			{
				$ret[$tmp["CITY"]][$tmp["PLACE_ID"]] = $tmp;
			}
			elseif($data["level"] == 3 && $data["type"] === "complete")
			{
				$tmp[$data["tag"]] = iconv("UTF-8", aw_global_get("charset"), ifset($data, "value"));
			}
		}
		return $ret;
	}

	public function get_automates_by_city()
	{
		$data = $this->get_data_by_cities();
		$jrk = $this->get_sorted_cities();
		$ret = array();
		foreach($jrk as $j)
		{
			$ret[$j["city"]] = $data[$j["city"]];
		}
		return $ret;
	}

	public function sort_cities($a, $b)
	{
		if($a["jrk"] === $b["jrk"])
		{
			return strcmp($a["city"], $b["city"]);
		}
		else
		{
			return $a["jrk"] > $b["jrk"] ? 1 : -1;
		}
	}

	protected function get_sorted_cities()
	{
		$jrk = $this->meta("cities");
		foreach($jrk as $k => $v)
		{
			$jrk[$k] = array(
				"jrk" => $v,
				"city" => $k,
			);
		}
		uasort($jrk, array($this, "sort_cities"));
		return $jrk;
	}

	public static function get_smart_post()
	{
		$ol = new object_list(array(
			"class_id" => CL_SMART_POST,
		));
		return $ol->begin();
	}

	public function get_place_name_by_id($id)
	{
		foreach($this->get_data_by_cities() as $city => $places)
		{
			foreach($places as $key => $place)
			{
				if($key == $id)
				{
					return $place["NAME"];
				}
			}
		}
		return "";
	}

}

?>
