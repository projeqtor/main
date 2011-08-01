<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projector.php";

// Get the link info
if (! array_key_exists('linkRef1Type',$_REQUEST)) {
  throwError('linkRef1Type parameter not found in REQUEST');
}
$ref1Type=$_REQUEST['linkRef1Type'];
if (! array_key_exists('linkRef1Id',$_REQUEST)) {
  throwError('linkRef1Id parameter not found in REQUEST');
}
$ref1Id=$_REQUEST['linkRef1Id'];
if (! array_key_exists('linkRef2Type',$_REQUEST)) {
  throwError('linkRef2Type parameter not found in REQUEST');
}
$ref2Type=SqlList::getNameFromId('Linkable', $_REQUEST['linkRef2Type']);
if (! array_key_exists('linkRef2Id',$_REQUEST)) {
  throwError('linkRef2Id parameter not found in REQUEST');
}
$ref2Id=$_REQUEST['linkRef2Id'];

$linkId=null;

// get the modifications (from request)
$link=new Link($linkId);

$link->ref1Id=$ref1Id;
$link->ref1Type=$ref1Type;
$link->ref2Id=$ref2Id;
$link->ref2Type=$ref2Type;

$result=$link->save();

// Message of correct saving
if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
  echo '<span class="messageERROR" >' . $result . '</span>';
} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
  echo '<span class="messageOK" >' . $result . '</span>';
} else { 
  echo '<span class="messageWARNING" >' . $result . '</span>';
}
?>