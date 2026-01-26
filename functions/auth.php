<?php
function isLoggedIn(): ?array
{
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return null;
}
