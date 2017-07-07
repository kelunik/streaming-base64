<?php

namespace Kelunik\StreamingBase64;

use Amp\ByteStream\InputStream;
use Amp\Promise;
use function Amp\call;

class DecodingInputStream implements InputStream {
    const TYPE_BASE64 = 0;
    const TYPE_BASE64URL = 1;

    private $source;
    private $type;
    private $buffer;

    public function __construct(InputStream $source, int $type = self::TYPE_BASE64) {
        $this->source = $source;
        $this->type = $type;
        $this->buffer = "";

        if ($type < 0 || $type > 1) {
            throw new \Error("Invalid type ({$type})");
        }
    }

    /** @inheritdoc */
    public function read(): Promise {
        return call(function () {
            if ($this->buffer === null) {
                return null;
            }

            $data = yield $this->source->read();

            if ($data === null) {
                $buffer = $this->buffer;

                $this->buffer = null;
                $this->source = null;

                if ($this->type === self::TYPE_BASE64) {
                    return \base64_decode($buffer);
                }

                return \base64_decode(\strtr($buffer, "-_", "+/"));
            }

            $this->buffer .= $data;
            $length = \strlen($this->buffer);
            $buffer = \substr($this->buffer, 0, $length - $length % 4);
            $this->buffer = \substr($this->buffer, $length - $length % 4);

            if ($buffer !== "") {
                if ($this->type === self::TYPE_BASE64) {
                    return \base64_decode($buffer);
                }

                return \base64_decode(\strtr($buffer, "-_", "+/"));
            }

            return "";
        });
    }
}
