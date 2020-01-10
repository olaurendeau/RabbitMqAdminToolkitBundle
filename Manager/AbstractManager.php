<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Http\Client\Exception\HttpException as ClientException;

abstract class AbstractManager
{
    /**
     * @param ClientException $e
     */
    protected function handleNotFoundException(ClientException $e): void
    {
        if ($e->getResponse()->getStatusCode() !== 404) {
            throw $e;
        }
    }

    /**
     * @param array $configuration
     * @param array $remoteConfiguration
     *
     * @return bool
     */
    protected function isUpToDate(array $configuration, array $remoteConfiguration): bool
    {
        $diff = $this->arrayDiffAssocRecursive($configuration, $remoteConfiguration);

        foreach ($remoteConfiguration as $k => $v) {
            if (!isset($configuration[$k])) {
                unset($remoteConfiguration[$k]);
            }
        }

        $diff = array_merge_recursive(
            $diff,
            $this->arrayDiffAssocRecursive($remoteConfiguration, $configuration)
        );

        return empty($diff);
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    private function arrayDiffAssocRecursive(array $array1, array $array2): array
    {
        $difference = [];

        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->arrayDiffAssocRecursive($value, $array2[$key]);
                    if (!empty($new_diff)) {
                        $difference[$key] = $new_diff;
                    }
                }
            } else if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                $difference[$key] = $value;
            }
        }

        return $difference;
    }
}
