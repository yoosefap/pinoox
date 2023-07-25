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

use pinoox\component\Config;
use pinoox\component\Request;
use pinoox\component\Response;
use pinoox\component\Url;
use pinoox\component\User;

class MainController extends MasterConfiguration
{

    public function _main()
    {
        self::$template->set('background', Url::theme('assets/images/tehran.jpg'));
        self::$template->show('home');
    }

    public function panel()
    {
        if(!User::isLoggedIn())
            Response::redirect(url());

        if(Request::isPost())
        {
            $form = Request::post('title,description,twitter,instagram,linkedin,telegram',null,'!empty');

            Config::set('app',$form);
            Config::save('app');
            Response::redirect(url());
        }

        self::$template->show('panel');
    }
}