<?php
include("const.aw");
include ("admin_header.$ext");
classload("aw_template");
$tt = new aw_template;
$tt->db_init();

classload("config","menuedit","icons");

$c = new db_config;
$t = new icons;

switch($type)
{
	case "sel_join":
		if (!$tt->prog_acl("view", PRG_JOINFORM))
		{
			$tt->prog_acl_error("view", PRG_JOINFORM);
		}
		$c->sel_join($id);
	case "join_form":
		if (!$tt->prog_acl("view", PRG_JOINFORM))
		{
			$tt->prog_acl_error("view", PRG_JOINFORM);
		}
		$content = $c->sel_join_form();
		break;

	case "sel_search":
		$c->sel_search($id);
	case "search_form":
		$content = $c->sel_search_form();
		break;

	case "icon_db":
		if (!$tt->prog_acl("view", PRG_ICONDB))
		{
			$tt->prog_acl_error("view", PRG_ICONDB);
		}
		$content = $t->gen_db($page);
		break;

	case "add_icon_zip":
		$content = $t->add_zip();
		break;

	case "add_icon":
		$content = $t->add();
		break;

	case "change_icon":
		$content = $t->change($id);
		break;

	case "del_icon":
		$content = $t->delete($id);
		header("Location: config.$ext?type=icon_db&page=$page");
		break;

	case "class_icons":
		if (!$tt->prog_acl("view", PRG_CLASS_ICONS))
		{
			$tt->prog_acl_error("view", PRG_CLASS_ICONS);
		}
		$content = $c->class_icons();
		break;
	
	case "class_icon":
		$c->set_class_icon($id,$icon_id);
		header("Location: config.$ext?type=class_icons");
		break;

	case "program_icons":
		if (!$tt->prog_acl("view", PRG_ROGRAM_ICONS))
		{
			$tt->prog_acl_error("view", PRG_PROGRAM_ICONS);
		}
		$content = $c->program_icons();
		break;
	
	case "program_icon":
		$c->set_program_icon($id,$icon_id);
		header("Location: config.$ext?type=program_icons");
		break;

	case "file_icons":
		if (!$tt->prog_acl("view", PRG_FILE_ICONS))
		{
			$tt->prog_acl_error("view", PRG_FILE_ICONS);
		}
		$content = $c->file_icons();
		break;
	
	case "file_icon":
		$c->set_file_icon($id,$icon_id);
		header("Location: config.$ext?type=file_icons");
		break;

	case "add_filetype":
		$content = $c->add_filetype($error);
		break;

	case "change_filetype":
		$content = $c->change_filetype($extt);
		break;

	case "delete_filetype":
		$c->delete_filetype($extt);
		header("Location: config.$ext?type=file_icons");
		break;

	case "sel_icon":
		$content = $t->sel_icon($rtype,$rid,$sstring,$sstring2);
		break;

	case "import_icons":
		$content = $t->import($level);
		break;

	case "import_class_icons":
		$content = $c->import_class_icons($level);
		break;

	case "import_file_icons":
		$content = $c->import_file_icons($level);
		break;

	case "import_program_icons":
		$content = $c->import_program_icons($level);
		break;

	case "import_other_icons":
		$content = $c->import_other_icons($level);
		break;

	case "other_icons":
		if (!$tt->prog_acl("view", PRG_OTHER_ICONS))
		{
			$tt->prog_acl_error("view", PRG_OTHER_ICONS);
		}
		$content = $c->other_icons();
		break;

	case "other_icon":
		$c->set_other_icon($id,$icon_id);
		header("Location: config.$ext?type=other_icons");

	case "import":
		if (!$tt->prog_acl("view", PRG_IMPORT_ICONS))
		{
			$tt->prog_acl_error("view", PRG_IMPORT_ICONS);
		}
		$content = $c->import();
		break;

	case "import_all_icons":
		$content = $c->import_all_icons($level);
		break;

	case "export":
		if (!$tt->prog_acl("view", PRG_EXPORT_ICONS))
		{
			$tt->prog_acl_error("view", PRG_EXPORT_ICONS);
		}
		$content = $c->export();
		break;

	case "menu_icon":
		classload("menuedit");
		$t = new menuedit;
		$t->set_menu_icon($id,$icon_id);
		$obj = $t->get_object($id);
		header("Location: ".$t->mk_orb("change", array("id" => $id, "parent" => $obj[parent])));
		break;

	default:
		$content = $c->gen_config();
}

include("admin_footer.$ext");
?>
