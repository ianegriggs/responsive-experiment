<?php

namespace Videos;

class GiftsController extends \Core\Base\Action {
	
	public function init() {
		$this->_ = new \Translate\Locale('Front\\'.__NAMESPACE__, $this->getModule('Language')->getLanguageId());
	}
	
	public function indexAction() {	
		
		$this->render('index');
		
	}
	
	
	
}