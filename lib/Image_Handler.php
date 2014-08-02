<?php
class Image_Handler{
    private $options; 
	private $handle, $log, $show_log = true;
	public $err = '', $err_code = 0;
	public $root_dir = '';
	public $root_url = '';
    
    function __construct($options=null) {
		$this->root_dir = dirname(dirname(dirname(__FILE__))) . '/uploads';
		$this->root_url = dirname(dirname(dirname($_SERVER['PHP_SELF']))) . '/uploads';

		if(file_exists($this->root_dir) == false){
			@mkdir($this->root_dir);
		}

		$thumb_root = $this->root_dir .'/thumbnails/';
		if(file_exists($thumb_root) == false){
			@mkdir($thumb_root);
		}

        $this->options = array(
            //'script_url' => $_SERVER['PHP_SELF'],
            //'upload_dir' => dirname(__FILE__).'/upload/',
            //'upload_url' => dirname($_SERVER['PHP_SELF']).'/upload/',
            //'param_name' => 'files',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            'accept_file_types' => '/.+(jpg|jpeg|gif|bmp|png|pdf)$/i',
            'max_number_of_files' => null,
            'discard_aborted_uploads' => true,
			//'crop_image' => false, //Added by Rex, default is false. For function create_cropped_image()
            'image_versions' => array(
                // Uncomment the following version to restrict the size of
                // uploaded images. You can also add additional versions with
                // their own upload directories:
                /*
                'large' => array(
                    'upload_dir' => dirname(__FILE__).'/files/',
                    'upload_url' => dirname($_SERVER['PHP_SELF']).'/files/',
                    'max_width' => 1920,
                    'max_height' => 1200
                ),
                */
                'thumbnail' => array(
                    'upload_dir' => $this->root_dir . '/thumbnails/',
                    'upload_url' => $this->root_url . '/thumbnails/',
                    'max_width' => 80,
                    'max_height' => 80, 
					'crop' => 'true'
                )
            )
        );
        if ($options) {
            $this->options = array_merge_recursive($this->options, $options);

			//$handle = fopen("test_log.txt", "w");
			//fwrite($handle, json_encode($this->options['image_versions']));
			//exit();
        }

    }

	public function __destruct(){

	}

	public function get_unique_file_name($filename){
		$new_name = uniqid('', true);
		$new_name = preg_replace('/\./', '_', $new_name);
		return $new_name . '.' . $this->get_extension($filename);
	}

	public function get_file_name($filename){
		$path_parts = pathinfo($filename);
		return $path_parts['basename'];
	}

	public function get_extension($filename){
		$path_parts = pathinfo($filename);
		return strtolower($path_parts['extension']);
	}

	private function get_image($file){
        switch ($this->get_extension($file)) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg($file);
               // $write_image = 'imagejpeg';
                break;
            case 'gif':
                $image = @imagecreatefromgif($file);
                //$write_image = 'imagegif';
                break;
            case 'png':
                $image = @imagecreatefrompng($file);
               // $write_image = 'imagepng';
                break;
            default:
                $image = null;
        }

		return $image;
	}

	private function write_image($image, $file_path, $output_to_browser=false){
        switch ($this->get_extension($file_path)) {
            case 'jpg':
            case 'jpeg':
            	$write_image = 'imagejpeg';
                break;
            case 'gif':
                $write_image = 'imagegif';
                break;
            case 'png':
                $write_image = 'imagepng';
                break;
            default:
                $write_image = 'imagejpeg';
        }

		if($write_image == 'imagepng'){
			if($output_to_browser){
				header('Content-Type: image/png');
				imagepng($image);
			}else{
				return imagepng($image, $file_path, 0);
			}
		}else if($write_image == 'imagegif'){
			if($output_to_browser){
				header('Content-Type: image/gif');
				imagepng($image);
			}else{
				return imagegif($image, $file_path);
			}
		}else{
			if($output_to_browser){
				header('Content-Type: image/jpg');
				imagepng($image);
			}else{
				return imagejpeg($image, $file_path, 100);
			}
		}
	}

    public function crop_image($file_path, $options) {
/*
		$options = array(
			'upload_dir' => $this->root_dir . '/thumbnails/',
			'upload_url' => $this->root_url . '/thumbnails/',
			'max_width' => 80,
			'max_height' => 80, 
			'crop' => 'true'
		);
*/

		$file_name = $this->get_file_name($file_path);
        $new_file_path = $options['upload_dir'] . $file_name;

        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height) {
            return false;
        }

		//Get settings of new image
        $new_width = intval($options['max_width']);
        $new_height = intval($options['max_height']);

		//Because defined width or height could be bigger than original file
		//So if original image is small, don't need to enlarge it.
		if($new_width > $img_width && $new_height > $img_height){
			$new_width = $img_width;
			$new_height = $img_height;
		}

        $new_img = @imagecreatetruecolor($new_width, $new_height);
		imagealphablending($new_img, false);
		imagesavealpha($new_img, true);  

		$src_img = $this->get_image($file_path);

		//Added by Rex to crop image by setting X and Y position of the source image
		$srcX = 0;
		$srcY = 0;
		$srcRatio = $img_width / $img_height;
		$dstRatio = $new_width / $new_height;

		if ($srcRatio > $dstRatio) { 
			// source has a wider ratio, get a new width of source according to $dstRatio
			$tempW = (int)($img_height * $dstRatio); 
			$tempH = $img_height;
			if((int)($img_width - $tempW) > 0){// Source image has bigger width
				$srcX = (int)(($img_width - $tempW) / 2); //Move to center
			}
		} else { 
			// source has a taller ratio 
			$tempW = $img_width; 
			$tempH = (int)($img_width / $dstRatio); 
			if((int)($img_height - $tempH) > 0){
				$srcY = (int)(($img_height - $tempH) / 2); 
			}
		} 

		$srcW = $tempW;
		$srcH = $tempH;

		imagealphablending($src_img, true);
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, $srcX, $srcY,
            $new_width,
            $new_height,
            $srcW,
            $srcH
        ) && $this->write_image($new_img, $new_file_path);


        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);

        return $success;
    }

	public function merge_images($top, $base, $width, $height, $new_file, $output_to_browser=false){
		$base_image = $this->get_image($base);
		$top_image = $this->get_image($top);
		//$merged_image = @imagecreatetruecolor($width, $height);
		
		imagesavealpha($top_image, true);
		imagealphablending($top_image, true);
		
		imagecopy($base_image, $top_image, 0, 0, 0, 0, $width, $height);

		$this->write_image($base_image, $new_file, $output_to_browser);
	}
}
?>