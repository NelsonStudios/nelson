<?php

namespace Fecon\Sso\Model;

/**
 * SimpleSamlPhp wrapper
 */
class SimpleSaml implements \Fecon\Sso\Api\SimpleSamlInterface
{

    protected $dir;

    protected $applicationInitialized;

    protected $configHelper;

    protected $url;

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

    protected function loadSimpleSamlApplication()
    {
        $magentoRoot = $this->dir->getRoot();
        $simpleSamlPhpRoot = $magentoRoot . '/' . self::SIMPLE_SAML_PHP_ROOT_FOLDER . '/' . self::SIMPLE_SAML_PHP_LIB_FOLDER;
        $simpleSamlPhpInclude = $simpleSamlPhpRoot . '/' . '_autoload.php';
        require_once($simpleSamlPhpInclude);
        $this->applicationInitialized = true;
    }
}