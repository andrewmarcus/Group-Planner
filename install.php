<?php
// Create the data directories
foreach (array('_data', '_attendees', '_details', '_members') as $dir) {
  $path = dirname(__FILE__) . '/' . $dir;
  if (!is_dir($path)) {
    mkdir($path);
  }
}


?>
<!DOCTYPE html>
<html>
<head>
  <title>Event Planner - Welcome!</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>

</body>
</html>