<?php

  // no direct access
  defined('EMONCMS_EXEC') or die('Restricted access');

  function myelectric_controller()
  {
    global $session,$route;

    $result = false;

    if ($route->format == 'html')
    {
      if ($route->action == "view" && $session['write']) $result = view("Modules/myelectric/myelectric_view.php",array());
    }

    return array('content'=>$result);
  }

?>
