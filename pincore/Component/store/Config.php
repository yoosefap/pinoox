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

namespace pinoox\component\store;

use pinoox\component\helpers\HelperArray;
use pinoox\component\helpers\HelperString;
use pinoox\component\package\App;

class Config
{
    /**
     * key data
     *
     * @var string
     */
    private string $key;

    /**
     * name config
     *
     * @var string
     */
    private string $name;

    /**
     * app current
     *
     * @var ?string
     */
    private ?string $app = null;

    /**
     * filename data
     *
     * @var string
     */
    private string $filename;

    /**
     * Pinker instance
     *
     * @var Pinker
     */
    private Pinker $pinker;

    /**
     * Data config
     *
     * @var array
     */
    private static array $data = [];

    /**
     * Config constructor
     *
     * @param Pinker $pinker
     * @param string|null $name
     */
    public function __construct(Pinker $pinker, ?string $name = null)
    {
        $this->pinker = $pinker;
        $this->initData($name);
    }

    public function name(string $name): Config
    {
        $this->initData($name);
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getApp(): string
    {
        return !empty($this->app) ? $this->app : App::package();
    }

    /**
     * Set file for pinoox baker
     *
     * @param string $name
     */
    private function initData(string $name): void
    {
        $this->name = $name;
        $this->app = null;
        $parts = explode(':', $name);
        if (count($parts) === 2) {
            $this->app = $parts[0];
            $name = $parts[1];
        }

        $name = str_replace(['/', '\\'], '>', $name);
        $filename = $name;
        if (HelperString::firstHas($name, '~')) {
            $filename = HelperString::firstDelete($filename, '~');
            $appDefault = '~';
        } else {
            $appDefault = App::package();
        }

        $this->app = !empty($app) ? $app : $appDefault;

        $file = 'config/' . $filename . '.config.php';
        $file = ($this->app === '~') ? '~' . $file : $file;
        $this->pinker->file($file);

        $this->key = $this->app . ':' . $filename;
        if (!isset(self::$data[$this->key])) {
            $value = $this->pinker->pickup();
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
    public function setLinear(string $pointer, ?string $key, mixed $value): Config
    {
        $data = $this->get($pointer);
        $data = is_array($data) ? $data : [];
        $data[$key] = $value;
        $this->set($pointer, $data);

        return $this;
    }

    /**
     * Get data from config
     *
     * @param string|null $value
     * @return mixed|null
     */
    public function get(?string $value = null): mixed
    {
        $data = @self::$data[$this->key];

        if (!is_null($value)) {
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
        return $this->pinker->getInfo($key);
    }

    /**
     * Set data in config
     *
     * @param string $key
     * @param mixed $value
     * @return Config
     */
    public function set(string $key, mixed $value): Config
    {
        HelperArray::pushingData($key, $value, 'set', self::$data[$this->key]);
        return $this;
    }

    /**
     * Set data in config
     *
     * @param mixed|null $value
     * @return Config
     */
    public function data(mixed $value): Config
    {
        self::$data[$this->key] = $value;
        return $this;
    }

    /**
     * Get target data from config
     *
     * @param string|null $pointer
     * @param string|null $key
     * @return mixed
     */
    public function getLinear(?string $pointer, ?string $key): mixed
    {
        $data = $this->get($pointer);
        return $data[$key] ?? null;
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
        return $this;
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

        return $this;
    }

    /**
     * Reset data in config with config file
     *
     * @return Config
     */
    public function reset(): Config
    {
        $value = $this->pinker->pickup();
        self::data($value);

        return $this;
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
        return $this;
    }

    /**
     * Save data on config file
     *
     * @return Config
     */
    public function save(): Config
    {
        $data = $this->get();
        $this->pinker->data($data)->bake();

        return $this;
    }
}
    
