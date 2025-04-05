<?php

#[Attribute]
class PermissionAttribute
{

    public function __construct(public string $value)
    {
    }
}
