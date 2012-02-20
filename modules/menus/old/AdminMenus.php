<?php
include_once _PS_MODULE_DIR_ . 'menus/backend/classes/Menu.class.php';
include_once _PS_MODULE_DIR_ . 'menus/backend/classes/MenuLink.class.php';
include_once _PS_MODULE_DIR_ . 'menus/AdminMenu.php';
include_once _PS_MODULE_DIR_ . 'menus/AdminMenuLink.php';
class AdminMenus extends AdminTab
{
	/** @var object AdminMenu() instance */
	private $adminMenu;

	/** @var object AdminMenuLink() instance */
	private $adminMenuLink;

	/** @var object Menu() instance for navigation*/
	private static $_menu = NULL;

	private $_module = 'menus';

	public function __construct()
	{
		/* Get current menu */
		/*$id_menu = abs((int)(Tools::getValue('id_menu')));
		if (!$id_menu) $id_menu = 1;
		self::$_menu = new Menu($id_menu);
		if (!Validate::isLoadedObject(self::$_menu))
			die('Menu cannot be loaded'); */

		$this->table = array('menu', 'menu_link');
		$this->adminMenu = new AdminMenu();
		$this->adminMenuLink = new AdminMenuLink();

		parent::__construct();
	}

	public static function getCurrentMenu()
	{
		return self::$_menu;
	}

	public function display()
	{
		global $currentIndex;

		if ((Tools::isSubmit('submitAddmenu_link') AND sizeof($this->adminMenuLink->_errors)) OR isset($_GET['updatemenu_link']) OR isset($_GET['addmenu_link']))
		{
			
			$this->adminMenuLink->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
		}
		elseif ((Tools::isSubmit('submitAddmenu') AND sizeof($this->adminMenu->_errors)) OR isset($_GET['updatemenu']) OR isset($_GET['addmenu']))
		{
			
			$this->adminMenu->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
		}
		else
		{
			$id_menu = (int)(Tools::getValue('id_menu'));
			if (!$id_menu)
				$id_menu = 0;
			$catBarIndex = $currentIndex;
			foreach ($this->table AS $tab)
				if (Tools::getValue($tab.'Orderby') && Tools::getValue($tab.'Orderway')) 
					$catBarIndex = preg_replace('/&'.$tab.'Orderby=([a-z _]*)&'.$tab.'Orderway=([a-z]*)/i', '', $currentIndex);

			echo '<div class="cat_bar"><span style="color: #3C8534;">'.$this->l('Current menu').' :</span>&nbsp;&nbsp;&nbsp;'.($id_menu != 0 ? getPath($catBarIndex, $id_menu) : $this->l('Root')).'</div>';
			echo '<h2>'.$this->l('Menus').'</h2>';
			$this->adminMenu->display($this->token);
			echo '<div style="margin:10px">&nbsp;</div>';
			echo '<h2>'.$this->l('Links in this menu').'</h2>';
			$this->adminMenuLink->display($this->token);
		}
		
	}
	
	public function postProcess()
	{
		if (!Tools::getValue('id_menu_link'))
			$this->adminMenu->postProcess();
	}
}
?>