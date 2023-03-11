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


use pinoox\component\helpers\HelperString;
use pinoox\component\helpers\Str;
use pinoox\component\package\AppReference;
use pinoox\component\package\AppReferenceInterface;

class AppNameParser implements AppNameParserInterface
{

    public function parse(AppReferenceInterface|string $name): AppReferenceInterface
    {
        if ($name instanceof AppReferenceInterface) {
            return $name;
        }

        $parts = explode(':', $name);
        if (count($parts) > 1) {
            $app = $parts[0];
            $path = $parts[1];
        } else {
            $app = null;
            $path = $parts[0];
            if (Str::firstHas($path, '~')) {
                $app = '~';
                $path = HelperString::firstDelete($path, '~');
            }
        }

        return new AppReference($app, $path);
    }
}