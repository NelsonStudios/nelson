<?php

namespace Fecon\Sso\Import;

/**
 * Class to import Organizations from csv files
 */
class ImportOrganization
{

    const CSV_FILES_FOLDER = '/app/code/Fecon/Sso/files/';

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Fecon\Sso\Api\Data\OrganizationInterfaceFactory
     */
    protected $organizationFactory;

    /**
     * @var \Fecon\Sso\Api\OrganizationRepositoryInterface
     */
    protected $organizationRepository;

    /**
     * @var \Fecon\Sso\Model\ResourceModel\Organization\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\DirectoryList $filesystem
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Fecon\Sso\Api\Data\OrganizationInterfaceFactory $organizationFactory
     * @param \Fecon\Sso\Api\OrganizationRepositoryInterface $organizationRepository
     * @param \Fecon\Sso\Model\ResourceModel\Organization\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $filesystem,
        \Magento\Framework\File\Csv $csvProcessor,
        \Fecon\Sso\Api\Data\OrganizationInterfaceFactory $organizationFactory,
        \Fecon\Sso\Api\OrganizationRepositoryInterface $organizationRepository,
        \Fecon\Sso\Model\ResourceModel\Organization\CollectionFactory $collectionFactory
    ) {
        $this->filesystem = $filesystem;
        $this->csvProcessor = $csvProcessor;
        $this->organizationFactory = $organizationFactory;
        $this->organizationRepository = $organizationRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Import Organization entities from csv file
     *
     * @param string $filename
     * @param boolean $deleteExisting
     * @param boolean $removeFirst
     */
    public function importOrganizations($filename, $deleteExisting = false, $removeFirst = true)
    {
        if ($deleteExisting) {
            $this->deleteAllOrganizations();
        }
        $rootPath = $this->filesystem->getRoot();
        try {
            $csvData = $this->csvProcessor->getData( $rootPath . self::CSV_FILES_FOLDER . $filename);
            if ($removeFirst) {
                unset($csvData[0]);
            }
        } catch (\Exception $ex) {
            $csvData = [];
        }
        foreach ($csvData as $organizationData) {
            $this->createOrganization($organizationData);
        }
    }

    /**
     * Delete all Organizations entities in the database
     */
    protected function deleteAllOrganizations()
    {
        $collection = $this->collectionFactory->create();
        foreach ($collection as $organization) {
            $this->organizationRepository->deleteById($organization->getId());
        }
    }

    /**
     * Create an Organization instance and save it to the database
     *
     * @param array $organizationData
     */
    protected function createOrganization($organizationData)
    {
        $organization = $this->organizationFactory->create();
        $organization->setName(trim($organizationData[0]));
        try {
            $this->organizationRepository->save($organization);
        } catch (\Exception $ex) { }
    }
}