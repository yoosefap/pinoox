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


namespace pinoox\component\package\manager;


use pinoox\component\helpers\Data;
use pinoox\portal\Pinker;

class AppManager implements ManagerInterface
{
    private \pinoox\component\store\Pinker $pinker;
    private Data $data;

    public function __construct(private string $appFile, private array $defaultData = [])
    {
        $this->pinker = Pinker::file($this->appFile);
        $fileData = $this->pinker->pickup();

        $data = array_merge($this->defaultData, $fileData);

        $this->data = new Data($data);
    }


    /**
     * @inheritDoc
     */
    public function get(?string $key = null): mixed
    {
        return $this->data->get($key);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value): static
    {
        $this->data->set($key, $value);

        return $this;
    }

    /**
     * Add data in config
     *
     * @param string $key
     * @param mixed $value
     * @return AppManager
     */
    public function add(string $key, mixed $value): static
    {
        $this->data->add($key, $value);

        return $this;
    }

    /**
     * Remove data in config
     *
     * @param string $key
     * @return AppManager
     */
    public function remove(string $key): static
    {
        $this->data->remove($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function save(): static
    {
        $this->pinker->data($this->data->get())->bake();

        return $this;
    }
}