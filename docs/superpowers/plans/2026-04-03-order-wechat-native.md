# Order Wechat Native Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 基于现有 `t_packages` 与 `t_orders`，实现套餐下单、微信 `Native` 二维码支付和支付回调落库。

**Architecture:** 使用控制器 + 服务层拆分。控制器负责 HTTP 入参与响应，订单服务负责订单创建和状态回写，微信支付服务负责 APIv3 签名、下单请求、回调验签和解密。通过配置文件读取商户号参数，并在 `web` 路由上新增下单与回调接口。

**Tech Stack:** Laravel 12, PHPUnit 11, Laravel HTTP Client, Eloquent, 微信支付 APIv3

---

### Task 1: 固定订单字段与微信配置入口

**Files:**
- Create: `database/migrations/2026_04_03_000001_add_billing_cycle_to_t_orders_table.php`
- Modify: `app/Models/TOrder.php`
- Modify: `config/services.php`
- Modify: `.env.example`

- [ ] **Step 1: 写失败测试，覆盖订单需要记录购买周期**

在 `tests/Feature/Order/CreateOrderTest.php` 新增一个测试，请求创建订单后断言数据库中存在 `billing_cycle` 字段值。

- [ ] **Step 2: 运行测试，确认因字段或实现缺失而失败**

Run: `php artisan test tests/Feature/Order/CreateOrderTest.php`
Expected: FAIL，提示不存在下单接口或数据库字段不满足断言

- [ ] **Step 3: 新增迁移与模型 fillable/casts 最小实现**

新增迁移给 `t_orders` 增加 `billing_cycle`，并在 `TOrder` 中补充基本属性配置。

- [ ] **Step 4: 增加微信配置映射**

在 `config/services.php` 与 `.env.example` 增加 `wechat_pay` 所需配置键。

- [ ] **Step 5: 运行测试验证此阶段通过**

Run: `php artisan test tests/Feature/Order/CreateOrderTest.php`
Expected: 仍可能失败，但失败原因应推进到“接口未实现”而不是字段缺失

### Task 2: 实现下单接口与订单创建服务

**Files:**
- Create: `app/Http/Controllers/OrderController.php`
- Create: `app/Http/Requests/CreateOrderRequest.php`
- Create: `app/Services/OrderService.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Order/CreateOrderTest.php`

- [ ] **Step 1: 写失败测试，定义下单接口契约**

测试覆盖：
- 成功下单时返回 `order_no`、`code_url`、`pay_status`
- `billing_cycle` 非法时返回 422
- 套餐不存在或禁用时返回业务错误

- [ ] **Step 2: 运行测试，确认失败原因正确**

Run: `php artisan test tests/Feature/Order/CreateOrderTest.php`
Expected: FAIL，提示路由或控制器不存在

- [ ] **Step 3: 写最小实现**

实现参数校验、套餐查询、金额计算、订单号生成和待支付订单落库。

- [ ] **Step 4: 使用 HTTP fake 打通微信 Native 下单最小返回**

在测试中 fake 微信接口返回 `code_url`，控制器将其原样返回。

- [ ] **Step 5: 运行测试验证通过**

Run: `php artisan test tests/Feature/Order/CreateOrderTest.php`
Expected: PASS

### Task 3: 实现微信 Native 支付服务

**Files:**
- Create: `app/Services/WechatPayService.php`
- Test: `tests/Unit/WechatPayServiceTest.php`

- [ ] **Step 1: 写失败测试，定义微信下单请求结构**

测试覆盖：
- 请求体包含 `appid`、`mchid`、`description`、`out_trade_no`、`notify_url`
- 金额按分传入 `amount.total`
- 成功解析 `code_url`

- [ ] **Step 2: 运行测试，确认失败**

Run: `php artisan test tests/Unit/WechatPayServiceTest.php`
Expected: FAIL，提示服务类不存在

- [ ] **Step 3: 写最小实现**

实现 APIv3 `Authorization` 生成、请求头组装、Native 下单调用和 `code_url` 解析。

- [ ] **Step 4: 再跑单元测试**

Run: `php artisan test tests/Unit/WechatPayServiceTest.php`
Expected: PASS

### Task 4: 实现微信支付回调处理

**Files:**
- Create: `app/Http/Controllers/WechatPayController.php`
- Modify: `app/Services/OrderService.php`
- Modify: `app/Services/WechatPayService.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/WechatPayNotifyTest.php`

- [ ] **Step 1: 写失败测试，定义支付成功回调行为**

测试覆盖：
- 回调成功时将订单更新为已支付
- 重复回调不重复更新
- 异常签名返回失败响应

- [ ] **Step 2: 运行测试，确认失败**

Run: `php artisan test tests/Feature/WechatPayNotifyTest.php`
Expected: FAIL，提示回调路由或控制器不存在

- [ ] **Step 3: 写最小实现**

实现回调路由、CSRF 排除、验签、解密、按 `out_trade_no` 更新订单。

- [ ] **Step 4: 运行测试验证通过**

Run: `php artisan test tests/Feature/WechatPayNotifyTest.php`
Expected: PASS

### Task 5: 整体验证与清理

**Files:**
- Verify: `tests/Feature/Order/CreateOrderTest.php`
- Verify: `tests/Feature/WechatPayNotifyTest.php`
- Verify: `tests/Unit/WechatPayServiceTest.php`

- [ ] **Step 1: 跑新增测试**

Run: `php artisan test tests/Feature/Order/CreateOrderTest.php tests/Feature/WechatPayNotifyTest.php tests/Unit/WechatPayServiceTest.php`
Expected: PASS

- [ ] **Step 2: 跑全量测试**

Run: `php artisan test`
Expected: PASS

- [ ] **Step 3: 检查配置和路由**

Run: `php artisan route:list`
Expected: 包含 `/order/create` 与 `/wechat/pay/notify`

