<?php

namespace App\Admin\Repositories;

use App\Models\TPackage as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TPackage extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
