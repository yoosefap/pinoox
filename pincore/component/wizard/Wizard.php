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
use pinoox\component\Dir;
use pinoox\component\File;
use pinoox\component\helpers\Str;
use pinoox\component\kernel\Exception;
use pinoox\portal\Zip;
use function Composer\Autoload\includeFile;

class Wizard
{
    protected string $path;

    protected string $filename;

    protected string $fullPath;

    protected string $type;

    protected array $errors;

    protected string $tmpPathRoot = '__wizardTemp';

    protected string $tmpPathPackage;

    public function __construct(string $path, string $filename)
    {
        $this->path = $path;
        $this->filename = $filename;
        $this->fullPath = $this->path . $this->filename;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function glance(): string|bool|array
    {
        if (!file_exists($this->path)) {
            $this->setError("The given path isn't valid");
            return false;
        }

        $checkFile = $this->type === 'app' ? 'app.php' : 'meta.json';

        $zip = Zip::openFile($this->fullPath);

        $hasEntry = $zip->hasEntry($checkFile);
        if (!$hasEntry) {
            $this->setError("Doesn't exists '" . $checkFile . "' in the package!");
            return false;
        }

        $this->createTmp();

        $isOk = Zip::extractTo($this->tmpPathPackage, [$checkFile]);
        if ($isOk) {
            $info = [];
            return $this->getMetaEntry($zip, $info, $checkFile);
        }
        return false;
    }

    protected function setError(string $error): void
    {
        $this->errors[] = $error;
    }

    protected function getErrors(): array
    {
        return $this->errors;
    }

    protected function isSuccess(): bool
    {
        return empty($this->getErrors());
    }

    private function createTmp(): void
    {
        if (!is_dir($this->tmpPathRoot)) mkdir($this->tmpPathRoot);
        $this->tmpPathPackage = $this->tmpPathRoot . DS . basename($this->filename, '.pin');
        if (!is_dir($this->tmpPathPackage)) mkdir($this->tmpPathPackage);
    }

    private function getMetaEntry($zip, &$info, $checkFile)
    {
        $info = include $this->tmpPathPackage . DS . 'app.php';
        $entry = $zip->getEntry($checkFile);
        $info['__meta'] = [
            'filename' => $entry->getName(),
            'filesCount' => $zip->count(),
            'compressedSize' => $entry->getCompressedSize(),
            'uncompressedSize' => $entry->getUncompressedSize(),
            'time' => $entry->getATime(),
        ];
        return $info;
    }

}