<style>
    .ucp-btn {
        border:1px solid #e2e8f0;
        background:#f8fafc;
        color:#475569;
    }
    .ucp-btn:hover { background:#eef2ff; color:#1f2937; }
    html.dark .ucp-btn {
        border-color: rgba(255,255,255,.2);
        background: rgba(255,255,255,.1);
        color: #f0abfc;
    }
    html.dark .ucp-btn:hover { background: rgba(255,255,255,.2); color:#fff; }
</style>

<div x-data="userControlPanel()" x-init="init()" class="flex items-center gap-2">
    <button
        @click="toggleTheme()"
        :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
        class="ucp-btn w-8 h-8 rounded-lg flex items-center justify-center transition"
    >
        <i :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'" class="text-xs"></i>
    </button>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button
            type="submit"
            class="ucp-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition"
            title="Cerrar sesión"
        >
            <i class="fa-solid fa-right-from-bracket text-[10px]"></i>
            Salir
        </button>
    </form>
</div>

<script>
if (!window.userControlPanel) {
    window.userControlPanel = function () {
        return {
            isDark: false,
            init() {
                this.isDark = localStorage.getItem('sp-dark-mode') === 'true';
                this.applyTheme();
            },
            toggleTheme() {
                this.isDark = !this.isDark;
                localStorage.setItem('sp-dark-mode', this.isDark ? 'true' : 'false');
                this.applyTheme();
            },
            applyTheme() {
                document.documentElement.classList.toggle('dark', this.isDark);
            },
        };
    };

    // Early apply for any page where this component gets rendered after load
    if (localStorage.getItem('sp-dark-mode') === 'true') {
        document.documentElement.classList.add('dark');
    }
}
</script>
