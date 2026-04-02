<nav class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-slate-950/55 px-6 backdrop-blur-xl md:px-8">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between">
        <a class="font-headline text-xl font-extrabold tracking-tight text-white uppercase" href="{{ url('/') }}">
            Neural Nexus <span class="text-cyan-300">星云算力</span>
        </a>

        <div class="hidden items-center gap-8 text-sm md:flex">
            <a class="{{ request()->url() === url('/') ? 'text-cyan-300' : 'text-slate-400 hover:text-white' }} transition-colors" href="{{ url('/') }}">首页</a>
            <a class="{{ request()->is('packages') ? 'text-cyan-300' : 'text-slate-400 hover:text-white' }} transition-colors" href="{{ url('/packages') }}">产品</a>
            <a class="text-slate-400 transition-colors hover:text-white" href="#faq">常见问题</a>
            <a class="text-slate-400 transition-colors hover:text-white" href="#channels">接入渠道</a>
        </div>

        <div class="flex items-center gap-3">
            <a class="rounded-full bg-cyan-300 px-5 py-2 text-xs font-bold uppercase tracking-[0.24em] text-slate-950 transition-transform hover:scale-[1.02]" href="{{ url('/packages') }}">
                立即购买
            </a>
            <div class="hidden h-9 w-9 overflow-hidden rounded-full border border-white/10 bg-slate-800 md:block">
                <img alt="用户头像" class="h-full w-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCOYnkYEFSMD9967VYrq1xKZCRcAamBPB_qqtgeJkkVFD18ljCpi2GzfOxdPvlLKL87j_SJN_fgxHZyou3Df7HOnO9Zlk_kavbuFDjJF7gZscNAbgDAerxt19fvzPVc-Qv_TySIEE09NAr4r4qXCrJvJZpwuy9vXb43bSW_jgVWyO1LSHqelx5zVtNiRxdHXZ6VwJTHSq3-EJDYkgitIgmlgvimiQIVWY3lb_zNiQyedel77SRGAkkMFjciPDjoAVm4AelpxW_rOFY">
            </div>
        </div>
    </div>
</nav>
