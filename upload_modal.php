<!-- Modal -->
<div class="modal fade" id="win_upload" tabindex="-1" role="dialog" aria-labelledby="upload_title" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
        <h4 class="modal-title" id="upload_title">Update File</h4>
      </div>
      <div class="modal-body">
      	<form name="upload_form" action="upload.php" method="POST" class='form-horizontal form-bordered' enctype='multipart/form-data'>
		  <div class="form-group">
		    <label for="upload_file" class="col-sm-2 control-label">File</label>
		    <div class="col-sm-10">
		      <input type="file" class="form-control" id="upload_file" name="upload_file" placeholder="File" />
		      <input type="hidden" name="versions" value="" />
		    </div>
		  </div>
		</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary bt_submit">Upload</button>
      </div>
    </div>
  </div>
</div>