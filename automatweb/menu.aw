<?php
// See on adminni framework
// $Revision: 2.0 $
include("const.aw");
include ("admin_header.$ext");
classload("acl");

$sf = new aw_template();
$sf->tpl_init("automatweb");
$sf->sub_merge = 1;
$sf->db_init();
$sf->read_template("topmenu.tpl");

reset($menu_defs);
$m_id = $amenustart;

// make one big array for the whole menu

$l1ar = array();
$l2ar = array();
$q = "SELECT objects.oid as oid, 
											objects.parent as parent,
											objects.name as name,
											objects.jrk as jrk,
											menu.type as type,
											menu.link as link
									FROM objects 
									LEFT JOIN menu ON menu.id = objects.oid
									WHERE objects.class_id = 1 AND objects.status = 2 AND menu.admin_feature=0
									GROUP BY objects.oid
									ORDER BY objects.parent, jrk,oid";
$sf->db_query($q);
while ($row = $sf->db_next())
{
		if ($row[parent] == $m_id) {
			$l1ar[$row[oid]] = $row;
		};

		if ($l1ar[$row[parent]][parent] == $m_id)
			$l2ar[$row[parent]][] = $row;
}

reset($l1ar);
while (list(,$row) = each($l1ar))
{
	$l2 = "";
	if (is_array($l2ar[$row[oid]]))
	{
		reset($l2ar[$row[oid]]);
		while (list(,$v) = each($l2ar[$row[oid]]))
		{
			if ($v[type] == MN_ADMIN_DOC)
				$url = "showdoc.".$GLOBALS["ext"]."?section=".$v[oid];
			else
				$url = $v[link];

			$sf->vars(array("text" => $v[name], "url" => $url, "menu_id" => $v[oid]));
			if ($l2 == "")
				$l2.=$sf->parse("L2_MENU_BEGIN");
			else
				$l2.=$sf->parse("L2_MENU");
		}
	}

	$sf->vars(array("text" => $row[name], "menu_id" => $row[oid], "L2_MENU_BEGIN" => $l2, "L2_MENU" => ""));
	$sf->parse("L1_MENU_LINE1");
	$sf->parse("L1_MENU_LINE2");
	$sf->parse("L1_MENU_LINE_3");
	$sf->parse("L1_MENU_LINE4");
}
$sf->vars(array("stitle" => $stitle));

echo $sf->parse();
?>
