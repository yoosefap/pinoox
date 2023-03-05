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

class Wizard
{
    protected string $path;

    protected string $filename;

    protected string $package;

    protected string $type;

    protected array $errors;

    protected string $tmpPathRoot = PINOOX_CORE_PATH . 'pinker' . DS . 'wizard_tmp';

    protected string $packagePath;

    protected string $tmpPathPackage;

    private array $info;

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

        $this->package = basename($this->filename, '.pin');

        $this->packagePath = PINOOX_APP_PATH . $this->package . DS;

        $this->createTmp();
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

        //extract target file (app.php | meta.json)
        $this->extractTemp($targetFile);
        $this->loadInfo();

        //extract icon
        $this->extractTemp($this->getIconPath());
        $this->addIcon();

        $this->checkUpdate();

        return $this;
    }

    private function extractTemp(...$files): void
    {
        Zip::extractTo($this->tmpPathPackage, $files);
    }

    public function getInfo(): array|null
    {
        return $this->info;
    }

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

    private function loadInfo(): void
    {
        $this->info = include $this->tmpPathPackage . DS . 'app.php';
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
     * @return bool
     * @throws Exception
     */
    private function hasEntry(string $targetFile): bool
    {
        $has = $this->zip->hasEntry($targetFile);

        if (!$has) {
            $this->setError("Doesn't exists '" . $targetFile . "' in the package!");
            return false;
        }
        return true;
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


}