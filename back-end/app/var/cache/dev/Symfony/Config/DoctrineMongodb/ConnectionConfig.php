<?php

namespace Symfony\Config\DoctrineMongodb;

require_once __DIR__.\DIRECTORY_SEPARATOR.'ConnectionConfig'.\DIRECTORY_SEPARATOR.'OptionsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ConnectionConfig'.\DIRECTORY_SEPARATOR.'DriverOptionsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ConnectionConfig'.\DIRECTORY_SEPARATOR.'AutoEncryptionConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ConnectionConfig 
{
    private $server;
    private $options;
    private $driverOptions;
    private $autoEncryption;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function server($value): static
    {
        $this->_usedProperties['server'] = true;
        $this->server = $value;

        return $this;
    }

    public function options(array $value = []): \Symfony\Config\DoctrineMongodb\ConnectionConfig\OptionsConfig
    {
        if (null === $this->options) {
            $this->_usedProperties['options'] = true;
            $this->options = new \Symfony\Config\DoctrineMongodb\ConnectionConfig\OptionsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "options()" has already been initialized. You cannot pass values the second time you call options().');
        }

        return $this->options;
    }

    public function driverOptions(array $value = []): \Symfony\Config\DoctrineMongodb\ConnectionConfig\DriverOptionsConfig
    {
        if (null === $this->driverOptions) {
            $this->_usedProperties['driverOptions'] = true;
            $this->driverOptions = new \Symfony\Config\DoctrineMongodb\ConnectionConfig\DriverOptionsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "driverOptions()" has already been initialized. You cannot pass values the second time you call driverOptions().');
        }

        return $this->driverOptions;
    }

    public function autoEncryption(array $value = []): \Symfony\Config\DoctrineMongodb\ConnectionConfig\AutoEncryptionConfig
    {
        if (null === $this->autoEncryption) {
            $this->_usedProperties['autoEncryption'] = true;
            $this->autoEncryption = new \Symfony\Config\DoctrineMongodb\ConnectionConfig\AutoEncryptionConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "autoEncryption()" has already been initialized. You cannot pass values the second time you call autoEncryption().');
        }

        return $this->autoEncryption;
    }

    public function __construct(array $value = [])
    {
        if (array_key_exists('server', $value)) {
            $this->_usedProperties['server'] = true;
            $this->server = $value['server'];
            unset($value['server']);
        }

        if (array_key_exists('options', $value)) {
            $this->_usedProperties['options'] = true;
            $this->options = new \Symfony\Config\DoctrineMongodb\ConnectionConfig\OptionsConfig($value['options']);
            unset($value['options']);
        }

        if (array_key_exists('driver_options', $value)) {
            $this->_usedProperties['driverOptions'] = true;
            $this->driverOptions = new \Symfony\Config\DoctrineMongodb\ConnectionConfig\DriverOptionsConfig($value['driver_options']);
            unset($value['driver_options']);
        }

        if (array_key_exists('autoEncryption', $value)) {
            $this->_usedProperties['autoEncryption'] = true;
            $this->autoEncryption = new \Symfony\Config\DoctrineMongodb\ConnectionConfig\AutoEncryptionConfig($value['autoEncryption']);
            unset($value['autoEncryption']);
        }

        if ([] !== $value) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($value)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['server'])) {
            $output['server'] = $this->server;
        }
        if (isset($this->_usedProperties['options'])) {
            $output['options'] = $this->options->toArray();
        }
        if (isset($this->_usedProperties['driverOptions'])) {
            $output['driver_options'] = $this->driverOptions->toArray();
        }
        if (isset($this->_usedProperties['autoEncryption'])) {
            $output['autoEncryption'] = $this->autoEncryption->toArray();
        }

        return $output;
    }

}
