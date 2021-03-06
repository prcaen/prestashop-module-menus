<?php
class MenuLink extends ObjectModel
{
	public $id;
	public $id_menu;
	public $id_link;
	public $id_parent;
	public $type;
	public $logged = 0;
	public $css_class;
	public $css_id;
	public $position;
	public $active;

	public $title;
	public $link;

	/** @var string Object creation date */
	public $date_add;

	/** @var string Object last modification date */
	public $date_upd;

	protected $tables = array('menu_link', 'menu_link_lang');

	protected $fieldsRequired = array('id_menu', 'id_parent', 'type', 'logged', 'active', 'id_link');
	protected $fieldsSize = array('position' => 16, 'css_id' => 32, 'css_class' => 32, 'active' => 1);
	protected $fieldsSizeLang = array('link' => 128, 'title' => 128);
	protected $fieldsValidate = array(
		'id_menu'		=> 'isUnsignedId',
		'id_parent' => 'isUnsignedId',
		'id_link'		=> 'isUnsignedId',
		'type'			=> 'isString',
		'logged'		=> 'isBool',
		'css_id'		=> 'isGenericName',
		'css_class' => 'isGenericName',
		'active'		=> 'isBool'
	);
	protected $fieldsValidateLang = array(
		'link' => 'isGenericName',
		'title' => 'isGenericName'
	);
	
	protected $table = 'menu_link';
	protected $identifier = 'id_menu_link';

	public function __construct($id_menu_link = NULL, $id_lang = NULL)
	{
		parent::__construct($id_menu_link, $id_lang);
	}
	
	public function getFields()
	{
		parent::validateFields();

		if (isset($this->id))
			$fields['id_menu_link'] = (int)($this->id);

		$fields['id_menu']	 = (int)($this->id_menu);
		$fields['id_parent'] = (int)($this->id_parent);
		$fields['id_link']	 = (int)($this->id_link);
		$fields['type']			 = (string)($this->type);
		$fields['logged']		 = (int)($this->logged);
		$fields['css_id']		 = (string)($this->css_id);
		$fields['css_class'] = (string)($this->css_class);
		$fields['active']		 = (int)($this->active);
		$fields['date_add']	 = pSQL($this->date_add);
		$fields['date_upd']	 = pSQL($this->date_upd);

		return $fields;
	}

	public function getTranslationsFieldsChild()
	{
		self::validateFieldsLang();

		$fieldsArray = array('title', 'link');
		$fields = array();
		$languages = Language::getLanguages(false);
		$defaultLanguage = Configuration::get('PS_LANG_DEFAULT');
		foreach ($languages as $language)
		{
			$fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
			$fields[$language['id_lang']][$this->identifier] = (int)($this->id);
			$fields[$language['id_lang']]['title'] = (isset($this->title[$language['id_lang']])) ? pSQL($this->title[$language['id_lang']], true) : '';
			$fields[$language['id_lang']]['link'] = (isset($this->link[$language['id_lang']])) ? pSQL($this->link[$language['id_lang']], true) : '';
			foreach ($fieldsArray as $field)
			{
				if (!Validate::isTableOrIdentifier($field))
					die(Tools::displayError());

				/* Check fields validity */
				if (isset($this->{$field}[$language['id_lang']]) AND !empty($this->{$field}[$language['id_lang']]))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']]);
				elseif (in_array($field, $this->fieldsRequiredLang))
				{
					if ($this->{$field} != '')
						$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$defaultLanguage]);
				}
				else
					$fields[$language['id_lang']][$field] = '';
			}
		}
		return $fields;
	}
	
	public function updatePosition($way, $position)
	{
		if (!$results = Db::getInstance()->ExecuteS('
			SELECT ml.`id_menu_link`, ml.`position`, ml.`id_menu`
			FROM `'._DB_PREFIX_.'menu_link` ml
			WHERE ml.`id_menu` = '.(int)(Tools::getValue('id_menu', 1)).'
			ORDER BY ml.`position` ASC'
		))
			return false;

		foreach ($results AS $result)
			if ((int)($result['id_menu_link']) == (int)($this->id))
				$movedMenu = $result;

		if (!isset($movedMenu) || !isset($position))
			return false;

		// < and > statements rather than BETWEEN operator
		// since BETWEEN is treated differently according to databases
		return (Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'menu_link`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position`
			'.($way
				? '> '.(int)($movedMenu['position']).' AND `position` <= '.(int)($position)
				: '< '.(int)($movedMenu['position']).' AND `position` >= '.(int)($position)).'
			AND `id_menu`='.(int)($movedMenu['id_menu']))
		AND Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'menu_link`
			SET `position` = '.(int)($position).'
			WHERE `id_menu_link` = '.(int)($movedMenu['id_menu_link']).'
			AND `id_menu`='.(int)($movedMenu['id_menu'])));
	}
	
	public function getLinks($id_lang, $id_menu)
	{
		$sql = 'SELECT ml.`id_menu_link`, ml.`type`
		FROM `'._DB_PREFIX_.'menu_link` ml
		LEFT JOIN `'._DB_PREFIX_.'menu_link_lang` mll ON (ml.`id_menu_link` = mll.`id_menu_link` AND mll.`id_lang` = '.$id_lang.')
		WHERE ml.`id_menu` = ' . $id_menu;

		return Db::getInstance()->ExecuteS($sql);
	}

	public static function getTitle($id_menu, $id_lang) {
			$menu = new MenuLink($id_menu, $id_lang);
			$title = $menu->title;
			if (trim($title) == '') {
					// Spec. CMS
					if($menu->type == 'cms') {
							$object = new CMS($menu->id_link, $id_lang);
							$title = $object->meta_title;
					}
					else if ($menu->type != 'link' && $menu->type != 'manufacturers' && $menu->type != 'suppliers') {
							$objectName = ucfirst($menu->type);
							$object = new $objectName($menu->id_link, $id_lang);
							$title = $object->name;
							if (is_array($title)) {
									$title = $object->name[$id_lang];
							}
					}
			}
			if(count(explode('.', $title)) > 1) {
					$title = str_replace('.', '', strstr($title, '.'));
			}
			return $title;
	}

	public static function cleanPositions($id_menu)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_menu_link`
		FROM `'._DB_PREFIX_.'menu_link`
		WHERE `id_menu` = '.(int)($id_menu).'
		ORDER BY `position`');
		$sizeof = sizeof($result);
		for ($i = 0; $i < $sizeof; ++$i){
				$sql = '
				UPDATE `'._DB_PREFIX_.'menu_link`
				SET `position` = '.(int)($i).'
				WHERE `id_menu` = '.(int)($id_menu).'
				AND `id_menu_link` = '.(int)($result[$i]['id_menu_link']);
				Db::getInstance()->Execute($sql);
			}
		return true;
	}
	
	// --- Links ---
	public static function getLinksForView($id_menu, $id_lang, $id_parent = 0) {
		if (self::isCached($id_lang))
			return self::getCache($id_lang);

		$sql = '
				SELECT ml.`id_menu_link`, ml.`type`, ml.`id_link`,  ml.`logged`, ml.`css_id`, ml.`css_class`, ml.`position`, mll.`title`, mll.`link`
				FROM `' . _DB_PREFIX_ . 'menu_link` ml
				LEFT JOIN `' . _DB_PREFIX_ . 'menu_link_lang` mll ON (mll.`id_menu_link` = ml.`id_menu_link` AND mll.`id_lang` = "' . $id_lang . '")
				WHERE ml.`id_parent` = "' . $id_parent . '" AND ml.`id_menu` = "' . $id_menu . '" AND ml.`active` = 1
				ORDER BY ml.`position` ASC';
		//die($sql);
		$results = Db::getInstance()->ExecuteS($sql);

		foreach($results as $k=>$result)
		{
			if (is_array($result) && count($result))
				$childrens = self::getLinksForView($id_menu, $id_lang, $result['id_menu_link']);
			else
				$childrens = array();

			// BEGIN - TITLE
			$link = $result['link'];
			if ($result['type'] == 'cms')
			{
				$object = new CMS($result['id_link'], $id_lang);
				$title = $object->meta_title;
				$cms = CMS::getLinks($id_lang, array($result['id_link']));
				if(count($cms))
					$link = $cms[0]['link'];
				else
					$link = '#';
			}
			else if ($result['type'] != 'link' && $result['type'] != 'manufacturers' && $result['type'] != 'suppliers') {
				if ($result['type'] == 'product')
				{
					$objectName = ucfirst($result['type']);
					$object = new $objectName($result['id_link'], true, $id_lang);
				}
				else
				{
					$objectName = ucfirst($result['type']);
					$object = new $objectName($result['id_link'], $id_lang);
				}

				$title = $object->name;
				switch ($result['type'])
				{
					case 'category':
						$link = $object->getLink();
						if (Configuration::get('MENU_CATEGORIES_NUM') == '2' && $result['id_link'] != '1')
							$results[$k]['numProducts'] = self::getNumProductsByCategory($result['id_link']);

						$categories = self::getCategories($result['id_link'], $id_lang, array(), 0);

						if (isset($categories[0]) && isset($categories[0]['childrens']))
								$childrens = array_merge($childrens, $categories[0]['childrens']);
						break;
					case 'product':
						$link = $object->getLink();
						break;
					case 'manufacturer':
						if (intval(Configuration::get('PS_REWRITING_SETTINGS')))
							$manufacturer->link_rewrite = Tools::link_rewrite($title, false);
						else
							$manufacturer->link_rewrite = 0;

						$_link = new Link;
						$link = $_link->getManufacturerLink($result['id_link'], $object->link_rewrite);
						break;
					case 'supplier':
						$_link = new Link;
						$link = $_link->getSupplierLink($result['id_link'], $object->link_rewrite);
						break;
						}
				}

				// Manufacturers
				if ($result['type'] == 'manufacturers')
				{
					$link = 'manufacturer.php';
					$childrens = self::getManufacturers();
				}

				// Suppliers
				if ($result['type'] == 'suppliers')
				{
					$link = 'supplier.php';
					$childrens = self::getSuppliers();
				}

				$results[$k]['link'] = $link;
				if (trim($result['title']) == '')
						$results[$k]['title'] = $title;

				if(count(explode('.', $results[$k]['title'])) > 1)
						$results[$k]['title'] = str_replace('.', '', strstr($results[$k]['title'], '.'));

				$results[$k]['id'] = $result['id_link'];
				unset(
						$results[$k]['id_parent'],
						$results[$k]['id_link']
				);

				// END - TITLE
				$results[$k]['childrens'] = $childrens;
			}

		if ($id_parent == 0)
		self::setCache($results, $id_lang);

		return $results;
	}

	public static function getCategories($id_category, $id_lang, $ignore = array(), $maxLevel = 0, $currLevel = 0)
	{
		$results = array();
		$currLevel++;
		$categorie = new Category($id_category, $id_lang);
  
		if (is_null($categorie->id))
			return $results;
  
		if (count(explode('.', $categorie->name)) > 1)
			$title = str_replace('.', '', strstr($categorie->name, '.'));
		else
			$title = $categorie->name;
  
		$link = $categorie->getLink();
  
		$childrens = array();
		$_childrens = Category::getChildren($id_category, $id_lang);
  
		if (count($_childrens))
		{
				foreach($_childrens as $children)
				{
					$id_category = $children['id_category'];
					$children = self::getCategories($id_category, $id_lang, $ignore, $maxLevel, $currLevel);
						if (!in_array($id_category, $ignore)) {
								if (isset($children[0])) {
										$childrens[] = $children[0];
								}
						}
				}
		}
  
		if (!in_array($categorie->id, $ignore) && !($currLevel > $maxLevel && $maxLevel != 0))
		{
			if(count(explode('.', $title)) > 1)
				$title = str_replace('.', '', strstr($title, '.'));
  
			$results[] = array(
				'id' => $categorie->id,
				'id_menu' => '',
				'type' => 'category',
				'title' => $title,
				'logged' => null,
				'css_id' => '',
				'css_class' => '',
				'link' => $link,
				'childrens' => $childrens,
			);
		}
		return $results;
	}

	public static function getManufacturers()
	{
		global $cookie;
		$results = array();
		$manufacturers = Manufacturer::getManufacturers(false, $cookie->id_lang);
		foreach($manufacturers as $_manufacturer)
		{
			$manufacturer = new Manufacturer(intVal($_manufacturer['id_manufacturer']));
			$title = $manufacturer->name;
  
			if (intval(Configuration::get('PS_REWRITING_SETTINGS')))
				$manufacturer->link_rewrite = Tools::link_rewrite($title, false);
			else
				$manufacturer->link_rewrite = 0;
  
			$_link = new Link;
			$link = $_link->getManufacturerLink($manufacturer->id, $manufacturer->link_rewrite);
			$results[] = array(
				'id' => $manufacturer->id,
				'type' => 'manufacturer',
				'title' => $title,
				'link' => $link,
				'childrens' => array(),
			);
		}
  
		return $results;
	}

	public static function getSuppliers()
	{
		global $cookie;
		$results = array();
		$suppliers = Supplier::getSuppliers(false, $cookie->id_lang);
		foreach($suppliers as $_supplier) {
			$supplier = new Supplier(intVal($_supplier['id_supplier']));
			$title = $supplier->name;
			if (intval(Configuration::get('PS_REWRITING_SETTINGS')))
				$supplier->link_rewrite = Tools::link_rewrite($title, false);
			else
				$supplier->link_rewrite = 0;

			$_link = new Link;
			$link = $_link->getSupplierLink($supplier->id, $supplier->link_rewrite);
			$results[] = array(
				'id' => $supplier->id,
				'type' => 'supplier',
				'title' => $title,
				'link' => $link,
				'childrens' => array(),
			);
		}

		return $results;
	}

	// --- TOOLS ---
	public static function getModulePath($dir = null)
	{
		return _PS_MODULE_DIR_ . 'menus' . DIRECTORY_SEPARATOR .
			(!is_null($dir) ? $dir . DIRECTORY_SEPARATOR : '');
	}

	// Cache
	public static function setCache($data, $id_lang)
	{
		if (self::cacheIsWritable())
		{
			file_put_contents(self::getModulePath('cache') . 'menu.' . $id_lang, serialize($data));
			Configuration::updateValue('MENU_CACHE_LATEST', array($id_lang => time()));
		}
	}

	public static function getCache($id_lang)
	{
		$data = file_get_contents(self::getModulePath('cache') . 'menu.' . $id_lang);
		return unserialize($data);
	}

	public static function isCached($id_lang)
	{
		if (Configuration::get('MENU_CACHE_ENABLE') == 0 || !self::cacheIsWritable())
			return false;
  
		$now = (int) time();
		$refresh = (int) Configuration::get('MENU_CACHE_REFRESH');
		$latest = (int) Configuration::get('MENU_CACHE_LATEST', $id_lang);
  
		return $now <= ($latest + $refresh);
	}

	public static function cacheIsWritable()
	{
		return self::is_writable(self::getModulePath('cache'));
	}

	public static function is_writable($path)
	{
		if ($path{strlen($path)-1} == '/')
			return self::is_writable($path.uniqid(mt_rand()).'.tmp');
		else if (is_dir($path))
			return self::is_writable($path.'/'.uniqid(mt_rand()).'.tmp');
	
		$rm = file_exists($path);
		$f = @fopen($path, 'a');
		if ($f===false)
			return false;
		fclose($f);
		if (!$rm)
			unlink($path);
	
		return true;
	}
}
?>