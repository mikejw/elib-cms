
<form action="" method="get">
<fieldset>
<legend>Add Data</legend>
<p>
<label>Data Type</label>
<br />

<span class="radios">
{html_radios name="data_type" options=$data_types separator="<br /> "}
</span>


</p>
<p id="containers" class="hidden">
<label>Container Type</label>
<select name="container_type">
{html_options options=$container_types class="form-control"}
</select>      
</p>
<p>
<label>&nbsp;</label>
<input type="hidden" name="id" value="{if $event eq 'data_add_data'}{$data_item->id}{else}{$section_item->id}{/if}" />
<button class="btn btn-sm btn-primary" type="submit" name="add">Add</button> 
<button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
</p>
</fieldset>
</form>