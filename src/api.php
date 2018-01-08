<?php

function bonnier_purchase()
{
    return isset($GLOBALS['bonnier_purchase']) ? $GLOBALS['bonnier_purchase'] : null;
}
