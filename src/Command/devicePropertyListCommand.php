<?php

namespace NCentral\Command;

use Consolidation\Config\Config;
use Solarwinds\Soap\devicePropertyList;
use Solarwinds\Soap\tKeyPair;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class devicePropertyListCommand extends Command {
    protected $config;

    public function __construct(?string $name = null, Config $config) {
        parent::__construct($name);

        $this->config = $config;
    }

    protected function configure() {
        $this
            ->setName('device:property:list')
            ->setDescription('Retrieve list of custom device properties')
            ->addArgument('device', InputArgument::REQUIRED, 'The device id to list properties for.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $id = $input->getArgument('device');

        $params = new devicePropertyList($this->config->get('user'), $this->config->get('pass'), $id);

        $result = $this->config->get('client')->devicePropertyList($params);
        $properties = $result->getReturn();

        if (!is_array($properties)) {
            return;
        }

        // Loop through the responses and build up and array of information
        $retrieved_info = [];
        $count = 0;
        foreach ($properties as $property) {
            // Retrieve all the info for the customer
            $info = $property->getProperties();

            if (is_array($info)) {
                // Loop through the info and set the keys/values
                foreach ($info as $details) {
                    $retrieved_info[$count][$details->getLabel()] = $details->getValue();
                }
                $count++;
            }
        }

        // Sort the array so its in customer id order
        asort($retrieved_info);

        $io = new SymfonyStyle($input, $output);

        // Output the customer id and customer name
        $output_list = [];
        foreach ($retrieved_info as $value) {
            foreach ($value as $label => $setting) {
                $output_list[] = [
                    $label,
                    $setting
                ];
            }
        }

        if (count($output_list)) {
            $io->table(
                ['Label', 'Value'],
                $output_list
            );
        }
        else {
            $io->text('No properties found');
        }
    }
}