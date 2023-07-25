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
     * @throws Exception
     */
    public function install(): array|bool
    {
        if ($this->isInstalled() && !$this->force) {
            $this->setError('The package is already installed');
            return false;
        }

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

    /**
     * Check if the package is installed.
     *
     * @return bool Returns true if the package is installed, false otherwise.
     */
    public function isInstalled(): bool
    {
        return is_dir(PINOOX_APP_PATH . $this->package);
    }


    public function getInfo(): array|null
    {
        return $this->info;
    }
}