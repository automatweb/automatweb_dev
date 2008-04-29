<?php

class postal_codes_obj extends _int_object
{

	const DEFAULT_REGISTER_URL = "http://register.automatweb.com";

	const REQUEST_PAGE_SIZE = 1000;

	public function get_postal_codes($arr)
	{
		$add = "";
		if(isset($arr["from"]) && $arr["count"])
		{
			$add = " LIMIT ".((int)$arr["from"]).",".((int)$arr["count"]);
		}
		$i = get_instance(CL_POSTAL_CODES);
		$data = $i->db_fetch_array("SELECT * FROM aw_postal_codes".$add);
		if(!$data)
		{
			$data = false;
		}
		return $data;
	}

	public function get_code($arr)
	{
		$i = get_instance(CL_POSTAL_CODES);
		$where = array();
		$db_fields = self::get_db_fields();
		foreach($arr as $var => $val)
		{
			if($db_fields[$var])
			{
				$where[] = "`".$var."` LIKE '".htmlspecialchars($val)."'";
			}
		}
		if(isset($arr["house"]))
		{
			$hn = $arr["house"];
			if(is_numeric($hn))
			{
				$where[] = "`house_start` <= ".$hn."";
				$where[] = "`house_end` >= ".$hn."";
			}
			else
			{
				$where[] = "`house_start` = '".$hn."'";
				$where[] = "`house_end` = '".$hn."'";
			}
		}
		if(count($where))
		{
			$sql = implode(" AND ", $where);
			$data = $i->db_fetch_array("SELECT * FROM aw_postal_codes WHERE".$sql);
			if(count($data)==1)
			{
				return $data[0];
			}
			elseif(count($data)>1)
			{
				if($hn)
				{
					foreach($data as $row)
					{
						if($hn%2==0 && $row["house_start"]%2==0)
						{
							return $row;
						}
						elseif($hn%2==1 && $row["house_start"]%2==1)
						{
							return $row;
						}
					}
				}
				return false;
			}
		}
		return false;
	}

	public function get_file_fields($obj)
	{
		$fields = array("none" => "");
		$data = $this->get_file_data($obj);
		$row = $data[0];
		$csv_fields = $this->get_data_row($row);
		$fields = $fields + $csv_fields;
		return $fields;
	}

	public function get_db_fields()
	{
		$fields = array(
			"country" => t("Riik"),
			"state" => t("Maakond"),
			"city" => t("Linn / Alev / K&uuml;la"),
			"street" => t("T&auml;nav / Talu"),
			"house_start" => t("Maja nr algus"),
			"house_end" => t("Maja nr l&otilde;pp"),
			"zip" => t("Postiindeks"),
		);
		return $fields;
	}

	public function export_csv($arr)
	{
		$obj = obj($arr["id"]);
		$i = $obj->instance();
		$db_fields = $this->get_db_fields();
		$data = $this->get_postal_codes();
		$rows = array();
		$rows[] = $this->get_export_row($db_fields, $db_fields);
		foreach($data as $raw)
		{
			$rows[] = $this->get_export_row($raw, $db_fields);
		}
		$csv = implode(chr(13).chr(10), $rows);
		header("Content-type: text/csv");
		header("Content-Disposition: filename=data.csv");
		die($csv);
	}

	public function import_from_csv($arr)
	{
		$values = $arr["values"];
		$obj = obj($arr["id"]);
		$i = $obj->instance();
		$data = $this->get_file_data($obj);
		$sql = array();
		$rows = $this->get_import_rows($data, $values);
		$sql = $this->get_file_import_sql($rows);
		$this->import_from_sql($sql);
	}

	public function import_from_register($arr)
	{
		$obj = obj($arr["id"]);
		$i = $obj->instance();
		$site = ($url = $obj->prop("register_url"))?$url:self::DEFAULT_REGISTER_URL;
		$count = self::REQUEST_PAGE_SIZE;
		$end = false;
		$start = true;
		$data = array();
		$from = 0;
		//aw_global_set("xmlrpc_dbg", 1);
		while(!$end)
		{
			$result = $i->do_orb_method_call(array(
				"server" => $site,
				"action" => "get_postal_codes",
				"class" => "postal_codes",
				"params" => array(
					"from" => $from,
					"count" => $count,
				),
				"method" => "xmlrpc",
			));
			if(!is_array($result) && $start)
			{
				throw new awex_pcodes_xmlrpc("Unable to fetch data");
			}
			if(is_array($result))
			{
				$data = array_merge($data, $result);
				$from += $count;
				$start = false;
			}
			else
			{
				$end = true;
			}
		}
		$sql = $this->get_register_import_sql($data);
		$this->import_from_sql($sql);
	}

	private function get_export_row($raw, $fields)
	{
		foreach($fields as $field => $name)
		{
			$row[] = '"'.html_entity_decode($raw[$field]).'"';
		}
		$ret = implode(";", $row);
		return $ret;
	}

	private function get_file_data($obj)
	{
		$i = $obj->instance();
		$fid = $obj->prop("file");
		$data = array();
		if($i->can("view", $fid))
		{
			$fo = obj($fid);
			$fi = $fo->instance();
			$url = $fi->get_url($fid, $fo->prop("filename"));
			$data = @file($url);
			if(!$data)
			{
				throw new awex_pcodes_badfile("Unable to open file");
			}
		}
		return $data;
	}

	private function get_data_row($row)
	{
		$fields = explode(";", $row);
		if(count($fields)<2)
		{
			$fields = explode(",", $row);
		}
		if(count($fields)<2)
		{
			throw new awex_pcodes_badfile("File format incorrect. CSV expected");
		}
		return $fields;
	}

	private function get_import_rows($data, $values)
	{
		$db_fields = $this->get_db_fields();
		foreach($data as $id=>$raw)
		{
			if(!$id)
			{
				continue;
			}
			$row = $this->get_data_row($raw);
			$s_row = array();
			foreach($db_fields as $col=>$foo)
			{
				if(strlen($def = $values[$col]["default"]))
				{
					$val = $def;
				}
				else
				{
					$key = $values[$col]["csv"];
					$val = str_replace('"', '', $row[$key]);
				}
				$s_row[$col] = htmlspecialchars(trim($val), ENT_QUOTES);
			}
			$rows[] = $s_row;
		}
		return $rows;
	}

	private function get_file_import_sql($rows)
	{
		$sql = array();
		$db_fields = $this->get_db_fields();
		$db_cols = array_keys($db_fields);
		$alphabet = "ABCDEFGHIJKLMOPQRSTUV";
		for($i=0;$i<count($rows);$i++)
		{
			$row = $rows[$i];
			if(!is_numeric($row["house_end"]) && $row["house_start"] != $row["house_end"])
			{
				$chk1 = ereg("^([0-9]+)([A-Z]*$)", $row["house_start"], $tmp1);
				$chk2 = ereg("([0-9]+)([A-Z]{1})", $row["house_end"], $tmp2);
				if($chk1 && $chk2)
				{
					if($l = $tmp1[2])
					{
						$start = $l;
					}
					else
					{
						$start = "A";
					}
					$end = $tmp2[2];
					$letters = strstr($alphabet, $start);
					for($j = 0;$j<strlen($letters); $j++)
					{
						$letter = $letters[$j];
						$new_row = $row;
						$new_row["house_start"] = $new_row["house_end"] = $tmp2[1].$letter;
						$rows[] = $new_row;
					}
				}
			}
			$insert = "
				INSERT INTO aw_postal_codes(`".implode('`, `', $db_cols)."`)
				VALUES('".implode("', '", $row)."')";
			$sql[] = $insert;
		}
		return $sql;
	}

	private function get_register_import_sql($data)
	{
		$sql = array();
		foreach($data as $row)
		{
			unset($row["id"]);
			$fields = array_keys($row);
			$insert = "
				INSERT INTO aw_postal_codes(`".implode('`, `', $fields)."`)
				VALUES('".implode("', '", $row)."')";
			$sql[] = $insert;
		}
		return $sql;
	}

	private function import_from_sql($sql)
	{
		$i = get_instance(CL_POSTAL_CODES);
		if(count($sql))
		{
			$i->db_query("TRUNCATE TABLE aw_postal_codes");
			foreach($sql as $id=>$query)
			{
				$i->db_query($query);
			}
		}
	}
}

class awex_pcodes extends awex_obj {}
class awex_pcodes_badfile extends awex_pcodes {}
class awex_pcodes_xmlrpc extends awex_pcodes {}
?>
