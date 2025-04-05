<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Diflex
 *
 * @author Administrator
 */
class Jwt_sample extends Su_Controller
{

    private function getCode()
    {
        $response = $this->curl(site_url('api/jwt/Code/access'));
        $obj_geo = json_decode($response);
        return $obj_geo->message;
    }

    public function index()
    {
        redirect(site_url('oauth/Login/index?code=' . $this->getCode()));
    }

    private function firstToken()
    {
        $tokenFromLogin = 'd595ae77e62e479d437b3ce651c0da1add85ac13d18f6afa630befd00cb3219e9ce38d3022c1e40fe66c49b2349849d07030f0914256a2a5605d1b9bfd16a045PYNpCbMk%2BnPy%2BOUO9LaSO%2F1KjA%2F2StVp4Pu0YQcsrSZpt7sRVYGbnVdkwgyCDLIYPj803dpKvm0solwyr6Q4r0y9MXpITEy%2BElDjZ71S%2BvE%3D';
        return $tokenFromLogin;
    }

    public function user()
    {
        $token = $this->firstToken();
        $response = $this->curl(site_url('api/jwt/User/index'), [], $token);
        $val = json_decode($response);
        if ($val->statusCode === 95) {
            $newToken = $this->refreshToken($token);
            $response = $this->curl(site_url('api/jwt/User/index'), [], $newToken);
            $val = json_decode($response);
        }
        dump($val);
    }

    public function login()
    {
        $token = $this->firstToken();
        $param['base_url'] = 'http://localhost/intranet/';
        $response = $this->curl(site_url('api/jwt/Login/windows'), $param, $token);
        $val = json_decode($response);
        if ($val->statusCode === 95) {
            $newToken = $this->refreshToken($token);
            $response = $this->curl(site_url('api/jwt/Login/index'), $param, $newToken);
            $val = json_decode($response);
        }
        redirect($val->message);
    }

    private function refreshToken(string $token = ""): string
    {
        $response = $this->curl(site_url('api/jwt/Token/refresh'), [], $token);
        $val = json_decode($response);
        if ($val->statusCode === 100) {
            return $val->message;
        }
        return "";
    }

    private function curl(string $url = "", array $params = [], string $token = "", string $apiId = "4", string $apiKey = "ca63be25600ead9dd7d7d588ab4ee425")
    {
        if (empty($url) === true || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return;
        }
        $httpheaders = array(
            "apiId:  $apiId",
            "token:  $token",
            "apiKey: $apiKey"
        );
        $fields_string = http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0); //return url reponse header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheaders);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100020);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return null;
        }
        curl_close($ch);
        if ($response === false || empty($response) === true) {
            return null;
        }
        return $response;
    }
}
