
{if $event eq 'export_section'}

<form action="" method="post">
    <fieldset>
        <legend>Edit Section</legend>
        <p>
            <label>Target ID:</label>
            <input class="form-control" type="text" name="target_id" value="{$target_id}" />
        </p>
        <p>
            <button class="btn btn-sm btn-primary" type="submit" name="submit">Export</button>
        </p>
        {if $output neq ''}
        <textarea rows="20" class="raw form-control">
            {$output}
        </textarea>
        {/if}
    </fieldset>
</form>

{/if}