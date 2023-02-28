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

namespace pinoox\component;

use pinoox\component\helpers\Str;
use ZipArchive;

class Zip
{
    /**
     * ZipArchive Object
     */
    private array|null $entries = [];

    /**
     * ZipArchive Object
     * @var ZipArchive
     */
    private ZipArchive $zip;

    public function __construct()
    {
        $this->zip = new ZipArchive();
    }

    public function folders($zippedFile, $isJustCurrent = false, $dir = null): array
    {
        $files = $this->info($zippedFile, $isJustCurrent, $dir);
        $result = [];
        foreach ($files as $index => $file) {
            if ($file['is_dir'])
                $result[$index] = $file;
        }
        return $result;
    }

    public function info($zippedFile, $isJustCurrent = false, $dir = null): ?array
    {
        if (!is_file($zippedFile)) return null;

        $this->zip->open($zippedFile);
        $files = [];
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $stat = $this->zip->statIndex($i);

            if ($isJustCurrent) {
                if (!empty($dir) && !Str::lastHas($dir, '/')) $dir .= '/';
                $string = Str::firstDelete($stat['name'], $dir);
                $string = Str::lastDelete($string, '/');
                if (!$string || Str::has($string, ['/', '\\']) || !Str::firstHas($stat['name'], $dir)) continue;
            }
            $isDir = (Str::lastHas($stat['name'], ['/', '\\']));
            $files[] = [
                'filename' => $stat['name'],
                'filesize' => $stat['size'],
                'comp_size' => $stat['comp_size'],
                'comp_method' => $stat['comp_method'],
                'datetime' => Date::g('Y-m-d H:i:s', $stat['mtime']),
                'is_dir' => $isDir];
        }
        return $files;
    }

    public function files($zippedFile, $isJustCurrent = false, $dir = null): array
    {
        $files = $this->info($zippedFile, $isJustCurrent, $dir);
        $result = [];
        foreach ($files as $index => $file) {
            if (!$file['is_dir'])
                $result[$index] = $file;
        }
        return $result;
    }

    public function archive($source, $zipName = null, $overwrite = false, $no_file = array(), $ext = array(), $ext_action = "out")
    {
        $zipName = empty($zipName) ? Str::get_unique_string(is_array($source) ? $source[0] : $source, 'md5') . '.zip' : $zipName;
        if (is_array($source) || is_dir($source)) {
            if (!is_array($source))
                $files = File::get_files($source, $no_file, $ext, $ext_action);
            else {
                $files = $source;
            }
            if (empty($files))
                return false;
            $valid_files = array();
            foreach ($files as $f)
                if (file_exists($f))
                    $valid_files[] = $f;
            if (empty($valid_files))
                return false;
            $destination = pathinfo($source[0], PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $zipName;
            if (!file_exists($destination))
                $overwrite = false;
            if ($this->zip->open($destination, $overwrite ? ZipArchive::OVERWRITE : ZipArchive::CREATE) !== true)
                return false;
            foreach ($valid_files as $file) {
                $this->zip->addFile($file, basename($file));
            }
            $this->zip->close();
            return file_exists($destination) ? $zipName : false;
        } else if (is_file($source)) {
            if (!file_exists($source)) return false;

            $destination = str_replace(basename($source), '', $source) . $zipName;
            if (!file_exists($destination))
                $overwrite = false;
            if ($this->zip->open($destination, $overwrite ? ZipArchive::OVERWRITE : ZipArchive::CREATE) !== true)
                return false;
            $this->zip->addFile($source, basename($source));
            $this->zip->close();
            return file_exists($destination) ? $zipName : false;
        } else {
            return false;
        }

    }


    public function addEntries($filename): void
    {
        if (!is_array($this->entries))
            $this->entries = [];
        $this->entries[] = $filename;
    }

    public function entries($filenames): void
    {
        $this->entries = $filenames;
    }

    public function extract($zippedFile, $dir): bool
    {
        $zip = new ZipArchive;
        $res = $zip->open($zippedFile);
        if ($res === TRUE) {
            $zip->extractTo($dir, $this->entries);
            $this->entries = null;
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    public function remove($zippedFile, $path): bool
    {
        if ($this->zip->open($zippedFile) === TRUE) {
            if (!is_array($path))
                $path = [$path];

            foreach ($path as $p) {
                $this->zip->deleteName($p);
            }

            $this->zip->close();
            return true;
        } else {
            return false;
        }
    }
}