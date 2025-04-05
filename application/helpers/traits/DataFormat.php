<?php

trait DataFormat
{

    private function _toXml($data, &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item';
            }
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->_toXml($value, $subnode);
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    protected function asXml(array $data = [], string $xmlElement = '<?xml version="1.0"?><xmldata></xmldata>'): string
    {
        $content = "";
        if (empty($data) === false) {
            $xml = new SimpleXMLElement($xmlElement);
            $this->_toXml($data, $xml);
            $content = $xml->asXML();
        }

        return $content;
    }


    protected function asJson(array $data = []): string
    {
        $content = "";
        if (empty($data) === false) {
            $content = json_encode($data);
        }

        return $content;
    }


    protected function asArray(string $json = ""): array
    {
        $content = [];
        if (empty($json) === false) {
            $content = json_decode($json, true);
        }

        return $content;
    }


    protected function asObject(string $json = "")
    {
        $content = [];
        if (empty($json) === false) {
            $content = json_decode($json);
        }

        return $content;
    }


    protected function asEncryptRawurl(array $data = []): string
    {
        $valuestring = $this->asJson($data);
        $tokenString = $this->encryption->encrypt($valuestring);
        $code = rawurlencode($tokenString);
        return $code;
    }


    protected function asDecryptRawurl(string $encodeString = ""): string
    {
        $valuestring = rawurldecode($encodeString);
        $json = $this->encryption->decrypt($valuestring);
        return $json;
    }
}
