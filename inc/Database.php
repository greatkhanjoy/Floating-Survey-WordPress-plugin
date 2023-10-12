<?php

namespace Floating\Survey;

/**
 * The Database class
 */

class Database
{

    /**
     * class constructor
     */
    function __construct()
    {
        new DB\Survey();
        new DB\Questions();
        new DB\Results();
    }
}
