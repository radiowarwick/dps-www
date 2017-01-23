<?php

Output::set_title("Sustainer Control Centre");
Output::add_script(LINK_ABS."js/jquery-ui-1.10.3.custom.min.js");

Output::require_group("Sustainer Admin");

MainTemplate::set_subtitle("Perform common sustainer tasks");

if (isset($_POST["restart-marceline"])) {
    system("sudo /etc/init.d/marceline restart");
}

if (isset($_POST["restart-javo"])) {
    system("sudo /etc/init.d/javo restart");
}

if (isset($_POST['trackid']) || isset($_GET['trackid'])) {
	$query = "SELECT * FROM audio WHERE id=:trackid";
	$parameters = array(':trackid' => $_REQUEST['trackid']);
	$result = DigiplayDB::query($query, $parameters);
	if( $result->rowCount() != 1 ) {
		echo(Bootstrap::alert_message_basic("danger","Couldn't find track ID in the digiplay audio DB."));
	} else {
		$track = $result->fetch();
		$query = "SELECT * FROM sustschedule order by id asc limit 1";
		$result = DigiplayDB::query($query);
		$scheduleslot = $result->fetch();
		if ($track['id'] != $scheduleslot['audioid']) {
			$query = "UPDATE sustschedule SET audioid=:trackid, trim_start_smpl=0, trim_end_smpl = :tracklength, fade_in = 0, fade_out = :tracklength WHERE id = :scheduleslot";
			$parameters = array(':trackid' => $track['id'], ':tracklength' => $track['length_smpl'], ':scheduleslot' => $scheduleslot['id']);
			DigiplayDB::query($query, $parameters);
			$query = "INSERT INTO sustlog (audioid,userid,timestamp) VALUES (:audioid,:userid,:timestamp)";
			date_default_timezone_set("Europe/London");
			$parameters = array(':audioid' => $track['id'], ':userid' => Session::get_id(), ':timestamp' => time());
			DigiplayDB::query($query, $parameters);
			echo(Bootstrap::alert_message_basic("info","Track Scheduled."));
		} else {
		 	echo(Bootstrap::alert_message_basic("warning","This track is already at the top of the queue."));
		}
	}
}

$currentQueue = Sustainer::get_queue();
$i = 0;
?>
<div class="row">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="title">Service</th>
				<th class="title">Status</th>
				<th class="icon">Restart</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Marceline</td>
				<td>
          <?echo preg_replace('/\(pid\s\d+\)/', '', substr(exec("sudo /etc/init.d/marceline status"), 8));?>
        </td>
				<td><form method="POST"><input name="restart-marceline" type="submit" class="btn btn-danger" value="Restart" /></form></td>
			</tr>
      <tr>
        <td>JAVO</td>
        <td>
          <?echo preg_replace('/\(pid\s\d+\)/', '', substr(exec("sudo /etc/init.d/javo status"), 8));?>
        </td>
        <td><form method="POST"><input name="restart-javo" type="submit" class="btn btn-danger" value="Restart" /></form></td>
      </tr>
		</tbody>
	</table>
</div>
<h3>Current queue:</h3>
<?
if (!is_null($currentQueue)) {

	if (array_key_exists('id', $currentQueue)) {
		$currentQueueTemp = array(0 => $currentQueue);
		$currentQueue = $currentQueueTemp;
	}
?>
<table class="table table-striped table-bordered">
	<thead>
	<tr>
	<th></th>
	<th>Title</th>
	<th>Artist</th>
	<th>Album</th>
	</tr>
	</thead>
	<tbody>
    <?foreach ($currentQueue as $row) {
	    $i++;
      ?>
      <tr>
	      <td><?echo($i);?></td>
        <td><?echo($row['title']);?></td>
        <td><?echo($row['artist']);?></td>
        <td><?echo($row['album']);?></td>
      </tr>
    <?}?>
  </tbody>
</table>
<?} else {
	Bootstrap::alert("warning","<b>Warning: </b>The current queue is empty","",false);
}?>
<h3>Schedule audio:</h3>
<p>You can use this tool to schedule the next audio track to be played on Sue by using its audio id.</p>
<form method="post">
  Track ID: <input type="text" name="trackid" /><input type="submit" name="submit" value="Schedule" />
</form>
<?
$currentLog = Sustainer::get_log();
$i = 0;
?>
<h3>Scheduler log:</h3>
<table class="table table-striped table-bordered">
	<thead>
  	<tr>
    	<th>Date</th>
    	<th>Title</th>
    	<th>Artist</th>
    	<th>Scheduled By</th>
  	</tr>
	</thead>
	<tbody>
    <?foreach ($currentLog as $row) {
    	$i++;?>
    	<tr>
    		<td><?echo(date('d/m/y H:i', $row['timestamp']));?></td>
      	<td><?echo($row['title']);?></td>
      	<td><?echo($row['artist']);?></td>
      	<td><?echo($row['username']);?></td>
    	</tr>
    <?}?>
  </tbody>
</table>
