

<form action="" method="post">
    <fieldset>
        <legend><h2>Edit Meta Text</h2></legend>
        <div class="mb-3">
            <label class="form-label">Meta Text</label>
            {*<textarea name="meta" rows="" cols="">{$data_item->meta|replace:'</p><p>':"\r\n"|replace:'<p>':""|replace:'</p>':""}</textarea>*}
            <textarea class="raw form-control" name="meta" rows="10" cols="">{$data_item->meta|escape}</textarea>
        </div>
        <div class="mb-3">
            <input type="hidden" name="id" value="{$data_item->id}"/>
            <button class="btn btn-sm btn-primary" type="submit" name="save">Save</button>
            <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
        </div>
    </fieldset>
</form>