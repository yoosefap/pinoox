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


namespace pinoox\component\package\parser;

use pinoox\component\package\AppReferenceInterface;

interface AppNameParserInterface
{
    /**
     * Convert a template name to a TemplateReferenceInterface instance.
     * @param string|AppReferenceInterface $name
     * @return AppReferenceInterface
     */
    public function parse(string|AppReferenceInterface $name): AppReferenceInterface;
}