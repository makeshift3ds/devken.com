<?php

function smarty_modifier_underline($data)
{
  $data=str_replace("_"," ",$data);
  return $data;
}
?>  