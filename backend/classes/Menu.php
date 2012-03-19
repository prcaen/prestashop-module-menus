<?php
class Menu extends ObjectModel
{
	public $id;
	public $id_hook = 0;
	public $logged = 0;
	public $css_id;
	public $css_class;
	public $active;
	
	/** @var string Object creation date */
	public $date_add;

	/** @var string Object last modification date */
	public $date_upd;

	protected $fieldsRequired = array('active', 'id_hook');
	protected $fieldsSize = array('id_hook' => 10, 'css_id' => 32, 'css_class' => 32, 'active' => 1, 'logged' => 1);
	protected $fieldsValidate = array('id_hook' => 'isUnsignedId', 'active' => 'isBool', 'logged' => 'isBool', 'css_id' => 'isGenericName' , 'css_class' => 'isGenericName');

	protected $table = 'menu';
	protected $identifier = 'id_menu';
	
	public function getFields()
	{
		parent::validateFields();

		if (isset($this->id))
			$fields['id_menu'] = (int)($this->id);

		$fields['id_hook']	 = (int)($this->id_hook);
		$fields['logged']	 	 = (int)($this->logged);
		$fields['css_id']		 = (string)($this->css_id);
		$fields['css_class'] = (string)($this->css_class);
		$fields['active']		 = (int)($this->active);
		$fields['date_add']	 = pSQL($this->date_add);
		$fields['date_upd']	 = pSQL($this->date_upd);
		return $fields;
	}

	public static function getMenuFromIdHook($id_hook)
	{
		$sql = 'SELECT m.`id_menu` FROM `' . _DB_PREFIX_ . 'menu` m WHERE m.`id_hook` = '. (int)$id_hook;
		$id_menu = Db::getInstance()->getValue($sql);

		return new Menu($id_menu);
	}
}
?>