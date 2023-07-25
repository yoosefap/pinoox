<?php

use pinoox\component\User;

function isLogin()
{
    return User::isLoggedIn();
}