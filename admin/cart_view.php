<?php
include('admin_header.php');

$Core = Registry::getKey('Core');
$Admin = Registry::getKey('Admin');
$Smarty = Registry::getKey('Smarty');

$Smarty->display('admin/cart_view.html');
?>