<?php
   session_start();
   include ("top.php");
   $debug = 0;
   require_once ("connect.php");
   if (!$_SESSION["Username"]){
      print "<p><a href='login.php'>Please login first</a></p>"; 
      die;
   }
   if (isset($_POST["cmdDelete"]))
   {
      /*
      if ($fromPage != $yourURL) {
              die("<p>Sorry you cannot access this page. Security breach detected and reported.</p>");
                                 }
                                 */
      $delID = htmlentities($_POST["deleteID"], ENT_QUOTES);

      $sql = "Delete ";
      $sql .= "FROM tblNote ";
      $sql .= "where pkNoteID=" .$delID;

      if ($debug) print "<p>sql " . $sql;
      $stmt = $db->prepare($sql);
      $DeleteData = $stmt->execute();

      $sql = "Delete ";
      $sql .= "FROM tblNoteToUser ";
      $sql .= "where fkNoteID=" .$delID;
      $sql .= "and fkToUsername=" .$_SESSION['Username'];

      if ($debug) print "<p>sql " . $sql;
      $stmt = $db->prepare($sql);
      if($debug){ print "<pre>"; print_r($user); print "</pre>";}
      $DeleteData = $stmt->execute();
         
      
         $sql = 'select fldName ';
         $sql .= 'from tblFile ';
         $sql .= 'where fkNoteID = '.$delID;
         if ($debug) print "<p>sql " .$sql;

         $stmt = $db->prepare($sql);

         $stmt->execute();
         $filesTable = $stmt->fetchAll();
         if($debug){ print "<pre>"; print_r($filesTable); print "</pre>";}
          
         foreach ($filesTable as $files)
         {
            print "<p> Deleted ".$files['fldName'];
//            unlink("files/".$files['fldName']);
         }

      $sql = "Delete ";
      $sql .= "FROM tblFile ";
      $sql .= "where fkNoteID=" .$delID;

      if ($debug) print "<p>sql " . $sql;
      $stmt = $db->prepare($sql);
      $DeleteData = $stmt->execute();
   }

?>
<p id = "add">
   <a href ="addDevice.php">Add New Device</a>
</p>

<p id = "toggle">
   <a href ="toggle.php">Toggle Your Devices LED</a>
</p>

<ol class = "MyNotes">
<?
##############################################################################
//selects all notes to me from table
   $sql = 'Select fkNoteID ';
   $sql .= 'from tblNoteToUser ';
   $sql .= 'where fkToUsername = "' .$_SESSION["Username"]. '"';
   if ($debug) print "<p>sql " .$sql;

   $stmt = $db->prepare($sql);
   
   $stmt->execute();

   $NotesToMe = $stmt->fetchAll();
   if($debug){ print "<pre>"; print_r($NotesToMe); print "</pre>";}

   foreach ($NotesToMe as $NTM) 
       {
       $sql = 'select fkFromUsername, fldToUsername, fldMessage, fldTimePosted,fldDeadline ';
       $sql .= 'from tblNote ';
       $sql .= 'where pkNoteID = "' .$NTM['fkNoteID']. '"';
       if ($debug) print "<p>sql " .$sql;

       $stmt = $db->prepare($sql);

       $stmt->execute();

       $NotesFull = $stmt->fetchAll();
       if($debug){ print "<pre>"; print_r($NotesFull); print "</pre>";}


       foreach ($NotesFull as $NF) 
       {
         print '<li class="note"> From: ' .$NF['fkFromUsername']. '';
         ?>
         <form action="<? print $_SERVER['PHP_SELF']; ?>"
         method="post"
         class="delete">
            <fieldset class="delete">
               <input type="submit" name="cmdDelete" class="deleteButton" value="x"/>
               <?php print '<input name= "deleteID" type="hidden" id="deleteID" value="' . $NTM['fkNoteID'] . '"/>';?>
	    </fieldset>
         </form>
         <?
         $NF['fldMessage'] = nl2br($NF['fldMessage']);
         print '<p class="message">' .$NF['fldMessage']. '</a>';
         $sql = 'select fldName ';
         $sql .= 'from tblFile ';
         $sql .= 'where fkNoteID = "'.$NTM['fkNoteID']. '"';
         if ($debug) print "<p>sql " .$sql;

         $stmt = $db->prepare($sql);

         $stmt->execute();
         $filesTable = $stmt->fetchAll();
         if($debug){ print "<pre>"; print_r($filesTable); print "</pre>";}
         foreach ($filesTable as $files)
         {
            print "<a id = files href = 'files/".$files['fldName']."'>".$files['fldName']."</a></p></li>";
         }

       }
       }
print '</ol>'
//include ("footer.php");
?>
