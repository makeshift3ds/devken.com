<?php

include("conf/initiate.php");
$Smarty->display($path.".html"); 

if($_REQUEST['debug'] == 'benchmark' && $_SESSION['super_user']) $profiler->display(Registry::getKey('Database'));
exit;
?>
