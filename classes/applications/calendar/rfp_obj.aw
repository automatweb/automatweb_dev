<?php
class rfp_obj extends _int_object
{
	/** Returns all reservations for this rfp
		@attrib api=1
	 **/
	public function get_reservations()
	{
		$connections = $this->connections_from(array(
			"type" => "RELTYPE_RESERVATION",
		));
		$return = array();
		$gather_res_props = array(
			"id", "people_count", "start1", "end", "resource",
		);
		$gather_res_meta = array(
			"amount"
		);
		foreach($connections as $conn)
		{
			$reservation = $conn->to();
			$return[$reservation->id]["rfp"] = $this->id();
			foreach($gather_res_props as $prop)
			{
				$return[$reservation->id()][$prop] = $reservation->prop($prop);
			}
			foreach($gather_res_meta as $meta)
			{
				$return[$reservation->id()]["meta_".$meta] = $reservation->meta($meta);
			}
		}
		return $return;
	}

	/** Returns all resreved resources for this rfp
		@attrib api=1
	 **/
	public function get_resources()
	{
		$connections = $this->connections_from(array(
			"type" => "RELTYPE_RESERVATION",
		));
		$gather_res_props = array(
			"id", "people_count", "start1", "end", "resource",
		);
		$gather_res_meta = array(
			"amount"
		);
		foreach($connections as $conn)
		{
			$reservation = $conn->to();

			$new = array(
				"rfp" => $this->id(),
			);
			foreach($gather_res_props as $prop)
			{
				$new[$prop] = $reservation->prop($prop);
			}
			foreach($gather_res_meta as $meta)
			{
				$new["meta_".$meta] = $reservation->meta($meta);
			}
			/* he.. this returns every resource itself.. we need resevations here. doooh..
			foreach($reservation->get_resources_data() as $resource => $resource_data)
			{
				$new2 = array(
					"real_resource" => $resource,
				);
				$return[] = $new2 + $new + $resource_data;
			}
			 */
			$return[] = $new;
		}
		return $return;
	}

	/** Returns housing information for this rfp
		@attrib api=1
	 **/
	public function get_housing()
	{
		$housing = $this->meta("housing");
		return $housing;
	}

	/** Returns catering information for this rfp
		@attrib api=1
	 **/
	public function get_catering()
	{
		$products = $this->meta("prods");
		return $products;
	}

	/** Removes given room reservation
		@attrib api=1 params=pos
		@param reservation type=oid required
	 **/
	public function remove_room_reservation($reservation)
	{
		if($this->can("view", $reservation))
		{
			$this->disconnect(array(
				"from" => $reservation,
				"type" => 3,
			));
			return true;
		}
		return false;
	}

	/** Removes all given rooms reservations
		@attrib api=1 params=pos
		@param room type=oid required
	 **/
	public function remove_room_reservations($room)
	{
		if($this->can("view", $room))
		{
			$conns = $this->connections_from(array(
				"to.class_id" => CL_RESERVATION,
				"CL_RESERVATION.resource" => $room,
				"type" => 3,
			));
			foreach($conns as $conn)
			{
				$conn->delete();
			}
			return true;
		}
		return false;
	}

}
?>
