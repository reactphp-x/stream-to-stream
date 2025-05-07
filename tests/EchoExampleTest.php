<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use React\Stream\ThroughStream;
use React\Stream\CompositeStream;
use ReactphpX\StreamToStream\StreamToStream;

class EchoExampleTest extends TestCase
{
    private $read1;
    private $read2;
    private $write1;
    private $write2;
    private $inputStream;
    private $outputStream;
    private $streamToStream;
    private $outputData = '';

    protected function setUp(): void
    {
        $read1 = new ThroughStream();   
        $read2 = new ThroughStream();
        $write1 = new ThroughStream();
        $write2 = new ThroughStream();

        $this->read1 = $read1;
        $this->read2 = $read2;
        $this->write1 = $write1;
        $this->write2 = $write2;

        $this->inputStream = new CompositeStream($read1, $write1);
        $this->outputStream = new CompositeStream($read2, $write2);
        $this->streamToStream = StreamToStream::create();
        
        // 重置输出数据
        $this->outputData = '';
        
        // 监听输出流的数据
        $this->write2->on('data', function($data) {
            $this->outputData .= $data;
        });
    }

    public function testDataTransformation(): void
    {
        $this->streamToStream->from($this->inputStream)
                           ->bridge($this->outputStream);

        $testData = "Hello World";
        $this->read1->emit('data', [$testData]);

        $this->assertEquals($testData, $this->outputData);
    }

    public function testDataTransformationWithMappers(): void
    {
        $inMapBuffer = function($data) {
            return "Received: " . $data;
        };

        $this->streamToStream->from($this->inputStream, $inMapBuffer)
                           ->bridge($this->outputStream);

        $testData = "Hello World";
        $this->read1->emit('data', [$testData]);

        $this->assertEquals("Received: Hello World", $this->outputData);
    }

    public function testErrorHandling(): void
    {
        $errorCaught = false;
        $this->read1->on('error', function() use (&$errorCaught) {
            $errorCaught = true;
        });

        $this->streamToStream->from($this->inputStream)
                           ->bridge($this->outputStream);

        $this->read1->emit('error', [new \Exception('Test error')]);
        
        $this->assertTrue($errorCaught);
    }

    public function testStreamClosure(): void
    {
        $isClosed = false;
        $this->read1->on('close', function() use (&$isClosed) {
            $isClosed = true;
        });

        $this->streamToStream->from($this->inputStream)
                           ->bridge($this->outputStream);

        $this->read1->close();
        
        $this->assertTrue($isClosed);
    }

    protected function tearDown(): void
    {
        $this->read1->close();
        $this->read2->close();
        $this->write1->close();
        $this->write2->close();
    }
} 