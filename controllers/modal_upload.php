<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH.'libraries/Auth_Page_Controller.php';

class Modal_upload extends Auth_Page_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->helper('url');
		$this->load->helper('string');
		$this->load->helper('form');

		$this->load->model('category_m');
		$this->load->model('pages_m');
		$this->load->model('options_m');

		$this->load->library('Image_handler');
	}

	public function index()
	{
		$versions = $this->input->post('versions');
		if($versions == false){
			$this->failed_response(0, 'No defined image sizes.');
		}else{
			$versions = $this->get_json($versions);
		}

		//Add thumbnail config
		$thumb = array(
			'folder' => 'thumbnails/', 
			'width' => 80, 
			'height' => 80, 
			'max_width' => 500, 
			'max_height' => 500, 
			'root' => true, 
			'crop' => true
		);
		array_push($versions, $thumb);
		//log_g($versions);

		$upload_dir = FCPATH . 'uploads/';
		$upload_url = base_url() . 'uploads/';

		$field = 'mupload_file';
		$file = $_FILES[$field];

		$new_file_name = $file["name"];
		$uploaded_file = $upload_dir . $new_file_name;

		$allowedExts = array("gif", "jpeg", "jpg", "png");
		$allowedTypes = array("image/gif", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png", "image/png");
		$extension = $this->image_handler->get_extension($file["name"]);
		$size_limit = 3*1024*1024; //3MB

		if (in_array($file['type'], $allowedTypes) == false){
			$this->failed_response(0, 'Invalid image type');
		}

		if ($file["size"] > $size_limit){
			$this->failed_response(0, 'Image is too large');
		}

		if (in_array($extension, $allowedExts) == false){
			$this->failed_response(0, 'Invalid file extension');
		}

		if($file["error"] > 0){
			$this->failed_response(0, $file["error"]);
		}

	    /*
	    echo "Upload: " . $file["name"] . "<br>";
	    echo "Type: " . $file["type"] . "<br>";
	    echo "Size: " . ($file["size"] / 1024) . " kB<br>";
	    echo "Temp file: " . $file["tmp_name"] . "<br>";
	    */

	    if (file_exists($upload_dir . $file["name"])) {
	      //echo $file["name"] . " already exists. ";
	      //exit();
	    } 

	    $move_result = move_uploaded_file($file["tmp_name"], $uploaded_file);
	    //echo "Stored in: " . "upload/" . $file["name"];

	    $uploaded_data = array();
	    foreach($versions as $version){
	    	$dir = $this->check_path($upload_dir . $version['folder']);
	    	$options = array(
				'upload_dir' => $dir . '/',
				'upload_url' => 'uploads/' . $version['folder'] . '/',
				'max_width' => $version['width'],
				'max_height' => $version['height'], 
				'crop' => $version['crop'], 
				'file_name' => $new_file_name, 
				'thumb' => $upload_url . 'thumbnails/' . $new_file_name
			);

	    	$result = $this->image_handler->crop_image($uploaded_file, $options);

	    	array_push($uploaded_data, $options);
	    }

	    //Remove source
	    unlink($uploaded_file);

	    $this->success_response($uploaded_data);
	}

	function get_json($str){
		$pattern = '/\&quot;/';
	    $replace = '"';

	    $str = preg_replace($pattern, $replace, $str);
	    $json = json_decode($str, true);

	    return $json;
	}

	function check_path($path){
		$folders = explode('/', $path);
		$p = '';
		foreach($folders as $folder){
			$p .= $folder . '/';
			if(file_exists($p) == false){
				$old = umask(0); 
				mkdir($p,0777); 
				umask($old); 
			}
		}

		return $path;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */