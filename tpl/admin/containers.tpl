{include file="elib:admin/admin_header.tpl"}


{if $event neq 'rename_container'}
    <div class="mb-4 mt-4">
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/add_container"
           class="btn btn-sm btn-primary">Add</a>
    </div>
{/if}


{if isset($errors)}
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Error</strong>
        {foreach from=$errors item=e}
            <p>{$e}</p>
        {/foreach}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

{if $event eq 'rename_container'}
    <fieldset class="mt-4 mb-4">
        <legend>Rename Container</legend>
        <form action="" method="post">
            <div class="mb-4">
                <label class="form-label">Name</label>
                <input class="form-control" type="text" value="{$container->name}" name="name"/>
            </div>
            <div class="mb-4">
                <button class="btn btn-sm btn-primary" type="submit" name="save">Save</button>
                <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
            </div>
        </form>
    </fieldset>
{else}
    <div id="properties">
        <form action="" method="post">
            {foreach from=$containers key=id item=container}
                <fieldset class="mb-4">
                    <legend><h1>{$container.name}</h1></legend>

                    <p class="f_actions">
                        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/rename_container/{$id}">Rename</a> |
                        <a class="confirm" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/remove_container/{$id}">Remove</a>
                    </p>


                    <h2 class="mb-4">Image Size:</h2>

                    {if count($image_sizes) < 1}
                        <p>No image size options in system.</p>
                    {else}

                        <div class="mb-4">
                            <div class="form-check">
                                <label class="form-check-label">
                                {html_checkboxes
                                    labels=false
                                    name="image_size[$id]"
                                    options=$image_sizes separator="<br />"
                                    selected=$container.image_size_ids
                                    class="form-check-input"
                                    separator='</label></div><div class="form-check"><label class="form-check-label">'
                                  }
                                </label>
                            </div>
                        </div>
                    {/if}



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

            <div class="mb-4">
                <button class="btn btn-sm btn-primary" type="submit" name="save">Save</button>
                <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
            </div>
        </form>
    </div>
{/if}


{include file="elib:admin/admin_footer.tpl"}


