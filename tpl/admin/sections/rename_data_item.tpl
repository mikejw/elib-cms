
<form action="" method="post">
    <fieldset>
        <legend><h2>Rename Data Item</h2></legend>
        <div class="mb-3">
            <label class="form-label">Label</label>
            <input class="form-control" type="text" name="label" value="{$data_item->label}"/>
        </div>
        <div class="mb-3">
            <input type="hidden" name="id" value="{$data_item->id}"/>
            <button class="btn btn-sm btn-primary" type="submit" name="save">Save</button>
            <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
        </div>
    </fieldset>
</form>
