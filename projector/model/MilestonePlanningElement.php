<?php
/* ============================================================================
 * Planning element is an object included in all objects that can be planned.
 */ 
class MilestonePlanningElement extends PlanningElement {

    public $id;
  public $idProject;
  public $refType;
  public $refId;
  public $refName;
  public $_tab_4_1 = array('requested', 'validated', 'planned', 'real', 'dueDate');
  public $initialEndDate;
  public $validatedEndDate;
  public $plannedEndDate;
  public $realEndDate;
  public $wbs;
  public $wbsSortable;
  public $topId;
  public $topRefType;
  public $topRefId;
  public $priority;
  public $idle;
  private static $_fieldsAttributes=array(
    "priority"=>"hidden",
    "initialStartDate"=>"hidden",
    "validatedStartDate"=>"hidden",
    "plannedStartDate"=>"hidden",
    "realStartDate"=>"hidden",
    "initialDuration"=>"hidden",
    "validatedDuration"=>"hidden",
    "plannedDuration"=>"hidden",
    "realDuration"=>"hidden",
    "initialWork"=>"hidden",
    "validatedWork"=>"hidden",
    "plannedWork"=>"hidden",
    "realWork"=>"hidden",
    "plannedEndDate"=>"readonly",
    "assignedWork"=>"hidden",
    "leftWork"=>"hidden",
    "realEndDate"=>"readonly"
  );   
  
  private static $_databaseTableName = 'planningelement';
  
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

    /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    global $paramDbPrefix;
    return $paramDbPrefix . self::$_databaseTableName;
  }
    
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }

  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    $this->initialStartDate=$this->initialEndDate;
    $this->validatedStartDate=$this->validatedEndDate;
    $this->plannedStartDate=$this->plannedEndDate;
    $this->realStartDate=$this->realEndDate;
    $this->initialDuration=0;
    $this->validatedDuration=0;
    $this->plannedDuration=0;
    $this->realDuration=0;
    $this->initialWork=0;
    $this->validatedWork=0;
    $this->plannedWork=0;
    $this->realWork=0;
    $this->elementary=1;
    return parent::save();
  }
  
}
?>