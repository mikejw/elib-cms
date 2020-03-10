

<form action="" method="post">
<fieldset>
<legend>Edit Meta Text</legend>
<p>
<label>Meta Text</label>
{*<textarea name="meta" rows="" cols="">{$data_item->meta|replace:'</p><p>':"\r\n"|replace:'<p>':""|replace:'</p>':""}</textarea>*}
<textarea class="raw form-control" name="meta" rows="10" cols="">{$data_item->meta|escape}</textarea>
</p>
<p>
<label>&nbsp;</label>
<input type="hidden" name="id" value="{$data_item->id}" />
<button class="btn btn-sm btn-primary" type="submit" name="save">Save</button>
<button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
</p>
</fieldset>
</form>