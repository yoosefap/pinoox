<?php

use App\com_pinoox_manager\Controller\AppViewController;
use Pinoox\Portal\View;
use function Pinoox\Router\get;
use function Pinoox\Router\{route_match,any};

any('app/{packageName}', [AppViewController::class, 'run'])->name('app.run');
any('app/{packageName}/{subPath}', [AppViewController::class, 'run'])
    ->name('app.run.sub')
    ->defaults(['subPath' => ''])
    ->filters(['subPath' => '.+']);
get('*', fn () => View::render('main'))->name('fallback');

