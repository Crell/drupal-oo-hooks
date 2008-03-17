<?php
// $Id$

/**
 * @file
 * Handles incoming requests to fire off regularly-scheduled tasks (cron jobs).
 */

include_once './includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
if (isset($_GET['cron_key']) && variable_get('cron_key', 'drupal') == $_GET['cron_key']) {
  drupal_cron_run();
}