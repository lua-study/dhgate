<?php

class App_Form_AddCategory extends App_Form  {
	public function init(){
		parent::init();
		$helper  = new Zend_View_Helper_Url();
		$this->setAction('/catalog/add/');
		$this->setMethod('POST');
		$this->setAttrib('class','addCategory');

		$cat = new Zend_Form_Element_Text(
			'title', array(
			 'label'=>'Subcategory: ',
			 'required'    => true,
			 'maxlength'   => '30',
			 'validators'  => array(
		array('Alnum', true, array(true)),
		array('StringLength', true, array(0, 30))
		),
            'filters'     => array('StringTrim'), 

		));
		$this->addElement($cat);
		
		$parentId=  new Zend_Form_Element_Hidden('parent_id',
		array('value'=>0)
		);
		
		//$cat->setName('name');

		 
		$submit = new Zend_Form_Element_Submit('submit', array(
            'label'       => 'Add',
		));

		$this->addElement($submit);
		$this->addElement($parentId);
	
		foreach($this->getElements() as $element){
			$element->clearDecorators()
			->addDecorator("ViewHelper")
			->addDecorator("Errors");
		}
		
	}
}