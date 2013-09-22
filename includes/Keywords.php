<?php
class Keywords {
	public function get($id) {
		return self::get_by_id($id);
	}

	public function get_by_id($id) { return DigiplayDB::select("* FROM keywords WHERE id = ".$id, "Keyword"); }
	public function get_by_text($text) { return DigiplayDB::select("* FROM keywords WHERE name = '".$text."';", "Keyword"); }

	public function get_by_audio($audio) { return DigiplayDB::select("keywords.* FROM keywords INNER JOIN audiokeywords ON (keywords.id = audiokeywords.keywordid) WHERE audiokeywords.audioid = ".$audio->get_id(), "Keyword", true); }
}