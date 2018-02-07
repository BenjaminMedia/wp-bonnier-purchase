<?php

namespace Bonnier\WP\Purchase\Interfaces;

interface UserInterface
{
    public function getIdentifier();
    
    public function validateIdentifier();
}
