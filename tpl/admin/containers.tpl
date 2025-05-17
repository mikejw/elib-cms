{include file="elib:/admin/admin_header.tpl"}


{if $event neq 'rename_container'}


<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/add_container"
 class="btn btn-sm btn-primary">Add</a>


<p style="line-height: 0.5em;">&nbsp;</p>
{/if}


  {if isset($errors)}
	<div class="alert alert-danger alert-dismissible" role="alert">
  	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  	<strong>Error!</strong>
  		{foreach from=$errors item=e} 
  			<p>{$e}</p>
  		{/foreach}
	</div>
  {/if}

{if $event eq 'rename_container'}
<fieldset><legend>Rename Container</legend>
<form action="" method="post">
<p><label>Name</label>
<input class="form-control" type="text" value="{$container->name}" name="name" />
</p>
<p><label>&nbsp;</label>
<button class="btn btn-sm btn-primary" type="submit" name="save">Save</button> 
<button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
</p>
</form>
</fieldset>

{else}
<div id="properties">

<form action="" method="post">

{foreach from=$containers key=id item=container}

<fieldset><legend>{$container.name}</legend>

<p class="f_actions">
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/rename_container/{$id}">Rename</a> |
<a class="confirm" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/remove_container/{$id}">Remove</a></p>



<p class="clear">
<label>Image Size:</label>
</p>

<p>
<span class="radios">
{html_checkboxes name="image_size[$id]" options=$image_sizes separator="<br />" selected=$container.image_size_ids}
</span>
</p>


{*
{if sizeof($container.image_sizes) > 0}
<p>
<label>&nbsp;</label>
<span>
<table class="inner">
{foreach from=$container.image_sizes item=image_size key=image_size_id}
<tr>
<td>{$image_size}</td>
<td><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/containers/remove_size/{$id}?size={$image_size_id}">Remove</a></td>
</tr>
{/foreach}
</table>
</span>
</p>
{/if}
*}


{*
{if sizeof($container.available_image_sizes) > 0}
<p><label>&nbsp;</label>
<select name="image_size">
{html_options options=$container.available_image_sizes}
</select>

<input type="text" value="{if $submitted_option->property_id eq $id}{$submitted_option->option_val}{/if}" name="option" />
<input type="hidden" name="id" value="{$id}" />
<button type="submit" name="add_option">Add</button>
</p>
{/if}
*}



</fieldset>
{/foreach}

<p>
<button class="btn btn-sm btn-primary" type="submit" name="save">Save</button> 
<button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
</p>
</form>
</div>

{/if}


<p class="clear">&nbsp;</p>






{include file="elib:/admin/admin_footer.tpl"}


