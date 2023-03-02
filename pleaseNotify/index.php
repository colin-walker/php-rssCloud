<?php

define('APP_RAN', '');

require_once('../config/config.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	try {
		$domain = $_POST["domain"];
		$port = $_POST["port"];
		$path = $_POST["path"];
		$registerProcedure = $_POST["registerProcedure"];
		$protocol = $_POST["protocol"];
		if (isset($_POST['url'])) {
		    $url = $_POST['url'];
		} else if (isset($_POST['url1'])) {
		    $url = $_POST['url1'];
		}
	} catch (exception $e) {
	}
	
	switch ($port) {
		case "80":
			$scheme = 'http';
			break;
		case "8080":
			$scheme = 'http';
			break;
		case "443":
			$scheme = 'https';
			break;
		default:
			$scheme = 'http';
	}
	
	$ds = parse_url($domain, PHP_URL_SCHEME);
	$ds != '' ? $scheme = $ds : null ;
	
	try {
		$headers = get_headers($url);
		if (substr($headers[0], 9, 3) == '200') {
			// feed http code 200 OK
			$fail = false;
		} else {
			// can't find feed
			$fail = true;
		}
	} catch (exception $e) {
		echo $e;
		$fail = true;
	}
	
	try {
		$endpoint = $scheme.'://'.$domain.$path;
		$headers = get_headers($endpoint);
		
		if (substr($headers[0], 9, 3) == '200') {
			// endpoint http code 200 OK
			$challenge = bin2hex(random_bytes(16));
			$qry_str = '?url='.$url.'&challenge='.$challenge;
			
			$options = array(
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_FOLLOWLOCATION => TRUE,
				CURLOPT_PORT => $port,
				
			);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$endpoint.$qry_str);
			curl_setopt_array($ch, $options);
			$result = curl_exec($ch);
	        if ($result === false || $result != $challenge) {
	            $fail = true;
	            $dbw = new DBW();
	            $dbw->write("INSERT INTO rssc_log (endpoint, feed, action, status) VALUES ('$endpoint', '$url', 1, 0)");
	        }
	        curl_close($ch);
	        $fail = false;
		} else {
			// can't find endpoint
			$fail = true;
			$msg = "can't find endpoint: ".$endpoint;
		}
	} catch (exception $e) {
		$fail = true;
		$msg = "curl failed";
	}
	
	if ($fail === false) {
		//check for duplicate
		echo '<?xml version="1.0" ?>';
		echo '<notifyResult success="true"/>';
		$dbr = readdb("SELECT * FROM rssc_sub WHERE endpoint='$endpoint' AND feed='$url'");
		if (count($dbr) > 0) {
			$time = time();
			$dbw = new DBW();
			$dbw->write("UPDATE rssc_sub SET time='$time' WHERE endpoint='$endpoint' AND feed='$url'");
	        $dbw = new DBW();
	        $dbw->write("INSERT INTO rssc_log (time, endpoint, feed, action, status) VALUES ('$time', '$endpoint', '$url', 1, 3)");
		} else {
			$time = time();
			$dbw = new DBW();
			$dbw->write("INSERT INTO rssc_sub (time, endpoint, port, feed, failures) VALUES ('$time', '$endpoint', '$port', '$url', 0)");
			$dbw = new DBW();
	        $dbw->write("INSERT INTO rssc_log (time, endpoint, feed, action, status) VALUES ('$time', '$endpoint', '$url', 1, 1)");
		}
	} else {
		echo '<?xml version="1.0" ?>';
		echo '<notifyResult success="false" msg="'.$msg.'"/>';
		$time = time();
        $dbw = new DBW();
        $dbw->write("INSERT INTO rssc_log (time, endpoint, feed, action, status) VALUES ('$time', '$endpoint', '$url', 1, 0)");
	}
}

//redirect to log if manual sub from form

if (isset($_POST["notifyForm"])) {
?>
<script>
window.location.href = '../viewLog/';
</script>
<?php } ?>