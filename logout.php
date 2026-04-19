<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Ends the active user session and redirects to the login page.
 */
// start the session so we can destroy it
session_start();
// wipe all session data
session_destroy();
// send them back to the login page
header('Location: index.html');
exit;