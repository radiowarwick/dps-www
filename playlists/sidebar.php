<?php
function sidebar() {
	$menu = new Menu;
	$menu->add_many(
		array("index.php","Playlists Overview"),
		array("add/","Add a new playlist"),
		array("sustainer/","Edit sustainer playlist")
	);
	$return = $menu->output(SITE_LINK_REL."playlists/",6);

	$tracks = Tracks::get_playlisted();
	foreach ($tracks as $track) { $total_length += $track->get_length(); }

	$time_arr = Time::seconds_to_dhms($total_length);
	$total_length = ($time_arr["days"])? $time_arr["days"]."d " : "";
	$total_length .= ($time_arr["hours"])? $time_arr["hours"]."h " : "";
	$total_length .= ($time_arr["minutes"])? $time_arr["minutes"]."m " : "0m ";
	$total_length .= ($time_arr["seconds"])? sprintf('%02d',$time_arr["seconds"])."s " : "00s ";

	$return .= "
	<h3>Playlists</h3>
	<dl>
		<dt>Playlisted Tracks:</dt>
		<dd>".count($tracks)."</dd>
		<dt>Length of Playlists:</dt>
		<dd>".$total_length."</dd>
	</dl>
	<h3>Sustainer Service</h3>
	<dl>
		<dt>Tracks on Sustainer</dt>
		<dd>".Sustainer::get_total_tracks()."</dd>
		<dt>Length of Sustainer Playlist</dt>
		<dd>".Sustainer::get_total_length_formatted()."</dd>
	</dl>";

	return $return;
}
?>