<?php

namespace App\Http\Controllers;

use App\Models\TPackage;

class HomeController extends Controller
{
    /**
     * 获取套餐列表
     */
   public function packetList()
   {
      //套餐表模型是TPackage 实现状态status为1的数据
      $list = TPackage::where('status',1)->get();

       return response()->json($list);

   }
}
