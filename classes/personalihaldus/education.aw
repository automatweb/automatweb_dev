<?php
// $Header: /home/cvs/automatweb_dev/classes/personalihaldus/Attic/education.aw,v 1.3 2004/03/18 01:56:19 sven Exp $
// education.aw - Education 
/*

@classinfo syslog_type=ST_EDUCATION relationmgr=yes no_status=1

@default table=objects
@default group=general

@property kool type=textbox field=meta method=serialize
@caption Haridusasutus

@property algusaasta type=select field=meta method=serialize
@caption Sisseastumis aasta

@property loppaasta type=select field=meta method=serialize
@caption L&otilde;petamise aasta



@property eriala type=classificator field=meta method=serialize  store=connect reltype=RELTYPE_ERIALA orient=vertical
@caption Eriala

@property teaduskond type=classificator field=meta method=serialize store=connect reltype=RELTYPE_TEADUSKOND orient=vertical
@caption Teaduskond

@property oppekava type=classificator field=meta method=serialize store=connect reltype=RELTYPE_OPPEKAVA orient=vertical
@caption Õppekava

@property oppeaste type=classificator field=meta method=serialize reltype=RELTYPE_OPPEASTE orient=vertical
@caption Õppeaste

@property oppevorm type=classificator field=meta method=serialize reltype=RELTYPE_OPPEVORM orient=vertical
@caption Õppevorm

@property lisainfo_edu type=textarea field=meta method=serialize
@caption Lisainfo


@reltype ERIALA value=1 clid=CL_META
@caption Tegevusvaldkond

@reltype TEADUSKOND value=2 clid=CL_META
@caption Teaduskond

@reltype OPPEKAVA value=3 clid=CL_META
@caption Õppekava

@reltype OPPEASTE value=4 clid=CL_META
@caption Õppeaste

@reltype OPPEVORM value=5 clid=CL_META
@caption Õppevorm

*/

class education extends class_base
{
	function education()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "personalihaldus/education",
			"clid" => CL_EDUCATION
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "loppaasta":
				for($i=date("Y"); $i>date("Y") - 80; $i--){
					$data["options"][$i]=$i;
				}
			break;
			
			case "algusaasta":
				for($i=date("Y"); $i>date("Y") - 80; $i--){
					$data["options"][$i]=$i;
				}
			break;
		};
		return $retval;
	}
	

	
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "kool":
				if(strlen($data["value"])==0)
				{
					$data["value"] = "Tartu Ülikool";

				}
			break;
		}
		return $retval;
	}	
	

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
