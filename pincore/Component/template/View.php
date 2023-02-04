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


namespace pinoox\component\template;


use pinoox\component\Dir;
use pinoox\component\package\App;
use pinoox\component\template\engine\PhpEngine;
use pinoox\component\template\engine\PhpTwigEngine;
use pinoox\component\template\engine\TwigEngine;
use pinoox\component\template\parser\TemplateNameParser;
use pinoox\component\template\engine\DelegatingEngine;
use Twig\Extension\DebugExtension;
use Twig\Extension\StringLoaderExtension;

class View implements ViewInterface
{
    private DelegatingEngine $template;
    protected array $globals = [];
    protected TemplateNameParser $parser;
    protected PhpEngine $phpEngine;
    protected TwigEngine $twigEngine;
    protected PhpTwigEngine $phpTwigEngine;
    private array $readyRenders = [];

    /**
     * View constructor.
     *
     * @param string|array|null $folders
     * @param string|null $pathTheme
     */
    public function __construct(string|array $folders = null, string $pathTheme = null)
    {
        $this->setView($folders, $pathTheme);
    }

    /**
     * Set View
     * @param string|array|null $folders
     * @param string|null $pathTheme
     */
    public function setView(string|array $folders = null, string $pathTheme = null)
    {
        // theme names
        $folders = !empty($folders) ? $folders : App::get('theme');

        // base path
        $pathTheme = !empty($pathTheme) ? $pathTheme : Dir::path(App::get('path-theme'));

        // template name parser
        $this->parser = new TemplateNameParser();

        // instance engines
        $this->phpEngine = new PhpEngine($this->parser, $folders, $pathTheme); // .php engine
        $this->twigEngine = new TwigEngine($this->parser, $folders, $pathTheme); // .twig engine
        $this->phpTwigEngine = new PhpTwigEngine($this->parser, $this->phpEngine, $this->twigEngine); // .twig.php engine

        // set main template engine
        $this->template = new DelegatingEngine([
            $this->phpEngine,
            $this->twigEngine,
            $this->phpTwigEngine
        ]);

        // add twig extensions
        $this->twigEngine->template->enableDebug();
        $this->twigEngine->addExtension(new DebugExtension());
        $this->twigEngine->addExtension(new StringLoaderExtension());

        // add twig functions
        $this->twigEngine->addInternalFunction([
            'url',
            'furl',
            'lang' => 'rlang',
            'config',
            'app',
            'dd',
        ]);
    }

    /**
     * render view file
     *
     * @param string $name
     * @param array $parameters
     * @return string
     */
    public function renderFile(string $name, array $parameters = []): string
    {
        $parameters = array_replace($this->getAll(), $parameters);
        return $this->template->render($name, $parameters);
    }

    /**
     * exists view file
     *
     * @param string $name
     * @return bool
     */
    public function existsFile(string $name): bool
    {
        return $this->template->exists($name);
    }

    /**
     * exists view
     *
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        if ($this->existsFile($name))
            return true;

        $engines = $this->engines();
        foreach ($engines as $engine) {
            $name .= '.' . $engine;
            if ($this->existsFile($name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the assigned globals data.
     */
    public function getAll(): array
    {
        return $this->globals;
    }

    /**
     * Returns the assigned one global data.
     *
     * @param string|int $index
     * @return mixed
     */
    public function get(string|int $index): mixed
    {
        return isset($this->globals[$index]) ? $this->globals[$index] : null;
    }

    /**
     * Set global data
     *
     * @param string $name
     * @param mixed $value
     */
    public function set(string $name, mixed $value): void
    {
        $this->globals[$name] = $value;
    }

    /**
     * get all engines
     *
     * @return array
     */
    public function engines(): array
    {
        return $this->parser::ENGINES;
    }

    /**
     * render view
     *
     * @param string $name
     * @param array $parameters
     * @return string
     */
    public function render(string $name, array $parameters = []): string
    {
        if ($this->existsFile($name))
            return $this->renderFile($name,$parameters);

        $engines = $this->engines();
        foreach ($engines as $engine) {
            $filename = $name . '.' . $engine;
            if ($this->existsFile($filename)) {
                return $this->renderFile($filename, $parameters);
            }
        }

        $template = $this->parser->parse($name);
        throw new \InvalidArgumentException(sprintf('The template "%s" does not exist.', $template));
    }

    /**
     * add ready render
     *
     * @param string $name
     * @param array $parameters
     * @return View
     */
    public function ready(string $name = '', array $parameters = []): View
    {
        if (!empty($name))
            $this->readyRenders[$name] = $parameters;

        return $this;
    }

    /**
     * get content ready
     *
     * @return string
     */
    public function getContentReady(): string
    {
        $content = '';
        foreach ($this->readyRenders as $name => $parameters) {
            $content .= $this->render($name, $parameters);
        }

        return $content;
    }
}