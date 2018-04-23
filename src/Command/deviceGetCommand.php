<?php

namespace NCentral\Command;

use Consolidation\Config\Config;
use Solarwinds\Soap\deviceGet;
use Solarwinds\Soap\tKeyPair;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class deviceGetCommand extends Command {
    protected $config;

    public function __construct(?string $name = null, Config $config) {
        parent::__construct($name);

        $this->config = $config;
    }

    protected function configure() {
        $this
            ->setName('device:get')
            ->setDescription('Retrieve device information')
            ->addArgument('device', InputArgument::REQUIRED, 'The device id to retrieve information for.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $id = $input->getArgument('device');

        $filters[] = new tKeyPair('deviceID', $id);

        $params = new deviceGet($this->config->get('user'), $this->config->get('pass'), $filters);

        $result = $this->config->get('client')->deviceGet($params);
        $devices = $result->getReturn();

        if (!is_array($devices)) {
            return;
        }

        // Loop through the responses and build up and array of information
        $retrieved_info = [];
        $count = 0;
        foreach ($devices as $device) {
            // Retrieve all the info
            $info = $device->getInfo();

            // Loop through the info and set the keys/values
            foreach ($info as $details) {
                $retrieved_info[$count][$details->getKey()] = $details->getValue();
            }
            $count++;
        }

        // Sort the array so its in customer id order
        asort($retrieved_info);

        $io = new SymfonyStyle($input, $output);

        // Output the customer id and customer name
        $output_list = [];
        foreach ($retrieved_info as $value) {
            $output_list[] = [
                array_pop($value['device.deviceid']),
                array_pop($value['device.customerid']),
                array_pop($value['device.customername']),
                array_pop($value['device.licensemode'])
            ];
        }

        $io->table(
            ['Device ID', 'Customer ID', 'Customer name', 'License mode'],
            $output_list
        );
    }
}