<?php
Output::set_template();
if(isset($_REQUEST["key"])) {
	$location = Locations::get_by_key($_REQUEST["key"]);
	$key = $_REQUEST["key"];
} else {
	if(isset($_REQUEST["location"])) {
		$location = Locations::get_by_id($_REQUEST["location"]);
		$key = $location->get_key();
	} else {
		exit("No location specified!");
	}
}

switch($_REQUEST["action"]) {
	case "now-next":
		$json = file_get_contents(Configs::get_system_param("now-next-api"));
		$json = json_decode($json);
		$return = "<div class=\"col-sm-6 navbar-brand\">On now: <span id=\"on-now\">".$json[0]->name."</span></div>
			<div class=\"col-sm-6 navbar-brand\">On next: <span id=\"on-next\">".$json[1]->name."</span></div>";
		echo $return;
		break;
	case "info-content":
		echo(Configs::get_system_param("info-content"));
		break;
	case "search":
		$index = array();
		$query = $_REQUEST["search-text"];
		if ($_REQUEST["search-title"] == "on") $index[] = "title";
		if ($_REQUEST["search-artist"] == "on") $index[] = "artist";
		if ($_REQUEST["search-album"] == "on") $index[] = "album";
		$index = implode($index," ");

		if($query) $search = Search::tracks($query,$index,50);

		if($search["results"]) {
			$return = "<table class=\"table table-striped table-hover\" cellspacing=\"0\">
				<thead>
					<tr>
						<th class=\"icon\"></th>
						<th class=\"artist\">Artist</th>
						<th class=\"title\">Title</th>
						<th class=\"album\">Album</th>
						<th class=\"length\">Length</th>
					</tr>
				</thead>
				<tbody>";
			foreach($search["results"] as $track) {
				$track = Tracks::get($track);
				if((Configs::get_system_param("censor_start") < date("H")) && (date("H") < Configs::get_system_param("censor_end"))) $censor_time = true;
				else $censor_time = false;
				if($censor_time && $track->is_censored()) continue;
				$return .= "<tr data-track-id=\"".$track->get_id()."\" class=\"track\">
					<td class=\"icon\">".Bootstrap::glyphicon("music")."</td>
					<td class=\"artist nowrap\">".$track->get_artists_str()."</td>
					<td class=\"title nowrap\">".$track->get_title()."</td>
					<td class=\"album nowrap\">".$track->get_album()->get_name()."</td>
					<td class=\"length nowrap\">".Time::format_succinct($track->get_length())."</td>
				</tr>";
			}
			$return .= "</tbody></table>";
			if($search["total"] > 50) $return .= "<span class=\"result-limit\">Only showing top 50 results out of ".$search["total"]." total.  Try a more specific search.</span>";
			echo($return);
		} else {
			echo("<h3>No results found, or your search term was too generic.  <br />Try a different search query.</h3>");
		}
		break;
	case "messages":
		$emails = Emails::get(NULL,NULL,NULL,25,NULL);
		$return = "<table class=\"table table-striped table-hover\">
			<thead>
				<tr>
					<th class=\"icon\"></th>
					<th class=\"from\">From</th>
					<th class=\"subject\">Subject</th>
					<th class=\"datetime\">Date/Time</th>
				</tr>
			</thead>
			<tbody>";
		foreach($emails as $email) {
			$return .= "<tr data-message-id=\"".$email->get_id()."\">
				<td class=\"icon\">".($email->get_new_flag()? Bootstrap::glyphicon("envelope") : "")."</td>
				<td class=\"from nowrap\">".$email->get_sender()."</td>
				<td class=\"subject nowrap\">".$email->get_subject()."</td>
				<td class=\"datetime nowrap\">".date("d/m/y H:i", $email->get_datetime())."</td>
			</tr>";
		}
		$return .= "</tbody></table>";
		echo($return);
		break;
	case "message":
		$message = Emails::get_by_id($_REQUEST['id']);
		echo($message->get_body_formatted());
		$message->mark_as_read();
		break;
	case "playlists":
		$playlists = Playlists::get_all(false);
		$return = "";
		foreach($playlists as $playlist) {
			$return .= "
				<div class=\"panel panel-default\">
					<div class=\"panel-heading\" data-toggle=\"collapse\" href=\"#playlist-".$playlist->get_id()."\">
						<h4 class=\"panel-title\">".Bootstrap::glyphicon("play-circle").$playlist->get_name()."</h4>
					</div>
					<div id=\"playlist-".$playlist->get_id()."\" class=\"panel-collapse collapse\">
						<table class=\"table table-striped table-hover\">
							<thead>
								<tr>
									<th class=\"icon\"></th>
									<th class=\"artist\">Artist</th>
									<th class=\"title\">Title</th>
									<th class=\"album\">Album</th>
									<th class=\"length\">Length</th>
								</tr>
							</thead>
							<tbody>";
			foreach($playlist->get_tracks() as $track) {
				$return .= "
								<tr data-track-id=\"".$track->get_id()."\" class=\"track\">
									<td class=\"icon\">".Bootstrap::glyphicon("music")."</td>
									<td class=\"artist nowrap\">".$track->get_artists_str()."</td>
									<td class=\"title nowrap\">".$track->get_title()."</td>
									<td class=\"album nowrap\">".$track->get_album()->get_name()."</td>
									<td class=\"length nowrap\">".Time::format_succinct($track->get_length())."</td>
								</tr>";
			}
			$return .= "
							</tbody>
						</table>
					</div>
				</div>
			";
			$first = false;
		}
		echo($return);
		break;
	case "log":

		if(isset($_REQUEST["title"])) {
			$log = new LogItem;
			$log->set_location($location);
			if(Session::is_user()) $log->set_user(Session::get_user());
			$log->set_track_title($_REQUEST["title"]);
			$log->set_track_artist($_REQUEST["artist"]);
			$log->save();
		}
		$logitems = LogItems::get($location,"datetime DESC",20,NULL);
		$return = "<table class=\"table table-striped table-hover\">
			<thead>
				<tr>
					<th class=\"icon\"></th>
					<th class=\"artist\">Artist</th>
					<th class=\"title\">Title</th>
					<th class=\"datetime\">Date/Time</th>
				</tr>
			</thead>
			<tbody>";
		foreach($logitems as $logitem) {
			$return .= "<tr data-log-id=\"logitem-".$logitem->get_id()."\">
				<td class=\"icon\">".Bootstrap::glyphicon("headphones")."</td>
				<td class=\"artist nowrap\">".$logitem->get_track_artist()."</td>
				<td class=\"title nowrap\">".$logitem->get_track_title()."</td>
				<td class=\"datetime nowrap\">".date("d/m/y H:i", $logitem->get_datetime())."</td>
			</tr>";
		}
		$return .= "</tbody></table>";
		echo($return);
		break;
	case "showplan":
		$showplan = Showplans::get_by_name("location_".$location->get_id());
		$items = $showplan->get_items();
		$return = "<div class=\"panel-group\" id=\"showplan-list\">";

		foreach($items as $item) {
			if($audio = $item->get_audio()) {
				$current = false;
				if($location->get_config("current_showitems_id")->get_val() == $item->get_id())
					if($location->get_config("next_on_showplan")->get_val() == $audio->get_md5())
						$current = true;
				$return .= "<div class=\"showplan-audio panel ".($current? "panel-primary" : "panel-default")."\" data-item-id=\"".$item->get_id()."\">
					<div class=\"panel-heading\" data-toggle=\"collapse\">
						<h4 class=\"panel-title\">
							<div class=\"pull-right\">
								<div class=\"controls\">".Bootstrap::glyphicon("remove")."</div>
								<div class=\"duration\">".Time::format_succinct($audio->get_length())."</div></div>
							".Bootstrap::glyphicon("music").$audio->get_artists_str()." - ".$audio->get_title()."
						</h4>
					</div>
				</div>";
			}
			
			if($script = $item->get_script()) {
				$return .= "<div class=\"showplan-script panel panel-default\" data-item-id=\"".$item->get_id()."\">
					<div class=\"panel-heading\" data-toggle=\"collapse\" href=\"#item-".$item->get_id()."-toggle\">
						<h4 class=\"panel-title\">
							<div class=\"pull-right\">
								<div class=\"controls\">".Bootstrap::glyphicon("remove")."</div>
								<div class=\"duration\">".($script->get_length() > 0? Time::format_succinct($script->get_length()) : "")."</div>
							</div>
							".Bootstrap::glyphicon("file").$script->get_name()."
						</h4>
					</div>
					<div id=\"item-".$item->get_id()."-toggle\" class=\"panel-collapse collapse\">
						<div class=\"panel-body\">
							".$script->get_contents()."
						</div>
					</div>
				</div>";
			}
		}
		$return .= "</div>";
		echo $return;
		break;
	case "showplan-append":
		$showplan = Showplans::get_by_id($location->get_config("default_showplan")->get_val());
		$item = new ShowplanItem();
		$audio = Audio::get_by_id($_REQUEST["id"]);
		if(!$audio) exit(json_encode(array("response"=>"invalid")));
		$item->set_title($audio->get_title());
		$item->set_audio($audio);
		$item->set_position($showplan->get_end_position());
		$item->set_length(round($audio->get_length()));
		$item->set_showplan($showplan);
		$item->save();
		echo(json_encode(array("response"=>"success")));
		break;
	case "showplan-remove":
		$item = ShowplanItems::get_by_id($_REQUEST["id"]);
		$item->delete();
		echo(json_encode(array("response"=>"success")));
		break;
	case "showplan-clear":
		$location->get_config("next_on_showplan")->set_val("");
		$location->get_config("current_showitems_id")->set_val("");
		$showplan = Showplans::get_by_id($location->get_config("default_showplan")->get_val());
		$showplan->clear();
		echo(json_encode(array("response" => "success")));
		break;
	case "set-current":
		if(!is_numeric($_REQUEST["id"])) exit(json_encode(array("response" => "error")));
		$item = ShowplanItems::get_by_id($_REQUEST["id"]);
		$location->get_config("next_on_showplan")->set_val($item->get_audio()->get_md5());
		$location->get_config("current_showitems_id")->set_val($item->get_id());
		echo(json_encode(array("response" => "success", "id" => $item->get_id())));
		break;
	case "login":
		if(($_POST["username"] == "") || ($_POST["password"] == "")) exit(json_encode(array("response"=>"error")));
		if(!Session::login($_POST["username"],$_POST["password"])) exit(json_encode(array("response"=>"invalid")));
		$location->get_config("user_aw_set")->set_val(Session::get_user()->get_config_var("default_aw_set"));
		echo(json_encode(array("response"=>"success")));
		break;
	case "logout":
		Session::logout();
		$location->get_config("user_aw_set")->set_val(0);
		break;
}
?>