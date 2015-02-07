<?php
/** =========================================================================== 
 * Chek login/password entered in connection screen
 */
  restore_error_handler();
  
  $param=$_REQUEST["param"];
  $pname=$_REQUEST["pname"];
  $label=$_REQUEST["label"];
  $value=$_REQUEST["value"];
  $ctrl=$_REQUEST["ctrls"];
  
  // Controls
  $error=false;
  foreach ($param as $id=>$val) {
    $ct=$ctrl[$id];
    if (substr($ct,0,1)=="=") {
      if ( strpos($ct, '=' . $val . '=')===false) {
        showError("incorrect value for '" . $label[$id] . "', valid values are : " . str_replace("="," ",$ct));
      }
    } else if ($ct=="mandatory") {
      if ( ! $val) {
        showError("incorrect value for '" . $label[$id] . "', field is mandatory");
      }
    } else if ($ct=="email") {
      if ($val and !filter_var($val, FILTER_VALIDATE_EMAIL)) {
        showError("incorrect value for '" . $label[$id] . "', invalid email address");  
      }
    } else if ($ct=="integer") {
      if (! is_numeric($val) or !is_int($val*1)) {
        showError("incorrect value for '" . $label[$id] . "', field must be an integer");  
      }
    }
  }
  // check database connexion
  //error_reporting();
  ini_set('mysql.connect_timeout', 10);
  if ( ! $connexion = mysql_connect($param['DbHost'], $param['DbUser'], $param['DbPassword']) ) {
    showError("incorrect database parameters : wrong host or user or password");
  } 
  if ( ! mysql_select_db($param['DbName'], $connexion) ) {
    $query='CREATE DATABASE ' . $param['DbName'] . ' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;';
    $result = mysql_query($query,$connexion);  
    if ($result) {
      showMsg('Database \'' . $param['DbName'] . '\' created.');
    } else {
      showError('Error while trying to create Database \'' . $param['DbName'] . '\' .');
    } 
  }
  
  // Check attachement directory (may be empty)
  if ($param['AttachementDirectory']) {
    if (! file_exists ($param['AttachementDirectory'])) {
      if (! mkdir($param['AttachementDirectory'],0777,true)) {
        showError("incorrect value for '" . $label['AttachementDirectory'] . "', this is not a valid directory name");
      }  
    }
  }
  // Check log file location : write possible
  if ($param['logFile']) {
    $rep=dirname($param['logFile']);
    if (! file_exists ($rep)) {
      if (! mkdir($rep,0777,true)) {
        showError("incorrect value for '" . $label['logFile'] . "', does not include a valid directory name");
      } 
    }
    if (! $error) {
      $logFile=str_replace('${date}',date('Ymd'),$param['logFile']);
      if (! write ( $logFile, 'CONFIGURATION CONTROLS ARE OK' )) {
        showError("incorrect value for '" . $label['logFile'] . "', cannot write to such a file");
      } else {
        //echo "Write in $file OK<br/>";
        kill($logFile);
      }
    }
  }  
  
  // Check parameter file location : write possible
  $paramFile=$_REQUEST['location'];
  if ($paramFile) {
    $rep=dirname($paramFile);
    if (! file_exists ($rep)) {
      if (! mkdir($rep,0777,true)) {
        showError("incorrect value for 'Parameter file name', does not include a valid directory name");
      } 
    }
    if (! $error) {
      if (! write ( $paramFile, 'TEST')) {
        showError("incorrect value for 'Parameter file name', cannot write to such a file");
      } else {
        kill($paramFile);
      }
    }
  }  
  
  if ($error) {exit;}

  kill($paramFile);
  write($paramFile, '<?php ' . "\n");
  write($paramFile, '// =======================================================================================' . "\n");
  write($paramFile, '// Automatically generated parameter file' . "\n");
  write($paramFile, '// =======================================================================================' . "\n");
  foreach ($param as $id=>$val) {
    write($paramFile, '$' . $pname[$id] . ' = \'' . addslashes($val) . '\';');
    //write($paramFile, '   // ' . $label[$id] . ' : ' . $value[$id] );
    write($paramFile, "\n");
  }
  write($paramFile, '?> ');
  if ($error) {exit;}
  
  $paramLocation="../tool/parametersLocation.php";
  kill($paramLocation);
  if (! write($paramLocation, "<?php \n")) {
    showError("impossible to write \'$paramLocation\' file, cannot write to such a file");
  }
  write($paramLocation, '$parametersLocation = "' . $paramFile . '";');
  write($paramLocation, '?> ');
  
  //rename ('../tool/config.php','../tool/config.php.old');
  showMsg("Parameters are saved.");
  
  echo '<br/><button id="continueButton" dojoType="dijit.form.Button" showlabel="true">continue';
  echo '<script type="dojo/connect" event="onClick" args="evt">';
  echo '  window.location = ".";';
  echo '</script>';
  echo '</button>';
  
  function showError($msg) {
    global $error;
    $error=true;
    echo "<div class='messageERROR'>" . $msg . "</div>";
  }
  function showMsg($msg) {
    echo "<div class='messageOK'>" . $msg . "</div>";
  }
  function write($file,$msg) {
    return error_log($msg,3,$file);
  }
  function kill($file) {
    if (file_exists($file)) {
      unlink($file);
    }
  }
?>