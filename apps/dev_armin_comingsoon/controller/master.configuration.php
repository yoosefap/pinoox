<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @license  https://opensource.org/licenses/MIT MIT License
 */
namespace pinoox\app\dev_armin_comingsoon\controller;

use pinoox\component\interfaces\ControllerInterface;
use pinoox\component\Response;
use pinoox\component\Template;

class MasterConfiguration implements ControllerInterface{

    protected static $template;

    public function __construct()
    {
        self::$template = new Template();
    }

    public function _main()
    {
        Response::redirect(url());
    }

    public function _exception()
    {
        Response::redirect(url());
    }
}
