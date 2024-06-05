<?php

namespace Cherrylu\iotroot;

class iotroot {

    private $domain = 'https://www.iotroot.com/interface';
    private $curl;
    private $params;

    public function __construct($clientId, $key) {
        // init curl request to $curl
        $this->curl = curl_init();

        encrypter::encrypt($clientId, $key);
        $this->params = $this->combineSignString();

        // set curl options
        curl_setopt($this->curl, CURLOPT_URL, $this->domain);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ]);
        //        curl_setopt($this->curl, CURLOPT_PROXY, "127.0.0.1");
        //        curl_setopt($this->curl, CURLOPT_PROXYPORT, 8888);
    }

    /**
     * 企业获取通用模板
     *
     * @return array
     * @throws \Exception
     */
    public function queryTemplateInfo() {
        curl_setopt($this->curl, CURLOPT_URL, $this->domain . "/company/queryTemplateInfo/" . $this->params);

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
    public function queryProductStatus($returnId) {
        curl_setopt($this->curl, CURLOPT_URL, $this->domain . "/company/queryProductStatus/" . $this->params);

        return $this->processRes(curl_exec($this->curl));
    }

    /**
     * 分批次回传
     *
     * @param string $productCode
     * @param array $codeList
     * @param array $moduleList
     *
     * @return array
     * @throws \Exception
     */
    public function ecodeBatchReturn($productCode, $codeList, $moduleList) {
        curl_setopt($this->curl, CURLOPT_URL, $this->domain . "/company/ecodeBatchReturn/" . $this->params);
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode([
            'code'       => $productCode,
            'ecodeList'   => $codeList,
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
    public function returnProduct($name, $typeNumber, $templateId, $base64, $typeList) {
        $data = [
            'productTypeNumber' => $typeNumber,
            'templeteId'        => $templateId,
            'productName'       => $name,
            'photos'            => is_array($base64) ? $base64 : [ $base64 ],
            'productTypelist'   => $typeList
        ];

        // post $data as json
        curl_setopt($this->curl, CURLOPT_URL, $this->domain . "/company/returnProduct/" . $this->params);
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));

        return $this->processRes(curl_exec($this->curl));
    }

    /**
     * 编码申请
     *
     * @param int $num 申请数量，一次不能超过50000个
     *
     * @return array
     * @throws \Exception
     */
    public function ecodeApply($num = 1) {
        if ( $num > 50000 ) {
            throw new \Exception('申请数量不能超过50000');
        }
        $params = 'clientId=' . encrypter::getClientId() . '/num=' . $num . '/timeStamp=' . encrypter::getTimeStamp() . '/sign=' . encrypter::getSignString();
        curl_setopt($this->curl, CURLOPT_URL, $this->domain . "/company/ecodeApply/" . $params);

        return $this->processRes(curl_exec($this->curl));
    }


    /**
     * 从模板中过滤出必要的产品信息字段
     *
     * @param $template
     * @param $onlyRequired
     * @return array
     */
    public function productItemFilter($template, $onlyRequired = true) {
        $typeList = [];
        for ( $i = 1; $i < 20; $i ++ ) {
            $index = 'tableList' . $i;
            if ( !isset($template[ $index ]) ) {
                break 1;
            }
            foreach ( $template[ $index ] as $item ) {
                $temp = $this->_templateItemFilter($item, true, $onlyRequired);
                if ( !empty($temp['list']) ) {
                    $typeList[] = $temp;
                }
            }
        }

        return $typeList;
    }

    /**
     * 从模板中过滤出必要的非产品信息字段
     *
     * @param $template
     * @param $onlyRequired
     * @return array
     */
    public function nonProductItemFilter($template, $onlyRequired = true) {
        $typeList = [];
        for ( $i = 1; $i < 20; $i ++ ) {
            $index = 'tableList' . $i;
            if ( !isset($template[ $index ]) ) {
                break 1;
            }
            foreach ( $template[ $index ] as $item ) {
                $temp = $this->_templateItemFilter($item, false, $onlyRequired);
                if ( !empty($temp['list']) ) {
                    $typeList[] = $temp;
                }
            }
        }

        return $typeList;
    }

    protected function _templateItemFilter($tab, $isProduct = true, $onlyRequired = true) {
        $list = [];
        if ( ($tab['isProduct'] === '1') === $isProduct ) {
            foreach ( $tab['detailList'] as $item ) {
                if ( ($item['isProduct'] === '1') === $isProduct && (!$onlyRequired || $item['required'] === '1')) {
                    $list[] = [ 'name' => $item['name'] ];
                }
            }
        }

        return [
            'type' => $tab['type'],
            'list' => $list
        ];
    }
    /**
     * 编码获取
     *
     * @param string $fileId
     *
     * @return array
     * @throws \Exception
     */
    public function queryEcodes($fileId) {
        $codes  = [];
        $params = 'clientId=' . encrypter::getClientId() . '/fileId=' . $fileId . '/timeStamp=' . encrypter::getTimeStamp() . '/sign=' . encrypter::getSignString();
        curl_setopt($this->curl, CURLOPT_URL, $this->domain . "/company/download/" . $params);

        // res is a zip stream
        $res = curl_exec($this->curl);

        // check curl response status
        if ( curl_getinfo($this->curl, CURLINFO_HTTP_CODE) !== 200 ) {
            throw new \Exception('下载失败');
        }


        // Save the zip stream to a temporary file
        $tmpFile = tempnam(sys_get_temp_dir(), 'zip');
        file_put_contents($tmpFile, $res);

        // Open the zip file
        $zip = new \ZipArchive;
        if ( $zip->open($tmpFile) === true ) {
            for ( $i = 0; $i < $zip->numFiles; $i ++ ) {
                //                $filename    = $zip->getNameIndex($i);
                $fileContent = $zip->getFromIndex($i);
                $fileContent = trim($fileContent);
                $codes       = array_merge($codes, explode("\n", $fileContent));
            }
            $zip->close();
        } else {
            throw new \Exception('读取下载内容失败');
        }

        // Delete the temporary file
        unlink($tmpFile);

        return $codes;
    }

    /**
     * 编码回退
     *
     * @param array | string $ecodes
     *
     * @return array
     * @throws \Exception
     */
    public function releaseEcodes($ecodes) {
        if ( is_array($ecodes) ) {
            $ecodes = implode(',', $ecodes);
        }

        curl_setopt($this->curl, CURLOPT_URL, $this->domain . "/company/fallbackByEcode/" . $this->params);
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode([ 'ecodes' => $ecodes ]));

        return $this->processRes(curl_exec($this->curl));
    }

    private function combineSignString() {
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
            file_put_contents('iotroot_' . date('Y-m-d') . '.log', $result . PHP_EOL, FILE_APPEND);
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