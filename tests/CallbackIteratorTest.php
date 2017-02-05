<?php

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
            array(range(5, 10, .1)),
            array(array('q', 'w', 'e', 'r', 't', 'y'))
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

            return mt_rand();
        });

        foreach ($iterator as $randValue) {}
        foreach ($iterator as $randValue) {}
    }

    public function testMemory()
    {
        $arrayProcess = new Process('php memoryUsage.php array', __DIR__);
        $arrayProcess->run();
        $callbackProcess = new Process('php memoryUsage.php callback', __DIR__);
        $callbackProcess->run();
        $callbackMemory = (int) $callbackProcess->getOutput();
        $arrayMemory = (int) $arrayProcess->getOutput();
        $div = 1024 * 1024;
        $this->assertTrue(
            $callbackMemory < $arrayMemory,
            sprintf('Callback: %0.2f MB, array: %0.2f MB', $callbackMemory / $div, $arrayMemory / $div)
        );
    }
}