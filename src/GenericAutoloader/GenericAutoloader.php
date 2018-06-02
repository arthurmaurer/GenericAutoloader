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
        $root = $this->getNamespaceRoot($className);

        if (array_key_exists($root, $this->vendors))
        {
            $classNameWithoutRoot = $this->removeNamespaceRoot($className);
            $classPath = str_replace('\\', "/", $classNameWithoutRoot);
            $filename = $this->vendors[$root] . DIRECTORY_SEPARATOR . $classPath .".php";

            return $this->tryToRequire($filename, $className);
        }

        return false;
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

    protected function getNamespaceRoot($className)
    {
        $i = strpos($className, '\\');

        if ($i === false)
            return $className;

        return substr($className, 0, $i);
    }

    protected function removeNamespaceRoot($className)
    {
        $i = strpos($className, '\\');

        if ($i === false)
            return $className;

        return substr($className, $i + 1);
    }
}