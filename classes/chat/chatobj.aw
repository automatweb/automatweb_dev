<?php
// $Id: chatobj.aw,v 1.1 2002/12/17 17:14:05 duke Exp $
// chatobj.aw - Chat object

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	Deemoni asukoha server
	@property serverObjId type=objpicker clid=CL_CHATSERVER 
	@caption Serveri objekt

	Sisesta siia tekst, mida soovid näha lehele ilmuval nupul
	@property buttontext type=textbox 
	@caption Nupu tekst

	0 - piirangud puuduvad, 1 - kanal + privad, 2 - priva, 4 - arco
	@property mode type=textbox
	@caption Mode

	Kanal, millega automaatselt ühinetakse.
	@property channel type=textbox
	@caption Kanal

	Teade, mis saadetakse jutuka käivitamisel.
	@property message type=textbox
	@caption Teade

	Jutuka käivitamisel alustatakse automaatselt seda priva.
	@property privat type=textbox
	@caption Privat

	@property windowcolor type=colorpicker
	@caption Akna värv

	@property backcolor type=colorpicker
	@caption Tausta värv

	@property textcolor type=colorpicker
	@caption Teksti värv

	@property buttoncolor type=colorpicker
	@caption Nupu värv

	@property icon type=textbox
	@caption Ikoon

*/

class chatobj extends aw_template
{
	
	function chatobj()
	{
		$this->init(array(
			"tpldir" => "chatobj",
			"clid" => CL_CHATOBJ,
		));
	}

	function add($arr)
	{
		extract($arr);

		$this->mk_path($parent, 'Lisa jutuka objekt');

		$tmpArr=core::list_objects(array('class'=>107));
		foreach($tmpArr as $value)
		{
			if($this->can('view', $value)){
				$chatServerlist[]=$value;
			}
		}
		$tmpArr='';

		$this->read_template('add.tpl');			
		$this->vars(array(
			'reforb'=>$this->mk_reforb('submit', array(
				'parent'=>$parent,
				'alias_to'=>$alias_to)),
			'servers'=>$this->picker('',$chatServerlist),
			'icon'=>'',
			'buttontext'=>'',
			'mode'=>'1',
			'channel'=>'',
			'message'=>'',
			'privat'=>'',
			'windowcolor'=>'#BDD2DE',
			'backcolor'=>'#EEEEEE',
			'textcolor'=>'#000000',
			'buttoncolor'=>'#BDD2DE'));
		
		return $this->parse();
	}


	function submit($arr)
	{
		extract($arr);
		
		if($id)
		{
			$this->upd_object(array(
				'oid'=>$id,
				'name'=>$name,
				'metadata'=>array(
					'serverObjId'=>$serverObjId,
					'buttontext'=>$buttontext,
					'mode'=>$mode,
					'channel'=>$channel,
					'message'=>$message,
					'privat'=>$privat,
					'windowcolor'=>$windowcolor,
					'backcolor'=>$backcolor,
					'textcolor'=>$textcolor,
					'buttoncolor'=>$buttoncolor,
					'port'=>$port,
					'host'=>$host,
					'icon'=>$icon)));
		}
		else
		{
			$id=$this->new_object(array(
				'parent'=>$parent,
				'name'=>$name,
				'class_id'=>CL_CHATOBJ,
				'metadata'=>array(
					'serverObjId'=>$serverObjId,
					'buttontext'=>$buttontext,
					'mode'=>$mode,
					'channel'=>$channel,
					'message'=>$message,
					'privat'=>$privat,
					'windowcolor'=>$windowcolor,
					'backcolor'=>$backcolor,
					'textcolor'=>$textcolor,
					'buttoncolor'=>$buttoncolor,
					'port'=>$port,
					'host'=>$host,
					'icon'=>$icon)));

			if($alias_to){
				$this->add_alias($alias_to,$id);
			}
		}			
	
		return $this->mk_my_orb('change', array('id'=>$id));
	}

	function change($arr)
	{
		extract($arr);
	
		$dat=$this->get_object($id);
		
		$this->mk_path($dat['parent'], 'Muuda jutuka objekti');

		$this->read_template('add.tpl');
		$this->vars(array(
			'servers'=>$this->picker($dat['meta']['serverObjId'],
																core::list_objects(array('class'=>107))),
			'name'=>$dat['name'],
			'icon'=>$dat['meta']['icon'],
			'buttontext'=>$dat['meta']['buttontext'],
			'mode'=>$dat['meta']['mode'],
			'channel'=>$dat['meta']['channel'],
			'message'=>$dat['meta']['message'],
			'privat'=>$dat['meta']['privat'],
			'windowcolor'=>$dat['meta']['windowcolor'],
			'backcolor'=>$dat['meta']['backcolor'],
			'textcolor'=>$dat['meta']['textcolor'],
			'buttoncolor'=>$dat['meta']['buttoncolor'],
			'reforb'=>$this->mk_reforb('submit', array('id'=>$id))
			));
		
		return $this->parse();
	}

	function parse_alias($arr)
	{
		extract($arr);

		$dat=$this->get_object($alias['target']);

		$this->read_template('show.tpl');

		$serverObj=$this->get_object($dat['meta']['serverObjId']);
		$userInfo=core::get_user();

		$this->vars($serverObj['meta']);	//servername, ircserver, port vars

		$this->vars(array(
			'name'=>$dat['name'],
			'icon'=>$dat['meta']['icon'],
			'buttontext'=>$dat['meta']['buttontext'],
			'mode'=>$dat['meta']['mode'],
			'channel'=>$dat['meta']['channel'],
			'message'=>$dat['meta']['message'],
			'privat'=>$dat['meta']['privat'],
			'windowcolor'=>$dat['meta']['windowcolor'],
			'backcolor'=>$dat['meta']['backcolor'],
			'textcolor'=>$dat['meta']['textcolor'],
			'buttoncolor'=>$dat['meta']['buttoncolor'],
			'uid'=>$userInfo['uid']));
		
		return $this->parse();
	}
}
?>
