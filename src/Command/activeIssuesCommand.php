<?php

namespace NCentral\Command;

use Consolidation\Config\Config;
use Solarwinds\Soap\activeIssuesList;
use Solarwinds\Soap\tKeyPair;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class activeIssuesCommand extends Command {
    protected $config;

    public function __construct(?string $name = null, Config $config) {
        parent::__construct($name);

        $this->config = $config;
    }

    protected function configure() {
        $this
            ->setName('active:issues')
            ->setDescription('Retrieve list of active issues')
            ->addArgument('customer', InputArgument::REQUIRED, 'The customer id to list active issues for.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $id = $input->getArgument('customer');

        $filters[] = new tKeyPair('customerID', $id);
        $filters[] = new tKeyPair('NOC_View_Status_Filter', 'normal');
        $filters[] = new tKeyPair('NOC_View_Status_Filter', 'warning');
        $filters[] = new tKeyPair('NOC_View_Status_Filter', 'failed');

        $params = new activeIssuesList($this->config->get('user'), $this->config->get('pass'), $filters);
        print "Retrieving active issues for $id\n";

        $result = $this->config->get('client')->activeIssuesList($params);
        $found_issues = $result->getReturn();

        if (!is_array($found_issues)) {
            return;
        }

        // Loop through the responses and build up and array of information
        $retrieved_info = [];
        $count = 0;
        foreach ($found_issues as $issue) {
            // Retrieve all the info for the customer
            $info = $issue->getIssue();

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
                $value['activeissue.customerid'],
                $value['activeissue.devicename'],
                $value['activeissue.deviceid'],
                $value['activeissue.servicename']
            ];
        }

        $io->table(
            ['Customer ID', 'Device name', 'Device ID', 'Service name'],
            $output_list
        );
    }

}


