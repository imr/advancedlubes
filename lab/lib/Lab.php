<?php

class Lab
{

    public function updateDrupalProducts()
    {
        $products = $GLOBALS['injector']->getInstance('Lab_Driver')->listProducts();
        $cookie = Lab::_drupalLogin();
        $request_url = 'http://www.advancedlubes.com/pib/node';

        foreach ($products as $product) {
            $prodSpecs = $GLOBALS['injector']->getInstance('Lab_Driver')->getProductSpecs($product['p.id']);

            //$prodSpecs to $node_data
            foreach($prodSpecs as $prodSpec) {
                $node_data[$prodSpec['drupal_name']] = $prodSpec['value'];
            }

            if ($product['drupal_node']) {
                $node_url = "$request_url/$product[drupal_node]";
                Lab::_putDrupal($cookie, $node_url, $node_data);
            } else {
                Lab::_postDrupal($cookie, $request_url, $node_data);
            }
        }

    }

    public function updateDrupalPibs()
    {
        $pibs = $GLOBALS['injector']->getInstance('Lab_Driver')->listPibs();
        $cookie = Lab::_drupalLogin();
        $request_url = 'http://www.advancedlubes.com/pib/node';

        foreach ($pibs as $pib) {
            $node_data['title'] = $pib['title'];
            $node_data['body'] = $pib['description'];
            $node_data['field_short_title'] = array('und' => array(0 => array('value' => $pib['short_title'])));
            $node_data['field_featured'] = array('und' => array(0 => array('value' => $pib['feature'])));
            $node_data['field_approval_separate'] = array('und' => array(0 => array('value' => $pib['approval_separate'])));

            if ($pib['drupal_node']) {
                $node_url = "$request_url/$pib[drupal_node]";
                Lab::_putDrupal($cookie, $node_url, $node_data);
            } else {
                Lab::_postDrupal($cookie, $request_url, $node_data);
            }
        }

    }

    public function testDrupal()
    {
        $request_url = 'http://www.advancedlubes.com/pib/node/645';
        $cookie = Lab::_drupalLogin();

        $node_data = array(
            'title' => 'REST API update (PUT) test node.',
            //'body' => array('und' => array(0 => array('value' => 'This is new')))
            'field_appearance' => array('und' => array(0 => array('value' => '5')))
        );

        $response = Lab::_putDrupal($cookie, $request_url, $node_data);
        print_r($response);
    }

    /*
     * Use for updating existing data
     */
    protected static function _putDrupal($session, $request_url, $node_data)
    {
        $node_data = http_build_query($node_data);

        // cURL
        $curl = curl_init($request_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json')); // Accept JSON response
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); // Do a regular HTTP PUT
        curl_setopt($curl, CURLOPT_POSTFIELDS, $node_data); // Set PUT data
        curl_setopt($curl, CURLOPT_HEADER, FALSE);  // Ask to not return Header
        curl_setopt($curl, CURLOPT_COOKIE, "$session"); // use the previously saved session
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($http_code == 200) {
            return json_decode($response);
        } else {
            throw new Lab_Exception(curl_error($curl));
        }
    }

    /*
     * Use for uploading data
     */
    protected static function _postDrupal($session, $request_url, $node_data)
    {
        $node_data = http_build_query($node_data);

        // cURL
        $curl = curl_init($request_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json')); // Accept JSON response
        curl_setopt($curl, CURLOPT_POST, 1); // Do a regular HTTP POST
        curl_setopt($curl, CURLOPT_POSTFIELDS, $node_data); // Set POST data
        curl_setopt($curl, CURLOPT_HEADER, FALSE);  // Ask to not return Header
        curl_setopt($curl, CURLOPT_COOKIE, "$session"); // use the previously saved session
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($http_code == 200) {
            return json_decode($response);
        } else {
            throw new Lab_Exception(curl_error($curl));
        }
    }

    protected static function _drupalLogin()
    {
        // REST Server URL
        $request_url = 'http://www.advancedlubes.com/pib/user/login';

        // User data
        $user_data = $GLOBALS['conf']['drupal'];
        $user_data = http_build_query($user_data);

        // cURL
        $curl = curl_init($request_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
        //    'Content-Type: application/json', 
        //    'Content-Length: ' . strlen($user_data)
        )); // Accept JSON response
        curl_setopt($curl, CURLOPT_POST, 1); // Do a regular HTTP POST
        curl_setopt($curl, CURLOPT_POSTFIELDS, $user_data); // Set POST data
        curl_setopt($curl, CURLOPT_HEADER, FALSE);  // Ask to not return Header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Check if login was successful
        if ($http_code == 200) {
            // Convert json response as array
            $logged_user = json_decode($response);
            return $logged_user->session_name . '=' . $logged_user->sessid;
        } else {
            // Get error msg
            throw new Lab_Exception(curl_error($curl));
        }
    }
}

