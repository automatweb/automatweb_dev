<?php
if ($class || $reforb)
{
	// if we detect an orb call, load the orb handler and let it take over
	include(aw_ini_get("classdir")."/".aw_ini_get("site_impl_dir")."/orb_impl.".aw_ini_get("ext"));
}
else
{
	// if no orb call, do a normal pageview
	include(aw_ini_get("classdir")."/".aw_ini_get("site_impl_dir")."/site_header.".aw_ini_get("ext"));
}

// get an instance if the site class
$si =&__get_site_instance();

// if we are drawing the site's front page
if ((!$section || $section == aw_ini_get("frontpage")) && !$class) 
{
	// then do the right callback
	$content = $si->on_frontpage();
}
else
// and if we should
if (!aw_global_get("no_menus"))
{
	$m = get_instance("contentmgmt/site_cache");
	$content = $m->show(array(
		"vars" => $si->on_page(),
		"text" => $content,
		"no_right_pane" => ($content) ? true : false,
		"sub_callbacks" => $si->get_sub_callbacks()
	));
}

// and finish gracefully
include(aw_ini_get("classdir")."/".aw_ini_get("site_impl_dir")."/site_footer.".aw_ini_get("ext"));
?>
