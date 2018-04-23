<?php

namespace NCentral\Command;

use Consolidation\Config\Config;
use Solarwinds\Soap\customerList;
use Solarwinds\Soap\tKeyPair;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class customerListCommand extends Command {
    protected $config;

    public function __construct(?string $name = null, Config $config) {
        parent::__construct($name);

        $this->config = $config;
    }

    protected function configure() {
        $this
            ->setName('customer:list')
            ->setDescription('Retrieve list of customers');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $params = new customerList($this->config->get('user'), $this->config->get('pass'), new tKeyPair('', ''));
        $result = $this->config->get('client')->customerList($params);
        $customers = $result->getReturn();

        if (!is_array($customers)) {
            return;
        }

        // Loop through the responses and build up and array of information
        $retrieved_info = [];
        $count = 0;
        foreach ($customers as $customer) {
            // Retrieve all the info for the customer
            $info = $customer->getInfo();

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
                $value['customer.customerid'],
                $value['customer.customername']
            ];
        }

        $io->table(
            ['Customer ID', 'Customer name'],
            $output_list
        );
    }
}