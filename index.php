<?php 
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', false);

require('db.php');
require('msq.php');

function fixFileArray(&$file_post)
{//From php.net anonymous comment
	$file_ary = array();
	$file_count = count($file_post['name']);
	$file_keys = array_keys($file_post);
	
	for ($i=0; $i<$file_count; $i++)
	{
		foreach ($file_keys as $key)
		{
			$file_ary[$i][$key] = $file_post[$key][$i];
		}
	}
	
	return $file_ary;
}

function checkUploads($files)
{//Expects fixed array instead of $_FILES array
	foreach ($files as $index => $file)
	{
		//Discard any with errors
		if ($file['error'] != UPLOAD_ERR_OK)
		{
			unset($files[$index]);
			continue;
		}
		
		//Check sizes against 1MiB
		if ($file['size'] > 1048576)
		{
			unset($files[$index]);
			continue;
		}
		
		//Get and check mime types (ignoring provided ones)
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		if ($finfo->file($file['tmp_name']) != "application/xml")
		{
			unset($files[$index]);
			continue;
		}
	}
	
	return $files;
}

require('header.php');
?>
<div id='content'>
<?php
	if (isset($_GET['msq'])) {
		$db = connect();
		$msq = getMSQ($db, $_GET['msq']);
		parseMSQ($msq);
	} else if (isset($_POST['upload']) && isset($_FILES)) {
		//var_dump($_POST);
		//var_dump($_FILES);
?>
<div class="info">Upload successful</div>
</div>
<?php
$files = checkUploads(fixFileArray($_FILES['files']));
if (count($files) == 0)
{
	//No files made it past the check
	echo '<div class="warning">Your files has asploded.</div>';
}
else
{
	//parse the files
	echo count($files) . " files made it";
	$db = connect();
	foreach ($files as $file)
	{
		//echo 'Adding ' . $file['tmp_name'];
		addFile($db, $file);
	}
}

}
?>
<?php require('footer.php'); ?>