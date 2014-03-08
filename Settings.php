<?php
namespace RemindCloud;
/**
 * Class Settings
 */
class Settings
{
    /**
     * @var
     */
    private static $instance;
    /**
     * @var array
     */
    private $settings;

    /**
     * @param $ini_file
     */
    private function __construct($ini_file)
    {
        $this->settings = parse_ini_file($ini_file, TRUE);
    }

    /**
     * @param $ini_file
     *
     * @return Settings
     */
    public static function getInstance($ini_file)
    {
        if (!isset(self::$instance))
        {
            self::$instance = new Settings($ini_file);
        }
        return self::$instance;
    }

    /**
     * @param $setting
     *
     * @return mixed
     */
    public function __get($setting)
    {
        if (array_key_exists($setting, $this->settings))
        {
            return $this->settings[$setting];
        }
        else
        {
            foreach ($this->settings as $section)
            {
                if (array_key_exists($setting, $section))
                {
                    return $section[$setting];
                }
            }
        }
    }
}