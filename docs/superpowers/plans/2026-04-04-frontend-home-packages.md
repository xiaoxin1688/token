# Frontend Home And Packages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 将首页和产品页静态 HTML 转成 Laravel Blade 页面，复用公共头尾，产品页动态读取 `t_packages` 并在页内弹窗完成微信 Native 二维码支付。

**Architecture:** 新增一个前台页面控制器负责渲染 `/` 和 `/packages`，将公共结构拆成 `frontend` 布局与头尾 partial。产品页使用后端提供的套餐数据渲染卡片，并通过少量前端脚本调用现有 `POST /order/create` 与新增的订单状态查询接口，完成下单和支付状态刷新。

**Tech Stack:** Laravel 12, Blade, Vite, Tailwind CSS 4, 原生 JavaScript, PHPUnit 11

---

### Task 1: 建立前台页面路由与基础返回测试

**Files:**
- Create: `app/Http/Controllers/FrontPageController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/FrontPageRoutesTest.php`

- [ ] **Step 1: 写失败测试，定义首页和产品页路由行为**

```php
public function test_home_page_is_accessible(): void
{
    $response = $this->get('/');

    $response->assertOk();
}

public function test_packages_page_is_accessible(): void
{
    $response = $this->get('/packages');

    $response->assertOk();
}
```

- [ ] **Step 2: 运行测试，确认因控制器或视图不存在而失败**

Run: `php artisan test tests/Feature/FrontPageRoutesTest.php`
Expected: FAIL，提示 `/packages` 路由不存在或视图缺失

- [ ] **Step 3: 写最小实现**

```php
class FrontPageController extends Controller
{
    public function home(): View
    {
        return view('pages.home');
    }

    public function packages(): View
    {
        return view('pages.packages', ['packages' => collect()]);
    }
}
```

并在 `routes/web.php` 中把 `/` 与 `/packages` 指向新控制器。

- [ ] **Step 4: 再跑测试，确认路由层通过**

Run: `php artisan test tests/Feature/FrontPageRoutesTest.php`
Expected: PASS

- [ ] **Step 5: 提交该阶段**

```bash
git add app/Http/Controllers/FrontPageController.php routes/web.php tests/Feature/FrontPageRoutesTest.php
git commit -m "feat(frontend): add public page routes"
```

### Task 2: 抽取公共布局、头部、底部并落地首页 Blade

**Files:**
- Create: `resources/views/layouts/frontend.blade.php`
- Create: `resources/views/partials/frontend-header.blade.php`
- Create: `resources/views/partials/frontend-footer.blade.php`
- Create: `resources/views/pages/home.blade.php`
- Modify: `resources/css/app.css`
- Test: `tests/Feature/FrontPageRoutesTest.php`

- [ ] **Step 1: 扩展失败测试，断言首页包含共享导航与首页关键文案**

```php
$response->assertSee('Neural Nexus', false)
    ->assertSee('星云算力', false)
    ->assertSee('产品', false);
```

- [ ] **Step 2: 运行测试，确认因 Blade 内容未落地而失败**

Run: `php artisan test tests/Feature/FrontPageRoutesTest.php`
Expected: FAIL，提示关键文案不存在

- [ ] **Step 3: 写最小实现**

将 `stitch/dark_mode/code.html` 转为 `pages.home`，并把共享头尾抽到 partial：

```blade
{{-- resources/views/layouts/frontend.blade.php --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>...</head>
  <body>
    @include('partials.frontend-header')
    @yield('content')
    @include('partials.frontend-footer')
  </body>
</html>
```

- [ ] **Step 4: 调整共享样式入口**

在 `resources/css/app.css` 中增加前台字体变量、背景和通用组件类，避免首页和产品页重复定义整段基础样式。

- [ ] **Step 5: 跑测试验证首页通过**

Run: `php artisan test tests/Feature/FrontPageRoutesTest.php`
Expected: PASS

- [ ] **Step 6: 提交该阶段**

```bash
git add resources/views/layouts/frontend.blade.php resources/views/partials/frontend-header.blade.php resources/views/partials/frontend-footer.blade.php resources/views/pages/home.blade.php resources/css/app.css tests/Feature/FrontPageRoutesTest.php
git commit -m "feat(frontend): add shared public layout and home page"
```

### Task 3: 动态渲染产品页套餐卡片

**Files:**
- Modify: `app/Http/Controllers/FrontPageController.php`
- Modify: `app/Models/TPackage.php`
- Create: `resources/views/pages/packages.blade.php`
- Test: `tests/Feature/FrontPackagesPageTest.php`

- [ ] **Step 1: 写失败测试，定义产品页读取 `t_packages` 的行为**

```php
public function test_packages_page_renders_enabled_packages_from_database(): void
{
    TPackage::query()->forceCreate([
        'name' => '专业版',
        'code' => 'pro',
        'price' => 1999,
        'year_price' => 19990,
        'features' => json_encode(['4x NVIDIA T4 GPU']),
        'sort' => 1,
        'status' => 1,
        'trial_days' => 7,
    ]);

    $response = $this->get('/packages');

    $response->assertOk()->assertSee('专业版')->assertSee('4x NVIDIA T4 GPU');
}
```

- [ ] **Step 2: 运行测试，确认因产品页仍是空页面或静态页面而失败**

Run: `php artisan test tests/Feature/FrontPackagesPageTest.php`
Expected: FAIL

- [ ] **Step 3: 写最小实现**

在控制器中查询并预处理套餐：

```php
$packages = TPackage::query()
    ->where('status', 1)
    ->orderBy('sort')
    ->get()
    ->map(fn (TPackage $package) => [
        'id' => $package->id,
        'name' => $package->name,
        'code' => $package->code,
        'price' => $package->price,
        'year_price' => $package->year_price,
        'features' => $package->features_array,
    ]);
```

在模型中为 `features` 增加 cast 或访问器，统一转数组。

- [ ] **Step 4: 将 `stitch/packages_page/code.html` 转为动态 Blade**

使用 `@foreach ($packages as $package)` 生成卡片，保留原视觉骨架，但价格和特性来自数据库。

- [ ] **Step 5: 跑测试验证通过**

Run: `php artisan test tests/Feature/FrontPackagesPageTest.php`
Expected: PASS

- [ ] **Step 6: 提交该阶段**

```bash
git add app/Http/Controllers/FrontPageController.php app/Models/TPackage.php resources/views/pages/packages.blade.php tests/Feature/FrontPackagesPageTest.php
git commit -m "feat(frontend): render packages from database"
```

### Task 4: 添加产品页月付年付切换与支付弹窗前端结构

**Files:**
- Modify: `resources/views/pages/packages.blade.php`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/FrontPackagesPageTest.php`

- [ ] **Step 1: 写失败测试，断言产品页包含弹窗容器和周期切换标识**

```php
$response->assertSee('月度付费', false)
    ->assertSee('年度付费', false)
    ->assertSee('payment-modal', false);
```

- [ ] **Step 2: 运行测试，确认产品页缺少这些结构**

Run: `php artisan test tests/Feature/FrontPackagesPageTest.php`
Expected: FAIL

- [ ] **Step 3: 写最小实现**

在 `packages.blade.php` 中增加：

```blade
<div id="payment-modal" class="hidden">...</div>
```

并在卡片上输出数据属性：

```blade
<button
    data-package-id="{{ $package['id'] }}"
    data-package-name="{{ $package['name'] }}"
    data-month-price="{{ $package['price'] }}"
    data-year-price="{{ $package['year_price'] }}"
    class="buy-button"
>
    立即购买
</button>
```

- [ ] **Step 4: 在 `resources/js/app.js` 中增加最小交互脚本**

实现：
- 周期切换状态管理
- 打开/关闭弹窗
- 从按钮读取套餐信息并填充弹窗

- [ ] **Step 5: 跑测试确认页面结构可见**

Run: `php artisan test tests/Feature/FrontPackagesPageTest.php`
Expected: PASS

- [ ] **Step 6: 提交该阶段**

```bash
git add resources/views/pages/packages.blade.php resources/js/app.js tests/Feature/FrontPackagesPageTest.php
git commit -m "feat(frontend): add package billing toggle and payment modal shell"
```

### Task 5: 新增订单状态查询接口

**Files:**
- Create: `app/Http/Controllers/OrderStatusController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/OrderStatusTest.php`

- [ ] **Step 1: 写失败测试，定义订单状态查询接口契约**

```php
public function test_it_returns_order_payment_status(): void
{
    $order = TOrder::query()->create([
        'order_no' => '20260404123456ABCDEFGH',
        'package_id' => '1',
        'package_name' => '专业版',
        'package_code' => 'pro',
        'billing_cycle' => 'month',
        'amount' => 1999,
        'pay_amount' => 1999,
        'pay_type' => 'wechat',
        'pay_status' => 1,
        'duration' => 1,
    ]);

    $response = $this->getJson("/orders/{$order->order_no}/status");

    $response->assertOk()->assertJsonPath('data.pay_status', 1);
}
```

- [ ] **Step 2: 运行测试，确认因路由或控制器缺失而失败**

Run: `php artisan test tests/Feature/OrderStatusTest.php`
Expected: FAIL

- [ ] **Step 3: 写最小实现**

```php
class OrderStatusController extends Controller
{
    public function show(string $orderNo): JsonResponse
    {
        $order = TOrder::query()->where('order_no', $orderNo)->firstOrFail();

        return response()->json([
            'data' => [
                'order_no' => $order->order_no,
                'pay_status' => $order->pay_status,
                'paid_at' => $order->paid_at,
                'transaction_id' => $order->transaction_id,
            ],
        ]);
    }
}
```

- [ ] **Step 4: 跑测试验证通过**

Run: `php artisan test tests/Feature/OrderStatusTest.php`
Expected: PASS

- [ ] **Step 5: 提交该阶段**

```bash
git add app/Http/Controllers/OrderStatusController.php routes/web.php tests/Feature/OrderStatusTest.php
git commit -m "feat(order): add order status endpoint"
```

### Task 6: 打通产品页购买与状态刷新脚本

**Files:**
- Modify: `resources/views/pages/packages.blade.php`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/FrontPackagesPageTest.php`

- [ ] **Step 1: 写失败测试，断言页面输出下单与状态查询接口地址**

```php
$response->assertSee('/order/create', false)
    ->assertSee('/orders/', false);
```

- [ ] **Step 2: 运行测试，确认这些前端入口尚未输出**

Run: `php artisan test tests/Feature/FrontPackagesPageTest.php`
Expected: FAIL

- [ ] **Step 3: 写最小实现**

在产品页输出脚本配置：

```blade
<script>
    window.packagePageConfig = {
        createOrderUrl: '{{ url('/order/create') }}',
        orderStatusUrlTemplate: '{{ url('/orders/__ORDER_NO__/status') }}',
    };
</script>
```

在 `resources/js/app.js` 中实现：
- 点击购买时 `fetch('/order/create')`
- 成功后用返回的 `code_url` 生成二维码
- 点击“我已支付，刷新状态”时请求订单状态
- 状态为 `1` 时显示成功态

- [ ] **Step 4: 为二维码接入最小依赖方式**

优先方案：
- 在产品页中通过 `<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>` 引入轻量二维码库

备用方案：
- 若不使用 CDN，则引入一个本地轻量二维码实现文件并在 Vite 中加载

- [ ] **Step 5: 跑测试确认页面已输出前端交互入口**

Run: `php artisan test tests/Feature/FrontPackagesPageTest.php`
Expected: PASS

- [ ] **Step 6: 提交该阶段**

```bash
git add resources/views/pages/packages.blade.php resources/js/app.js tests/Feature/FrontPackagesPageTest.php
git commit -m "feat(frontend): wire package purchase modal to order APIs"
```

### Task 7: 全量验证与构建检查

**Files:**
- Verify: `tests/Feature/FrontPageRoutesTest.php`
- Verify: `tests/Feature/FrontPackagesPageTest.php`
- Verify: `tests/Feature/OrderStatusTest.php`
- Verify: `resources/views/pages/home.blade.php`
- Verify: `resources/views/pages/packages.blade.php`

- [ ] **Step 1: 跑前台新增测试**

Run: `php artisan test tests/Feature/FrontPageRoutesTest.php tests/Feature/FrontPackagesPageTest.php tests/Feature/OrderStatusTest.php`
Expected: PASS

- [ ] **Step 2: 跑全量测试**

Run: `php artisan test`
Expected: PASS

- [ ] **Step 3: 跑前端构建**

Run: `npm run build`
Expected: exit 0，无 Vite 构建错误

- [ ] **Step 4: 检查关键路由**

Run: `php artisan route:list | rg "/$|packages|order/create|orders/.*/status|wechat/pay/notify"`
Expected: 输出首页、产品页、下单、订单状态、微信回调路由

- [ ] **Step 5: 手动验证页面**

检查：
- `/` 页面可正常显示首页
- `/packages` 页面能显示数据库套餐
- 月付/年付切换价格正确
- 点击“立即购买”弹窗出现
- 弹窗内二维码、订单号、金额展示正常

- [ ] **Step 6: 提交该阶段**

```bash
git add app/Http/Controllers/FrontPageController.php app/Http/Controllers/OrderStatusController.php app/Models/TPackage.php routes/web.php resources/views/layouts/frontend.blade.php resources/views/partials/frontend-header.blade.php resources/views/partials/frontend-footer.blade.php resources/views/pages/home.blade.php resources/views/pages/packages.blade.php resources/js/app.js resources/css/app.css tests/Feature/FrontPageRoutesTest.php tests/Feature/FrontPackagesPageTest.php tests/Feature/OrderStatusTest.php
git commit -m "feat(frontend): add public home and package purchase pages"
```
