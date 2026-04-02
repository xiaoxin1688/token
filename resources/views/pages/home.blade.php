@extends('layouts.frontend')

@section('title', 'Neural Nexus - 星云算力')

@section('content')
    <section class="relative overflow-hidden px-6 pt-32 pb-24 md:px-8 md:pt-40">
        <div class="mx-auto flex max-w-7xl flex-col items-center gap-16 lg:flex-row">
            <div class="flex-1 text-center lg:text-left">
                <div class="font-label inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.28em] text-cyan-200">
                    <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                    下一代神经算力
                </div>
                <h1 class="font-headline mt-7 text-5xl font-extrabold leading-none tracking-tight text-white md:text-7xl">
                    星云算力
                    <br>
                    <span class="bg-gradient-to-r from-cyan-200 via-cyan-300 to-violet-300 bg-clip-text text-transparent">您的数字引擎</span>
                </h1>
                <p class="mt-8 max-w-2xl text-lg leading-8 text-slate-300 md:text-xl">
                    为 AI 开发者与企业级算力需求而生。极速响应、多模型支持、稳定计费，
                    提供适合生产环境的高并发算力接口服务。
                </p>
                <div class="mt-10 flex flex-wrap justify-center gap-4 lg:justify-start">
                    <a class="rounded-2xl bg-cyan-300 px-8 py-4 font-headline text-sm font-bold tracking-[0.14em] text-slate-950 shadow-[0_0_40px_rgba(161,250,255,0.25)] transition hover:brightness-110" href="{{ url('/packages') }}">
                        立即购买
                    </a>
                    <a class="rounded-2xl border border-white/10 bg-white/5 px-8 py-4 font-headline text-sm font-bold tracking-[0.14em] text-white transition hover:bg-white/10" href="#channels">
                        查看文档
                    </a>
                </div>
            </div>

            <div class="w-full max-w-2xl flex-1">
                <div class="glass-panel overflow-hidden rounded-3xl border border-white/10 bg-slate-950/75 shadow-2xl">
                    <div class="flex items-center gap-2 border-b border-white/5 bg-slate-900/70 px-5 py-4">
                        <div class="flex gap-2">
                            <span class="h-3 w-3 rounded-full bg-red-400/70"></span>
                            <span class="h-3 w-3 rounded-full bg-amber-400/70"></span>
                            <span class="h-3 w-3 rounded-full bg-emerald-400/70"></span>
                        </div>
                        <div class="font-label mx-auto text-[11px] font-bold uppercase tracking-[0.28em] text-slate-500">nexus-api-client</div>
                    </div>
                    <div class="space-y-3 px-6 py-6 font-label text-sm leading-7 text-slate-300">
                        <div><span class="mr-4 text-slate-600">01</span><span class="text-violet-300">import</span> neural_nexus</div>
                        <div><span class="mr-4 text-slate-600">02</span>client = neural_nexus.Client(api_key=<span class="text-emerald-300">"NX-9921-X"</span>)</div>
                        <div><span class="mr-4 text-slate-600">03</span></div>
                        <div><span class="mr-4 text-slate-600">04</span><span class="text-slate-500"># 请求全球最强模型</span></div>
                        <div><span class="mr-4 text-slate-600">05</span>response = client.chat.completions.create(</div>
                        <div><span class="mr-4 text-slate-600">06</span><span class="pl-5 text-cyan-300">model</span>=<span class="text-emerald-300">"claude-3-5-sonnet"</span>,</div>
                        <div><span class="mr-4 text-slate-600">07</span><span class="pl-5 text-cyan-300">messages</span>=[{"role": "user", "content": "Hello Nexus!"}]</div>
                        <div><span class="mr-4 text-slate-600">08</span>)</div>
                        <div><span class="mr-4 text-slate-600">09</span>print(response.choices[0].message)</div>
                        <div class="mt-5 border-t border-white/5 pt-4">
                            <span class="mr-4 text-slate-600">$</span>
                            <span class="font-bold text-emerald-300">输出: "已连接至星云算力集群-04"</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="px-6 py-20 md:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="text-center">
                <h2 class="font-headline text-3xl font-bold text-white md:text-4xl">为什么选择星云算力</h2>
                <div class="mx-auto mt-5 h-1 w-24 rounded-full bg-cyan-300"></div>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ([
                    ['icon' => 'payments', 'title' => '实惠价格', 'desc' => '提供竞争力定价策略，按量付费，降低开发者接入和试错成本。'],
                    ['icon' => 'verified_user', 'title' => '稳定可靠', 'desc' => '多节点容灾架构，保障企业级接口在高峰流量下稳定可用。'],
                    ['icon' => 'bolt', 'title' => '即时激活', 'desc' => '自动部署与额度下发，支付完成即可开启接口权限。'],
                    ['icon' => 'hub', 'title' => '多模型支持', 'desc' => '统一入口接入 GPT、Claude、文生图等主流模型。'],
                    ['icon' => 'speed', 'title' => '低延迟响应', 'desc' => '全球线路优化，显著降低模型调用的平均等待时间。'],
                    ['icon' => 'support_agent', 'title' => '技术支持', 'desc' => '7x24 小时工程师支持，协助处理复杂集成场景。'],
                ] as $feature)
                    <article class="rounded-3xl border border-white/5 bg-slate-900/60 p-8 transition hover:border-cyan-300/40 hover:bg-slate-900/90">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-300/10 text-cyan-300">
                            <span class="material-symbols-outlined">{{ $feature['icon'] }}</span>
                        </div>
                        <h3 class="font-headline mt-6 text-xl font-bold text-white">{{ $feature['title'] }}</h3>
                        <p class="mt-3 leading-7 text-slate-400">{{ $feature['desc'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-slate-950/40 px-6 py-20 md:px-8" id="channels">
        <div class="mx-auto max-w-7xl">
            <div class="mb-10 flex items-center gap-4">
                <span class="h-px w-12 bg-cyan-300/40"></span>
                <h2 class="font-headline text-2xl font-bold uppercase tracking-[0.2em] text-white">模型接入渠道</h2>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['tag' => 'AWS BEDROCK', 'models' => [['Claude 3.5 Sonnet', 'anthropic.claude-v3.5'], ['Llama 3 70B', 'meta.llama3-70b'], ['Stability AI SDXL', 'stability.stable-diff']], 'latency' => '120ms', 'tagClass' => 'bg-violet-400/15 text-violet-200'],
                    ['tag' => 'ANTHROPIC DIRECT', 'models' => [['Claude 3 Opus', 'claude-3-opus'], ['Claude 3.5 Sonnet', 'claude-3-5-sonnet'], ['Claude 3 Haiku', 'claude-3-haiku']], 'latency' => '85ms', 'tagClass' => 'bg-cyan-300/15 text-cyan-200'],
                    ['tag' => 'OPENAI ENTERPRISE', 'models' => [['GPT-4o', 'gpt-4o-latest'], ['GPT-4 Turbo', 'gpt-4-turbo'], ['DALL-E 3', 'dall-e-3']], 'latency' => '145ms', 'tagClass' => 'bg-emerald-300/15 text-emerald-200'],
                ] as $channel)
                    <article class="overflow-hidden rounded-3xl border border-white/5 bg-slate-900/70">
                        <div class="p-6">
                            <div class="mb-6 flex items-start justify-between">
                                <span class="{{ $channel['tagClass'] }} rounded-full px-3 py-1 font-label text-[10px] font-bold tracking-[0.22em]">{{ $channel['tag'] }}</span>
                                <span class="material-symbols-outlined text-emerald-300" style="font-variation-settings: 'FILL' 1">check_circle</span>
                            </div>
                            <div class="space-y-4">
                                @foreach ($channel['models'] as [$label, $code])
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-500">{{ $label }}</span>
                                        <span class="font-label text-slate-200">{{ $code }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex items-center justify-between bg-white/5 px-6 py-4">
                            <span class="font-label text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Latency: {{ $channel['latency'] }}</span>
                            <a class="text-[10px] font-bold uppercase tracking-[0.2em] text-cyan-300" href="{{ url('/packages') }}">Deploy Instance</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="px-6 py-20 md:px-8" id="faq">
        <div class="mx-auto max-w-4xl">
            <div class="text-center">
                <h2 class="font-headline text-3xl font-bold text-white">常见问题</h2>
                <p class="mt-3 text-slate-400">如果您有更多疑问，请联系我们的客服团队</p>
            </div>

            <div class="mt-10 space-y-4">
                @foreach ([
                    ['title' => '算力资源是如何计费的？', 'answer' => '我们采用按套餐与按量结合的方式，您可以在后台和支付弹窗中查看当前金额与支付状态。'],
                    ['title' => '接口响应速度能保证吗？', 'answer' => '全球部署多个加速节点，通过智能路由选择最优链路，平均响应延迟显著低于传统直连。'],
                    ['title' => '是否支持高并发调用？', 'answer' => '支持。后端架构支持动态水平扩展，可以根据业务需求平滑扩容。'],
                ] as $faq)
                    <details class="group overflow-hidden rounded-3xl border border-white/5 bg-slate-900/60 open:ring-1 open:ring-cyan-300/40">
                        <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-5">
                            <span class="font-headline font-semibold text-white">{{ $faq['title'] }}</span>
                            <span class="material-symbols-outlined text-cyan-300 transition-transform group-open:rotate-180">expand_more</span>
                        </summary>
                        <div class="px-6 pb-6 leading-7 text-slate-400">
                            {{ $faq['answer'] }}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <section class="px-6 pb-24 md:px-8">
        <div class="mx-auto max-w-7xl rounded-[2rem] border border-white/10 bg-gradient-to-r from-cyan-300/10 to-violet-300/10 p-12 text-center glass-panel md:p-20">
            <h2 class="font-headline text-4xl font-black text-white md:text-5xl">开启您的星云算力之旅</h2>
            <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                现在加入，体验毫秒级响应的顶级 AI 模型算力，并通过统一支付与套餐中心快速上线业务。
            </p>
            <div class="mt-10 flex flex-col justify-center gap-4 sm:flex-row">
                <a class="rounded-2xl bg-cyan-300 px-10 py-4 font-headline text-lg font-bold text-slate-950 transition hover:brightness-110" href="{{ url('/packages') }}">免费开始使用</a>
                <a class="rounded-2xl border border-white/15 bg-white/5 px-10 py-4 font-headline text-lg font-bold text-white transition hover:bg-white/10" href="#faq">联系商务洽谈</a>
            </div>
        </div>
    </section>
@endsection
