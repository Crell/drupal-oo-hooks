<?php

include_once "includes/common.inc";

page_header();

foreach(explode("&", $QUERY_STRING) as $attribute) {
  if ($attribute) $query .= "attribute LIKE '%". check_input(strtr($attribute, "=", ":")) ."%' AND ";
}

$query = !$date ? $query : "";

$result = db_query("SELECT nid, type FROM node WHERE $query promote = '1' AND status = '". node_status("posted") ."' AND timestamp <= '". ($date > 0 ? check_input($date) : time()) ."' ORDER BY timestamp DESC LIMIT ". ($user->nodes ? $user->nodes : variable_get(default_nodes_main, 10)));

$theme->header();
while ($node = db_fetch_object($result)) {
  node_view(node_get_object(array("nid" => $node->nid, "type" => $node->type)), 1);
}
$theme->footer();

page_footer();

?>
