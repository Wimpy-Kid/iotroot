<?php

namespace Cherrylu\iotroot;

class iotroot {

    private $domain = 'https://www.iotroot.com/interface';
    private $curl;
    private $params = [];

    public function __construct($clientId, $key) {
        // init curl request to $curl
        $this->curl = curl_init();

        encrypter::encrypt($clientId, $this->microTime(), $key);

        // set curl options
        curl_setopt($this->curl, CURLOPT_URL, $this->domain);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
    }

    public function microTime() {
        list($usec, $sec) = explode(" ", microtime());

        return $sec . substr($usec, 2, 3);
    }

    /**
     * 企业获取通用模板
     *
     * @return array
     * @throws \Exception
     */
    public function getTemplates() {
        curl_setopt($this->curl, CURLOPT_URL, $this->domain . "/company/queryTemplateInfo/" . $this->combineSignString());

        return $this->processRes(curl_exec($this->curl));
    }

    /**
     * 查看产品库审核状态
     *
     * @param $returnId
     *
     * @return array
     * @throws \Exception
     */
    public function getProductStatus($returnId) {
        curl_setopt($this->curl, CURLOPT_URL, $this->domain . "/company/queryProductStatus/" . $this->combineSignString());

        return $this->processRes(curl_exec($this->curl));
    }

    /**
     * 分批次回传
     * 
     * @param string $code
     * @param array $codeList
     * @param array $moduleList
     *
     * @return array
     * @throws \Exception
     */
    public function returnProductBatch($code, $codeList, $moduleList) {
        curl_setopt($this->curl, CURLOPT_URL, $this->domain . "/company/returnProductBatch/" . $this->combineSignString());
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode([
            'code' => $code,
            'codeList' => $codeList,
            'moduleList' => $moduleList
        ]));
        
        return $this->processRes(curl_exec($this->curl));
    }

    /**
     * 产品库回传
     *
     * @param string $name
     * @param string $typeNumber
     * @param string $templateId
     * @param string $base64
     * @param array $typeList
     *
     * @return array
     */
    public function returnProductInfo($name, $typeNumber, $templateId, $base64, $typeList) {
        $data   = [
            'productTypeNumber' => $typeNumber,
            'templeteId'        => $templateId,
            'productName'       => $name,
            'photos'            => $base64,
            'productTypelist'   => $typeList
        ];

        // post $data as json
        curl_setopt($this->curl, CURLOPT_URL, $this->domain . "/company/returnProduct/" . $this->combineSignString());
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        
        return $this->processRes(curl_exec($this->curl));
    }

    private function combineSignString(){
        return 'clientId=' . encrypter::getClientId() . '/timeStamp=' . encrypter::getTimeStamp() . '/sign=' . encrypter::getSignString();
    }
    
    /**
     * @param $result
     *
     * @return array
     * @throws \Exception
     */
    private function processRes($result) {
        // check if curl request is successful
        if ( curl_errno($this->curl) ) {
            throw new \Exception(curl_error($this->curl));
        }

        // decode json result
        $jsonRes = json_decode($result, true);

        // check if json decode is successful
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            file_put_contents(date('Y-m-d') . '.log', $result . PHP_EOL, FILE_APPEND);
            throw new \Exception('响应内容有误，请联系管理员检查');
        }

        $this->resetCurl();
        
        return $jsonRes;
    }

    private function resetCurl() {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, '');
    }
    
    public function __destruct() {
        curl_close($this->curl);
    }

}