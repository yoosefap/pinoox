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

namespace pinoox\app\com_pinoox_manager\controller\api\v1;

use pinoox\app\com_pinoox_manager\component\Wizard;
use pinoox\app\com_pinoox_manager\model\AppModel;
use pinoox\component\worker\Config;
use pinoox\component\Dir;
use pinoox\component\Download;
use pinoox\component\HelperHeader;
use pinoox\component\Lang;
use pinoox\component\Request;
use pinoox\component\Response;
use pinoox\component\Url;

class MarketController extends LoginConfiguration
{
    public function getDownloads()
    {
        $result = AppModel::fetch_all_downloads();
        Response::json($result);
    }

    public function deleteDownload()
    {
        $packageName = Request::inputOne('package_name', null, '!empty');

        if (empty($packageName))
            Response::json(Lang::get('manager.error_happened'), false);

        $pinFile = Wizard::get_downloaded($packageName);
        if (!is_file($pinFile))
            Response::json(Lang::get('manager.error_happened'), false);

        Wizard::deletePackageFile($pinFile);
        Config::init('market')
            ->delete($packageName)
            ->save();
        Response::json(Lang::get('manager.delete_successfully'), true);
    }


    private function getAuthParams($auth)
    {
        $pinVer = Config::init('~pinoox')->get();
        return [
            'token' => $auth['token'],
            'remote_url' => Url::site(),
            'user_agent' => HelperHeader::getUserAgent() . ';Pinoox/' . $pinVer['version_name'] . ' Manager',
        ];
    }

    public function getApps($keyword = '')
    {
        $data = Request::sendGet('https://www.pinoox.com/api/manager/v1/market/get/' . $keyword);
        HelperHeader::contentType('application/json', 'UTF-8');
        echo $data;
    }

    public function getOneApp($packageName)
    {
        $data = Request::sendGet("https://www.pinoox.com/api/manager/v1/market/getApp/" . $packageName);
        HelperHeader::contentType('application/json', 'UTF-8');
        $arr = json_decode($data, true);
        $arr['state'] = Wizard::app_state($packageName);
        Response::json($arr);
    }

    public function downloadRequest($packageName)
    {
        $app = AppModel::fetch_by_package_name($packageName);
        if (!empty($app))
            Response::json(rlang('manager.currently_installed'), false);

        $auth = Request::inputOne('auth');
        $params = $this->getAuthParams($auth);

        $res = Request::sendPost('https://www.pinoox.com/api/manager/v1/market/downloadRequest/' . $packageName, $params);
        if (!empty($res)) {
            $response = json_decode($res, true);
            if (!$response['status']) {
                exit($res);
            } else {
                $path = path("downloads>apps>" . $packageName . ".pin");
                Download::fetch('https://www.pinoox.com/api/manager/v1/market/download/' . $response['result']['hash'], $path)->process();
                Config::init('market')
                    ->set($packageName, $response['result'])
                    ->save();
                Response::json(rlang('manager.download_completed'), true);
            }
        }
    }

    /*-----------------------------------------------------------
    * Templates
    */

    public function getTemplates($packageName)
    {
        $data = Request::sendGet('https://www.pinoox.com/api/manager/v1/market/getAppTemplates/' . $packageName);
        HelperHeader::contentType('application/json', 'UTF-8');
        $result = json_decode($data, true);
        $templates = [];
        if (!empty($result)) {
            foreach ($result as $t) {
                //check template state
                $t['state'] = Wizard::template_state($packageName, $t['uid']);
                $t['type'] = 'theme';
                $templates[] = $t;
            }
        }

        Response::json($templates);
    }


    public function downloadRequestTemplate($uid)
    {
        $data = Request::input('auth,package_name', null, '!empty');
        $params = $this->getAuthParams($data['auth']);

        if (!Wizard::is_installed($data['package_name']))
            exit();

        $res = Request::sendPost('https://www.pinoox.com/api/manager/v1/market/downloadRequestTemplate/' . $uid, $params);
        if (!empty($res)) {
            $response = json_decode($res, true);
            if (!isset($response['status']) || !$response['status']) {
                exit($res);
            } else {
                $path = path("downloads>templates>$uid.pin");
                Download::fetch('https://www.pinoox.com/api/manager/v1/market/downloadTemplate/' . $response['result']['hash'], $path)->process();
                Response::json(rlang('manager.download_completed'), true);
            }
        }
    }

}
