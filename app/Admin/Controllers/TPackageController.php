<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\TPackage;
use App\Models\TPackage as TPackageModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TPackageController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TPackage(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('code');
            $grid->column('price');
            $grid->column('year_price');
            $grid->column('features');
            $grid->column('sort');
            $grid->column('status');
            $grid->column('trial_days');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new TPackage(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('code');
            $show->field('price');
            $show->field('year_price');
            $show->field('features');
            $show->field('sort');
            $show->field('status');
            $show->field('trial_days');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new TPackage(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('code');
            $form->text('price');
            $form->text('year_price');
            $form->textarea('features')
                ->rows(8)
                ->help('每行填写一个功能点')
                ->customFormat(function ($value) {
                    return TPackageModel::formatFeaturesForTextarea($value);
                })
                ->saving(function ($value) {
                    return TPackageModel::normalizeFeaturesInput($value);
                });
            $form->text('sort');
            $form->text('status');
            $form->text('trial_days');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
