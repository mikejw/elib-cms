

{if $error neq ''}
    <ul id="error">
        <li>{$error}</li>
    </ul>
{/if}

<form action="" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend>Add Audio File</legend>
        <p>
            <label>File</label>
            <input type="file" id="file" name="file" accept="audio/*" />
            <!-- <input type="file" id="file" name="file" /> -->
        </p>
        <p>
            <label>&nbsp;</label>
            <input type="hidden" name="id" value="{if $class eq 'data_item'}{$data_item->id}{else}{$section_id}{/if}" />
            <button class="btn btn-sm btn-primary" type="submit" name="save">Submit</button>
            <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
        </p>
    </fieldset>
</form>