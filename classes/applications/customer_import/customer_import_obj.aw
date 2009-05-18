<?php

class customer_import_obj extends _int_object
{
////////////////////////////////////////////////// process management support

	function getpidinfo($pid, $ps_opt="aux")
	{
		$ps=shell_exec("ps ".$ps_opt."p ".$pid);
		$ps=explode("\n", $ps);
  
		if(count($ps) < 2)
		{
			return false;
		}

		foreach($ps as $key=>$val)
		{
			$ps[$key]=explode(" ", ereg_replace(" +", " ", trim($ps[$key])));
		}

		foreach($ps[0] as $key=>$val)
		{
			$pidinfo[$val] = $ps[1][$key];
			unset($ps[1][$key]);
		}
  
		if(is_array($ps[1]))
		{
			$pidinfo[$val].=" ".implode(" ", $ps[1]);
		}

		if ($pidinfo["PID"] == null)
		{
			return false;
		}
		return $pidinfo;
	}


	static private function _status_fn($type, $wh_id = "")
	{
		return aw_ini_get("server.tmpdir")."/aw_cust_imp_".aw_ini_get("site_id")."_".$type."_".$wh_id;
	}

	function import_is_running($type, $wh_id = null)
	{
		$tf = self::_status_fn($type, $wh_id);
		if (file_exists($tf))
		{
			list($start_time, $pid, $state) = explode("\n", file_get_contents($tf));
			if (($pd = $this->getpidinfo($pid)) === false)
			{
				$this->write_import_end_log_entry($type, t("Staatuse kontrollis avastati protsessi kadumine"), false, $wh_id);
				unlink($tf);
				return false;
			}
			return $pid;
		}
		return false;
	}

	function get_import_log($type, $wh_id = "")
	{
		return $this->meta("import_log_".$type."_".$wh_id);
	}

	function import_status($type, $wh_id = "")
	{		
		$tf = self::_status_fn($type, $wh_id);
		if (file_exists($tf))
		{
			list($pid, $state, $count) = explode("\n", file_get_contents($tf));
			return $state;
		}
		return "Viga";
	}

	function full_import_status($type, $wh_id = "")
	{
		$tf = self::_status_fn($type, $wh_id);
		if (file_exists($tf))
		{
			return explode("\n", file_get_contents($tf), 7);
		}
		return "Viga";
	}

	function import_count($type, $wh_id = "")
	{		
		$tf = self::_status_fn($type, $wh_id);
		if (file_exists($tf))
		{
			list($pid, $state, $count) = explode("\n", file_get_contents($tf));
			return $count;
		}
		return "Viga";
	}

	function _int_stop($type, $wh_id = "")
	{
		$this->_update_status($type, warehouse_import_if::STATE_FINISHING, $wh_id);

		$sf = self::_status_fn($type, $wh_id);
		unlink($sf);
		if (file_exists($sf."_stop_flag"))
		{
			unlink($sf."_stop_flag");
		}
	}

	function _end_import_from_flag($type, $wh_id = "")
	{
		$this->write_import_end_log_entry($type, t("Kasutaja n&otilde;udis protsessi peatamist manuaalselt"), false, $wh_id);
		$this->_int_stop($type);
	}

	function _end_import($type, $wh_id = "")
	{
		$this->write_import_end_log_entry($type, t("L&otilde;ppes edukalt"), true, $wh_id);
		$this->_int_stop($type, $wh_id);
	}

	function reset_import($type, $wh_id = "")
	{
		$this->write_import_end_log_entry($type, t("Kasutaja resettis protsessi manuaalselt"), false, $wh_id);
		$sf = self::_status_fn($type, $wh_id);
		if (file_exists($sf))
		{
			unlink($sf);
		}
		if (file_exists($sf."_stop_flag"))
		{
			unlink($sf."_stop_flag");
		}
	}

	function stop_import($type, $wh_id = "")
	{
		if ($this->import_is_running($type, $wh_id))
		{
			$tf = self::_status_fn($type, $wh_id)."_stop_flag";
			touch($tf);
		}
	}

	function need_to_stop_now($type, $wh_id = "")
	{
		if ($this->import_is_running($type, $wh_id))
		{
			$tf = self::_status_fn($type, $wh_id)."_stop_flag";
			if (file_exists($tf))
			{
				//unlink($tf);
				return true;
			}
		}
		return false;
	}

	function _start_import($type, $wh_id = "")
	{
		$this->_update_status($type, warehouse_import_if::STATE_PREPARING, $wh_id);
	}

	function _update_status($type, $status, $wh_id = null, $count = null, $total = null, $info = null)
	{
		$tf = self::_status_fn($type, $wh_id);
		if (!file_exists($tf))
		{
			$start_time = time();
		}
		else
		{
			list($start_time, $t1, $t2, $t3, $t4, $t5, $t6) = explode("\n", file_get_contents($tf), 7);
			if ($count === null)
			{
				$count = $t4;
			}
			if ($total === null)
			{
				$total = $t5;
			}
			if ($info === null)
			{
				$info = $t6;
			}
		}
		$f = fopen($tf, "w");
		fwrite($f, $start_time."\n".getmypid()."\n".$status."\n".$wh_id."\n".$count."\n".$total."\n".$info);
		fclose($f);
	}


	function write_import_end_log_entry($type, $reason, $success = true, $wh_id = null)
	{
		// need to reload meta from database
		$this->_int_object($GLOBALS["object_loader"]->ds->get_objdata($this->id()));
		
		$typedata = $this->meta("import_log_".$type."_".$wh_id);
		if (!is_array($typedata))
		{
			$typedata = array();
		}
		if (count($typedata) > 9)
		{
			// cut off from the end
			array_pop($typedata);
		}

		$s = $this->full_import_status($type, $wh_id);

		array_unshift($typedata, array(
			"finish_tm" => time(),
			"full_status" => $s,
			"reason" => $reason,
			"success" => $success
		));
		$this->set_meta("import_log_".$type."_".$wh_id, $typedata);
		$this->save();
	}

	function start_customer_import()
	{
		while (ob_get_level()) { ob_end_clean(); }
		$this->_start_import("customer");

		$i = get_instance($this->prop("data_source"));

		$this->_categories($i);
		$this->_customers($i);
		$this->_persons($i);
		$this->_users($i);

		// finish
		$this->_end_import("customer");
		
	}

	private function _users($i)
	{
		// status fetch xml
		$this->_update_status("customer", customer_import_datasource::STATE_FETCH_USER_XML);
		$xml = $i->get_user_list_xml();
		
		$sx = new SimpleXMLElement($xml);
		$total = count($sx->user);

		$this->_update_status("customer", customer_import_datasource::STATE_PROCESS_USER_XML, null, 0, $total); 

		// process
		$this->_do_customer_import_process_users($sx);
	}

	private function _do_customer_import_process_users($sx)
	{
		$cur_list = $this->_list_current_users();
		$total = count($sx->user);
		$counter = 0;
		foreach($sx->user as $cat)
		{
			$ext_id = (string)$cat->extern_id;
			if (isset($cur_list[$ext_id]))
			{
				// update existing
				$this->_update_existing_user($cat, $cur_list[$ext_id]);
				unset($cur_list[$ext_id]);
			}
			else
			{
				// add new
				$this->_add_new_user($cat);
			}

			if ((++$counter % 10) == 1)
			{
				$this->_update_status("customers", customer_import_datasource::STATE_PROCESS_USER_XML, null, $counter, $total);
				if ($this->need_to_stop_now("customers"))
				{
					$this->_end_import_from_flag("customers");
					die("stopped for flag");
				}
			}
		}

		foreach($cur_list as $ext_id => $cat)
		{
			$this->_delete_unused_user($cat);
		}
	}

	private function _list_current_users()
	{
		$ol = new object_list(array(
			"class_id" => CL_USER,
			"lang_id" => array(),
			"site_id" => array()
		));
		$d = array();
		foreach($ol->arr() as $o)
		{
			$d[$o->uid] = $o;
		}
		return $d;
	}

	private function _update_existing_user($external, $aw)
	{
		// check if different
		$mod = false;
		foreach($external as $key => $value) 
		{
			if ($aw->$key != $value)
			{
				$mod = true;
				$aw->set_prop($key, $value);
			}
		}

		if ($mod)
		{
			$aw->save();
		}
	}

	private function _add_new_user($external)
	{
		$u = get_instance("core/users/user");
		$aw = $u->add_user(array(
			"uid" => (string)$external->uid,
			"email" => (string)$external->email,
			"password" => (string)$external->password,
			"real_name" => (string)$external->real_name,
			"person" => $this->_resolve_user_person($external)
		));
		$this->_update_existing_user($external, $aw);
	}

	private function _resolve_user_person($external)
	{
		$ext_id = (string)$external->person_external_id;
		if (trim($ext_id) != "")
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_PERSON,
				"lang_id" => array(),
				"site_id" => array(),
				"external_id" => $ext_id
			));
			if ($ol->count())
			{
				return $ol->begin()->id();
			}
		}
		return null;
	}

	private function _delete_unused_user($aw)
	{
		// TODO: implement
	}

	private function _persons($i)
	{
		// status fetch xml
		$this->_update_status("customer", customer_import_datasource::STATE_FETCH_PERSON_XML);
		$xml = $i->get_person_list_xml();
		
		$sx = new SimpleXMLElement($xml);
		$total = count($sx->person);

		$this->_update_status("customer", customer_import_datasource::STATE_PROCESS_PERSON_XML, null, 0, $total); 

		// process
		$this->_do_customer_import_process_persons($sx);	
	}

	private function _do_customer_import_process_persons($sx)
	{
		$cur_list = $this->_list_current_persons();
		$total = count($sx->person);
		$counter = 0;
		foreach($sx->person as $cat)
		{
			$ext_id = (string)$cat->extern_id;
			if (isset($cur_list[$ext_id]))
			{
				// update existing
				$this->_update_existing_person($cat, $cur_list[$ext_id]);
				unset($cur_list[$ext_id]);
			}
			else
			{
				// add new
				$this->_add_new_person($cat);
			}

			if ((++$counter % 10) == 1)
			{
				$this->_update_status("customers", customer_import_datasource::STATE_PROCESS_PERSON_XML, null, $counter, $total);
				if ($this->need_to_stop_now("customers"))
				{
					$this->_end_import_from_flag("customers");
					die("stopped for flag");
				}
			}
		}

		foreach($cur_list as $ext_id => $cat)
		{
			$this->_delete_unused_person($cat);
		}
	}

	private function _list_current_persons()
	{
		$ol = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"lang_id" => array(),
			"site_id" => array()
		));
		$d = array();
		foreach($ol->arr() as $o)
		{
			$d[$o->external_id] = $o;
		}
		return $d;
	}

	private function _update_existing_person($external, $aw, $mod = false)
	{
		// check if different
		foreach($external as $key => $value) 
		{
			if ($aw->$key != $value)
			{
				$mod = true;
				$aw->set_prop($key, $value);
			}
		}

		if ($mod)
		{
			$aw->save();
		}
	}

	private function _add_new_person($external)
	{
		$aw = obj();
		$aw->set_class_id(CL_CRM_PERSON);
		$aw->set_parent($this->id());
		$this->_update_existing_person($external, $aw, true);
	}

	private function _delete_unused_person($aw)
	{
		// TODO: implement
	}


	private function _customers($i)
	{
		// status fetch xml
		$this->_update_status("customer", customer_import_datasource::STATE_FETCH_CUSTOMER_XML);
		$xml = $i->get_customer_list_xml();
		
		$sx = new SimpleXMLElement($xml);
		$total = count($sx->customer);

		$this->_update_status("customer", customer_import_datasource::STATE_PROCESS_CUSTOMER_XML, null, 0, $total); 

		// process
		$this->_do_customer_import_process_customers($sx);	
	}

	private function _do_customer_import_process_customers($sx)
	{
		$cur_list = $this->_list_current_customers();
		$total = count($sx->customer);
		$counter = 0;
		foreach($sx->customer as $cat)
		{
			$ext_id = (string)$cat->extern_id;
			if (isset($cur_list[$ext_id]))
			{
				// update existing
				$this->_update_existing_customer($cat, $cur_list[$ext_id]);
				unset($cur_list[$ext_id]);
			}
			else
			{
				// add new
				$this->_add_new_customer($cat);
			}

			if ((++$counter % 10) == 1)
			{
				$this->_update_status("customers", customer_import_datasource::STATE_PROCESS_CUSTOMER_XML, null, $counter, $total);
				if ($this->need_to_stop_now("customers"))
				{
					$this->_end_import_from_flag("customers");
					die("stopped for flag");
				}
			}
		}

		foreach($cur_list as $ext_id => $cat)
		{
			$this->_delete_unused_customer($cat);
		}
	}

	private function _list_current_customers()
	{
		$co = obj($this->prop("company"));
		$d = array();
		foreach($co->get_customers_by_customer_data_objs()->arr() as $cust_co)
		{
			$d[$cust_co->extern_id] = $cust_co;
		}
		return $d;
	}

	private function _update_existing_customer($external, $aw)
	{
		// check if different
		$mod = false;
		foreach($external as $key => $value) 
		{
			if ($aw->$key != $value)
			{
				$mod = true;
				$aw->set_prop($key, $value);
			}
		}

		if ($mod)
		{
			$aw->save();
		}
	}

	private function _add_new_customer($external)
	{
		$co = obj($this->prop("company"));
		$aw = obj($co->add_customer((string)$external->name));
		$this->_update_existing_person($external, $aw, true);
	}

	private function _delete_unused_customer($aw)
	{
		// TODO: implement
	}

	private function _categories($i)
	{
		// status fetch xml
		$this->_update_status("customer", customer_import_datasource::STATE_FETCH_CATEGORY_XML);
		$xml = $i->get_category_list_xml();
		
		$sx = new SimpleXMLElement($xml);
		$total = count($sx->category);

		$this->_update_status("customer", customer_import_datasource::STATE_PROCESS_CATEGORY_XML, null, 0, $total); 

		// process
		$this->_do_customer_import_process_categories($sx);	
	}

	private function _do_customer_import_process_categories($sx)
	{
		$cur_list = $this->_list_current_categories();
		$total = count($sx->category);
		$counter = 0;
		foreach($sx->category as $cat)
		{
			$ext_id = (string)$cat->extern_id;
			if (isset($cur_list[$ext_id]))
			{
				// update existing
				$this->_update_existing_cat($cat, $cur_list[$ext_id]);
				unset($cur_list[$ext_id]);
			}
			else
			{
				// add new
				$this->_add_new_cat($cat);
			}

			if ((++$counter % 10) == 1)
			{
				$this->_update_status("customers", customer_import_datasource::STATE_PROCESS_CATEGORY_XML, null, $counter, $total);
				if ($this->need_to_stop_now("customers"))
				{
					$this->_end_import_from_flag("customers");
					die("stopped for flag");
				}
			}
		}

		foreach($cur_list as $ext_id => $cat)
		{
			$this->_delete_unused_cat($cat);
		}
	}

	private function _list_current_categories()
	{
		$co = obj($this->prop("company"));
		$existing = array();
		foreach($co->connections_from(array("type" => "RELTYPE_CATEGORY")) as $c)
		{
			$t = $c->to();
			if ($t->prop("extern_id"))
			{
				$existing[$c->prop("to")] = $t;
			}
		}
		return $existing;
	}

	private function _update_existing_cat($external, $aw, $mod = false)
	{
		// check if different
		foreach($external as $key => $value) 
		{
			if ($aw->$key != $value)
			{
				$mod = true;
				$aw->set_prop($key, $value);
			}
		}

		if ($mod)
		{
			$aw->save();
		}
	}

	private function _add_new_cat($external)
	{
		$aw = obj();
		$aw->set_class_id(CL_CRM_CATEGORY);
		$aw->set_parent($this->prop("company"));
		$this->_update_existing_cat($external, $aw, true);

		obj($this->prop("company"))->connect(array(
			"to" => $aw->id(),
			"type" => "RELTYPE_CATEGORY"
		));
	}

	private function _delete_unused_cat($aw)
	{
		// TODO: implement
	}
}

?>
