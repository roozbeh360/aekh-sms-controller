<?php

namespace AmsController;

class AmsControllerAdaptor
{

    private $smsService;
    private $smsControllerNumber;

    public function __construct($smsService, $smsControllerNumber)
    {
        if (!$smsService) {
            die('sms service is empty');
            exit;
        }
        $this->smsControllerNumber = $smsControllerNumber;
        $this->smsService = $smsService;
    }

    public function outputSwitchOn($switchNumber)
    {
        $command = "OFF:$switchNumber";
        return $this->execute($command);
    }

    public function outputSwitchOff($switchNumber)
    {
        $command = "ON:$switchNumber";
        return $this->execute($command);
    }

    public function addUser($contact)
    {
        $command = "USER.NEW=$contact#";
        return $this->execute($command);
    }

    public function reportAllUsers()
    {
        $command = "USER.REP";
        return $this->execute($command);
    }

    public function formatAllUsers()
    {
        $command = "USER.FORMAT";
        return $this->execute($command);
    }

    public function removeUserById($id)
    {
        $command = "USER.DEL=$id";
        return $this->execute($command);
    }

    public function turnOnAlarm()
    {
        $command = "RM.ON";
        return $this->execute($command);
    }

    public function turnOffAlarm()
    {
        $command = "RM.OFF";
        return $this->execute($command);
    }

    public function turnOnDevice()
    {
        $command = "@DZ1";
        return $this->execute($command);
    }

    public function turnOffDevice()
    {
        $command = "@DZ0";
        return $this->execute($command);
    }

    public function getStatus()
    {
        $command = "REP";
        return $this->execute($command);
    }

    public function getTemperature()
    {
        $command = "S.REP";
        return $this->execute($command);
    }

    public function outputSwitchOnByDuration($number, $duration)
    {
        $command = "TMR.$number($duration)";
        return $this->execute($command);
    }

    public static function mapResponse($response)
    {

        $r = trim(str_replace(['#AEK', '---'], '', $response));

        if ($r == 'k set R') {
            return ['type' => 'status', 'result' => true];
        }

        if ($r == 'YES') {
            return ['type' => 'status', 'result' => true];
        }

        if ($r == 'User Admin Saved') {
            return ['type' => 'status', 'result' => true];
        }

        if (strpos($r, 'Alert') > -1) {
            $t_m = explode(':', $r);
            return ['event' => 'alert', 'result' => self::translateResponse($t_m[1])];
        }

        if (strpos($r, 'Report') > -1) {
            return ['event' => 'report', 'result' => self::translateResponse($r)];
        }
    }

    private function translateResponse($response)
    {
        if (strpos($response, 'AC Power Normal') > -1) {
            return ['type' => 'power', 'status' => true]; // city power on
        }

        if (strpos($response, 'AC Power Alarm') > -1) {
            return ['type' => 'power', 'status' => false]; // city power off
        }

        if (strpos($response, 'Dama Normal ast') > -1) {
            return ['type' => 'temperature', 'status' => false]; // normal temperature
        }

        if (strpos($response, 'Alarme Dama Darid') > -1) {
            return ['type' => 'temperature', 'status' => true]; // high temperature
        }

        if (strpos($response, 'INPUT') > -1 && strpos($response, 'Normal') > -1) {
            preg_match_all('!\d+!', $response, $ports);
            return ['type' => 'input', 'status' => false, 'port' => $ports[0][0]]; // normal input
        }

        if (strpos($response, 'INPUT') > -1 && strpos($response, 'Alarm') > -1) {
            preg_match_all('!\d+!', $response, $ports);
            return ['type' => 'input', 'status' => true, 'port' => $ports[0][0]]; // alert input
        }
        
        if (strpos($response, 'Config') > -1) {
            $c_r = trim(preg_replace('/\s+/', ' ', str_replace(['Config Report... C Temp(', ') C Siren(', ') C Dial(', ') C Secure(', ') C Relay(', ')'], ' ', $response)));
            $c = explode(' ', $c_r);           
            return ['type' => 'status', 'params' =>
                [
                    'temp' => explode('-', $c[0])[0].'C',
                    'min_temp' => explode('-', $c[0])[1],
                    'max_temp' => explode('-', $c[0])[2],
                    'siren' => explode('-', $c[1]),
                    'dial' => explode('-', $c[2]),
                    'secure' => explode('-', $c[3]),
                    'relay' => explode('-', $c[4])
                ]
            ];
        }

        if (strpos($response, 'Report') > -1) {
            $c_r = trim(preg_replace('/\s+/', ' ', str_replace(['Report...', 'OUT:', 'IN:', 'temp:', 'DZ:', 'RM:'], ' ', $response)));            
            $c = explode(' ', $c_r) ;        
            return ['type' => 'status', 'params' =>
                [
                    'out1' => (boolean)explode('-', $c[0])[0],
                    'out2' => (boolean)explode('-', $c[0])[1],
                    'out3' => (boolean)explode('-', $c[0])[2],
                    'in1' => !((boolean) explode('-', $c[1])[0]),
                    'in2' => !((boolean) explode('-', $c[1])[1]),
                    'in3' => !((boolean) explode('-', $c[1])[2]),
                    'cp' => (boolean) explode('-', $c[1])[3],
                    'temp' => $c[2],
                    'dz' => $c[3],
                    'rm' => $c[4],
                ]
            ];
        }
    }

    private function execute($command)
    {
        return ['command' => $command, 'result' => $this->smsService->send($this->smsControllerNumber, $command)];
    }

}
