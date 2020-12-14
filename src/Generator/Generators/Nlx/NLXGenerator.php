<?php/*** @unfixed**/

namespace Generator\Nlx\NLXGenerator;

use Cli\Tools\CommandUtils;
use Core\Logger;
use Exception;
use Exception\LogicException;
use GuzzleHttp\Client;
use Helper\ApiXsd\Schema\Api;

final class NLXGenerator
{
    public function create(Api $oApi, array $aAnswers)
    {
        $sNLXDir = CommandUtils::getRoot() . '/public_html/' . $oApi->getApi_dir() . '/nlx';

        if (!is_dir($sNLXDir)) {
            echo "Make dir " . $sNLXDir . PHP_EOL;
            mkdir($sNLXDir);
        }

        $sRootCertificate = file_get_contents('https://certportal.demo.nlx.io/root.crt');
        $sRootCertificateDest = $sNLXDir . '/root.crt';
        $sOrgKey = $sNLXDir . '/org.key';
        file_put_contents($sNLXDir . '/root.crt', $sRootCertificate);

        $sCheck = "openssl x509 -in $sRootCertificateDest -text | grep Subject:";
        $aOutput = [];
        exec($sCheck, $aOutput);

        echo "Checking certificate" . PHP_EOL;

        if (!trim($aOutput[0]) == 'Subject: C = NL, ST = Noord-Holland, L = Amsterdam, O = Common Ground, OU = NLX') {
            throw new LogicException("Root certificate validation failed.");
        } else {
            echo "Certificate is ok " . PHP_EOL;
        }

        $sEndpointDomain = $oApi->getEndpoint_url(true);
        $aCertificateCommand = [
            'openssl req -utf8 -nodes -sha256 -newkey rsa:4096 -keyout ' . $sOrgKey . ' ',
            '-out ' . $sNLXDir . '/org.csr -subj ',
            '"/C=NL/ST=Noord Holland/L=Amstelveen/O=Novum/OU=Sociale verzekeringsbank/CN=' . $sEndpointDomain . '"',
        ];

        $sCertificateCommand = join('', $aCertificateCommand);

        echo 'Create certificate ' . PHP_EOL;
        echo $sCertificateCommand . PHP_EOL;
        $aOutput = [];
        exec($sCertificateCommand, $aOutput);

        echo "Result: " . PHP_EOL;
        foreach ($aOutput as $sLine) {
            echo '$ -> ' . $sLine . PHP_EOL;
        }

        echo "Request a development certificate based on our generated certificate" . PHP_EOL;
        $client = new Client();

        $aParams = [
            'body'    => json_encode(['csr' => file_get_contents($sNLXDir . '/org.csr')]),
            'headers' => ['Content-Type' => 'application/json'],
        ];

        try {
            $oResponse = $client->request('POST', 'https://certportal.demo.nlx.io/api/request_certificate', $aParams);

            $sNlxCert = $sNLXDir . '/org.crt';
            $sNlxResponse = $oResponse->getBody();
            $sOrgCertificate = json_decode($sNlxResponse, true)['certificate'];

            echo "Received NLX certificate" . PHP_EOL;
            echo $sOrgCertificate . PHP_EOL;

            echo "Saving NLX certificate:" . PHP_EOL;
            echo $sNlxCert . PHP_EOL;
            file_put_contents($sNlxCert, $sOrgCertificate);

            echo "Verify new NLX certificate:" . PHP_EOL;
            $sVerifyCommand = "openssl x509 -in $sNlxCert -text | grep Subject:";
            echo $sVerifyCommand . PHP_EOL;
            $aOutput = [];
            exec($sVerifyCommand, $aOutput);

            echo "Verification result:" . PHP_EOL;
            $sExpectedResult = 'Subject: C = NL, ST = Noord Holland, L = Amstelveen, O = novum.svb, OU = Sociale verzekeringsbank, CN = ' . $sEndpointDomain;
            if (!$aOutput[0] == $sExpectedResult) {
                throw new Exception("Obtaining NLX certificate failed, received invalid response during validation.");
            }
            echo "OK!" . PHP_EOL;
            echo $aOutput[0] . PHP_EOL;

            $sDockerInwayLabel = strtolower('Inway_' . $oApi->getApiNamespace());
            $aInway = [
                'echo "Starting on tcp port: ' . $oApi->getNlxLocalOut() . '"',
                'docker run --rm \\',
                '--name ' . $sDockerInwayLabel . ' \\',
                '--volume ' . $aAnswers['nlx_dir'] . '/root.crt:/certs/root.crt:ro \\',
                '--volume ' . $aAnswers['nlx_dir'] . '/org.crt:/certs/org.crt:ro \\',
                '--volume ' . $aAnswers['nlx_dir'] . '/org.key:/certs/org.key:ro \\',
                '--volume ' . $aAnswers['nlx_dir'] . '/service-config.toml:/service-config.toml:ro \\',
                '--env DIRECTORY_REGISTRATION_ADDRESS=directory-registration-api.demo.nlx.io:443 \\',
                '--env SELF_ADDRESS=' . $oApi->getApi_dir() . ':' . $oApi->getNlxLocalIn() . ' \\',
                '--env SERVICE_CONFIG=/service-config.toml \\',
                '--env TLS_NLX_ROOT_CERT=/certs/root.crt \\',
                '--env TLS_ORG_CERT=/certs/org.crt \\',
                '--env TLS_ORG_KEY=/certs/org.key \\',
                '--env DISABLE_LOGDB=1 \\',
                '--publish ' . $oApi->getNlxLocalIn() . ':8443 \\',
                'nlxio/inway:latest',
            ];
            file_put_contents($sNLXDir . '/start_inway.sh', join(PHP_EOL, $aInway));

            $sDockerOutwayLabel = strtolower('Outway_' . $oApi->getApiNamespace());
            $aOutway = [

                'echo "Starting on tcp port: ' . $oApi->getNlxLocalIn() . '"',

                'docker run --rm \\',
                '--name ' . $sDockerOutwayLabel . ' \\',
                '--volume ' . $aAnswers['nlx_dir'] . '/root.crt:/certs/root.crt:ro \\',
                '--volume ' . $aAnswers['nlx_dir'] . '/org.crt:/certs/org.crt:ro \\',
                '--volume ' . $aAnswers['nlx_dir'] . '/org.key:/certs/org.key:ro \\',
                '--env DIRECTORY_INSPECTION_ADDRESS=directory-inspection-api.demo.nlx.io:443 \\',
                '--env TLS_NLX_ROOT_CERT=/certs/root.crt \\',
                '--env TLS_ORG_CERT=/certs/org.crt \\',
                '--env TLS_ORG_KEY=/certs/org.key \\',
                '--env DISABLE_LOGDB=1 \\',
                '--publish ' . $oApi->getNlxLocalOut() . ':8080 \\',
                'nlxio/outway:latest',
            ];
            file_put_contents($sNLXDir . '/start_outway.sh', join(PHP_EOL, $aOutway));
        } catch (Exception $e) {
            echo $e->getMessage();
            Logger::warning($e->getMessage());
        }
    }
}