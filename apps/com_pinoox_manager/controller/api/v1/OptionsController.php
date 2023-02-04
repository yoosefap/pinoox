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

use pinoox\component\app\AppProvider;
use pinoox\portal\Config;
use pinoox\component\Dir;
use pinoox\component\Response;

class OptionsController extends LoginConfiguration
{
    public function changeBackground($name)
    {
        $path = Dir::theme('dist/images/backgrounds/' . $name . '.jpg');
        if (is_file($path)) {
            Config::name('options')
                ->set('background', $name)
                ->save();
            Response::json($name, true);
        }

        Response::json($name, false);
    }

    public function changeLockTime($minutes = 0)
    {
        switch ($minutes) {
            case 10:
                break;
            case 20:
                break;
            case 30:
                break;
            case 60:
                break;
            default:
                $minutes = 0;
                break;
        }

        $lock_time = Config::name('options')->get('lock_time');
        if ($lock_time != $minutes) {
            Config::name('options')
                ->set('lock_time', $minutes)
                ->save();
            Response::json($minutes, true);
        }

        Response::json($minutes, false);
    }
}
    
