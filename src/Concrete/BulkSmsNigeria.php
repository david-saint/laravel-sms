<?php
/**
 * Created by PhpStorm.
 * User: Djunehor
 * Date: 1/22/2019
 * Time: 9:36 AM
 */

namespace Djunehor\Sms\Concrete;

use Djunehor\Sms\Contracts\SmsServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class BulkSmsNigeria extends Sms
{
    private $baseUrl = 'https://www.bulksmsnigeria.com/api/v1/sms/';
    /**
     * Class Constructor
     * @param null $message
     */
    public function __construct($message = null)
    {
        $this->username = config('laravel-sms.bulk_sms_nigeria.token');

        if ($message) {
            $this->text($message);
        };

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            "headers" => [
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'User-Agent' => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.78 Safari/537.36 OPR/47.0.2631.39"
            ]
        ]);
    }

    /**
     * @param null $text
     * @return bool
     */
    public function send($text = null) : bool
    {
        if ($text) $this->setText($text);
        try {
            $response = $this->client->post('create', [
                "query" => [
                    "api_token" => $this->username,
                    "to" => join(',', $this->recipients),
                    "from" => $this->sender ?? config('laravel-sms.sender'),
                    "body" => $this->text,
                    "dnd" => config('laravel-sms.bulk_sms_nigeria.dnd', 2)
                ]
            ]);

            $response = json_decode($response->getBody()->getContents(), true);
            $this->response = array_key_exists('status', $response) ? $response['status'] : $response['error'];
            return $this->response == 'OK' ? true : false;
        } catch (ClientException $e) {
            Log::info('SMS Client Exception: '. json_encode($e->getMessage()));
            $this->httpError = $e;
            return false;
        } catch (\Exception $e) {
            Log::info('SMS Exception: '. json_encode($e->getMessage()));
            $this->httpError = $e;
            return false;
        }
    }
}
