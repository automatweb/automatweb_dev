<?php
if (empty($_COOKIE["nocache"]) && aw_ini_get("config.use_squid"))
{
        session_cache_limiter("public");
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

aw_startup();

// oughta put this in aw_startup() as well, but it is used in so many places
// in the code that I just don't have the time do deal with that right now
if (!$section)
{
	$section = aw_ini_get("frontpage");
};

// yeah. need to get rid of this as well. no time for that now though :(
$awt = new aw_timer();
?>
