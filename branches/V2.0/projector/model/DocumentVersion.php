<?php 
/* ============================================================================
 * Stauts defines list stauts an activity or action can get in (lifecylce).
 */ 
class DocumentVersion extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;
  public $name;
  public $fullName;
  public $version;
  public $revision;
  public $draft;
  public $fileName;
  public $fileSize;
  public $mimeType;
  public $versionDate;
  public $createDateTime;
  public $updateDateTime;
  public $extension;
  public $idDocument;
  public $idAuthor;
  public $idStatus;
  public $description;
  public $isRef;
  public $idle;
  
  private static $_colCaptionTransposition = array();
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
  
    /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld) {
    return self::$_colCaptionTransposition;
  }
  
  /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $critWhere="idDocument='". $this->idDocument . "' and name='" . $this->name . "'";
    if ($this->id) {
    	$critWhere .= " and id!='" . $this->id . "'";
    }
    $lst=$this->getSqlElementsFromCriteria(null, false, $critWhere);
    if (count($lst)>0) {
        $result.='<br/>' . i18n('errorDuplicateDocumentVersion',array($this->name));
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  
  function save() {
  	if ($this->id) {
  		$this->updateDateTime=Date('Y-m-d H:i:s');
  	} else  {
  		$this->createDateTime=Date('Y-m-d H:i:s');
  	}
  	$doc=new Document($this->idDocument);
  	$this->fullName=$doc->name."_".$this->name;
  	$result=parent::save();
    if ( ($doc->version==null) 
    or ( $this->version>$doc->version ) 
    or ( $this->version==$doc->version and $this->revision>$doc->revision) 
    or ( $this->version==$doc->version and $this->revision==$doc->revision and $this->draft>$doc->draft) ) {
      $doc->version=$this->version;
      $doc->revision=$this->revision;
      $doc->draft=$this->draft;
      $doc->idDocumentVersion=$this->id;
      $doc->save();
    }
  	return $result;
  }
  
  function delete() {
  	$recalcDoc=false;
  	$crit=array('idDocument'=>$this->idDocument);
  	$doc=new Document($this->idDocument);
    if ($doc->idDocumentVersion==$this->id) {
      $doc->version=null;
      $doc->revision=null;  
      $doc->draft=null;
      $doc->idDocumentVersion=null;
      $doc->save();
    }
  	$result=parent::delete();
  	$list=$this->getSqlElementsFromCriteria($crit, false, null, 'id desc',false);
  	if (count($list)>0) {
  		$dv=$list[0];
  		$dv->save();
  	}
  	return $result;
  }
  
  function getUploadFileName() {
  	global $paramPathSeparator;
  	$doc=New Document($this->idDocument);
    $dir=New DocumentDirectory($doc->idDocumentDirectory);
  	$root=Parameter::getGlobalParameter('documentRoot');
    $uploaddir = $root . $dir->location ;
    return $uploaddir . $paramPathSeparator . $this->fileName . '.' . $this->id;
  }
}
?>