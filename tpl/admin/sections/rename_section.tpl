
<form action="" method="post">
<fieldset>
<legend>Rename Section</legend>
<p>
<label>Label</label>
<input class="form-control" type="text" name="label" value="{$section->label}" />
</p>
<p>
<label>&nbsp;</label>
<input type="hidden" name="id" value="{$section->id}" />
<button class="btn btn-sm btn-primary" type="submit" name="save">Save</button> 
<button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
</p>
</fieldset>
</form>