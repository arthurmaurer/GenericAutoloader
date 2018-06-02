<?php
namespace GenericAutoloader;

class GenericAutoloader
{
    protected $vendors = array();

    public function __construct($namespace, $path)
    {
        // In case we're bulk adding
        if (is_array($namespace))
            $this->vendors = $namespace;
        else
            $this->vendors = array($namespace => $path);

        $this->bind();
    }

    public function bind()
    {
        spl_autoload_register(array($this, "load"));
    }

    public function load($className)
    {
        $vendor = $this->getVendorFromClassName($className);

        if ($vendor !== null)
        {
            $classNameWithoutRoot = $this->removeNamespaceVendor($vendor, $className);
            $classPath = str_replace("\\", "/", $classNameWithoutRoot);
            $filename = $this->vendors[$vendor] . DIRECTORY_SEPARATOR . $classPath .".php";

            return $this->tryToRequire($filename, $className);
        }

        return false;
    }

    protected function getVendorFromClassName($className)
    {
        foreach ($this->vendors as $vendor => $path)
        {
            if (strpos($className, $vendor) === 0)
                return $vendor;
        }

        return null;
    }

    protected function tryToRequire($filename, $className)
    {
        if (file_exists($filename))
        {
            require_once $filename;

            if (class_exists($className))
                return true;
        }

        return false;
    }

    protected function removeNamespaceVendor($vendor, $className)
    {
        return ltrim(mb_substr($className, mb_strlen($vendor)), "\\");
    }
}