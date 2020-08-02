<?php

namespace Rawilk\Settings\Support;

class ContextSerializer
{
    public function serialize(Context $context = null): string
    {
        return serialize($context);
    }
}
