<?php

namespace Src\Exception;

use Hyperf\Utils\Traits\StaticInstance;

class Propagation
{
    use StaticInstance;

    /**
     * 确定异常是否应该传播到下一个处理程序。
     *
     * @var bool
     */
    protected $propagationStopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function setPropagationStopped(bool $propagationStopped): Propagation
    {
        $this->propagationStopped = $propagationStopped;
        return $this;
    }
}