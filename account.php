<?php

include_once "includes/common.inc";

page_header();

function account_get_user($name) {
  return db_fetch_object(db_query("SELECT * FROM users WHERE name = '". check_input($name) ."'"));
}

function account_email_form() {
  global $REQUEST_URI;

  $output .= "<p>". t("Lost your password?  Fill out your username and e-mail address, and your password will be mailed to you.") ."</p>\n";

  $output .= form_textfield(t("Username"), "login", $edit[login], 30, 64, t("Enter your full name or username."));
  $output .= form_textfield(t("E-mail address"), "email", $edit[email], 30, 64, t("You will be sent a new password."));
  $output .= form_submit(t("E-mail new password"));

  return form($REQUEST_URI, $output);
}

function account_page() {
  global $theme;

  $theme->header();

  if (variable_get("account_register", 1)) {
    $theme->box(t("Create user account"), account_create_form());
  }

  if (variable_get("account_password", 1)) {
    $theme->box(t("E-mail new password"), account_email_form());
  }

  $theme->footer();
}

function account_create_form($edit = array(), $error = "") {
  global $theme, $REQUEST_URI;

  if ($error) {
    $output .= "<p><font color=\"red\">". t("Failed to create new account") .": ". check_output($error) ."</font></p>\n";
    watchdog("account", "failed to create new account: $error");
  }
  else {
    $output .= "<p>". t("Registering allows you to comment, to moderate comments and pending submissions, to customize the look and feel of the site and generally helps you interact with the site more efficiently.") ."</p><p>". t("To create an account, simply fill out this form an click the 'Create new account' button below.  An e-mail will then be sent to you with instructions on how to validate your account.") ."</p>\n";
  }

  $output .= form_textfield(t("Username"), "login", $edit[login], 30, 64, t("Enter your full name or username: only letters, numbers and common special characters like spaces are allowed."));
  $output .= form_textfield(t("E-mail address"), "email", $edit[email], 30, 64, t("You will be sent instructions on how to validate your account via this e-mail address: make sure it is accurate."));
  $output .= form_submit(t("Create new account"));

  return form($REQUEST_URI, $output);
}

function account_session_start($userid, $passwd) {
  global $user;

  if ($userid && $passwd) {
    $user = new User($userid, $passwd);
  }

  if ($user->id) {
    if ($rule = user_ban($user->userid, "username")) {
      watchdog("account", "failed to login for '$user->userid': banned by $rule->type rule '$rule->mask'");
    }
    else if ($rule = user_ban($user->last_host, "hostname")) {
      watchdog("account", "failed to login for '$user->userid': banned by $rule->type rule '$rule->mask'");
    }
    else {
      session_register("user");
      watchdog("account", "session opened for '$user->userid'");
    }
  }
  else {
    watchdog("account", "failed to login for '$userid': invalid password");
  }
}

function account_session_close() {
  global $user;
  watchdog("account", "session closed for user '$user->userid'");
  session_unset();
  session_destroy();
  unset($user);
}

function account_info_edit($error = 0) {
  global $theme, $user;

  if ($user->id) {

    if ($error) {
      $form .= "<p><font color=\"red\">$error</font></p>\n";
    }

    $form .= form_textfield(t("Username"), "userid", $user->userid, 30, 55, t("Required, a unique name that can be used to log on."));
    $form .= form_textfield(t("Name"), "name", $user->name, 30, 55, t("Required, a unique name displayed with your contributions."));
    $form .= form_item(t("Real e-mail address"), $user->real_email, t("Required, unique, can not be changed.") ." ". t("Your real e-mail address is never displayed publicly: only needed in case you lose your password."));
    $form .= form_textfield(t("Fake e-mail address"), "fake_email", $user->fake_email, 30, 55, t("Optional") .". ". t("Displayed publicly so you may spam proof your real e-mail address if you want."));
    $form .= form_textfield(t("Homepage"), "url", $user->url, 30, 55, t("Optional") .". ". t("Make sure you enter fully qualified URLs only.  That is, remember to include \"http://\"."));
    $form .= form_textarea(t("Bio"), "bio", $user->bio, 35, 5, t("Optional") .". ". t("Maximal 255 characters.") ." ". t("This biographical information is publicly displayed on your user page.") ."<BR>". t("Allowed HTML tags") .": ". htmlspecialchars(variable_get("allowed_html", "")));
    $form .= form_textarea(t("Signature"), "signature", $user->signature, 35, 5, t("Optional") .". ". t("Maximal 255 characters.") ." ". t("This information will be publicly displayed at the end of your comments.") ."<BR>". t("Allowed HTML tags") .": ". htmlspecialchars(variable_get("allowed_html", "")));
    $form .= form_item(t("Password"), "<INPUT TYPE=\"password\" NAME=\"edit[pass1]\" SIZE=\"10\" MAXLENGTH=\"20\"> <INPUT TYPE=\"password\" NAME=\"edit[pass2]\" SIZE=\"10\" MAXLENGTH=\"20\">", t("Enter your new password twice if you want to change your current password or leave it blank if you are happy with your current password."));
    $form .= form_submit(t("Save user information"));

    $theme->header();
    $theme->box(t("Edit user information"), form("account.php", $form));
    $theme->footer();
  }
  else {
    account_page();
  }
}

function account_info_save($edit) {
  global $user;

  if ($error = user_validate_name($edit[userid])) {
    return t("Invalid name") .": $error";
  }
  else if ($error = user_validate_name($edit[name])) {
    return t("Invalid name") .": $error";
  }
  else if (db_num_rows(db_query("SELECT userid FROM users WHERE id != '$user->id' AND (LOWER(userid) = LOWER('$edit[userid]') OR LOWER(name) = LOWER('$edit[userid]'))")) > 0) {
    return t("Invalid username") .": the username '$edit[userid]' is already taken.";
  }
  else if (db_num_rows(db_query("SELECT name FROM users WHERE id != '$user->id' AND (LOWER(userid) = LOWER('$edit[name]') OR LOWER(name) = LOWER('$edit[name]'))")) > 0) {
    return t("Invalid name") .": the name '$edit[name]' is already taken.";
  }
  else if ($user->id) {
    $user = user_save($user, array("userid" => $edit[userid], "name" => $edit[name], "fake_email" => $edit[fake_email], "url" => $edit[url], "bio" => $edit[bio], "signature" => $edit[signature]));
    if ($edit[pass1] && $edit[pass1] == $edit[pass2]) $user = user_save($user, array("passwd" => $edit[pass1]));
  }
}

function account_settings_edit() {
  global $cmodes, $corder, $theme, $themes, $languages, $user;

  if ($user->id) {
    foreach ($themes as $key=>$value) $options .= "<OPTION VALUE=\"$key\"". (($user->theme == $key) ? " SELECTED" : "") .">$key - $value[1]</OPTION>\n";
    $form .= form_item(t("Theme"), "<SELECT NAME=\"edit[theme]\">$options</SELECT>", t("Selecting a different theme will change the look and feel of the site."));
    for ($zone = -43200; $zone <= 46800; $zone += 3600) $zones[$zone] = date("l, F dS, Y - h:i A", time() - date("Z") + $zone) ." (GMT ". $zone / 3600 .")";
    $form .= form_select(t("Timezone"), "timezone", $user->timezone, $zones, t("Select what time you currently have and your timezone settings will be set appropriate."));
    $form .= form_select(t("Language"), "language", $user->language, $languages, t("Selecting a different language will change the language of the site."));
    $form .= form_select(t("Number of nodes to display"), "nodes", $user->nodes, array(10 => 10, 15 => 15, 20 => 20, 25 => 25, 30 => 30), t("The maximum number of nodes that will be displayed on the main page."));
    $form .= form_select(t("Comment display mode"), "mode", $user->mode, $cmodes);
    $form .= form_select(t("Comment display order"), "sort", $user->sort, $corder);
    for ($count = -1; $count < 6; $count++) $threshold[$count] = t("Filter") ." - $count";
    $form .= form_select(t("Comment filter"), "threshold", $user->threshold, $threshold, t("Comments that scored less than this threshold setting will be ignored.  Anonymous comments start at 0, comments of people logged on start at 1 and moderators can add and subtract points."));
    $form .= form_submit(t("Save site settings"));

    $theme->header();
    $theme->box(t("Edit your preferences"), form("account.php", $form));
    $theme->footer();
  }
  else {
    account_page();
  }
}

function account_settings_save($edit) {
  global $user;

  if ($user->id) {
    $user = user_save($user, array("theme" => $edit[theme], "timezone" => $edit[timezone], "language" => $edit[language], "nodes" => $edit[nodes], "mode" => $edit[mode], "sort" => $edit[sort], "threshold" => $edit[threshold]));
  }

}

function account_blocks_edit() {
  global $theme, $user;

  if ($user->id) {
    // construct form:
    $result = db_query("SELECT * FROM blocks WHERE status = 1 ORDER BY module");
    while ($block = db_fetch_object($result)) {
      $entry = db_fetch_object(db_query("SELECT * FROM layout WHERE block = '". check_input($block->name) ."' AND user = '$user->id'"));
      $options .= "<input type=\"checkbox\" name=\"edit[$block->name]\"". ($entry->user ? " checked=\"checked\"" : "") ." /> ". t($block->name) ."<br />\n";
    }

    $form .= form_item(t("Blocks in side bars"), $options, t("Enable the blocks you would like to see displayed in the side bars."));
    $form .= form_submit(t("Save block settings"));

    // display form:
    $theme->header();
    $theme->box(t("Edit your content"), form("account.php", $form));
    $theme->footer();
  }
  else {
    account_page();
  }
}

function account_blocks_save($edit) {
  global $user;
  if ($user->id) {
    db_query("DELETE FROM layout WHERE user = '$user->id'");
    foreach (($edit ? $edit : array()) as $block=>$weight) {
      db_query("INSERT INTO layout (user, block) VALUES ('$user->id', '". check_input($block) ."')");
    }
  }
}

function account_user($name) {
  global $user, $theme;

  if ($user->id && $user->name == $name) {
    $output .= "<TABLE BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"2\">\n";
    $output .= " <TR><TD ALIGN=\"right\"><B>". t("Name") .":</B></TD><TD>". check_output($user->name) ."</TD></TR>\n";
    $output .= " <TR><TD ALIGN=\"right\"><B>". t("E-mail") .":</B></TD><TD>". format_email($user->fake_email) ."</A></TD></TR>\n";
    $output .= " <TR><TD ALIGN=\"right\"><B>". t("Homepage") .":</B></TD><TD>". format_url($user->url) ."</TD></TR>\n";
    $output .= " <TR><TD ALIGN=\"right\" VALIGN=\"top\"><B>". t("Bio") .":</B></TD><TD>". check_output($user->bio, 1) ."</TD></TR>\n";
    $output .= " <TR><TD ALIGN=\"right\" VALIGN=\"top\"><B>". t("Signature") .":</B></TD><TD>". check_output($user->signature, 1) ."</TD></TR>\n";
    $output .= "</TABLE>\n";

    // Display account information:
    $theme->header();
    $theme->box(t("Personal information"), $output);
    $theme->footer();
  }
  elseif ($name && $account = account_get_user($name)) {
    $theme->header();

    // Display account information:
    $output .= "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\">\n";
    $output .= " <TR><TD ALIGN=\"right\"><B>". t("Name") .":</B></TD><TD>". check_output($account->name) ."</TD></TR>\n";
    $output .= " <TR><TD ALIGN=\"right\"><B>". t("E-mail") .":</B></TD><TD>". format_email($account->fake_email) ."</TD></TR>\n";
    $output .= " <TR><TD ALIGN=\"right\"><B>". t("Homepage") .":</B></TD><TD>". format_url($account->url) ."</TD></TR>\n";
    $output .= " <TR><TD ALIGN=\"right\"><B>". t("Bio") .":</B></TD><TD>". check_output($account->bio) ."</TD></TR>\n";
    $output .= "</TABLE>\n";

    $theme->box(strtr(t("%a's user information"), array("%a" => $name)), $output);

    // Display contributions:
    if (user_access("access contents")) {
      $result = db_query("SELECT n.nid, n.type, n.title, n.timestamp, COUNT(c.cid) AS count FROM node n LEFT JOIN comments c ON c.lid = n.nid WHERE n.status = '". node_status("posted") ."' AND n.author = '$account->id' GROUP BY n.nid DESC ORDER BY n.nid DESC LIMIT 25");

      while ($node = db_fetch_object($result)) {
        $nodes .= "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\">\n";
        $nodes .= " <TR><TD ALIGN=\"right\" VALIGN=\"top\"><B>". t("Subject") .":</B></TD><TD><A HREF=\"node.php?id=$node->nid\">". check_output($node->title) ."</A> (". format_plural($node->count, "comment", "comments") .")</TD></TR>\n";
        $nodes .= " <TR><TD ALIGN=\"right\" VALIGN=\"top\"><B>". t("Type") .":</B></TD><TD>". check_output($node->type) ."</A></TD></TR>\n";
        $nodes .= " <TR><TD ALIGN=\"right\" VALIGN=\"top\"><B>". t("Date") .":</B></TD><TD>". format_date($node->timestamp) ."</TD></TR>\n";
        $nodes .= "</TABLE>\n";
        $nodes .= "<P>\n";
      }

      $theme->box(strtr(t("%a's contributions"), array("%a" => $name)), ($nodes ? $nodes : t("Not posted any nodes.")));
    }

    if (user_access("access comments")) {
      $sresult = db_query("SELECT n.nid, n.title, COUNT(n.nid) AS count FROM comments c LEFT JOIN node n ON c.lid = n.nid WHERE c.author = '$account->id' GROUP BY n.nid DESC ORDER BY n.nid DESC LIMIT 5");

      while ($node = db_fetch_object($sresult)) {
        $comments .= "<LI>". format_plural($node->count, "comment", "comments") ." ". t("attached to node") ." `<A HREF=\"node.php?id=$node->nid\">". check_output($node->title) ."</A>`:</LI>\n";
        $comments .= " <UL>\n";

        $cresult = db_query("SELECT * FROM comments WHERE author = '$account->id' AND lid = '$node->nid'");
        while ($comment = db_fetch_object($cresult)) {
          $comments .= "  <LI><A HREF=\"node.php?id=$node->nid&cid=$comment->cid&pid=$comment->pid#$comment->cid\">". check_output($comment->subject) ."</A> (". t("replies") .": ". comment_num_replies($comment->cid) .", ". t("votes") .": $comment->votes, ". t("score") .": ". comment_score($comment) .")</LI>\n";
        }
        $comments .= " </UL>\n";
      }

      $theme->box(strtr(t("%a's comments"), array("%a" => $name)), ($comments ? $comments : t("Not posted any comments.")));
    }

    $theme->footer();
  }
  else {
    account_page();
  }
}

function account_email_submit($edit) {
  global $theme;

  $result = db_query("SELECT id FROM users WHERE (userid = '". check_input($edit[login]) ."' OR name = '". check_input($edit[login]) ."') AND real_email = '". check_input($edit[email]) ."'");

  if ($account = db_fetch_object($result)) {

    /*
    ** Generate a password and a confirmation hash:
    */

    $passwd = user_password();
    $hash = substr(md5("$userid. ". time() .""), 0, 12);
    $status = 1;

    /*
    ** Update the user account in the database:
    */

    db_query("UPDATE users SET passwd = PASSWORD('$passwd'), hash = '$hash', status = '$status' WHERE userid = '". check_input($edit[login]) ."'");

    /*
    ** Send out an e-mail with the account details:
    */

    $link = path_uri() ."account.php?op=confirm&name=". urlencode($edit[login]) ."&hash=$hash";
    $subject = strtr(t("Account details for %a"), array("%a" => variable_get(site_name, "drupal")));
    $message = strtr(t("%a,\n\n\nyou requested us to e-mail you a new password for your account at %b.  You will need to re-confirm your account or you will not be able to login.  To confirm your account updates visit the URL below:\n\n   %c\n\nOnce confirmed you can login using the following username and password:\n\n   username: %a\n   password: %d\n\n\n-- %b team"), array("%a" => $edit[login], "%b" => variable_get(site_name, "drupal"), "%c" => $link, "%d" => $passwd));

    mail($email, $subject, $message, "From: noreply");

    watchdog("account", "new password: `$edit[login]' &lt;$edit[email]&gt;");

    $output = t("Your password and further instructions have been sent to your e-mail address.");
  }
  else {
    watchdog("account", "new password: '$edit[login]' and &lt;$edit[email]&gt; do not match");

    $output = t("Could not sent password: no match for the specified username and e-mail address.");
  }

  $theme->header();
  $theme->box(t("E-mail new password"), $output);
  $theme->footer();
}

function account_create_submit($edit) {
  global $theme, $HTTP_HOST, $REQUEST_URI;

  if (variable_get("account_register", 1)) {

    $theme->header();

    if ($error = user_validate_name($edit[login])) {
      $theme->box(t("Create user account"), account_create_form($edit, $error));
    }
    else if ($error = user_validate_mail($edit[email])) {
      $theme->box(t("Create user account"), account_create_form($edit, $error));
    }
    else if ($ban = user_ban($edit[login], "username")) {
      $theme->box(t("Create user account"), account_create_form($edit, t("the username '$edit[login]' is banned") .": <i>$ban->reason</i>."));
    }
    else if ($ban = user_ban($edit[real_email], "e-mail address")) {
      $theme->box(t("Create user account"), account_create_form($edit, t("the username '$edit[email]' is banned") .": <i>$ban->reason</i>."));
    }
    else if (db_num_rows(db_query("SELECT userid FROM users WHERE (LOWER(userid) = LOWER('$edit[login]') OR LOWER(name) = LOWER('$edit[login]'))")) > 0) {
      $theme->box(t("Create user account"), account_create_form($edit, t("the username '$edit[login]' is already taken.")));
    }
    else if (db_num_rows(db_query("SELECT real_email FROM users WHERE LOWER(real_email) = LOWER('$edit[email]')")) > 0) {
      $theme->box(t("Create user account"), account_create_form($edit, t("the e-mail address '$edit[email]' is already in use by another account.")));
    }
    else {

      /*
      ** Generate a password and a confirmation hash:
      */

      $edit[passwd] = user_password();
      $edit[hash] = substr(md5("$new[userid]. ". time()), 0, 12);

      /*
      ** Create the new user account in the database:
      */

      $user = user_save("", array("userid" => $edit[login], "name" => $edit[login], "real_email" => $edit[email], "passwd" => $edit[passwd], "role" => "authenticated user", "status" => 1, "hash" => $edit[hash]));

      /*
      ** Send out an e-mail with the account details:
      */

      $link = path_uri() ."account.php?op=confirm&name=". urlencode($edit[login]) ."&hash=$edit[hash]";
      $subject = strtr(t("Account details for %a"), array("%a" => variable_get(site_name, "drupal")));
      $message = strtr(t("%a,\n\n\nsomeone signed up for a user account on %b and supplied this e-mail address as their contact.  If it wasn't you, don't get your panties in a bundle and simply ignore this mail.  If this was you, you will have to confirm your account first or you will not be able to login.  To confirm your account visit the URL below:\n\n   %c\n\nOnce confirmed you can login using the following username and password:\n\n  username: %a\n   password: %d\n\n\n-- %b team\n"), array("%a" => $edit[login], "%b" => variable_get(site_name, "drupal"), "%c" => $link, "%d" => $edit[passwd]));

      mail($edit[email], $subject, $message, "From: noreply");

      watchdog("account", "new account: `$edit[login]' &lt;$edit[email]&gt;");

      $theme->box(t("Create user account"), t("Congratulations!  Your member account has been successfully created and further instructions on how to confirm your account have been sent to your e-mail address.  You have to confirm your account first or you will not be able to login."));
    }

    $theme->footer();
  }
}

function account_create_confirm($name, $hash) {
  global $theme;

  $result = db_query("SELECT userid, hash, status FROM users WHERE userid = '$name'");

  if ($account = db_fetch_object($result)) {
    if ($account->status == 1) {
      if ($account->hash == $hash) {
        db_query("UPDATE users SET status = '2', hash = '' WHERE userid = '$name'");
        $output = t("Your account has been successfully confirmed.");
        watchdog("account", "$name: account confirmation successful");
      }
      else {
        $output = t("Confirmation failed: invalid confirmation hash.");
        watchdog("warning", "$name: invalid confirmation hash");
      }
    }
    else {
      $output = t("Confirmation failed: your account has already been confirmed.");
      watchdog("warning", "$name: attempt to re-confirm account");
    }
  }
  else {
    $output = t("Confirmation failed: non-existing account.");
    watchdog("warning", "$name: attempt to confirm non-existing account");
  }

  $theme->header();
  $theme->box(t("Create user account"), $output);
  $theme->footer();
}

function account_track_comments() {
  global $theme, $user;

  $sresult = db_query("SELECT n.nid, n.title, COUNT(n.nid) AS count FROM comments c LEFT JOIN node n ON c.lid = n.nid WHERE c.author = '$user->id' GROUP BY n.nid DESC ORDER BY n.nid DESC LIMIT 5");

  while ($node = db_fetch_object($sresult)) {
    $output .= "<LI>". format_plural($node->count, "comment", "comments") ." ". t("attached to node") ." `<A HREF=\"node.php?id=$node->nid\">". check_output($node->title) ."</A>`:</LI>\n";
    $output .= " <UL>\n";

    $cresult = db_query("SELECT * FROM comments WHERE author = '$user->id' AND lid = '$node->nid'");
    while ($comment = db_fetch_object($cresult)) {
      $output .= "  <LI><A HREF=\"node.php?id=$node->nid&cid=$comment->cid&pid=$comment->pid#$comment->cid\">". check_output($comment->subject) ."</A> (". t("replies") .": ". comment_num_replies($comment->cid) .", ". t("votes") .": $comment->votes, ". t("score") .": ". comment_score($comment) .")</LI>\n";
    }
    $output .= " </UL>\n";
  }

  $theme->header();
  $theme->box(t("Track your comments"), ($output ? $output : t("You have not posted any comments recently.")));
  $theme->footer();
}

function account_track_contributions() {
  global $theme, $user;

  $result = db_query("SELECT n.nid, n.type, n.title, n.timestamp, COUNT(c.cid) AS count FROM node n LEFT JOIN comments c ON c.lid = n.nid WHERE n.status = '". node_status("posted") ."' AND n.author = '$user->id' GROUP BY n.nid DESC ORDER BY n.nid DESC LIMIT 25");

  while ($node = db_fetch_object($result)) {
    $output .= "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\">\n";
    $output .= " <TR><TD ALIGN=\"right\" VALIGN=\"top\"><B>". t("Subject") .":</B></TD><TD><A HREF=\"node.php?id=$node->nid\">". check_output($node->title) ."</A> (". format_plural($node->count, "comment", "comments") .")</TD></TR>\n";
    $output .= " <TR><TD ALIGN=\"right\" VALIGN=\"top\"><B>". t("Type") .":</B></TD><TD>". check_output($node->type) ."</A></TD></TR>\n";
    $output .= " <TR><TD ALIGN=\"right\" VALIGN=\"top\"><B>". t("Date") .":</B></TD><TD>". format_date($node->timestamp) ."</TD></TR>\n";
    $output .= "</TABLE>\n";
    $output .= "<P>\n";
  }

  $theme->header();
  $theme->box(t("Track your contributions"), ($output ? $output : t("You have not posted any nodes.")));
  $theme->footer();
}

function account_track_site() {
  global $theme, $user;

  $period = 259200; // 3 days

  $theme->header();

  $nresult = db_query("SELECT n.nid, n.title, COUNT(c.cid) AS count FROM comments c LEFT JOIN node n ON n.nid = c.lid WHERE n.status = '". node_status("posted") ."' AND c.timestamp > ". (time() - $period) ." GROUP BY c.lid ORDER BY count DESC");
  while ($node = db_fetch_object($nresult)) {
    $output .= "<LI>". format_plural($node->count, "comment", "comments") ." ". t("attached to") ." '<A HREF=\"node.php?id=$node->nid\">". check_output($node->title) ."</A>':</LI>";

    $cresult = db_query("SELECT c.subject, c.cid, c.pid, u.userid, u.name FROM comments c LEFT JOIN users u ON u.id = c.author WHERE c.lid = $node->nid ORDER BY c.timestamp DESC LIMIT $node->count");
    $output .= "<UL>\n";
    while ($comment = db_fetch_object($cresult)) {
      $output .= " <LI>'<A HREF=\"node.php?id=$node->nid&cid=$comment->cid&pid=$comment->pid#$comment->cid\">". check_output($comment->subject) ."</A>' ". t("by") ." ". format_name($comment->name) ."</LI>\n";
    }
    $output .= "</UL>\n";
  }

  $theme->box(t("Recent comments"), ($output ? $output : t("No comments recently.")));

  unset($output);

  $result = db_query("SELECT n.title, n.nid, n.type, n.status, u.userid, u.name FROM node n LEFT JOIN users u ON n.author = u.id WHERE ". time() ." - n.timestamp < $period ORDER BY n.timestamp DESC LIMIT 10");

  if (db_num_rows($result)) {
    $output .= "<TABLE BORDER=\"0\" CELLSPACING=\"4\" CELLPADDING=\"4\">\n";
    $output .= " <TR><TH>". t("Subject") ."</TH><TH>". t("Author") ."</TH><TH>". t("Type") ."</TH><TH>". t("Status") ."</TH></TR>\n";
    while ($node = db_fetch_object($result)) {
      $output .= " <TR><TD><A HREF=\"node.php?id=$node->nid\">". check_output($node->title) ."</A></TD><TD ALIGN=\"center\">". format_name($node->name) ."</TD><TD ALIGN=\"center\">$node->type</TD><TD>". node_status($node->status) ."</TD></TR>";
    }
    $output .= "</TABLE>";
  }

  $theme->box(t("Recent nodes"), ($output ? $output : t("No nodes recently.")));

  $theme->footer();
}

switch ($op) {
  case t("E-mail new password"):
    account_email_submit($edit);
    break;
  case t("Create new account"):
    account_create_submit($edit);
    break;
  case t("Save user information"):
    if ($error = account_info_save($edit)) {
      account_info_edit($error);
    }
    else {
      account_user($user->name);
    }
    break;
  case t("Save site settings"):
    account_settings_save($edit);
    header("Location: account.php?op=info");
    break;
  case t("Save block settings"):
    account_blocks_save($edit);
    account_user($user->name);
    break;
  case "confirm":
    account_create_confirm(check_input($name), check_input($hash));
    break;
  case "login":
    account_session_start(check_input($userid), check_input($passwd));
    header("Location: account.php?op=info");
    break;
  case "logout":
    account_session_close();
    header("Location: account.php?op=info");
    break;
  case "view":
    switch ($type) {
      case "information":
        account_user($user->name);
        break;
      case "site":
        account_track_site();
        break;
      case "contributions":
        account_track_contributions();
        break;
      case "comments":
        account_track_comments();
        break;
      default:
        account_user(check_input($name));
    }
    break;
  case "edit":
    switch ($type) {
      case "blocks":
        account_blocks_edit();
        break;
      case "settings":
        account_settings_edit();
        break;
      default:
        account_info_edit();
    }
    break;
  default:
    account_user($user->name);
}

page_footer();

?>