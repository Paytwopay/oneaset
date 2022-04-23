<?php

class SMSHub {

    private $url = 'https://smshub.org/stubs/handler_api.php';

    private $apiKey;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    public function getBalance() {
        return $this->request(array('api_key' => $this->apiKey, 'action' => __FUNCTION__), 'GET');
    }
    
    public function getNumber($service, $country = null, $forward = 0, $operator = null, $ref = null){
        $requestParam = array('api_key' => $this->apiKey,'action' => __FUNCTION__,'service' => $service,'forward'=>$forward);
        if($country){
            $requestParam['country']=$country;
        }
        if($operator &&($country==0 || $country == 1 || $country == 2)){
            $requestParam['service'] = $operator;
        }
        if($ref){
            $requestParam['ref'] = $ref;
        }
        return $this->request($requestParam, 'POST',null,1);
    }

    public function setStatus($id, $status, $forward = 0){
        $requestParam = array('api_key' => $this->apiKey,'action' => __FUNCTION__,'id' => $id,'status' => $status);

        if($forward){
            $requestParam['forward'] = $forward;
        }

        return $this->request($requestParam,'POST',null,3);
    }

    private function request($data, $method, $parseAsJSON = null, $getNumber = null) {
        $method = strtoupper($method);

        if (!in_array($method, array('GET', 'POST')))
            throw new InvalidArgumentException('Method can only be GET or POST');

        $serializedData = http_build_query($data);

        if ($method === 'GET') {
            $result = file_get_contents("$this->url?$serializedData");
        } else {
            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => $serializedData
                )
            );

            $context = stream_context_create($options);
            $result = file_get_contents($this->url, false, $context);
        }

        if ($parseAsJSON)
            return json_decode($result,true);

        $parsedResponse = explode(':', $result);

        if ($getNumber == 1) {
            $returnNumber = array('id' => $parsedResponse[1], 'number' => $parsedResponse[2]);
            return $returnNumber;
        }
        if ($getNumber == 2) {
            $returnStatus = array('status' => $parsedResponse[0], 'code' => $parsedResponse[1]);
            return $returnStatus;
        }
        if ($getNumber == 3) {
            $returnStatus = array('status' => $parsedResponse[0]);
            return $returnStatus;
        }

        return $parsedResponse[1];
    }

}
