<?php

namespace SlimCD\Images;

class Images_DownloadSignatureRequest
{
    // property declaration
    public $username = '';
    public $password = '';
    public $gateid = 0;

    public function jsonSerialize() {
        return (get_object_vars($this));
    }
}