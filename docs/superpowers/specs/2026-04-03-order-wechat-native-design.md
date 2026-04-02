# 订单下单与微信 Native 支付设计

## 目标

在现有套餐与订单模型基础上，新增前台下单接口与微信支付 `Native` 二维码支付能力，并实现支付回调落库，基于现有 `t_orders` 表完成，不改表名。

## 范围

- 新增下单接口：前端传 `package_id` 与 `billing_cycle`
- 后端根据 `t_packages` 计算订单金额并创建本地订单
- 调用微信支付 `Native` 下单接口，返回 `code_url`
- 新增微信支付异步回调接口
- 回调验签、解密并幂等更新 `t_orders`
- 新增微信支付配置项与必要测试

## 不在范围内

- 退款
- 主动查单接口
- 支付超时主动关闭订单
- 分账、优惠券、邀请码、会员权益发放

## 路由设计

### `POST /order/create`

请求参数：

- `package_id`：套餐 ID
- `billing_cycle`：`month` 或 `year`

返回字段：

- `order_no`
- `package_id`
- `package_name`
- `package_code`
- `billing_cycle`
- `amount`
- `pay_amount`
- `pay_type`
- `pay_status`
- `code_url`

### `POST /wechat/pay/notify`

由微信支付服务端回调：

- 验证签名
- 解密回调资源
- 根据 `out_trade_no` 查订单
- 幂等更新支付信息
- 返回微信要求的成功应答

## 数据设计

现有 `t_orders` 已包含大部分字段，但缺少订单购买周期。建议新增：

- `billing_cycle`：`month` / `year`

现有字段使用约定：

- `order_no`：本地商户订单号，同时作为微信 `out_trade_no`
- `amount`：套餐原价
- `pay_amount`：实际支付金额
- `pay_type`：固定写 `wechat`
- `pay_status`：`0` 未支付，`1` 已支付，`2` 已取消，`3` 已退款
- `transaction_id`：微信支付交易单号
- `paid_at`：支付成功时间
- `duration`：购买时长，月付写 `1`，年付写 `12`

## 组件设计

### `OrderController`

职责：

- 校验请求参数
- 调用订单服务创建本地订单
- 调用微信支付服务发起 `Native` 下单
- 返回统一 JSON 响应

### `WechatPayController`

职责：

- 接收微信支付回调
- 验签与异常处理
- 调用订单服务更新支付状态
- 输出微信要求的 JSON 应答

### `OrderService`

职责：

- 查询有效套餐
- 根据 `billing_cycle` 计算金额与时长
- 生成订单号
- 创建 `t_orders` 记录
- 处理支付成功后的幂等回写

### `WechatPayService`

职责：

- 读取商户配置
- 生成微信支付 APIv3 请求签名
- 调用 `/v3/pay/transactions/native`
- 验签回调请求
- 解密回调 `resource`

## 数据流

1. 前端请求 `/order/create`
2. 后端校验套餐存在且 `status = 1`
3. 后端按 `billing_cycle` 计算价格
4. 后端在 `t_orders` 写入待支付订单
5. 后端调用微信 `Native` 下单接口
6. 微信返回 `code_url`
7. 前端把 `code_url` 生成二维码展示
8. 用户支付成功后，微信回调 `/wechat/pay/notify`
9. 后端验签、解密并将订单更新为已支付

## 配置设计

新增 `services.wechat_pay` 配置，字段包括：

- `app_id`
- `mch_id`
- `serial_no`
- `private_key_path`
- `api_v3_key`
- `notify_url`
- `public_key_id`
- `public_key_path`

`.env.example` 中同步增加示例配置。

## 安全与异常处理

- 下单接口只信任后端价格计算，不接收前端金额
- 微信回调必须做签名校验
- 回调解密失败、签名失败、订单不存在都应记录日志
- 回调更新必须幂等，避免重复通知重复改状态
- 微信回调路由需要排除 CSRF 校验

## 测试设计

### Feature

- 创建订单成功，返回 `code_url`
- 套餐不存在或禁用时返回校验错误
- `billing_cycle` 非法时返回校验错误
- 微信回调成功时订单更新为已支付
- 重复回调时保持幂等

### Unit

- 订单金额与时长计算正确
- 微信支付签名串生成与响应解析逻辑可测试

