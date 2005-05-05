<?php
include(aw_ini_get("classdir")."/".aw_ini_get("site_impl_dir")."/site_header.".aw_ini_get("ext"));

$te = new aw_template;
$te->tpl_init("");
$te->read_template("login.tpl");

// if there is an auth config then get the list of servers to add 
$ac = get_instance(CL_AUTH_CONFIG);
if (is_oid($ac_id = auth_config::has_config()))
{
	$sl = $ac->get_server_ext_list($ac_id);
	$te->vars(array(
		"servers" => $te->picker(-1, $sl)
	));
	if (count($sl))
	{
		$te->vars(array(
			"SERVER_PICKER" => $te->parse("SERVER_PICKER")
		));
	}
}

$m = get_instance("contentmgmt/site_cache");

$si =&__get_site_instance();

$content = $m->show(array(
	"vars" => $si->on_page(),
	"text" => $te->parse(),
	"no_right_pane" => ($content) ? true : false,
	"sub_callbacks" => $si->get_sub_callbacks()
));

include(aw_ini_get("classdir")."/".aw_ini_get("site_impl_dir")."/site_footer.".aw_ini_get("ext"));
?>
