<?php
class AdminMenuLink extends AdminTab
{
	private $_module = 'menus';

	public function __construct()
	{
		$this->table			= 'menu_link';
		$this->className	= 'MenuLink';
		$this->lang 			= true;
		$this->identifier = 'id_menu_link';
		
		$this->add			 = true;
		$this->edit			 = true;
		$this->delete		 = true;
		$this->duplicate = true;
		
		$this->fieldsDisplay = array(
			'id_menu_link' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'id_parent' => array('title' => $this->l('ID parent'), 'align' => 'center', 'width' => 25),
			'type' => array('title' => $this->l('Type'), 'align' => 'center', 'width' => 130),
			'level' => array('title' => $this->l('Level'), 'align' => 'center', 'width' => 70),
			'logged' => array('title' => $this->l('Logged'), 'align' => 'center', 'width' => 70),
			'css' => array('title' => $this->l('CSS'), 'align' => 'center', 'width' => 270),
			'position' => array('title' => $this->l('Position'), 'width' => 40,'filter_key' => 'a!position', 'align' => 'center', 'position' => 'position'),
			'a!active' => array('title' => $this->l('Displayed'), 'active' => 'status', 'filter_key' => 'a!active', 'align' => 'center', 'type' => 'bool', 'orderby' => false)
		);
		$this->_orderBy = 'position';
		
		parent::__construct();
	}
	public function display($token = NULL)
	{
		global $currentIndex, $cookie;
		
		$this->getList((int)($cookie->id_lang));

		$id_menu = Tools::getValue('id_menu');
		echo '<a href="'.$currentIndex.'&id_menu='.$id_menu.'&add'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add a new item').'</a><br />';
		$this->displayList($token);

	}

	public function displayList($token = NULL)
	{
		global $currentIndex;

		/* Display list header (filtering, pagination and column names) */
		$this->displayListHeader($token);
		if (!sizeof($this->_list))
			echo '<tr><td class="center" colspan="'.(sizeof($this->fieldsDisplay) + 2).'">'.$this->l('No items found').'</td></tr>';

		/* Show the content of the table */
		$this->displayListContent($token);

		/* Close list table and submit button */
		$this->displayListFooter($token);
	}
}
?>