<?php
/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote 
 */
$metadata['http://172.18.0.8:8000/simplesaml/saml2/idp/metadata.php'] = array(
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'http://172.18.0.8:8000/simplesaml/saml2/idp/metadata.php',
    'SingleSignOnService' =>
    array(
        0 =>
        array(
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://172.18.0.8:8000/simplesaml/saml2/idp/SSOService.php',
        ),
    ),
    'SingleLogoutService' =>
    array(
        0 =>
        array(
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://172.18.0.8:8000/simplesaml/saml2/idp/SingleLogoutService.php',
        ),
    ),
    'certData' => 'MIID3zCCAsegAwIBAgIJAIwVOaQPHY3dMA0GCSqGSIb3DQEBCwUAMIGFMQswCQYDVQQGEwJVUzELMAkGA1UECAwCTlkxETAPBgNVBAcMCE5ldyBZb3JrMRAwDgYDVQQKDAdDb21wYW55MRAwDgYDVQQLDAdTZWN0aW9uMQ0wCwYDVQQDDAROYW1lMSMwIQYJKoZIhvcNAQkBFhRzb21lYXNAbWFpbG5hdG9yLmNvbTAeFw0xODExMDYxOTI0MzdaFw0yODExMDUxOTI0MzdaMIGFMQswCQYDVQQGEwJVUzELMAkGA1UECAwCTlkxETAPBgNVBAcMCE5ldyBZb3JrMRAwDgYDVQQKDAdDb21wYW55MRAwDgYDVQQLDAdTZWN0aW9uMQ0wCwYDVQQDDAROYW1lMSMwIQYJKoZIhvcNAQkBFhRzb21lYXNAbWFpbG5hdG9yLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAMUrFqxk797rZpGnwFEBITBhzdVV3VnC7MPAy21778aDGMZUQ64il4SaOUVGA/LrxJt7XhpNorWu0IeIF/z+j2zMwlFO+Y5UIiwbW1F9LIduOTai8boAOs9jc8uMWXxHX/4kz69Jv488qNWygffGXaVRYMfAMKOHWzk6y8fDy7k/BeQrGc2Pb9BWt+8PO/48rUoYBRTY8/SAP70ZWtOyeRfucdnh0rh0kzSQs6QjbfEfJAP1ssxc80ZRuEDb+zvoH/HS1zSWc2NVSxiGkZZF79Bcylc+I4dd1//L5poPjE72v7eF3kAF9UZRvYpbPImbVZEdwlXc/Uvr0QBxbo1to0kCAwEAAaNQME4wHQYDVR0OBBYEFCesceYFynqA4FhaMGrgu7Zz4nx5MB8GA1UdIwQYMBaAFCesceYFynqA4FhaMGrgu7Zz4nx5MAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBADl0adou4OmzwKHal60BKXFaZsiRabxMOvrvJUMzwjHwCy8O65O7DwSLKHzcn6Wn3kIPPzBJpVHmAZpe7TitKmFcOk+lJyMhXgxUjeXknQecDOg+z3v5zV4lXy/L1P/ZQU3HZlVs3i4y0zDKBc2GASyyzAbefDiyM4HZfJNb/yU473wALEOPtyJkGaXHPkQafSJhI1Jgk/l+nS8T6N686kjdHgzwWKXJhQ4BNQBufHsX6enhMROrclYWU4K1kUMAiPb9DC+8+UaFrd3xZH3iilWFXV9xCnV1cwjd4jk7OpaHVUI93UN/K5PLDPgm3P1i1fjMAzRz2rPIqEdw1OP2Ha8=',
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
);

$metadata['https://172.18.0.8:8443/sso/metadata/'] = array(
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'https://172.18.0.8:8443/sso/metadata/',
    'SingleSignOnService' =>
    array(
        0 =>
        array(
//      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://172.18.0.8:8443/sso/idp/signon',
        ),
    ),
    'SingleLogoutService' =>
    array(
        0 =>
        array(
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://172.18.0.8:8443/sso/idp/signon',
        ),
    ),
//    'certData' => 'MIIE+zCCAuOgAwIBAgIJAN4G3h8JGnwjMA0GCSqGSIb3DQEBCwUAMBQxEjAQBgNVBAMMCWxvY2FsaG9zdDAeFw0xODExMDkxNzA3MzVaFw0xOTExMDkxNzA3MzVaMBQxEjAQBgNVBAMMCWxvY2FsaG9zdDCCAiIwDQYJKoZIhvcNAQEBBQADggIPADCCAgoCggIBALhpaJsdW4RffyT+fgbv8JGg/oj78wulHs6xuLOWYUL+xGmMUikglI1H+QtoSuAZrKt8ZcgjcGETafTKpT4JYdpVT7zjSFwm8aJqn0iajiAx9eDZIRB+d6Ivn7jgbxWVtJdsvd0S0h3RGTWqYhQ8xXcxfN3xPWsh9bseHehDeX9ySfPXZY07mwi9k1hobWfvRizCRWgjZYZuwtO3dNTew9rCm2gEnHAdK2zOwcWHPsCxm4Hp3AijRb18C9gvR5rDmNfaboo5j8dF05wVBFHCS8C5zQpnB96jvy379Ma0FoOj/6ZmU7SbmN4nglcqChCWkkCTR6KjDrAZkbHavILjsQ49KcQHz+PJVor34YLSRPr7lkKiHURSc/uTcsubj7R3p+gzUmqkUQoSvwbLh3wqCndrDCKlvb1W7yZ7ZGzm8YbsMYrL4VmsvP8e5aa/LgTkh/vDLrdHTT6rRZrDCZKlbpFO+kLYkqXjXlaxj59CVQ+hnwLEbsg1TMe1hHcj3DmsBdt7nK8LByYTmz4EJ38ERb1L85kAo+9m96UkCaVUGDfPr+kd5CK3o17YbTpjqD6G841v+Am3wsOjQ+UFeNPaW6qjUi0VSaaG1zicV8o2htaIBynXm203S3gvBIiJg0KIFYLYkWcoi4qDhPExOdnRaAq+mxx2Whx62X3R5qWaEBEfAgMBAAGjUDBOMB0GA1UdDgQWBBQxMYpu55MZeOLRN1JqGAgJULqd3TAfBgNVHSMEGDAWgBQxMYpu55MZeOLRN1JqGAgJULqd3TAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBCwUAA4ICAQBoanLzaXjdncuI3QxWsI6vs77DjcFUmCZwsdsVOrvVno5jGtsDkV+I36B0/WLI0TxyDX60y5h7PDUyQG0PyQK2mtFiXX2QvFhONIOgpiPd/qglsNxNOq95+qOXlfbmarkLN1w521ckr4nE7tyn+M2Z28j4dkoPddPnKhJkIsW1zCiH17gFkkJD0oS3PrnxGkG4BU3WG+HYy+8qpdjvUnp0welW82XEqhqI8Gstnnhp6keqnz7hprvvTinbQSgSTSVzwRK2MEiG8HF7VnpYVqMonlzQivvjnue3untEOhMb4ACBhYwMxNvxuanWVB4y0K0RrMOz4E7eDTJVZTCkTE+STL3ZzVUqYnUZjWJqPUwPElUfcomqjvuc6EZp3hK01ugibygV+Bw9q3LPXAXfW4B6ktKLOO3zo2VKv9RreZuk0A+baYj7CfEKdy8eddCwvHjUvh3HuB7RWdEjxajdItej/EUoCxSGLd5V9FmsRcStVArvNpIF7MX9R+WI/sZPo+X7rBGjqUrAV96Y5IECyeGWgUttgCLoQlRsVZTnoImn7sQA4qqFAq4y99fRjw5GHb2WJ9XCeXVXG5Yr16nmG7PiE76Znb8C2B1aWHUs3CVdPKu7gswStA0C0CJ/9uoN+apLPm5Th8K7yLVQMC7Jpf/pRNPsMfZ+1/Zma78coS6HuA==',
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
    'keys' => array(
        0 => array(
            'encryption' => false,
            'signing' => true,
            'type' => 'X509Certificate',
            'X509Certificate' => 'MIICYDCCAcmgAwIBAgIBADANBgkqhkiG9w0BAQ0FADBNMQswCQYDVQQGEwJ1czER
MA8GA1UECAwITmV3IFlvcmsxDjAMBgNVBAoMBUZlY29uMRswGQYDVQQDDBJmZWNv
bS5kZXZwaGFzZS5jb20wHhcNMTgxMTIyMTEyMzE4WhcNMTkxMTIyMTEyMzE4WjBN
MQswCQYDVQQGEwJ1czERMA8GA1UECAwITmV3IFlvcmsxDjAMBgNVBAoMBUZlY29u
MRswGQYDVQQDDBJmZWNvbS5kZXZwaGFzZS5jb20wgZ8wDQYJKoZIhvcNAQEBBQAD
gY0AMIGJAoGBAL7p2MjLN5TfwhXPztdxqAw4v9iu/yjxAXagmCewT1+PVDiHPciE
M0lXFlij1DYveqhZj8AwAkjrJiNGrYZY/cno5qkNrnvB4dKy7yhJI/Byzm0Lbxay
MZboDx8cllbcbcQikUNFnFkDYIAzLQbUOE1GCvZbDy68V87VtzUwXgA1AgMBAAGj
UDBOMB0GA1UdDgQWBBQnJunFkyEhUU2aYfQO0Cz+2ovIoTAfBgNVHSMEGDAWgBQn
JunFkyEhUU2aYfQO0Cz+2ovIoTAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBDQUA
A4GBADrgiNOh3PUmKR3O0O3hESmqsUmRFKMIFnBY2GTKK8W9MZij8+Yi/WNLdVeE
RsDjLc7w8pCE1woOk3jXNqlRwbuYUpgK2mbb4VHM2mJ7dVGiIFUXhGKlEiJCp9Bl
FRp8DHwMdgicdfUmswZayagJvW6WiNkb8H73jr/HC/IJvcl5',
        ),
        1 => array(
            'encryption' => true,
            'signing' => false,
            'type' => 'X509Certificate',
            'X509Certificate' => 'MIICYDCCAcmgAwIBAgIBADANBgkqhkiG9w0BAQ0FADBNMQswCQYDVQQGEwJ1czER
MA8GA1UECAwITmV3IFlvcmsxDjAMBgNVBAoMBUZlY29uMRswGQYDVQQDDBJmZWNv
bS5kZXZwaGFzZS5jb20wHhcNMTgxMTIyMTEyMzE4WhcNMTkxMTIyMTEyMzE4WjBN
MQswCQYDVQQGEwJ1czERMA8GA1UECAwITmV3IFlvcmsxDjAMBgNVBAoMBUZlY29u
MRswGQYDVQQDDBJmZWNvbS5kZXZwaGFzZS5jb20wgZ8wDQYJKoZIhvcNAQEBBQAD
gY0AMIGJAoGBAL7p2MjLN5TfwhXPztdxqAw4v9iu/yjxAXagmCewT1+PVDiHPciE
M0lXFlij1DYveqhZj8AwAkjrJiNGrYZY/cno5qkNrnvB4dKy7yhJI/Byzm0Lbxay
MZboDx8cllbcbcQikUNFnFkDYIAzLQbUOE1GCvZbDy68V87VtzUwXgA1AgMBAAGj
UDBOMB0GA1UdDgQWBBQnJunFkyEhUU2aYfQO0Cz+2ovIoTAfBgNVHSMEGDAWgBQn
JunFkyEhUU2aYfQO0Cz+2ovIoTAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBDQUA
A4GBADrgiNOh3PUmKR3O0O3hESmqsUmRFKMIFnBY2GTKK8W9MZij8+Yi/WNLdVeE
RsDjLc7w8pCE1woOk3jXNqlRwbuYUpgK2mbb4VHM2mJ7dVGiIFUXhGKlEiJCp9Bl
FRp8DHwMdgicdfUmswZayagJvW6WiNkb8H73jr/HC/IJvcl5',
        ),
    )
);
