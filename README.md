# ams controller
aekh.ir ams sms controllers adaptor .


#### Install

    composer require aekh/sms-controller dev-master

#### usage
    use AmsController\AmsControllerAdaptor;

    $number = 09100000000; // sms controller number
    $smsService = new SmsService(); // sms service class must implement send($receptor, $message) method
    $AmsControllerAdaptor = new AmsControllerAdaptor(smsService, number);
    $result = $AmsControllerAdaptor->turnOffAlarm() ;
