<?php
session_name("automatweb");
session_start();
classload("aw_template","defs","cache","timer","menuedit","document","file");

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
