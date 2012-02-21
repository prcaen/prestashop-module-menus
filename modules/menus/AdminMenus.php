<?php
include_once _PS_MODULE_DIR_ . 'menus/backend/classes/Menu.php';
include_once _PS_MODULE_DIR_ . 'menus/AdminMenuLink.php';
class AdminMenus extends AdminTab
{
	private $_module = 'menus';

	/** @var object Menu() instance for navigation*/
	private static $_menu = NULL;

	/** @var object AdminMenuLink() instance */
	private $adminMenuLink;

	public function __construct()
	{
		$this->table = 'menu';
		$this->className	= 'Menu';
		$this->lang = false;
		$this->identifier = 'id_menu';

		$this->add			 = true;
		$this->edit			 = true;
		$this->delete		 = true;
		$this->duplicate = true;

		$this->fieldsDisplay = array(
			'id_menu'		=> array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'h!name'		=> array('title' => $this->l('Hook'), 'align' => 'center', 'width' => 35),
			'logged' 		=> array('title' => $this->l('Logged'), 'align' => 'center', 'width' => 70),
			'css_id'		=> array('title' => $this->l('CSS id'), 'align' => 'center', 'width' => 310),
			'css_class' => array('title' => $this->l('CSS class'), 'align' => 'center', 'width' => 310),
			'a!active'	=> array('title' => $this->l('Displayed'), 'active' => 'status', 'filter_key' => 'a!active', 'align' => 'center', 'type' => 'bool', 'orderby' => false)
		);
		$this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'hook` h ON (h.`id_hook` = a.`id_hook`)';
		$this->_select = 'h.`name`';

		$id_menu = abs((int)(Tools::getValue('id_menu')));
		if (!$id_menu) $id_menu = null;
		self::$_menu = new Menu($id_menu);

		$this->adminMenuLink = new AdminMenuLink();

		parent::__construct();
	}

	public function display()
	{
		global $currentIndex;

		if ((Tools::isSubmit('submitAddmenu_link') AND sizeof($this->adminMenuLink->_errors)) OR isset($_GET['updatemenu_link']) OR isset($_GET['addmenu_link']) OR isset($_GET['deletemenu_link']))
		{
			if(self::$_menu->id)
				$id_menu = self::$_menu->id;
			else
				$id_menu = (int)Tools::getValue('id_menu');

			$this->adminMenuLink->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&id_menu='.$id_menu.'&updatemenu&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
		}
		else
			parent::display();
	}

	public function displayForm($token = NULL)
	{
		global $currentIndex, $cookie;
		parent::displayForm();

		if (!($obj = $this->loadObject(true)))
			return false;

		$id_menu	 = $this->getFieldValue($obj, 'id');
		$id_hook	 = $this->getFieldValue($obj, 'id_hook');
		$logged		 = $this->getFieldValue($obj, 'logged');
		$active		 = $this->getFieldValue($obj, 'active');
		$css_id		 = $this->getFieldValue($obj, 'css_id');
		$css_class = $this->getFieldValue($obj, 'css_class');

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.($token!=NULL ? $token : $this->token).'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$id_menu.'" />' : '').'
			<fieldset>
				<legend><img src="' . _MODULE_DIR_ . $this->_module . '/logo.gif" />'.$this->l('Menu').'</legend>
				<label>'.$this->l('Hook:').'</label>
				<div class="margin-form">
					<select name="id_hook">';
					foreach(Hook::getHooks() AS $hook)
					{
						echo '<option value="'.$hook['id_hook'].'" '.($id_hook == $hook['id_hook'] ? 'selected="selected"' : '').'>'.$hook['name'].'</option>';
					}
		echo '</select>
				</div>
				<label>'.$this->l('CSS ID:').'</label>
				<div class="margin-form">
					<input type="text" name="css_id" value="'.$css_id.'" id="css_id" />
				</div>
				<label>'.$this->l('CSS class:').'</label>
				<div class="margin-form">
					<input type="text" name="css_class" value="'.$css_class.'" id="css_class" />
				</div>
				<label>'.$this->l('User must be logged:').' </label>
				<div class="margin-form">
					<input type="radio" name="logged" id="logged_on" value="1" '.($logged ? 'checked="checked" ' : '').'/>
					<label class="t" for="logged_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="logged" id="logged_off" value="0" '.(!$logged ? 'checked="checked" ' : '').'/>
					<label class="t" for="logged_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
				</div>
				<label>'.$this->l('Displayed:').' </label>
				<div class="margin-form">
					<input type="radio" name="active" id="active_on" value="1" '.($active ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="active" id="active_off" value="0" '.(!$active ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
				</div>
				<div class="margin-form">
					<input type="submit" class="button" name="submitAdd'.$this->table.'" value="'.$this->l('Save').'"/>
				</div>
			</fieldset>
		</form>
		<div style="margin:10px">&nbsp;</div>';

		echo '<h2>'.$this->l('Links in this menu').'</h2>';
		$this->adminMenuLink->display($this->token);
	}
	
	public function postProcess()
	{
		if(Tools::isSubmit('submitAddmenu_link') || Tools::isSubmit('submitDelmenu_link') || isset($_GET['deletemenu_link']) || isset($_GET['duplicatemenu_link']) || (isset($_GET['position']) && isset($_GET['id_menu_link'])))
			$this->adminMenuLink->postProcess($this->token);
		else
		{
			if(Tools::isSubmit('submitAddmenu'))
			{
				$module = Module::getInstanceByName('menus');

				if(isset($_POST['id_menu']))
				{
					$id_menu = (int)Tools::getValue('id_menu');
					$menu = new Menu($id_menu);
					$id_hook = $menu->id_hook;
					$module->unregisterHook($id_hook);
				}

				$id_hook = (int)(Tools::getValue('id_hook'));
				$hook = new Hook($id_hook);
				
				if(!Validate::isLoadedObject($module))
					$this->_errors[] = Tools::displayError('module cannot be loaded');
				elseif (!$id_hook OR !Validate::isLoadedObject($hook))
					$this->_errors[] = Tools::displayError('Hook cannot be loaded.');
				elseif (!$module->registerHook($hook->name))
					$this->_errors[] = Tools::displayError('An error occurred while transplanting module to hook.');
				elseif (Hook::getModuleFromHook($id_hook, $id_module))
					$this->_errors[] = Tools::displayError('This module is already transplanted to this hook.');
				elseif (!$module->isHookableOn($hook->name))
					$this->_errors[] = Tools::displayError('This module cannot be transplanted to this hook.');
				else
					parent::postProcess();
			}
			else
				parent::postProcess();
		}
	}
	
	public function displayErrors()
	{
		parent::displayErrors();
		$this->adminMenuLink->displayErrors();
	}
	
	public static function getCurrentMenu()
	{
		return self::$_menu;
	}
}
?>