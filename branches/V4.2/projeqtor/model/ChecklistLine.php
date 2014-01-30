<?php 
/* ============================================================================
 * Line defines right to the application for a menu and a profile.
 */ 
class ChecklistLine extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $idChecklist;
  public $idChecklistDefinitionLine;
  public $value01;
  public $value02;
  public $value03;
  public $value04;
  public $value05;
  public $idUser;
  public $checkTime;
  public $comment;
  
  public $_noHistory=true; // Will never save history for this object
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
 
/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";    
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
/** =========================================================================
   * Overrides SqlElement::deleteControl() function to add specific treatments
   * @see persistence/SqlElement#deleteControl()
   * @return the return message of persistence/SqlElement#deleteControl() method
   */  
  
  public function deleteControl() {
  	$result="";    

  	if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
  
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
  }
  
  
  /** =========================================================================
   * Overrides SqlElement::delete() function to add specific treatments
   * @see persistence/SqlElement#delete()
   * @return the return message of persistence/SqlElement#delete() method
   */  
  public function delete() {  	
    //echo"";
    $result = parent::delete();
    return $result;
  }
  
  /** =========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */  
  public function save() {
  	$this->checkTime=date('Y-m-d H:i:s');
  	$result=parent::save();
  	return $result;
  }
  public static function sort($a, $b) {
  	if ($a->sortOrder == $b->sortOrder) {
  		if ($a->id < $b->id) {
  			return -1;
  		} else {
  			return 1;
  		}
  	} else if ($a->sortOrder < $b->sortOrder) {
  		return -1;
  	} else {
  		return 1;
  	}
  }
}
?>