<?php

define('APP_RAN', '');

require_once('../config/config.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	try {
		$domain = $_POST["domain"];
		$path = $_POST["path"];
		$url = $_POST["url"];
		
		$endpoint = $domain.$path;
		
		$dbr = readdb("SELECT * FROM rssc_sub WHERE endpoint REGEXP '$endpoint' AND feed='$url'");
		$time = time();
		if (count($dbr) > 0) {
			$dbw = new DBW();
		   	$dbw->write("DELETE FROM rssc_sub WHERE endpoint REGEXP '$endpoint' AND feed='$url'");
	    
            $dbw->write("INSERT INTO rssc_log (time, endpoint, feed, action, status) VALUES ('$time', '$domain', '$url', 0, 1)");
	    	echo '<?xml version="1.0"?>';
			echo '<cancelResult success="true">';
	    } else {
	    	$dbw = new DBW();
            $dbw->write("INSERT INTO rssc_log (time, endpoint, feed, action, status) VALUES ('$time', '$domain', '$url', 0, 0)");
            echo '<?xml version="1.0"?>';
			echo '<cancelResult success="false">';
	    }
		
	} catch (exception	$e) {
		echo '<?xml version="1.0"?>';
		echo '<cancelResult success="false">';
	}
	
}

if (isset($_POST["cancelForm"])) {
?>
<script>
window.location.href = '../viewLog/';
</script>
<?php } ?>