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

use PhpZip\Exception\ZipEntryNotFoundException;
use PhpZip\Exception\ZipException;
use PhpZip\ZipFile;
use pinoox\component\kernel\Exception;
use pinoox\portal\Zip;

abstract class Wizard
{
    protected string $path;

    protected string $filename;

    protected string $package;

    protected string $type;

    protected array $errors;

    protected string $tmpPathRoot = PINOOX_CORE_PATH . 'pinker' . DS . 'wizard_tmp';

    protected string $packagePath;

    protected string $tmpPathPackage;

    protected array $info;

    protected bool $isUpdate = false;

    protected ZipFile $zip;

    /**
     * @throws Exception
     */
    private function initPath($path): void
    {
        $this->path = $path;

        if (!$this->isExists()) {
            throw new Exception($this->getErrors(true));
            return;
        }

        $this->filename = basename($this->path);

        $this->createTmp();
    }

    protected function setPackage(): void
    {

        $this->package = $this->info['package'];
        $this->packagePath = PINOOX_APP_PATH . $this->package . DS;
    }

    /**
     * @throws Exception
     */
    public function open(string $path): Wizard
    {
        $this->initPath($path);

        $this->zip = Zip::openFile($this->path);

        $targetFile = $this->targetFile();
        $this->hasEntry($targetFile);

        return $this;
    }

    protected function extractTemp(...$files): void
    {
        Zip::extractTo($this->tmpPathPackage, $files);
    }

    abstract public function getInfo(): array|null;

    /**
     * @throws Exception
     */
    protected function setError(string $error): void
    {
        $this->errors[] = $error;
        throw new Exception($error);
    }

    public function getErrors($last = false): mixed
    {
        if (!isset($this->errors)) return false;
        if ($last) return end($this->errors);
        return $this->errors;
    }

    private function createTmp(): void
    {
        if (!is_dir($this->tmpPathRoot)) mkdir($this->tmpPathRoot);
        $this->tmpPathPackage = $this->tmpPathRoot . DS . basename($this->filename, '.pin');
        if (!is_dir($this->tmpPathPackage)) mkdir($this->tmpPathPackage);
    }

    /**
     * @throws ZipEntryNotFoundException
     */
    public function getMeta(): array
    {
        $entry = $this->zip->getEntry($this->targetFile());
        return [
            'filename' => $entry->getName(),
            'filesCount' => $this->zip->count(),
            'compressedSize' => $entry->getCompressedSize(),
            'uncompressedSize' => $entry->getUncompressedSize(),
            'time' => $entry->getATime(),
        ];
    }

    /**
     * @throws Exception
     */
    private function isExists(): bool
    {
        if (!file_exists($this->path)) {
            $this->setError('package not found: "' . $this->path . '"');
            return false;
        }
        return true;
    }

    /**
     * Target file is an essential [app.php for apps OR meta.json for templates] file that must exists in a package knows as a valid package
     *
     * @return string
     */
    protected function targetFile(): string
    {
        return $this->type === 'app' ? 'app.php' : 'meta.json';
    }

    /**
     * getEntry used for fetch specific file from .pin
     *
     * @param string $targetFile
     * @return void
     * @throws Exception
     */
    private function hasEntry(string $targetFile): void
    {
        $has = $this->zip->hasEntry($targetFile);

        if (!$has) {
            $this->setError("Doesn't exists '" . $targetFile . "' inside the package!");
        }
    }

    protected function checkUpdate(): bool
    {
        return $this->isUpdate = file_exists(PINOOX_APP_PATH . $this->package);
    }

    /**
     * @throws ZipException
     */
    protected function extract($path): ZipFile
    {
        return $this->zip->extractTo($path)->deleteFromRegex('~^\.~');
    }


    /**
     * @throws Exception
     */
    protected function getExistsPackageInfo(): bool|array
    {
        $existsInfo = include PINOOX_APP_PATH . $this->package . DS . $this->targetFile();
        if (empty($existsInfo)) {
            $this->setError('The package is not valid because there is no essential file inside (Doesn\'t exists "' . $this->targetFile() . '" in "' . $this->package . '")');
            return false;
        }
        return $existsInfo;
    }

    protected function loadTargetFileFromPin(): void
    {
        if ($this->type == 'template') {
            $this->info = json_decode(file_get_contents($this->tmpPathPackage . DS . $this->targetFile()), true);
        } else {
            $this->info = include $this->tmpPathPackage . DS . $this->targetFile();
        }

        $this->setPackage();

        $this->checkUpdate();
    }

}