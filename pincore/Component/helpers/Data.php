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


namespace pinoox\component\helpers;


class Data
{
    private mixed $data;
    private array $merge;

    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    private function dataPreparationByInput(string $key)
    {
        $temp = &$this->data;
        $parts = explode('.', $key);
        $countKeys = count($parts) - 1;
        $key = null;
        for ($i = 0; $i <= $countKeys; $i++) {
            $key = $parts[$i];
            if (($i != $countKeys)) {
                if (!isset($temp[$key]))
                    $temp[$key] = [];
                else if (!is_array($temp[$key]))
                    $temp[$key] = [$temp[$key]];

                $temp = &$temp[$key];
            }
        }
    }

    public function add(string $key, mixed $value): static
    {
        $this->dataPreparationByInput($key);

        if (!isset($this->data[$key])) {
            $this->data[$key] = [$value];
        } else {
            if (!is_array($this->data[$key]))
                $this->data[$key] = [$this->data[$key]];
            $this->data[$key][] = $value;
        }

        return $this;
    }

    public function set(string $key, mixed $value): static
    {
        $this->dataPreparationByInput($key);

        $this->data[$key] = $value;

        return $this;
    }

    public function merge(mixed $data)
    {
        $this->merge[] = $data;
    }

    public function get(?string $key = null): mixed
    {
        return $this->pullData($key, true);
    }

    public function getData(?string $key = null): mixed
    {
        return $this->pullData($key, false);
    }

    private function pullData(?string $key = null, $isMerge = false): mixed
    {
        $data = $this->data;

        if (is_array($data) && $isMerge && !empty($this->merge)) {
            $merge = $this->merge;
            $merge[] = $data;
            $data = array_merge(...$merge);
        }

        if (is_null($key)) return $data;

        $parts = explode('.', $key);
        if (is_array($data)) {
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

        return $data;
    }

    public function remove(string $key): static
    {
        $this->dataPreparationByInput($key);

        unset($this->data[$key]);

        return $this;
    }
}