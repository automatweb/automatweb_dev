<?php
classload(
	"core/obj/_int_obj_container_base", 
	"core/obj/_int_object", 
	"core/obj/ds_local_sql",
	"core/obj/connection",
	"core/obj/object_loader", 
	"core/obj/object_list", 
	"core/obj/object_tree"
);

// TODO:
// cache properties by type
// cache tableinfo by type, don't use member vars. 
// cache ds, don't use member
// access ini settings directly?

// god damn, this is a fucking great idea!
// how to get around the php copy-object problem. 
// basically, make the object class contain only object id, store the real objects in a global hash
// and access them through that only, so object data is in memory only once, but there can be several oid pointers to it. 
// the dummy object class just forwards all calls to the global table.
// voila - instant object cache!


class object
{
	var $oid;	// the object this instance points to

	function object($param = NULL)
	{
		if ($param != NULL)
		{
			$this->load($param);
		}
		else
		{
			$this->oid = $GLOBALS["object_loader"]->new_object_temp_id();
		}
	}

	function load($param)
	{
		if (!is_object($GLOBALS["object_loader"]))
		{
			die("object loader is not object!!");
		}
		return $this->oid = $GLOBALS["object_loader"]->load($GLOBALS["object_loader"]->param_to_oid($param));
	}

	function save()
	{
		return $this->oid = $GLOBALS["object_loader"]->save($this->oid);
	}

	function save_new()
	{
		return $this->oid = $GLOBALS["object_loader"]->save_new($this->oid);
	}

	function set_implicit_save($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_implicit_save($param);
	}

	function get_implicit_save()
	{
		return $GLOBALS["objects"][$this->oid]->get_implicit_save($param);
	}

	function arr()
	{
		return $GLOBALS["objects"][$this->oid]->arr($param);
	}

	function delete()
	{
		return $GLOBALS["objects"][$this->oid]->delete($param);
	}

	function connect($param)
	{
		return $GLOBALS["objects"][$this->oid]->connect($param);
	}

	function disconnect($param)
	{
		return $GLOBALS["objects"][$this->oid]->disconnect($param);
	}

	function connections_from($param = NULL)
	{
		return $GLOBALS["objects"][$this->oid]->connections_from($param);
	}

	function connections_to($param)
	{
		return $GLOBALS["objects"][$this->oid]->connections_to($param);
	}

	function path($param = NULL)
	{
		return $GLOBALS["objects"][$this->oid]->path($param);
	}

	function path_str($param = NULL)
	{
		return $GLOBALS["objects"][$this->oid]->path_str($param);
	}

	function can($param)
	{
		return $GLOBALS["objects"][$this->oid]->can($param);
	}

	function parent()
	{
		return $GLOBALS["objects"][$this->oid]->parent($param);
	}

	function set_parent($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_parent($param);
	}

	function name()
	{
		return $GLOBALS["objects"][$this->oid]->name($param);
	}

	function set_name($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_name($param);
	}

	function class_id()
	{
		return $GLOBALS["objects"][$this->oid]->class_id($param);
	}

	function set_class_id($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_class_id($param);
	}

	function status()
	{
		return $GLOBALS["objects"][$this->oid]->status($param);
	}

	function set_status($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_status($param);
	}

	function lang()
	{
		return $GLOBALS["objects"][$this->oid]->lang($param);
	}

	function set_lang($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_lang($param);
	}

	function comment()
	{
		return $GLOBALS["objects"][$this->oid]->comment($param);
	}

	function set_comment($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_comment($param);
	}

	function ord()
	{
		return $GLOBALS["objects"][$this->oid]->ord($param);
	}

	function set_ord($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_ord($param);
	}

	function alias()
	{
		return $GLOBALS["objects"][$this->oid]->alias($param);
	}

	function set_alias($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_alias($param);
	}

	function id()
	{
		return $GLOBALS["objects"][$this->oid]->id($param);
	}

	function createdby()
	{
		return $GLOBALS["objects"][$this->oid]->createdby($param);
	}

	function created()
	{
		return $GLOBALS["objects"][$this->oid]->created($param);
	}

	function modifiedby()
	{
		return $GLOBALS["objects"][$this->oid]->modifiedby($param);
	}

	function modified()
	{
		return $GLOBALS["objects"][$this->oid]->modified($param);
	}

	function period()
	{
		return $GLOBALS["objects"][$this->oid]->period($param);
	}

	function set_period($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_period($param);
	}

	function is_periodic()
	{
		return $GLOBALS["objects"][$this->oid]->is_periodic($param);
	}

	function set_periodic($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_periodic($param);
	}

	function site_id()
	{
		return $GLOBALS["objects"][$this->oid]->site_id($param);
	}

	function set_site_id($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_site_id($param);
	}

	function is_brother()
	{
		return $GLOBALS["objects"][$this->oid]->is_brother($param);
	}

	function get_original()
	{
		return $GLOBALS["objects"][$this->oid]->get_original($param);
	}

	function subclass()
	{
		return $GLOBALS["objects"][$this->oid]->subclass($param);
	}

	function set_subclass($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_subclass($param);
	}

	function flags()
	{
		return $GLOBALS["objects"][$this->oid]->flags($param);
	}

	function set_flags($param)
	{
		return $GLOBALS["objects"][$this->oid]->set_flags($param);
	}

	function flag($param)
	{
		return $GLOBALS["objects"][$this->oid]->flag($param);
	}

	function set_flag($flag, $val)
	{
		return $GLOBALS["objects"][$this->oid]->set_flag($flag, $val);
	}

	function meta($param)
	{
		return $GLOBALS["objects"][$this->oid]->meta($param);
	}

	function set_meta($key, $value)
	{
		return $GLOBALS["objects"][$this->oid]->set_meta($key, $value);
	}

	function prop($param)
	{
		return $GLOBALS["objects"][$this->oid]->prop($param);
	}

	function set_prop($key, $value)
	{
		return $GLOBALS["objects"][$this->oid]->set_prop($key, $value);
	}

	function merge($param)
	{
		return $GLOBALS["objects"][$this->oid]->merge($param);
	}

	function merge_prop($param)
	{
		return $GLOBALS["objects"][$this->oid]->merge_prop($param);
	}

	function properties()
	{
		return $GLOBALS["objects"][$this->oid]->properties($param);
	}

	function is_cache_dirty()
	{
		return $GLOBALS["objects"][$this->oid]->is_cache_dirty($param);
	}

	function set_cache_dirty($param = true)
	{
		return $GLOBALS["objects"][$this->oid]->set_cache_dirty($param);
	}
}

function &obj($param = NULL)
{
	return new object($param);
}

function dump_obj_table($pre)
{
	echo "---------------------------------------- object table dump: <br>$pre <br>\n";
	foreach($GLOBALS["objects"] as $oid => $obj)
	{
		echo "oid in list $oid , data: {oid => ".$obj->id().", name = ".$obj->name()." } <br>\n";
	}
	echo "++++++++++<br>\n";
	flush();
}
?>
