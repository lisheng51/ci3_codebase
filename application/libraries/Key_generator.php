<?php

class Key_generator
{

    private $indexJaar1 = 19;
    private $indexJaar2 = 3;
    private $indexJaar3 = 12;
    private $indexJaar4 = 5;
    private $indexJaarCopy1 = 17;
    private $indexJaarCopy2 = 7;
    private $indexJaarCopy3 = 16;
    private $indexJaarCopy4 = 4;
    private $indexNaam1 = 2;
    private $indexNaam2 = 13;
    private $indexNaam3 = 14;
    private $indexNaam4 = 9;
    private $indexNaam5 = 18;
    private $indexNaam6 = 8;
    private $indexNaam7 = 1;
    private $naamVerlenger = "GGGGGGG";
    private $optellenBijJaar = 1647;
    private $optellenBijJaarCopy = 1453;
    private $aantalDagenNaJaar = 30;
    private $formatTeken = '-';
    private $size = 20;
    private static $instance;

    /**
     * Get the Key_generator singleton
     *
     * @static
     * @return	object
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        log_message('debug', 'Key_generator Class Initialized');
    }

    /**
     * 
     * @param int $license_year
     * @param string $license_name
     * @return string
     */
    public function makeString(int $license_year = 0, string $license_name = ""): string
    {
        if ($license_year <= 0) {
            $license_year = date('Y');
        }

        $pool = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
        $randomString = substr(str_shuffle($pool), 0, $this->size);
        $randomString = $this->SetJaar2Sleutel($randomString, $license_year);
        $randomString = $this->SetJaarCopy2Sleutel($randomString, $license_year);
        $randomString = $this->LicentieNaam2Sleutel($randomString, $license_name);
        return substr($randomString, 0, 4) . $this->formatTeken .
            substr($randomString, 4, 4) . $this->formatTeken .
            substr($randomString, 8, 4) . $this->formatTeken .
            substr($randomString, 12, 4) . $this->formatTeken .
            substr($randomString, 16, 4);
    }

    /**
     * 
     * @param string $randomString
     * @return int
     */
    public function GetJaarInSleutel(string $randomString = ""): int
    {
        $randomString = str_replace($this->formatTeken, "", $randomString);
        $jaarInSleutel = substr($randomString, $this->indexJaar1, 1) . substr($randomString, $this->indexJaar2, 1) . substr($randomString, $this->indexJaar3, 1) . substr($randomString, $this->indexJaar4, 1);
        $tempJaar = $jaarInSleutel;
        return (int) $tempJaar - $this->optellenBijJaar;
    }

    /**
     * 
     * @param string $randomString
     * @return int
     */
    private function GetJaarCopyInSleutel(string $randomString = ""): int
    {
        $jaarInSleutel = substr($randomString, $this->indexJaarCopy1, 1) . substr($randomString, $this->indexJaarCopy2, 1) . substr($randomString, $this->indexJaarCopy3, 1) . substr($randomString, $this->indexJaarCopy4, 1);
        $tempJaar = $jaarInSleutel;
        return (int) $tempJaar - $this->optellenBijJaarCopy;
    }

    /**
     * 
     * @param string $randomString
     * @return int
     */
    public function BerekenDagenNogTeGaan(string $randomString = ""): int
    {
        $licentieJaar = $this->GetJaarInSleutel($randomString);
        $datumEindeLicentie = date('Y-01-01', strtotime("+1 year", strtotime($licentieJaar . '-12-31')));
        $bday = new DateTime($datumEindeLicentie);
        $date_now = date("Y-m-d");
        $today = new DateTime($date_now);
        $diff = $today->diff($bday);
        if ($diff->invert > 0) {
            return 0;
        }
        return $diff->days;
    }

    /**
     * 
     * @param string $randomString
     * @return string
     */
    public function ControleOpJaar(string $randomString = ""): array
    {
        $randomString = str_replace($this->formatTeken, "", $randomString);
        $jaarVandaag = date('Y');
        $jaarInSleutel = $this->GetJaarInSleutel($randomString);
        $jaarCopyInSleutel = $this->GetJaarCopyInSleutel($randomString);

        $result['status'] = 'good';
        $result['msg'] = "Dit is geldige sleutel";

        if ($jaarInSleutel != $jaarCopyInSleutel) {
            $result['status'] = 'error';
            $result['msg'] = "Dit is geen geldige sleutel. Neem contact op met de helpdesk van LogiPoort.";
        }

        if ($jaarInSleutel <= 0) {
            $result['status'] = 'error';
            $result['msg'] = "Dit is geen geldige sleutel. Neem contact op met de helpdesk van LogiPoort.";
        }

        if ($jaarInSleutel < $jaarVandaag) {
            $aantalDagen = $this->BerekenDagenNogTeGaan($randomString);
            if ($aantalDagen > 0 && $aantalDagen <= $this->aantalDagenNaJaar) {
                $result['status'] = 'error';
                $result['msg'] = "Uw sleutel vervalt binnen " . $aantalDagen . " dag(en)";
            }

            if ($aantalDagen <= 0) {
                $result['status'] = 'error';
                $result['msg'] = "Uw sleutel is verlopen. U kunt alleen nog aangiftes indienen voor periodes in " . $jaarInSleutel . " of in de jaren daarvoor.";
            }
        }

        return $result;
    }

    /**
     * 
     * @param string $randomString
     * @param string $license_name
     * @return bool
     */
    public function ControleOpLicentieNaam(string $randomString = "", string $license_name = ""): bool
    {
        $randomString = str_replace($this->formatTeken, "", $randomString);
        $new = strtoupper($license_name);
        //$new = preg_replace('/[^A-Za-z0-9]/', '', $new);
        $new = str_replace(" ", "", $new);
        $new = str_replace(".", "", $new);
        $new = str_replace("O", "", $new);
        if (strlen($new) < 7) {
            $new = str_pad($new, 7, $this->naamVerlenger);
        }
        $length = strlen($new);
        $result = true;
        if (substr($randomString, $this->indexNaam1, 1) != substr($new, $length - 1, 1)) {
            $result = false;
        }

        if (substr($randomString, $this->indexNaam2, 1) != substr($new, $length - 2, 1)) {
            $result = false;
        }

        if (substr($randomString, $this->indexNaam3, 1) != substr($new, $length - 3, 1)) {
            $result = false;
        }

        if (substr($randomString, $this->indexNaam4, 1) != substr($new, $length - 4, 1)) {
            $result = false;
        }

        if (substr($randomString, $this->indexNaam5, 1) != substr($new, $length - 5, 1)) {
            $result = false;
        }

        if (substr($randomString, $this->indexNaam6, 1) != substr($new, $length - 6, 1)) {
            $result = false;
        }

        if (substr($randomString, $this->indexNaam7, 1) != substr($new, $length - 7, 1)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 
     * @param string $randomString
     * @param int $license_year
     * @return string
     */
    private function SetJaar2Sleutel(string $randomString = "", int $license_year = 0): string
    {
        $jaar = intval($license_year) + $this->optellenBijJaar;
        $randomString = substr($randomString, 0, $this->indexJaar1) . substr($jaar, 0, 1) . substr($randomString, $this->indexJaar1 + 1);
        $randomString = substr($randomString, 0, $this->indexJaar2) . substr($jaar, 1, 1) . substr($randomString, $this->indexJaar2 + 1);
        $randomString = substr($randomString, 0, $this->indexJaar3) . substr($jaar, 2, 1) . substr($randomString, $this->indexJaar3 + 1);
        $randomString = substr($randomString, 0, $this->indexJaar4) . substr($jaar, 3, 1) . substr($randomString, $this->indexJaar4 + 1);
        return $randomString;
    }

    /**
     * 
     * @param string $randomString
     * @param int $license_year
     * @return string
     */
    private function SetJaarCopy2Sleutel(string $randomString = "", int $license_year = 0): string
    {
        $jaar = intval($license_year) + $this->optellenBijJaarCopy;
        $randomString = substr($randomString, 0, $this->indexJaarCopy1) . substr($jaar, 0, 1) . substr($randomString, $this->indexJaarCopy1 + 1);
        $randomString = substr($randomString, 0, $this->indexJaarCopy2) . substr($jaar, 1, 1) . substr($randomString, $this->indexJaarCopy2 + 1);
        $randomString = substr($randomString, 0, $this->indexJaarCopy3) . substr($jaar, 2, 1) . substr($randomString, $this->indexJaarCopy3 + 1);
        $randomString = substr($randomString, 0, $this->indexJaarCopy4) . substr($jaar, 3, 1) . substr($randomString, $this->indexJaarCopy4 + 1);
        return $randomString;
    }

    /**
     * 
     * @param string $randomString
     * @param string $license_name
     * @return string
     */
    private function LicentieNaam2Sleutel(string $randomString = "", string $license_name = ""): string
    {
        $new = strtoupper($license_name);
        //$new = preg_replace('/[^A-Za-z0-9]/', '', $new);
        $new = str_replace(" ", "", $new);
        $new = str_replace(".", "", $new);
        $new = str_replace("O", "", $new);

        if (strlen($new) < 7) {
            $new = str_pad($new, 7, $this->naamVerlenger);
        }
        $length = strlen($new);
        $randomString = substr($randomString, 0, $this->indexNaam1) . substr($new, $length - 1, 1) . substr($randomString, $this->indexNaam1 + 1);
        $randomString = substr($randomString, 0, $this->indexNaam2) . substr($new, $length - 2, 1) . substr($randomString, $this->indexNaam2 + 1);
        $randomString = substr($randomString, 0, $this->indexNaam3) . substr($new, $length - 3, 1) . substr($randomString, $this->indexNaam3 + 1);
        $randomString = substr($randomString, 0, $this->indexNaam4) . substr($new, $length - 4, 1) . substr($randomString, $this->indexNaam4 + 1);
        $randomString = substr($randomString, 0, $this->indexNaam5) . substr($new, $length - 5, 1) . substr($randomString, $this->indexNaam5 + 1);
        $randomString = substr($randomString, 0, $this->indexNaam6) . substr($new, $length - 6, 1) . substr($randomString, $this->indexNaam6 + 1);
        $randomString = substr($randomString, 0, $this->indexNaam7) . substr($new, $length - 7, 1) . substr($randomString, $this->indexNaam7 + 1);
        return $randomString;
    }

    public function test_eigen()
    {
        $license_name = "Te";
        $license_year = 2019;
        $randomString = $this->makeString($license_year, $license_name);
        $randomString = str_replace($this->formatTeken, "", $randomString);
        $result = $this->ControleOpLicentieNaam($randomString, $license_name);
        $msg = null;
        if ($result === true) {
            $msg = $this->ControleOpJaar($randomString);
        }
        var_dump($msg);
    }
}
