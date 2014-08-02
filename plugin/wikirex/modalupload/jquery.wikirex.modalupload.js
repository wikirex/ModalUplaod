/*

jquery.wikirex.modalupload Plugin
Author: Rex Lo wikirexlo@gmail.com
Version: 1.0
Updated: 2014-8-2

*/


(function( $ ){
	$.fn.modalupload = function( method ) {
		var methods = $.fn.modalupload.methods;
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
		}    
	
	};

	$.fn.modalupload.defaults = {
		data_url: 'upload.php', 
		data_params: {}, 
		preview: true, 
		modal_id: 'modalupload_win', 
		upload_field_name: 'mupload_file', 
		versions: {
			'photo': [
				{
					folder: 'uploads/demo/thumbs/', 
					width: 100, 
					height: 100, 
					max_width: 500, 
					max_height: 500, 
					root: true, 
					crop: true
				}, 
				{
					folder: 'uploads/demo/', 
					width: 300, 
					height: 200, 
					max_width: 800, 
					max_height: 800, 
					root: true, 
					crop: true
				}
			]
		}
		on_preview: function($wrapper){}, 
		on_click: function($wrapper, $item, index){}, 
		loaded: function(event){}
	};


  $.fn.modalupload.methods = {
	init : function( options ) { 
		var settings = $.extend({}, $.fn.modalupload.defaults, options || {});

		return this.each(function(){
			var wrap = this;
			var $win = get_win(settings.modal_id);
			var $form = $win.find('form');
			var $bt_submit = $win.find('.bt_submit');

			$('input.modal_upload').each(function(index){
		    	var $this = $(this);
		    	var versions = settings.versions;
		    	var field_versions = versions[$this.attr('id')];

		    	//Add versions to each field
		    	$this.attr('data-version', json2str(field_versions));

		    	$this.on('click', function(){
		    		$win.data().source_item = $(this);
		    		$form.find('input[name="versions"]').val(versions);
					$win.modal();
				});

				var $thumb = $('<img class="photo_thumb" src="" />').insertAfter($this);
		        show_thumb($this, $thumb);

		        $this.focusout(function(){
		            show_thumb($this, $thumb);
		        });
		    });

			$form.submit(function() { 
				$bt_submit.text('Uploading...').attr('disabled', true);

		        $(this).ajaxSubmit({
					beforeSubmit: function(arr, $form, options) {
						for(var i = 0; i < arr.length; i++){
							var obj = arr[i];
							//console.log(obj);

							if(obj.required == true && (obj.value == null || obj.value == '')){
								//alert('Please enter all required fields');
								//return false;
							}
						}

						//$('#bt_submit').text('Processing...').attr('disabled', true);
						return true;
					}, 
					data: {
						
					}, 
					success: function(data, statusText, xhr, $form) { 
						//console.log(data);
						//return;

						$bt_submit.text('Upload').attr('disabled', false);

						if(data.status == 'success'){
							$source_item.val(data.data);
							wrap.hide();
							$form[0].reset();
						}else{
							alert(data.err);
						}
					}, 
					error: function(){
						alert("ERROR");
					}
				}); 
		 
		        return false; 
		    });

			$bt_submit.on('click', function(e){
				$form.submit();
			});

			init_listeners();
			set_style();
			callback();

		});//End main loop

		function get_win(modal_id){
			var $win = $('#' + modal_id);
			if($win.size() == 0){
				var $win = $('<div class="modal fade" id="win_upload" tabindex="-1" role="dialog" aria-labelledby="upload_title" aria-hidden="true"></div>').appendTo("body");
				var $dialog = $('<div class="modal-dialog"></div>').appendTo($win);
				var $content = $('<div class="modal-content"></div>').appendTo($dialog);

				var $header = $('<div class="modal-header"></div>').appendTo($content);
				$header.append('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>');
				$header.append('<h4 class="modal-title" id="upload_title">Update File</h4>');

				$content.append(
					'<div class="modal-body">
				      	<form name="upload_form" action="upload.php" method="POST" class="form-horizontal form-bordered" enctype="multipart/form-data">
						    <div class="form-group">
						    	<label for="' + settings.upload_field_name + '" class="col-sm-2 control-label">File</label>
				    		    <div class="col-sm-10">
				    		    	<input type="file" class="form-control" id="' + settings.upload_field_name + '" name="' + settings.upload_field_name + '" placeholder="File" />
				    		    	<input type="hidden" name="versions" value="" />
				    			</div>
						    </div>
						</form>
				    </div>'
				);

				$content.append(
					'<div class="modal-footer">
				        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				    	<button type="button" class="btn btn-primary bt_submit">Upload</button>
				    </div>'
				);
			}

			return $win;
		}

		function json2str(json){
			var str = JSON.stringify(json);
			var pattern = /"/g;
			var str = str.replace(pattern, '&quot;');
			return str;
		}

		function show_thumb(){
			var wrap = this;
		    var value = $field.val();

		    if(value != '') {
		        var file_name = wrap.get_file_name(value);
		        var dir_url = wrap.get_dir(value);
		        console.log(dir_url);
		        var url = dir_url + 'thumb/' + file_name;
		        $thumb.attr('src', url);
		    }
		}

		function get_dir(url){
			var pattern = /(.+\/).+\.\w{3}$/i;
			var result = url.match(pattern);

			if(result != null){
				return result[1];
			}else{
				return '';
			}
		}

		function get_file_name(url){
			var pattern = /.+\/(.+\.\w{3})$/i;
			var result = url.match(pattern);

			if(result != null){
				return result[1];
			}else{
				return '';
			}
		}
    }
  };



})( jQuery );