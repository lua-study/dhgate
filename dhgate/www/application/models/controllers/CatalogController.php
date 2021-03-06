<?php
class CatalogController extends MainController
{
	public function indexAction()
	{
		$catalogTalbe = new Catalog();
		$this->view->categorys = $catalogTalbe->fetchAll();
	}

	public function categoryAction()
	{
		$id = (int)$this->_getParam('id',0);
		if($id < 1){
			$this->_redirect('/');
		}
		$catalogTable = new Catalog();
		$currentCategory = $this->view->currentCategory  = $catalogTable->getCurrent($id);
		$this->view->parents = array_reverse($catalogTable->getParentsRecursive($id));
		$this->view->children = $catalogTable->getChildren($currentCategory);
		$products = $catalogTable->getItems($currentCategory->id, (int) $this->_getParam('page',0));
		$count = (int)$this->_getParam('count',20);
		$products->setItemCountPerPage($count);
		$products->setView($this->view);
		$this->view->products = $products;
		$this->view->current_category_id = $currentCategory->id;
		if($_SESSION['admin'])
		{
			$this->view->categorys = $catalogTable->fetchAll();
		}
	}

	public function addAction()
	{
		if(!$_SESSION['admin']){
			$this->_redirect('/');
		}
		if($this->_request->isPost()){
			$catalogTable = new Catalog();
			$row = $catalogTable->createChildRow((int)$this->_getParam('parent_id', 0), $_POST);
			$row->save();
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}
	}

	public function editAction()
	{
		if(!$_SESSION['admin']){
			$this->_redirect('/');
		}
		$id = (int) $this->_getParam('id',0);
		if($id < 1) {
			$this->_redirect('/');
		}
		$catalogTable = new Catalog();
		$currentRow = $catalogTable->getCurrent($id);
		$currentRow->title = $_POST['title'];
		$currentRow->save();
		$this->_redirect($_SERVER['HTTP_REFERER']);
	}

	public function deleteAction()
	{
		if(!$_SESSION['admin']){
			$this->_redirect('/');
		}
		$id = (int) $this->_getParam('id',0);
		if($id < 1) {
			$this->_redirect('/');
		}
		$catalogTable = new Catalog();
		$currentRow = $catalogTable->getCurrent($id);
		$parent = $currentRow->parent;
		$connectTable = new Connect_Catalog();
		$connectTable->deleteItem($id);
		$currentRow->delete();
		if($parent !=0 ){
			$this->_redirect('/catalog/category/id/' . $parent);
		} else {
			$this->_redirect('/');
		}
	}

	public function moveAction()
	{
		if(!$_SESSION['admin']){
			$this->_redirect('/');
		}
		$category_id = (int) $this->_getParam('id',0);
		$catalogTable = new Catalog();
		$category = $catalogTable->find($category_id)->current();
		$catalogTable->moveBranch((int)$_POST['category_id'], $category->parent);
		$this->_redirect($_SERVER['HTTP_REFERER']);
	}
}