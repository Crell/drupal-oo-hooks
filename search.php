<?
 include "includes/common.inc";
 include "includes/section.inc";

 $theme->header();

 $terms = check_input($terms);

 $output .= "<TABLE WIDTH=\"100%\" BORDER=\"0\">\n";
 $output .= " <TR VALIGN=\"center\">\n";
 $output .= "  <TD COLSPAN=3>\n";
 $output .= "   <FORM ACTION=\"search.php\" METHOD=\"POST\">\n";
 $output .= "    <INPUT SIZE=\"50\" VALUE=\"$terms\" NAME=\"terms\" TYPE=\"text\"><BR>\n";

 // section:
 $output .= "<SELECT NAME=\"section\">\n";
 $output .= " <OPTION VALUE=\"\">All sections</OPTION>\n";
 foreach ($sections = section_get() as $value) $output .= " <OPTION VALUE=\"$value\"". ($section == $value ? " SELECTED" : "") .">$value</OPTION>\n";
 $output .= "</SELECT>\n";

 // order:
 $output .= "<SELECT NAME=\"order\">\n";
 if ($order == 1) {
   $output .= " <OPTION VALUE=\"1\">Oldest first</OPTION>\n";
   $output .= " <OPTION VALUE=\"2\">Newest first</OPTION>\n";
 }
 else {
   $output .= " <OPTION VALUE=\"1\">Newest first</OPTION>\n";
   $output .= " <OPTION VALUE=\"2\">Oldest first</OPTION>\n";
 }
 $output .= "</SELECT>\n";

 $output .= "   <INPUT TYPE=\"submit\" VALUE=\"Search\">\n";
 $output .= "  </TD>\n";
 $output .= " </TR>\n";
 $output .= " <TR>\n";
 $output .= "  <TD>\n";
   
 // Compose and perform query:
 $query = "SELECT s.id, s.subject, u.userid, s.timestamp, COUNT(c.cid) AS comments FROM stories s LEFT JOIN users u ON s.author = u.id LEFT JOIN comments c ON s.id = c.lid WHERE s.status = 2 ";
 $query .= ($author) ? "AND u.userid = '$author' " : "";
 $query .= ($terms) ? "AND (s.subject LIKE '%$terms%' OR s.abstract LIKE '%$terms%' OR s.updates LIKE '%$terms%') " : "";
 $query .= ($section) ? "AND s.section = '$section' GROUP BY s.id " : "GROUP BY s.id ";
 $query .= ($order == 1) ? "ORDER BY s.timestamp ASC" : "ORDER BY s.timestamp DESC";
 $result = db_query($query);
 
 // Display search results:
 $output .= "<HR>\n";

 while ($entry = db_fetch_object($result)) {
   $num++;
   $output .= "<P>$num) <B><A HREF=\"story.php?id=$entry->id\">". check_output($entry->subject) ."</A></B> (". format_plural($entry->comments, "comment", comments) .")<BR><SMALL>by ". format_username($entry->userid) ."</B>, posted on ". format_date($entry->timestamp) .".</SMALL></P>\n";
 }

 if ($num == 0) $output .= "<P>Your search did <B>not</B> match any articles in our database: <UL><LI>Try using fewer words.</LI><LI>Try using more general keywords.</LI><LI>Try using different keywords.</LI></UL></P>\n";
 else $output .= "<P><B>$num</B> results matched your search query.</P>\n";
 
 $output .= "  </TD>\n";
 $output .= " </TR>\n";
 $output .= "</TABLE>\n";

 $theme->box("Search", $output);
 $theme->footer();

?>