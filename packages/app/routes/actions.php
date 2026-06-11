<?php

use App\__PINX_PACKAGE__\Controller\MainController;
use App\__PINX_PACKAGE__\Router\Actions;
use function Pinoox\Router\action;

action(Actions::HOME, [MainController::class, 'index']);
