<?php 
/* ============================================================================
 * Stauts defines list stauts an activity or action can get in (lifecylce).
 */ 
class Document extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $idProject;
  public $idProduct;
  public $idDocumentDirectory;
  public $idDocumentType;
  public $name;
  public $idStatus;
  public $idAuthor;
  public $idle;
  public $_sec_Lock;
  public $_spe_lockButton;
  public $locked;
  public $idLocker;
  public $lockedDate;
  public $_col_2_2_Version; 
  public $idVersioningType;
  //public $idCurrentVersion;
  //public $idCurrentRefVersion;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="nameProject" width="10%">${idProject}</th>
    <th field="nameProduct" width="10%">${idProduct}</th>
    <th field="nameDocumentType" width="10%">${type}</th>
    <th field="name" width="35%">${name}</th>
    <th field="locked" width="5%" formatter="booleanFormatter">${locked}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
//<th field="nameCurrentVersion" width="10%">${idCurrentVersion}</th>
//<th field="nameCurrentRefVersion" width="10%">${idCurrentRefVersion}</th>
    
   private static $_fieldsAttributes=array(
    "id"=>"nobr",
    "locked"=>"readonly",
    "idLocker"=>"readonly",
    "lockedDate"=>"readonly");
   
   private static $_colCaptionTransposition = array('idDocumentType' => 'type');
   
   
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
    
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
 
    /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld) {
    return self::$_colCaptionTransposition;
  }
  
  public function drawSpecificItem($item){
    $result="";
    if ($item=='lockButton') {
    	if ($this->locked) {
    		$result .= '<tr><td></td><td>';
        $result .= '<button id="unlockDocument" dojoType="dijit.form.Button" showlabel="true"'; 
        $result .= ' title="' . i18n('unlockDocument') . '" >';
        $result .= '<span>' . i18n('unlockDocument') . '</span>';
        $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
        $result .=  '  unlockDocument();';
        $result .= '</script>';
        $result .= '</button>';
        $result .= '</td></tr>';
    	} else {
	    	$result .= '<tr><td></td><td>';
	    	$result .= '<button id="lockDocument" dojoType="dijit.form.Button" showlabel="true"'; 
	      $result .= ' title="' . i18n('lockDocument') . '" >';
	      $result .= '<span>' . i18n('lockDocument') . '</span>';
	      $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
	      $result .=  '  lockDocument();';
	      $result .= '</script>';
	      $result .= '</button>';
	      $result .= '</td></tr>';
    	}
    	return $result;
    }
  }
}
?>