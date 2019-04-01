<?php

namespace Fecon\Sso\Import;

/**
 * Class to import UserGroup from csv files
 */
class ImportUserGroup
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
     * @var \Fecon\Sso\Api\Data\UserGroupInterfaceFactory
     */
    protected $userGroupFactory;

    /**
     * @var \Fecon\Sso\Api\UserGroupRepositoryInterface
     */
    protected $userGroupRepository;

    /**
     * @var \Fecon\Sso\Model\ResourceModel\UserGroup\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\DirectoryList $filesystem
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Fecon\Sso\Api\Data\UserGroupInterfaceFactory $userGroupFactory
     * @param \Fecon\Sso\Api\UserGroupRepositoryInterface $userGroupRepository
     * @param \Fecon\Sso\Model\ResourceModel\UserGroup\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $filesystem,
        \Magento\Framework\File\Csv $csvProcessor,
        \Fecon\Sso\Api\Data\UserGroupInterfaceFactory $userGroupFactory,
        \Fecon\Sso\Api\UserGroupRepositoryInterface $userGroupRepository,
        \Fecon\Sso\Model\ResourceModel\UserGroup\CollectionFactory $collectionFactory
    ) {
        $this->filesystem = $filesystem;
        $this->csvProcessor = $csvProcessor;
        $this->userGroupFactory = $userGroupFactory;
        $this->userGroupRepository = $userGroupRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Import UserGroups entities from csv file
     *
     * @param string $filename
     * @param boolean $deleteExisting
     * @param boolean $removeFirst
     */
    public function importUserGroups($filename, $deleteExisting = false, $removeFirst = true)
    {
        if ($deleteExisting) {
            $this->deleteAllUserGroups();
        }
        $rootPath = $this->filesystem->getRoot();
        try {
            $csvData = $this->csvProcessor->getData($rootPath . self::CSV_FILES_FOLDER . $filename);
            if ($removeFirst) {
                unset($csvData[0]);
            }
        } catch (\Exception $ex) {
            $csvData = [];
        }
        foreach ($csvData as $userGroupData) {
            $this->createUserGroup($userGroupData);
        }
    }

    /**
     * Delete all UserGroups entities in the database
     */
    protected function deleteAllUserGroups()
    {
        $collection = $this->collectionFactory->create();
        foreach ($collection as $userGroup) {
            $this->userGroupRepository->deleteById($userGroup->getId());
        }
    }

    /**
     * Create an UserGroups instance and save it to the database
     *
     * @param array $userGroupData
     */
    protected function createUserGroup($userGroupData)
    {
        $userGroup = $this->userGroupFactory->create();
        $userGroup->setName(trim($userGroupData[0]));
        try {
            $this->userGroupRepository->save($userGroup);
        } catch (\Exception $ex) { }
    }
}