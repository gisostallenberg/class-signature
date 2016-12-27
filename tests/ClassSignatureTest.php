<?php
namespace GisoStallenberg\ClassSignature\Tests;

use Dog;
use GisoStallenberg\ClassSignature\ClassSignature;
use PHPUnit_Framework_TestCase;
use Rabbit;

class ClassSignatureTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the create method
     */
    public function testCreation()
    {
        $this->assertEquals(new ClassSignature('stdClass'), ClassSignature::create('stdClass'));
    }

    /**
     * See if both an object instance or a
     */
    public function testClassNameAndObjectCanBothBeUsed()
    {
        $classNameSignature = ClassSignature::create('Dog')
            ->generate();
        $objectSignature = ClassSignature::create(new Dog())
            ->generate();

        $this->assertJsonStringEqualsJsonString($classNameSignature, $objectSignature);
    }


    /**
     * See if both an object instance or a
     */
    public function testDifferentClassesAreNotTheSame()
    {
        $classNameSignature = ClassSignature::create('Dog')
            ->generate();
        $objectSignature = ClassSignature::create('Rabbit')
            ->generate();

        $this->assertJsonStringNotEqualsJsonString($classNameSignature, $objectSignature);
    }

    /**
     * Test an export
     */
    public function testExportAll()
    {
        $resultSignature = ClassSignature::create(new Dog())
            ->generate();

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Resources/signatures/Dog.protected.json',
            $resultSignature
        );
    }

    /**
     * Test an export
     */
    public function testExportPublic()
    {
        $resultSignature = ClassSignature::create(new Dog())
            ->onlyPublic()
            ->generate();

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Resources/signatures/Dog.public.json',
            $resultSignature
        );
    }

    /**
     * Test an export
     */
    public function testExportAnonymisedParameters()
    {
        $resultSignature = ClassSignature::create(new Dog())
            ->anonymiseParameters()
            ->generate();

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Resources/signatures/Dog.anonymised.json',
            $resultSignature
        );
    }

    /**
     * Test an export
     */
    public function testCouplingObjectsHasNoInfluence()
    {
        $dog = new Dog();
        $dog->chase(new Rabbit());
        $resultSignature = ClassSignature::create($dog)
            ->generate();

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Resources/signatures/Dog.protected.json',
            $resultSignature
        );
    }
}