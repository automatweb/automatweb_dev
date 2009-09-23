<?php
class user_activity extends core
{
	public function __construct()
	{
		$this->init();
		$this->check_db();
	}

	public function log_modified_props($clid, $props_modified)
	{
		foreach($props_modified as $prop => $modified)
		{
			if($modified)
			{
				$this->db_query(sprintf("
					INSERT INTO 
						aw_user_activity_property_log (aw_class_id, aw_property, aw_user, aw_timestamp) 
					VALUES 
						(%u, '%s', %u, %u)
					ON DUPLICATE KEY UPDATE
						aw_timestamp = %s
					",
					$clid,
					$prop,
					aw_global_get("uid_oid"),
					time(),
					time()
				));
			}
		}
	}

	public function log_property_group_display($clid, $group)
	{
		$this->db_query(sprintf("
			INSERT INTO 
				aw_user_activity_group_log (aw_class_id, aw_group, aw_user, aw_timestamp) 
			VALUES 
				(%u, '%s', %u, %u)
			ON DUPLICATE KEY UPDATE
				aw_timestamp = %u
			",
			$clid, $group, aw_global_get("uid_oid"), time(), time()
		));
	}

	public function get_timers_for_group()
	{
		return $this->get_timers_for_type(1);
	}

	public function get_timers_for_property()
	{
		return $this->get_timers_for_type(0);
	}

	public function is_timer_active_for_group($clid, $group)
	{
		return $this->is_timer_active_for_type(1, $clid, $group);
	}

	public function is_timer_active_for_property($clid, $property)
	{
		return $this->is_timer_active_for_type(0, $clid, $property);
	}

	public function activate_timer_for_group($clid, $group)
	{
		$this->activate_timer_for_type(1, $clid, $group);
	}

	public function activate_timer_for_property($clid, $property)
	{
		$this->activate_timer_for_type(0, $clid, $property);
	}

	public function inactivate_timer_for_group($clid, $group)
	{
		$this->inactivate_timer_for_type(1, $clid, $group);
	}

	public function inactivate_timer_for_property($clid, $property)
	{
		$this->inactivate_timer_for_type(0, $clid, $property);
	}

	protected function get_timers_for_type($type)
	{
		$timers = array();
		foreach($this->db_fetch_array(sprintf("SELECT * FROM aw_user_activity_deactivation_timers WHERE aw_type = '%u'", $type)) as $timer)
		{
			$timers[] = new user_activity_timer(array(
				"class_id" => $timer["aw_class_id"],
				"type" => $timer["aw_type"],
				"user" => $timer["aw_user"],
				"inactive_period" => $timer["aw_inactive_period"],
				"subject" => $timer["aw_subject"],
			));
		}
		return $timers;
	}

	protected function inactivate_timer_for_type($type, $clid, $subject)
	{
		$this->db_query(sprintf("
			DELETE FROM
				aw_user_activity_deactivation_timers
			WHERE 
				aw_class_id = %u AND
				aw_type = %u AND
				aw_user = %u AND
				aw_subject = '%s'",
//			$clid, $type, aw_global_get("uid_oid"), $subject
			$clid, $type, 0, $subject
		));
	}

	protected function is_timer_active_for_type($type, $clid, $subject)
	{
		$this->db_query(sprintf("
			SELECT aw_inactive_period FROM
				aw_user_activity_deactivation_timers
			WHERE 
				aw_class_id = %u AND
				aw_type = %u AND
				aw_user = %u AND
				aw_subject = '%s'
			LIMIT 1",
//			$clid, $type, aw_global_get("uid_oid"), $subject
			$clid, $type, 0, $subject
		));
		return $this->num_rows() > 0;
	}

	protected function activate_timer_for_type($type, $clid, $subject, $period = 2592000)
	{
		$this->db_query(sprintf("
			INSERT INTO 
				aw_user_activity_deactivation_timers (aw_class_id, aw_type, aw_user, aw_inactive_period, aw_subject)
			VALUES
				(%u, %u, %u, %u, '%s')
			ON DUPLICATE KEY UPDATE
				aw_inactive_period = %u",
//			$clid, $type, aw_global_get("uid_oid"), $period, $subject, $period
			$clid, $type, 0, $period, $subject, $period
		));
	}

	protected function check_db()
	{
		foreach(array("aw_user_activity_property_log", "aw_user_activity_group_log", "aw_user_activity_deactivation_timers") as $t)
		{
			if($this->db_query("SELECT * FROM ".$t." LIMIT 1", false) === false)
			{
				$this->do_db_upgrade($t);
			}
		}
	}

	public function do_db_upgrade($t, $f = "")
	{
		if (empty($f) && $t === "aw_user_activity_property_log")
		{
			$this->db_query("CREATE TABLE aw_user_activity_property_log (aw_class_id int, aw_property varchar(255), aw_user int, aw_timestamp int)");
			$this->db_query("ALTER TABLE aw_user_activity_property_log ADD UNIQUE aw_property_in_class_per_user (aw_class_id, aw_property, aw_user)");
			return true;
		}
		elseif (empty($f) && $t === "aw_user_activity_group_log")
		{
			$this->db_query("CREATE TABLE aw_user_activity_group_log (aw_class_id int, aw_group varchar(255), aw_user int, aw_timestamp int)");
			$this->db_query("ALTER TABLE aw_user_activity_group_log ADD UNIQUE aw_group_in_class_per_user (aw_class_id, aw_group, aw_user)");
			return true;
		}
		elseif (empty($f) && $t === "aw_user_activity_deactivation_timers")
		{
			//aw_type = 1 for group, 0 for property
			$this->db_query("CREATE TABLE aw_user_activity_deactivation_timers (aw_class_id int, aw_type int, aw_user int, aw_inactive_period int, aw_subject varchar(255))");
			$this->db_query("ALTER TABLE aw_user_activity_deactivation_timers ADD UNIQUE aw_subject_with_type_in_class_for_user (aw_class_id, aw_type, aw_subject, aw_user)");
			return true;
		}
		return false;
	}
}
?>