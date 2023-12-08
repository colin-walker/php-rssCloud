<?php

define('APP_RAN', '');

require_once('../config/config.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	try {
		$url = $_POST["url"];

		if (filter_var($url, FILTER_VALIDATE_URL)) {
		
			$options = array(
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0) Gecko/20100101 Firefox/9.0',
				CURLOPT_ENCODING => '',
				CURLOPT_HEADER => FALSE,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_FOLLOWLOCATION => TRUE,
				CURLOPT_FILETIME => TRUE,
			);
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt_array($curl, $options);
			$result = curl_exec($curl);
			if ($result === false) {
				echo (curl_error($curl)); 
			}
			$datestamp = curl_getinfo($curl, CURLINFO_FILETIME);
			if ($datestamp != -1) {
				$date = date("Y-m-d H:i:s", $datestamp);
			}
			$hash = md5($result);
			curl_close($curl);
			
			$dbr = readdb("SELECT * FROM rssc_ping WHERE feed='$url'");

			if (count($dbr) > 0) {
				// compare datestamps and hashes between database and feed
				$dbtime = $dbr[0]['time'];
				$dbhash = $dbr[0]['hash'];
				
				if (($dbtime != $date) || ($dbhash != $hash)) {
					$updated = true;
					$dbw = new DBW();
					$dbw->write("UPDATE rssc_ping SET time='$date', hash='$hash' WHERE feed='$url'");
					$ping = true;
					$time = time();
					$dbw = new DBW();
					$dbw->write("INSERT INTO rssc_log (time, endpoint, feed, action, status) VALUES ('$time', '$endpoint', '$url', 2, 3)");
					echo '<?xml version="1.0"?>';
					echo '<pingResult success="true"/>';
				} else {
					$ping = false;
					$time = time();
					$dbw = new DBW();
					$dbw->write("INSERT INTO rssc_log (time, endpoint, feed, action, status) VALUES ('$time', '$endpoint', '$url', 2, 4)");
					echo '<?xml version="1.0"?>';
					echo '<pingResult success="false" msg="No change"/>';
				}
			} else {
				$dbtime = $dbhash = '';
				$dbw = new DBW();
				$dbw->write("INSERT INTO rssc_ping (feed, time, hash) VALUES ('$url', '$date', '$hash')");
				$ping = true;
				$time = time();
				$dbw = new DBW();
				$dbw->write("INSERT INTO rssc_log (time, endpoint, feed, action, status) VALUES ('$time', '$endpoint', '$url', 2, 3)");
				echo '<?xml version="1.0"?>';
				echo '<pingResult success="true"/>';
			}
				
			if ($ping) {
				$subs = readdb("SELECT * from rssc_sub WHERE feed='$url'");
				if (count($subs) > 0) {
					$fields = array('url' => $url);
					$postdata = http_build_query($fields);
					$ping_options = array(
						CURLOPT_POST => TRUE,
						CURLOPT_POSTFIELDS => $postdata,
						CURLOPT_RETURNTRANSFER => TRUE,
						CURLOPT_FOLLOWLOCATION => TRUE,
					);
		
					foreach ($subs as $sub) {
						try {
							// send notification to subscriber
							$endpoint = $sub['endpoint'];
							$port = $sub['port'];
							$ch = curl_init($endpoint);
							curl_setopt_array($ch, $ping_options);
							curl_setopt($ch, CURLOPT_POSTREDIR, CURL_REDIR_POST_ALL);
							curl_setopt($ch, CURLOPT_PORT, $port);
							$httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
							$result = curl_exec($ch);
							if ($result === false) {
								throw new Exception(curl_error($ch), curl_errno($ch));
							}
							$time = time();
							$dbw = new DBW();
							$dbw->write("INSERT INTO rssc_log (time, endpoint, feed, action, status) VALUES ('$time', '$endpoint', '$url', 3, 1)");     
						} catch(Exception $e) {
							//notification failed
							$time = time();
							$dbw = new DBW();
							$dbw->write("INSERT INTO rssc_log (time, endpoint, feed, action, status) VALUES ('$time', '$endpoint', '$url', 3, 0)");
							
							$dbr = readdb("SELECT failures FROM rssc_sub WHERE endpoint='$endpoint' AND feed='$url'");
							$count = (int)$dbr[0]['failures'];
							$count++;
							
							if ($count == 5) {			            
								$dbw = new DBW();
								$dbw->write("DELETE FROM rssc_sub WHERE endpoint='$endpoint' AND feed='$url'");
								$dbw = new DBW();
								$dbw->write("INSERT INTO rssc_log (time, endpoint, feed, action, status) VALUES ('$time', '$endpoint', '$url', 1, 5)");
							} else {			            
								$dbw = new DBW();
								$dbw->write("UPDATE rssc_sub SET failures='$count' WHERE endpoint='$endpoint' AND feed='$url'");
							}
						} finally {
							curl_close($ch);
						}
					}
				}
			}
		}
	} catch (exception $e) {
	}
}

//redirect to log if manual ping from form

if (isset($_POST["pingForm"])) {
?>
<script>
window.location.href = '../viewLog/';
</script>
<?php } ?>