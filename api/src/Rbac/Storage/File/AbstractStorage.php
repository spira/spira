<?php


namespace Spira\Rbac\Storage\File;


abstract class AbstractStorage
{
    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->load();
    }

    /**
     * Loads authorization data from persistent storage.
     */
    abstract protected function load();

    /**
     * Loads the authorization data from a PHP script file.
     *
     * @param string $file the file path.
     * @return array the authorization data
     */
    protected function loadFromFile($file)
    {
        if (is_file($file)) {
            return require($file);
        } else {
            return [];
        }
    }


    /**
     * Saves the authorization data to a PHP script file.
     *
     * @param array $data the authorization data
     * @param string $file the file path.
     */
    protected function saveToFile($data, $file)
    {
        file_put_contents($file, "<?php\nreturn " . var_export($data, true) . ";\n", LOCK_EX);
    }
}