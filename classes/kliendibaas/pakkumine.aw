<?php
// $Header: /home/cvs/automatweb_dev/classes/kliendibaas/Attic/pakkumine.aw,v 1.1 2003/08/29 14:30:48 axel Exp $
// pakkumine.aw - Pakkumine 
/*

@classinfo syslog_type=ST_PAKKUMINE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property start1 type=datetime_select
// field=start table=planner group=calendar
@caption Algab 

@property duration type=time_select 
//field=end table=planner group=calendar
@caption Kestab

@property content type=textarea cols=60 rows=30
@caption Sisu


@property into_user_calendar type=checkbox ch_value=1
@caption Pane kasutaja kalendrisse



*/

class pakkumine extends class_base
{
	function pakkumine()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "kliendibaas/pakkumine",
			"clid" => CL_PAKKUMINE
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	
	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case 'into_user_calendar':
				if ($cal_id = aw_global_get('user_calendar'))
				{
					
					
				}
				else
				{
					$retval = PROP_IGNORE;
				}
			break;
		};
		return $retval;
	}
	

	
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {
			case 'into_user_calendar':
		
				//teha kalendrisse sündmus
				if ($cal_id = aw_global_get('user_calendar'))
				{
					$planner = get_instance('planner');
					$kal = $this->get_object($cal_id);
					$arr['obj'] = $kal;

					$arr['form_data']['emb'] =  $args['form_data'];
					$arr['form_data']['emb']['title'] = 'Pakkumine: '.$args['form_data']['name'];
					//$arr['form_data']['emb']['nimi'] = 'Pakkumine: '.$args['form_data']['name'];					
					$arr['form_data']['class'] = 'doc';
					$arr['form_data']['action'] = 'submit';
					$arr['form_data']['group'] = 'general';
//<input type='hidden' name='emb[cfgform]' value="96038" />
					
//arr($arr) ;
					$planner->create_planner_event($arr);

///				
/*
Array
(
    [form_data] => Array
        (
            [MAX_FILE_SIZE] => 5000000
            [emb] => Array
                (
                    [start1] => Array
                        (
                            [day] => 30
                            [month] => 8
                            [year] => 2003
                            [hour] => 12
                            [minute] => 24
                        )

                    [duration] => Array
                        (
                            [hour] => 4
                            [minute] => 0
                        )

                    [status] => 1
                    [title] => nimi siin
                    [content] => sisu on see
                    [sbt] => Salvesta
                    [class] => doc
                    [action] => submit
                    [group] => general
                    [id] => 125492
                )

            [reforb] => 1
            [class] => planner
            [action] => submit
            [id] => 125451
            [group] => add_event
            [parent] => 97175
            [section] => 
            [period] => 
            [cb_view] => 
            [alias_to] => 
            [reltype] => 
            [cfgform] => 0
            [return_url] => 
            [subgroup] => 
            [event_id] => 125492
            [rawdata] => Array
                (
                    [MAX_FILE_SIZE] => 5000000
                    [emb] => Array
                        (
                            [start1] => Array
                                (
                                    [day] => 30
                                    [month] => 8
                                    [year] => 2003
                                    [hour] => 12
                                    [minute] => 24
                                )

                            [duration] => Array
                                (
                                    [hour] => 4
                                    [minute] => 0
                                )

                            [status] => 1
                            [title] => nimi siin
                            [content] => sisu on see
                            [sbt] => Salvesta
                            [class] => doc
                            [action] => submit
                            [group] => general
                            [id] => 125492
                        )

                    [reforb] => 1
                    [class] => planner
                    [action] => submit
                    [id] => 125451
                    [group] => add_event
                    [parent] => 97175
                    [section] => 
                    [period] => 
                    [cb_view] => 
                    [alias_to] => 
                    [reltype] => 
                    [cfgform] => 0
                    [return_url] => 
                    [subgroup] => 
                    [event_id] => 125492
                    [rawdata] => Array
 *RECURSION*
                )

        )

    [new] => 
)		
*/		
//////////////					
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
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array("id" => $alias["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);

		$this->read_template("show.tpl");

		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		return $this->parse();
	}
}
?>
