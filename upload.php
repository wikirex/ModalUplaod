<?php
require_once('classes/Image_Handler.php');

$ih = new Image_Handler();
$err = '';

$versions = $_POST['versions'];
$versions = get_json($versions);
//log_g(get_json($versions));

$upload_dir = dirname(dirname(__FILE__)) . '/uploads/';
$upload_url = '/uploads/';

$field = 'upload_file';
$file = $_FILES[$field];

$new_file_name = $file["name"];
$uploaded_file = $upload_dir . $new_file_name;
//log_g($upload_dir);

$allowedExts = array("gif", "jpeg", "jpg", "png");
$allowedTypes = array("image/gif", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png", "image/png");
$extension = get_extension($file["name"]);
$size_limit = 3*1024*1024;

//log_g($extension);

if (in_array($file['type'], $allowedTypes) 
	&& ($file["size"] < $size_limit) 
	&& in_array($extension, $allowedExts)) 
{
  if ($file["error"] > 0) 
  {
    $err = "Return Code: " . $file["error"] . "<br>";
  } 
  else 
  {
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

    move_uploaded_file($file["tmp_name"], $uploaded_file);
    //echo "Stored in: " . "upload/" . $file["name"];

    foreach($versions as $version){
    	$dir = check_path($upload_dir . $version['folder']);
    	$options = array(
			'upload_dir' => $dir,
			'upload_url' => $upload_url . $version['folder'],
			'max_width' => $version['width'],
			'max_height' => $version['height'], 
			'crop' => $version['crop']
		);

    	$ih->crop_image($uploaded_file, $options);
    }

    //Remove source
    unlink($uploaded_file);
  }
} else {
  $err = "Invalid file";
}


header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json;charset=utf-8');
  
$response = array(
	'status' => '', 
	'data' => null
);
  
if(strlen($err) == 0){
    $response['status'] = 'success';
    $response['data'] = $upload_url . $versions[0]['folder'] . $new_file_name;
}else{
	$response['status'] = 'fail';
	$response['error'] = $err;
}
  
echo json_encode($response);




function log_g($obj){
	if(is_array($obj)){
		print_r($obj);
		exit();
	}else{
		echo var_dump($obj);
		exit();
	}
}

function get_json($str){
	$pattern = '/\&quot;/';
    $replace = '"';

    $str = preg_replace($pattern, $replace, $str);
    $json = json_decode($str, true);

    return $json;
}

function get_unique_file_name($filename){
	$new_name = uniqid('', true);
	$new_name = preg_replace('/\./', '_', $new_name);
	return $new_name . '.' . $this->get_extension($filename);
}

function get_extension($filename){
	$path_parts = pathinfo($filename);
	return strtolower($path_parts['extension']);
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
?>


