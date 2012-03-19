{if !$link.logged || ($link.logged && $module_menus.logged)}
<li class="{$link.css_class}{if ($link.type eq $page_name && $link.id_menu_link eq $menu.id)} active{/if}" {if $link.css_id} id="{$link.css_id}"{/if}>
	<a href="{$link.link|escape:htmlall:'UTF-8'}" title="{$link.title|escape:htmlall:'UTF-8'}">{$link.title|escape:htmlall:'UTF-8'}</a>
	{if $link.childrens|@count > 0}
		<ul>
		{assign var='childrens' value=$link.childrens}
		{foreach from=$childrens item=link name=menuTreeChildrens}
			{include file=$menu_tpl_tree}
		{/foreach}
		</ul>
	{/if}
</li>
{/if}