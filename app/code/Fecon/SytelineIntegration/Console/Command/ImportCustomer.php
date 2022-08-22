<?php

namespace Fecon\SytelineIntegration\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\Module\Dir\Reader;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Math\Random;

class ImportCustomer extends Command
{
    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";

    /** @var Reader */
    protected $moduleReader;

    /** @var Csv */
    protected $csv;

    /** @var Customer */
    protected $customerModel;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /**
     * @param string $name
     * @param Reader $moduleReader
     * @param Csv $csv
     */
    public function __construct(
        Reader $moduleReader,
        Csv $csv,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        Random $mathRandom
    ) {
        parent::__construct();
        $this->moduleReader = $moduleReader;
        $this->csv = $csv;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->mathRandom = $mathRandom;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $name = $input->getArgument(self::NAME_ARGUMENT);
        $option = $input->getOption(self::NAME_OPTION);

        $moduleDir = $this->moduleReader->getModuleDir("", 'Fecon_SytelineIntegration');
        $fileName = $moduleDir . "/Data/customer.csv";
        $csvData = $this->csv->getData($fileName);

        foreach ($csvData as $row => $data) {
            if ($row > 0){
                $availableCustomer = $this->getCustomerByEmail($data[0], $data[3]);
                if(!$availableCustomer) {
                    echo $row. " -- Added new customer email: ". $data[0] . "\n";
                    $this->createCustomer($data[0], $data[3], $data[1], $data[2]);
                } else {
                    echo $row. " -- Updated customer email: ", $data[0] . "\n";
                }
            }
        }
        $output->writeln("Success");
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("fecon:syteline:import_customer");
        $this->setDescription("Import customer form CSV");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);

        parent::configure();
    }

    /**
     * Load customer by email
     *
     * @param string $email
     * param string $customerNumber
     * @return boolean
     */
    public function getCustomerByEmail($email, $customerNumber = null)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        try {
            $customer = $this->customerRepository->get($email, $websiteId);
            $number = $customer->getCustomAttribute('customer_number') ? $customer->getCustomAttribute('customer_number')->getValue() : '';
            if ($customerNumber && (!$number || $number != $customerNumber)) {
                $customer->setCustomAttribute('customer_number', $customerNumber);
                $this->customerRepository->save($customer);
            }
        } catch (\Exception $ex) {
            $customer = false;
        }
        
        return $customer;
    }

    public function createCustomer($username, $customerNumber, $firstname, $lastname)
    {
        $customerData = [
            'email' => $username,
            'firstname' => !empty($firstname) ? $firstname :  '-',
            'lastname' => !empty($lastname) ? $lastname: '-',
            'password' => $this->mathRandom->getRandomString(15),
            'customer_number' => $customerNumber ?? '',
            'username' => $username,
            'is_documoto_user'=> true
        ];

        $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->addData($customerData);

        try {
            $customer->save();
            $customer->sendNewAccountEmail();
            $newCustomer = $customer;
        } catch (\Exception $exc) {
            $newCustomer = false;
        }

        return $newCustomer;
    }
}
