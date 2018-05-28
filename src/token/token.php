<?php
namespace wacai\open\token;
class Token
{
    private $access_token;
    private $refresh_token;
    private $expires_in;

    public function __construct($token, $refresh, $expire)
    {
        $this->access_token = $token;
        $this->refresh_token = $refresh;
        $this->expires_in = $expire;
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    public function getExpire()
    {
        return $this->expires_in;
    }
}

?>
