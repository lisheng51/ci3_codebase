<?php

class MY_Exceptions extends CI_Exceptions
{

    public function __construct()
    {
        parent::__construct();
    }

    public function show_404($page = '', $log_error = false)
    {
        parent::show_404($page, false);
    }
}
