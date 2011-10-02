<?php 
/* ============================================================================
 * User is a resource that can connect to the application.
 */ 
class User extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $email;
  public $password;
  public $_spe_buttonSendMail;
  public $idProfile;
  public $isContact;
  public $isResource=0;
  public $resourceName;
  public $locked;
  public $idle;
  public $description;
  public $_col_2_2;
  public $_arrayFilters;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="30%">${userName}</th>
    <th field="nameProfile" width="20%" formatter="translateFormatter">${idProfile}</th>
    <th field="resourceName" width="30%">${name}</th>  
    <th field="isResource" width="5%" formatter="booleanFormatter">${isResource}</th>
    <th field="isContact" width="5%" formatter="booleanFormatter">${isContact}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  
  private static $_fieldsAttributes=array("name"=>"required"
  );  
  
  private static $_databaseCriteria = array('isUser'=>'1');
  
  private static $_databaseColumnName = array('resourceName'=>'fullName');
  
  private static $_colCaptionTransposition = array('resourceName'=>'name',
   'name'=> 'userName');
  
  private $_accessControlRights;
  
  public $_accessControlVisibility; // ALL if user should have all projects listed

  private $_affectedProjects;  // Array listing all affected projects
  private $_visibleProjects;   // Array listing all visible projects (affected and their subProjects)
  private $_hierarchicalViewOfVisibleProjects;
  
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
    
    $crit=array("name"=>"menuContact");
    $menu=SqlElement::getSingleSqlElementFromCriteria('Menu', $crit);
    if (! $menu) {
      return;
    }     
    if (securityCheckDisplayMenu($menu->id)) {
      self::$_fieldsAttributes["isContact"]="";
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
   
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld) {
    return self::$_colCaptionTransposition;
  }  
  
  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
  
   /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="isResource") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked || dijit.byId("isContact").get("checked")) { ';
      $colScript .= '    dijit.byId("resourceName").set("required", "true");';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("resourceName").set("required", null);';
      $colScript .= '    dijit.byId("resourceName").set("value", "");';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    if ($colName=="isContact") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked || dijit.byId("isResource").get("checked")) { ';
      $colScript .= '    dijit.byId("resourceName").set("required", "true");';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("resourceName").set("required", null);';
      $colScript .= '    dijit.byId("resourceName").set("value", "");';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    return $colScript;

  }
  
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){
    global $print;
    $result="";
    if ($item=='buttonSendMail') {
      if ($print) {
        return "";
      } 
      $result .= '<tr><td valign="top" class="label"><label></label></td><td>';
      $result .= '<button id="sendInfoToUser" dojoType="dijit.form.Button" showlabel="true"'; 
      $result .= ' title="' . i18n('sendInfoToUser') . '" >';
      $result .= '<span>' . i18n('sendInfoToUser') . '</span>';
      $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
	    $result .=  '  var email="";';
	    $result .=  '  if (dojo.byId("email")) {email = dojo.byId("email").value;}';
      $result .=  '  if (email==null || email=="") { ';
      $result .=  '    showAlert("' . i18n('emailMandatory') . '");';
	    $result .=  '  } else {';
      $result .=  '    loadContent("../tool/sendMail.php","resultDiv","objectForm",true);';
	    $result .=  '  }';	
      $result .= '</script>';
      $result .= '</button>';
      $result .= '</td></tr>';
      return $result;
    } 
  }

  /** =========================================================================
   * Get the access rights for all the screens
   * For more information, refer to AccessControl.ofp diagram 
   * @param $item the item. Correct values are : 
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function getAccessControlRights() {
    // _accessControlRights fetched yet, just return it
    if ($this->_accessControlRights) {
      return $this->_accessControlRights;
    }
    $menuList=SqlList::getListNotTranslated('Menu');
    $noAccessArray=array( 'read' => 'NO', 'create' => 'NO', 'update' => 'NO', 'delete' => 'NO');
    $allAccessArray=array( 'read' => 'ALL', 'create' => 'ALL', 'update' => 'ALL', 'delete' => 'ALL');
    // first time function is called for object, so go and fetch data
    $this->_accessControlVisibility='PRO';
    $accessControlRights=array();
    $accessScopeList=SqlList::getList('AccessScope', 'accessCode');
    $accessRight=new AccessRight();
    $crit=array('idProfile'=>$this->idProfile);
    $accessRightList=$accessRight->getSqlElementsFromCriteria( $crit, false);
    $habilitation=new Habilitation();
    $crit=array('idProfile'=>$this->idProfile, 'allowAccess'=>'1');
    $habilitationList=$habilitation->getSqlElementsFromCriteria( $crit, false);
    foreach ($habilitationList as $hab) {
    	if (array_key_exists($hab->idMenu,$menuList)) {
    	  $menuName=$menuList[$hab->idMenu];
    	  $accessControlRights[$menuName]=$allAccessArray;
    	}
    }
    foreach ($accessRightList as $arObj) {
      $menuName=(array_key_exists($arObj->idMenu,$menuList))?$menuList[$arObj->idMenu]:'';
      if (! $menuName or ! array_key_exists($menuName, $accessControlRights)) {
        $accessControlRights[$menuName]=$noAccessArray;	
      } else {
        $accessProfile=new AccessProfile($arObj->idAccessProfile);
        $scopeArray=array( 'read' =>  $accessScopeList[$accessProfile->idAccessScopeRead],
                           'create' => $accessScopeList[$accessProfile->idAccessScopeCreate],
                           'update' => $accessScopeList[$accessProfile->idAccessScopeUpdate],
                           'delete' => $accessScopeList[$accessProfile->idAccessScopeDelete] );
        $accessControlRights[$menuName]=$scopeArray;
        if ($accessScopeList[$accessProfile->idAccessScopeRead]=='ALL') {
          $this->_accessControlVisibility='ALL';
        }
      }
    }
    foreach ($menuList as $menuId=>$menuName) {
      if (! array_key_exists($menuName, $accessControlRights)) {
        $accessControlRights[$menuName]=$noAccessArray; 
      }     	
    }
    // override with habilitation 
    $this->_accessControlRights=$accessControlRights;
    return $this->_accessControlRights;
  }

  /** =========================================================================
   * Get the list of all projects the resource corresponding to the user is affected to
   * @return a list of projects (id=>name)
   */
  public function getAffectedProjects() {
    if ($this->_affectedProjects) {
      return $this->_affectedProjects;
    }
    $result=array();
    $aff=new Affectation();
    $crit = array("idResource"=>$this->id, "idle"=>'0');
    $affList=$aff->getSqlElementsFromCriteria($crit,false);
    foreach ($affList as $aff) {
      $result[$aff->idProject]=SqlList::getNameFromId('Project',$aff->idProject);
    }
    // Also get Project user have created
    /* V1.7 => removed : it's not because user created the project that he is alowed to see all data about it
    $prj=new Project();
    $crit = array("idUser"=>$this->id);
    $prjList=$prj->getSqlElementsFromCriteria($crit,false);
    foreach ($prjList as $prj) {
      if ( ! array_key_exists($prj->id, $result) ) {
        $result[$prj->id]=$prj->name;
      }
    }
    */
    $this->_affectedProjects=$result;
    return $this->_affectedProjects;
  }
  
  /** =========================================================================
   * Get the list of all projects the user can have readable access to, 
   * this means the projects the resource corresponding to the user is affected to
   * and their sub projects
   * @return a list of projects id
   */
  public function getVisibleProjects() {
    if ($this->_visibleProjects) {
      return $this->_visibleProjects;
    }
    $result=array();
    $affPrjList=$this->getAffectedProjects();
    foreach($affPrjList as $idPrj=>$namePrj) {
      $result[$idPrj]=$namePrj;
      $prj=new Project($idPrj);
      $lstSubPrj=$prj->getRecursiveSubProjectsFlatList(true);
      foreach ($lstSubPrj as $idSubPrj=>$nameSubPrj) {
        $result[$idSubPrj]=$nameSubPrj;
      }  
    }
    $this->_visibleProjects=$result;
    return $result;
  }
  
  /** =========================================================================
   * Get the list of all projects the user can have readable access to, 
   * this means the projects the resource corresponding to the user is affected to
   * and their sub projects
   * @return a list of projects id
   */
  public function getHierarchicalViewOfVisibleProjects($projId='*') {
  	if ($this->_hierarchicalViewOfVisibleProjects) {
  		return $this->_hierarchicalViewOfVisibleProjects;
  	}
    $result=array();
    $visibleProjectsList=$this->getVisibleProjects();
    if ($projId=='*') {
      $prj=new Project();
      $prj->id='*';
    } else {
      $prj=new Project($projId);
    }
    $lst=$prj->getSubProjects();
    foreach ($lst as $prj) {
      if (array_key_exists( $prj->id , $visibleProjectsList)) {
        $subList=$prj->getRecursiveSubProjectsFlatList(false);
        $result['#' . $prj->id]=$prj->name;
        foreach($subList as $id=>$name) {
          $result['#' . $id]=$name;
        }
      } else {
        $recursList=$this->getHierarchicalViewOfVisibleProjects($prj->id);
        if (count($recursList)>0) {
          $result['#' . $prj->id]=$prj->name;
          $result=array_merge($result,$recursList);
        }  
      }
    }
    $this->_hierarchicalViewOfVisibleProjects=$result;
    return $result;
  }
  /** =========================================================================
   * Reinitalise Visible Projects list to force recalculate
   * @return void
   */  
  public function resetVisibleProjects() {
    $this->_visibleProjects=null;
  }

/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    if ($this->isResource and (! $this->resourceName or $this->resourceName=="")) {
      $result.='<br/>' . i18n('messageMandatory',array(i18n('colresourceName')));
    } 
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function reset() {
    $this->_accessControlRights=null;
    $this->_accessControlVisibility=null;
    $this->_visibleProjects=null;
    $this->_hierarchicalViewOfVisibleProjects=null;
  }
}
?>