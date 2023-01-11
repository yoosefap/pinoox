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


namespace pinoox\component\template\engine;


use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class TwigEngine implements EngineInterface
{
    private LoaderInterface $loader;
    private TemplateNameParserInterface $parser;
    private Environment $template;

    public function __construct(TemplateNameParserInterface $parser, LoaderInterface|string|array $folder, ?string $rootPath = null)
    {
        if ($folder instanceof LoaderInterface) {
            $this->loader = $folder;
        } else {
            $this->loader = new FilesystemLoader($folder, $rootPath);
        }
        $this->parser = $parser;
        $this->template = new Environment($this->loader);
    }

    public function setLoader(LoaderInterface $loader)
    {
        $this->template->setLoader($loader);
    }

    public function render(TemplateReferenceInterface|string $name, array $parameters = []): string
    {
        return $this->template->render($name, $parameters);
    }

    public function exists(TemplateReferenceInterface|string $name): bool
    {
        try {
            $this->template->load($name);
        } catch (LoaderError $e) {
            return false;
        }

        return true;
    }

    public function supports(TemplateReferenceInterface|string $name): bool
    {
        $reference = $this->parser->parse($name);

        return 'twig' === $reference->get('engine');
    }
}