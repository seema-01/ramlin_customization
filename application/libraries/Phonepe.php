<?php
/* 
    1. get_credentials()
    2. create_order($amount,$receipt='')
    3. fetch_payments($id ='')
    4. capture_payment($amount, $id, $currency = "INR")
    5. verify_payment($order_id, $razorpay_payment_id, $razorpay_signature)

    0. curl($url, $method = 'GET', $data = [])
*/
class Phonepe
{
    private $client_id = "";
    private $client_secret = "";
    private $url = "";
    private $environment = "";
    private $marchant_id = "";

    public $client_version = 1;
    public $grant_type = "client_credentials";

    function __construct()
    {
        $settings = get_settings('payment_method', true);
        $system_settings = get_settings('system_settings', true);

        $this->client_id = (isset($settings['phonepe_client_id'])) ? $settings['phonepe_client_id'] : " ";
        $this->client_secret = (isset($settings['phonepe_client_secret'])) ? $settings['phonepe_client_secret'] : " ";
        $this->marchant_id = (isset($settings['phonepe_marchant_id'])) ? $settings['phonepe_marchant_id'] : " ";

        $this->url = (isset($settings['phonepe_payment_mode']) && $settings['phonepe_payment_mode'] == "PRODUCTION") ? "https://api.phonepe.com/apis/pg" : "https://api-preprod.phonepe.com/apis/pg-sandbox";

        if (isset($settings['phonepe_payment_mode'])) {
            if ($settings['phonepe_payment_mode'] == 'PRODUCTION') {
                $this->environment = 'PRODUCTION';
            } elseif ($settings['phonepe_payment_mode'] == 'UAT') {
                $this->environment = 'UAT';
            } else {
                $this->environment = 'SANDBOX';
            }
        }
    }

    public function get_access_token()
    {
        $client_id = $this->client_id;
        $client_secret = $this->client_secret;
        $client_version = 1;
        $grant_type = "client_credentials";

        $settings = get_settings('payment_method', true);

        if (!empty($settings['phonepe_payment_mode']) && $settings['phonepe_payment_mode'] === "PRODUCTION") {
            $token_url = "https://api.phonepe.com/apis/identity-manager/v1/oauth/token";
        } else {
            $token_url = "https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token";
        }

        // Prepare payload
        $payload = http_build_query([
            'client_id'      => $client_id,
            'client_secret'  => $client_secret,
            'client_version' => $client_version,
            'grant_type'     => $grant_type,
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $token_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            log_message('error', 'PhonePe Token Curl Error: ' . curl_error($curl));
            curl_close($curl);
            return '';
        }

        curl_close($curl);

        $result = json_decode($response, true);

        if (isset($result['access_token']) && $result['access_token'] != '') {
            $accessToken = $result['access_token'];
            $expires_at = isset($result['expires_at']) ? $result['expires_at'] : '';

            // Optionally save access token details to database for reuse
            // $this->db->insert('phonepe_tokens', ['token' => $accessToken, 'expires_at' => $expires_at]);

        } else {
            log_message('error', 'PhonePe Token not received. Response: ' . $response);
            $accessToken = '';
            $expires_at = '';
        }

        return $accessToken;
    }


    public function pay_v2($data, $type)
    {
        $accessToken = $this->get_access_token();

        if (!$accessToken) {
            return ['error' => true, 'message' => 'Access token not available'];
        }

        if ($type == 'web') {
            $url = $this->url . "/checkout/v2/pay";
        } else {
            $url = $this->url . "/checkout/v2/sdk/order";
        }

        $metaData = array(
            'udf1' => isset($data['merchantTransactionId']) ? $data['merchantTransactionId'] : 'TXN_' . time(),
            'udf2' => isset($data['merchantUserId']) ? $data['merchantUserId'] : 'USER_' . rand(1000, 9999),
            'udf3' => isset($data['amount']) ? $data['amount'] : 0,
        );

        $payload = [
            "merchantOrderId" => isset($data['merchantTransactionId']) ? $data['merchantTransactionId'] : 'TXN_' . time(),
            "amount" => intval($data['amount']),
            "metaInfo" => $metaData,
            "paymentFlow" => [
                "type" => "PG_CHECKOUT",
                "message" => "Payment message used for collect requests",
                "merchantUrls" => [
                    "redirectUrl" => (isset($data['redirectUrl']) ? $data['redirectUrl'] : '') . "?TransactionID=" . (isset($data['merchantTransactionId']) ? $data['merchantTransactionId'] : 'TXN_' . time()),
                ]
            ]
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: O-Bearer ' . $accessToken
        ];

        try {
            $response = $this->curl($url, 'POST', json_encode($payload), $headers);
            $response_data = json_decode($response['body'], true);

            if (isset($response['http_code']) && $response['http_code'] >= 400) {
                log_message('error', 'PhonePe: Payment creation failed --> ' . json_encode($response_data));
                return ['error' => true, 'response' => $response_data];
            }

            if (isset($data['redirectUrl'])) {
                return [
                    'orderId' => isset($response_data['orderId']) ? $response_data['orderId'] : '',
                    'redirectUrl' => isset($response_data['redirectUrl']) ? $response_data['redirectUrl'] : '',
                    'merchantOrderId' => isset($data['merchantTransactionId']) ? $data['merchantTransactionId'] : '',
                ];
            } else {
                return $response_data;
            }
        } catch (Exception $e) {
            log_message('error', 'PhonePe: pay_v2 exception --> ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    public function check_status_v2($id = '')
    {
        // print_r($id);
        // $data['merchantId'] = $this->merchant_id;
        $accessToken = $this->get_access_token();
        
        $data['paymentInstrument'] = array(
            'type' => 'PAY_PAGE',
        );
        $endpoint = '/checkout/v2/order/' . $id . '/status';
        $url = $this->url . $endpoint;
    
        // print_r($url);
        $method = 'GET';
        
        $header = [
            'Content-Type: application/json',
            'Authorization: O-Bearer ' . $accessToken
        ];
        $response = $this->curl($url, $method, [], $header);
        $res = json_decode($response['body'], true);
        // print_R($res);
        return $res;
    }

    public function phonepe_checksum_v2($data)
    {
        // print_r($data);

        $orderId = 'TX' . time(); // unique order ID

        $expireAfter = 1200; // in seconds (20 mins)
        $token = $this->get_access_token();
        // print_r($token); die;

        $order = $this->pay_v2($data, 'app');
        // print_r($order);
        // die;

        $requestPayload = [
            // "orderId" => $data['merchantTransactionId'],
            "state"  => "PENDING",
            "merchantOrderId" => $order['orderId'],
            "amount" => $data['amount'], // marchant id
            "merchantId" => $this->client_id,
            "expireAT" => $expireAfter,
            "token" => $order['token'],
            "paymentMode" => [
                "type" => "PAY_PAGE"
            ]
        ];

        // Convert to JSON string as required by Flutter SDK
        $requestString = json_encode($requestPayload);

        return [
            "environment" => $this->environment, // or "PRODUCTION"
            "merchantOrderId" => $order['orderId'],
            "flowId" => $orderId,
            "enableLogging" => true, // false in production
            "request" => $requestPayload,
            "token" => $token,
        ];
    }

    public function curl($url, $method = 'POST', $data = [], $header = [])
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => $header
        );
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            if (!empty($data)) {
                $curl_options[CURLOPT_POSTFIELDS] = $data;
            }
        } else {
            $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
        }
        curl_setopt_array($ch, $curl_options);
        $result = array(
            'body' => curl_exec($ch),
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );
        return $result;
    }
}
