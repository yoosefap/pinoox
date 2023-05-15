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

use pinoox\component\store\strategy\ConfigStrategyInterface;

class ConfigManager
{
    private ConfigStrategyInterface $strategy;

    public function __construct(ConfigStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
        $this->strategy->load();
    }

    public function save(): void
    {
        $this->strategy->save();
    }

    public function get(string $key = null): mixed
    {
        return $this->strategy->get($key);
    }

    public function add(string $key, mixed $value): ConfigManager
    {
        $this->strategy->add($key, $value);
        return $this;
    }

    public function set(string $key, mixed $value): ConfigManager
    {
        $this->strategy->set($key, $value);
        return $this;
    }


    public function remove(string $key): ConfigManager
    {
        $this->strategy->remove($key);
        return $this;
    }

    public function merge(array $array): ConfigManager
    {
        $this->strategy->merge($array);
        return $this;
    }

    public function reset(): ConfigManager
    {
        $this->strategy->reset();
        return $this;
    }

    public function setLinear(string $key, string $target, mixed $value)
    {
        $this->strategy->set($key . '.' . $target, $value);
        return $this;
    }

    public function getLinear(string $key, string $target)
    {
        $this->strategy->get($key . '.' . $target);
        return $this;
    }


}
