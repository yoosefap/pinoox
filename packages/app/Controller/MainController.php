<?php

namespace App\__PINX_PACKAGE__\Controller;

use Pinoox\Component\Kernel\Controller\Controller;
use Pinoox\Portal\View;

class MainController extends Controller
{
    public function index()
    {
        return View::render('hello', [
            'title' => '__PINX_DISPLAY_NAME__',
            'message' => '__PINX_DESCRIPTION__',
        ]);
    }
}
