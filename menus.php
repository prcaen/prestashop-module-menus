<?php
if (!defined('_PS_VERSION_'))
	exit;

include_once _PS_MODULE_DIR_ . 'menus/backend/classes/Menu.php';
include_once _PS_MODULE_DIR_ . 'menus/backend/classes/MenuLink.php';

class Menus extends Module
{
	public function __construct()
	{
		$this->name		 = 'menus';
		$this->tab		 = 'front_office_features';
		$this->version = '1.0';
		$this->author	 = 'Pierrick CAEN';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Menus');
		$this->description = $this->l('Manage your menus (header and footer)');

		$this->_tpl = getcwd() . '/../modules/'.$this->name.'/backend/tpl/'.$this->name.'.backend.tpl';

		$this->_idTabParent		= Tab::getIdFromClassName('AdminPreferences');
		$this->_adminTabClass = 'AdminMenus';
		$this->_adminTabName	= array(
			1 => 'Menus',
			2 => 'Menus',
			3 => 'Menus',
			4 => 'Menus',
			5 => 'Menus',
		);

		$this->_configs = array(
			1 => array(
			'name'		=> 'MENU_CACHE_ENABLE',
			'id'			=> 'cache_enable',
			'title'		=> $this->l('Use cache'),
			'type'		=> 'boolean',
			'default' => 1
			),
			2 => array(
			'name'		=> 'MENU_CACHE_REFRESH',
			'id'			=> 'cache_refresh',
			'title'		=> $this->l('Time refresh cache'),
			'type'		=> 'none',
			'default' => 8600
			),
			3 => array(
			'name'		=> 'MENU_CACHE_LATEST',
			'id'			=> 'cache_latest',
			'title'		=> $this->l('Cache latest'),
			'type'		=> 'none',
			'default' => 1
			)
		);
	}

	public function install()
	{
		parent::install();

		if(!$this->_installTables() || !$this->_installHooks())
			return false;

		if(!$this->_installModuleTab($this->_adminTabClass, $this->_adminTabName, $this->_idTabParent))
			return false;

		if(!$this->registerHook('header') || !$this->registerHook('menuTop'))
			return false;

		foreach($this->_configs as $config)
		{
			if(!Configuration::updateValue($config['name'], $config['default']))
				return false;
		}

		return true;
	}

	public function uninstall()
	{
		parent::uninstall();
		
		if(!$this->_uninstallTables() || !$this->_uninstallHooks())
			return false;

		if(!$this->_uninstallModuleTab($this->_adminTabClass))
			return false;

		foreach($this->_configs as $config)
		{
			if(!Configuration::deleteByName($config['name']))
				return false;
		}

		return true;
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		$this->_postProcess();
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		global $smarty;

		foreach($this->_configs as $key => &$config)
		{
			if($config['type'] == 'none')
				unset($this->_configs[$key]);
			else
				$config['value'] = Configuration::get($config['name']);
		}
		
		$smarty->assign('action', Tools::safeOutput($_SERVER['REQUEST_URI']));
		$smarty->assign('module_dir', $this->_path);
		$smarty->assign('configs', $this->_configs);

		return $smarty->fetch($this->_tpl);
	}
	private function _postProcess()
	{
		if(Tools::isSubmit('submit_menus'))
		{
			foreach($this->_configs as $config)
			{
				if($config['type'] != 'none')
					Configuration::updateValue($config['name'], Tools::getValue($config['id']));
			}
			echo '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
	}

	public function hookHeader()
	{
		global $smarty, $cookie;

		$vars = array(
			'path'		=> $this->_path,
			'id_lang' => (int)$cookie->id_lang,
			'logged'	=> isset($cookie->id_customer) && $cookie->isLogged() ? true : false,
			'id'			=> $this->_getId()
		);
		Tools::addCSS($this->_path . $this->name . '.css', 'all');
		Tools::addJS($this->_path	 . $this->name . '.js');

		$smarty->assign('HOOK_MENU_TOP', Module::hookExec('menuTop'));
		$smarty->assign('HOOK_MENU_FOOTER', Module::hookExec('menuFooter'));

		$smarty->assign('module_menus', $vars);
	}

	private function _installTables()
	{
		$database	 = Db::getInstance();
		$charset	 = 'utf8';
		$engine		 = 'InnoDB';
		if (defined('_MYSQL_ENGINE_'))
			$engine = _MYSQL_ENGINE_;

		// Add menu table
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'menu` (
			`id_menu` int(10) unsigned NOT NULL auto_increment,
			`id_hook` int(10) unsigned NOT NULL,
			`logged` tinyint(1) NOT NULL default \'0\',
			`css_id` varchar(32) NULL,
			`css_class` varchar(32) NULL,
			`active` tinyint(1) unsigned NOT NULL,
			`date_add` datetime NOT NULL,
			`date_upd` datetime NOT NULL,
			PRIMARY KEY	 (`id_menu`)
		)	 ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset . ';';

		if(!$database->Execute($sql))
			return false;

		// Add menu_link table
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'menu_link` (
			`id_menu_link` int(10) unsigned NOT NULL auto_increment,
			`id_menu` int(10) unsigned NOT NULL,
			`id_parent` int(10) unsigned NOT NULL,
			`id_link` int(10) unsigned,
			`type` varchar(16) NOT NULL,
			`logged` tinyint(1) NOT NULL default \'0\',
			`css_id` varchar(32) NULL,
			`css_class` varchar(32) NULL,
			`active` tinyint(1) unsigned NOT NULL,
			`position` int(10) unsigned NOT NULL DEFAULT	\'0\', 
			`date_add` datetime NOT NULL,
			`date_upd` datetime NOT NULL,
			PRIMARY KEY	 (`id_menu_link`)
			) ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset . ';';

		if(!$database->Execute($sql))
			return false;

		// Add menu_link_lang table
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'menu_link_lang` (
			`id_menu_link` int(10) unsigned NOT NULL,
			`id_lang` tinyint(2) unsigned NOT NULL,
			`title` varchar(128) NOT NULL,
			`link` varchar(128) NOT NULL,
			PRIMARY KEY	 (`id_menu_link`,`id_lang`)
			) ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset . ';';

		if(!$database->Execute($sql))
			return false;

		return true;
	}

	private function _uninstallTables()
	{
		if(!Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'menu`'))
			return false;
		if(!Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'menu_link`'))
			return false;
		if(!Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'menu_link_lang`'))
			return false;

		return true;
	}

	private function _installHooks()
	{
		$sql = "INSERT INTO `" . _DB_PREFIX_ . "hook` SET `name`= 'menuTop', `title`= 'Menu top', `description`= ''";
		if(!DB::getInstance()->Execute($sql))
			return false;

		$sql = "INSERT INTO `" . _DB_PREFIX_ . "hook` SET `name`= 'menuFooter', `title`= 'Menu footer', `description`= ''";
		if(!DB::getInstance()->Execute($sql))
			return false;

		return true;
	}

	private function _uninstallHooks()
	{
		if(!DB::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "hook` WHERE `hook`.`name` = 'menuTop'"))
			return false;

		if(!DB::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "hook` WHERE `hook`.`name` = 'menuFooter'"))
			return false;

		return true;
	}

	// ---------------
	// ---- TOOLS ----
	// ---------------
	private function _installModuleTab($className, $name, $idParent)
	{
		$tab = new Tab();

		$tab->class_name = $className;
		$tab->name			 = $name;
		$tab->module		 = $this->name;
		$tab->id_parent	 = $idParent;

		if(!$tab->save())
		{
			$this->_errors[] = Tools::displayError('An error occurred while saving new tab: ') . ' <b>' . $tab->name . ' (' . mysql_error() . ')</b>';
			return false;
		}
		
		return true;
	}

	private function _uninstallModuleTab($className)
	{
		$idTab = Tab::getIdFromClassName($className);

		if($idTab != 0)
		{
			$tab = new Tab($idTab);
			$tab->delete();
			
			return true;
		}
		else
			return false;
	}
	
	private function _getIdMenuFromHookFctName($hookFctName)
	{
		$hookname = explode('hook', $hookFctName);
		$hookname = $hookname[1];
		$id_hook = Hook::get($hookname);
		$menu = Menu::getMenuFromIdHook($id_hook);

		return $menu->id;
	}
	
	private function _getId()
	{
		if($id_category = Tools::getValue('id_category', 0) != 0)
			return $id_category;
		elseif($id_product = Tools::getValue('id_product', 0) != 0)
			return $id_product;
		elseif($id_cms = Tools::getValue('id_cms', 0) != 0)
			return $id_cms;
		elseif($id_manufacturer = Tools::getValue('id_manufacturer', 0) != 0)
			return $id_manufacturer;
		elseif($id_supplier = Tools::getValue('id_supplier', 0) != 0)
			return $id_supplier;
	}
	
	// -------------------
	// ---- ALL HOOKS ----
	// -------------------
	public function allHooks($params, $function_name)
	{
		global $smarty, $cookie;

		// Menu
		$id_menu = $this->_getIdMenuFromHookFctName($function_name);
		$menu = new Menu($id_menu);

		if($menu->active == 1)
		{
			$vars = array(
				'css_id'		=> $menu->css_id,
				'css_class' => $menu->css_class,
				'logged'		=> $menu->logged,
				'links'			=> MenuLink::getLinksForView((int)$id_menu, (int)$cookie->id_lang)
			);

			$smarty->assign('menu', $vars);

			if (file_exists(_PS_THEME_DIR_ . 'modules/'. $this->name .'/menu_tree.tpl'))
				$smarty->assign('menu_tpl_tree', _PS_THEME_DIR_ . 'modules/' . $this->name . '/menu_tree.tpl');
			else
				$smarty->assign('menu_tpl_tree', _PS_MODULE_DIR_ . $this->name . '/menu_tree.tpl');

			return $this->display(__FILE__, 'menus.tpl');
		}
		else
		return;
	}

	public function hookMenuTop($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookPayment($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookNewOrder($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookPaymentConfirm($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookPaymentReturn($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookUpdateQuantity($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookRightColumn($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookLeftColumn($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookHome($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookCart($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAuthentication($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAddProduct($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookTop($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookExtraRight($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookDeleteProduct($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookInvoice($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookUpdateOrderStatus($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAdminOrder($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookFooter($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookPDFInvoice($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAdminCustomers($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookOrderConfirmation($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookCreateAccount($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookCustomerAccount($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookOrderSlip($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookProductTab($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookProductTabContent($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookShoppingCart($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookCreateAccountForm($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAdminStatsModules($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookGraphEngine($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookOrderReturn($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookProductActions($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookBackOfficeHome($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookGridEngine($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookWatermark($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookCancelProduct($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookExtraLeft($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookProductOutOfStock($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookUpdateProductAttribute($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookExtraCarrier($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookShoppingCartExtra($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookSearch($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookBackBeforePayment($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookUpdateCarrier($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookPostUpdateOrderStatus($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookCreateAccountTop($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookBackOfficeHeader($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookBackOfficeTop($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookBackOfficeFooter($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookDeleteProductAttribute($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookProcessCarrier($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookOrderDetail($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookBeforeCarrier($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookOrderDetailDisplayed($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookPaymentCCAdded($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookExtraProductComparison($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookCategoryAddition($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookCategoryUpdate($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookCategoryDeletion($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookBeforeAuthentication($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookPaymentTop($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAfterCreateHtaccess($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAfterSaveAdminMeta($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAttributeGroupForm($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAfterSaveAttributeGroup($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAfterDeleteAttributeGroup($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookFeatureForm($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAfterSaveFeature($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAfterDeleteFeature($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAfterSaveProduct($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookProductListAssign($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookPostProcessAttributeGroup($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookPostProcessFeature($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookFeatureValueForm($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookPostProcessFeatureValue($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAfterDeleteFeatureValue($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAfterSaveFeatureValue($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAttributeForm($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookPostProcessAttribute($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAfterDeleteAttribute($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookAfterSaveAttribute($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookFrontCanonicalRedirect($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}

	public function hookMyAccountBlock($params)
	{
		return $this->allHooks($params, __FUNCTION__);
	}
}
?>