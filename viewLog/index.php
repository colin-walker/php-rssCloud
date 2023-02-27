<?php

define('APP_RAN', '');

require_once('../config/config.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>rssCloud log</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/png" href="../images/cloud.png">
</head>

<body class="log">
	<header id="masthead" class="site-header">
        <div class="site-branding">
            <h1 class="site-title">
                <a href="../" rel="home" title="home">
                    <img class="cloud" src="../images/cloud.png"/><span class="p-name">rssCloud log</span>
                </a>
            </h1>
        </div>
    </header>
        
    <div class="wrapper log">
	    <span class="log">Recent events</span>
	    <br><br>
	    <table>
	    	<thead>
		    	<tr>
		    		<th>Type</th>
		    		<th>Description</th>
		    		<th>Time</th>
		    	</tr>
	    	</thead>
	    	<tbody>
<?php

$logs = readdb("SELECT * from rssc_log ORDER BY ID DESC LIMIT 100");

foreach ($logs as $event) {
	$time = date("Y-m-d, H:i:s", $event['time']);
	$endpoint = $event['endpoint'];
	$feed = $event['feed'];
	$action = $event['action'];
	$status = $event['status'];
	
	echo '<tr>';
	
	/*	Actions:
		0:	cancel
		1:	subscribe
		2:	ping
		3:	notify
		
		Status:
		0: failure
		1: success
		2: duplicate
		3: update
		4: no change
		5: remove
	*/
	
	// cancel
	
	if ($action == 0) {
		$host = parse_url($endpoint, PHP_URL_HOST);
		empty($host) ? $host = $endpoint : null;
		switch ($status) {
			case "0":
				echo '<td>CANCEL</td>';
				echo '<td>Cancellation failed as '.$host.' is not subscribed to a <a href="'.$feed.'">feed</a></td>';
				echo '<td>'.$time.'</td>';
				break;
			case "1":
				echo '<td>CANCEL</td>';
				echo '<td>'.$host.' cancelled their subscription to a <a href="'.$feed.'">feed</a></td>';
				echo '<td>'.$time.'</td>';
				break;
		}
	}
	
	// subscribe
	
	if ($action == 1) {
		$host = parse_url($endpoint, PHP_URL_HOST);
		switch ($status) {
			case "0":
				echo '<td>SUBSCRIBE</td>';
				echo '<td>'.$host.' failed to subscribed to a <a href="'.$feed.'">feed</a></td>';
				echo '<td>'.$time.'</td>';
				break;
			case "1":
				echo '<td>SUBSCRIBE</td>';
				echo '<td>'.$host.' successfully subscribed to a <a href="'.$feed.'">feed</a></td>';
				echo '<td>'.$time.'</td>';
				break;
			case "3":
				echo '<td>SUBSCRIBE</td>';
				echo '<td>'.$host.' refreshed their subscription to a <a href="'.$feed.'">feed</a></td>';
				echo '<td>'.$time.'</td>';
				break;
			case "5":
				echo '<td>SUBSCRIBE</td>';
				echo '<td>'.$host.' subscription to a <a href="'.$feed.'">feed</a> removed due to failures</td>';
				echo '<td>'.$time.'</td>';
				break;
		}
	}
	
	// ping
	
	if ($action == 2) {
		$host = parse_url($endpoint, PHP_URL_HOST);
		switch ($status) {
			case "3":
				echo '<td>PING</td>';
				echo '<td>A <a href="'.$feed.'">feed</a> sent a ping and was confirmed to have changed</td>';
				echo '<td>'.$time.'</td>';
				break;
			case "4":
				echo '<td>PING</td>';
				echo '<td>A <a href="'.$feed.'">feed</a> sent a ping but there was no change</td>';
				echo '<td>'.$time.'</td>';
		}
	}
	
	// notify
	
	if ($action == 3) {
		$host = parse_url($endpoint, PHP_URL_HOST);
		switch ($status) {
			case "0":
				echo '<td>NOTIFY</td>';
				echo '<td>Failed to notify '.$host.' that a <a href="'.$feed.'">feed</a> was updated</td>';
				echo '<td>'.$time.'</td>';
				break;
			case "1":
				echo '<td>NOTIFY</td>';
				echo '<td>Successfully notified '.$host.' that a <a href="'.$feed.'">feed</a> was updated</td>';
				echo '<td>'.$time.'</td>';
				break;
		}
	}
	
	echo '</tr>';
}

?>
			</tbody>
		</table>
	</div>
	
</body>
</html>