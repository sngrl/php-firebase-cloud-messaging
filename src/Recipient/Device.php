<?php
namespace sngrl\PhpFirebaseCloudMessaging\Recipient;

class Device extends Recipient
{
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }
}