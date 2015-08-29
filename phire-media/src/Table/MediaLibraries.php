<?php

namespace Phire\Media\Table;

use Pop\Db\Record;

class MediaLibraries extends Record
{

    /**
     * Table prefix
     * @var string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Primary keys
     * @var array
     */
    protected $primaryKeys = ['id'];

}