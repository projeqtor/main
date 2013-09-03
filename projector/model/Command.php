<?php 
/** ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */ 
class Command extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_col_1_2_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $idProject;
  public $idCommandType;
  public $name;
  public $externalReference;  
  public $creationDate;
  public $idUser;
  public $Origin;
  public $description;
  public $_tab_2_2 = array('initial', 'validated', 'startDate', 'endDate');
  public $initialStartDate; 
  public $_void_12;
  public $initialEndDate;
  public $validatedEndDate;
  
  public $additionalInfo;
  
  public $_col_2_2_treatment;
  public $idActivity;
  public $idStatus;
  public $idResource;  
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;

  public $_tab_3_3 = array('initial', 'add', 'validated', 'Work', 'PricePerDay', 'Amount');
  public $initialWork;
  public $addWork;
  public $validatedWork;
  public $initialPricePerDayAmount;
  public $addPricePerDayAmount;
  public $validatedPricePerDayAmount;
  public $initialAmount;
  public $addAmount;
  public $validatedAmount;
  
  public $comment;
  
  public $_col_1_1_Link;
  public $_Link=array();
  public $_Attachement=array();
  public $_Note=array();
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="4%" ># ${id}</th>
    <th field="nameProject" width="9%" >${idProject}</th>
    <th field="nameCommandType" width="7%" >${idCommandType}</th>
    <th field="name" width="12%" >${name}</th>
    <th field="colorNameStatus" width="9%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="nameResource" width="8%" >${responsible}</th>
    <th field="validatedEndDate" width="8%" >${validatedEndDate}</th>
  	<th field="validatedWork" formatter="numericFormatter" width="4%" >${validatedWork2}</th>
  	<th field="validatedPricePerDayAmount" formatter="numericFormatter" width="4%" >${validatedPricePerDayAmount2}</th>
  	<th field="validatedAmount" formatter="numericFormatter" width="4%" >${validatedAmount2}</th>
  	<th field="handled" width="4%" formatter="booleanFormatter" >${handled}</th>
    <th field="done" width="4%" formatter="booleanFormatter" >${done}</th>
    <th field="idle" width="4%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "name"=>"required", 
                                  "idCommandType"=>"required",
                                  "idStatus"=>"required",
  								  "creationDate"=>"hidden",	
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
  								  "validatedWork"=>"readonly",
  							      "validatedPricePerDayAmount"=>"readonly",
  							      "validatedAmount"=>"readonly",
  							      "externalReference"=>"required"
  );  
  
  private static $_colCaptionTransposition = array('idUser'=>'issuer', 
                                                   'idResource'=> 'responsible',
  													'idActivity'=>'linkActivity',
  													'creationDate'=>'receptionDateTime');
  
//  private static $_databaseColumnName = array('idResource'=>'idUser');
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    
    if ($this->id) {
    	self::$_fieldsAttributes["creationDate"]='readonly';
    }
  }

   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
   }


// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  
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
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld) {
    return self::$_colCaptionTransposition;
  }

  /** ========================================================================
   * Return the specific databaseColumnName
   * @return the databaseTableName
   */
  /**protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  */
  // ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
	
    return $colScript;
  }

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
   	
   	if ($result=="") $result='OK';
	
    return $result;
  }

  
  /** =========================================================================
   * Overrides SqlElement::deleteControl() function to add specific treatments
   * @see persistence/SqlElement#deleteControl()
   * @return the return message of persistence/SqlElement#deleteControl() method
   */  
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
  	$result='';
  	
  	$oldInitialAmount=0;
  	$oldAddAmount=0;
  	$oldValidatedWork=0;
  	$oldIdProject=0;
  	
  	//Check if we are in CREATION

    if (trim($this->id)=='') {
    	// fill the creatin date if it's empty - creationDate is not empty for import ! 
    	if ($this->creationDate=='') $this->creationDate=date('Y-m-d H:i');
	} else {
		$old=$this->getOld();
		$oldIdProject=$old->idProject;
		$oldInitialAmount=$old->initialAmount;
		$oldAddAmount=$old->addAmount;
		$oldValidatedWork=$old->validatedWork;
	}

	if (!$this->initialAmount) $this->initialAmount=0;
	if (!$this->initialWork) $this->initialWork=0;
	if (!$this->initialPricePerDayAmount) $this->initialPricePerDayAmount=0;
	
	if (!$this->addAmount) $this->addAmount=0;
	if (!$this->addWork) $this->addWork=0;
	if (!$this->addPricePerDayAmount) $this->addPricePerDayAmount=0;
	
  	if ($this->initialAmount!=$oldInitialAmount && $this->initialWork!=0) {
		$this->initialPricePerDayAmount=round($this->initialAmount/$this->initialWork, 2);
	} else if ($this->initialWork==0) {
		$this->initialPricePerDayAmount=0;
	} else if ($this->initialAmount==$oldInitialAmount) {
		$this->initialAmount=round($this->initialPricePerDayAmount*$this->initialWork, 2);
	}
	
  	if ($this->addAmount!=$oldAddAmount && $this->addWork!=0) {
		$this->addPricePerDayAmount=round($this->addAmount/$this->addWork, 2);
	} else if ($this->addWork==0) {
		$this->addPricePerDayAmount=0;
	} else if ($this->addAmount==$oldAddAmount) {
		$this->addAmount=round($this->addPricePerDayAmount*$this->addWork, 2);
	}
	
	$this->validatedWork=$this->initialWork+$this->addWork;
	$this->validatedAmount=$this->initialAmount+$this->addAmount;
	if ($this->validatedWork!=0) {
		$this->validatedPricePerDayAmount=round($this->validatedAmount/$this->validatedWork, 2);
	} else {
		$this->validatedPricePerDayAmount=0;
	}
	
	$this->externalReference=strtoupper(trim($this->externalReference));
    $this->name=trim($this->name);
    
    // #305 : need to recalculate before dispatching to PE
    $this->recalculateCheckboxes();
        	
	$resultClass = parent::save();
    
    if (! strpos($resultClass,'id="lastOperationStatus" value="OK"')) {
    	return $resultClass;
    }
    
    if ($oldValidatedWork!=$this->validatedWork || 
    	$oldIdProject!=$this->idProject) {
    	
    	if (trim($oldIdProject)!='') {
	    	$prj=new Project($oldIdProject);
	    	$prj->updateValidatedWork();
    	}
        if (trim($this->idProject)!='' && $oldIdProject!=$this->idProject) {
	    	$prj=new Project($this->idProject);
	    	$prj->updateValidatedWork();
    	}
    }
    return $resultClass;
  }
  
  private function zeroIfNull($value) {
  	$val = $value;
  	if (!$val || $val=='' || !is_numeric($val)) {
  		$val=0;
  	} else { 
  		$val=$val*1;
  	}
  	
  	return $val;
  	
  }
  
}
?>