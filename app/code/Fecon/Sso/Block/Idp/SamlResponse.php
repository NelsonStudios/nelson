<?php

namespace Fecon\Sso\Block\Idp;

/**
 * Description of SamlResponse
 */
class SamlResponse extends \Magento\Framework\View\Element\Template
{

    protected $postData;

    protected $sso;

    protected $destination;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Fecon\Sso\Api\SsoInterfaceFactory $ssoFactory,
        array $data = array()
    ) {
        $this->sso = $ssoFactory->create();
        parent::__construct($context, $data);
    }

    protected function _beforeToHtml()
    {
        $samlResponse = $this->sso->sendSamlResponse();
        $this->destination = $samlResponse['destination'];
        $this->postData = $samlResponse['postData'];
        parent::_beforeToHtml();
    }

    public function getPostData()
    {
        return $this->postData;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Write out one or more INPUT elements for the given name-value pair.
     *
     * If the value is a string, this function will write a single INPUT element.
     * If the value is an array, it will write multiple INPUT elements to
     * recreate the array.
     *
     * @param string $name  The name of the element.
     * @param string|array $value  The value of the element.
     */
    public function printItem($name, $value)
    {
        assert(is_string($name));
        assert(is_string($value) || is_array($value));
        if (is_string($value)) {
            $input = '<input type="hidden" name="' .
            htmlspecialchars($name) . '" value="' .
            htmlspecialchars($value) . '" />';
            return $input;
        }
        $inputs = '';
        // This is an array...
        foreach ($value as $index => $item) {
            $inputs .= $this->printItem($name . '[' . $index . ']', $item);
        }

        return $inputs;
    }
}