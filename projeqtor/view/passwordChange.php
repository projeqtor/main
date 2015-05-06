<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2015 Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/* ============================================================================
 * Connnexion page of application.
 */
   require_once "../tool/projeqtor.php";
   header ('Content-Type: text/html; charset=UTF-8');
   scriptLog('   ->/view/passwordChange.php'); 
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <title><?php echo i18n("applicationTitle");?></title>
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="stylesheet" type="text/css" href="css/projeqtor.css" />
  <script type="text/javascript" src="../external/CryptoJS/rollups/sha256.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtor.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtorDialog.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/dojo/dojo.js?version=<?php echo $version.'.'.$build;?>"
    djConfig='modulePaths: {i18n: "../../tool/i18n"},
              parseOnLoad: true, 
              isDebug: <?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramDebugMode'));?>'></script>
  <script type="text/javascript" src="../external/dojo/projeqtorDojo.js?version=<?php echo $version.'.'.$build;?>"></script>
  <script type="text/javascript"> 
    dojo.require("dojo.parser");
    dojo.require("dojo.i18n");
    dojo.require("dijit.Dialog"); 
    dojo.require("dijit.form.ValidationTextBox");
    dojo.require("dijit.form.TextBox");
    dojo.require("dijit.form.Button");
    dojo.require("dijit.form.Form");
    dojo.require("dijit.form.FilteringSelect");
    dojo.require("dojox.form.PasswordValidator");
    var fadeLoading=<?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramFadeLoadingMode'));?>;
    dojo.addOnLoad(function(){
      currentLocale="<?php echo $currentLocale?>";
      hideWait();
      changePassword=false;
      dojo.byId('dojox_form__NewPWBox_0').focus();
    }); 
  </script>
</head>

<body class="<?php echo getTheme();?>" >
  <div id="wait" >
  </div> 
  <table align="center" width="100%" height="100%" class="loginBackground">
    <tr height="100%">
      <td width="100%" align="center">
        <div class="background loginFrame" >
        <table  align="center" >
          <tr style="height:10px;" >
            <td align="left" style="position:relative;height: 1%;" valign="top">
			        <div style="position:relative;width: 400px; height: 54px;">
			          <div style="z-index:10;overflow:visible;position:absolute;width: 480px; height: 280px;top:15px;text-align: center">
				        <img src="<?php 
				          if (file_exists("../logo.gif")) echo '../logo.gif';
				          else if (file_exists("../logo.jpg")) echo '../logo.jpg';
				          else if (file_exists("../logo.png")) echo '../logo.png';
				          else echo 'img/titleSmall.png';?>" />
			          </div>
			        </div>
            </td>
          </tr>
          <tr style="height:100%" height="100%">
            <td style="height:99%" align="left" valign="middle">
              <div  id="formDiv" dojoType="dijit.layout.ContentPane" region="center" style="width: 450px; height:210px;overflow:hidden;position: relative;">
             <form  dojoType="dijit.form.Form" id="passwordForm" jsId="passwordForm" name="passwordForm" encType="multipart/form-data" action="" method="" >
             <script type="dojo/method" event="onSubmit" >
              dojo.byId('goButton').focus();
              var extDate=new Date();
              var userSalt=CryptoJS.SHA256('projeqtor'+extDate.getTime());
              dojo.byId('userSalt').value=userSalt;
              var pwd=dijit.byId('password').get('value')+userSalt;
              var crypted=CryptoJS.SHA256(pwd);
              dojo.byId('hashString').value=crypted;
              dojo.byId('passwordLength').value=dijit.byId('password').get('value').length;
              loadContent("../tool/changePassword.php","passwordResultDiv", "passwordForm");
              return false;       
            </script><br/><br/>     
            <div dojoType="dojox.form.PasswordValidator" id="password" class="rounded">
              <label class="label" style="width:200px;"><?php echo i18n('newPassword');?>&nbsp;:&nbsp;</label>
              <input type="password" pwType="new" class="rounded" /><br/>
              <br/>
              <label class="label" style="width:200px;"><?php echo i18n('validatePassword');?>&nbsp;:&nbsp;</label>
              <input type="password" pwType="verify" class="rounded" /><br/>
            </div>            
            <input type="hidden" id="hashString" name="password" value=""/>
            <input type="hidden" id="userSalt" name="userSalt" value=""/>
            <input type="hidden" id="passwordLength" name="passwordLength" value=""/>
            <br/>
            <label class="label" style="width:200px;">&nbsp;</label>
            <button type="submit" style="width:200px" id="goButton" dojoType="dijit.form.Button" showlabel="true">OK
              <script type="dojo/connect" event="onClick" args="evt">
                //loadContent("../tool/changePassword.php","passwordResultDiv", "passwordForm");
              </script>
            </button>
            <br/>
            <?php if ( $user->password != md5(Parameter::getGlobalParameter('paramDefaultPassword')) ) {?>
            <label class="label" style="width:200px;">&nbsp;</label>
            <button type="button" style="width:200px" id="cancelButton" dojoType="dijit.form.Button" showlabel="true"><?php echo i18n('buttonCancel');?>
              <script type="dojo/connect" event="onClick" args="evt">
              showWait(); 
              window.location=".";
              </script>
            </button>  
            <?php }?>  
            <br/><br/>
            
            <label class="label" style="width:200px" >&nbsp;</label>
            <div id="passwordResultDiv" dojoType="dijit.layout.ContentPane" region="none" style="top:150px;">
            </div>
            </form>
              </div>
            </td>
          </tr>
        </table>
        </div>
      </td>
    </tr>
  </table>
</body>
</html>
