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

use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Twig\Loader\ArrayLoader;

class PhpTwigEngine implements EngineInterface
{

    private TemplateNameParserInterface $parser;
    private PhpEngine $php;
    private TwigEngine $twig;

    public function __construct(TemplateNameParserInterface $parser, PhpEngine $php, TwigEngine $twig)
    {
        $this->parser = $parser;
        $this->php = $php;
        $this->twig = $twig;
    }

    public function render(TemplateReferenceInterface|string $name, array $parameters = []): string
    {
        $loader = new ArrayLoader([
            $name => $this->php->render($name, $parameters),
        ]);
        $this->twig->setLoader($loader);
        return $this->twig->render($name, $parameters);
    }

    public function exists(TemplateReferenceInterface|string $name): bool
    {
        return true;
    }

    public function supports(TemplateReferenceInterface|string $name): bool
    {
        $template = $this->parser->parse($name);

        return 'twig.php' === $template->get('engine');
    }
}