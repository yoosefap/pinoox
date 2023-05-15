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


namespace pinoox\component\store\strategy;

use pinoox\component\store\data\DataInterface;
use pinoox\component\store\Pinker;

class FileConfigStrategy implements ConfigStrategyInterface
{

    public function __construct(private readonly Pinker $pinker, private DataInterface $data)
    {
        $this->load();
    }

    public function load(): void
    {
        $this->data = $this->data->fromArray($this->pinker->pickup());
    }

    public function save(): void
    {
        $this->pinker->data($this->data->getData())->bake();
    }

    public function set($key, $value): void
    {
        $this->data->set($key, $value);
    }

    public function get($key = null): array
    {
        return $this->data->get($key);
    }

    public function remove($key): void
    {
        $this->data->remove($key);
    }

    public function add(string $key, mixed $value): void
    {
        $this->data->add($key, $value);
    }

    public function reset(): void
    {
        $this->pinker->restore();
        $this->load();
    }

    public function merge(array $array): void
    {
        $this->data->merge($array);
    }
}