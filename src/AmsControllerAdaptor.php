<?php

namespace AmsController;

class AmsControllerAdaptor
{

    private $smsService;
    private $smsControllerNumber;

    public function __construct($smsService,$smsControllerNumber)
    {
        if (!$smsService) {
            die('sms service is empty');
            exit;
        }
        $this->smsControllerNumber = $smsControllerNumber ;
        $this->smsService = $smsService;
    }

    public function outputSwitchOn($switchNumber)
    {
        $command = "ON:$switchNumber";
        return $this->execute($command);
    }
    
    public function outputSwitchOff($switchNumber)
    {
        $command = "OFF:$switchNumber";
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
    
    private function execute($command)
    {
        return ['command'=>$command,'result'=>$this->smsService->send($this->smsControllerNumber, $command)];
    }

    

}
