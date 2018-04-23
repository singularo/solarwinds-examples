#!/usr/bin/php -q
<?php
/**
 * Utility to work with an n-central server
 */

namespace NCentral;

require_once __DIR__ . '/vendor/autoload.php';

use Solarwinds\Soap\ServerEI2SoapBindingImplService;

use NCentral\Command\activeIssuesCommand;
use NCentral\Command\customerListCommand;
use NCentral\Command\deviceGetCommand;
use NCentral\Command\deviceListCommand;
use NCentral\Command\devicePropertyListCommand;
use Symfony\Component\Console\Application;

use Consolidation\Config\Config;
use Consolidation\Config\Loader\YamlConfigLoader;
use Consolidation\Config\Loader\ConfigProcessor;

// Load config from config.yml and store it
$config = new Config();
$loader = new YamlConfigLoader();
$processor = new ConfigProcessor();
$processor->extend($loader->load('config.yml'));
$config->import($processor->export());

// Setup the service to talk to
$client = new ServerEI2SoapBindingImplService('https://' . $config->get('host') . '/dms2/services2/ServerEI2?wsdl');
$config->set('client', $client);

$application = new Application('ncentral', '0.1');

$application->add(new activeIssuesCommand(NULL, $config));
$application->add(new customerListCommand(NULL, $config));
$application->add(new deviceGetCommand(NULL, $config));
$application->add(new deviceListCommand(NULL, $config));
$application->add(new devicePropertyListCommand(NULL, $config));

$application->run();
