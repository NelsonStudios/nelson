<?php
/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote 
 */


$metadata['http://172.21.0.8:8000/simplesaml/saml2/idp/metadata.php'] = array (
  'metadata-set' => 'saml20-idp-remote',
  'entityid' => 'http://172.21.0.8:8000/simplesaml/saml2/idp/metadata.php',
  'SingleSignOnService' => 
  array (
    0 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'http://172.21.0.8:8000/simplesaml/saml2/idp/SSOService.php',
    ),
  ),
  'SingleLogoutService' => 
  array (
    0 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'http://172.21.0.8:8000/simplesaml/saml2/idp/SingleLogoutService.php',
    ),
  ),
  'certData' => 'MIID3zCCAsegAwIBAgIJAIwVOaQPHY3dMA0GCSqGSIb3DQEBCwUAMIGFMQswCQYDVQQGEwJVUzELMAkGA1UECAwCTlkxETAPBgNVBAcMCE5ldyBZb3JrMRAwDgYDVQQKDAdDb21wYW55MRAwDgYDVQQLDAdTZWN0aW9uMQ0wCwYDVQQDDAROYW1lMSMwIQYJKoZIhvcNAQkBFhRzb21lYXNAbWFpbG5hdG9yLmNvbTAeFw0xODExMDYxOTI0MzdaFw0yODExMDUxOTI0MzdaMIGFMQswCQYDVQQGEwJVUzELMAkGA1UECAwCTlkxETAPBgNVBAcMCE5ldyBZb3JrMRAwDgYDVQQKDAdDb21wYW55MRAwDgYDVQQLDAdTZWN0aW9uMQ0wCwYDVQQDDAROYW1lMSMwIQYJKoZIhvcNAQkBFhRzb21lYXNAbWFpbG5hdG9yLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAMUrFqxk797rZpGnwFEBITBhzdVV3VnC7MPAy21778aDGMZUQ64il4SaOUVGA/LrxJt7XhpNorWu0IeIF/z+j2zMwlFO+Y5UIiwbW1F9LIduOTai8boAOs9jc8uMWXxHX/4kz69Jv488qNWygffGXaVRYMfAMKOHWzk6y8fDy7k/BeQrGc2Pb9BWt+8PO/48rUoYBRTY8/SAP70ZWtOyeRfucdnh0rh0kzSQs6QjbfEfJAP1ssxc80ZRuEDb+zvoH/HS1zSWc2NVSxiGkZZF79Bcylc+I4dd1//L5poPjE72v7eF3kAF9UZRvYpbPImbVZEdwlXc/Uvr0QBxbo1to0kCAwEAAaNQME4wHQYDVR0OBBYEFCesceYFynqA4FhaMGrgu7Zz4nx5MB8GA1UdIwQYMBaAFCesceYFynqA4FhaMGrgu7Zz4nx5MAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBADl0adou4OmzwKHal60BKXFaZsiRabxMOvrvJUMzwjHwCy8O65O7DwSLKHzcn6Wn3kIPPzBJpVHmAZpe7TitKmFcOk+lJyMhXgxUjeXknQecDOg+z3v5zV4lXy/L1P/ZQU3HZlVs3i4y0zDKBc2GASyyzAbefDiyM4HZfJNb/yU473wALEOPtyJkGaXHPkQafSJhI1Jgk/l+nS8T6N686kjdHgzwWKXJhQ4BNQBufHsX6enhMROrclYWU4K1kUMAiPb9DC+8+UaFrd3xZH3iilWFXV9xCnV1cwjd4jk7OpaHVUI93UN/K5PLDPgm3P1i1fjMAzRz2rPIqEdw1OP2Ha8=',
  'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
);
