<?

include "includes/common.inc";

function story_render($id, $cid) {
  global $theme, $threshold, $mode, $order, $user;

  // Compose story query:
  $result = db_query("SELECT s.*, u.userid FROM stories s LEFT JOIN users u ON s.author = u.id WHERE s.status != 0 AND s.id = $id");
  $story = db_fetch_object($result);

  // Display story:
  if ($story->status == 1) $theme->article($story, "[ <A HREF=\"submission.php\"><FONT COLOR=\"$theme->hlcolor2\">submission queue</FONT></A> | <A HREF=\"story.php?op=reply&id=$story->id&pid=0\"><FONT COLOR=\"$theme->hlcolor2\">add a comment</FONT></A> ]");
  else $theme->article($story, "[ <A HREF=\"\"><FONT COLOR=\"$theme->hlcolor2\">home</FONT></A> | <A HREF=\"story.php?op=reply&id=$story->id&pid=0\"><FONT COLOR=\"$theme->hlcolor2\">add a comment</FONT></A> ]");

  // Display 'comment control'-box:
  if ($user->id) $theme->controls($threshold, $mode, $order);

  // Display comments:
  comment_render($id, $cid);
}

switch($op) {  
  case "Preview comment":
    $theme->header();
    comment_preview($pid, $id, $subject, $comment);
    $theme->footer();
    break;
  case "Post comment":
    comment_post($pid, $id, $subject, $comment);
    break;
  case "reply":
    $theme->header();
    comment_reply($pid, $id);
    $theme->footer();
    break;
  case "Update":
    comment_settings($mode, $order, $threshold);
    $theme->header();
    story_render($id, $cid);
    $theme->footer();
    break;
  case "Moderate comments":
    comment_moderate($moderate);
    $theme->header();
    story_render($id, $cid);
    $theme->footer();
    break;
  default:
    $theme->header();
    story_render($id, $cid);
    $theme->footer();
}

?>
