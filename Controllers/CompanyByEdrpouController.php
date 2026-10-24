<?php

namespace Okay\Modules\Sviat\InvoicePayment\Controllers;

use Okay\Controllers\AbstractController;

class CompanyByEdrpouController extends AbstractController
{
    private const string API_URL = 'https://adm.tools/action/gov/api/?egrpou=%s';

    public function getCompany()
    {
        $this->response->setContentType(RESPONSE_JSON);

        $egrpou = preg_replace('/\D/', '', $this->request->get('egrpou', 'string'));
        if (strlen($egrpou) < 8 || strlen($egrpou) > 10) {
            $this->response->setContent(json_encode(['error' => 'invalid_egrpou']));
            return;
        }

        $url = sprintf(self::API_URL, $egrpou);
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $xmlString = @file_get_contents($url, false, $context);
        if ($xmlString === false) {
            $this->response->setContent(json_encode(['error' => 'fetch_failed']));
            return;
        }

        libxml_use_internal_errors(true);
        $xml = @simplexml_load_string($xmlString);
        if ($xml === false) {
            $this->response->setContent(json_encode(['error' => 'parse_failed']));
            return;
        }

        $company = $xml->company;
        if (empty($company)) {
            $this->response->setContent(json_encode(['error' => 'not_found']));
            return;
        }

        $name = (string) $company['name'];
        $this->response->setContent(json_encode([
            'name' => $name,
        ]));
    }
}
