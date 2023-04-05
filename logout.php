<?php
session_start();
if (isset($_SESSION['user_id']))
{
    $_SESSION = array();
    if (isset($_COOKIE[session_name()]))
        setcookie(session_name(), '', time() - 3600);

    session_destroy();
}
setcookie('user_id','',time()-3600);
header('Location: /index.php');