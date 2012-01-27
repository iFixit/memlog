<?php

/*
 * DO NOT USE THIS ON A PRODUCTION MACHINE.
 *
 * There is an easy way to get this script to basically dump out any file on
 * the machine that the web server user has read access to, which is absolutely
 * atrociously bad for security.
 *
 * This is a developer tool. Use it on a machine that only your developers have
 * access to. Failure to heed this advice means you are willing to accept
 * whatever punishment the intertubes inflict upon you.
 *
 * YOU HAVE BEEN WARNED.
 *
 * @author Bob Somers
 */

if (isset($_POST['logfile'])) {
   $logfile = base64_decode($_POST['logfile']);
} else if (isset($_GET['logfile'])) {
   header('Content-type: application/json');

   // HERE BE DRAGONS. This is insanely bad. Never ever do this.
   $lines = file(base64_decode($_GET['logfile']), FILE_IGNORE_NEW_LINES);

   $name = array_shift($lines);

   echo "{\n";
   echo "\t\"name\": " . json_encode($name) . ",\n";
   echo "\t\"data\": [\n";
   $first = true;
   foreach ($lines as $line) {
      if (!$first) {
         echo ",\n";
      } else {
         $first = false;
      }
      echo "\t\t" . $line;
   }
   echo "\n\t]\n";
   echo "}\n";

   exit();
}

?>
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>MemLog Viewer</title>

      <link href="http://fonts.googleapis.com/css?family=Ubuntu:700"
       rel="stylesheet" type="text/css">
      <link href="http://fonts.googleapis.com/css?family=Anonymous+Pro:400,700"
       rel="stylesheet" type="text/css">

      <script src="https://ajax.googleapis.com/ajax/libs/mootools/1.4.1/mootools-yui-compressed.js"
       type="text/javascript"></script>
      <script src="dygraph-combined.js"
       type="text/javascript"></script>
      <script src="memlog.js"
       type="text/javascript"></script>

      <?php if (isset($logfile)): ?>
      <script type="text/javascript">
         window.addEvent('domready', function() {
            new Request.JSON({
               url: '?logfile=<?php echo base64_encode($logfile); ?>',
               onSuccess: function(resp) {
                  $('name').set('text', resp.name);
                  memlog(resp.data);
               }
            }).get();
         });
      </script>
      <?php endif; ?>
      <style type="text/css">
         #codeloc {
            font: 24px 'Anonymous Pro';
            width 960px;
            text-align: right;
            line-height: 30px;
            width: 960px;
            height: 30px;
         }

         #choose {
            position: absolute;
            top: 28px;
            left: 480px;
         }

         h1 {
            font: bold 48px 'Ubuntu';
            margin: 0;
            padding-left: 20px;
            height: 64px;
            line-height: 64px;
            border-bottom: 1px solid #aaa;
         }

         h2 {
            font: bold 24px 'Ubuntu';
            margin: 0;
            padding: 10px 0 0 10px;
         }
      </style>
   </head>
   <body>
      <h1>MemLog Viewer</h1>
      <form id="choose" method="post">
         <select name="logfile">
         <?php
            $files = glob('/tmp/php_mem_usage*.log');
            rsort($files);
            foreach ($files as $file) {
               echo '<option value="' . base64_encode($file) . '"';
               if ($logfile == $file) {
                  echo ' selected';
               }
               echo ">$file</option>\n";
            }
         ?>
         </select>
         <input type="submit" value="View">
      </form>
<?php if (isset($logfile)): ?>

   <h2 id="name">Loading...</h2>
   <div id="codeloc"></div>
   <div id="graph"></div>

<?php endif; ?>
   </body>
</html>
