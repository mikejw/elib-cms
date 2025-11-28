

{if $errors neq ''}
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Error</strong>
        <p>{$error}</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<form action="" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend><h2>Add Image</h2></legend>
        <div class="mb-3">
            <label class="form-label" for="file">File</label>
            <input type="file" id="file" name="file[]" multiple="multiple" accept="image/*" class="form-control" />
            <!-- <input type="file" id="file" name="file" /> -->
        </div>
        <div class="mb-3">
            <input type="hidden" name="id" value="{if $class eq 'data_item'}{$data_item->id}{else}{$section_id}{/if}"/>
            <button class="btn btn-sm btn-primary" type="submit" name="save">Submit</button>
            <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
        </div>
    </fieldset>
</form>

