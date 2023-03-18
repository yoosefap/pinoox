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


namespace pinoox\component\package\engine;

use pinoox\component\package\reference\PathReferenceInterface;
use pinoox\component\store\Config;

class DelegatingEngine implements EngineInterface
{
    /**
     * @var EngineInterface[]
     */
    protected array $engines = [];

    /**
     * @param EngineInterface[] $engines An array of EngineInterface instances to add
     */
    public function __construct(array $engines = [])
    {
        foreach ($engines as $engine) {
            $this->addEngine($engine);
        }
    }

    public function config(PathReferenceInterface|string $packageName): Config
    {
        return $this->getEngine($packageName)->config($packageName);
    }

    public function exists(PathReferenceInterface|string $packageName): bool
    {
        return $this->getEngine($packageName)->exists($packageName);
    }

    public function supports(PathReferenceInterface|string $packageName): bool
    {
        try {
            $this->getEngine($packageName);
        } catch (\RuntimeException) {
            return false;
        }

        return true;
    }

    /**
     * Get an engine able to render the given template.
     *
     * @param string|PathReferenceInterface $packageName
     * @return EngineInterface
     */
    public function getEngine(string|PathReferenceInterface $packageName): EngineInterface
    {
        foreach ($this->engines as $engine) {
            if ($engine->supports($packageName)) {
                return $engine;
            }
        }

        throw new \RuntimeException(sprintf('No engine is able to work with the package name "%s".', $packageName));
    }

    public function addEngine(EngineInterface $engine): void
    {
        $this->engines[] = $engine;
    }
}