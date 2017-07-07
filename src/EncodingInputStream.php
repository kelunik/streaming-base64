<?php

namespace Kelunik\StreamingBase64;

use Amp\ByteStream\InputStream;
use Amp\Promise;
use function Amp\call;

class EncodingInputStream implements InputStream {
    const TYPE_BASE64 = 0;
    const TYPE_BASE64URL = 1;

    private $source;
    private $type;
    private $buffer;

    public function __construct(InputStream $source, int $type = self::TYPE_BASE64) {
        $this->source = $source;
        $this->type = $type;
        $this->buffer = "";
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

                if ($buffer !== "") {
                    if ($this->type === self::TYPE_BASE64) {
                        return \base64_encode($buffer);
                    } else {
                        return \rtrim(\strtr(\base64_encode($buffer), "+/", "-_"), "=");
                    }
                }

                return null;
            }

            $this->buffer .= $data;
            $length = \strlen($this->buffer);
            $buffer = \substr($this->buffer, 0, $length - $length % 3);
            $this->buffer = \substr($this->buffer, $length - $length % 3);

            if ($buffer !== "") {
                if ($this->type === self::TYPE_BASE64) {
                    return \base64_encode($buffer);
                } else {
                    return \strtr(\base64_encode($buffer), "+/", "-_");
                }
            }

            return "";
        });
    }
}
