<?php

namespace SlimCD;

/**
 * Class SlimCD
 * @package SlimCD
 * @version 1.0.1
 */
abstract class SlimCD implements Interfaces\SlimCD
{
    /**
     * @var string
     */
    public $transURL = "https://trans.slimcd.com";

    /**
     * @var string
     */
    public $statsURL = "https://stats.slimcd.com";

    /**
     * @var bool
     */
    public $debug = false;

    /**
     * Data to send
     * @var
     */
    protected $send;

    /**
     * Data that is received
     * @var
     */
    protected $receive;

    /**
     * Curl timeout
     * @var int
     */
    protected $defaultTimeout = 600;

    /**
     * @param $url
     * @param $errorMessage
     * @return object
     */
    protected function errorBlock($url, $errorMessage)
    {
        $reply = (object) array('response' => 'Error', 'responsecode' => '2', 'description' => $errorMessage,
                                'responseurl' => $url,'datablock' => '');
        $result = (object) array('reply' => $reply) ;

        return ($result);
    }

    /**
     * @param $urlString
     * @param $timeout
     * @param $nameValueArray
     * @return mixed|object
     */
    protected function httpPost($urlString, $timeout, $nameValueArray)
    {
        $ch = curl_init($urlString);

        curl_setopt($ch,CURLOPT_TIMEOUT, $timeout) ;
        curl_setopt($ch, CURLOPT_POST, 1);

        $this->send = http_build_query($nameValueArray) ;

        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->send);

        // SLIMCD.COM uses a GODADDY SSL certificate.  Once you install the CA for GoDaddy SSL, please remove the line below.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

        // POST the data
        $this->receive = curl_exec($ch);

        if(curl_errno($ch)) {
            $result = $this->errorBlock(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL), curl_error($ch));
        } else {

            $httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

            if (intval($httpstatus) !== 200 || ($contentType !== 'application/json' && $contentType !== 'text/javascript')) {
                $result =  $this->errorBlock(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL), $this->receive) ;
            } else {
                $result = json_decode($this->receive);
            }

            // Make sure we can decode the results...
            if($result === null) {
                switch (json_last_error()) {
                    case JSON_ERROR_NONE:
                        $errortext= ' - No errors';
                        break;
                    case JSON_ERROR_DEPTH:
                        $errortext = ' - Maximum stack depth exceeded';
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $errortext = ' - Underflow or the modes mismatch';
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        $errortext = ' - Unexpected control character found';
                        break;
                    case JSON_ERROR_SYNTAX:
                        $errortext= ' - Syntax error, malformed JSON';
                        break;
                    case JSON_ERROR_UTF8:
                        $errortext = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                        break;
                    default:
                        $errortext = ' - Unknown JSON error';
                        break;
                }
                $result = $this->errorBlock($urlString, $errortext);
            }
        }

        curl_close ($ch);

        // flatten out the "reply" so we don't have that extra (unneeded) level
        $myarray = get_object_vars($result->reply);
        if($this->debug) {
            $myarray = array_merge($myarray, array("senddata" => $this->send , "recvdata" => $this->receive));
        }
        $result = (object) $myarray;

        $this->send = '';
        $this->receive = '';

        return $result ;
    }

    /**
     * @param $timeout
     * @return int
     */
    protected function getTimeout($timeout)
    {
        if(!$timeout) {
            $timeout = $this->defaultTimeout;
        } else {
            $timeout = intval($timeout);
            if($timeout === 0) {
                $timeout = $this->defaultTimeout;
            }
        }
        return $timeout;
    }
}
