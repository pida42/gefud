<?php
namespace Notilio\DDx\Generator;

use Mandango\Mondator\Definition\Definition;
use Mandango\Mondator\Definition\Method;
use Mandango\Mondator\Dumper;

class EntityClassGenerator extends GeneratorAware
{
    private $className;
    private $nameSpace;
    private $variablesArray = [];

    public function __construct($className, $nameSpace, $variablesArray)
    {
        $this->className = $className;
        $this->nameSpace = $nameSpace;
        $this->variablesArray = $variablesArray;
    }

    public function create()
    {
        $definition = new Definition($this->className);
        $definition->setClass($this->nameSpace . '\\' . $this->className);
        $definition->setAbstract(true);

        $properties = $this->extractProperties();
        $definition->setProperties($properties);

        $methods = $this->extractMethods();
        $definition->setMethods($methods);

        $definition->addInterface('\Serializable');
        $definition->addMethod($this->createSerialize());
        $definition->addMethod($this->createUnserialize());

        return $definition;
    }

    private function extractProperties()
    {
        $properties = EntityPropertyGenerator::createFromArray($this->variablesArray);
        return $properties;
    }

    private function extractMethods()
    {
        $methods = [];

        $constructor = new EntityMethodConstructorGenerator($this->className, $this->variablesArray);
        $methods[] = $constructor->create();

        foreach ($this->variablesArray as $name => $type) {
            $methods[] = $this->createGetter($name, $type);
            if ($name == 'id') {
            continue;
            }
            $methods[] = $this->createSetter($name, $type);
        }
        return $methods;
    }

    private function createGetter($name, $type)
    {
        $ucName = ucfirst($name);
        $getter = new Method('public', 'get' . $ucName, '', <<<EOF
        return \$this->$name;
EOF
);
	    $getter->setDocComment(<<<EOF
    /**
     * Get $ucName value
     *
     * @return $type
     */
EOF
);
	    return $getter;
    }

    private function createSetter($name, $type)
    {
        $ucName = ucfirst($name);
        $setter = new Method('public', 'set' . $ucName, '$value', <<<EOF
        \$this->$name = \$value;
EOF
);
        $setter->setDocComment(<<<EOF
    /**
     * Set $ucName value
     *
     * @param $type \$name $ucName value to set
     */
EOF
);
        return $setter;
    }

    private function createSerialize()
    {
    	$serialize = new Method('public', 'serialize', '', <<<EOF
        // TODO: Implement generator
EOF
);
	    return $serialize;
    }

    private function createUnserialize()
    {
    	$unserialize = new Method('public', 'unserialize', '$data', <<<EOF
        // TODO: Implement generator
EOF
);
	    return $unserialize;
    }
}