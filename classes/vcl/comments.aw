<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/comments.aw,v 1.2 2004/03/24 17:49:59 duke Exp $
// comments VCL component

// what kind of forms do I need?
// 1. uid, title of comment, comment - not implemented
// 2. user, email, title of comment, comment - not imlemented
// 3. uid, comment - for logged in users - implemented
class comments extends class_base
{
	function comments()
	{
		$this->init("");
	}

	function init_vcl_property($arr)
	{
		$prop = &$arr["property"];
		$this->obj = $arr["obj_inst"];
		$oid = $this->obj->id();
		$fcg = get_instance(CL_COMMENT);
		$comms = $fcg->get_comment_list(array(
			"parent" => $oid,
		));
		$prname = $prop["name"];
		$pager = $this->pager(array(
			"total" => count($comms),
			"onpage" => 20,
		));
		$res = "";
		$res .= "<h2>" . $this->obj->name() . "</h2>";
		$res .= "Selle objekti kohta on " . count($comms) . " kommentaari<br><br>";
		$res .= "$pager<br><br>";
		$c = 0;
		foreach($comms as $row)
		{
			$c++;
			if ($c >= $this->from && $c <= $this->to)
			{
				$author = $row["uname"];
				if (empty($author))
				{
					$author = $row["createdby"];
				};
				$res .= "<p><b>" . $author . "</b>, " . $this->time2date($row["created"]) . "<br>";
				$res .= nl2br(create_links($row["commtext"])) . "</p>";
			};
		};
		$pr1 = $prop;
		$pr1["type"] = "text";
		$pr1["value"] = $res;
		$pr1["name"] = $prname . "[list]";
		$rv = array(
			$prname . "_list" => $pr1,
			$prname . "_capt2" => array(
				"type" => "text",
				"subtitle" => 1,
				"value" => "Lisa kommentaar",
				"name" => $prname . "[capt2]",
			),
			$prname . "_capt" => array(
				"type" => "text",
				"caption" => "Kasutaja",
				"value" => "<b>" . aw_global_get("uid") . "</b>",
				"name" => $prname . "[capt]",
			),
			$prname . "_comment" => array(
				"type" => "textarea",
				"caption" => "Kommentaar",
				"name" => $prname . "[comment]",
			),
		);
		return $rv;
	}

	function process_vcl_property($arr)
	{
		$comm = get_instance(CL_COMMENT);
		$commdata = $arr["prop"]["value"];
		$nc = $comm->submit(array(
			"parent" => $arr["obj_inst"]->id(),
			"commtext" => $commdata["comment"],
			"return" => "id",
		));
	}

	function pager($arr)
	{
		$pages = $arr["total"] / $arr["onpage"];
		$res = array();
		$page = (int)$_GET["page"];
		for ($i = 0; $i < $pages; $i++)
		{
			$from = $i * $arr["onpage"] + 1;
			$to = min(($i+1)*$arr["onpage"], $arr["total"]);
			if ($i == $page)
			{
				$res[] = "<strong>${from}-${to}</strong>";
				$this->from = $from;
				$this->to = $to;
			}
			else
			{
				$res[] = html::href(array(
						"url" => aw_url_change_var(array("page" => $i)),
						"caption" => $from . "-" . $to,
				));
			};
		};
		return join(" | ",$res);
	}
};
?>
