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

namespace pinoox\component\http;

use pinoox\component\router\Collection;
use pinoox\component\router\Route;
use Symfony\Component\HttpFoundation\Request as RequestSymfony;

class Request extends RequestSymfony
{

    /**
     * get current Route
     *
     * @return Route|null
     */
    public function route(): Route|null
    {
        return @$this->get('_router');
    }

    /**
     * get current Collection
     *
     * @return Collection|null
     */
    public function collection(): Collection|null
    {
        return @$this->route()->getCollection();
    }
}