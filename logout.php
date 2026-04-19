<?php
// start the session so we can destroy it
session_start();
// wipe all session data
session_destroy();
// send them back to the login page
header('Location: index.html');
exit;