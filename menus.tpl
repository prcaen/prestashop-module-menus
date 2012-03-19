{if $menu.links|@count > 0}
	{if !$menu.logged || ($menu.logged && $module_menus.logged)}
		<!-- MODULE MENUS -->
		<ul {if $menu.css_id}id="{$menu.css_id}"{/if} {if $menu.css_class}class="{$menu.css_class}"{/if}>
		<li {if $page_name eq 'index'}class="active"{/if}>
			<a href="{$link->getPageLink('index.php')}" title="{$shop_name|escape:'htmlall':'UTF-8'}">{l s='Home' mod='menus'}</a>
		</li>
		{foreach from=$menu.links item=link name=menuTree}
			{include file=$menu_tpl_tree}
		{/foreach}
		</ul>
		<!-- /MODULE MENUS -->
		{else}
		test 2
	{/if}
{else}
test 1
{/if}