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

use pinoox\component\package\reference\PathReferenceInterface;

interface ParserInterface
{
    /**
     * Convert a template name to a TemplateReferenceInterface instance.
     * @param string|PathReferenceInterface $name
     * @return PathReferenceInterface
     */
    public function parse(string|PathReferenceInterface $name): PathReferenceInterface;
}