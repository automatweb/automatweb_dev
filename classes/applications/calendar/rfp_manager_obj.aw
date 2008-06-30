<?php
class rfp_manager_obj extends _int_object
{

	/**
		@comment
			Fetches rooms from folder pointed by room_folder prop
		@returns
			Object list of rooms
	 **/
	public function get_rooms_from_room_folder()
	{
		$ol = new object_list(array(
			"class_id" => CL_ROOM,
			"parent" => $this->prop("room_folder"),
		));
		return $ol;
	}
}
?>
