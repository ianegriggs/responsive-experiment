<?php

namespace Pin;

class PinRow extends \Core\Db\Table\Row {
	
	private $_board;
	
    /**
     * Allows pre-insert logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _insert()
    {
    	$boardTable = new \Board\Board();
    	$_board = $boardTable->fetchRow(array('id = ?' => $this->board_id));
    	if($_board) {
    		$this->category_id = $_board->category_id;
    	}
    	$this->date_added = \Core\Date::getInstance(null,\Core\Date::SQL_FULL, true)->toString();
    	$this->date_modified = $this->date_added;
    	/////////////// price
    	$price = \Currency\Currency::findPrice($this->description);
    	if($price) {
    		$this->gift = 1;
    		$this->price = $price->price;
    		$this->currency_code = $price->code;
    	}
    	if($this->source_id) {
    		$media = new \Media\Helper\UrlInfo();
    		if($media->parseUrl($this->from)) {
    			$this->video = 1;
    		}
    	}
    }

    /**
     * Allows pre-update logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _update()
    {
    	$boardTable = new \Board\Board();
    	$_board = $boardTable->fetchRow(array('id = ?' => $this->board_id));
    	if($_board) {
    		$this->category_id = $_board->category_id;
    	}
    	$this->date_modified = \Core\Date::getInstance(null,\Core\Date::SQL_FULL, true)->toString();
    	///////////// price
    	$price = \Currency\Currency::findPrice($this->description);
    	if($price) {
    		$this->gift = 1;
    		$this->price = $price->price;
    		$this->currency_code = $price->code;
    	} else {
    		$this->gift = 0;
    		$this->price = null;
    		$this->currency_code = null;
    	}
    	$this->video = 0;
    	if($this->source_id) {
    		$media = new \Media\Helper\UrlInfo();
    		if($media->parseUrl($this->from)) {
    			$this->video = 1;
    		}
    	}
    }

    /**
     * Allows post-insert logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _postInsert()
    {
    	$boardTable = new \Board\Board();
    	$_board = $boardTable->fetchRow(array('id = ?' => $this->board_id));
    	if($_board) {
    		/*$boardTable->update(array(
    			'pins' => $this->getTable()->countByBoardId_Status($this->board_id, 1)
    		), array('id = ?' => $this->board_id));*/
    		$boardTable->updateInfo($this->board_id);
    	}
    	$userTable = new \User\User();
    	
    	if($this->parent_id) {
    		$repin = $this->getTable()->fetchRow(array('id = ?' => $this->parent_id, 'status' => 1 ));
    		if($repin) {
    			$repin->repins = $this->getTable()->countByParentId_Status($this->parent_id,1);
    			$repin->save();
    		}
    	}
    	if($this->source_id) {
    		$sourceTable = new \Source\Source();
    		$source = $sourceTable->fetchRow(array('id = ?' => $this->source_id));
    		if($source) {
    			$source->pins = $this->getTable()->countBySourceId_Status($this->source_id,1);
    			$source->save();
    		}
    	}
    	$user = $userTable->fetchRow(array('id = ?' => $this->user_id));
    	if($user) {
    		/*$user->pins = $this->getTable()->countByUserId_Status($this->user_id, 1);
    		 if($this->parent_id) {
    		$user->repins = $this->getTable()->countByUserId_Status_ParentId($this->user_id, 1, 'IS NOT NULL');
    		}
    		$user->save();*/
    		$userTable->updateInfo($this->user_id);
    	}
    	
    	//register events
    	\Base\Event::trigger('pin.insert',$this->id);
    	if($this->image && array_key_exists('image', $this->_modifiedFields)) {
    		\Base\Event::trigger('pin.uploadImage',$this->id);
    	}
    	//end events
    	
    }

    /**
     * Allows post-update logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _postUpdate()
    {
    	
   	 	$boardTable = new \Board\Board();
    	$_board = $boardTable->fetchRow(array('id = ?' => $this->board_id));
    	if($_board) {
    		/*$boardTable->update(array(
    			'pins' => $this->getTable()->countByBoardId_Status($this->board_id, 1)
    		), array('id = ?' => $this->board_id));*/
    		$boardTable->updateInfo($this->board_id);
    	}
    	
    	$userTable = new \User\User();
    	if(array_key_exists('status', $this->_modifiedFields)) {
    		$pinLikeTable = new \Pin\PinLike();
    		/*$userTable->update(array(
    			'likes' => $pinLikeTable->select()->from($pinLikeTable,'count(1)')->where('pin_like.user_id = user.id AND pin_like.status = 1')
    		), array('status = ?' => $this->status));*/
    		$userTable->update(array(
    				'likes' => $pinLikeTable->countByUserId_Status($this->user_id,1)
    		), array('id=?'=>$this->user_id));
    	}
    	
    	if($this->source_id) {
    		$sourceTable = new \Source\Source();
    		$source = $sourceTable->fetchRow(array('id = ?' => $this->source_id));
    		if($source) {
    			$source->pins = $this->getTable()->countBySourceId_Status($this->source_id,1);
    			$source->save();
    		}
    	}
    	$user = $userTable->fetchRow(array('id = ?' => $this->user_id));
    	if($user) {
    		/*$boardTable = new \Board\Board();
    		 $user->pins = $this->getTable()->countByUserId_Status($this->user_id, 1);
    		if($this->parent_id) {
    		$user->repins = $this->getTable()->countByUserId_Status_ParentId($this->user_id, 1, 'IS NOT NULL');
    		}
    		$user->save();*/
    		$userTable->updateInfo($this->user_id);
    	}
    	
    	//register events
    	if(array_key_exists('image', $this->_modifiedFields)) {
			//mod for extension
			$ext = strtolower(substr(strrchr($this->image, "."), 1));
			$this->ext = ($ext=='jpg'?'jpeg':$ext);
			$this->getTable()->update(array(
				'ext' => $this->ext
			), array('id = ?' => $this->id));
			//end mod
    		\Base\Event::trigger('pin.uploadImage',$this->id);
    	}
    	\Base\Event::trigger('pin.update',$this->id);
		//end mod
		
    	//end events
    }

    /**
     * Allows post-delete logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _postDelete()
    { 
    	$boardTable = new \Board\Board();
    	$_board = $boardTable->fetchRow(array('id = ?' => $this->board_id));
    	if($_board) {
    		/*$boardTable->update(array(
    			'pins' => $this->getTable()->countByBoardId_Status($this->board_id, 1)
    		), array('id = ?' => $this->board_id));*/
    		$boardTable->updateInfo($this->board_id);
    	}
    	$userTable = new \User\User();
    	
    	if($this->parent_id) {
    		$repin = $this->getTable()->fetchRow(array('id = ?' => $this->parent_id, 'status' => 1 ));
    		if($repin) {
    			$repin->repins = $this->getTable()->countByParentId_Status($this->parent_id, 1);
    			$repin->save();
    		}
    	}
    	if($this->source_id) {
    		$sourceTable = new \Source\Source();
    		$source = $sourceTable->fetchRow(array('id = ?' => $this->source_id));
    		if($source) {
    			$source->pins = $this->getTable()->countBySourceId_Status($this->source_id,1);
    			$source->save();
    		}
    	}
    	$user = $userTable->fetchRow(array('id = ?' => $this->user_id));
    	if($user) {
    		/*$user->pins = $this->getTable()->countByUserId_Status($this->user_id, 1);
    		 if($this->parent_id) {
    		$user->repins = $this->getTable()->countByUserId_Status_ParentId($this->user_id, 1, 'IS NOT NULL');
    		}
    		$user->save();*/
    		$userTable->updateInfo($this->user_id);
    	}
    	
    	//register events
    	\Base\Event::trigger('pin.delete',$this->id);
    	//end events
    }
    
    public function getUserFullname() {
    	$username_method_show = \Base\Config::get('username_show');
    	if($username_method_show == 'fullname') {
    		return $this->firstname . ' ' . $this->lastname;
    	} else if($username_method_show == 'fullname-desc') {
    		return $this->lastname . ' ' . $this->firstname;
    	} else if($username_method_show == 'firstname') {
    		return $this->firstname;
    	} else {
    		return $this->username;
    	}
    }
    
    public function getPrice() {
    	if($this->price) {
    		return \Currency\Helper\Format::format($this->price, $this->currency_code, 1);
    	}
    	return null;
    }
	
}