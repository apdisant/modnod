<?php
//The point of this file is only to pull the data from every single photon in the database once, it will then be run repeatedly through cron
   //session_start();
   include ("top.php");
   $debug = 1;
   require_once ("connect.php");
   /*
   if (!$_SESSION["Username"]){
      print "<p><a href='login.php'>Please login first</a></p>"; 
      die;
   }
   */

##############################################################################
//Get an array with all access tokens
   $todaysDate = date("Y-m-d");
   if ($debug) echo $todaysDate ."<br>";
   $sql = 'SELECT fldAccessToken, pkUsername ';
   $sql .= 'FROM tblUser; ';
   if ($debug) print "<p>sql ".$sql;

   $stmt = $db->prepare($sql);
   $stmt->Execute();
   $MyAccessToken = $stmt->fetchAll();

   foreach ($MyAccessToken as $MAT) 
   {
      $CurrentAccessToken = $MAT['fldAccessToken'];
      $CurrentUser = $MAT['pkUsername'];

      //Find all devices owned by the user with $AccessToken
      $sql = 'Select pkDeviceID ';
      $sql .= 'from tblDevices ';
      $sql .= 'where fkUsername = "' .$CurrentUser. '"';
      if ($debug) print "<p>sql " .$sql;

      $stmt = $db->prepare($sql);
      
      $stmt->execute();

      $MyDeviceIDs = $stmt->fetchAll();


      foreach ($MyDeviceIDs as $MDI) //should now have each username. access token and device ID
      {
            $CurrentDeviceID = $MDI['pkDeviceID'];
         if($debug)
         {
            echo "<br>" .$CurrentDeviceID;
            echo '<br>';
            echo $CurrentAccessToken;
            echo '<br>';
            echo $CurrentUser;
            echo '<br>';
         }

                     $sql = 'INSERT INTO tblSensorData SET fkDeviceID = "' .$CurrentDeviceID.'", ';

            for ($i = 1; $i < 7; $i++)
            {
               $readPhotoURL = "https://api.particle.io/v1/devices/" .$CurrentDeviceID."/analog".$i."value/?access_token=" .$CurrentAccessToken;

               if($debug) echo $readPhotoURL;

               echo "<br>";

               $pr = curl_init($readPhotoURL);
               curl_setopt($pr, CURLOPT_RETURNTRANSFER, true);
               curl_setopt($pr, CURLOPT_TIMEOUT, 1);
               $html = curl_exec($pr);
               if ($debug)
               {
                  echo $html;
                  echo "<br>";
               }

               //trying to decode the get data
               //$prJSONARRAY = get_defined_constants(var_dump(json_decode($html,true)));
               $prJSONArray = json_decode($html,true);
               if ($debug)
               {
                  echo $prJSONArray;
                  echo "<br>";
               }

               foreach($prJSONArray as $PRA => $value)
               {
                  if($PRA == "result")
                  {
                     if ($value)
                     {
                        if ($debug) echo 'Sensor '.$i.': ' .$value. ' ' ;
                        $sql .= 'fldValue'.$i.' = ' .$value. ', ';
                        $insert = $insert + 1;
                        if ($debug) echo "<br>" .$insert. "<br>";
                     }
                  }
               }
            }
            if ($insert)
            {
               //finally insert all data for the given device at once
               $sql .= 'fldDay = CURDATE();';
               $db -> beginTransaction();
               $stmt = $db -> prepare($sql);
               if ($debug) print "<p>sql " .$sql;

               $stmt -> execute();

               $dataEntered = $db->commit();
               if ($debug) echo "<p> Transaction complete </p>";
            }
            $insert = 0;
         }
      }


//print '</ol>'
//include ("footer.php");
?>
