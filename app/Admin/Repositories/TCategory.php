<?php

namespace App\Admin\Repositories;

use App\Models\TCategory as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TCategory extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
