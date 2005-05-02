<?php
classload(
	"core/obj/ds_decorator_base",
	"core/obj/_int_obj_container_base", 
	"core/obj/_int_object", 
	"core/obj/ds_base",
	"core/obj/connection",
	"core/obj/object_loader", 
	"core/obj/object_list", 
	"core/obj/object_data_list", 
	"core/obj/object_tree",
	"core/obj/object_list_filter",
	"core/obj/obj_predicate_not",
	"core/obj/obj_predicate_compare",
	"core/obj/obj_predicate_prop"
);

$GLOBALS["properties"] = array();
$GLOBALS["tableinfo"] = array();
$GLOBALS["of2prop"] = array();



// god damn, this is a fucking great idea!
// how to get around the php copy-object problem. 
// basically, make the object class contain only object id, store the real objects in a global hash
// and access them through that only, so object data is in memory only once, but there can be several oid pointers to it. 
// the dummy object class just forwards all calls to the global table.
// voila - instant object cache!


// rules for datasources: 
// if you create a new wrapper datasource, then it must not derive from anything, be in this folder 
// the file name must be ds_[name], the class name must be _int_obj_ds_[name] 
// the constructor must take one parameter - the contained ds instance
// it must implement all functions in ds_base, even if that just means passing everything to the contained ds
// this way we minimize the number of connections and duplicate data, therefore save memory
// if you create a new db-specific datasource (the last one in the chain), the file naming convention still applies, 
// but it should derive from ds_base and call $this->init() from the constructor, that takes no parameters


/////////////
// TODO:
// - separate, confable, possibly shared folders for caches: acl, search, connection, objdata/properties
// - don't load properties if not asked for, implement this by swithing the object instance in the objects array
// - is object_list caching really worth it?
// - merge objdata/propdata caches?

class object
{
	var $oid;	// the object this instance points to

	function object($param = NULL)
	{
		if ($param != NULL && !is_array($param))
		{
			$this->load($param);
		}
		else
		{
			$this->oid = $GLOBALS["object_loader"]->new_object_temp_id($param);
		}
	}

	function load($param)
	{
		if (!is_object($GLOBALS["object_loader"]))
		{
			die(t("object loader is not object!!"));
		}
		enter_function("object::load");
		$this->oid = $GLOBALS["object_loader"]->load($GLOBALS["object_loader"]->param_to_oid($param));
		exit_function("object::load");
		return $this->oid;
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

	function delete($full_delete = false)
	{
		return $GLOBALS["objects"][$this->oid]->delete($full_delete);
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

	function connections_to($param = NULL)
	{
		return $GLOBALS["objects"][$this->oid]->connections_to($param);
	}

	function get_first_conn_by_reltype($type = NULL)
	{
		$conns = $GLOBALS["objects"][$this->oid]->connections_from(array(
			"type" => $type,
		));
		return reset($conns); // reset($empty_arr) gives bool(false)
	}

	function get_first_obj_by_reltype($type = NULL)
	{
		$conns = $GLOBALS["objects"][$this->oid]->connections_from(array(
			"type" => $type,
		));
		if ($first = reset($conns))
		{
			return $first->to();
		}
		return false;
	}

	function path($param = NULL)
	{
		return $GLOBALS["objects"][$this->oid]->path($param);
	}

	function path_str($param = NULL)
	{
		return $GLOBALS["objects"][$this->oid]->path_str($param);
	}

	function is_property($param)
	{
		return $GLOBALS["objects"][$this->oid]->is_property($param);
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

	function lang_id()
	{
		return $GLOBALS["objects"][$this->oid]->lang_id($param);
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

	function meta($param = false)
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

	function prop_str($param, $is_oid = NULL)
	{
		return $GLOBALS["objects"][$this->oid]->prop_str($param, $is_oid);
	}

	function get_property_list()
	{
		return $GLOBALS["objects"][$this->oid]->get_property_list();
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

	function fetch()
	{
		return $GLOBALS["objects"][$this->oid]->fetch();
	}


	function is_cache_dirty()
	{
		return $GLOBALS["objects"][$this->oid]->is_cache_dirty($param);
	}

	function set_cache_dirty($param = true)
	{
		return $GLOBALS["objects"][$this->oid]->set_cache_dirty($param);
	}

	function last()
	{
		return $GLOBALS["objects"][$this->oid]->last();
	}

	function brother_of()
	{
		return $GLOBALS["objects"][$this->oid]->brother_of();
	}

	function &instance()
	{
		return $GLOBALS["objects"][$this->oid]->instance();
	}

	function create_brother($parent)
	{
		return $GLOBALS["objects"][$this->oid]->create_brother($parent);
	}

	function is_connected_to($param)
	{
		return $GLOBALS["objects"][$this->oid]->is_connected_to($param);
	}

	function acl_set($group, $acl)
	{
		if (!$this->is_connected_to(array("to" => $group->id())))
		{
			$this->connect(array(
				"to" => $group->id(),
				"reltype" => RELTYPE_ACL,
			));
		}

		$GLOBALS["object_loader"]->save_acl(
			$this->id(),
			$group->prop("gid"),
			$acl
		);
	}

	function acl_del($g_oid)
	{
		
	}
}

function &obj($param = NULL)
{
	return new object($param);
}

/** sets an object system property 

@comment
currently possible options:
no_auto_translation - 1/0 - if 1, no auto object translation is performed
no_cache - 1/0 - if 1, ds_cache is not used even if it is loaded
**/
function obj_set_opt($opt, $val)
{
	$tmp = $GLOBALS["__obj_sys_opts"][$opt];
	$GLOBALS["__obj_sys_opts"][$opt] = $val;
	return $tmp;
}

function dump_obj_table($pre = "")
{
	echo "---------------------------------------- object table dump: <br />$pre <br />\n";
	foreach($GLOBALS["objects"] as $oid => $obj)
	{
		echo "oid in list $oid , data: {oid => ".$obj->id().", name = ".$obj->name()." parent = ".$obj->parent()." } <br />\n";
	}
	echo "++++++++++<br />\n";
	flush();
}
?>
