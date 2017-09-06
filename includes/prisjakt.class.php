<?php
    define('PRISJAKT_API_URL', 'https://www.prisjakt.nu/ajax/server.php');

    class Prisjakt {
        private $client;
        private $userId;
        private $passwordHash;

        private $headers = [
            'Host' => 'www.prisjakt.nu',
            'Origin' => 'https://www.prisjakt.nu',
            'X-Requested-With' => 'XMLHttpRequest',
            'X-Request-Thirdparty' => 'support@ninetwozero.com'
        ];

        function __construct($data = []) {
            $this->client = new GuzzleHttp\Client();
            $this->userId = $data['user_id'] ?? 0;
            $this->passwordHash = $data['password_hash'] ?? '';
        }

        public function login($username, $password) {
            $payload = [
                'class' => 'C_LoginAndRegistration',
                'method' => 'login_user',
                'username' => $username,
                'password' => $password,
                'request_id' => 1
            ];

            $response = $this->client->post(PRISJAKT_API_URL, [
                'form_params' => $payload,
                'cookies' => $this->createCookieJar(),
                'headers' => $this->headers
            ]);

            foreach ($response->getHeader('Set-Cookie') as $header) {
                $headerName = substr($header, 0, strpos($header, '='));
                if ($headerName === 'member_id' || $headerName === 'pass_hash') {
                    $headerValue = explode('=', substr($header, 0, strpos($header, ';')))[1];
                    if ($headerName === 'member_id') {
                        $this->userId = $headerValue;
                    } else {
                        $this->passwordHash = $headerValue;
                    }
                }
            }

            return ($this->userId && $this->passwordHash) ? [ 'user_id' => $this->userId, 'password_hash' => $this->passwordHash ] : [];
        }

        public function getProductsInSearch($searchUrl) {
            $products = [];
            if (strpos($searchUrl, 'expert.php') === false) {
                // Quick search
                //https://www.prisjakt.nu/ajax/server.php
                $searchString = substr($searchUrl, strrpos($searchUrl, '='));
                $payload = [
                    'class' => 'Search_Supersearch',
                    'method' => 'search',
                    'skip_login' => 1,
                    'modes' => 'product,raw_sorted,raw',
                    'limit' => 20,
                    'q' => urldecode($searchString)
                ];

                $response = $this->client->get(PRISJAKT_API_URL, [
                    'query' => $payload,
                    'cookies' => $this->createCookieJar(),
                    'headers' => $this->headers
                ]);

                $data = json_decode($response->getBody()->getContents());
                if (!$data->error) {
                    $searchResults = $data->message->product->items ?? [];
                    if (count($searchResults) > 0) {
                        $products = $searchResults;
                    }
                }
            } else {
                // TODO: expert search?
            }
            return $products;
        }

        public function addAlertForProduct($productId, $extras = []) {
            $payload = [
                'alert_id' => '',
                'user_id' => $this->userId,
                'product_id' => $productId,
                'alert_by_email' => 1,
                'alert_by_push' => 1,
                'alert_type' => 'product',
                'price_threshold' => $extras['price_threshold'] ?? 0,
                'liked_store_preference' => ($extras['only_liked_stores'] ?? false) ? 1 : 0,
                'store_grade_threshold' => $extras['storeMinRating'] ?? 0,
                'class' => 'C_Alert',
                'method' => 'create_alert'
            ];

            $response = $this->client->post(PRISJAKT_API_URL, [
                'form_params' => $payload,
                'cookies' => $this->createCookieJar(),
                'headers' => $this->headers
            ]);

            $data = json_decode($response->getBody()->getContents());
            if (!$data->error) {
                return $data->alert ?? false;
            }
            return false;
        }

        private function createCookieJar() {
            return GuzzleHttp\Cookie\CookieJar::fromArray([
                'member_id' => $this->userId,
                'pass_hash' => $this->passwordHash,
            ], parse_url(PRISJAKT_API_URL)['host']);

        }
    }