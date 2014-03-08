<?php
namespace RemindCloud;
use PDO;
include 'Settings.php';

class Db extends PDO
{
    public function __construct()
    {
        $settings = Settings::getInstance('settings.ini');
        parent::__construct("mysql:host=" . $settings->host . ";dbname=" . $settings->db, $settings->user, $settings->pass);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
    }
}