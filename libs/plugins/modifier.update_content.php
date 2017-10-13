<?php

function smarty_modifier_update_content($data, $pageID = null, $typeID = null, $minrows = '20', $mincols = '90')
{
    //Default data
    $newdata=$data;

    //Make sure that we can even print out the instant update system.
    if(isset($_SESSION['user_data']['is_admin']) && $_SESSION['user_data']['is_admin']=="1" && $_SESSION['updateContent_switch']=="1"){

    //---------------------------------------
    //=What kind of instant update is it?
    //==1 - Page system
    //==2 - Letters
    //==3 - Fund structure
    //==4 - Performance
    //---------------------------------------
   
    $old_content_pages="<div id=\"old_content\">$data<BR><BR>";
    $old_content_pages.="<a href=\"javascript:void(0);\" onClick=\"updateContent(1);\"><img src=\"".$GLOBALS['config']['image_url']."/update_content.jpg\" border=0> Update This Content</a></div>";

    $old_content_letters=$old_content_pages;
    
    $old_content_structure="<span id=\"old_content\" onClick=\"updateContent(3);\">$data";

    $old_content_performance="<span id=\"old_content\" onClick=\"updateContent(3);\">$data";

    $form_top.="<div id=\"new_content\" style=\"display: none;position: absolute;\">";
    $form_top.="<form name=\"updatemycontent\" method=\"POST\" action=\"/updatecontent\">";
    $form_and_textarea="<textarea name=\"update_content\" id=\"update_content\" rows=\"".$minrows."\" cols=\"".$mincols."\" wrap=\"virtual\" wysiwyg=\"true\" width=\"400\">$data</textarea>";
    $form_and_textarea.="<BR><input type=\"submit\" value=\"SAVE CONTENT\">";
    $form_and_textarea.="<input type=\"hidden\" name=\"pid\" value=\"$pageID\"><input type=\"hidden\" name=\"tid\" value=\"$typeID\"></form></div>";

    switch($typeID){
      case "1":
       $newdata=$old_content_pages . $form_top . $form_and_textarea;
      break;
      case "2":
       $newdata=$old_content_letters . $form_top . $form_and_textarea;
      break;
      case "3":
       $newdata=$old_content_structure;
      break;
      case "4":
       $newdata=$old_content_performance;
      break;
      default:
       $newdata="";
    }
  }
  return $newdata;
}