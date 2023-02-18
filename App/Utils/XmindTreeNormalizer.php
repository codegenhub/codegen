<?php

namespace Material4\Codegen\App\Utils;


class XmindTreeNormalizer
{
    private const RESERVED_KEY = 'title';

    public function __construct(private StringTree $stringTree)
    {
    }
}
