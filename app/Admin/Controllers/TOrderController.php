<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\TOrder;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TOrderController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TOrder(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('order_no');
            $grid->column('package_id');
            $grid->column('package_name');
            $grid->column('package_code');
            $grid->column('amount');
            $grid->column('pay_amount');
            $grid->column('pay_type');
            $grid->column('pay_status');
            $grid->column('transaction_id');
            $grid->column('start_time');
            $grid->column('end_time');
            $grid->column('duration');
            $grid->column('remark');
            $grid->column('paid_at');
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
        return Show::make($id, new TOrder(), function (Show $show) {
            $show->field('id');
            $show->field('order_no');
            $show->field('package_id');
            $show->field('package_name');
            $show->field('package_code');
            $show->field('amount');
            $show->field('pay_amount');
            $show->field('pay_type');
            $show->field('pay_status');
            $show->field('transaction_id');
            $show->field('start_time');
            $show->field('end_time');
            $show->field('duration');
            $show->field('remark');
            $show->field('paid_at');
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
        return Form::make(new TOrder(), function (Form $form) {
            $form->display('id');
            $form->text('order_no');
            $form->text('package_id');
            $form->text('package_name');
            $form->text('package_code');
            $form->text('amount');
            $form->text('pay_amount');
            $form->text('pay_type');
            $form->text('pay_status');
            $form->text('transaction_id');
            $form->text('start_time');
            $form->text('end_time');
            $form->text('duration');
            $form->text('remark');
            $form->text('paid_at');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
