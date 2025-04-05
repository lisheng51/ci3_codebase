<?php

class Address extends Ajax_Controller
{
    public function data()
    {
        $zipcode = CIInput()->post('zipcode') ?? '';
        $houseNumber = CIInput()->post('housenr') ?? '';

        $json["city"] = null;
        $json["province"] = null;
        $json["street"] = null;
        $json["municipality"] = null;
        $json["country"] = null;
        $json["lat"] = 0;
        $json["lng"] = 0;
        $json["msg"] = "Geen adres gevonden!";
        $json["status"] = "error";

        if (empty($zipcode) || empty($houseNumber)) {
            exit(json_encode($json));
        }

        $housenr_addition = CIInput()->post('housenr_addition') ?? '';

        $ckArray = $this->splitHouseNumber($houseNumber);
        if (!empty($ckArray[1])) {
            $housenr_addition = $ckArray[1];
            $houseNumber = $ckArray[0];
        }

        $arrResult = Api_service::addressSearch($zipcode, $houseNumber, $housenr_addition);

        if (empty($arrResult)) {
            exit(json_encode($json));
        }

        $json["city"] = $arrResult["city"];
        $json["street"] = $arrResult["street"];
        $json["province"] = $arrResult["state"];
        $json["municipality"] = $arrResult["locality"];
        $json["lat"] =  $arrResult["lat"];
        $json["lng"] =  $arrResult["lng"];
        $json["country"] = "Nederland";
        $json["status"] = "good";
        $json["msg"] = "Postcode is correct";
        exit(json_encode($json));
    }


    private function splitHouseNumber(string $houseNumber = ""): array
    {
        $houseNumberAddition = '';
        $match = [];
        if (preg_match('~^(?<number>[0-9]+)(?:[^0-9a-zA-Z]+(?<addition1>[0-9a-zA-Z ]+)|(?<addition2>[a-zA-Z](?:[0-9a-zA-Z ]*)))?$~', $houseNumber, $match)) {
            $houseNumber = $match['number'];
            $houseNumberAddition = isset($match['addition2']) ? $match['addition2'] : (isset($match['addition1']) ? $match['addition1'] : '');
        }
        return [$houseNumber, $houseNumberAddition];
    }
}
