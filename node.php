<?php

include "includes/common.inc";

function node_history($node) {
  global $status;
  if ($node->status == $status[expired] || $node->status == $status[posted]) {
    $output .= "<DT><B>". format_date($node->timestamp) ." by ". format_username($node->userid) .":</B></DT><DD>". check_output($node->log, 1) ."<P></DD>";
  }
  if ($node->pid) {
    $output .= node_history(node_get_object("nid", $node->pid));
  }
  return $output;
}

function node_refers($node) {
  print "under construction";
}

$node = ($title ? node_get_object(title, check_input($title)) : node_get_object(nid, check_input($id)));

if ($node && node_visible($node)) {
  switch ($op) {
    case "history":
      $theme->header();
      $theme->box(t("History"), node_info($node) ."<DL>". node_history($node) ."</DL>");
      $theme->footer();
      break;
    default:
      if ($user->id) user_load($user->userid);
      node_view($node, 1);
  }
}
else {
  $theme->header();
  $theme->box(t("Warning: not found"), t("The content or data you requested does not exist or is not accessible."));
  $theme->footer();
}

?>