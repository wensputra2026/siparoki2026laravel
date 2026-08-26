<?php

namespace App\Models;

/**
 * Class Pastor
 * Alias/subclass for MasterPastor to maintain backward compatibility across modules.
 */
class Pastor extends MasterPastor
{
    protected $table = 'master_pastor';
}
