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
      background-color: #FFCC00;
      color: #000;
      border-color: #FFCC00;
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
      background-color: #FFCC00;
      border-color: #FFCC00;
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
      border-color: #FFCC00;
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

