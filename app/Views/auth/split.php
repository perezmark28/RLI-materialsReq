<?php
/**
 * Split-Screen Login / Sign Up with sliding decorative panel
 * Theme: Black (#000000), Golden Yellow (#FFCC00), soft gray background
 * Expects: $base, $error (optional), $username, $full_name, $email, $initialForm ('login'|'signup')
 */
$base = $base ?? (defined('BASE_PATH') ? BASE_PATH : '');
$initialForm = $initialForm ?? 'login';
$showSignup = ($initialForm === 'signup');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $showSignup ? 'Sign Up' : 'Login'; ?> - RLHI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primaryBlack: '#000000',
            primaryYellow: '#FFCC00',
          }
        }
      }
    };
  </script>
  <style>
    .form-fade { transition: opacity 0.6s ease-in-out; }
    .panel-slide { transition: left 0.6s ease-in-out; }
  </style>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
  <div class="w-full max-w-5xl md:hidden mb-4">
    <div class="rounded-3xl bg-black text-white p-5 shadow-2xl">
      <div class="flex items-center gap-3">
        <div class="h-12 w-12 rounded-full bg-[#FFCC00] flex items-center justify-center shrink-0">
          <span class="text-xl font-black text-black">RLHI</span>
        </div>
        <div>
          <div class="text-xl font-bold tracking-tight">RLHI</div>
          <div class="text-white/80 text-sm">Material Request System</div>
        </div>
      </div>
      <p class="mt-4 text-white/80 text-sm leading-relaxed">
        A clean internal workflow for submitting, reviewing, and approving material requests.
      </p>
    </div>
  </div>

  <div id="authWrapper" class="w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row md:min-h-[640px] relative">
    <!-- Left slot: Signup form (visible when decorative has slid right) -->
    <div class="w-full md:w-1/2 md:min-h-[640px] flex flex-col justify-center p-6 md:p-8 lg:p-10 relative z-10">
      <div id="signupFormWrap" class="form-fade max-w-sm mx-auto w-full <?php echo $showSignup ? 'opacity-100' : 'opacity-0 pointer-events-none absolute inset-0 flex items-center'; ?>" style="<?php echo !$showSignup ? 'visibility: hidden' : ''; ?>">
        <div class="w-full px-4">
          <h1 class="text-2xl font-bold text-gray-900">Create account</h1>
          <p class="text-gray-500 mt-1 text-sm">Fill in your details to get started.</p>

          <?php if (!empty($error) && $initialForm === 'signup'): ?>
            <div class="mt-2 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-red-700 text-sm font-semibold">
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="<?php echo $base; ?>/signup" class="mt-6 flex flex-col gap-4 max-w-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="signup_full_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                <input id="signup_full_name" name="full_name" type="text" required
                  value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                  class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/20 outline-none transition">
              </div>
              <div>
                <label for="signup_email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                <input id="signup_email" name="email" type="email" required
                  value="<?php echo htmlspecialchars($email ?? ''); ?>"
                  class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/20 outline-none transition">
              </div>
            </div>
            <div>
              <label for="signup_username" class="block text-sm font-semibold text-gray-700 mb-1.5">Username</label>
              <input id="signup_username" name="username" type="text" required
                value="<?php echo htmlspecialchars($username ?? ''); ?>"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/20 outline-none transition">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="signup_password" class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                <input id="signup_password" name="password" type="password" required
                  class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/20 outline-none transition">
              </div>
              <div>
                <label for="signup_confirm_password" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                <input id="signup_confirm_password" name="confirm_password" type="password" required
                  class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/20 outline-none transition">
              </div>
            </div>
            <button type="submit" class="w-full py-3.5 rounded-xl bg-black text-white font-semibold hover:bg-gray-800 transition mt-1">
              Create Account
            </button>
          </form>

          <p class="mt-6 text-center text-sm text-gray-600">
            Already have an account? <button type="button" id="showLoginBtn" class="font-semibold text-black hover:underline focus:outline-none">Login</button>
          </p>
        </div>
      </div>
    </div>

    <!-- Right slot: Login form (visible when decorative is on left) -->
    <div class="w-full md:w-1/2 md:min-h-[640px] flex flex-col justify-center p-6 md:p-8 lg:p-10 relative z-10">
      <a href="<?php echo $base; ?>/" class="md:absolute md:top-6 md:right-6 text-sm font-semibold text-gray-600 hover:text-black z-20 self-end mb-4 md:mb-0">Back</a>
      <div id="loginFormWrap" class="form-fade max-w-sm mx-auto w-full <?php echo $showSignup ? 'opacity-0 pointer-events-none absolute inset-0 flex items-center' : 'opacity-100'; ?>" style="<?php echo $showSignup ? 'visibility: hidden' : ''; ?>">
        <div class="w-full px-4">
          <h1 class="text-2xl font-bold text-gray-900">Welcome home</h1>
          <p class="text-gray-500 mt-1 text-sm">Enter your credentials to continue.</p>

          <?php if (!empty($error) && $initialForm === 'login'): ?>
            <div class="mt-4 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-red-700 text-sm font-semibold">
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="<?php echo $base; ?>/login" class="mt-6 space-y-4">
            <div>
              <label for="username" class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
              <input id="username" name="username" type="text" required autofocus
                value="<?php echo htmlspecialchars($username ?? ''); ?>"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/20 outline-none transition">
            </div>
            <div>
              <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
              <input id="password" name="password" type="password" required
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/20 outline-none transition">
            </div>
            <button type="submit" class="w-full py-3 rounded-xl bg-black text-white font-semibold hover:bg-gray-800 transition">
              Login
            </button>
          </form>

          <p class="mt-4 text-center text-sm text-gray-500">or continue with</p>
          <div class="mt-3 flex justify-center gap-4">
            <button type="button" class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600" title="Apple" aria-label="Apple">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M12 2c.73 0 1.5.08 2.28.24-.4 1.1-.48 2.2-.2 3.24-.99.12-2.01-.27-2.77-.9-.78-.66-1.31-1.62-1.55-2.66-.24-1.05-.06-2.1.38-3.08.52.12 1.03.28 1.5.48.12-.96.45-1.9.96-2.72.51-.82 1.2-1.49 2-1.94-.27-.08-.55-.12-.8-.12-1.28 0-2.5.82-2.5 2.5 0 1.28 1.25 2.5 2.5 2.5.45 0 .88-.12 1.25-.34-.33.9-.5 1.84-.5 2.78 0 1.94.72 3.75 1.9 5.12-.58.36-1.26.58-2 .58-1.66 0-3-1.34-3-3 0-1.66 1.34-3 3-3 .74 0 1.42.22 2 .58-.18-.94-.25-1.88-.25-2.82 0-2.21 1.79-4 4-4 .74 0 1.45.2 2.06.56.61-.36 1.32-.56 2.06-.56 2.21 0 4 1.79 4 4 0 .94-.07 1.88-.25 2.82.58-.36 1.26-.58 2-.58 1.66 0 3 1.34 3 3 0 1.66-1.34 3-3 3-.74 0-1.42-.22-2-.58 1.18-1.37 1.9-3.18 1.9-5.12 0-.94-.17-1.88-.5-2.78.37.22.8.34 1.25.34 1.28 0 2.5-1.22 2.5-2.5 0-1.68-1.22-2.5-2.5-2.5-.25 0-.53.04-.8.12.8.45 1.49 1.12 2 1.94.51.82.84 1.76.96 2.72.47-.2.98-.36 1.5-.48-.44.98-.62 2.03-.38 3.08.24 1.04.77 2 1.55 2.66.76.63 1.78.9 2.77.9.28-1.04.2-2.14-.2-3.24.78-.16 1.55-.24 2.28-.24 2.21 0 4 1.79 4 4 0 2.21-1.79 4-4 4-.74 0-1.45-.2-2.06-.56-.61.36-1.32.56-2.06.56-2.21 0-4-1.79-4-4 0-2.21 1.79-4 4-4z"/></svg>
            </button>
            <button type="button" class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600" title="Google" aria-label="Google">
              <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            </button>
            <button type="button" class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600" title="Facebook" aria-label="Facebook">
              <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </button>
          </div>

          <p class="mt-6 text-center text-sm text-gray-600">
            Don't have an account? <button type="button" id="showSignupBtn" class="font-semibold text-black hover:underline">Sign Up</button>
          </p>
        </div>
      </div>
    </div>

    <!-- Decorative panel overlay (tablet/desktop only): slides left → right (0.6s ease-in-out) -->
    <div id="decorativePanel" class="panel-slide hidden md:flex absolute top-0 bottom-0 w-1/2 z-20 flex-col justify-between p-8 lg:p-10 bg-black text-white rounded-l-3xl <?php echo $showSignup ? 'left-1/2 rounded-l-none rounded-r-3xl' : 'left-0'; ?>">
      <div>
        <div class="flex items-center gap-3">
          <div class="h-12 w-12 rounded-full bg-[#FFCC00] flex items-center justify-center shrink-0">
            <span class="text-xl font-black text-black">RLHI</span>
          </div>
          <div>
            <div class="text-2xl font-bold tracking-tight">RLHI</div>
            <div class="text-white/80 text-sm">Material Request System</div>
          </div>
        </div>
        <p class="mt-6 text-white/90 leading-relaxed text-sm lg:text-base">
          A clean internal workflow for submitting, reviewing, and approving material requests with role-based access.
        </p>
      </div>
      <div class="text-white/60 text-sm mt-6">© <?php echo date('Y'); ?> RLHI</div>
    </div>
  </div>

  <script>
    (function() {
      const decorativePanel = document.getElementById('decorativePanel');
      const loginWrap = document.getElementById('loginFormWrap');
      const signupWrap = document.getElementById('signupFormWrap');
      const showSignupBtn = document.getElementById('showSignupBtn');
      const showLoginBtn = document.getElementById('showLoginBtn');
      const canSlidePanel = window.matchMedia && window.matchMedia('(min-width: 768px)').matches;

      function showSignup() {
        if (decorativePanel && canSlidePanel) {
          decorativePanel.classList.remove('left-0', 'rounded-l-3xl');
          decorativePanel.classList.add('left-1/2', 'rounded-r-3xl', 'rounded-l-none');
        }
        loginWrap.classList.add('opacity-0', 'pointer-events-none');
        loginWrap.style.visibility = 'hidden';
        signupWrap.classList.remove('opacity-0', 'pointer-events-none');
        signupWrap.style.visibility = 'visible';
      }

      function showLogin() {
        if (decorativePanel && canSlidePanel) {
          decorativePanel.classList.remove('left-1/2', 'rounded-r-3xl', 'rounded-l-none');
          decorativePanel.classList.add('left-0', 'rounded-l-3xl');
        }
        signupWrap.classList.add('opacity-0', 'pointer-events-none');
        signupWrap.style.visibility = 'hidden';
        loginWrap.classList.remove('opacity-0', 'pointer-events-none');
        loginWrap.style.visibility = 'visible';
      }

      if (showSignupBtn) showSignupBtn.addEventListener('click', showSignup);
      if (showLoginBtn) showLoginBtn.addEventListener('click', showLogin);
    })();
  </script>
</body>
</html>
