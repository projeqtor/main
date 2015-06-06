<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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
 * Profile defines right to the application or to a project.
 */ 
require_once('_securityCheck.php');
class Profile extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $profileCode;
  public $sortOrder=0;
  public $idle;
  public $description;
  public $_sec_void;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%" ># ${id}</th>
    <th field="name" width="85%" formatter="translateFormatter">${name}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
  
  public $_isNameTranslatable = true;
  
  private static $_fieldsAttributes=array();
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($this->profileCode=="ADM" or $this->profileCode=="PL") {
      self::$_fieldsAttributes["profileCode"]="readonly";
    }
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

  public function deleteControl() {
    $result="";
    if ($this->profileCode=='ADM' or $this->profileCode=='PL') {    
      $result="<br/>" . i18n("msgCannotDeleteProfile");
    }
    if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
  
      /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  public function copy() {
    $result=parent::copy();
    $new=$result->id;
    $toCopy=array('AccessRight', 'Habilitation', 'HabilitationOther', 'HabilitationReport');
    foreach ($toCopy as $objectClass) {
      $obj=new $objectClass();
      $crit=array('idProfile'=>$this->id);
      $lst=$obj->getSqlElementsFromCriteria($crit);
      foreach ($lst as $obj) {
        $obj->idProfile=$new;
        $obj->id=null;
        $obj->save();
      }
    }
    Sql::$lastQueryNewid=$new;
    return $result;
  }
}
?>