<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/msg_sql.aw,v 2.4 2001/06/25 12:59:42 duke Exp $
// msg_sql.aw - sql draiver messengeri jaoks
class msg_sql_driver extends db_connector
{
	function msg_sql_driver()
	{
		$this->db_init();
	}

	////
	// !fetches a message by it's ID
	// arguments:
	// id(int) - message id
	function msg_get($args = array())
	{
		// Will show only users own messages
		$q = sprintf("SELECT *,objects.* 
				FROM messages
				LEFT JOIN objects ON (messages.id = objects.oid)
				WHERE id = '%d'",
				$args["id"]);
		$this->db_query($q);
		$row = $this->db_next();
		return $row;
	}

	////
	// !otsib teateid
	function msg_search($args = array())
	{
		extract($args);
		//if (!is_array($fields))
		//{
		//	return false;
		//};
		//$flist = array();
		//foreach($fields as $fval)
		//{
		//	$flist[] = "($fval LIKE '%$value%')";
		//};
		//$fl = join(" $connector ",$flist);

		$s = join(",",$folders);
		$q = "SELECT *,objects.*
			FROM messages
			LEFT JOIN objects ON (messages.id = objects.oid) 
			WHERE parent IN ($s) AND ($qs)
			ORDER BY parent";
		$this->db_query($q);
		$rows = array();
		while($row = $this->db_next())
		{
			$rows[$row["oid"]] = $row;
		};
		return $rows;
	}
			

	////
	// !Koostab attachide nimekirja
	function msg_list_attaches($args = array())
	{
		extract($args);
		$this->attaches = array();
		$reslist = array();
		$q = "SELECT * FROM objects WHERE parent = '$id'";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->attaches[$row["id"]] = $row;
			$reslist[] = $row["id"];
		};
		// ja tagastab arrays nende nimekirja
		return $reslist;
	}

	function msg_get_attach_by_id($args = array())
	{
		extract($args);
		return $this->attaches[$id];
	}

	////
	// !liigutab mingi messi teise folderisse
	// argumendid:
	// folder(int) - kuhu teade liigutada
	// id(int voi array) - milline teade(teated) liigutada
	function msg_move($args = array())
	{
		if (is_array($args["id"]) && (sizeof($args["id"]) > 0))
		{
			$idl = join(',',map("'%d'",$args["id"]));
		}
		else
		{
			$idl = sprintf("'%d'",$args["id"]);
		};
		$q = sprintf("UPDATE objects SET parent = %d WHERE oid IN (%s)",$args["folder"],$idl);
		$this->db_query($q);
	}

	////
	// !Märgib teate loetuks, ehk siis soltuvalt etteantud konfiguratsioonivotmest,
	// muudab ainult status flagi voi ka parentit (ehk folderit)
	// argumendid:
	// parent(int)(optional) - kui defineeritud, siis viiakse teade ka uude folderisse
	// id(int voi array) - teate id, voi id-de array
	// status(int)(optional) - teate staatus. kui defineerimata, siis märgime loetuks
	// aga samas saab selle abil seada ka koiki teisi tulevikus tekkida voivaid staatuskoode
	function msg_mark($args = array())
	{
		$status = (isset($args["status"])) ? $args["status"] : MSG_STATUS_READ;
		if (is_array($args["id"]) && (sizeof($args["id"]) > 0))
		{
			$idl = join(',',map("'%d'",$args["id"]));
		}
		else
		{
			$idl = sprintf("'%d'",$args["id"]);
		};

		$q = sprintf("UPDATE messages SET status = %d WHERE id IN (%s)",$status,$idl);
		$this->db_query($q);

		if ($args["folder"])
		{
			$this->msg_move($args);
		}
	}

	// listib teated. inefficient? maybe
	function msg_list($args = array())
	{
		extract($args);
		if (!$folder)
		{
			return false;
		};
		$q = sprintf("SELECT objects.*,messages.* FROM objects
			LEFT JOIN messages ON (objects.oid = messages.id)
			WHERE class_id = %d AND parent = '$folder'
			ORDER BY created DESC",CL_MESSAGE);
		$this->db_query($q);
		$res = array();
		while($row = $this->db_next())
		{
			$res[] = $row;
		};
		return $res;
	}

	function msg_send($args = array())
	{
		extract($args);
		$t = time();
		$q = "INSERT INTO messages (id,pri,mfrom,reply,mtargets1,mtargets2,folder,subject,tm,type,message)
			VALUES('$oid','$pri','$mfrom','$reply','$mtargets1','$mtargets2','$folder','$subject',$t,1,'$message')";
		$this->db_query($q);
		$retval = true;
		return $retval;
	}

	//// 
	// !Purges marked message
	function msg_delete($args = array())
	{
		extract($args);
		// kustutame teated
		$q = "DELETE FROM messages WHERE id = '$id'";
		$this->db_query($q);
		// kustutame objektitabeli kirje
		$q = "DELETE FROM objects WHERE oid = '$id'";
		$this->db_query($q);
		// kustutame voimalikud attachid
		$q = "DELETE FROM msg_objects WHERE message_id = '$id'";
		$this->db_query($q);
	}

	////
	// !Counts unread messages
	function count_unread($args = array())
	{
		extract($args);
		$q = "SELECT count(*) AS cnt FROM objects
			LEFT JOIN messages ON (objects.oid = messages.id)
			WHERE class_id = " . CL_MESSAGE . " and parent = $folder and messages.status = " . MSG_STATUS_UNREAD;
		$this->db_query($q);
		$row = $this->db_next();
		return $row["cnt"];
	}

}
?>
