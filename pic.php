<?php
define('ROOT_PATH',dirname(__file__));
error_reporting(E_STRICT|E_ALL);
date_default_timezone_set('Asia/Shanghai');
define('Encoding','utf-8');
define('CACHE_TIMEOUT',144000);
define('PIC_ALLOW_EXT','|jpg|png|');
function input_tryStr($name,&$value,&$input){
	$v = &$input[$name];
	if(!isset($v)){return false;}
	if(get_magic_quotes_gpc()){$value=stripslashes($v);}else{$value = $v;}
	return true;
}
function &input_switch($name,&$input,&$switchArray,$defaultKey=false){
	$key = &$input[$name];
	$val = &$switchArray[$key];
	if(isset($val)){return $val;}
	if($defaultKey===false) return $defaultKey;
	$val = &$switchArray[$defaultKey];
	if(isset($val)){return $val;}
	$val=false;return $val;
}
function if_modified($mtime,$message=0){
	if(filter_has_var(INPUT_SERVER,"HTTP_IF_MODIFIED_SINCE")){
		$since = strtotime($_SERVER["HTTP_IF_MODIFIED_SINCE"]);
		if($mtime <= $since){
			header("HTTP/1.1 304 Not Modified");
			die($message);
		}
	}
	header('Last-Modified:'.gmdate('D, d M Y H:i:s',$mtime).' GMT');
}
$sets = array(
	'item_wh368x368'		=>	array('base_path'=>'/','type'=>'wh','w'=>368,'h'=>368),
	'item_wh172x172'		=>	array('base_path'=>'/','type'=>'wh','w'=>172,'h'=>172),
	'art_wh219x219'			=>	array('base_path'=>'/','type'=>'wh','w'=>219,'h'=>219)
);
function not_found(){
	header('HTTP/1.1 404 Not Found');
	header('status:404 Not Found');
	die(0);
}
// get:path,type
if(!input_tryStr('path',$path,$_GET) || !input_tryStr('type',$type_name,$_GET) || !($set = &input_switch('type',$_GET,$sets,false)))not_found();
$base_path = realpath(ROOT_PATH.$set['base_path']);
$path = realpath($base_path.'/'.$path);
$base_path_len = strlen($base_path);
$rpath = substr($path,$base_path_len);
if(!$path || (substr($path,0,$base_path_len))!=$base_path)not_found($base_path);
$ext = strtolower(pathinfo($path,PATHINFO_EXTENSION));
if(strpos(PIC_ALLOW_EXT,'|'.$ext.'|')===false)not_found();
// check cache

define('CACHE_PATH',ROOT_PATH.'/zoom_pic_cache/');
define('CACHE_CLEAR_FIRE_MAX',500);
define('CACHE_CLEAR_FIRE_MIN',495);
define('CACHE_LOCK_FILE_PATH',ROOT_PATH.'zoom_pic_cache.lock');
function cache_trylock(){
	if(!file_exists(CACHE_LOCK_FILE_PATH)){
		file_put_contents(CACHE_LOCK_FILE_PATH,'');
	}
	$file = fopen(CACHE_LOCK_FILE_PATH,'r');
	if($file===false)return false;
	if(flock($file,LOCK_EX)) return $file;
	fclose($file);
	return false;
}
function cache_unlock($lock){
	if($lock!==false){
		flock($lock,LOCK_UN);
		fclose($lock);
	}
}
function cache_add($key,$timeout,&$contents){
	/// 添加一个缓存项
	$path=CACHE_PATH.$key.'.cache';
	$mtime = time();
	$atime = $timeout;
	if(file_put_contents($path,$contents)===false)return false;
	touch($path,$mtime,$atime);
	return true;
}
function remove($key){
	return unlink(CACHE_PATH.$key.'.cache');
}
function cache_isTimeout($key,$src_mtime=false){
	$path=CACHE_PATH.$key.'.cache';
	if(!file_exists($path))return true;
	$atime=@fileatime($path);
	$mtime=@filemtime($path);
	if($atime === false || $mtime === false) return true;
	if($src_mtime!==false && $mtime <= $src_mtime) return true;
	if($atime <= time()) return true;
	return false;
}
function cache_clearTimeout(){
	if(mt_rand(0,CACHE_CLEAR_FIRE_MAX)>CACHE_CLEAR_FIRE_MIN){
		$lock=cache_trylock();
		if($lock===false)return;
		$arr = scandir(CACHE_PATH);
		foreach ($arr as $name){
			if($name == '.' || $name == '..')continue;
			$filename=CACHE_PATH.$name;
			if(is_dir($filename)){
				continue;
			}else{
				$timeout=fileatime($filename);
				if($timeout!==false && $timeout<=time()){
					@unlink($filename);
				}
			}
		}
		cache_unlock($lock);
	}
}
function &cache_getContents($key,$newtimeout=false,$src_mtime=false){
	if(!cache_isTimeout($key,$src_mtime)){
		$path=CACHE_PATH.$key.'.cache';
		if($newtimeout!==false) $atime = $newtimeout; else $atime = fileatime($path);
		$mtime = filemtime($path);
		$str = file_get_contents($path);
		touch($path,$mtime,$atime);
		if($str===false)return false;
		cache_clearTimeout();
		return $str;
	}
	$r=false;
	return $r;
}
function cache_output($key,$newtimeout=false,$src_mtime=false){
	$rt = false;
	if(!cache_isTimeout($key,$src_mtime)){
		$path=CACHE_PATH.$key.'.cache';
		if($newtimeout!==false) $atime = $newtimeout; else $atime = fileatime($path);
		$mtime = filemtime($path);
		touch($path,$mtime,$atime);
		if(readfile($path)!==false)$rt = true;
		cache_clearTimeout();
	}
	return $rt;
}
function cache_add_image($key,$timeout,$image,$ext){
	/// 添加一个缓存项
	$path=CACHE_PATH.$key.'.cache';
	$mtime = time();
	$atime = $timeout;
	switch($ext){
		case 'jpg':
			if(imagejpeg($image,$path)===false)return false;
			break;
		case 'png':
			if(imagepng($image,$path)===false)return false;
			break;
		default:
			return false;
	}
	touch($path,$mtime,$atime);
	return true;
}
function cache_mtime($key){
	$path=CACHE_PATH.$key.'.cache';
	if(!file_exists($path)) return false;
	return filemtime($path);
}

function image_zoom($srcPath,$ext,$zt,$z1,$z2){
	switch($ext){
		case 'jpg':
			$img = imagecreatefromjpeg($srcPath);
			break;
		case 'png':
			$img = imagecreatefrompng($srcPath);
			break;
	}
	if($img === false) return false;
	$srcx=$srcy=0;
	$srcw = imagesx($img);
	$srch = imagesy($img);
	$toh=$tow=1;
	$tox=$toy=0;
	switch($zt){
		case 'h':
			$toh = $z2;
			$tow = max(round($toh/$srch*$srcw),1);
			break;
		case 'w':
			$tow = $z1;
			$toh = max(round($tow/$srcw*$srch),1);
			break;
		case 'wh':
			if($srcw/$srch>$z1/$z2){
				$toh = $z2;
				$tow = $z1;
				$srcw_n=$tow*$srch/$toh;
				$srcx = round(($srcw-$srcw_n)/2);
				$srcw=$srcw_n;
				$srcy = 0;
			}else{
				$toh = $z2;
				$tow = $z1;
				$srch_n=$toh*$srcw/$tow;
				$srcy = round(($srch-$srch_n)/2);
				$srch=$srch_n;
				$srcx = 0;
			}
			break;
		case 'box':
			if($srcw/$srch>$z1/$z2){
				$tow = $z1;
				$toh = max(round($tow/$srcw*$srch),1);
			}else{
				$toh = $z2;
				$tow = max(round($toh/$srch*$srcw),1);
			}
			break;
		default:
			$tow = max(round($srcw*$z1),1);
			$toh = max(round($srch*$z2),1);
			break;
	}
	$toimg = imagecreatetruecolor($tow,$toh);
	if($toimg === false) {
		imagedestroy($img);
		return false;
	}
	if(imagecopyresampled($toimg,$img,$tox,$toy,$srcx,$srcy,$tow,$toh,$srcw,$srch) === false) {
		imagedestroy($img);
		imagedestroy($toimg);
		return false;
	}
	imagedestroy($img);
	return $toimg;
}

$mtime = filemtime($path);
$cache_key = $type_name.'.'.urlencode($rpath);
if_modified($mtime);
$timeout=time()+CACHE_TIMEOUT;
header('Content-Type:image/jpeg');
header('Cache-Control:max-age='.CACHE_TIMEOUT);
if(!cache_output($cache_key,$timeout,$mtime)){
	$img=image_zoom($path,'jpg',$set['type'],$set['w'],$set['h']);
	if($img===false){
		not_found();
	}else{
		cache_add_image($cache_key,$timeout,$img,'jpg');
		imagejpeg($img);
		imagedestroy($img);
	}
}
?>