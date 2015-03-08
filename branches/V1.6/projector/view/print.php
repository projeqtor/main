<?php
/* ============================================================================
 * Print page of application.
 */
   require_once "../tool/projector.php";
   set_time_limit(300);
   ob_start();
   $outMode='html';
   $printInNewPage=false;
   if (array_key_exists('outMode', $_REQUEST)) {
     if ($_REQUEST['outMode']) {
       $outMode=$_REQUEST['outMode'];
     }
   }
   if ($outMode!='pdf') {
     header ('Content-Type: text/html; charset=UTF-8');
   } else {
     $printInNewPage=true;
     if (isset($paramMemoryLimitForPDF)) {
       $limit=$paramMemoryLimitForPDF;	
     } else {
     	 $limit='';
     }
     if ($limit===0) {
     	 header ('Content-Type: text/html; charset=UTF-8');
     	 echo "<html><head></head><body>";
     	 echo i18n("msgPdfDisabled");
     	 echo "</body></html>";
     	 return;
     } else if ($limit=='') {
       // Keep existing
     } else {
      ini_set("memory_limit", $limit.'M');
     }
   }
   scriptLog('   ->/view/print.php'); 
  if ($outMode!='pdf') {?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<?php }?>
<html>
<head>   
  <title><?php echo i18n("applicationTitle");?></title>
  <link rel="stylesheet" type="text/css" href="css/jsgantt.css" />
  <link rel="stylesheet" type="text/css" href="css/projectorPrint.css" />
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
  <script type="text/javascript" src="../external/dojo/dojo.js"
    djConfig='modulePaths: {i18n: "../../tool/i18n"},
              parseOnLoad: true, 
              isDebug: <?php echo getBooleanValueAsString($paramDebugMode);?>'></script>
  <script type="text/javascript" src="../external/dojo/projectorDojo.js"></script>
  <script type="text/javascript"> 
    dojo.require("dojo.parser");
    dojo.require("dojo.i18n");
    //dojo.require("dojo.date.locale");
    dojo.addOnLoad(function(){
      <?php 
        if (array_key_exists('directPrint', $_REQUEST)) {
          echo "window.print();";
          //echo "window.close();";
        }
      ?>
      top.hideWait();
    }); 
  </script>
</head>
<page backtop="100px" backbottom="20px" footer="page">
<<?php echo ($printInNewPage or $outMode=='pdf')?'body':'div';?> id="bodyPrint" class="white" onload="top.hideWait();";>
  <?php 
  $includeFile=$_REQUEST['page'];
  if (! substr($_REQUEST['page'],0,3)=='../') {
    $includeFile.='../view/';
  }
  if (strpos($includeFile,'?')>0) {
    $params=substr($includeFile,strpos($includeFile,'?')+1);
    $includeFile=substr($includeFile,0,strpos($includeFile,'?'));
    $paramArray=explode('&',$params);
    foreach ($paramArray as $param) {
      $par=explode('=',$param);
      $_REQUEST[$par[0]]=$par[1];
    }
  }
  include $includeFile;
?>
</<?php echo ($printInNewPage or $outMode=='pdf')?'body':'div';?>>
</page>
</html>
<?php 
  finalizePrint();
?>
<?php function finalizePrint() {
  global $outMode;
  $pdfLib='html2pdf';
  //$pdfLib='dompdf';
  if ($outMode=='pdf') {
    $content = ob_get_clean(); 
    if ($pdfLib=='html2pdf') {
      /* HTML2PDF way */
      require_once('../external/html2pdf/html2pdf.class.php');
      $html2pdf = new HTML2PDF('L','A4','en');
      $html2pdf->pdf->SetDisplayMode('fullpage');
      $html2pdf->setDefaultFont('freesans');
      $html2pdf->setTestTdInOnePage(false);
      //$html2pdf->setDefaultFont('uni2cid_ag15');
      $html2pdf->writeHTML($html2pdf->getHtmlFromPage($content)); 
      $html2pdf->Output();
    } else if ($pdfLib=='dompdf') {
    /* DOMPDF way */
      require_once("../external/dompdf/dompdf_config.inc.php");
      $dompdf = new DOMPDF();
      $dompdf->load_html($content);
      $dompdf->render();
      $dompdf->stream("sample.pdf");
    }
  }
}
?>