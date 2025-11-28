
<form action="" method="get">
    <fieldset>
        <legend><h2>Add Data</h2></legend>
        <h4 class="mb-3">Data Type</h4>

        <div class="radios">
            <div class="form-check ms-3">
                <label class="form-check-label">
                    {html_radios
                        labels=false
                        name="data_type"
                        options=$data_types
                        class="form-check-input"
                        separator='</label></div><div class="form-check ms-3"><label class="form-check-label">'
                    }
                </label>
            </div>
        </div>

        <div id="containers" class="hidden mb-3">
            <label class="form-label">Container Type</label>
            <select name="container_type" class="form-control">
                {html_options options=$container_types }
            </select>
        </div>
        <div class="mb-3">
            <input type="hidden" name="id"
                   value="{if $event eq 'data_add_data'}{$data_item->id}{else}{$section_item->id}{/if}"/>
            <button class="btn btn-sm btn-primary" type="submit" name="add">Add</button>
            <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
        </div>
    </fieldset>
</form>