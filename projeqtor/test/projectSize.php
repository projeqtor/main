<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 * 
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

projeqtor_set_time_limit(3600);

// PREPARE TESTS
// => remove mail sending, to avoid spamming
//Sql::query('UPDATE statusmail set idle=1');

$dir="..";

function sizeOfDir($dir) {
  $res=array($dir=>array('name'=>$dir,'size'=>0,'files'=>0));
  if (is_dir($dir)) {
    if ($dirHandler = opendir($dir)) {
      while (($file = readdir($dirHandler)) !== false) {
        if ($file!="." and $file!="..") {
          $fileName=$dir . '/' .$file;
          if (is_file($fileName)) {
            $res[$dir][size]+=filesize($fileName);
            $res[$dir][files]++;
          } else if (is_dir($fileName)) {
            $sub=sizeOfDir($fileName);
            $res[$dir][size]=  
            $res[$dir][files]++;
            $res=array_merge($res,$sub);
          }
        }
      }
    }
  }
}
var_dump($res);