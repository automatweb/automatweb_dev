<?php
// Väliste linkide haldamise klass
if (!$GLOBALS[extlinks]) {
	$GLOBALS[extlinks] = 1;
class extlinks extends aw_template {

  // lisab lingi objekti och dokumendi juurde
	function add_link($args) {
	  extract($args);
		if ($type == "")
		{
			$type = "ext";
		}
		$q = "INSERT INTO extlinks (id,oid,url,name,hits,newwindow,descript,type,docid,doclinkcollection) 
						VALUES('$id','$oid','$url','$name','$hits','$newwindow','$desc','$type','$docid','$doclinkcollection')";
		$this->db_query($q);
		$this->log_action($GLOBALS["uid"],"link","Lisas lingi $name");
	}

	function save_link($args) {
		$this->quote($args);
		extract($args);
		if ($type == "")
		{
			$type = "ext";
		}
		$q = "UPDATE extlinks 
			SET name = '$name',
			    url = '$url',
			    descript = '$desc',
					newwindow = '$newwindow',
					type = '$type',
					doclinkcollection = '$doclinkcollection',
					docid = '$docid'
			WHERE id = '$lid'";
		$this->db_query($q);
		$this->upd_object(array("oid" => $lid,
					"name" => $name));
		$this->log_action($GLOBALS["uid"],"link","Muutis linki $name");
	}

	function get_link($id) {
		$q = "SELECT extlinks.*,objects.* FROM extlinks LEFT JOIN objects ON objects.oid = extlinks.id WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_fetch_row();
		if ($row[type] == "int")
		{
			$row[url] = $GLOBALS["baseurl"]."/index.".$GLOBALS["ext"]."?section=".$row[docid];
		}
		return $row;
	}

  // registreerib kliki lingile

	// peab ehitama ka mehhanisimi spämmimise vältimiseks
	function add_hit($id,$host,$uid) {
		$q = "UPDATE extlinks
							SET hits = hits + 1
							WHERE id = '$id'";
		$this->db_query($q);
		$t = time();
		$q = "INSERT INTO extlinkstats (lid,tm,host,uid) 
						VALUES ('$id',$t,'$host','$uid')";
		$this->db_query($q);
		$name = $this->db_fetch_field("SELECT name FROM objects where oid = $id","name");
		$this->log_action($GLOBALS["uid"],"link","Klikkis lingil $name");
	}

};
};
?>
