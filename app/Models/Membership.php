<?php

declare(strict_types=1);

namespace App\Models;

use Laravel\Jetstream\Membership as JetstreamMembership;

class Membership extends JetstreamMembership
{
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
}
