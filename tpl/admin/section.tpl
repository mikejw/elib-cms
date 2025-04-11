{include file="elib:/admin/admin_header.tpl"}


<div class="form-group cms-actions">
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/containers" class="btn btn-sm btn-primary{if $event eq 'edit_containers'} disabled{/if}">
    Containers</a>


    <a class="btn btn-sm btn-primary{if $event eq 'edit_image_sizes'} disabled{/if}" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/image_sizes">Image Sizes</a>


    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/add_section/{$section_id}"
     class="btn btn-sm btn-primary{if $class eq 'data_item' or ($class eq 'dsection' && $event eq 'data_item')} disabled{/if}">Add Section</a>


    {if $class eq 'dsection' && $event neq 'data_item'}

    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/add_data/{$section_id}"
     class="btn btn-sm btn-primary{if $event eq 'add_data'} disabled{/if}">Add Data</a>

    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/delete/{$section_id}"
     class="confirm btn btn-sm btn-primary{if $section_id eq 0} disabled{/if}">Delete</a>

    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/rename/{$section_id}"
     class="btn btn-sm btn-primary{if $section->id eq 0 || $event eq 'rename'} disabled{/if}">Rename</a>

    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/export_section/{$section_id}"
       class="btn btn-sm btn-primary">Export</a>

    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/import_section/{$section_id}"
       class="btn btn-sm btn-primary">Import</a>

        {if $section_id > 0}        
            <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/import_container/{$section_id}?section=true"
            class="btn btn-sm btn-primary">Import Data</a>
         {/if}

    {else}

    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/data_add_data/{$data_item_id}"
     class="btn btn-sm btn-primary{if $event eq 'add_data' || $event eq 'data_add_data' || !$is_container} disabled{/if}">Add Data</a>

    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/delete_data_item/{$data_item_id}"
     class="confirm btn btn-sm btn-primary">Delete</a>

    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/rename_data_item/{$data_item_id}"
     class="btn btn-sm btn-primary{if $event eq 'rename'} disabled{/if}">Rename</a>

      {if $is_container}

        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/export_container/{$data_item_id}"
             class="btn btn-sm btn-primary">Export</a>

        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/import_container/{$data_item_id}"
             class="btn btn-sm btn-primary">Import</a>
      {/if}

    {/if}


    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/change_template/{$section_id}"
     class="btn btn-sm btn-primary{if $class eq 'data_item' || $event eq 'change_template' || $section_id eq 0} disabled{/if}">Change Template</a>

    {if $event eq 'default_event'|| $event eq 'edit_section_item_meta'}
     <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/edit_section_item_meta/{$section_id}"
     class="btn btn-sm btn-primary{if $event eq 'edit_section_item_meta'} disabled{/if}">Edit Meta</a>
    {elseif $event eq 'data_item' || $event eq 'edit_meta'}
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/edit_data_item_meta/{$data_item_id}"
     class="btn btn-sm btn-primary{if $event eq 'edit_meta'} disabled{/if}">Edit Meta</a>
    {/if}

    {if $event eq 'data_item'}

    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/data_item_toggle_hidden/{$data_item_id}"
     class="btn btn-sm btn-primary">{if $data_item->hidden}Show{else}Hide{/if}</a>

    {elseif $class eq 'dsection' && $section_id > 0}
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/toggle_hidden/{$section_id}"
     class="btn btn-sm btn-primary">{if $section->hidden}Show{else}Hide{/if}</a>
    {/if}

</div>


<div class="row">

<div class="col-md-5">

{if $section_id != 0 || $data_item_id != 0}
<p><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/0">Top Level</a></p>
{else}
    <p>&nbsp;</p>
{/if}

{$sections}

</div>

<div class="col-md-7">


{if $event eq 'rename'}
{include file="elib:/admin/sections/rename_section.tpl"}
{elseif $event eq 'rename_data_item'}
{include file="elib:/admin/sections/rename_data_item.tpl"}

{elseif $event eq 'add_data' || $event eq 'data_add_data'}

{include file="elib:/admin/sections/add_data.tpl"}

{elseif $event eq 'add_data_heading' || $event eq 'data_add_data_heading'}
{include file="elib:/admin/sections/add_data_heading.tpl"}
{elseif $event eq 'add_data_body' || $event eq 'data_add_data_body'}
{include file="elib:/admin/sections/add_data_body.tpl"}
{elseif $event eq 'add_data_image' || $event eq 'data_add_data_image'}
{include file="elib:/admin/sections/add_data_image.tpl"}
{elseif $event eq 'add_data_audio'|| $event eq 'data_add_data_audio'}
{include file="elib:/admin/sections/add_data_audio.tpl"}
{elseif $event eq 'add_data_video' || $event eq 'data_add_data_video'}
{include file="elib:/admin/sections/add_data_video.tpl"}
{elseif $event eq 'data_item'}
{include file="elib:/admin/sections/data_item.tpl"}
{elseif $event eq 'edit_heading'}
{include file="elib:/admin/sections/edit_heading.tpl"}
{elseif $event eq 'edit_body'}
{include file="elib:/admin/sections/edit_body.tpl"}
{elseif $event eq 'change_template'}
{include file="elib:/admin/sections/change_template.tpl"}
{elseif $event eq 'edit_meta'}
{include file="elib:/admin/sections/edit_meta.tpl"}
{elseif $event eq 'edit_section_item_meta'}
{include file="elib:/admin/sections/edit_section_item_meta.tpl"}
{elseif $event eq 'edit_containers'}
{include file="elib:/admin/sections/edit_containers.tpl"}
{elseif $event eq 'export_section'}
{include file="elib:/admin/sections/import_export.tpl"}
{elseif $event eq 'import_section'}
{include file="elib:/admin/sections/import_export.tpl"}

{elseif $event eq 'export_container'}
{include file="elib:/admin/sections/import_export_container.tpl"}
{elseif $event eq 'import_container'}
{include file="elib:/admin/sections/import_export_container.tpl"}

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


</div>


{include file="elib:/admin/admin_footer.tpl"}
