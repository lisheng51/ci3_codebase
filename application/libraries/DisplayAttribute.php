<?php

#[Attribute]
class DisplayAttribute
{

    public function __construct(public string $description, public string $title = '', public int $order_num = 0, public string $action = '')
    {
    }
}
