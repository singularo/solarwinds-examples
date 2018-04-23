<?php

namespace NCentral\Command;

use Consolidation\Config\Config;
use Solarwinds\Soap\deviceList;
use Solarwinds\Soap\tKeyPair;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class deviceListCommand extends Command {
    protected $config;

    public function __construct(?string $name = null, Config $config) {
        parent::__construct($name);

        $this->config = $config;
    }

    protected function configure() {
        $this
            ->setName('device:list')
            ->setDescription('Retrieve list of devices')
            ->addArgument('customer', InputArgument::REQUIRED, 'The customer id to list devices for.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $id = $input->getArgument('customer');

        $filters[] = new tKeyPair('customerID', $id);
        $filters[] = new tKeyPair('devices', TRUE);

        $params = new deviceList($this->config->get('user'), $this->config->get('pass'), $filters);

        $result = $this->config->get('client')->deviceList($params);
        $devices = $result->getReturn();

        if (!is_array($devices)) {
            return;
        }

        // Loop through the responses and build up and array of information
        $retrieved_info = [];
        $count = 0;
        foreach ($devices as $device) {
            // Retrieve all the info for the customer
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
        foreach ($retrieved_info as $value) {
            $output_list[] = [
                $value['device.deviceid'],
                $value['device.longname'],
                $value['device.agentversion']
            ];
        }

        $io->table(
            ['Device ID', 'Longname', 'Agent version'],
            $output_list
        );
    }
}