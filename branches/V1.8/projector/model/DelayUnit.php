<?php 
/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
class DelayUnit extends SqlElement {

  // Define the layout that will be used for lists
  public $id;    // redefine $id to specify its visible place 
  public $code;
  public $name;
  public $idle;
  public $_isNameTranslatable = true;
  
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
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  
}
?>