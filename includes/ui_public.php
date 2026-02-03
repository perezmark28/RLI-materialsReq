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
            <div class="h-12 w-12 rounded-full bg-[#FFCC00] flex items-center justify-center shrink-0">
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

