{include file="elib:admin/admin_header.tpl"}


<div class="mb-4 mt-4">
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/add_image_size"
   class="btn btn-sm btn-primary">Add</a>
</div>


{if isset($errors)}
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Error</strong>
        {foreach from=$errors item=e}
            <p>{$e}</p>
        {/foreach}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<div id="image_sizes">
    <form class="mt-4" action="" method="post">
        <table class="table">
            <tr>
                <th>Name</th>
                <th>Prefix</th>
                <th>Max Width</th>
                <th>Max Height</th>
                <th>&nbsp;</th>
            </tr>
            {foreach from=$image_sizes item=i}
                <tr>
                    <td><span id="name_{$i.id}" class="edit_box">{$i.name}</span></td>
                    <td><span id="prefix_{$i.id}" class="edit_box">{$i.prefix}</span></td>
                    <td><span id="width_{$i.id}" class="edit_box">{$i.width}</span></td>
                    <td><span id="height_{$i.id}" class="edit_box">{$i.height}</span></td>
                    <td>
                        <a class="confirm"
                           href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/remove_image_size/{$i.id}">Remove</a> |
                        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/update_image_sizes/{$i.id}">Update</a>
                    </td>
                </tr>
            {/foreach}
        </table>

        {*
        <p>
        <button type="submit" name="save">Save</button>
        <button type="submit" name="cancel">Cancel</button>
        </p>
        *}
    </form>
</div>


{include file="elib:admin/admin_footer.tpl"}


