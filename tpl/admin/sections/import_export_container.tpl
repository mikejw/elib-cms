
{if $event eq 'export_container'}

<form action="" method="post">
    <fieldset>
        <legend><h2>Export Data Item Container</h2></legend>
        <div class="mb-3">
            <label class="form-label">Target ID:</label>
            <input class="form-control" type="text" name="target_id" value="{$target_id}" />
        </div>
        <div class="mb-3">
            <button class="btn btn-sm btn-primary" type="submit" name="submit">Export</button>
        </div>
        {if $output neq ''}
        <div class="mb-3">
            <textarea rows="20" class="raw form-control">{$output}</textarea>
        </div>
        {/if}
    </fieldset>
</form>

{elseif $event eq 'import_container'}

    <form action="" method="post">
        <fieldset>
            <legend><h2>Import Data Item Container</h2></legend>
            <div class="mb-3">
                <label class="form-label">Parent ID:</label>
                <input class="form-control" type="text" name="parent_id" value="{$parent_id}" />
            </div>
            <div class="mb-3">
                <label class="form-label">Sections Data</label>
                <textarea name="content" rows="20" class="raw form-control">{$content}</textarea>
            </div>
            <div class="mb-3">
                <button class="btn btn-sm btn-primary" type="submit" name="submit">Import</button>
            </div>
        </fieldset>
    </form>
{/if}
