<?php

namespace Core\CommonBundle\Twig;

class ConstantsExtension extends \Twig_Extension {

    /**
     * The filter's name: constants
     * 
     * @return array
     */
    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('constants', array($this, 'getConstants')),
        );
    }

    /**
     * Returns the constants of the given $namespace class via ReflectionClass::getConstants() method.
     * The $namespace must contain the class's fully qualified name and can be used '/' or '\\' as well in it.
     * 
     * @param string $namespace
     * @return array
     */
    public function getConstants($namespace) {
        if (!is_null($namespace)) {
            if (strpos($namespace, '/') !== false) {
                $explodedClass = explode('/', $namespace);
                $namespace = array_shift($explodedClass);
                foreach ($explodedClass as $classPiece) {
                    $namespace .= '\\' . $classPiece;
                }
            }
            $reflectedClass = new \ReflectionClass($namespace);
            return $reflectedClass->getConstants();
        }
    }

    public function getName() {
        return 'constants_extension';
    }

}
