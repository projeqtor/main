<?php
/* ============================================================================
 * Presents left menu of application. 
 */
  require_once "../tool/projector.php";
  scriptLog('   ->/view/menuBar.php');
 
  if ( ! $showTopMenu) {return;}
  
  function drawMenu($menu) {
    $menuName=$menu->name;
    $idMenu=$menu->id;
    if ($menu->type=='menu') {
    	if ($menu->idMenu==0) {
    		echo '<td class="menuBarSeparator" ></td>';
    	}
    } else if ($menu->type=='item') {
    	  $class=substr($menuName,4); 
        echo '<td class="menuBarItem" title="' .i18n($menu->name) . '">';
        echo '<img src="../view/css/images/icon' . $class . '16.png" onClick="loadMenuBarItem(\'' . $class . '\');" />';
        echo '</td><td>&nbsp;</td>';    	
    } else if ($menu->type=='object') { 
      $class=substr($menuName,4);
      if (securityCheckDisplayMenu($idMenu, $class)) {
      	echo '<td class="menuBarItem" title="' .i18n('menu'.$class) . '">';
      	echo '<img src="../view/css/images/icon' . $class . '16.png" onClick="loadMenuBarObject(\'' . $class . '\');" />';
      	echo '</td><td>&nbsp;</td>';
      }
    }
  }  
  
  function drawAllMenus() {
  	echo '<td class="menuBarSeparator"></td>';
    $obj=new Menu();
    $suspend=false;
    $menuList=$obj->getSqlElementsFromCriteria(null, false);
    $lastType='';
    foreach ($menuList as $menu) { 
    	if ($menu->id==36) {$suspend=true;}
    	if (! $suspend and securityCheckDisplayMenu($menu->id,$menu) ) {
    		drawMenu($menu);
    		$lastType=$menu->type;
    	}
    }
    if ($lastType!='menu') {
      echo '<td class="menuBarSeparator" ></td>';
    }
    $menu=new Menu('20');
    drawMenu($menu);
    echo '<td class="menuBarSeparator" ></td>';
	/*	    if ($level>0 and securityCheckDisplayMenu($menu->id,$menu) ) {
		      while ($level>0 and $menu->idMenu!= $menuLevel[$level]) {
		        drawMenuCloseChildren();
		      }
		    }
	    if ($menu->type=='class') {
	      drawMenuItemClass($menu->id,$menu->name);
	    } else if ($menu->type=='menu') {
	      drawMenuItem($menu->id,$menu->name,'menu', true);
	    } else if ($menu->type=='item') {
	      drawMenuItem($menu->id,$menu->name,'item', false);
	    } else if ($menu->type=='object') {
	      drawMenuItem($menu->id,$menu->name,'object', false);
	    } 
	  }
	  while ($level>0) {
	    drawMenuCloseChildren();
	  }*/
  }
?>
  <table width="100%"><tr>
    
    <td width="20%">
    <span class="titleProject">&nbsp;<?php echo (i18n("projectSelector"));?></span>
    <span style="height: 20px; color:#202020; " dojoType="dijit.form.DropDownButton" 
     id="selectedProject" jsId="selectedProject" name="selectedProject" showlabel="true" class="">
      <span width="200px" ><?php
        $proj='*'; 
        if (array_key_exists('project',$_SESSION)) {
          $proj=$_SESSION['project'];
        } else {
        	$_SESSION['project']="*";
        }
        if ($proj=='*') {
          echo '<i>' . i18n('allProjects') . '</i>';
        } else {
          $projObject=new Project($proj);
          echo htmlEncode($projObject->name);
        };
        $prj=new Project();
        $prj->id='*';
        $cpt=$prj->countMenuProjectsList();
        ?>
      </span>
      <?php ?>
      <span dojoType="dijit.TooltipDialog" class="white" <?php echo ($cpt>25)?'style="width:300px;"':'';?>>   
        <div <?php echo ($cpt>25)?'style="height: 500px; overflow-y: scroll;"':'';?>>    
         <?php 
           $prj=new Project();
           $prj->id='*';
           echo $prj->drawSubProjects('selectedProject', false, true, true); 
         ?>
        </div>       
      </span>
    </span>
    </td>
    <td width="3px"></td>
    <td>
      <table><tr>
    <?php drawAllMenus();?>
    </tr></table>
    </td>
    <td class="menuBarSeparator" ></td>
    <td width="60px" align="center">
      <button id="menuBarUndoButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonUndoItem');?>"
       disabled="disabled"
       iconClass="dijitEditorIcon dijitEditorIconUndo" >
        <script type="dojo/connect" event="onClick" args="evt">
          undoItemButton();
        </script>
      </button>    
      <button id="menuBarRedoButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonRedoItem');?>"
       disabled="disabled"
       iconClass="dijitEditorIcon dijitEditorIconRedo" >
        <script type="dojo/connect" event="onClick" args="evt">
          redoItemButton();
        </script>
      </button>    
    </td>
  </tr></table>