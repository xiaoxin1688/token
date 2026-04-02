# 前台首页与产品页 Blade 化设计

## 目标

将以下两个静态页面转成可在当前 Laravel 项目中直接运行的前台页面：

- `/Users/macfans/php/TokenApp/stitch/dark_mode/code.html` 作为首页 PC 端展示
- `/Users/macfans/php/TokenApp/stitch/packages_page/code.html` 作为产品页展示

并在转换过程中：

- 抽取共享头部与底部
- 将产品页套餐卡片改成读取 `t_packages` 表
- 点击套餐购买时调用现有 `POST /order/create` 接口
- 在产品页内以弹窗方式展示微信 Native 支付二维码

## 范围

- 新增前台页面控制器与 Blade 模板
- 抽取前台公共布局、头部、底部
- 首页挂载到 `/`
- 产品页挂载到 `/packages`
- 套餐页动态渲染 `t_packages`
- 套餐页实现月付/年付前端切换
- 套餐页购买按钮对接现有 `OrderController@store`
- 新增订单支付状态查询接口 `GET /orders/{orderNo}/status`
- 产品页弹窗中根据 `code_url` 生成二维码

## 不在范围内

- 手机端重新设计
- 用户登录/注册体系
- 订单中心页面
- 支付成功后的权益发放
- 自动长轮询、WebSocket 或推送通知

## 现状约束

- 项目当前已有 `OrderController@store`，用于创建订单并调用微信 Native 下单
- 项目当前已有 `t_packages`、`t_orders`
- 产品页 UI 原稿中的头部和底部与首页风格一致，适合抽成复用布局
- 产品页目前需要展示数据库动态数据，而不是静态写死套餐卡片

## 路由设计

### 页面路由

- `GET /`
  - 渲染首页
- `GET /packages`
  - 渲染产品页

### 业务路由

- `POST /order/create`
  - 继续复用现有下单接口
- `GET /orders/{orderNo}/status`
  - 新增订单状态查询接口

返回字段：

- `order_no`
- `pay_status`
- `paid_at`
- `transaction_id`

## 控制器设计

### `FrontPageController`

职责：

- `home()`
  - 渲染首页 Blade
- `packages()`
  - 查询 `t_packages` 中启用套餐
  - 按 `sort asc` 排序
  - 预处理 `features` 字段后传给产品页

### `OrderController`

继续复用现有 `store()`，前台购买弹窗通过 JS 发起请求。

### `OrderStatusController`

新增轻量查询控制器，职责：

- 根据 `order_no` 查询 `t_orders`
- 返回支付状态所需最小字段
- 用于产品页弹窗点击“我已支付，刷新状态”时调用

## 视图结构

建议新增以下视图文件：

- `resources/views/layouts/frontend.blade.php`
  - 前台公共 `<html>`、`<head>`、字体、共享样式入口
- `resources/views/partials/frontend-header.blade.php`
  - 首页和产品页共用头部导航
- `resources/views/partials/frontend-footer.blade.php`
  - 首页和产品页共用底部
- `resources/views/pages/home.blade.php`
  - 基于 `dark_mode/code.html` 改写
- `resources/views/pages/packages.blade.php`
  - 基于 `packages_page/code.html` 改写

## 页面设计

### 首页

目标：

- 尽量保留原始视觉风格
- 替换原静态 HTML 中不适合 Laravel 的直接写死结构
- 保留 PC 端展示优先级

主要内容：

- 固定头部导航
- Hero 区域
- 特性区、常见问题区、渠道区
- 共享底部

导航跳转建议：

- 首页：`/`
- 产品：`/packages`
- 其他菜单先保留展示或锚点占位，不扩展额外业务页面

### 产品页

目标：

- 保留原稿视觉
- 将静态套餐卡片改成数据库驱动
- 在当前页内完成下单和支付二维码展示

主要内容：

- 顶部月付/年付切换
- 套餐网格
- 定制方案 CTA 区
- 共享底部
- 支付弹窗

## 动态套餐映射

产品页中每个套餐卡片字段映射如下：

- 标题：`name`
- 标识：`code`
- 月价：`price`
- 年价：`year_price`
- 功能列表：`features`
- 排序：`sort`
- 是否展示：`status = 1`

### `features` 字段处理

由于数据库字段是 JSON，控制器层需要统一处理成数组：

- 若已是数组，直接使用
- 若是 JSON 字符串，解码后使用
- 若为空或异常，回退为空数组

### 推荐态规则

先采用简单可维护规则：

- `code = 'pro'` 优先高亮
- 若不存在 `pro`，则不做特殊推荐态

这样避免为纯展示需求新增额外数据库字段。

## 购买与支付流程

### 页面交互

1. 用户在 `/packages` 页面切换月付或年付
2. 用户点击某张套餐卡的“立即购买”
3. 前端调用 `POST /order/create`
4. 后端返回订单信息与 `code_url`
5. 前端打开支付弹窗
6. 前端将 `code_url` 渲染为二维码
7. 用户扫码支付后，点击“我已支付，刷新状态”
8. 前端调用 `GET /orders/{orderNo}/status`
9. 若状态为已支付，弹窗切换为成功态

### 请求入参

下单请求继续沿用现有结构：

- `package_id`
- `billing_cycle`

其中 `billing_cycle` 仅允许：

- `month`
- `year`

### 支付弹窗展示内容

- 套餐名
- 计费周期
- 支付金额
- 订单号
- 二维码
- “我已支付，刷新状态”按钮
- “关闭”按钮

### 支付成功态

支付状态变更为 `pay_status = 1` 后：

- 显示“支付成功”
- 展示订单号和支付时间
- 关闭扫码区块或弱化二维码
- 不跳转出产品页

## 前端实现方式

### 样式

优先保留原稿视觉，但做 Laravel 化整合：

- 共享字体和主题配置收口到布局层
- 共享头尾拆 partial
- 页面专属复杂样式可保留在各自 Blade 中，避免一次性重构过度

### 交互脚本

使用少量原生 JS 或项目已有前端入口脚本，不引入额外重框架。

脚本职责：

- 月付/年付切换
- 打开与关闭支付弹窗
- 请求 `/order/create`
- 生成二维码
- 请求 `/orders/{orderNo}/status`
- 更新支付状态显示

### 二维码生成

采用轻量前端二维码库，根据后端返回的 `code_url` 直接生成二维码图像。

理由：

- 不需要服务端再做二维码图片生成
- 页面交互更直接
- 依赖更轻

## 错误处理

### 下单失败

场景：

- 套餐不存在
- 套餐禁用
- 微信支付接口异常

处理：

- 弹窗不打开
- 页面展示简短错误提示

### 查询支付状态失败

处理：

- 保持弹窗打开
- 提示用户稍后重试

### 二维码生成失败

处理：

- 显示错误提示
- 保留订单号，方便后续人工排查

## 测试设计

### Feature Tests

- 首页 `/` 可正常返回
- 产品页 `/packages` 可正常返回
- 产品页可读取数据库套餐并渲染
- 订单状态查询接口可返回订单状态

### 页面级手动验证

- 打开首页确认视觉结构与原稿一致
- 打开产品页确认套餐来自 `t_packages`
- 切换月付/年付时价格展示正确
- 点击购买后能弹出二维码弹窗
- 支付状态刷新时能正确反映 `pay_status`

## 兼容与边界

- 本次以 PC 端优先，不针对移动端重构布局
- 若原稿中存在大量第三方 CDN 依赖，应迁移为项目可控的资源加载方式或尽量减少外部依赖
- 不修改现有 `OrderController@store` 接口契约，避免影响已完成的支付接入

