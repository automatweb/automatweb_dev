<?php

/*
	@default table=objects
	@default group=general

	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

	@default table=kliendibaas_isik

	@property firstname type=textbox size=15 maxlength=50
	@caption eesnimi
	@property lastname type=textbox size=15 maxlength=50
	@caption perekonnanimi
	@property gender type=textbox size=5 maxlength=10
	@caption sugu
	@property personal_id type=textbox size=10 maxlength=15
	@caption isikukood
	@property title type=textbox size=5 maxlength=10
	@caption tiitel
	@property nickname type=textbox size=10 maxlength=20
	@caption hüüdnimi
	@property messenger type=textbox size=10 maxlength=200
	@caption msn
	@property birthday type=textbox size=10 maxlength=20
	@caption sünnipäev
	@property social_status type=textbox size=10 maxlength=20
	@caption perekonnaseis
	@property spouse type=textbox size=15 maxlength=50
	@caption abikaasa
	@property children type=textarea cols=20 rows=3
	@caption lapsed
	@property digitalID type=textarea cols=20 rows=3
	@caption digitaalallkiri
	@property pictureurl type=textbox size=20 maxlength=200
	@caption foto/pildi asukoht
	@property picture type=file
	@caption foto

	@property work_contact_change type=text
	@caption töökoha kontakt

	@property personal_contact_change type=text
	@caption kodukoha kontakt

	@property more type=text
	@caption more

	@classinfo objtable=kliendibaas_isik
	@classinfo objtable_index=oid
*/

/*
    -> personal_contact int,
    -> work_contact int,
*/

class isik extends class_base
{

	function isik()
	{
		$this->init("kliendibaas");
		$this->init(array(
			'clid' => CL_ISIK,
		));
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case 'name':
				$data['value'] =  $args['objdata']['firstname']." ".$args['objdata']['lastname'];
			break;
		};
		return $retval;
	}


	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = true;
		switch($data["name"])
		{
			case 'more':
				$data['value'] = html::iframe(array('name' => 'contacts','src' => 'juhuu','width' => '500', 'height' => '300'));
			break;
			case 'personal_contact_change':

				if (!$args['objdata']['home_contact'])
				{
					$data['value'] = html::href(array(
						'caption' => 'uus',
						'target' => '_blank',
						'url' => $this->mk_my_orb("new",array(
							'parent'=>$args['obj']['parent'], //parent tuleb kliendibaasi objektist pigem võtta
						),'kliendibaas/contact'),
					));
				}
				else
				{
					$data['value'] = html::href(array(
						'caption' => 'muuda',
						'target' => '_blank',
						'url' => $this->mk_my_orb("change",array(
							"id" => $args['objdata']['home_contact'],
							'parent'=>$args['obj']['parent'],
							"return_url" => urlencode($return_url)
						),'kliendibaas/contact'),
					));
					$data['value'] .= html::href(array(
						'caption' => 'kustuta',
						'target' => 'contacts',
						'url' => $this->mk_my_orb('delete',array(
							"id" => $args['objdata']['home_contact'],
						),'kliendibaas/contact'),
					));
				}
			break;
			case 'work_contact_change':
				$data['value'] = html::href(array(
					'caption' => 'muuda',
					'target' => '_blank',
					'url' => $this->mk_my_orb("change",array(
						"id" => $args['objdata']['work_contact'],
						'parent'=>$args['obj']['parent'],
						"return_url" => urlencode($return_url),
					),'kliendibaas/contact'),
				));

			break;

		}
		return $retval;
	}

/*
	////
	// ! create new isik entry
	// isik			at least one element required
	//	name
	//	firstname
	//	lastname
	//	...
	// name
	// parent
	// comment
	// ...
	function new_isik($arr)
	{
		extract($arr);

			$isik["name"]=$isik["name"]?$isik["name"]:$isik["firstname"]." ".$isik["lastname"];
			foreach ($isik as $key=>$val)
			{
				{
					$this->quote($val);
					$f[]=$key;
					$v[]="'".$val."'";
				}
			}

			$id = $this->new_object(array(
				"name" => $isik["name"],
				"parent" => $parent,
				"class_id" => CL_ISIK,
				"comment" => $comment,
				"metadata" => array(
				)
			));
			$f[]='oid';
			$v[]="'".$id."'";

			$q='insert into kliendibaas_isik('.implode(",",$f).')values('.implode(",",$v).')';

		$this->db_query($q);
		return $id;
	}
*/

}
?>
