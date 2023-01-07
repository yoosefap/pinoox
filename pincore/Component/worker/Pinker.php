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


namespace pinoox\component\worker;

use pinoox\component\helpers\HelperAnnotations;
use pinoox\component\File;
use pinoox\component\helpers\HelperObject;
use pinoox\component\helpers\HelperString;
use pinoox\component\Dir;

/**
 * Pinoox Baker
 * @package pinoox\component\worker
 */
class Pinker
{
    /**
     * name of the folder to store Pinoox baker data
     */
    const folder = 'pinker';

    /**
     * Instance class
     * @var Pinker|null
     */
    private static ?Pinker $obj = null;

    /**
     * Data for pinoox baker
     * @var mixed
     */
    private $data = null;

    /**
     * Data dump status
     * @var mixed
     */
    private $dumping = false;

    /**
     * Info for pinoox baker
     * @var ?array
     */
    private ?array $info = null;

    /**
     * File baked storage location
     * @var string
     */
    private string $file;

    /**
     * File baked storage location
     * @var string|null
     */
    private ?string $mainFile = null;


    /**
     * PinooxBaker constructor
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->file($fileName);
    }

    /**
     * PinooxBaker init
     * @param string $fileName
     * @return Pinker|null
     */
    public static function init(string $fileName): ?Pinker
    {
        self::$obj = new Pinker($fileName);

        return self::$obj;
    }

    /**
     * Set data for pinoox baker
     *
     * @param mixed $data
     * @return Pinker
     */
    public function data($data): ?Pinker
    {
        $this->data = $data;

        return self::$obj;
    }

    /**
     * Set info for pinoox baker
     *
     * @param array $info
     * @return Pinker
     */
    public function info(array $info): ?Pinker
    {
        $this->info = $info;

        return self::$obj;
    }

    /**
     * get info for pinoox baker
     *
     * @param string|null $key
     * @return mixed|null
     */
    public function getInfo(?string $key = null): ?array
    {
        $info = HelperAnnotations::getTagsCurrentBlock($this->file);
        return !is_null($key)? @$info[$key] : $info;
    }

    /**
     * Change data dump status
     *
     * @param bool $status
     * @return Pinker
     */
    public function dumping(bool $status = true): ?Pinker
    {
        $this->dumping = $status;

        return self::$obj;
    }


    /**
     * Set file for pinoox baker
     *
     * @param string $fileName
     * @return Pinker
     */
    private function file(string $fileName): ?Pinker
    {
        $fileName = Dir::ds($fileName);
        if (!HelperString::firstHas($fileName, '~')) {
            $mainFile = Dir::path($fileName);
            $file = Dir::path(self::folder . '/' . $fileName);
        } else {
            $fileName = HelperString::firstDelete($fileName, '~');
            $mainFile = Dir::path('~pincore/' . $fileName);
            $file = Dir::path('~pincore/' . self::folder . '/' . $fileName);
        }

        $this->file = $file;
        $this->mainFile = is_file($mainFile) ? $mainFile : null;

        return self::$obj;
    }

    /**
     * Bake file
     */
    public function bake(): ?Pinker
    {
        if (!$this->dumping) {
            $config = $this->format($this->generateData());
        } else {
            $config = $this->transmutation();
        }

        File::generate($this->file, $config);
        return self::$obj;
    }

    /**
     * Data storage format
     *
     * @param mixed $data
     * @return string
     */
    private function format($data): string
    {
        $tags = $this->generateInformation();
        $docBlock = HelperAnnotations::generateDocBlock('Pinoox Baker', $tags);

        return '<?' . 'php' . "\n" .
            $docBlock . "\n\n" .
            'return ' . var_export($data, true) . ';';
    }

    private function transmutation()
    {
        $data = $this->generateData();
        $replaces = [];
        $printData = [];

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $k = HelperString::camelToUnderscore($k);
                if (is_callable($v)) {
                    $replaces['{_{' . $k . '}_}'] = HelperObject::closure_dump($v);
                    $printData[$k] = '{_{' . $k . '}_}';
                } else {
                    $printData[$k] = $v;
                }
            }
        } else if (is_callable($data)) {
            $replaces['{_{dump}_}'] = HelperObject::closure_dump($data);
            $printData = '{_{dump}_}';
        } else {
            $printData = $data;
        }

        $printData = $this->format($printData);

        foreach ($replaces as $k => $v) {
            $printData = str_replace("'$k'", $v, $printData);
        }

        return $printData;
    }

    /**
     * Generate info for bake
     *
     * @return array
     */
    private function generateInformation(): array
    {
        $data = $this->data;
        $mainInfo = $this->info;
        $mainInfo = !is_array($mainInfo) ? [] : $mainInfo;
        $info = [];

        if (is_array($data) && isset($data['__pinker__']) && $data['__pinker__'] == true) {
            $info = !is_array($data['info']) ? [] : $data['info'];;
        }

        return array_merge(
            [
                'time' => time(),
            ],
            $info,
            $mainInfo,
        );
    }

    /**
     * Generate data for bake
     *
     * @return mixed
     */
    private function generateData()
    {
        $data = $this->data;
        if (is_array($data) && isset($data['__pinker__']) && $data['__pinker__'] == true) {
            return @$data['data'];
        }

        return $data;
    }

    /**
     * Get the baked file information
     */
    public function pickup()
    {
        $data = $this->getData();
        $info = $this->getInfo();
        $lifetime = @$info['lifetime'];

        if (!empty($lifetime) && is_numeric($lifetime)) {
            $lifetime = @$info['time'] + $lifetime;

            if ($lifetime < time()) {
                $this->remove();
                $data = $this->getData();
            }
        }

        return @$data;
    }

    /**
     * Remove the baked file
     */
    public function remove()
    {
        File::remove_file($this->file);
    }

    /**
     * get config data file
     *
     * @return mixed
     */
    private function getData()
    {
        if (!is_file($this->file) && !empty($this->mainFile)) {
            $this->data = (include $this->mainFile);
            $this->bake();
        }

        return is_file($this->file) ? (include $this->file) : null;
    }

    /**
     * Building pinker data
     *
     * @param mixed $data
     * @param array $info
     * @return array
     */
    public static function build($data, array $info = []): array
    {
        if (is_callable($data)) {
            $data = $data();
        }

        return [
            'data' => $data,
            'info' => $info,
            '__pinker__' => true,
        ];
    }
}