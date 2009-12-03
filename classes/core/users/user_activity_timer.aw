<?php
class user_activity_timer extends core
{
	private $class_id;
	private $type;
	private $user;
	private $inactive_period;
	private $subject;

	public function __construct($arr = array())
	{
		foreach($arr as $k => $v)
		{
			$this->$k = $v;
		}
		$this->init();
	}

	
}
?>