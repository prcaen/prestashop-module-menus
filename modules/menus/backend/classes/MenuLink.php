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
}
?>