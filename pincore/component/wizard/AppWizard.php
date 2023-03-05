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

namespace pinoox\component\wizard;

use PhpZip\Exception\ZipException;
use pinoox\component\kernel\Exception;

class AppWizard extends Wizard implements WizardInterface
{

    public function __construct()
    {
        $this->type('app');
    }

    public function type(string $type)
    {
        $this->type = $type;
    }

    /**
     * @throws ZipException
     */
    public function install(): array
    {
        $zip = $this->extract($this->packagePath);
        return [
            'message' =>  'Package was installed successfully',
            'listFiles' => $zip->getListFiles(),
        ];
    }

    /**
     * @throws Exception
     */
    public function isUpdateAvailable(): bool
    {
        if (!$this->isUpdate) return false;

        $info = $this->getExistsPackageInfo();
        return $info['version-code'] < $this->getInfo()['version-code'];
    }

    /**
     * @throws Exception
     */
    private function getExistsPackageInfo(): bool|array
    {
        $existsInfo = include PINOOX_APP_PATH . $this->package . DS . $this->targetFile();
        if (empty($existsInfo)) {
            $this->setError('The package is not valid because there is no essential file inside (Doesn\'t exists "' . $this->targetFile() . '" in "' . $this->package . '")');
            return false;
        }
        return $existsInfo;
    }
}