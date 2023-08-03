<?php

namespace Pebble\Database\Doc;

class DocTable
{
    public $name;
    public $comment;

    /**
     * @var DocField[]
     */
    public $fields = [];
}
