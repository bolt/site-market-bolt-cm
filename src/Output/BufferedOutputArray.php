<?php

namespace Bundle\Site\MarketPlace\Output;

use Symfony\Component\Console\Output\Output;

/**
 * Buffered output array.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BufferedOutputArray extends Output
{
    /** @var array */
    private $buffer = [];

    /**
     * Return and flush the buffer.
     *
     * @return array
     */
    public function fetch()
    {
        $content = $this->buffer;
        $this->buffer = [];

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($message, $newline)
    {
        if ($newline) {
            $this->buffer[] = $message;
        } else {
            end($this->buffer);
            $lastIndex = key($this->buffer);
            $this->buffer[$lastIndex] .= $message;
        }
    }
}
