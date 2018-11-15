<?php

namespace Fecon\Sso\Model;

/**
 * SimpleSamlPhp wrapper
 */
class SimpleSaml implements \Fecon\Sso\Api\SimpleSamlInterface
{

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList 
     */
    protected $dir;

    /**
     * @var boolean 
     */
    protected $applicationInitialized;

    /**
     * @var \Fecon\Sso\Helper\Config 
     */
    protected $configHelper;

    /**
     * @var \Magento\Framework\UrlInterface 
     */
    protected $url;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Fecon\Sso\Helper\Config $configHelper
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Fecon\Sso\Helper\Config $configHelper,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->dir = $dir;
        $this->configHelper = $configHelper;
        $this->applicationInitialized = false;
        $this->url = $urlInterface;
    }

    /**
     * Loads SimpleSamlPhp application
     *
     * @returns void
     */
    protected function loadSimpleSamlApplication()
    {
        $magentoRoot = $this->dir->getRoot();
        $simpleSamlPhpRoot = $magentoRoot . '/' . self::SIMPLE_SAML_PHP_ROOT_FOLDER . '/' . self::SIMPLE_SAML_PHP_LIB_FOLDER;
        $simpleSamlPhpInclude = $simpleSamlPhpRoot . '/' . '_autoload.php';
        require_once($simpleSamlPhpInclude);
        $this->applicationInitialized = true;
    }
}