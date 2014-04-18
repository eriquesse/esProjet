<?php
  if (count($_GET) > 0){
    if (isset($_GET['voir']))
      include_once($_GET['voir'] . ".php");
  } else
    echo file_get_contents("index.html");
?>
