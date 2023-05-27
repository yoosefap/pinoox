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
            'message' => 'Package was installed successfully',
            'listFiles' => $zip->getListFiles(),
        ];
    }

    public function open(string $path): Wizard
    {
        parent::open($path);

        //extract target file (app.php)
        $this->extractTemp($this->targetFile());
        $this->loadTargetFileFromPin();

        //extract icon
        $this->extractTemp($this->getIconPath());
        $this->addIcon();

        return $this;
    }

    private function addIcon(): void
    {
        if (!isset($this->info)) return;
        $this->info['icon_path'] = $this->tmpPathPackage . DS . $this->info['icon'];
    }

    private function getIconPath()
    {
        return $this->info['icon'] ?? null;
    }

    /**
     * @throws Exception
     */
    public function isUpdateAvailable(): bool
    {
        if (!$this->isUpdate) return false;

        $existsInfo = $this->getExistsPackageInfo();
        return $existsInfo['version-code'] <= $this->getInfo()['version-code'];
    }

    public function getInfo(): array|null
    {
        return $this->info;
    }
}