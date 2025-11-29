
<form action="" method="post">
    <fieldset>
        <legend><h2>Add Body Text</h2></legend>
        <div class="mb-3">
            <label class="form-label">Body</label>
            <textarea name="body" rows="" cols="">{$data_item->body|escape}</textarea>
        </div>
        <div class="mb-3">
            <input type="hidden" name="id" value="{$data_item->id}"/>
            <button class="btn btn-sm btn-primary" type="submit" name="save">Submit</button>
            <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
        </div>
    </fieldset>
</form>