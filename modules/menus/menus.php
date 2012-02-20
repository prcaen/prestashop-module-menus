<?php
if (!defined('_PS_VERSION_'))
	exit;

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
			'name'		=> 'MENU_DISPLAY_TOP',
			'id'			=> 'display_top',
			'title'		=> $this->l('Display top menu'),
			'type'		=> 'boolean',
			'default' => 1
			),
			2 => array(
			'name'		=> 'MENU_DISPLAY_FOOTER',
			'id'			=> 'display_footer',
			'title'		=> $this->l('Display footer menu'),
			'type'		=> 'boolean',
			'default' => 0
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
		global $smarty;

		foreach($this->_configs as &$config)
		{
			$config['value'] = Configuration::get($config['name']);
		}

		$smarty->assign('action', Tools::safeOutput($_SERVER['REQUEST_URI']));
		$smarty->assign('module_dir', $this->_path);
		$smarty->assign('configs', $this->_configs);

		echo $smarty->display($this->_tpl);
	}

	public function hookHeader()
	{
		global $smarty;

		Tools::addCSS($this->_path . $this->name . '.css', 'all');
		Tools::addJS($this->_path	 . $this->name . '.js');

		if (Configuration::get('MENU_DISPLAY_TOP'))
		{
			$smarty->assign('HOOK_MENU_TOP', Module::hookExec('menuTop'));
		}

		if (Configuration::get('MENU_DISPLAY_FOOTER'))
		{
			$smarty->assign('HOOK_MENU_FOOTER', Module::hookExec('menuFooter'));
		}
	}

	public function hookMenuTop()
	{
		
	}

	public function hookMenuFooter()
	{
		
	}

	private function _postProcess()
	{
		
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
			`position` int(10) unsigned NOT NULL DEFAULT  \'0\', 
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
	
}
?>