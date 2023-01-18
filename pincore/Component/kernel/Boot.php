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

namespace pinoox\component\kernel;

use Closure;
use pinoox\component\Console;
use pinoox\component\http\Request;
use pinoox\component\kernel\listener\ExceptionListener;
use pinoox\component\kernel\resolver\RouteValueResolver;
use pinoox\component\template\View;
use pinoox\component\Url;
use Symfony\Component\HttpFoundation\Response;
use pinoox\component\router\Router;
use pinoox\component\kernel\resolver\ContainerControllerResolver;
use pinoox\component\kernel\event\ResponseEvent;
use pinoox\component\kernel\listener\ActionRoutesManageListener;
use pinoox\component\kernel\listener\ViewListener;
use pinoox\component\package\App;
use pinoox\component\package\AppRouter;
use pinoox\controller\ErrorController;
use Symfony\Component\EventDispatcher\DependencyInjection\AddEventAliasesPass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\SessionValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Boot
{
    public static ?Request $request = null;
    public static Closure $next;

    public function build()
    {
        self::$request = Request::createFromGlobals();
        $core = Container::pincore();
        $layer = AppRouter::find();
        App::setLayer($layer);
        $this->buildContainer($core);
        if ( is_null(Url::request())){
            Global $argv ;
            Console::run($argv);
            exit;
        }
        App::run();
    }

    private function buildContainer(ContainerBuilder $container): void
    {
        $this->setParameters($container);
        $this->addEvents($container);
        $this->registerView($container);
        $this->registerListeners($container);
        $this->registerResolvers($container);
        $this->registerDispatcher($container);
        $this->registerRoutes($container);
        $this->registerSerializer($container);
        $this->registerKernel($container);
        $this->setNext($container);
    }

    private static function setRoute()
    {
        $router = Router::getMainCollection();
        Container::pincore()->removeDefinition('routes');
        Container::pincore()->set('routes', $router->routes);
    }

    public static function handle(?Request $request = null)
    {
        self::setRoute();
        $request = !empty($request) ? $request : self::$request;
        $container = Container::pincore();
        $response = $container->get('kernel')->handle($request);
        $response->send();
    }

    private function setNext(ContainerBuilder $container): void
    {
        // self::$request->setSession(new Session());
        self::$next = function ($request) use ($container): Response {
            return $container->get('kernel')->handle($request);
        };
        //  $container->get('dispatcher')->dispatch(new ResponseEvent($response, self::$request), 'response');
    }

    private function registerListeners(ContainerBuilder $container): void
    {
        $container->register('listener.router', RouterListener::class)
            ->setArgument('matcher', Container::ref('matcher'))
            ->setArgument('requestStack', Container::ref('request_stack'))
            ->setArgument('context', null)
            ->setArgument('logger', null)
            ->setArgument('projectDir', null)
            ->setArgument('debug', false);

        $container->register('listener.view', ViewListener::class);
        $container->register('listener.e', ExceptionListener::class);

        $container->register('listener.controller', ActionRoutesManageListener::class);

        $container->register('listener.response', ResponseListener::class)
            ->setArguments(['%charset%']);

        $container->register('listener.exception', ErrorListener::class)
            ->setArguments([[ErrorController::class, 'exception']]);
    }

    private function registerDispatcher(ContainerBuilder $container): void
    {
        $container->register('dispatcher', EventDispatcher::class)
            ->addMethodCall('addSubscriber', [Container::ref('listener.router')])
            ->addMethodCall('addSubscriber', [Container::ref('listener.response')])
            ->addMethodCall('addSubscriber', [Container::ref('listener.exception')])
            ->addMethodCall('addSubscriber', [Container::ref('listener.e')])
            ->addMethodCall('addSubscriber', [Container::ref('listener.controller')])
            ->addMethodCall('addSubscriber', [Container::ref('listener.view')]);
    }

    private function registerResolvers(ContainerBuilder $container): void
    {
        $container->register('controller_resolver', ContainerControllerResolver::class)->setArguments([
            Container::ref('service_container'),
        ]);

        $resolvers = [
            new RequestAttributeValueResolver(),
            new RequestValueResolver(),
            new RouteValueResolver(),
            new SessionValueResolver(),
            new DefaultValueResolver(),
            new VariadicValueResolver(),
        ];

        $container->register('argument_resolver', ArgumentResolver::class)->setArguments([
            null,
            $resolvers,
        ]);
    }

    private function addEvents(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddEventAliasesPass(
            [
                ResponseEvent::class => 'response',
            ]
        ));
    }

    private function setParameters(ContainerBuilder $container): void
    {
        $container->setParameter('charset', 'UTF-8');
    }

    private function registerRoutes(ContainerBuilder $container): void
    {
        $container->register('request_stack', RequestStack::class);
        $container->register('context', RequestContext::class);
        $container->register('matcher', UrlMatcher::class)
            ->setArguments([Container::ref('routes'), Container::ref('context')]);
        $container->register('url_generator', UrlGenerator::class)
            ->setArguments([Container::ref('routes'), Container::ref('context')]);

        // dd($router->routes);
        // dd(Route::path('blog_yoosef_test'));
    }

    private function registerKernel(ContainerBuilder $container): void
    {
        $container->register('kernel', Kernel::class)
            ->setArguments([
                Container::ref('dispatcher'),
                Container::ref('controller_resolver'),
                Container::ref('request_stack'),
                Container::ref('argument_resolver'),
            ]);
    }

    private function registerView(ContainerBuilder $container)
    {
        $container->register('view', View::class);
    }

    private function registerSerializer(ContainerBuilder $container): void
    {
        $container->register('serializer.encoder.json', JsonEncoder::class);
        $container->register('serializer.encoder.xml', XmlEncoder::class);
        $container->register('serializer.encoder.csv', CsvEncoder::class);

        $container->register('serializer.normalizer', ObjectNormalizer::class);

        $container->register('serializer', Serializer::class)
            ->setArguments([
                [
                    Container::ref('serializer.normalizer')
                ],
                [
                    Container::ref('serializer.encoder.json'),
                    Container::ref('serializer.encoder.xml'),
                    Container::ref('serializer.encoder.csv')
                ]
            ]);
    }
}