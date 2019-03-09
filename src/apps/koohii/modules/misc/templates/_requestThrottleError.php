<?php
/**
 * This partial is returned (renderPartial()) by actions that export data
 * and use RequestThrottler to prevent users from spamming server with heavy requests.
 * 
 */
?>
<?php #DBG::user(); echo 'NOW='.time(); ?>
<html>
<head>
  <title>Oops, please retry in a short moment</title>
</head>
<body>
<pre>
 /----------------------------------------------------------------------------------------------------------------\
 | Please wait at least one second before refreshing this page, this timer is to reduce the load on the database, |
 | thanks, your mate Kirby.                                                                                       |
 \-------v--------------------------------------------------------------------------------------------------------/

<?php  if (time() & 1): ?>
      <(^_^<)
<?php else: ?>
       (>^_^)>
<?php endif; ?>
</pre>
    <ul>
      <li><a href="javascript:history.go(-1)">Back</a></li>
    </ul>
</body>
</html>
