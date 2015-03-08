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
  public $approved;
  public $idle;
  
  private static $_colCaptionTransposition = array('name'=>'nextDocumentVersion');
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
    $mode="";
  	if ($this->id) {
  		$this->updateDateTime=Date('Y-m-d H:i:s');
      $mode='update';
  	} else  {
  		$this->createDateTime=Date('Y-m-d H:i:s');
      $mode='insert';
  	}
  	$doc=new Document($this->idDocument);
  	$saveDoc=false;
  	$this->fullName=$doc->name."_".$this->name;
  	$result=parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    if ( ($doc->version==null) 
    or ( $this->version>$doc->version ) 
    or ( $this->version==$doc->version and $this->revision>$doc->revision) 
    or ( $this->version==$doc->version and $this->revision==$doc->revision and $this->draft>$doc->draft) ) {
      $doc->version=$this->version;
      $doc->revision=$this->revision;
      $doc->draft=$this->draft;
      $doc->idDocumentVersion=$this->id;
      $saveDoc=true;
    }
    if ($this->isRef) {
      $doc->idDocumentVersionRef=$this->id;
      $saveDoc=true;
      $critWhere="idDocument='" . $this->idDocument . "' and isRef='1' and id!='" . $this->id . "'";
      $list=$this->getSqlElementsFromCriteria(null, false, $critWhere);
      foreach ($list as $elt) {
      	$elt->isRef='0';
      	$elt->save();
      } 
    }
    if ($doc->idDocumentVersion==$this->id) {
    	$doc->idStatus=$this->idStatus;
      $saveDoc=true;
    }
    if ($saveDoc) {
      $doc->save();
    }
    // Inset approvers from document if not existing (on creation)
    if ($mode=='insert') {
      $approver=new Approver();
      $crit=array('refType'=>'Document','refId'=>$this->idDocument);
      $lstDocApp=$approver->getSqlElementsFromCriteria($crit);
      foreach ($lstDocApp as $app) {
        $newApp=new Approver();
        $newApp->refType='DocumentVersion';
        $newApp->refId=$this->id;
        $newApp->idAffectable=$app->idAffectable;
        $newApp->save();
      }
    }
  	return $result;
  }
  
  function delete() {
    $result=parent::delete();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
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
  	$list=$this->getSqlElementsFromCriteria($crit, false, null, 'id desc',false);
  	if (count($list)>0) {
  		$dv=$list[0];
  		$dv->save();
  	}
  	return $result;
  }
  
  function getUploadFileName() {
  	$paramPathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
  	$doc=New Document($this->idDocument);
    $dir=New DocumentDirectory($doc->idDocumentDirectory);
    $uploaddir = $dir->getLocation();
    if (! file_exists($uploaddir)) {
    	$dir->createDirectory();
    }
    return $uploaddir . $paramPathSeparator . $this->fileName . '.' . $this->id;
  }

  function checkApproved() {
    $crit=array('refType'=>'DocumentVersion','refId'=>$this->id);
    $app=new Approver();
    $list=$app->getSqlElementsFromCriteria($crit);
    $approved=null;
    foreach ($list as $app) {
      if ($app->approved) {
        if ($approved==null) {$approved=1;}
      } else {
        $approved=0;
      }
    }
    $this->approved=($approved==1)?1:0;
    $this->save();
  }
}
?>