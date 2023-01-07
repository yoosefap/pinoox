<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */


namespace pinoox\service;


use pinoox\component\http\Response;
use pinoox\component\router\Router;
use pinoox\component\interfaces\ServiceInterface;
use pinoox\component\kernel\Boot;
use pinoox\component\kernel\Container;
use pinoox\component\kernel\ContainerBuilder;
use pinoox\component\kernel\event\ResponseEvent;
use pinoox\component\kernel\Kernel;
use pinoox\component\kernel\listener\RequestListener;
use pinoox\component\kernel\listener\StringResponseListener;
use pinoox\component\http\Request;
use pinoox\component\kernel\resolver\TestResolver;
use pinoox\component\package\App;
use pinoox\component\package\AppRouter;
use pinoox\controller\ErrorController;
use Symfony\Component\EventDispatcher\DependencyInjection\AddEventAliasesPass;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use pinoox\component\kernel\controller\ControllerResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;

class BootService implements ServiceInterface
{

    public function _run()
    {
        (new Boot())->build();
    }

    public function _stop()
    {
    }


}