# ReactPHP Stream to Stream

一个用于处理 ReactPHP 流之间数据转换的库。这个库允许你轻松地在两个流之间建立双向连接，并在数据传输过程中进行转换。

## 特性

- 支持双向流数据传输
- 支持数据转换函数
- 错误处理和事件传播
- 支持流的关闭和资源清理
- 支持 Promise 和异步操作

## 安装

使用 Composer 安装:

```bash
composer require reactphp-x/stream-to-stream -vvv
```

## 基本用法

```php
use React\EventLoop\Loop;
use React\Stream\ThroughStream;
use ReactphpX\StreamToStream\StreamToStream;

$read1 = new ThroughStream();
$read2 = new ThroughStream();
$write1 = new ThroughStream();
$write2 = new ThroughStream();

// 创建流
$inputStream = new CompositeStream($read1, $write1);
$outputStream = new CompositeStream($read2, $write2);

// 创建 StreamToStream 实例
$streamToStream = StreamToStream::create();

// 设置数据转换函数
$inMapBuffer = function($data) {
    return "Received: " . $data;
};

// 设置输出数据转换函数
$outMapBuffer = function($data) {
    return "Echo: " . $data;
};

// 连接流并应用转换
$streamToStream->from($inputStream, $inMapBuffer)
              ->bridge($outputStream, $outMapBuffer);

// 写入数据
$inputStream->write("Hello World");
```

## 高级用法

### Echo 服务器示例

```php
use React\EventLoop\Loop;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;
use React\Stream\CompositeStream;
use ReactphpX\StreamToStream\StreamToStream;

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

// 连接流
$streamToStream->from($inputStream, $inMapBuffer)
              ->bridge($outputStream);

// 设置错误处理
$inputStream->on('error', function($error) use ($loop) {
    echo "Stream error: " . $error->getMessage() . "\n";
    $loop->stop();
});

// 运行事件循环
$loop->run();
```

## API 文档

### StreamToStream 类

#### `create()`
创建一个新的 StreamToStream 实例。

#### `from($fromStream, $inMapBuffer = null)`
设置源流和输入数据转换函数。
- `$fromStream`: 实现了 DuplexStreamInterface 的流对象
- `$inMapBuffer`: 可选的数据转换函数

#### `bridge($stream, $outMapBuffer = null)`
建立两个流之间的双向连接，并设置数据转换函数。
- `$stream`: 实现了 DuplexStreamInterface 的流对象或返回此类对象的 Promise
- `$outMapBuffer`: 可选的数据转换函数，用于从目标流返回到源流的数据转换

## 测试

运行测试：

```bash
composer install
./vendor/bin/phpunit tests
```

测试覆盖了以下场景：
- 基本数据传输
- 数据转换功能
- 错误处理
- 流关闭处理

## 要求

- PHP >= 8.1
- ReactPHP Stream ^1.4
- ReactPHP Promise ^3.2

## 许可证

MIT

## 作者

- wpjscc <wpjscc@gmail.com> 