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
     * Test to see if manipulating an object is not changing the signature
     */
    public function testCouplingObjectsHasNoInfluence()
    {
        $dog = new Dog();
        $expectedSignature = ClassSignature::create($dog)
            ->generate();

        $dog->chase(new Rabbit());
        $resultSignature = ClassSignature::create($dog)
            ->generate();

        $this->assertJsonStringEqualsJsonString(
            $expectedSignature,
            $resultSignature
        );
    }

    /**
     * Test case conversion
     */
    public function testConversionOfCase()
    {
        $resultSignature = ClassSignature::create('Dog')
            ->convertCase()
            ->generate();

        $results = json_decode($resultSignature, true);

        $this->assertFalse(array_key_exists('silenceAllDogs', $results['Dog']['methods']));
        $this->assertTrue(array_key_exists('silencealldogs', $results['Dog']['methods']));

        $this->assertFalse(array_key_exists('doSilence', $results['Dog']['methods']['silencealldogs']['parameters']));
        $this->assertTrue(array_key_exists('dosilence', $results['Dog']['methods']['silencealldogs']['parameters']));
    }
}