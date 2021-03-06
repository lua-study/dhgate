<?php
class UserController extends MainController
{
	/**
	 * The default action - show the home page
	 */
	public function indexAction ()
	{
		//$this->_redirect($_SERVER['HTTP_REFERER']);
		$this->_redirect('/user/profile/');
	}


	public function profileAction()
	{
		

		if (!Zend_Auth::getInstance()->hasIdentity()){
			$this->_redirect('/');
		}else{
			$user = new User();
			$id=Zend_Auth::getInstance()->getIdentity()->id;
			$this->view->user = $user->find($id)->current();
		}


	}


	public function recoverAction()
	{
		//
		$form = new App_Form_Recover();
		$this->view->form= $form;
		if ($this->getRequest()->isPost()){
			if ($form->isValid($this->getRequest()->getPost())){
				$user = new User();
				if ($newPass = $user->passRecover($_POST['mail'])){
					$mail = new Zend_Mail();
					$mail->setBodyText($newPass)
					->setFrom('somebody@example.com', 'Some Sender')
					->addTo($_POST['mail'], 'Some Recipient')
					->setSubject('Password Recover')
					->send();
					$this->_redirect('/user/login/');
				}else{
					$this->view->message = 'This mail is not registered!!!';
				}
			}
		}



	}


	public function ordersAction() 
	{
		$orders= new Order();
		$this->view->orders = $orders->getOrdersByStatus();
	}

	public function registerAction()
	{

		$checkout = (int)$this->_getParam('checkout',0);
	
		$form= new App_Form_Register();
		$this->view->form = $form;
		if($checkout){
			$form->setAction('/user/register/checkout/1/');
		}
		if ($this->getRequest()->isPost()){
			if ($form->isValid($this->getRequest()->getPost())){
					
				$userTable = new User();
				$passTmp = $_POST['pass'];
				$_POST['pass'] = md5($_POST['pass']);
				$row = $userTable->createRow();
				foreach($_POST as $key => $value ){
					if(isset($row->$key)){
						$row->$key = $value;
					}
				}
				$row->save();
				if($this->_login($_POST['mail'], $passTmp)){
				
					if($checkout){
						$this->_redirect('/cart/');
					}else{
						$this->_redirect('/');
					}
				}
				$cart = new Cart();
				$cart->savecookie();

			}
			
		}
	}


	public function loginAction()
	{
		
		if($this->auth->hasIdentity()){
			$this->_redirect('/');
		}
		$checkout = (int)$this->_getParam('checkout',0);

		
		
		$form= new App_Form_Login();
		
		if($checkout){
		$form->setAction('/user/login/checkout/1/');
		}
		
		$this->view->form=$form;
		

		if($this->_request->isPost())
		{
			if ($form->isValid($this->getRequest()->getPost())){
				if($this->_login($_POST['mail'], $_POST['pass'])){
					
					if (isset($_POST['remember'])){
						if((bool)$_POST['remember']){
							Zend_Session::rememberMe(3600*24*7);
						}
					}
					$cart = new Cart_Cookie();
					$cart->saveCookie();
					
					if($checkout){
						$this->_redirect('/cart/');
					}else{
						$this->_redirect('/');
					}
					
				}else{
					$this->view->message = 'Failed';
				}
			}
		}
	}


	private function _login($login, $pass, $hash = null)
	{
		$dbAdapter = Zend_Registry::get('db');
		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
		$authAdapter->setTableName('user');
		$authAdapter->setIdentityColumn('mail');
		$authAdapter->setCredentialColumn('pass');
		$authAdapter->setIdentity($login);
		$authAdapter->setCredential(md5($pass));
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		$result = $auth->authenticate($authAdapter);
		if($result->isValid()){
			$data = $authAdapter->getResultRowObject(null, 'pass');
			$auth->getStorage()->write($data);
			return  true;
		} else {
			return false;
		}
	}




	public function editAction()
	{
		if (Zend_Auth::getInstance()->hasIdentity()){
			$form = new App_Form_User();
			$id = Zend_Auth::getInstance()->getIdentity()->id;
			if($this->_request->isPost()){
					
				if (Zend_Auth::getInstance()->getIdentity()->mail != $_POST['mail']){
					$form->getElement('mail')->addValidator('NoDbRecordExists', true, array('user', 'mail'));
				}
				if (Zend_Auth::getInstance()->getIdentity()->login != $_POST['login']){
					$form->getElement('login')->addValidator('NoDbRecordExists', true, array('user', 'login'));
				}

				if($form->isValid($this->getRequest()->getPost())){

					$User = new User();
					$currentUser=$User->find($id)->current();
					$passMD5 = $currentUser->pass;
					if($_POST['pass']!= ''){
						$_POST['pass']=md5($_POST['pass']);
						
					}else{
						$_POST['pass']= $passMD5;
					}

					foreach($_POST as $key => $value ){
						if(isset($currentUser->$key)){
							$currentUser->$key = $value;
						}
					}
					$currentUser->save();
					
					
					
					$auth = Zend_Auth::getInstance();
					$auth->getStorage()->write($currentUser);
					Zend_Auth::getInstance()->getIdentity()->pass='';
					
					
					$this->view->form = $form;
					$this->view->message='	Information successfully updated';
				}else{
					$this->view->form = $form;
				}
					
			}else{
					
				$user = new User();
				$userData = $user->find($id)->current()->toArray();
				$this->view->form = $form->populate($userData);
			}




		}else{
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}

	}


	public function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		$this->_redirect('/');
	}


	public function setvaluteAction()
	{
		Zend_Layout::getMvcInstance()->disableLayout();
		$id=$this->_getParam('id',0);
		if($id>0){
		setcookie('valute' , $id, mktime(0,0,0,01,25,2099),'/');
		}
		$this->_redirect($_SERVER['HTTP_REFERER']);
	}
}