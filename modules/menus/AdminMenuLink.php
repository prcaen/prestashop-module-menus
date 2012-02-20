<?php
include_once _PS_MODULE_DIR_ . 'menus/backend/classes/MenuLink.php';
class AdminMenuLink extends AdminTab
{
	private $_module = 'menus';
	private $_menu;

	public function __construct()
	{
		$this->table = 'menu_link';
		$this->className	= 'MenuLink';
		$this->lang = false;
		$this->identifier = 'id_menu_link';

		$this->add			 = true;
		$this->edit			 = true;
		$this->delete		 = true;
		$this->duplicate = true;

		$this->_menu = AdminMenus::getCurrentMenu();

		$this->fieldsDisplay = array(
			'id_menu_link' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'id_parent' => array('title' => $this->l('ID parent'), 'align' => 'center', 'width' => 25),
			'type' => array('title' => $this->l('Type'), 'align' => 'center', 'width' => 130),
			'logged' => array('title' => $this->l('Logged'), 'align' => 'center', 'width' => 70),
			'css_id'		=> array('title' => $this->l('CSS id'), 'align' => 'center', 'width' => 135),
			'css_class' => array('title' => $this->l('CSS class'), 'align' => 'center', 'width' => 135),
			'position' => array('title' => $this->l('Position'), 'width' => 40,'filter_key' => 'a!position', 'align' => 'center', 'position' => 'position'),
			'a!active' => array('title' => $this->l('Displayed'), 'active' => 'status', 'filter_key' => 'a!active', 'align' => 'center', 'type' => 'bool', 'orderby' => false)
		);
		$this->_filter = 'AND a.`id_menu` = '.(int)($this->_menu->id);

		parent::__construct();
	}

	public function display($token = NULL)
	{
		global $currentIndex, $cookie;

		$currentIndex .= '&id_menu='. $this->_menu->id;
		$this->getList((int)($cookie->id_lang));

		echo '<a href="'.$currentIndex.'&add'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add a new link').'</a><br />';
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

	public function displayListContent($token = NULL)
	{
		global $currentIndex;
		echo '<input type="hidden" name="id_menu" value="'.$this->_menu->id.'" />';
		parent::displayListContent($token);
	}

	public function displayForm($token = NULL)
	{
		global $currentIndex, $cookie;

		parent::displayForm();

		if (!($obj = $this->loadObject(true)))
			return false;

		// Object values
		$id_menu_link = $this->getFieldValue($obj, 'id');
		$id_menu			= $this->getFieldValue($obj, 'id_menu');
		$id_parent		= $this->getFieldValue($obj, 'id_parent');
		$id_link			= $this->getFieldValue($obj, 'id_link');
		$type					= $this->getFieldValue($obj, 'type');
		$logged				= $this->getFieldValue($obj, 'logged');
		$css_id				= $this->getFieldValue($obj, 'css_id');
		$css_class		= $this->getFieldValue($obj, 'css_class');
		$active				= $this->getFieldValue($obj, 'active');
		// $active				= $this->getFieldValue($obj, 'position');

		// Lang
		$id_lang = (int)$cookie->id_lang;
		$divLangName = 'link¤title';

		$links				 = MenuLink::getLinks($id_lang, $id_menu);
		$manufacturers = Manufacturer::getManufacturers(false, $id_lang);
		$suppliers		 = Supplier::getSuppliers(false, $id_lang);
		$_cms					 = CMS::listCms($cookie->id_lang);

		echo '
		<style type="text/css">
		.hide {display: none}
		</style>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.($token!=NULL ? $token : $this->token).'" method="post">
			<fieldset>
				<legend><img src="' . _MODULE_DIR_ . $this->_module . '/logo.gif" />'.$this->l('Menu link').'</legend>
				'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$id_menu_link.'" />' : '').'
				<input type="hidden" name="id_menu" value="'.$id_menu.'" />';
				if(count($links) == 0 || $links[0]['id_menu_link'] == $id_menu_link)
					echo '<input type="hidden" name="id_parent" value="0" />';
				else
				{
					echo '<label for="type">' . $this->l('Parent Item:') . '</label>
					<div class="margin-form">
						<select name="id_parent">
							<option value="0">-- ' . $this->l('Choose a parent item') . ' --</option>';
							$this->_showOption($links, $id_lang, $id_parent, array($id_menu_link));
					echo'</select>
					</div>';
				}
				echo'
				<label for="type">' . $this->l('Type:') . '</label>
				<div class="margin-form">
					<select name="type" style="width: 189px" onchange="$(\'.case\').addClass(\'hide\');$(\'.case_\'+$(this).val()).removeClass(\'hide\');">
						<option value="">-- ' . $this->l('Select an item type') . ' --</option>
						<option value="category" ' . ($type == 'category' ? 'selected="selected"' : '') . '>' . $this->l('Categories') . '</option>
						<option value="product"' . ($type == 'product' ? 'selected="selected"' : '') . '>' . $this->l('Products') . '</option>
						<option value="cms"' . ($type == 'cms' ? 'selected="selected"' : '') . '>' . $this->l('CMS') . '</option>
						<option value="manufacturers"' . ($type == 'manufacturers' ? 'selected="selected"' : '') . '>' . $this->l('Manufacturers List') . '</option>
						<option value="manufacturer"' . ($type == 'manufacturer' ? 'selected="selected"' : '') . '>' . $this->l('Manufacturer') . '</option>
						<option value="suppliers"' . ($type == 'suppliers' ? 'selected="selected"' : '') . '>' . $this->l('Suppliers List') . '</option>
						<option value="supplier"' . ($type == 'supplier' ? 'selected="selected"' : '') . '>' . $this->l('Supplier') . '</option>
						<option value="link"' . ($type == 'link' ? 'selected="selected"' : '') . '>' . $this->l('Links') . '</option>
					</select><sup> *</sup>
				</div>';
				echo '
				<div class="case_category case ' . ($type == 'category' ? '' : 'hide'). '">
					<label for="id_category">' . $this->l('Category:') . '</label>
					<div class="margin-form">
						<select name="id_category" id="id_category" size="10" style="width: 189px">';
						$this->_getCategoryOption(1, $cookie->id_lang, true, (!is_null($id_menu_link)) ? $id_link : null);
						echo '
						</select><sup> *</sup>
						<p class="clear">' . $this->l('Start category') . '</p>
					</div>
				</div>
				<div class="case_product case ' . ($type == 'product' ? '' : 'hide'). '">
					<label for="id_product">' . $this->l('Product ID:') . '</label>
					<div class="margin-form">
						<input type="text" name="id_product" id="id_product" size="30" />
						<!--
						<select name="id_product" id="id_product">';
						echo '
						</select><sup> *</sup>
						-->
					</div>
				</div>
				<div class="case_cms case ' . ($type == 'cms' ? '' : 'hide'). '">
					<label for="id_cms">' . $this->l('CMS Page:') . '</label>
					<div class="margin-form">
						<select name="id_cms" id="id_cms" style="width: 189px">';
						foreach($_cms as $cms)
							echo '<option value="' . $cms['id_cms'] . '" 
							' . ((!is_null($id_menu_link) && $id_link == $cms['id_cms']) ? 'selected="selected"' : '') . '
							>' . 
							$cms['meta_title'] . '</option>';
						echo '
						</select><sup> *</sup>
					</div>
				</div>
				<div class="case_manufacturer case ' . ($type == 'manufacturer' ? '' : 'hide'). '">
					<label for="manufacturer_id">' . $this->l('Manufacturer:') . '</label>
					<div class="margin-form">
						<select name="id_manufacturer" id="id_manufacturer" style="width: 189px">';
						foreach($manufacturers as $manufacturer)
							echo '<option value="' . $manufacturer['id_manufacturer'] . '" 
							' . ((!is_null($id_menu_link) && $id_link == $manufacturer['id_manufacturer']) ? 'selected="selected"' : '') . '
							>' . $manufacturer['name'] . '</option>';
						echo '
						</select>
					</div>
				</div>
				<div class="case_supplier case ' . ($type == 'supplier' ? '' : 'hide'). '">
					<label for="id_supplier">' . $this->l('Supplier:') . '</label>
					<div class="margin-form">
						<select name="id_supplier" id="id_supplier" style="width: 189px">';
						foreach($suppliers as $supplier)
							echo '<option value="' . $supplier['id_supplier'] . '" 
							' . ((!is_null($id_menu_link) && $id_link == $supplier['id_supplier']) ? 'selected="selected"' : '') . '
							>' . $supplier['name'] . '</option>';
						echo '
						</select><sup> *</sup>
					</div>
				</div>
				<div class="case_link case ' . ($type == 'link' ? '' : 'hide'). '">
					<label for="link">' . $this->l('URL:') . '</label>
					<div class="margin-form">';
					foreach ($this->_languages as $language) {
						echo '
						<div id="link_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
							<input size="30" type="text" name="link['.$language['id_lang'].']" value="'.htmlentities($this->getFieldValue($obj, 'link', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" class="'.($language['id_lang'] != $this->_defaultFormLanguage ? 'clone' : 'cloneParent').'" /><sup> *</sup>
						</div>';
					}
				$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'link');
				echo '</div><div class="clear space">&nbsp;</div></div>';
				echo '	<label>'.$this->l('Title:').' </label>
						<div class="margin-form">';
				foreach ($this->_languages as $language)
					echo '	<div id="title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
								<input size="30" type="text" name="title['.$language['id_lang'].']" value="'.htmlentities($this->getFieldValue($obj, 'title', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
							</div>';
				$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'title');
				echo '	</div><div class="clear space">&nbsp;</div>';
				echo '<label>'.$this->l('CSS ID:').'</label>
			<div class="margin-form">
				<input size="30" type="text" name="css_id" value="'.$css_id.'" id="css_id" />
			</div>
			<label>'.$this->l('CSS class:').'</label>
			<div class="margin-form">
				<input size="30" type="text" name="css_class" value="'.$css_class.'" id="css_class" />
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
			<div class="margin-form space">
				<input type="submit" value="'.$this->l('	 Save		').'" name="submitAdd'.$this->table.'" class="button" />
			</div>
			<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
	
	public function postProcess($token = NULL)
	{
		global $cookie, $currentIndex;
		if (Tools::isSubmit('submitAddmenu_link'))
		{
			// ID
			$id_menu_link = (isset($_POST['id_menu_link']) ? Tools::getValue('id_menu_link') : null);

			// Types
			$type = trim(Tools::getValue('type'));
			$id_category = intval(Tools::getValue('id_category'));
			$id_product = intval(Tools::getValue('id_product'));
			$id_cms = intval(Tools::getValue('id_cms'));
			$id_manufacturer = intval(Tools::getValue('id_manufacturer'));
			$id_supplier = intval(Tools::getValue('id_supplier'));
			$id_link = null;
			$linkL = Tools::getValue('link');
			
			// Object
			$link = new MenuLink($id_menu_link);
			$link->id_menu	 = (int)Tools::getValue('id_menu');
			$link->id_parent = (int)Tools::getValue('id_parent');
			$link->type			 = $type;
			$link->logged		 = (int)Tools::getValue('logged', 0);
			$link->css_id		 = Tools::getValue('css_id', 'test');
			$link->css_class = Tools::getValue('css_class', 'test');
			$link->active		 = (int)Tools::getValue('active', 0);
			$link->title		 = Tools::getValue('title');

			$isValid = false;
			switch($type)
			{
				case 'category':
					$fieldsValidate = array('id_category'=>'isUnsignedInt');
					if($this->_fieldsValidate($fieldsValidate))
					{
						$isValid = true;
						$link->id_link = $id_category;
					}
					else
						$this->_errors[] = $this->l('You must enter the required fields');
					break;
				case 'product':
					$fieldsValidate = array('id_product'=>'isUnsignedInt');
					if($this->_fieldsValidate($fieldsValidate))
					{
						$isValid = true;
						$link->id_link = $id_product;
					}
					else
						$this->_errors[] = $this->l('You must enter the required fields');
					break;
				case 'cms':
					$fieldsValidate = array('id_cms'=>'isUnsignedInt');
					if($this->_fieldsValidate($fieldsValidate))
					{
						$isValid = true;
						$link->id_link = $id_cms;
					}
					else
						$this->_errors[] = $this->l('You must enter the required fields');
					break;
				case 'manufacturer':
					$fieldsValidate = array('id_manufacturer'=>'isUnsignedInt');
					if($this->_fieldsValidate($fieldsValidate))
					{
						$isValid = true;
						$link->id_link = $id_manufacturer;
					}
					else
						$this->_errors[] = $this->l('You must enter the required fields');
					break;
				case 'supplier':
					$fieldsValidate = array('id_supplier'=>'isUnsignedInt');
					if($this->_fieldsValidate($fieldsValidate))
					{
						$isValid = true;
						$link->id_link = $id_supplier;
					}
					else
						$this->_errors[] = $this->l('You must enter the required fields');
					break;
				case 'link':
					$fieldsValidate = array('title'=>'isGenericName', 'linkL'=>'isGenericName');
					if($this->_fieldsValidate($fieldsValidate))
					{
						$isValid = true;
						$link->id_link = 0;
						$link->link = $linkL;
					}
					else
						$this->_errors[] = $this->l('You must enter the required fields');
					break;
			}

			// Add a new link
			if($this->add && !isset($_POST['id_menu_link']) && $isValid)
			{
				if (!$link->add())
					$this->_errors[] = Tools::displayError('An error occurred while creating object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
				else
					Tools::redirectAdmin($currentIndex.'&conf=3&id_menu='.$link->id_menu.'&updatemenu&token='.Tools::getAdminTokenLite('AdminMenus'));
			}
			// Edit a link
			elseif ($this->edit && isset($_POST['id_menu_link']) && $isValid)
			{
				if (!$link->update())
					$this->_errors[] = Tools::displayError('An error occurred while updating object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
				else
					Tools::redirectAdmin($currentIndex.'&conf=4&id_menu='.$link->id_menu.'&updatemenu&token='.Tools::getAdminTokenLite('AdminMenus'));
			}
			elseif($isValid)
				$this->_errors[] = Tools::displayError($this->l('You must enter the required fields'));
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add or edit here.');
		}
		// Delete a link
		elseif (Tools::isSubmit('deletemenu_link'))
		{
			$id_menu_link = Tools::getValue('id_menu_link');
			if ($this->delete && $_GET['id_menu_link'])
			{
				$link = new MenuLink($id_menu_link);
				$id_menu = $link->id_menu;
				if (!$link->delete())
					$this->_errors[] = Tools::displayError('An error occurred while deleting object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
				else
					Tools::redirectAdmin($currentIndex.'&conf=1&id_menu='.$id_menu.'&updatemenu&token='.Tools::getAdminTokenLite('AdminMenus'));
			}
		}
		// Delete multiple links
		elseif (Tools::getValue('submitDel'.$this->table))
		{
			if ($this->delete)
			{
				if (isset($_POST[$this->table.'Box']))
				{
					$id_menu = (int)(Tools::getValue('id_menu'));
					$link = new MenuLink();
					$result = true;
					$result = $link->deleteSelection(Tools::getValue($this->table.'Box'));
					if ($result)
					{
						$link->cleanPositions($id_menu);
						Tools::redirectAdmin($currentIndex.'&conf=2&id_menu='.$id_menu.'&updatemenu&token='.Tools::getAdminTokenLite('AdminMenus'));
					}
					$this->_errors[] = Tools::displayError('An error occurred while deleting selection.');
				}
				else
					$this->_errors[] = Tools::displayError('You must select at least one element to delete.');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
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
				Tools::redirectAdmin($currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=4'.(($id_category = (int)(Tools::getValue('id_cms_category'))) ? ('&id_cms_category='.$id_category) : '').'&token='.Tools::getAdminTokenLite('AdminCMSContent'));
		}
		/* Change object statuts (active, inactive) */
		elseif (Tools::isSubmit('status') AND Tools::isSubmit($this->identifier))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isLoadedObject($object = $this->loadObject()))
				{
					if ($object->toggleStatus())
						Tools::redirectAdmin($currentIndex.'&conf=5'.((int)Tools::getValue('id_cms_category') ? '&id_cms_category='.(int)Tools::getValue('id_cms_category') : '').'&token='.Tools::getValue('token'));
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

// ---- TOOLS
	private function _getCategoryOption($id_category, $id_lang, $children = true, $selectedCat = 0)
	{
		$categorie = new Category($id_category, $id_lang);
		if(is_null($categorie->id))
			return;
		if(count(explode('.', $categorie->name)) > 1)
			$name = str_replace('.', '', strstr($categorie->name, '.'));
		else
			$name = $categorie->name;
		echo '<option value="'.$categorie->id.'" ' . (($categorie->id == $selectedCat) ? 'selected="selected"' : '') . ' 
										style="margin-left:'.(($children) ? round(0+(15*(int)$categorie->level_depth)) : 0).'px;">'.$name.'</option>';
		if($children)
		{
			$childrens = Category::getChildren($id_category, $id_lang);
			if(count($childrens))
				foreach($childrens as $_children)
					$this->_getCategoryOption($_children['id_category'], $id_lang, $children, $selectedCat);
		}
	}

	private function _fieldsValidate($fields)
	{
		foreach ($fields as $field => $values)
		{
			if (!is_array($values))
			{
				$values = array($field=>$values);
			}
			foreach ($values as $field => $method)
			{
				$_values = Tools::getValue($field, $method == 'isUnsignedInt' ? 0 : '');
				if (!is_array($_values))
					$_values = array($_values);

				foreach ($_values as $value)
				{
					$return = call_user_func(array('Validate', $method), $value);
					if (!$return || ($method == 'isUnsignedInt' && intval(Tools::getValue($field, 0)) === 0))
						return false;
				}
			}
		}
		return true;
	}

	private function _showOption($items, $id_lang, $itemSelected = 0, $ignoreItems = array()) {
		foreach ($items as $item) {
			$value = $item['id_menu_link'];
			if (in_array($item['id_menu_link'], $ignoreItems)) {
				continue;
			}
			echo '<option value="' . $value . '" ' . 
											(intVal($itemSelected) == $item['id_menu_link'] ? 'selected="selected"' : '') . '>' . 
											MenuLink::getTitle($item['id_menu_link'], $id_lang) . ' (' . $this->l(ucFirst($item['type'])) . ')' .
											'</option>';
			if (isset($item['childrens']) && count($item['childrens'])) {
				$this->_showOption($item['childrens'], $id_lang, $itemSelected, $ignoreItems);
			}
		}
	}
}
?>