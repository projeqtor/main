<?php
/** ===========================================================================
 * Save a filter : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projector.php";
scriptLog('   ->/tool/displayFiletrList.php');
$user=$_SESSION['user'];
$context="";

if (! $user->_arrayFilters) {
  $user->_arrayFilters=array();
}

// Get the filter info
if (! array_key_exists('filterObjectClass',$_REQUEST)) {
	if (isset($objectClass)) {
		$filterObjectClass=$objectClass;
		$context="list";
	} else {
    throwError('filterObjectClass parameter not found in REQUEST');
	}
} else {
  $filterObjectClass=$_REQUEST['filterObjectClass'];
}

// Get existing filter info
if (array_key_exists($filterObjectClass,$user->_arrayFilters)) {
  $filterArray=$user->_arrayFilters[$filterObjectClass];
} else {
  $filterArray=array();
}

$flt=new Filter();
$crit=array('idUser'=> $user->id, 'refType'=>$filterObjectClass );
$filterList=$flt->getSqlElementsFromCriteria($crit, false);
htmlDisplayStoredFilter($filterList,$filterObjectClass);

?>