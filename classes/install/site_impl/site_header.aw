<?php
if (empty($_COOKIE["nocache"]) && aw_ini_get("config.use_squid"))
{
	$ma = aw_ini_get("config.http_cache_max_age");
        session_cache_limiter("must-revalidate, max-age=".$ma);
	header("Cache-Control: must-revalidate, max-age=".$ma);
	header("Expires: ".gmdate("D, d M Y H:i:s",time()+$ma)." GMT");
};

session_name("automatweb");
session_start();
classload("aw_template");
classload("defs");
classload("cache");
classload("timer");
classload("menuedit");
classload("document");
classload("file");

$awt = new aw_timer();

aw_startup();

// oughta put this in aw_startup() as well, but it is used in so many places
// in the code that I just don't have the time do deal with that right now
if (!$section)
{
	$section = aw_ini_get("frontpage");
};

// yeah. need to get rid of this as well. no time for that now though :(
?>
