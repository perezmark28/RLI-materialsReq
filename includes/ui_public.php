<?php
function ui_public_head(string $title): void {
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
          colors: { accentYellow: '#CCB38A', bgGrey: '#F4F7FE', ink: '#0B0B0C' },
          borderRadius: { card: '24px' },
          boxShadow: { soft: '0 10px 30px rgba(17, 24, 39, 0.08)' }
        }
      }
    }
  </script>
  <style>
    :root {
      --rli-hover-shadow: 0 15px 35px rgba(15, 23, 42, 0.12);
    }

    button:not([disabled]),
    [type="button"]:not([disabled]),
    [type="submit"]:not([disabled]),
    [role="button"],
    .btn {
      transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, color .18s ease, border-color .18s ease;
    }

    button:not([disabled]):hover,
    [type="button"]:not([disabled]):hover,
    [type="submit"]:not([disabled]):hover,
    [role="button"]:hover,
    .btn:hover {
      background-color: #CCB38A;
      color: #000;
      border-color: #CCB38A;
      transform: translateY(-2px);
      box-shadow: var(--rli-hover-shadow);
    }

    button[class*="bg-red-"]:hover,
    button[class*="bg-green-"]:hover,
    [type="button"][class*="bg-red-"]:hover,
    [type="button"][class*="bg-green-"]:hover,
    a[class*="bg-red-"]:hover,
    a[class*="bg-green-"]:hover {
      background-color: #000;
      color: #fff;
      border-color: #000;
    }

    a {
      transition: color .18s ease, opacity .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    a:hover {
      opacity: .95;
      color: #000;
      background-color: #CCB38A;
      border-color: #CCB38A;
      box-shadow: var(--rli-hover-shadow);
      transform: translateY(-1px);
    }

    table tbody tr {
      transition: background-color .18s ease, transform .18s ease, box-shadow .18s ease;
    }

    table tbody tr:hover {
      background-color: #FFF3C4;
      transform: translateY(-1px);
      box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
    }

    .card-clickable,
    .hover-card,
    .rounded-2xl.cursor-pointer {
      transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background-color .2s ease;
    }

    .card-clickable:hover,
    .hover-card:hover,
    .rounded-2xl.cursor-pointer:hover {
      transform: translateY(-3px);
      box-shadow: 0 25px 45px rgba(15, 23, 42, 0.15);
      border-color: #CCB38A;
      background-color: #FFF8DA;
    }
  </style>
</head>
<body class="font-sans bg-bgGrey">
<?php
}

function ui_public_shell_start(): void {
?>
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-[980px] grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
      <!-- Brand panel -->
      <div class="hidden lg:flex flex-col justify-between rounded-card bg-ink text-white p-8 shadow-soft">
        <div>
          <div class="inline-flex items-center gap-3">
            <div class="h-12 w-12 rounded-full bg-[#CCB38A] flex items-center justify-center shrink-0">
              <span class="text-xl font-black text-black">RLHI</span>
            </div>
            <div>
              <div class="text-2xl font-bold tracking-tight">RLHI</div>
              <div class="text-white/70 text-sm">Material Request System</div>
            </div>
          </div>
          <p class="mt-6 text-white/80 leading-relaxed">
            A clean internal workflow for submitting, reviewing, and approving material requests with role-based access.
          </p>
        </div>
        <div class="text-white/60 text-sm">© <?php echo date('Y'); ?> RLHI</div>
      </div>

      <!-- Content -->
      <div class="rounded-card bg-white p-8 shadow-soft border border-slate-100">
<?php
}

function ui_public_shell_end(): void {
?>
      </div>
    </div>
  </div>
</body>
</html>
<?php
}

/**
 * Hero-style landing layout – black, accent (#CCB38A), white theme
 * Logo: assets/images/logo.png | Hero bg: assets/images/hero-bg.jpg
 */
function ui_public_hero_layout(string $base = ''): void {
  $base = $base ?: '';
  $heroImage = $base . '/assets/images/hero-bg.jpg';
  $logoPath = __DIR__ . '/../assets/images/logo-icon.png';
  $logoImage = $base . '/assets/images/logo-icon.png' . (file_exists($logoPath) ? '?v=' . filemtime($logoPath) : '');
?>
  <!-- Fixed header: black bar, your logo image, nav – black / accent / white -->
  <header class="fixed top-0 left-0 right-0 z-50 bg-black border-b border-[#CCB38A]/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between gap-2">
      <a href="<?php echo htmlspecialchars($base ?: '/'); ?>" class="inline-flex items-center gap-2 sm:gap-3 min-w-0 shrink-0">
        <img src="<?php echo htmlspecialchars($logoImage); ?>" alt="RUDOLF LIETZ, INC." class="h-10 sm:h-14 w-auto max-w-[200px] sm:max-w-[280px] object-contain object-left" loading="eager" />
        <span class="text-white font-medium hidden sm:inline truncate text-sm">Material Request System</span>
      </a>
      <nav class="flex items-center gap-2 sm:gap-4 shrink-0">
        <a href="<?php echo htmlspecialchars($base ?: '/'); ?>" class="text-[#CCB38A] font-medium text-sm sm:text-base hidden md:inline">Home</a>
        <a href="<?php echo htmlspecialchars($base); ?>/login" class="px-3 py-1.5 sm:px-4 sm:py-2 text-sm sm:text-base bg-[#CCB38A] text-black font-semibold hover:bg-white transition rounded-lg">Login</a>
        <a href="<?php echo htmlspecialchars($base); ?>/signup" class="px-3 py-1.5 sm:px-4 sm:py-2 text-sm sm:text-base border border-[#CCB38A] text-white font-semibold hover:bg-[#CCB38A] hover:text-black transition rounded-lg">Sign Up</a>
      </nav>
    </div>
  </header>

  <!-- Hero: black/dark bg, accent, white text + feature block inside -->
  <section class="relative min-h-screen min-h-[100dvh] flex flex-col overflow-hidden pt-20 sm:pt-0 bg-black"
           style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.6)), url('<?php echo htmlspecialchars($heroImage); ?>') center/cover no-repeat;">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative z-10 flex-1 flex items-center justify-center text-center px-4 sm:px-6 py-12 sm:py-0 w-full max-w-3xl mx-auto">
      <div>
        <p class="text-[#CCB38A] text-xs sm:text-sm uppercase tracking-[0.2em] mb-2">Material Request System</p>
        <div class="w-16 h-px bg-[#CCB38A] mx-auto mb-6"></div>
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-semibold text-white tracking-tight">
          <span class="block">Clean Workflow,</span>
          <span class="text-[#CCB38A] mt-1">Built With Care</span>
        </h1>
        <p class="text-white/90 mt-6 text-base sm:text-lg max-w-xl mx-auto leading-relaxed">A simple internal workflow for submitting, reviewing, and approving material requests with role-based access.</p>
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center items-stretch sm:items-center">
          <a href="<?php echo htmlspecialchars($base); ?>/login" class="px-6 py-3.5 bg-[#CCB38A] text-black font-semibold rounded-lg hover:bg-white transition shadow-lg text-center">Login</a>
          <a href="<?php echo htmlspecialchars($base); ?>/signup" class="px-6 py-3.5 border-2 border-[#CCB38A] text-white font-semibold rounded-lg hover:bg-[#CCB38A] hover:text-black transition text-center">Sign Up</a>
        </div>
      </div>
    </div>
    <!-- Feature highlights inside hero (not footer) -->
    <div class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
      <div class="w-full h-px bg-[#CCB38A] mb-8"></div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-6">
        <div class="text-center md:text-left">
          <h2 class="text-xl font-bold text-white">Clean Workflow</h2>
          <p class="text-white/80 mt-1 text-sm sm:text-base">Submit and track requests in one place.</p>
        </div>
        <div class="text-center md:text-left">
          <h2 class="text-xl font-bold text-white">Role-Based Access</h2>
          <p class="text-white/80 mt-1 text-sm sm:text-base">Viewers, admins, and super admins.</p>
        </div>
        <div class="text-center md:text-left">
          <h2 class="text-xl font-bold text-white">Transparent Process</h2>
          <p class="text-white/80 mt-1 text-sm sm:text-base">Approve, decline, and remarks in one system.</p>
        </div>
      </div>
    </div>
  </section>

  <footer class="bg-black text-white/90">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-6">
        <!-- Company -->
        <div>
          <img src="<?php echo htmlspecialchars($base); ?>/assets/images/logo-icon.png" alt="RLHI" class="h-10 w-auto mb-3 object-contain" />
          <p class="text-sm font-semibold text-white mb-2">RUDOLF LIETZ, INC.</p>
          <p class="text-white/70 text-sm leading-relaxed">Creating exceptional architectural experiences that blend form, function, and innovation.</p>
        </div>
        <!-- Navigation -->
        <div>
          <h3 class="text-sm font-semibold text-[#CCB38A] uppercase tracking-wide mb-4">Navigation</h3>
          <ul class="space-y-2 text-sm text-white/80">
            <li><a href="<?php echo htmlspecialchars($base); ?>/" class="hover:text-[#CCB38A] transition">Home</a></li>
            <li><a href="<?php echo htmlspecialchars($base); ?>/login" class="hover:text-[#CCB38A] transition">Login</a></li>
            <li><a href="<?php echo htmlspecialchars($base); ?>/signup" class="hover:text-[#CCB38A] transition">Sign Up</a></li>
          </ul>
        </div>
        <!-- Services / Features -->
        <div>
          <h3 class="text-sm font-semibold text-[#CCB38A] uppercase tracking-wide mb-4">Features</h3>
          <ul class="space-y-2 text-sm text-white/80">
            <li>Create Request</li>
            <li>Track Requests</li>
            <li>Approve &amp; Decline</li>
            <li>Role-Based Access</li>
          </ul>
        </div>
        <!-- Contact -->
        <div>
          <h3 class="text-sm font-semibold text-[#CCB38A] uppercase tracking-wide mb-4">Contact</h3>
          <ul class="space-y-3 text-sm text-white/80">
            <li class="flex items-start gap-2">
              <span class="text-[#CCB38A] shrink-0 mt-0.5" aria-hidden="true">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </span>
              <span>Paranaque City, Phillipines</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-[#CCB38A] shrink-0 mt-0.5" aria-hidden="true">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              </span>
              <a href="mailto:hello@example.com" class="hover:text-[#CCB38A] transition">hello@example.com</a>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-[#CCB38A] shrink-0 mt-0.5" aria-hidden="true">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
              </span>
              <span>+1 (555) 123-4567</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="border-t border-white/10">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-3 text-sm text-white/70">
        <span>© <?php echo date('Y'); ?> RLHI. All rights reserved.</span>
        <div class="flex items-center gap-4">
          <a href="#" class="text-[#CCB38A] hover:opacity-80 transition" aria-label="Twitter"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
          <a href="#" class="text-[#CCB38A] hover:opacity-80 transition" aria-label="Instagram"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
          <a href="#" class="text-[#CCB38A] hover:opacity-80 transition" aria-label="LinkedIn"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a>
        </div>
      </div>
    </div>
  </footer>
</body>
</html>
<?php
}

