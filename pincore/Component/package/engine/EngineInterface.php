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


use pinoox\component\package\AppReferenceInterface;
use RuntimeException;

interface EngineInterface
{
    /**
     * Renders an App.
     *
     * @param string|AppReferenceInterface $packageName
     * @return string
     * @throws RuntimeException if the template cannot be rendered
     */
    public function render(string|AppReferenceInterface $packageName): string;

    /**
     * Returns true if the App exists.
     *
     * @param string|AppReferenceInterface $packageName
     * @return bool
     * @throws RuntimeException if the engine cannot handle the App name
     */
    public function exists(string|AppReferenceInterface $packageName): bool;

    /**
     * Returns true if this class is able to render the given App.
     * @param string|AppReferenceInterface $packageName
     * @return bool
     */
    public function supports(string|AppReferenceInterface $packageName): bool;
}