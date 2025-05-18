

<div id="data_item">
{if $data_item->heading neq ''}

<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/edit_heading/{$data_item->id}"
 class="btn btn-sm btn-primary">Edit</a>
<p>&nbsp;</p>
<h3>{$data_item->heading}</h3>



{elseif $data_item->body neq ''}

<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/edit_body/{$data_item->id}"
 class="btn btn-sm btn-primary">Edit</a>
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/dsection/edit_body_raw/{$data_item->id}"
 class="btn btn-sm btn-primary">Edit Raw</a>
<p>&nbsp;</p>

{$data_item->body}
    
{elseif $data_item->image neq ''}
<img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/uploads/{$image_prefix}_{$data_item->image}" alt="" />

{elseif $data_item->video neq ''}
<img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/uploads/tn_{$data_item->video}.jpg" alt="" />
<p><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/data_item/generate_thumb/{$data_item->id}">Generate New Thumbnail</a></p>
<p>(You may need to refresh your browser before seeing new thumbnails.)</p>

{elseif $data_item->audio neq ''}
<audio controls>
    <source src="http://{$WEB_ROOT}{$PUBLIC_DIR}/uploads/{$data_item->audio}" type="audio/mpeg">
    Your browser does not support the audio tag.
</audio>
<p><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/uploads/{$data_item->audio}">Download file</a></p>
    
{/if}

</div>