<?php

namespace Kelunik\StreamingBase64\Test;

use Amp\ByteStream\InMemoryStream;
use Amp\ByteStream\IteratorStream;
use Amp\ByteStream\Message;
use Amp\Loop;
use Amp\PHPUnit\TestCase;
use Kelunik\StreamingBase64\EncodingInputStream;
use function Amp\Iterator\fromIterable;

class EncodingInputStreamTest extends TestCase {
    public function testInvalidType() {
        $this->expectException(\Error::class);
        new EncodingInputStream(new InMemoryStream(""), 2);
    }

    public function testBase64() {
        Loop::run(function () {
            $array = [\random_bytes(16), \random_bytes(1), \random_bytes(23)];
            $stream = new IteratorStream(fromIterable($array, 10));

            $encodingStream = new EncodingInputStream($stream, EncodingInputStream::TYPE_BASE64);
            $result = yield new Message($encodingStream);

            $this->assertSame(\base64_encode(\implode("", $array)), $result);
        });
    }

    public function testBase64Url() {
        Loop::run(function () {
            $array = [\random_bytes(16), \random_bytes(1), \random_bytes(23)];
            $stream = new IteratorStream(fromIterable($array, 10));

            $encodingStream = new EncodingInputStream($stream, EncodingInputStream::TYPE_BASE64URL);
            $result = yield new Message($encodingStream);

            $this->assertSame(\rtrim(\strtr(\base64_encode(\implode("", $array)), "+/", "-_"), "="), $result);
        });
    }
}
