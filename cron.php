<?php

include_once "includes/common.inc";

/*
** If not in 'safe mode', increase the maximum execution time:
*/

if (!get_cfg_var("safe_mode")) { 
  set_time_limit(180);
}

/*
** Iterate through the modules calling their cron handlers (if any):
*/

foreach (module_list() as $module) {
  module_invoke($module, "cron");
}

?>