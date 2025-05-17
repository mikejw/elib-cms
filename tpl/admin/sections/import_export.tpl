
{if $event eq 'export_section'}

<form action="" method="post">
    <fieldset>
        <legend>Export Section</legend>
        <p>
            <label>Target ID:</label>
            <input class="form-control" type="text" name="target_id" value="{$target_id}" />
        </p>
        <p>
            <button class="btn btn-sm btn-primary" type="submit" name="submit">Export</button>
        </p>
        {if $output neq ''}
        <textarea rows="20" class="raw form-control">{$output}</textarea>
        {/if}
    </fieldset>
</form>

{elseif $event eq 'import_section'}

    <form action="" method="post">
        <fieldset>
            <legend>Import Section</legend>
            <p>
                <label>Parent ID:</label>
                <input class="form-control" type="text" name="parent_id" value="{$parent_id}" />
            </p>
            <p>
                <label>Sections Data</label>
                <textarea name="content" rows="20" class="raw form-control">{$content}</textarea>
            </p>
            <p>
                <button class="btn btn-sm btn-primary" type="submit" name="submit">Import</button>
            </p>
        </fieldset>
    </form>

{/if}