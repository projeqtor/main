<?php 
/* ============================================================================
 * Stauts defines list stauts an activity or action can get in (lifecylce).
 */ 
class DocumentDirectory extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  //public $localName;
  public $idProject;
  public $idDirectory;
  public $location;
  public $sortOrder=0;
  public $idle;
  public $_col_2_2;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="40%">${name}</th>
    <th field="nameProject" width="20%">${project}</th>
    <th field="nameDirectory" width="20%">${parent}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  
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
  
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
}
?>