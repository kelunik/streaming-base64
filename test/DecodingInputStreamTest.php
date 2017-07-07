<?php

namespace Kelunik\StreamingBase64\Test;

use Amp\ByteStream\InMemoryStream;
use Amp\ByteStream\IteratorStream;
use Amp\ByteStream\Message;
use Amp\Loop;
use Amp\PHPUnit\TestCase;
use Kelunik\StreamingBase64\DecodingInputStream;
use function Amp\Iterator\fromIterable;

class DecodingInputStreamTest extends TestCase {
    public function testInvalidType() {
        $this->expectException(\Error::class);
        new DecodingInputStream(new InMemoryStream(""), 2);
    }

    public function testBase64() {
        Loop::run(function () {
            $expected = "0d8de60626e522dc2c4bebb7e99b222b";
            $array = ["M", "GQ4ZGU2MD", "YyNmU1MjJkYzJjNGJlYmI3ZTk5YjIyMmI", "="];
            $stream = new IteratorStream(fromIterable($array, 10));

            $decodingStream = new DecodingInputStream($stream, DecodingInputStream::TYPE_BASE64);
            $result = yield new Message($decodingStream);

            $this->assertSame($expected, $result);
        });
    }

    public function testBase64Url() {
        Loop::run(function () {
            $expected = "0d8de60626e522dc2c4bebb7e99b222b";
            $array = ["M", "GQ4ZGU2MD", "YyNmU1MjJkYzJjNGJlYmI3ZTk5YjIyMmI"];
            $stream = new IteratorStream(fromIterable($array, 10));

            $decodingStream = new DecodingInputStream($stream, DecodingInputStream::TYPE_BASE64URL);
            $result = yield new Message($decodingStream);

            $this->assertSame($expected, $result);
        });
    }
}
