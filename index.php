<?php

include_once "includes/common.inc";

if (variable_get(dev_timing, 0)) timer_start();

// Initialize/pre-process variables:
$number = ($user->nodes) ? $user->nodes : 10;
$date = ($date > 0) ? $date : time();

// Perform query:
$result = db_query("SELECT n.*, s.*, u.userid, COUNT(c.lid) AS comments FROM node n LEFT JOIN story s ON n.nid = s.nid LEFT JOIN comments c ON n.nid = c.lid LEFT JOIN users u ON n.author = u.id WHERE n.status = '$status[posted]' AND n.type = 'story' ". ($section ? "AND s.section = '$section' " : "") ."AND n.timestamp <= $date GROUP BY n.nid ORDER BY n.timestamp DESC LIMIT $number");

// Display nodes:
$theme->header();
while ($story = db_fetch_object($result)) $theme->story($story);
$theme->footer();

if (variable_get(dev_timing, 0)) timer_print();

?>
