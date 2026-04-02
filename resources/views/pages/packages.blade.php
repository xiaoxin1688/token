@extends('layouts.frontend')

@section('title', 'Neural Nexus | 星云算力 套餐页面')

@section('content')
    <section class="px-6 pt-32 pb-24 md:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="text-center">
                <h1 class="font-headline text-5xl font-extrabold tracking-tight text-white md:text-6xl">
                    算力套餐 <span class="text-cyan-300">Packages</span>
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-slate-400">
                    选择适合您业务需求的神经网络算力配置，即刻开启高性能计算旅程。
                </p>
                <div class="mt-10 inline-flex rounded-full border border-white/10 bg-slate-900/70 p-1">
                    <button class="billing-toggle rounded-full bg-cyan-300 px-8 py-2 text-sm font-bold text-slate-950 shadow-[0_0_15px_rgba(161,250,255,0.3)]" data-cycle="month" type="button">
                        月度付费
                    </button>
                    <button class="billing-toggle rounded-full px-8 py-2 text-sm font-bold text-slate-400 transition hover:text-white" data-cycle="year" type="button">
                        年度付费 <span class="ml-1 text-emerald-300">-20%</span>
                    </button>
                </div>
            </div>

            <div class="mt-16 grid gap-8 lg:grid-cols-3">
                @forelse ($packages as $package)
                    <article class="package-card relative flex h-full flex-col rounded-[1.75rem] border p-8 transition {{ $package['is_featured'] ? 'border-cyan-300/50 bg-slate-900/85 shadow-[0_0_40px_rgba(161,250,255,0.18)] lg:-translate-y-2' : 'border-white/8 bg-slate-900/65 hover:border-cyan-300/30 hover:-translate-y-1' }}">
                        @if ($package['is_featured'])
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-cyan-300 px-5 py-1.5 font-label text-[10px] font-black uppercase tracking-[0.28em] text-slate-950">
                                Popular / 推荐
                            </div>
                        @endif

                        <div class="w-fit rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-[0.28em] {{ $package['is_featured'] ? 'border-cyan-300/40 bg-cyan-300/10 text-cyan-200' : 'border-white/10 bg-slate-800 text-slate-400' }}">
                            {{ strtoupper($package['code']) }} / {{ $package['name'] }}
                        </div>

                        <div class="mt-8">
                            <div class="flex items-baseline gap-1 text-white">
                                <span class="font-headline text-4xl font-bold">¥</span>
                                <span class="package-price font-headline text-6xl font-extrabold" data-month="{{ number_format((float) $package['price'], 2, '.', '') }}" data-year="{{ number_format((float) $package['year_price'], 2, '.', '') }}">
                                    {{ number_format((float) $package['price'], 2, '.', '') }}
                                </span>
                                <span class="package-cycle-label font-label text-sm text-slate-400">/月</span>
                            </div>
                            @if ($package['trial_days'] > 0)
                                <p class="mt-3 text-sm text-emerald-300">支持 {{ $package['trial_days'] }} 天试用期体验</p>
                            @endif
                        </div>

                        <div class="mt-8 flex-grow space-y-4">
                            @foreach ($package['features'] as $feature)
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined mt-0.5 text-cyan-300" style="font-variation-settings: 'FILL' 1">check_circle</span>
                                    <span class="font-label text-sm uppercase tracking-[0.12em] text-slate-100">{{ $feature }}</span>
                                </div>
                            @endforeach
                        </div>

                        <button
                            class="buy-button mt-10 w-full rounded-2xl py-4 font-bold uppercase tracking-[0.26em] transition {{ $package['is_featured'] ? 'bg-cyan-300 text-slate-950 hover:brightness-110' : 'border border-white/10 bg-slate-800 text-white hover:bg-slate-700' }}"
                            data-package-id="{{ $package['id'] }}"
                            data-package-name="{{ $package['name'] }}"
                            data-package-code="{{ $package['code'] }}"
                            data-month-price="{{ number_format((float) $package['price'], 2, '.', '') }}"
                            data-year-price="{{ number_format((float) $package['year_price'], 2, '.', '') }}"
                            type="button"
                        >
                            立即购买
                        </button>
                    </article>
                @empty
                    <div class="rounded-[1.75rem] border border-white/8 bg-slate-900/65 p-10 text-center text-slate-400 lg:col-span-3">
                        当前没有可购买套餐，请先在后台配置 `t_packages` 数据。
                    </div>
                @endforelse
            </div>

            <section class="relative mt-24 overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900/65 p-12">
                <div class="absolute inset-y-0 right-0 hidden w-1/3 bg-gradient-to-l from-cyan-300/10 to-transparent lg:block"></div>
                <div class="relative z-10 max-w-2xl">
                    <h3 class="font-headline text-3xl font-bold text-white">需要定制化算力解决方案？</h3>
                    <p class="mt-5 text-lg leading-8 text-slate-400">
                        我们的专家团队将为您量身定制算力集群配置，满足从 LLM 训练到科学计算的各种极端需求。
                        支持大规模集群动态伸缩及私有化部署。
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a class="rounded-2xl bg-emerald-300 px-8 py-3 font-bold uppercase tracking-[0.18em] text-slate-950 transition hover:brightness-110" href="#">咨询专家</a>
                        <a class="rounded-2xl border border-white/15 px-8 py-3 font-bold uppercase tracking-[0.18em] text-white transition hover:bg-white/5" href="#">下载技术规格</a>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <div class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/85 px-6" id="payment-modal">
        <div class="glass-panel w-full max-w-xl rounded-[2rem] border border-cyan-300/15 bg-slate-950/95 p-8 shadow-2xl">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <p class="font-label text-[11px] font-bold uppercase tracking-[0.32em] text-cyan-300">微信 Native 支付</p>
                    <h3 class="font-headline mt-3 text-3xl font-bold text-white" id="payment-package-name">套餐支付</h3>
                    <p class="mt-2 text-sm text-slate-400" id="payment-order-summary">请使用微信扫码完成支付。</p>
                </div>
                <button class="rounded-full border border-white/10 p-2 text-slate-400 transition hover:text-white" id="payment-modal-close" type="button">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="mt-8 grid gap-8 md:grid-cols-[1.2fr_0.8fr]">
                <div class="space-y-4">
                    <div class="rounded-2xl border border-white/5 bg-slate-900/60 p-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">套餐</span>
                            <span class="text-white" id="payment-package-value">-</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <span class="text-slate-500">周期</span>
                            <span class="text-white" id="payment-cycle-value">-</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <span class="text-slate-500">金额</span>
                            <span class="font-headline text-xl font-bold text-cyan-300" id="payment-amount-value">¥0.00</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <span class="text-slate-500">订单号</span>
                            <span class="max-w-[16rem] truncate text-white" id="payment-order-no">-</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/5 bg-slate-900/60 p-5">
                        <p class="text-sm text-slate-400" id="payment-status-text">二维码已生成，请使用微信扫描支付。</p>
                        <p class="mt-2 hidden text-sm text-emerald-300" id="payment-success-text">支付成功，订单已完成。</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button class="rounded-2xl bg-cyan-300 px-5 py-3 text-sm font-bold uppercase tracking-[0.2em] text-slate-950 transition hover:brightness-110" id="payment-refresh-status" type="button">
                            我已支付，刷新状态
                        </button>
                        <button class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-bold uppercase tracking-[0.2em] text-white transition hover:bg-white/5" id="payment-close-secondary" type="button">
                            关闭
                        </button>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center rounded-[1.75rem] border border-white/5 bg-slate-900/70 p-6 text-center">
                    <div class="flex min-h-[228px] w-full items-center justify-center rounded-[1.5rem] bg-white p-4" id="payment-qrcode"></div>
                    <p class="mt-4 text-xs leading-6 text-slate-500">如二维码未显示，请稍后重试或检查微信支付配置是否完整。</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.packagePageConfig = {
            createOrderUrl: '{{ url('/order/create') }}',
            orderStatusUrlTemplate: '{{ url('/orders/__ORDER_NO__/status') }}',
        };
    </script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
@endpush
