<?php

MainTemplate::set_sidebar(NULL);
Output::add_script("../js/jquery-ui-1.10.3.custom.min.js");
Output::add_script("aw.js");
Output::add_less_stylesheet("aw.less");
Output::add_stylesheet("aw.css");
$aw_set = AudiowallSets::get((int)$_REQUEST['id']);
if ($aw_set == null){
  Output::http_error(404);
}
else if(!$aw_set->user_can_view()) {
  Output::http_error(401);
}

$aw_walls = $aw_set->get_walls();

$styles = AudiowallStyles::get_all();
Output::set_title("Audiowall: " . $aw_set->get_name());
MainTemplate::set_subtitle("<br><span id=\"wall-description\">".$aw_set->get_description()."</span><span id=\"aw_edit_buttons\"><p class=\"text-success\">Changes saved!</p><a href=\"#\" class=\"btn btn-primary\">Edit</a><a href=\"#\" class=\"btn btn-success\">Save</a></span>");

echo("<span id=\"wall-name\" data-dps-set-id=\"".$aw_set->get_id()."\">".$aw_set->get_name()."</span>");
?>
<div class="alert alert-danger" role="alert">
  There are unsaved changes!
</div>
<div class="row">
  <div class="col-md-5">
    <div class="list-group" id="walls-tabs">
       <?php
        $w = 0;  
        foreach ($aw_walls as $wall) { 
          echo("<a id=\"walls-tab\" href=\"#page".$wall->get_page()."\" data-toggle=\"tab\" data-dps-wall-page=\"".$wall->get_page()."\" data-dps-wall-id=\"".$wall->get_id()."\" class=\"list-group-item");
          if ($w == 0) {
            echo(" list-group-item-info");
          }
          echo("\">");
          echo ("<span id='page-name'>");
          if ($wall->get_name() != "") {
            echo($wall->get_name());
          } 
          else {
            echo("Page ".$wall->get_page()+1);
          }
          echo("</span>"); 
          echo("<span class=\"badge badge-remove\">".Bootstrap::fontawesome("times")."</span>");
          echo("<span class=\"badge badge-down\">".Bootstrap::fontawesome("chevron-down")."</span>");
          echo("<span class=\"badge badge-up\">".Bootstrap::fontawesome("chevron-up")."</span>");
          echo("<span class=\"badge badge-edit\">".Bootstrap::fontawesome("pencil-alt")."</span>");
          echo("</a>");
          $w++;
        }

        if(count($aw_walls) >= 8) {
          echo("<a class=\"list-group-item active\">You have reached the page limit</a>");
        }
        else {
          echo("<a class=\"list-group-item active\" id=\"wall-new\" href=\"#new\">Add Page</a>");
        }

        ?>
      </div>
    </div>
  <div class="col-md-7">
    <div id="walls" class="tab-content">
      <?php $w = 0;
       foreach ($aw_walls as $wall) {
        echo("<div id=\"page".$wall->get_page()."\" class=\"dps-wall tab-pane"); if ($w == 0) { echo(" active"); } echo("\" data-dps-wall-id=\"".$wall->get_id()."\" data-dps-wall-page=\"".$wall->get_page()."\"><ul class=\"wall\">");
        $i = 0;
        foreach ( $wall->get_items() as $item){
          $styleid = $item->get_style();
          $rgb = $styleid->get_background_rgb();
          while ($i < $item->get_item()) { echo("<li data-dps-aw-slot=\"".$i."\" class=\"spacer\"></li>"); $i++; }
            $audio = $item->get_audio();
            echo("<li data-dps-aw-slot=\"".$i."\" class=\"spacer\">
              <div data-dps-audio-id=\"".$item->get_audio_id()."\" data-dps-aw-style=\"".$item->get_style_id()."\" style=\"background-color: ".$rgb.";\" data-dps-item-id=\"".$item->get_id()."\" class=\"dps-aw-item dps-aw-style-".$item->get_style_id()."\">
                <span class=\"text\">".$item->get_text()."</span>
                <span class=\"length\" data-dps-audio-length=\"".$audio->get_length()."\">".$audio->get_length_formatted()."</span>
                <span class=\"play-audio\" data-dps-audio-id=\"".$item->get_audio_id()."\" data-dps-action=\"play\">
                  ".Bootstrap::fontawesome("play-circle", "fa-lg")."
                </span>
              </div>
            </li>");
            $i++;
        }
        while ($i < 12) { echo("<li data-dps-aw-slot=\"".$i."\" class=\"spacer\"></li>"); $i++; }


        echo ("</ul></div>");
        $w++;
        }
       echo("<div id=\"new\" data-dps-wall-id=\"\" data-dps-wall-page=\"\" class=\"tab-pane\"><ul class=\"wall\">");
       $i = 0;
        while ($i < 12) { echo("<li data-dps-aw-slot=\"".$i."\" class=\"spacer\"></li>"); $i++; }
        echo ("</ul></div>");
      ?>
    </div>
  </div>
</div>

<div class="row">
  <div id="tray-wrap">
    <p>This is the tray, you can use it to temporarily store or delete Audiowall items.</p>
    <div id="tray-container">
      <div id="tray">
        <div class="clearfix">&nbsp;</div>
      </div>
    </div>
    <div class="clearfix">&nbsp;</div>
  </div>
</div>

<div class="alert alert-info">
  <?php
    // if(!(Session::is_group_user('Audiowalls Admin')))
    //   echo("Search includes all tracks which are marked as a Jingle or Advert. Please contact the <a href=\"mailto:music@radio.warwick.ac.uk\">Head of Music</a> if a track you require is marked incorrectly.");
    // else
    echo("Search includes all tracks which are marked as a Jingle, Advert or Track.");
  ?>
</div>

<div class="row">
  <form id="search-form" class="form-inline">
    <input type="text" class="form-control" id="search-term" placeholder="Search Tracks">
    <button type="submit" class="btn btn-primary" id="search-btn">Search</button>
    <span id="search-result-message" style="display: none;"><span id="search-result-count"></span> results for <span id="search-result-term"></span></span>
  </form>
</div>

<table id="search-results" class="table table-striped">
  <thead>
    <tr>
      <th class="icon"></th>
      <th class="icon"></th>
      <th>Title</th>
      <th>Artist</th>
      <th>Album</th>
      <th class="icon"></th>
      <th>Length</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>

<div id="delete-modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content"> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">Delete Page</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-8">
            Are you sure you want to delete the page: 
          </div>
          <div class="col-md-4" id="delete-modal-page"></div>
        </div>
        <p>&nbsp;</p>
        <div class="modal-footer clearfix">
          <a href="#" class="btn btn-primary">Yes</a>
          <a href="#" class="btn btn-danger">No</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="delete-item-modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content"> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">Delete Item</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-8">
            Are you sure you want to delete the item: 
          </div>
          <div class="col-md-4" id="delete-item-item"></div>
        </div>
        <p>&nbsp;</p>
        <div class="modal-footer clearfix">
          <a href="#" class="btn btn-primary">Yes</a>
          <a href="#" class="btn btn-danger">No</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="item-edit" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content"> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">Edit Item</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-8">
            <?php
            echo("<form role=\"form\" class=\"form-horizontal\"><input type=\"hidden\" id=\"dps-aw-item\" name=\"dps-aw-item\"><input type=\"hidden\" id=\"dps-aw-page\" name=\"dps-aw-page\">
            <div class=\"form-group\">
            <label for=\"text\" class=\"col-lg-2 control-label\">Label</label>
            <div class=\"col-lg-10\">
            <input class=\"form-control\" id=\"text\" name=\"text\" type=\"text\">
            </div>
            </div>
            <div class=\"form-group\">
            <label for=\"style\" class=\"col-lg-2 control-label\">Style</label>
            <div class=\"col-lg-10\">
            <select class=\"form-control\" id=\"style\" name=\"style\">");
            foreach($styles as $style) {
              $rgb = $style->get_background_rgb();
              echo("<option value=\"".$style->get_id()."\" style=\"background-color:".$rgb.";\" class=\"dps-aw-style-".$style->get_id()."\">".$style->get_name()."</option>");
            }               
            echo("</select>
            </div>
            </div>
            </form>
            </div>
            <div class=\"col-md-4\">
            <div id=\"sample\"><span class=\"text\"></span><span class=\"length\"></span></div>"); ?>
          </div>
        </div>
      </div>
      <div class="modal-footer clearfix">
        <a href="#" class="btn btn-info" id="item-delete">Delete Item</a>
        <a href="#" class="btn btn-primary">Apply</a>
        <a href="#" class="btn btn-danger">Cancel</a>
      </div>
    </div>
  </div>
</div>
        
<div id="set-edit" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content"> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">Edit Audiowall Set</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <?php echo("<form role=\"form\" class=\"form-horizontal\">           <div class=\"form-group\">
            <label for=\"name\" class=\"col-lg-2 control-label\">Name</label>
            <div class=\"col-lg-10\">
            <input class=\"form-control\" id=\"set-edit-name\" name=\"name\" type=\"text\" value=\"".$aw_set->get_name()."\">
            </div>
            </div>
            <div class=\"form-group\">
            <label for=\"desc\" class=\"col-lg-2 control-label\">Description</label>
            <div class=\"col-lg-10\">
            <input class=\"form-control\" id=\"set-edit-desc\" name=\"desc\" type=\"text\" value=\"".$aw_set->get_description()."\">
            </div>
            </div>
            <div class=\"form-group\">
            <label for=\"name\" class=\"col-lg-2 control-label\">Page Name</label>
            <div class=\"col-lg-10\">
            <input type=\"text\" class=\"form-control\" id=\"set-edit-page\" name=\"set-edit-page\">
            </div>
            </div>
            </form>"); ?>
          </div>
        </div>
      </div>
      <div class="modal-footer clearfix">
        <a href="#" class="btn btn-primary">Apply</a>
        <a href="#" class="btn btn-danger">Cancel</a>
      </div>
    </div>
  </div>
</div>

<div id="add-page-modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content"> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">Add New Page</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <form role="form" class="form-horizontal">
              <div class="form-group">
                <label for="name" class="col-lg-2 control-label">Page Name</label>
                <div class="col-lg-10">
                  <input class="form-control" id="new-page-name" name="name" type="text" value="New Page"\>
                </div>
              </div>
              <div class="form-group">
                <label for="desc" class="col-lg-2 control-label">Page Description</label>
                <div class="col-lg-10">
                  <input class="form-control" id="new-page-desc" name="desc" type="text" value="New Page"\>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="modal-footer clearfix">
        <a href="#" class="btn btn-primary">Add</a>
        <a href="#" class="btn btn-danger">Cancel</a>
      </div>
    </div>
  </div>
</div>

<div id="edit-page-modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content"> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">Edit Page Name</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <form role="form" class="form-horizontal">
              <div class="form-group">
                <label for="name" class="col-lg-2 control-label">Page Name</label>
                <div class="col-lg-10">
                  <input class="form-control" id="edit-page-name" name="name" type="text" value=""\>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="modal-footer clearfix">
        <a href="#" class="btn btn-primary">Update</a>
        <a href="#" class="btn btn-danger">Cancel</a>
      </div>
    </div>
  </div>
</div>