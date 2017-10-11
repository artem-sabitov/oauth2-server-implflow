<?php

namespace OAuth2\Grant\Implicit\Adapter;

interface AdapterInterface
{
    /**
     * @return string
     */
    public function getClientId();

    /**
     * @return string
     */
    public function getRedirectUri();

    /**
     * @return string
     */
    public function getResponseType();
}