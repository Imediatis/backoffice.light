<?php
$timeout = 900;
ini_set('session.gc_maxlifetime', $timeout);
ini_set("session.cookie_lifetime", $timeout);

session_start();

!isset($_COOKIE[session_name()]) || $_COOKIE[session_name()] = session_id();

if (isset($_COOKIE[session_name()])) {
	setcookie(session_name(), $_COOKIE[session_name()], time() + $timeout, "/", "", false, true);
}

require '../bootstrap/app.php';

$app->run();