<?php

require_once __DIR__ . '/../vendor/autoload.php';

use React\EventLoop\Loop;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;
use React\Stream\CompositeStream;
use ReactphpX\StreamToStream\StreamToStream;

// 创建一个简单的回显服务器示例
$loop = Loop::get();

// 创建读写流
$readable = new ReadableResourceStream(STDIN, $loop);
$writable = new WritableResourceStream(STDOUT, $loop);

// 创建组合流
$inputStream = new CompositeStream($readable, $writable);
$outputStream = new CompositeStream($readable, $writable);

// 创建 StreamToStream 实例
$streamToStream = StreamToStream::create();

// 设置数据转换函数
$inMapBuffer = function($data) {
    return "Received: " . $data;
};

$outMapBuffer = function($data) {
    return "Echo: " . $data;
};

// 连接流
$streamToStream->from($inputStream, $inMapBuffer)
              ->bridge($outputStream, $outMapBuffer);

// 设置错误处理
$inputStream->on('error', function($error) use ($loop) {
    echo "Stream error: " . $error->getMessage() . "\n";
    $loop->stop();
});

// 设置完成处理
$inputStream->on('close', function() use ($loop) {
    echo "\nStream closed\n";
    $loop->stop();
});

// 提示用户输入
echo "Please enter some text (press Ctrl+D to end):\n";

Loop::addTimer(10, function() use ($inputStream, $loop) {
    $inputStream->end();
    $loop->stop();
});

// 运行事件循环
$loop->run(); 