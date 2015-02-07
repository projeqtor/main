<?php
/** ============================================================================
 * 
 */

require_once "../tool/projector.php";
scriptLog('   ->/tool/dynamicListDependency.php');
$refType=$_REQUEST['objectClass'];
$refId=$_REQUEST['objectType'];

$refTypeId=SqlList::getIdFromTranslatableName('Textable', $refType);
//echo $refType.'/'.$refId.'/'.$refTypeId;

$crit="scope='Note' and (idTextable is null or idTextable='" . $refTypeId ."')";
$crit.=" and (idType is null or idType='" . $refId ."') and idle=0";

$txt=new PredefinedNote();
$list=$txt->getSqlElementsFromCriteria(null, false, $crit, 'name asc');
if (count($list)==0) {
	return;
}
?>
<label for="dialogNotePredefinedNote" ><?php echo i18n("colPredefinedNote");?>&nbsp;:&nbsp;</label>
<select id="dialogNotePredefinedNote" name="dialogNotePredefinedNote" 
onchange="noteSelectPredefinedText(this.value);" dojoType="dijit.form.FilteringSelect"  
class="input" style="width:345px">
 <option value=""></option>
 <?php
 foreach ($list as $lstObj) {
   echo '<option value="' . $lstObj->id .'" >'.$lstObj->name.'</option>';
 }
 
 ?>
</select>