<?php
function isLoggedIn(): false|array
{
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return false;
}
