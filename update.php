<?php
/*
** USAGE:
**
** - Point your browser to "http://www.site.com/update.php" and follow
**   the instructions.
**
*/

if (!get_cfg_var("safe_mode")) {
  set_time_limit(180);
}

// Define the various updates in an array("date : comment" => "function");
$mysql_updates = array(
  "2001-10-10" => "update_1",
  "2001-10-12 : pearification" => "update_2",
  "2001-10-14" => "update_3",
  "2001-10-16" => "update_4",
  "2001-10-17" => "update_5",
  "2001-10-22" => "update_6",
  "2001-11-01" => "update_7",
  "2001-11-02" => "update_8",
  "2001-11-04" => "update_9",
  "2001-11-17 : distributed authentication" => "update_10",
  "2001-12-01" => "update_11",
  "2001-12-06" => "update_12",
  "2001-12-09" => "update_13",
  "2001-12-16" => "update_14",
  "2001-12-24" => "update_15",
  "2001-12-30" => "update_16",
  "2001-12-31" => "update_17",
  "2002-01-05" => "update_18",
  "2002-01-17" => "update_19",
  "2002-01-27" => "update_20",
  "2002-01-30" => "update_21",
  "2002-02-19" => "update_22",
  "2002-03-05" => "update_23",
  "2002-04-08" => "update_24",
  "2002-04-14 : modules/themes web config" => "update_25",
  "2002-04-14 : new taxonomy system" => "update_26",
  "2002-04-16" => "update_27",
  "2002-04-20" => "update_28",
  "2002-04-23 : roles cleanup" => "update_29",
  "2002-05-02" => "update_30",
  "2002-05-15" => "update_31",
  "2002-06-22" => "update_32",
  "2002-07-07" => "update_33",
  "2002-07-31" => "update_34",
  "2002-08-10" => "update_35"
);

// Update functions
function update_1() {
  update_sql("ALTER TABLE users DROP INDEX real_email;");
  update_sql("ALTER TABLE users DROP fake_email;");
  update_sql("ALTER TABLE users DROP nodes;");
  update_sql("ALTER TABLE users DROP bio;");
  update_sql("ALTER TABLE users DROP hash;");
  update_sql("ALTER TABLE users ADD jabber varchar(128) DEFAULT '' NULL;");
  update_sql("ALTER TABLE users ADD drupal varchar(128) DEFAULT '' NULL;");
  update_sql("ALTER TABLE users ADD init varchar(64) DEFAULT '' NULL;");
  update_sql("ALTER TABLE users CHANGE passwd pass varchar(32) DEFAULT '' NOT NULL;");
  update_sql("ALTER TABLE users CHANGE real_email mail varchar(64) DEFAULT '' NULL;");
  update_sql("ALTER TABLE users CHANGE id uid int(10) unsigned DEFAULT '0' NOT NULL auto_increment;");
  update_sql("ALTER TABLE users CHANGE url homepage varchar(128) DEFAULT '' NOT NULL;");
  update_sql("UPDATE users SET status = 1 WHERE status = 2;");
  update_sql("UPDATE users SET name = userid;");
  update_sql("ALTER TABLE users DROP userid;");
  update_sql("UPDATE users SET init = mail;");
  update_sql("DROP TABLE access;");
  update_sql("CREATE TABLE access (aid tinyint(10) DEFAULT '0' NOT NULL auto_increment, mask varchar(255) DEFAULT '' NOT NULL, type varchar(255) DEFAULT '' NOT NULL, status tinyint(2) DEFAULT '0' NOT NULL, UNIQUE mask (mask), PRIMARY KEY (aid));");
  update_sql("CREATE TABLE moderate (cid int(10) DEFAULT '0' NOT NULL, nid int(10) DEFAULT '0' NOT NULL, uid int(10) DEFAULT '0' NOT NULL, score int(2) DEFAULT '0' NOT NULL, timestamp int(11) DEFAULT '0' NOT NULL, INDEX (cid), INDEX (nid) );");
  update_sql("ALTER TABLE comments DROP score;");
  update_sql("ALTER TABLE comments DROP votes;");
  update_sql("ALTER TABLE comments DROP users;");
}

function update_2() {
  update_sql("ALTER TABLE users CHANGE pass pass varchar(32) DEFAULT '' NOT NULL;");
  update_sql("ALTER TABLE rating CHANGE user userid int(10) DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE layout CHANGE user userid int(10) DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE blocks CHANGE offset delta tinyint(2) DEFAULT '0' NOT NULL;");
  foreach (module_list() as $name) {
    if (module_hook($name, "node", "name")) {
      $output .= "$name ...";
      db_query("DROP TABLE IF EXISTS ". $name ."_seq");
      db_query("CREATE TABLE ". $name ."_seq (id INTEGER UNSIGNED AUTO_INCREMENT NOT NULL, PRIMARY KEY(id))");
      $result = db_query("SELECT MAX(". ($name == "node" ? "nid" : "lid") .") FROM $name");
      $count = $result ? db_result($result, 0) : 0;
      db_query("INSERT INTO ". $name ."_seq (id) VALUES ('$count')");
      $output .= "done ($count)<br />";
    }
  }
  print $output;
}

function update_3() {
  update_sql("ALTER TABLE user RENAME users;");
  update_sql("ALTER TABLE locales CHANGE id lid int(10) DEFAULT '0' NOT NULL auto_increment;");
  update_sql("ALTER TABLE layout CHANGE userid uid int(10) DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE rating CHANGE userid uid int(10) DEFAULT '0' NOT NULL;");
}

function update_4() {
  print "remove the \"auto_increment\"s:<br />";
  update_sql("ALTER TABLE story CHANGE nid nid int(10) unsigned DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE blog CHANGE nid nid int(10) unsigned DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE page CHANGE nid nid int(10) unsigned DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE forum CHANGE nid nid int(10) unsigned DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE book CHANGE nid nid int(10) unsigned DEFAULT '0' NOT NULL;");

  print "drop the \"lid\"s:<br />";
  update_sql("ALTER TABLE story DROP lid;");
  update_sql("ALTER TABLE blog DROP lid;");
  update_sql("ALTER TABLE page DROP lid;");
  update_sql("ALTER TABLE forum DROP lid;");
  update_sql("ALTER TABLE book DROP lid;");

  print "rename \"author\" to \"uid\":<br />";
  update_sql("ALTER TABLE comments CHANGE author uid int(10) DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE node CHANGE author uid int(10) DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE node DROP KEY author;");
  update_sql("ALTER TABLE node ADD KEY uid (uid);");

  print "resize some \"id\"s:<br />";
  update_sql("ALTER TABLE feed CHANGE fid fid int(10) NOT NULL auto_increment;");
  update_sql("ALTER TABLE bundle CHANGE bid bid int(10) NOT NULL auto_increment;");
  update_sql("ALTER TABLE item CHANGE iid iid int(10) NOT NULL auto_increment;");
  update_sql("ALTER TABLE item CHANGE fid fid int(10) NOT NULL;");
  update_sql("ALTER TABLE comments CHANGE cid cid int(10) NOT NULL auto_increment;");
  update_sql("ALTER TABLE comments CHANGE pid pid int(10) NOT NULL;");
  update_sql("ALTER TABLE comments CHANGE lid lid int(10) NOT NULL;");
}

function update_5() {
  print "add primary keys:<br />";
  update_sql("ALTER TABLE story ADD PRIMARY KEY nid (nid);");
  update_sql("ALTER TABLE blog ADD PRIMARY KEY nid (nid);");
  update_sql("ALTER TABLE page ADD PRIMARY KEY nid (nid);");
  update_sql("ALTER TABLE forum ADD PRIMARY KEY nid (nid);");
  update_sql("ALTER TABLE book ADD PRIMARY KEY nid (nid);");

}

function update_6() {
  print "add new field to blocks:<br />";
  update_sql("ALTER TABLE blocks ADD path varchar(255) NOT NULL DEFAULT '';");
}

function update_7() {
  print "updating the story table:<br />";
  update_sql("UPDATE story SET body = CONCAT(abstract, '\n\n', body)");

  print "rename the body fields:<br />";
  update_sql("ALTER TABLE story CHANGE body body_old TEXT DEFAULT '' NOT NULL;");
  update_sql("ALTER TABLE page CHANGE body body_old TEXT DEFAULT '' NOT NULL;");
  update_sql("ALTER TABLE blog CHANGE body body_old TEXT DEFAULT '' NOT NULL;");
  update_sql("ALTER TABLE forum CHANGE body body_old TEXT DEFAULT '' NOT NULL;");
  update_sql("ALTER TABLE book CHANGE body body_old TEXT DEFAULT '' NOT NULL;");

  print "update the node table:<br />";
  update_sql("ALTER TABLE node DROP lid;");
  update_sql("ALTER TABLE node ADD teaser TEXT DEFAULT '' NOT NULL;");
  update_sql("ALTER TABLE node ADD body TEXT DEFAULT '' NOT NULL;");
  update_sql("ALTER TABLE node ADD changed int(11) DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE node CHANGE timestamp created int(11) DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE node CHANGE comment comment int(2) DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE node CHANGE promote promote int(2) DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE node CHANGE moderate moderate int(2) DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE node DROP timestamp_posted;");
  update_sql("ALTER TABLE node DROP timestamp_queued;");
  update_sql("ALTER TABLE node DROP timestamp_hidden;");
  update_sql("UPDATE node SET status = 0 WHERE status = 1;");
  update_sql("UPDATE node SET status = 0 WHERE status = 2;");
  update_sql("UPDATE node SET status = 1 WHERE status = 3;");

  $result = db_query("SELECT nid,type FROM node WHERE type = 'story' OR type = 'page' OR type = 'blog' OR type = 'forum' OR type = 'book'");
  include_once("modules/node.module");

  while ($object = db_fetch_object($result)) {

    include_once("modules/$object->type.module");
    $node = node_load(array("nid" => $object->nid));

    $old = db_fetch_object(db_query("SELECT * FROM $node->type WHERE nid = $node->nid"));

    switch ($node->type) {
      case "forum":
      case "story":
      case "book":
      case "page":
      case "blog":
        node_save($node, array("nid", "body" => $old->body_old, "teaser" => ($old->abstract ? $old->abstract : node_teaser($old->body_old))));
        print "updated node $node->nid '$node->title' ($node->type)<br />";
        break;
      default:
        print "unknown node $node->nid '$node->title' ($node->type)<br />";
    }

    unset($node);
    unset($body);
  }

  update_sql("UPDATE node SET changed = created;");
  update_sql("ALTER TABLE story DROP abstract;");
  update_sql("ALTER TABLE book DROP section;");
}

function update_8() {
  update_sql("ALTER TABLE node ADD revisions TEXT DEFAULT '' NOT NULL;");
}

function update_9() {
  update_sql("ALTER TABLE book ADD revision int(2) DEFAULT '1' NOT NULL;");
  update_sql("ALTER TABLE book DROP log;");
  update_sql("ALTER TABLE book DROP pid;");

  // remove book pages that used to be 'expired':
  $result = db_query("SELECT n.nid, n.title FROM node n WHERE n.type = 'book' AND n.status = 0");
  while ($node = db_fetch_object($result)) {
    print "removing node $node->nid '$node->title' (dumped node)<br />";
    db_query("DELETE FROM node WHERE nid = '$node->nid'");
    db_query("DELETE FROM book WHERE nid = '$node->nid'");
    db_query("DELETE FROM comments WHERE lid = '$node->nid'");
  }
}

function update_10() {
  // create a new table:
  update_sql("CREATE TABLE authmap (
      aid int(10) unsigned DEFAULT '0' NOT NULL auto_increment,
      uid int(10) DEFAULT '' NOT NULL,
      authname varchar(128) DEFAULT '' NOT NULL,
      module varchar(128) DEFAULT '' NOT NULL,
      UNIQUE authname (authname),
      PRIMARY KEY (aid)
    );");

  // populate the new table:
  $result = db_query("SELECT uid, name, jabber, drupal FROM users WHERE jabber != '' || drupal != ''");
  while ($user = db_fetch_object($result)) {
    if ($user->jabber) {
      update_sql("INSERT INTO authmap (uid, authname, module) VALUES ('$user->uid', '$user->jabber', 'jabber')");
    }
    if ($user->drupal) {
      update_sql("INSERT INTO authmap (uid, authname, module) VALUES ('$user->uid', '$user->drupal', 'drupal')");
    }
  }

  // remove the old user-table leftovers:
  update_sql("DELETE FROM variable WHERE name = 'user_jabber'");
  update_sql("DELETE FROM variable WHERE name = 'user_drupal'");
  update_sql("ALTER TABLE users DROP drupal");
  update_sql("ALTER TABLE users DROP jabber");

  // remove the old node-table leftovers:
  update_sql("ALTER TABLE forum DROP body_old;");
  update_sql("ALTER TABLE story DROP body_old;");
  update_sql("ALTER TABLE book DROP body_old;");
  update_sql("ALTER TABLE page DROP body_old;");
  update_sql("ALTER TABLE blog DROP body_old;");
}

function update_11() {
  update_sql("ALTER TABLE users ADD session TEXT;");
  update_sql("ALTER TABLE users ADD sid varchar(32) DEFAULT '' NOT NULL;");
}

function update_12() {
  update_sql("ALTER TABLE book DROP revision;");
  update_sql("ALTER TABLE book ADD format tinyint(2) DEFAULT '0';");
}

function update_13() {
  update_sql("ALTER TABLE referer RENAME AS referrer;");
  update_sql("DROP TABLE blog;");
  update_sql("DROP TABLE story;");
  update_sql("DROP TABLE forum;");
}

function update_14() {
  update_sql("CREATE TABLE directory (
    link varchar(255) DEFAULT '' NOT NULL,
    name varchar(128) DEFAULT '' NOT NULL,
    mail varchar(128) DEFAULT '' NOT NULL,
    slogan text NOT NULL,
    mission text NOT NULL,
    timestamp int(11) DEFAULT '0' NOT NULL,
    PRIMARY KEY (link)
  );");
}

function update_15() {
  update_sql("ALTER TABLE feed DROP uncache;");
}

function update_16() {
  update_sql("ALTER TABLE comments CHANGE lid nid int(10) NOT NULL;");
}

function update_17() {
  update_sql("CREATE TABLE history (
    uid int(10) DEFAULT '0' NOT NULL,
    nid int(10) DEFAULT '0' NOT NULL,
    timestamp int(11) DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid, nid)
  );");
}

function update_18() {
  update_sql("ALTER TABLE cache CHANGE timestamp expire int(11) DEFAULT '0' NOT NULL;");
  update_sql("ALTER TABLE cache CHANGE url cid varchar(255) DEFAULT '' NOT NULL;");
}

function update_19() {
  update_sql("ALTER TABLE users ADD data TEXT;");
}

function update_20() {
  update_sql("INSERT INTO blocks SET name='User information', module='user', delta='0', status='2', weight='0', region='1', remove='0', path='';");
}

function update_21() {
  update_sql("ALTER TABLE node ADD static int(2) DEFAULT '0' NOT NULL;");
}

function update_22() {
  update_sql("ALTER TABLE cache MODIFY data MEDIUMTEXT;");
}

function update_23() {
  update_sql("CREATE TABLE search_index (word varchar(50) default NOT NULL, lno int(10) unsigned default NOT NULL, type varchar(16) default NOT NULL, count int(10) unsigned default NOT NULL, KEY lno (lno), KEY word (word));");
}

function update_24() {
  update_sql("ALTER TABLE site ADD refresh int(11) NOT NULL;");
  update_sql("ALTER TABLE site ADD threshold int(11) NOT NULL;");
  update_sql("UPDATE site SET refresh = '7200';");
  update_sql("UPDATE site SET threshold = '60';");
}

function update_25() {
  update_sql("UPDATE users SET theme = LOWER(theme);");
}

function update_26() {
  update_sql("CREATE TABLE vocabulary (
    vid int UNSIGNED NOT NULL PRIMARY KEY auto_increment,
    name varchar(255) NOT NULL,
    description TEXT,
    relations TINYINT UNSIGNED NOT NULL,
    hierarchy TINYINT UNSIGNED NOT NULL,
    multiple TINYINT UNSIGNED NOT NULL,
    required TINYINT UNSIGNED NOT NULL,
    types TEXT,
    weight TINYINT NOT NULL);");

  update_sql("CREATE TABLE term_data (
    tid int UNSIGNED NOT NULL PRIMARY KEY auto_increment,
    vid int UNSIGNED NOT NULL,
    name varchar(255) NOT NULL,
    description TEXT,
    weight TINYINT NOT NULL);");

  update_sql("CREATE TABLE term_hierarchy (
    tid int UNSIGNED NOT NULL,
    parent int UNSIGNED NOT NULL
  );");

  update_sql("CREATE TABLE term_relation (
    tid1 int UNSIGNED NOT NULL,
    tid2 int UNSIGNED NOT NULL
  );");

  update_sql("CREATE TABLE term_synonym (
    tid int UNSIGNED NOT NULL,
    name varchar(255) NOT NULL
  );");

  update_sql("CREATE TABLE term_node (
    nid int UNSIGNED NOT NULL,
    tid int UNSIGNED NOT NULL
  );");

  update_sql("ALTER TABLE term_data ADD INDEX (vid);");
  update_sql("ALTER TABLE term_hierarchy ADD INDEX (tid);");
  update_sql("ALTER TABLE term_hierarchy ADD INDEX (parent);");
  update_sql("ALTER TABLE term_relation ADD INDEX (tid1);");
  update_sql("ALTER TABLE term_relation ADD INDEX (tid2);");
  update_sql("ALTER TABLE term_synonym ADD INDEX (tid);");
  update_sql("ALTER TABLE term_synonym ADD INDEX (name(3));");
  update_sql("ALTER TABLE term_node ADD INDEX (nid);");
  update_sql("ALTER TABLE term_node ADD INDEX (tid);");
  update_sql("UPDATE node SET comment = 2 WHERE comment = 1");
}

function update_27() {
  update_sql("ALTER TABLE book ADD log TEXT;");
}

function update_28() {
  update_sql("ALTER TABLE poll DROP lid;");
}

function update_29() {
  update_sql("INSERT INTO permission (rid, perm) SELECT rid, perm FROM role");

  $result = db_query("SELECT rid, name FROM role");
  while ($role = db_fetch_object($result)) {
    update_sql("UPDATE users SET rid = ". $role->rid ." WHERE role = '". $role->name ."'");
  }

  update_sql("ALTER TABLE users DROP role");
  update_sql("ALTER TABLE role DROP perm");
}

function update_30() {
  update_sql("ALTER TABLE blocks ADD custom tinyint(2) not null;");
  update_sql("UPDATE blocks SET module = 'block' WHERE module = 'boxes';");
  update_sql("UPDATE blocks SET status = 1, custom = 1 WHERE status = 1;");
  update_sql("UPDATE blocks SET status = 1, custom = 0 WHERE status = 2;");
}

function update_31() {
  include_once("modules/taxonomy.module");

  print "Wiping tables.<br />";
  /*db_query("DELETE FROM vocabulary");
  db_query("DELETE FROM term_data");
  db_query("DELETE FROM term_node");
  db_query("DELETE FROM term_hierarchy");**/

  print "Creating collections.<br />";
  $offset = db_result(db_query("SELECT MAX(vid) AS high FROM vocabulary"), 0);
  $result = db_query("SELECT * FROM collection");
  while ($c = db_fetch_object($result)) {
    $offset++;
    $collections[$c->name] = $offset;
    db_query("INSERT INTO vocabulary SET vid = '$offset', name = '$c->name', types = '". str_replace(" ", "", $c->types) ."'");
  }

  print "Creating terms.<br />";
  $result = db_query("SELECT * FROM tag");
  $i = db_result(db_query("SELECT MAX(tid) AS high FROM term_data"), 0) + 1;
  while ($t = db_fetch_object($result)) {
    foreach (explode(", ", $t->collections) as $c) {
      if ($collections[$c]) {
        db_query("INSERT INTO term_data SET tid = '$i', vid = '$collections[$c]', name = '$t->name'");
        db_query("INSERT INTO term_hierarchy SET tid = '$i', parent = '0'");
        $terms[$t->name] = $i;
        $i++;
      }
    }
  }

  print "Linking nodes with terms.<br />";
  $result = db_query("SELECT nid,attributes FROM node WHERE attributes != ''");
  while ($node = db_fetch_object($result)) {
    $tag = db_fetch_object(db_query("SELECT name FROM tag WHERE attributes = '$node->attributes'"));
    $tag = trim($tag->name);
    if ($tag) {
      if ($terms[$tag]) {
        db_query("INSERT INTO term_node SET nid = '$node->nid', tid = '$terms[$tag]'");
      }
      else {
        $errors[$tag] = "$tag";
      }
    }
  }

  if (count($errors)) {
    asort($errors);
    print "<br /><br />Terms not found:<br /><pre>  ". implode("\n  ", $errors) ."</pre>";
  }

  // Clean up meta tag system
  update_sql("DROP TABLE collection");
  update_sql("DROP TABLE tag");
  update_sql("ALTER TABLE node DROP attributes");
}

function update_32() {
  update_sql("ALTER TABLE users ADD index (sid(4));");
  update_sql("ALTER TABLE users ADD index (timestamp);");
}

function update_33() {
  $result = db_query("SELECT * FROM variable WHERE value NOT LIKE 's:%;';");
  // NOTE: the "WHERE"-part of the query above avoids variables to get serialized twice.
  while ($variable = db_fetch_object($result)) {
    variable_set($variable->name, $variable->value);
  }
}

function update_34() {
  update_sql("ALTER TABLE feed MODIFY refresh int(10) NOT NULL default '0';");
  update_sql("ALTER TABLE feed MODIFY timestamp int (10) NOT NULL default '0';");
}

function update_35() {
  update_sql("ALTER TABLE poll_choices ADD INDEX (nid);");
}

function update_upgrade3() {
  update_sql("INSERT INTO system VALUES ('archive.module','archive','module','',1);");
  update_sql("INSERT INTO system VALUES ('block.module','block','module','',1);");
  update_sql("INSERT INTO system VALUES ('blog.module','blog','module','',1);");
  update_sql("INSERT INTO system VALUES ('book.module','book','module','',1);");
  update_sql("INSERT INTO system VALUES ('cloud.module','cloud','module','',1);");
  update_sql("INSERT INTO system VALUES ('comment.module','comment','module','',1);");
  update_sql("INSERT INTO system VALUES ('forum.module','forum','module','',1);");
  update_sql("INSERT INTO system VALUES ('help.module','help','module','',1);");
  update_sql("INSERT INTO system VALUES ('import.module','import','module','',1);");
  update_sql("INSERT INTO system VALUES ('locale.module','locale','module','',1);");
  update_sql("INSERT INTO system VALUES ('node.module','node','module','',1);");
  update_sql("INSERT INTO system VALUES ('page.module','page','module','',1);");
  update_sql("INSERT INTO system VALUES ('poll.module','poll','module','',1);");
  update_sql("INSERT INTO system VALUES ('queue.module','queue','module','',1);");
  update_sql("INSERT INTO system VALUES ('rating.module','rating','module','',1);");
  update_sql("INSERT INTO system VALUES ('search.module','search','module','',1);");
  update_sql("INSERT INTO system VALUES ('statistics.module','statistics','module','',1);");
  update_sql("INSERT INTO system VALUES ('story.module','story','module','',1);");
  update_sql("INSERT INTO system VALUES ('taxonomy.module','taxonomy','module','',1);");
  update_sql("INSERT INTO system VALUES ('themes/example/example.theme','example','theme','Internet explorer, Netscape, Opera, Lynx',1);");
  update_sql("INSERT INTO system VALUES ('themes/goofy/goofy.theme','goofy','theme','Internetexplorer, Netscape, Opera',1);");
  update_sql("INSERT INTO system VALUES ('themes/marvin/marvin.theme','marvin','theme','Internet explorer, Netscape, Opera',1);");
  update_sql("INSERT INTO system VALUES ('themes/unconed/unconed.theme','unconed','theme','Internet explorer, Netscape, Opera',1);");
  update_sql("INSERT INTO system VALUES ('tracker.module','tracker','module','',1);");
  update_sql("REPLACE variable SET value = 'marvin', name = 'theme_default';");
  update_sql("REPLACE blocks SET name = 'User information', module = 'user', delta = '0', status = '1';");
  update_sql("REPLACE blocks SET name = 'Log in', module = 'user', delta = '1', status = '1';");
}

/*
** System functions
*/

function update_sql($sql) {
  global $edit;
  print nl2br(htmlentities($sql)) ." ";
  $result = db_query($sql);
  if ($result) {
    print "<font color=\"green\">OK</font>\n";
    return 1;
  }
  else {
    print "<font color=\"red\">FAILED</font>\n";
    if ($edit["bail"]) {
      die("Fatal error. Bailing");
    }
    return 0;
  }
}

function update_data($start) {
  global $mysql_updates;
  $mysql_updates = array_slice($mysql_updates, ($start-- ? $start : 0));
  foreach ($mysql_updates as $date => $func) {
    print "<b>$date</b><br />\n<pre>\n";
    $func();
    variable_set("update_start", $date);
    print "</pre>\n";
  }
}

function update_page() {
  global $op, $edit, $user, $mysql_updates;

  switch ($op) {
    case "Update":
      // make sure we have updates to run.
      print "<html><h1>Drupal update</h1>";
      print "<b>&raquo; <a href=\"index.php\">home</a></b><br />\n";
      print "<b>&raquo; <a href=\"admin.php\">administer</a></b><br />\n";
      if ($edit["start"] == -1) {
        print "No updates to perform.";
      }
      else {
        update_data($edit["start"]);
      }
      print "</html>";
      break;
    case "upgrade3":
      // make sure we have updates to run.
      print "<html><h1>Drupal upgrade</h1>";
      print "<b>&raquo; <a href=\"index.php\">home</a></b><br />\n";
      print "<b>&raquo; <a href=\"admin.php\">administer</a></b><br />\n";
      if ($edit["start"] == -1) {
        print "No updates to perform.";
      }
      else {
        update_data($edit["start"]);
      }
      print "<pre>\n";
      update_upgrade3();
      print "</pre>\n";
      print "</html>";
      break;
    default:
      $start = variable_get("update_start", 0);
      $dates[] = "All";
      $i = 1;
      foreach ($mysql_updates as $date => $sql) {
        $dates[$i++] = $date;
        if ($date == $start) {
          $selected = $i;
        }
      }
      $dates[$i] = "None";

      // make update form and output it.
      $form .= form_select("Perform updates since", "start", (isset($selected) ? $selected : -1), $dates);
      $form .= form_select("Stop on errors", "bail", 0, array("Disabled", "Enabled"), "Don't forget to backup your database before performing an update.");
      $form .= form_submit("Update");
      print "<html><h1>Drupal update</h1>";
      print form($form);
      print "</html>";
      break;
  }
}

function update_info() {
  print "<html><h1>Drupal update</h1>";
  print "<h2>Instructions</h2>\n";
  print "<ol>\n";
  print "<li><p>Before doing anything backup your database. This process will change your database and its values, and some things might get lost.</p></li>\n";
  print "<li><p>Don't run this script twice as it will cause some serious problems!</p></li>\n";
  print "<li>Before doing anything else these queries have to be run manually:<br />\n";
  print "<pre>\n";
  print "ALTER TABLE watchdog CHANGE user uid int(10) DEFAULT '0' NOT NULL;\n";
  print "ALTER TABLE watchdog CHANGE id wid int(5) DEFAULT '0' NOT NULL auto_increment;\n";
  print "ALTER TABLE users ADD sid varchar(32) DEFAULT '' NOT NULL;\n";
  print "ALTER TABLE users ADD session TEXT;\n";
  print "ALTER TABLE users CHANGE last_host hostname varchar(128) DEFAULT '' NOT NULL;\n";
  print "ALTER TABLE users CHANGE last_access timestamp int(11) DEFAULT '0' NOT NULL;\n";
  print "CREATE TABLE system (filename varchar(255) NOT NULL default '', name varchar(255) NOT NULL default '', type varchar(255) NOT NULL default '', description varchar(255) NOT NULL default '', status int(2) NOT NULL default '0', PRIMARY KEY (filename));\n";
  print "CREATE TABLE permission (rid INT UNSIGNED NOT NULL, perm TEXT, tid INT UNSIGNED NOT NULL, KEY (rid));\n";
  print "INSERT INTO permission (rid, perm) SELECT rid, perm FROM role;\n";
  print "ALTER TABLE users ADD rid INT UNSIGNED NOT NULL;\n";
  print "</pre></li>\n";
  print "<li><p>You might have to by-pass the access check near the bottom of the file called update.php such that you can gain access to the updates: search for <i>user_access()</i>.</p></li>";
  print "<li><p>";
  print "Choose one of the links below to either upgrade from Drupal 3.x or update from a CVS checkout.<br />";
  print "&raquo; upgrading will by default enable the standard Drupal themes and modules as well as setting some default values.<br />";
  print "&raquo; updating will require modules and themes enabled manually under <i>Administer | Site configuration | modules</i>.<br />";
  print "<p><b>&raquo; <a href=\"update.php?op=upgrade3\">Upgrade 3.x to 4.0.0</a></b></p>\n";
  print "<p><b>&raquo; <a href=\"update.php?op=update\">Update CVS database</a></b></p>\n";
  print "<p>Once you are done remove or disable access to update.php so nobody else can tamper with the database.</p>\n";
  print "</p></li>";
  print "<li><p>Go through the various administration pages to change the existing and new settings to your liking.</p></li>\n";
  print "<li><p>Thanks for using Drupal!</p></li>\n";
  print "</ol>";
  print "</html>";
}

if ($op) {
  include_once "includes/common.inc";
  // Security check:
  if (user_access(NULL) || variable_get("update_start", 0) == 0) {
    update_page();
  }
  else {
    print message_access();
  }
}
else {
  update_info();
}
?>