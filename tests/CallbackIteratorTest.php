<?php

/*
 * This file is part of the awesomite/iterators package.
 *
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Awesomite\Iterators;

use Symfony\Component\Process\Process;

/**
 * @internal
 */
class CallbackIteratorTest extends BaseTestCase
{
    /**
     * @dataProvider providerInvalidConstructor
     * @expectedException \InvalidArgumentException
     *
     * @param $argument
     */
    public function testInvalidConstructor($argument)
    {
        new CallbackIterator($argument);
    }

    public function providerInvalidConstructor()
    {
        return array(
            array(true),
            array(null),
            array('hello world'),
        );
    }

    /**
     * @dataProvider providerIterate
     *
     * @param array $inputData
     */
    public function testIterate(array $inputData)
    {
        $outputData = array();

        $copiedData = new \ArrayIterator($inputData);
        /** @var CallbackIterator $iterator */
        $iterator = new CallbackIterator(function () use ($copiedData) {
            if (!$copiedData->valid()) {
                throw new StopIterateException();
            }

            $result = $copiedData->current();
            $copiedData->next();

            return $result;
        });
        foreach ($iterator as $key => $value) {
            $outputData[$key] = $value;
        }
        $this->assertSame($inputData, $outputData);
    }

    public function providerIterate()
    {
        return array(
            array(array()),
            array(array(1, 2, 3, 4, 5)),
            array(\range(5, 10, .1)),
            array(array('q', 'w', 'e', 'r', 't', 'y')),
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testTwoLoops()
    {
        $i = 0;
        /** @var CallbackIterator $iterator */
        $iterator = new CallbackIterator(function () use (&$i, &$iterator) {
            if (++$i > 5) {
                CallbackIterator::stopIterate();
            }

            return \mt_rand();
        });

        foreach ($iterator as $randValue) {
        }
        foreach ($iterator as $randValue) {
        }
    }

    /**
     * @dataProvider providerLength
     *
     * @param int $length
     */
    public function testMemory($length)
    {
        $arrayProcess = new Process('php memoryUsage.php array ' . $length, __DIR__);
        $arrayProcess->run();
        $callbackProcess = new Process('php memoryUsage.php callback ' . $length, __DIR__);
        $callbackProcess->run();
        $callbackMemory = (int)$callbackProcess->getOutput();
        $arrayMemory = (int)$arrayProcess->getOutput();
        $mb = 1024 * 1024;
        $this->assertTrue(
            $callbackMemory <= ($arrayMemory + $mb),
            \sprintf('Callback: %0.2f MB, array: %0.2f MB', $callbackMemory / $mb, $arrayMemory / $mb)
        );
        \register_shutdown_function(function () use ($callbackMemory, $arrayMemory, $mb, $length) {
            echo \sprintf(
                "Length: % 7d, callback: % 6.2f MB, array: % 6.2f MB\n",
                $length,
                $callbackMemory / $mb,
                $arrayMemory / $mb
            );
        });
    }

    public function providerLength()
    {
        return array(
            array(1),
            array(10),
            array(100),
            array(10000),
            array(100000),
            array(1000000),
        );
    }
}
