
<form action="" method="post">
    <fieldset>
        <legend><h2>Add Heading</h2></legend>
        <div class="mb-3">
            <label class="form-label">Heading</label>
            <input class="form-control" type="text" name="heading" value="{$data_item->heading}"/>
        </div>
        <div class="mb-3">
            <input type="hidden" name="id" value="{if $class eq 'data_item'}{$data_item->id}{else}{$section_id}{/if}"/>
            <button class="btn btn-sm btn-primary" type="submit" name="save">Submit</button>
            <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
        </div>
    </fieldset>
</form>