<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       pinoox.com
 * @copyright  pinoox
 */


namespace pinoox\app\com_pinoox_manager\controller\api\v1;

use pinoox\portal\Config;
use pinoox\component\HelperString;
use pinoox\component\HttpRequest;
use pinoox\component\Request;
use pinoox\component\Response;
use pinoox\component\Url;
use pinoox\component\Validation;

class AccountController extends LoginConfiguration
{

    public function login()
    {
        $form = Request::input('email,password', null, '!empty');

        $valid = Validation::check($form, [
            'email' => ['required', rlang('user.username_or_email')],
            'password' => ['required', rlang('user.password')],
        ]);

        if ($valid->isFail())
            Response::json($valid->first(), false);

        $form['remote_url'] = Url::site();

        $data = Request::sendPost(
            'https://www.pinoox.com/api/manager/v1/account/login',
            $form,
            [
                'type' => HttpRequest::form,
                'timeout' => 8000
            ]
        );
        $array = json_decode($data, true);
        if ($array['status']) {
            Config::name('connect')
                ->set('token_key', $array['result']['token'])
                ->save();
        }
        exit($data);
    }

    public function getPinooxAuth()
    {
        $token_key = Config::name('connect')->get('token_key');
        $data = Request::sendPost(
            'https://www.pinoox.com/api/manager/v1/account/getData',
            [
                'remote_url' => Url::site(),
                'token_key' => $token_key,
            ],
            [
                'type' => HttpRequest::form,
                'timeout' => 8000
            ]
        );

        $data = HelperString::decodeJson($data);
        if ($data['status']) {
            Response::json($data['result']);
        }

        Response::json(null);
    }

    public function connect()
    {
        $data = Request::sendPost(
            'https://www.pinoox.com/api/manager/v1/account/getToken',
            [
                'remote_url' => Url::site(),
            ],
            [
                'type' => HttpRequest::form,
                'timeout' => 8000
            ]
        );

        $array = json_decode($data, true);
        if (!empty($array['token_key'])) {
            Config::name('connect')
                ->set('token_key', $array['token_key'])
                ->save();

            Response::json($array['token_key'], true);
        }

        Response::json(null, false);
    }

    public function getConnectData()
    {
        $data = Config::name('connect')->get();
        Response::json($data);
    }

    public function logout()
    {
        Config::name('connect')
            ->set('token_key', null)
            ->save();
    }
}