<?php

	class chatserver extends aw_template
	{
		
		function chatserver()
		{
			$this->init('chatserver');
		}

		function add($arr)
		{
			extract($arr);

			$this->mk_path($parent, 'Lisa jutuka serveri objekt');

			$this->read_template('add.tpl');			
			$this->vars(array(
				'reforb'=>$this->mk_reforb('submit', array(
					'parent'=>$parent,
					'alias_to'=>$alias_to))
				));
			
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
						'servername'=>$servername,
						'ircserver'=>$ircserver,
						'port'=>$port)));
			}
			else
			{
				$id=$this->new_object(array(
					'parent'=>$parent,
					'name'=>$name,
					'class_id'=>CL_CHATSERVER,
					'metadata'=>array(
						'servername'=>$servername,
						'ircserver'=>$ircserver,
						'port'=>$port)));

				if($alias_to)
				{
					$this->add_alias($alias_to,$id);
				}
			}			
		
			return $this->mk_my_orb('change', array('id'=>$id));
		}

		function change($arr){
			extract($arr);
			
			$dat=$this->get_object($id);
			
			$this->mk_path($dat['parent'], 'Muuda jutuka serverit');
			
			$this->read_template('add.tpl');
			$this->vars(array(
				'name'=>$dat['name'],
				'servername'=>$dat['meta']['servername'],
				'ircserver'=>$dat['meta']['ircserver'],
				'port'=>$dat['meta']['port'],
				'reforb'=>$this->mk_reforb('submit', array('id'=>$id))
				));
			
			return $this->parse();
		}

		function parse_alias($arr)
		{
			extract($arr);

			$dat=$this->get_object($alias['target']);

			$this->read_template('show.tpl');

			return $this->parse();
		}
	}
?>
