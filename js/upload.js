//Upload modal using jquery.form.js
var upload = {
	init: function(){
		var wrap = this;
		var $win = $('#win_upload');
		var $form = $('#win_upload form');
		var $bt_submit = $win.find('.bt_submit');

		var $thumb = null;
		var $source_item = null;

		$('input.modal_upload').each(function(index){
	    	var $this = $(this);
	    	var versions = get_versions($this.attr('id'), true);
	    	//console.log(versions);
	    	wrap.init_fields($this, versions);

	    	$this.on('click', function(){
	    		$source_item = $(this);
	    		$thumb = $source_item.next();

	    		wrap.set_versions(versions);
				wrap.show();
			});

			$thumb = $('<img class="photo_thumb" src="" />').insertAfter($this);
	        wrap.show_thumb($this, $thumb);

	        $this.focusout(function(){
	            wrap.show_thumb($this, $thumb);
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
						var files = data.data;
						var file = files[0];

						$source_item.val(file.upload_url + file.file_name);
						wrap.hide();
						$form[0].reset();

						wrap.show_thumb($source_item, $thumb);
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
	}, 
	show: function(){
		$('#win_upload').modal();
	}, 
	hide: function(){
		$('#win_upload').modal('hide');
	},
	init_fields: function($field, version_str){
		$field.attr('data-version', version_str);
	}, 
	set_versions: function(versions){
		var wrap = this;
		versions = wrap.json2str(versions);
		$('#win_upload form input[name="versions"]').val(versions);
	}, 
	get_dir: function (url){
		///uploads/cat/icon/normal/glasses_small.jpg
	    var pattern = /(.+\/).+\.\w{3}$/i;
		var result = url.match(pattern);

		if(result != null){
			return result[1];
		}else{
			return '';
		}
	}, 
	get_file_name: function (url){
		///uploads/cat/icon/normal/glasses_small.jpg
	    var pattern = /.+\/(.+\.\w{3})$/i;
		var result = url.match(pattern);

		if(result != null){
			return result[1];
		}else{
			return '';
		}
	}, 
	show_thumb: function ($field, $thumb){
		var wrap = this;
	    var value = $field.val();

	    if(value != '') {
	    	var file_name = wrap.get_file_name(value);

	    	if(base_url != null){
	    		$thumb.attr('src', base_url + 'uploads/thumbnails/' + file_name);
	    	}else{
		        var dir_url = wrap.get_dir(value);
		        //console.log(dir_url);
		        var url = dir_url + 'thumb/' + file_name;
		        $thumb.attr('src', url);
	    	}
	    }
	}, 
	json2str: function(json){
		var str = JSON.stringify(json);
		var pattern = /"/g;
		var str = str.replace(pattern, '&quot;');
		return str;
	}
}