<?php

class ProblemController extends Zend_Controller_Action {

	public function indexAction() {
	
	}
	
		
	public function listAction() {
		$tag = intval($this->getParam("tag"));
		if($tag){
			$select = Application_Model_ProblemManager::select();
			$select->joinInner("problemtag", "problem.id = problemtag.problem and problemtag.tag = $tag");
			$db = Zend_Db_Table::getDefaultAdapter();
			$this->view->problems = $db->query($select)->fetchAll();
			$tag = Application_Model_TagManager::get($tag);
			$this->view->title = "Problems - $tag[name]";
		}else{
			$this->view->title = "All Problem";
			$this->view->problems = Application_Model_ProblemManager::getAll();
		}
		
		
	}
		
	public function registerAction() {
		Application_Model_Auth::checkIsAdmin();
		$this->view->tags = Application_Model_TagManager::getAll(null, 'name');
		if($_POST){
			
			$_POST['active'] = !!$_POST['active'];
			$_POST['tags'] = array_flip(explode(',', $_POST['tags']));
			
			Application_Model_ProblemManager::add($_POST);
			$id = Zend_Db_Table::getDefaultAdapter()->lastInsertId();
			move_uploaded_file($_FILES['description']['tmp_name'], "../data/problems/".$id.".pdf");
			move_uploaded_file($_FILES['cases']['tmp_name'], "../data/testcases/".$id.".zip");
			
			foreach ($_POST['tags'] as $key=>$value){
				if($key) Application_Model_ProblemTagManager::add(array('problem' => $id, 'tag' => $key));
			}
			
			$this->view->id = $id;
			$this->view->success = "Problem successfully created.";
		}
	}

	public function editAction() {
		Application_Model_Auth::checkIsAdmin();
		$this->view->tags = Application_Model_TagManager::getAll(null, 'name');
		$id = $this->getParam("id");
		if($_POST){
	
			$_POST['active'] = !!$_POST['active'];
			$_POST['tags'] = array_flip(explode(',', $_POST['tags']));
	
			Application_Model_ProblemManager::set($_POST, $id);
			Application_Model_ProblemManager::increment("version", $id);
			
			if($_FILES['description']['tmp_name']) move_uploaded_file($_FILES['description']['tmp_name'], "../data/problems/".$id.".pdf");
			if($_FILES['cases']['tmp_name']) move_uploaded_file($_FILES['cases']['tmp_name'], "../data/testcases/".$id.".zip");
	
			Application_Model_ProblemTagManager::removeByProblem($id);
			foreach ($_POST['tags'] as $key=>$value){
				if($key) Application_Model_ProblemTagManager::add(array('problem' => $id, 'tag' => $key));
			}
	
			$this->view->id = $id;
			$this->view->success = "Problem has been changed.";
		}else{
			$_POST = Application_Model_ProblemManager::get($id);
			$tags = Application_Model_ProblemTagManager::getAll(array("problem = ?"=>$id));
			foreach ($tags as $tag) $_POST['tags'][$tag['tag']] = true;
		}
	}
	public function removeAction() {
	
	}
	
	public function pdfAction(){
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$id = intval($this->getParam("id"));
		header('Content-type: application/pdf');
		header('Content-Disposition: inline; filename="UFFS - Problem '.$id.'.pdf"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize("../data/problems/$id.pdf"));
		header('Accept-Ranges: bytes');
		@readfile("../data/problems/$id.pdf");
	}
	
}
