<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Guzzle\Http\Exception\ClientErrorResponseException;

abstract class AbstractManager
{
    /**
     * @param ClientErrorResponseException $e
     */
    protected function handleNotFoundException(ClientErrorResponseException $e)
    {
        if ($e->getResponse()->getStatusCode() !== 404) {
            throw $e;
        }
    }

    /**
     * @param $configuration
     * @param $remoteConfiguration
     *
     * @return bool
     */
    protected function isUpToDate($configuration, $remoteConfiguration)
    {
        $diff = $this->array_diff_assoc_recursive($configuration, $remoteConfiguration);

        foreach ($remoteConfiguration as $k => $v) {
            if (!isset($configuration[$k])) {
                unset($remoteConfiguration[$k]);
            }
        }

        $diff = array_merge_recursive(
            $diff,
            $this->array_diff_assoc_recursive($remoteConfiguration, $configuration)
        );

        return empty($diff);
    }

    private function array_diff_assoc_recursive($array1, $array2) {
        $difference = array();
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->array_diff_assoc_recursive($value, $array2[$key]);
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
