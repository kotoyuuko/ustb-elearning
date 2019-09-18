<?php

namespace Kotoyuuko\UstbElearning\Client;

interface Client
{
    public function __construct(array $options);
    public function getClient();
}
