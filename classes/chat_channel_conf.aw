<?php

	class chat_channel_conf extends aw_template{
		
		function chat_channel_conf(){
			$this->init('chat_channel_conf');
		}

		function add($arr){
			extract($arr);

			$this->mk_path($parent, 'Lisa jutuka kanali konfiguratsioon');
	
			$this->read_template('add.tpl');

			$this->vars($this->get_user());
			
			$this->vars(array(
				'reforb'=>$this->mk_reforb('submit', array(
					'parent'=>$parent,
					'alias_to'=>$alias_to)),
					'name'=>'#'
				));
			
			return $this->parse();
		}

	
		function submit($arr){
			extract($arr);

			if($id){
				$this->upd_object(array(
					'oid'=>$id,
					'name'=>$name,
					'metadata'=>array(
						'uid'=>$uid,
						'kanal'=>$kanal)));
			}
			else{
				$id=$this->new_object(array(
					'parent'=>$parent,
					'name'=>$name,
					'class_id'=>CL_CHAT_CHANNEL_CONF,
					'metadata'=>array(
						'uid'=>$uid,
						'kanal'=>$kanal)));

				if($alias_to){
					$this->add_alias($alias_to, $id);
				}
			}			
		
			return $this->mk_my_orb('change', array('id'=>$id));
		}

		function change($arr){
			extract($arr);
			
			$dat=$this->get_object($id);

			$this->mk_path($dat['parent'], 'Muuda jutuka kanali konfiguratsiooni');
		
			$this->read_template('add.tpl');
			$this->vars(array(
				'name'=>$dat['name'],
				'uid'=>$dat['meta']['uid'],
				'kanal'=>$dat['meta']['kanal'],
				'reforb'=>$this->mk_reforb('submit', array('id'=>$id))
				));

			return $this->parse();
		}


		function parse_alias($arr){
			extract($arr);

			$dat=$this->get_object($alias['target']);

			$this->read_template('show.tpl');

			$this->vars(array(
				'name'=>$dat['name'],
				'uid'=>$dat['meta']['uid'],
				'kanal'=>$dat['meta']['kanal']));
			
			return $this->parse();
		}
	}
?>
