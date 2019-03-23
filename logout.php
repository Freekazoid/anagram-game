<?php
session_start();
unset($_SESSION);
$_SESSION['_key_'] = '';
session_destroy();
?>