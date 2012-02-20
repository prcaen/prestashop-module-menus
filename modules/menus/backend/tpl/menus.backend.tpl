<form action="{$action}" method="post" enctype="multipart/form-data">
	<fieldset style="margin-bottom: 1em">
		<legend><img src="{$module_dir}/logo.gif" />{l s='Menus' mod='menus'}</legend>
		{foreach from=$configs item=config name=configLoop}
			<p style="margin-top:15px">
			{if $config.type == 'boolean'}
				<label>{$config.title}</label>
				<input type="radio" name="{$config.id}" id="{$config.title}_yes" value="1" {if $config.value == 1}checked="checked"{/if} />
				<label for="{$config.id}_yes" class="t"><img src="../img/admin/enabled.gif" alt="{l s='Enabled' mod='menus'}" title="{l s='Enabled' mod='menus'}"></label>
				<input type="radio" name="{$config.id}" id="{$config.id}_no" value="0" {if $config.value == 0}checked="checked"{/if} />
				<label for="{$config.id}_no" class="t"><img src="../img/admin/disabled.gif" alt="{l s='Disabled' mod='menus'}" title="{l s='Disabled' mod='menus'}"></label>
			{/if}
			</p>
		{/foreach}
	</fieldset>
</form>