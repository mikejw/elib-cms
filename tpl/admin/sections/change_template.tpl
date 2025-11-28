
<form action="" method="post">
    <fieldset>
        <legend><h2>Change Template</h2></legend>
        <div class="mb-3">
            <label class="form-label">Template</label>
            <select class="form-control" name="template">
                {html_options options=$templates selected=$section->template}
            </select>
        </div>
        <div class="mb-3">
            <input type="hidden" name="id" value="{$section->id}"/>
            <button class="btn btn-sm btn-primary" type="submit" name="save">Save</button>
            <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
        </div>
    </fieldset>
</form>