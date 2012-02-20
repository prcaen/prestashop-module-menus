<?php
class AdminMenu extends AdminTab
{
	private $_module = 'menus';

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
			'id_menu' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'position' => array('title' => $this->l('Position'), 'align' => 'center', 'width' => 35),
			'css_id' => array('title' => $this->l('CSS id'), 'align' => 'center', 'width' => 310),
			'css_class' => array('title' => $this->l('CSS class'), 'align' => 'center', 'width' => 310),
			'a!active' => array('title' => $this->l('Displayed'), 'active' => 'status', 'filter_key' => 'a!active', 'align' => 'center', 'type' => 'bool', 'orderby' => false)
		);
		
		parent::__construct();
	}

	public function displayForm($token = NULL)
	{
		global $currentIndex, $cookie;
		parent::displayForm();
		
		if (!($obj = $this->loadObject(true)))
			return false;
		
		$active    = $this->getFieldValue($obj, 'active');
		$position  = $this->getFieldValue($obj, 'position');
		$css_id    = $this->getFieldValue($obj, 'css_id');
		$css_class = $this->getFieldValue($obj, 'css_class');

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.($token!=NULL ? $token : $this->token).'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset>
				<legend><img src="' . _MODULE_DIR_ . $this->_module . '/logo.gif" />'.$this->l('Menu').'</legend>
				<label>'.$this->l('Position:').' </label>
				<div class="margin-form">
					<input type="radio" name="position" id="position_top" value="top" '.($position == 'top' ? 'checked="checked" ' : '').'/>
					<label class="t" for="position_top">' . $this->l('Top') .'</label>
					<input type="radio" name="position" id="position_footer" value="footer" '.($position == 'footer' ? 'checked="checked" ' : '').'/>
					<label class="t" for="position_footer">' . $this->l('Footer') .'</label>
				</div>
				<label>'.$this->l('CSS ID:').'</label>
				<div class="margin-form">
					<input type="text" name="css_id" value="'.$css_id.'" id="css_id" />
				</div>
				<label>'.$this->l('CSS class:').'</label>
				<div class="margin-form">
					<input type="text" name="css_class" value="'.$css_class.'" id="css_class" />
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
		</form>';
	}
	public function display($token = NULL)
	{
		global $currentIndex, $cookie;
		
		$this->getList((int)($cookie->id_lang));

		$id_menu = Tools::getValue('id_menu');
		echo '<a href="'.$currentIndex.'&add'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add a new item').'</a><br />';
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
	
	public function postProcess()
	{
		global $cookie, $link, $currentIndex;
		
		if (Tools::isSubmit('deletemenu'))
		{
			$menu = new Menu((int)(Tools::getValue('id_menu')));
			//$menu->cleanPositions($menu->id_menu_category);
			if (!$menu->delete())
				$this->_errors[] = Tools::displayError('An error occurred while deleting object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
			else
			{
				Tools::redirectAdmin($currentIndex.'&id_menu='.$menu->id_menu.'&conf=1&token='.Tools::getAdminTokenLite('AdminMenus'));
			}
		}/* Delete multiple objects */
		elseif (Tools::getValue('submitDel'.$this->table))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (isset($_POST[$this->table.'Box']))
				{
					$menu = new Menu();
					$result = true;
					$result = $menu->deleteSelection(Tools::getValue($this->table.'Box'));
					if ($result)
					{
						//$menu->cleanPositions((int)(Tools::getValue('id_menu_category')));
						if(isset($_GET['id_menu']))
							$id_menu = (int)(Tools::getValue('id_menu_category'));
						else
							$id_menu = 0;
						Tools::redirectAdmin($currentIndex.'&conf=2&token='.Tools::getAdminTokenLite('AdminMenus').'&id_menu='. $id_menu);
					}
					$this->_errors[] = Tools::displayError('An error occurred while deleting selection.');

				}
				else
					$this->_errors[] = Tools::displayError('You must select at least one element to delete.');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (Tools::isSubmit('submitAddmenu'))
		{
			parent::validateRules();

			if (!sizeof($this->_errors))
			{
				if (!$id_menu = (int)(Tools::getValue('id_menu')))
				{
					$menu = new Menu();
					$this->copyFromPost($menu, 'menu');
					if (!$menu->add())
						$this->_errors[] = Tools::displayError('An error occurred while creating object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
					else
						Tools::redirectAdmin($currentIndex.'&id_menu='.$menu->id_menu.'&conf=3&token='.Tools::getAdminTokenLite('AdminMenus'));
				}
				else
				{
					$menu = new Menu($id_menu);
					$this->copyFromPost($menu, 'menu');
					if (!$menu->update())
						$this->_errors[] = Tools::displayError('An error occurred while updating object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
					else
						Tools::redirectAdmin($currentIndex.'&id_menu='.$menu->id_menu.'&conf=4&token='.Tools::getAdminTokenLite('AdminMenus'));
				}
			}
		}
		elseif (Tools::getValue('position'))
		{
			if ($this->tabAccess['edit'] !== '1')
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
			elseif (!Validate::isLoadedObject($object = $this->loadObject()))
				$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			elseif (!$object->updatePosition((int)(Tools::getValue('way')), (int)(Tools::getValue('position'))))
				$this->_errors[] = Tools::displayError('Failed to update the position.');
			else
				Tools::redirectAdmin($currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=4'.(($id_category = (int)(Tools::getValue('id_menu_category'))) ? ('&id_menu_category='.$id_category) : '').'&token='.Tools::getAdminTokenLite('AdminMenuContent'));
		}
		/* Change object statuts (active, inactive) */
		elseif (Tools::isSubmit('status') AND Tools::isSubmit($this->identifier))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isLoadedObject($object = $this->loadObject()))
				{
					if ($object->toggleStatus())
						Tools::redirectAdmin($currentIndex.'&conf=5'.((int)Tools::getValue('id_menu_category') ? '&id_menu_category='.(int)Tools::getValue('id_menu_category') : '').'&token='.Tools::getValue('token'));
					else
						$this->_errors[] = Tools::displayError('An error occurred while updating status.');
				}
				else
					$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		else
			parent::postProcess(true);
	}
}
?>