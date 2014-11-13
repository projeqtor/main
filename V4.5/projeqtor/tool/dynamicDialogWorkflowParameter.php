<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2014 Pascal BERNARD - support@projeqtor.org
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

$id=$_REQUEST['idWorkflow'];
$statusList=SqlList::getList('Status');
$statusColorList=SqlList::getList('Status', 'color');
?>
<form id="dialogWorkflowParameterForm" name="dialogWorkflowParameterForm" action="">
<input type="hidden" name="workflowId" value="<?php echo $id;?>" />
<table style="width: 100%;">
  <tr>
    <td style="width: 100%;">
	    <table width="100%;" >
<?php foreach($statusList as $idStatus=>$status) { 
  $canUpdate=true;
  $checked=true;
  $ws=new WorkflowStatus();
  $cptWs=$ws->countSqlElementsFromCriteria(null,"idWorkflow=$id and (idStatusFrom=$idStatus or idStatusTo=$idStatus)");
  if ($cptWs>0) {
    $canUpdate=false;
  } else {
    $critArray=array('scope'=>'workflow', 'objectClass'=>'workflow#'.$id, 'idUser'=>$idStatus);
    $cs=SqlElement::getSingleSqlElementFromCriteria("ColumnSelector", $critArray);
    if ($cs and $cs->id and $cs->hidden) {
      $checked=false;
    }
  }
  ?>
		    <tr style="height:20px;border:2px solid <?php echo $statusColorList[$idStatus];?>">
		      
			    <td style="width:50px">&nbsp;&nbsp;
			      <div dojoType="dijit.form.CheckBox" type="checkbox"
						  name="dialogWorkflowParameterCheckStatusId_<?php echo $idStatus;?>" 
						  id="dialogWorkflowParameterCheckStatusId_<?php echo $idStatus;?>"
						  <?php if (! $canUpdate) echo 'readonly';?>
				      <?php if ($checked) { echo 'checked'; }?> >
				    </div>
						<span style="cursor:pointer;" 
						  onClick="dojo.byId('dialogWorkflowParameterCheckStatusId_<?php echo $idStatus;?>').click();">
						  <?php echo $status?>
						</span>
				  </td>
	      </tr>
	      <tr style="font-size:2px;height: 5px;"><td>&nbsp;</td></tr>
<?php } ?>
	    </table>
    </td></tr>
    <tr><td style="width: 100%;">&nbsp;</td></tr>
    <tr>
      <td style="width: 100%;" align="center">
        <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogWorkflowParameter').hide();">
        <?php echo i18n("buttonCancel");?>
        </button>
        <button id="dialogWorkflowParameterSubmit" dojoType="dijit.form.Button" type="submit" 
         onclick="saveWorkflowParameter();return false;" >
         <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>      
  </table>
</form>
