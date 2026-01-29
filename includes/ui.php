<?php
require_once __DIR__ . '/auth.php';

function ui_title(string $title): string {
    return htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
}

function ui_active(string $current, string $item): string {
    return $current === $item ? 'bg-white text-black shadow-sm' : 'text-white hover:bg-white/10';
}

function ui_layout_start(string $title, string $active = ''): void {
    $role = current_role();
    $username = $_SESSION['user']['username'] ?? '';

    // Build role-based nav
    $nav = [];
    if ($role === 'viewer') {
        $nav = [
            ['key'=>'home','label'=>'Home','href'=>'home.php','icon'=>'home'],
            ['key'=>'create','label'=>'Create Request','href'=>'create_request.php','icon'=>'plus'],
            ['key'=>'requests','label'=>'My Requests','href'=>'requests.php','icon'=>'list'],
            ['key'=>'profile','label'=>'Profile','href'=>'profile.php','icon'=>'user'],
        ];
    } elseif ($role === 'admin') {
        $nav = [
            ['key'=>'home','label'=>'Home','href'=>'home.php','icon'=>'home'],
            ['key'=>'dashboard','label'=>'Dashboard','href'=>'dashboard.php','icon'=>'grid'],
            ['key'=>'create','label'=>'Create Request','href'=>'create_request.php','icon'=>'plus'],
            ['key'=>'requests','label'=>'All Requests','href'=>'requests.php','icon'=>'list'],
            ['key'=>'suppliers','label'=>'Suppliers','href'=>'suppliers.php','icon'=>'box'],
            ['key'=>'statistics','label'=>'Statistics','href'=>'statistics.php','icon'=>'chart'],
            ['key'=>'profile','label'=>'Profile','href'=>'profile.php','icon'=>'user'],
        ];
    } elseif ($role === 'super_admin') {
        $nav = [
            ['key'=>'home','label'=>'Home','href'=>'home.php','icon'=>'home'],
            ['key'=>'dashboard','label'=>'Dashboard','href'=>'dashboard.php','icon'=>'grid'],
            ['key'=>'create','label'=>'Create Request','href'=>'create_request.php','icon'=>'plus'],
            ['key'=>'requests','label'=>'All Requests','href'=>'requests.php','icon'=>'list'],
            ['key'=>'suppliers','label'=>'Suppliers','href'=>'suppliers.php','icon'=>'box'],
            ['key'=>'statistics','label'=>'Statistics','href'=>'statistics.php','icon'=>'chart'],
            ['key'=>'users','label'=>'User Accounts','href'=>'users.php','icon'=>'users'],
            ['key'=>'profile','label'=>'Profile','href'=>'profile.php','icon'=>'user'],
        ];
    } else {
        $nav = [];
    }

    $icons = [
        'home' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M3 10.5L12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
        'grid' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z" stroke="currentColor" stroke-width="2" /></svg>',
        'list' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M7 4h10v16H7V4Z" stroke="currentColor" stroke-width="2"/><path d="M9 8h6M9 12h6M9 16h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
        'plus' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
        'box' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M3 7l9-4 9 4v10l-9 4-9-4V7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M12 3v18" stroke="currentColor" stroke-width="2" opacity=".6"/></svg>',
        'chart' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M4 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M4 19h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M8 15V10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 15V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M16 15V12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
        'user' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2"/><path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
        'users' => '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2"/><path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2"/><path d="M2 21a6 6 0 0 1 12 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M14 21a5 5 0 0 1 8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    ];

  $flash = function_exists('flash_get') ? flash_get() : null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo ui_title($title); ?></title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
          colors: { accentYellow: '#FACC15', bgGrey: '#F4F7FE', ink: '#0B0B0C' },
          borderRadius: { card: '24px' },
          boxShadow: { soft: '0 10px 30px rgba(17, 24, 39, 0.08)' }
        }
      }
    }
  </script>
</head>
<body class="font-sans bg-bgGrey">
  <div class="min-h-screen lg:flex">
    <!-- Mobile top bar -->
    <div class="lg:hidden sticky top-0 z-30 bg-white border-b border-slate-100">
      <div class="px-4 py-3 flex items-center justify-between">
        <button type="button" id="rliSidebarOpen" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-bgGrey hover:bg-slate-200">
          <svg class="w-6 h-6 text-slate-800" viewBox="0 0 24 24" fill="none">
            <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
        <div class="font-bold text-slate-900">RLHI</div>
        <a href="logout.php" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-red-600 text-white text-xs font-semibold shadow-sm hover:bg-red-700">
          <span>Logout</span>
        </a>
      </div>
    </div>

    <!-- Mobile overlay -->
    <div id="rliSidebarOverlay" class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden"></div>

    <!-- Sidebar (desktop + mobile off-canvas) -->
    <aside id="rliSidebar"
      class="fixed lg:static inset-y-0 left-0 z-50 w-[280px] bg-ink text-white px-6 py-8 flex flex-col
             transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-out">
      <div class="mb-6 flex items-start justify-between gap-3">
        <div>
          <div class="text-2xl font-bold tracking-tight">RLHI</div>
          <div class="text-white/70 text-sm mt-1">Material Request System</div>
        </div>
        <button type="button" id="rliSidebarClose" class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20">
          <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none">
            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
      </div>

      <!-- User card (top, above nav) -->
      <div class="mb-6">
        <div class="rounded-2xl bg-ink border border-white px-4 py-3 flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-accentYellow">
            <svg class="h-5 w-5 text-ink" viewBox="0 0 24 24" fill="none">
              <path d="M12 12a3 3 0 100-6 3 3 0 000 6zM6 20a6 6 0 1112 0H6z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div>
            <div class="text-sm font-semibold text-accentYellow">
              <?php echo htmlspecialchars($username); ?>
            </div>
            <div class="text-xs uppercase tracking-wide text-accentYellow font-semibold">
              <?php echo htmlspecialchars($role ?? ''); ?>
            </div>
          </div>
        </div>
      </div>

      <nav class="space-y-2">
        <?php foreach ($nav as $item): ?>
          <a href="<?php echo htmlspecialchars($item['href']); ?>"
             class="flex items-center gap-3 px-4 py-3 rounded-full transition <?php echo ui_active($active, $item['key']); ?>">
            <?php echo $icons[$item['icon']] ?? ''; ?>
            <span class="font-semibold"><?php echo htmlspecialchars($item['label']); ?></span>
          </a>
        <?php endforeach; ?>
      </nav>

      <div class="mt-auto pt-8">
        <a href="logout.php"
           class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
          Logout
        </a>
      </div>
    </aside>

    <main class="flex-1 p-4 sm:p-6 lg:p-8">
      <div class="bg-white rounded-card shadow-soft p-5 sm:p-7">
        <?php if ($flash && !empty($flash['message'])): ?>
          <?php
            $t = $flash['type'] ?? 'info';
            $cls = 'bg-slate-100 text-slate-800 border-slate-200';
            if ($t === 'success') $cls = 'bg-green-50 text-green-800 border-green-200';
            if ($t === 'error') $cls = 'bg-red-50 text-red-800 border-red-200';
            if ($t === 'warning') $cls = 'bg-yellow-50 text-yellow-900 border-yellow-200';
          ?>
          <div class="mb-5 rounded-2xl border px-4 py-3 text-sm font-semibold <?php echo $cls; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
          </div>
        <?php endif; ?>
<?php
}

function ui_layout_end(): void {
    ?>
      </div>
    </main>
  </div>

  <script>
  (function () {
    const sidebar = document.getElementById('rliSidebar');
    const overlay = document.getElementById('rliSidebarOverlay');
    const openBtn = document.getElementById('rliSidebarOpen');
    const closeBtn = document.getElementById('rliSidebarClose');

    if (!sidebar || !overlay || !openBtn || !closeBtn) return;

    function open() {
      sidebar.classList.remove('-translate-x-full');
      overlay.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }
    function close() {
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
      document.body.style.overflow = '';
    }

    openBtn.addEventListener('click', open);
    closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', close);
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') close();
    });
  })();
  </script>
</body>
</html>
<?php
}

