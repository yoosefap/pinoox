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

use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Templating\PhpEngine as PhpEngineSymfony;

class PhpEngine implements EngineInterface
{
    private FilesystemLoader $loader;
    private TemplateNameParserInterface $parser;
    private PhpEngineSymfony $template;

    public function __construct(TemplateNameParserInterface $parser, string|array $folder, ?string $rootPath = null)
    {
        $paths = $this->getPaths($rootPath, $folder);
        $this->loader = new FilesystemLoader($paths);
        $this->parser = $parser;
        $this->template = new PhpEngineSymfony($this->parser, $this->loader, [new SlotsHelper()]);
    }

    private function getPaths(?string $rootPath, string|array $folders): array|string
    {
        $paths = [];
        if (is_array($folders)) {
            foreach ($folders as $folder) {
                $paths[] = $this->getPaths($rootPath, $folder);
            }
        } else {
            $paths = $rootPath . '/' . $folders .'/'. '%name%';
        }

        return $paths;
    }

    public function render(TemplateReferenceInterface|string $name, array $parameters = []): string
    {
        return $this->template->render($name, $parameters);
    }

    public function exists(TemplateReferenceInterface|string $name): bool
    {
        return $this->template->exists($name);
    }

    public function supports(TemplateReferenceInterface|string $name): bool
    {
        $reference = $this->parser->parse($name);

        return 'php' === $reference->get('engine');
    }
}