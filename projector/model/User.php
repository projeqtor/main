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
  public $initials;
  public $isLdap;
  public $locked;
  public $idle;
  public $description;
  public $_col_2_2_Affectations;
  public $_spe_affectations;
  public $_arrayFilters=array();
  public $_arrayFiltersId=array();
  
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="25%">${userName}</th>
    <th field="nameProfile" width="15%" formatter="translateFormatter">${idProfile}</th>
    <th field="resourceName" width="25%">${name}</th>
    <th field="initials" width="10%">${initials}</th> 
    <th field="isResource" width="5%" formatter="booleanFormatter">${isResource}</th>
    <th field="isContact" width="5%" formatter="booleanFormatter">${isContact}</th>
    <th field="isLdap" width="5%" formatter="booleanFormatter">${isLdap}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  
  private static $_fieldsAttributes=array("name"=>"required",
  										                    "isLdap"=>"hidden,forceExport",
                                          "idProfile"=>"required"
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
    global $objClass;
  	parent::__construct($id);
    
  	// Fetch data to set attributes only to display user. Other access to User (for History) don't need these attributes.
  	if (isset($objClass) and $objClass and $objClass=='User') {
	    $crit=array("name"=>"menuContact");
	    $menu=SqlElement::getSingleSqlElementFromCriteria('Menu', $crit);
	    if (! $menu) {
	      return;
	    }     
	    if (securityCheckDisplayMenu($menu->id)) {
	      self::$_fieldsAttributes["isContact"]="";
	    }
	    if ($this->isLdap!=0) {
	    	self::$_fieldsAttributes["name"]="readonly";
	    	//self::$_fieldsAttributes["resourceName"]="readonly";
	    	self::$_fieldsAttributes["email"]="readonly";
	    	self::$_fieldsAttributes["password"]="hidden";
	    }
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
      //$colScript .= '    dijit.byId("resourceName").set("value", "");';
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
      //$colScript .= '    dijit.byId("resourceName").set("value", "");';
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
    if ($item=='affectations') {
      $aff=new Affectation();
      $critArray=array('idUser'=>(($this->id)?$this->id:'0'),'idle'=>'0');
      $affList=$aff->getSqlElementsFromCriteria($critArray, false);
      drawAffectationsFromObject($affList, $this, 'Project', false);   
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
/*  public function getHierarchicalViewOfVisibleProjects($projId='*') {
  	if ($this->_hierarchicalViewOfVisibleProjects and $projId=='*') {
  		return $this->_hierarchicalViewOfVisibleProjects;
  	} */
  	
  public function getHierarchicalViewOfVisibleProjects($projId='*') {
    if (is_array($this->_hierarchicalViewOfVisibleProjects)) {
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
    $this->_affectedProjects=null;
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
    $crit=array("name"=>$this->name);
    $lst=$this->getSqlElementsFromCriteria($crit,false);
    if (count($lst)>0) {
    	if (! $this->id or count($lst)>1 or $lst[0]->id!=$this->id) {
    		$result.='<br/>' . i18n('errorDuplicateUser');
    	}
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function save() {
    $result=parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    Affectation::updateAffectations($this->id);
    return $result;
  }
  
  public function reset() {
    $this->_accessControlRights=null;
    $this->_accessControlVisibility=null;
    $this->_visibleProjects=null;
    $this->_hierarchicalViewOfVisibleProjects=null;
  }
  
  
  /** =========================================================================
   * fonction for authentificate user with user/password
   * @param $Username $Password
   * can create user directly from Ldap
   * @return -1 or Id of authentified user
   */
	public function authenticate( $paramlogin, $parampassword) {
//scriptLog("UserClass->authenticate ('" . $paramlogin . "', '*****')" );	
	
	  global $paramLdap_allow_login, $paramLdap_base_dn, $paramLdap_host, $paramLdap_port, $paramLdap_version, $paramLdap_search_user, $paramLdap_search_pass, $paramLdap_user_filter, $paramLdap_defaultprofile;
	
	 	if ( ! $this->id ) {
			//debugLog("authenticate - user '" . $paramlogin . "' unknown in database" );
			if (isset($paramLdap_allow_login) and strtolower($paramLdap_allow_login)=='true') {
		  	//debugLog("authenticate - user '" . $paramlogin . "' - LDAP enabled - create from LDAP directory if password is OK" );
		  	$this->name=strtolower($paramlogin);
		  	$this->isLdap = 1;
		  	//$this->id=-2;
			} else {
			  //debugLog("authenticate - user '" . $paramlogin . "' - unknown in database - LDAP disabled" );
				return "login";
		  }	
	 	}	
 	
	 	//debugLog("authenticate - user '" . $paramlogin . "' isLdap=".$this->isLdap );
		if ($this->isLdap == 0) {
			//debugLog("authenticate - user '" . $paramlogin . "' - integrated mode" );
			if ($this->password <> md5($parampassword)) {
				//debugLog("authenticate - user '" . $paramlogin . "' - wrong password" );
	      return "password";
			} else {
				//debugLog("authenticate - user '" . $paramlogin . "' - password OK" );
	  	  return "OK";	
	  	}
	  } else {
	  	// check passsword on LDAP
			//debugLog("authenticate - user '" . $paramlogin . "' - LDAP mode" );
			//debugLog("authenticate - LDAP - Host='". $paramLdap_host ."' Port='". $paramLdap_port ."' Version='". $paramLdap_version . "'" );
	    if (! function_exists('ldap_connect')) {
	    	errorLog('Ldap non installed on your PHP server, you should not set $paramLdap_allow_login to "true"');        
        return "ldap";
	    }
			try { 
	    	$ldapCnx=ldap_connect($paramLdap_host, $paramLdap_port);
			} catch (Exception $e) {
          traceLog("authenticate - LDAP error : " . $e->getMessage() );
          return "ldap";
	    }
	    if (! $ldapCnx) {
        traceLog("authenticate - Mode LDAP - LdapConnectError");        
        return "ldap";
      }
			//debugLog("authenticate - Mode LDAP - LdapConnect OK");       
			@ldap_set_option($ldapCnx, LDAP_OPT_PROTOCOL_VERSION, $paramLdap_version);
			@ldap_set_option($ldapCnx, LDAP_OPT_REFERRALS, 0);
	
			//$ldap_bind_dn = 'cn='.$this->ldap_search_user.','.$this->base_dn;
			$ldap_bind_dn = empty($paramLdap_search_user) ? null : $paramLdap_search_user;
			$ldap_bind_pw = empty($paramLdap_search_pass) ? null : $paramLdap_search_pass;
	
			//debugLog("authenticate - LdapBind - DN='". $ldap_bind_dn ."' PW='". $ldap_bind_pw ."'" );
  		try {
			  $bind=ldap_bind($ldapCnx, $ldap_bind_dn, $ldap_bind_pw);
  		} catch (Exception $e) {
        traceLog("authenticate - LdapBind Error : " . $e->getMessage() );
        return "ldap";
      } 
			if (! $bind) {
	      traceLog("authenticate - LdapBind Error" );
				return "ldap";
			}
			$filter_r = html_entity_decode(str_replace('%USERNAME%', $this->name, $paramLdap_user_filter), ENT_COMPAT, 'UTF-8');
			//debugLog("authenticate - LdapSearch - DN '". $paramLdap_base_dn ."' Filter : '". $filter_r ."'" );
			$result = @ldap_search($ldapCnx, $paramLdap_base_dn, $filter_r);
			if (!$result) {
				//debugLog("authenticate - Mode LDAP - LdapSearch - USERNAME not found");
				return "login";
			}
			//debugLog("authenticate - Mode LDAP - Ldap_get_entries'");
			$result_user = ldap_get_entries($ldapCnx, $result);
			if ($result_user['count'] == 0) {
				//debugLog("authenticate - Mode LDAP - Ldap_get_entries - no result = unknown login'");
				return "login";
			}
		  if ($result_user['count'] > 1) {
        //debugLog("authenticate - Mode LDAP - Ldap_get_entries - too many results = ambigous login");
        return "login";
      }
			$first_user = $result_user[0];
			$ldap_user_dn = $first_user['dn'];

			// Bind with the dn of the user that matched our filter (only one user should match filter ..)

			//debugLog("authenticate - Mode LDAP - LdapBind - DN '". $ldap_user_dn ."' Password : '". $parampassword ."'" );
			try {
			  $bind_user = ldap_bind($ldapCnx, $ldap_user_dn, $parampassword);
			} catch (Exception $e) {
        traceLog("authenticate - LdapBind Error : " . $e->getMessage() );
        return "ldap";
      }   
			if (! $bind_user) {
				//debugLog("authenticate - Mode LDAP - LdapBind - KO" );
				return "login";
			}
			//debugLog("authenticate - Mode LDAP - LdapBind - SUCCESS" );
			if (! $this->id and $this->isLdap) {
				//debugLog("authenticate - Mode LDAP - LdapBind - SUCCESS - Create user from LDAP" );
				if (!count($first_user) == 0) {
					// Contact information based on the inetOrgPerson class schema
					if (isset( $first_user['mail'][0] )) {
				  		$this->email=$first_user['mail'][0];						
					}
					if (isset( $first_user['cn'][0] )) {
						$this->resourceName=$first_user['cn'][0];    
					} 
				  $this->isLdap=1;
				  $this->name=$paramlogin;
				  $this->idProfile=Parameter::getGlobalParameter('ldapDefaultProfile');
				  $this->save();
					//debugLog("authenticate - Mode LDAP - Create user from LDAP - new id=".$this->id );
					$sendAlert=Parameter::getGlobalParameter('ldapMsgOnUserCreation');	
					if ($sendAlert!='NO') {
						$title="Project'Or RIA - " . i18n('newUser');
						$message=i18n("newUserMessage",array($paramlogin));
						if ($sendAlert=='MAIL' or $sendAlert=='ALERT&MAIL') {
							global $paramAdminMail;
						  sendMail($paramAdminMail, $title, $message);
						}
						if ($sendAlert=='ALERT' or $sendAlert=='ALERT&MAIL') {
							$prof=new Profile();
							$crit=array('profileCode'=>'ADM');
							$lstProf=$prof->getSqlElementsFromCriteria($crit,false);
							foreach ($lstProf as $prof) {
								$crit=array('idProfile'=>$prof->id);
								$lstUsr=$this->getSqlElementsFromCriteria($crit,false);
								foreach($lstUsr as $usr) {
									$alert=new Alert();
									$alert->idUser=$usr->id;
									$alert->alertType='INFO';
									$alert->alertInitialDateTime=date('Y-m-d h:i:s');
									$alert->message=$message;
									$alert->title=$title;
									$alert->alertDateTime=date('Y-m-d h:i:s');
									$alert->save();
								}
							}
						}
					}	
				}					
			}
	  }
	  return "OK";     
  }
}
?>