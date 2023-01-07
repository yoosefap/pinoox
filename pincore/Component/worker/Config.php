<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace pinoox\component\worker;

use pinoox\component\Dir;
use pinoox\component\helpers\HelperArray;
use pinoox\component\helpers\HelperString;
use pinoox\component\package\App;
use pinoox\component\package\AppBuilder;

class Config
{
    /**
     * key data
     *
     * @var string
     */
    private string $key;

    /**
     * filename data
     *
     * @var string
     */
    private string $filename;

    /**
     * Data config
     *
     * @var array
     */
    private static array $data = [];

    /**
     * Instance class
     *
     * @var Config
     */
    private static Config $obj;

    /**
     * Instance class
     *
     * @var Config[]
     */
    private static array $objects;

    /**
     * Config constructor
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->initData($name);
    }

    /**
     * Config init
     * @param string $name
     * @return Config|null
     */
    public static function init(string $name): Config
    {
        $app = App::package();
        $key = $app . ':' . $name;
        if (!isset(self::$objects[$key])) {
            self::$objects[$key] = new Config($name);
        }

        self::$obj = self::$objects[$key];
        return self::$obj;
    }

    /**
     * Set file for pinoox baker
     *
     * @param string $name
     */
    private function initData(string $name)
    {
        $name = str_replace(['/', '\\'], '>', $name);
        $filename = $name;
        if (HelperString::firstHas($name, '~')) {
            $filename = HelperString::firstDelete($filename, '~');
            $app = '~';
        } else {
            $app = App::package();
        }

        $filename = 'config/' . $filename . '.config.php';
        $this->filename = ($app === '~') ? '~' . $filename : $filename;

        $this->key = $app . ':' . $name;
        if (!isset(self::$data[$this->key])) {
            $value = Pinker::init($this->filename)->pickup();
            self::$data[$this->key] = $value;
        }
    }

    /**
     * Set target data in config
     *
     * @param string $pointer
     * @param string|null $key
     * @param mixed $value
     * @return Config
     */
    public function setLinear(string $pointer, ?string $key, $value): Config
    {
        $data = $this->get($pointer);
        $data = is_array($data) ? $data : [];
        $data[$key] = $value;
        $this->set($pointer, $data);

        return self::$obj;
    }

    /**
     * Get data from config
     *
     * @param string|null $value
     * @return mixed|null
     */
    public function get(?string $value = null)
    {
        $data = @self::$data[$this->key];

        if (!empty($value)) {
            if (is_array($data)) {
                $parts = explode('.', $value);
                foreach ($parts as $value) {

                    if (isset($data[$value])) {
                        $data = $data[$value];
                    } else {
                        $data = null;
                        break;
                    }
                }
            } else {
                $data = null;
            }
        }


        return $data;
    }

    /**
     * Get info from config
     *
     * @param string|null $key
     * @return array
     */
    public function getInfo(?string $key = null): array
    {
        return Pinker::init($this->filename)->getInfo($key);
    }

    /**
     * Set data in config
     *
     * @param string $key
     * @param mixed $value
     * @return Config
     */
    public function set(string $key, $value): Config
    {
        HelperArray::pushingData($key, $value, 'set', self::$data[$this->key]);
        return self::$obj;
    }

    /**
     * Set data in config
     *
     * @param mixed|null $value
     * @return Config
     */
    public function data($value): Config
    {
        self::$data[$this->key] = $value;
        return self::$obj;
    }

    /**
     * Get target data from config
     *
     * @param string|null $pointer
     * @param string|null $key
     * @return mixed|null
     */
    public function getLinear(?string $pointer, ?string $key)
    {
        $data = $this->get($pointer);
        return isset($data[$key]) ? $data[$key] : null;
    }

    /**
     * Delete data in config
     *
     * @param string $key
     * @return Config
     */
    public function delete(string $key): Config
    {
        HelperArray::pushingData($key, null, 'del', self::$data[$this->key]);
        return self::$obj;
    }

    /**
     * Delete target data in config
     *
     * @param string $pointer
     * @param string|null $key
     * @return Config
     */
    public function deleteLinear(string $pointer, ?string $key): Config
    {
        $data = $this->get($pointer);
        $data = is_array($data) ? $data : [];
        unset($data[$key]);
        $this->set($pointer, $data);

        return self::$obj;
    }

    /**
     * Reset data in config with config file
     *
     * @return Config
     */
    public function reset(): Config
    {
        $value = Pinker::init($this->filename)->pickup();
        $this->data($value);

        return self::$obj;
    }

    /**
     * Add data in config
     *
     * @param string $key
     * @param string $value
     * @return Config
     */
    public function add(string $key, string $value): Config
    {
        HelperArray::pushingData($key, $value, 'add', self::$data[$this->key]);
        return self::$obj;
    }

    /**
     * Save data on config file
     *
     * @return Config
     */
    public function save(): Config
    {
        $data = $this->get();
        Pinker::init($this->filename)->data($data)->bake();

        return self::$obj;
    }
}
    
