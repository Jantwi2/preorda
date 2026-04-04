<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in • PreOrda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600&display=swap');

        :root {
            --primary: 234 179 8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system_ui, sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%);
            color: #f1f1f1;
            min-height: 100vh;
            overflow: hidden;
        }

        .logo-font {
            font-family: 'Space Grotesk', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .gradient-text {
            background: linear-gradient(90deg, #facc15, #eab308);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .input-focus {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-focus:focus {
            box-shadow: 0 0 0 4px rgba(234, 179, 8, 0.15);
            border-color: rgb(234 179 8);
        }

        .hero-bg {
            background: radial-gradient(circle at 30% 20%, rgba(234, 179, 8, 0.15) 0%, transparent 50%);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">
    <div class="max-w-7xl w-full mx-auto grid lg:grid-cols-12 gap-8 items-center">
        
        <!-- Left Visual Panel -->
        <div class="lg:col-span-7 hidden lg:flex flex-col justify-between min-h-[640px] rounded-3xl overflow-hidden relative hero-bg">
            
            <!-- Subtle grid overlay -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:40px_40px]"></div>

            <div class="p-12 relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-yellow-400 rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-box text-black text-xl"></i>
                    </div>
                    <div class="logo-font text-4xl font-semibold tracking-tighter text-white">PreOrda</div>
                </div>
            </div>

            <!-- Main Visual Content -->
            <div class="flex-1 flex items-center justify-center p-12 relative">
                <div class="text-center max-w-md">
                    <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-6 py-2.5 rounded-3xl mb-8 border border-white/10">
                        <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-emerald-300">Now serving vendors in Ghana</span>
                    </div>

                    <h1 class="text-6xl font-semibold tracking-tighter leading-none text-white mb-6">
                        Your store.<br>
                        <span class="gradient-text">Your link.</span><br>
                        Your sales.
                    </h1>

                    <p class="text-xl text-zinc-400 max-w-sm mx-auto">
                        Beautiful pre-order storefronts with personalized URLs. 
                        Built for vendors who move fast.
                    </p>
                </div>
            </div>

            <!-- Trust signals -->
            <div class="p-12 border-t border-white/10 flex items-center gap-10 text-sm">
                <div>
                    <div class="flex -space-x-3 mb-3">
                        <div class="w-8 h-8 rounded-2xl bg-zinc-700 border-2 border-zinc-900 flex items-center justify-center text-xs font-mono">🇬🇭</div>
                        <div class="w-8 h-8 rounded-2xl bg-zinc-700 border-2 border-zinc-900 flex items-center justify-center text-xs">👟</div>
                        <div class="w-8 h-8 rounded-2xl bg-zinc-700 border-2 border-zinc-900 flex items-center justify-center text-xs">📱</div>
                    </div>
                    <p class="text-zinc-400 text-sm">Trusted by 1,200+ vendors</p>
                </div>

                <div class="h-10 w-px bg-white/10"></div>

                <div class="flex items-center gap-6 text-zinc-400">
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span class="text-sm">Secure</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt"></i>
                        <span class="text-sm">Instant</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-link"></i>
                        <span class="text-sm">Personal</span>
                    </div>
                </div>
            </div>

            <!-- Floating product mockup -->
            <div class="absolute -bottom-6 right-12 bg-zinc-900 rounded-3xl shadow-2xl border border-white/10 overflow-hidden w-80 hidden xl:block">
                <div class="bg-black p-4">
                    <div class="flex justify-between items-center mb-4">
                        <div class="text-xs text-zinc-400">yourstore.lightgoldenrodyellow-dove-201674.hostingersite.com</div>
                        <div class="px-3 py-1 bg-yellow-400 text-black text-xs font-semibold rounded-full">LIVE</div>
                    </div>
                    <div class="aspect-video bg-zinc-800 rounded-2xl mb-4 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fa-solid fa-shirt text-6xl text-zinc-700 mb-3"></i>
                            <p class="text-xs text-zinc-500">Custom Sneakers Pre-order</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-1 h-2 bg-yellow-400 rounded-full"></div>
                        <div class="flex-1 h-2 bg-white/20 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Form Panel -->
        <div class="lg:col-span-5">
            <div class="glass rounded-3xl p-10 md:p-14 max-w-[460px] mx-auto lg:mx-0 shadow-2xl">
                
                <!-- Header -->
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-8 lg:hidden">
                        <div class="w-8 h-8 bg-yellow-400 rounded-2xl flex items-center justify-center">
                            <i class="fa-solid fa-box text-black"></i>
                        </div>
                        <div class="logo-font text-3xl font-semibold tracking-tighter">PreOrda</div>
                    </div>

                    <h2 class="text-4xl font-semibold tracking-tight text-white mb-3">Welcome back</h2>
                    <p class="text-zinc-400 text-lg">Sign in to access your vendor dashboard</p>
                </div>

                <!-- Success Message -->
                <div id="successMessage" class="hidden mb-8 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-6 py-4 rounded-2xl text-sm flex items-center gap-3">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Login successful. Redirecting to your dashboard...</span>
                </div>

                <form id="loginForm" class="space-y-8">
                    
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-2.5">Email address</label>
                        <div class="relative">
                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-zinc-500">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <input 
                                type="email" 
                                id="loginEmail"
                                class="input-focus w-full bg-white/5 border border-white/10 focus:border-yellow-400 text-white placeholder-zinc-500 rounded-2xl py-4 pl-12 pr-6 text-base outline-none transition-all"
                                placeholder="you@yourbusiness.com"
                                required>
                        </div>
                        <div class="error text-red-400 text-sm mt-2 hidden" id="loginEmailError">
                            Please enter a valid email address
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex justify-between items-center mb-2.5">
                            <label class="text-sm font-medium text-zinc-400">Password</label>
                            <a href="#" class="text-xs text-yellow-400 hover:text-yellow-300 transition-colors">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-zinc-500">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input 
                                type="password" 
                                id="loginPassword"
                                class="input-focus w-full bg-white/5 border border-white/10 focus:border-yellow-400 text-white placeholder-zinc-500 rounded-2xl py-4 pl-12 pr-6 text-base outline-none transition-all"
                                placeholder="••••••••••••"
                                required>
                            <button type="button" onclick="togglePassword()" class="absolute right-5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-white">
                                <i id="eyeIcon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="error text-red-400 text-sm mt-2 hidden" id="loginPasswordError">
                            Password is required
                        </div>
                    </div>

                    <!-- Sign In Button -->
                    <button 
                        type="submit"
                        class="w-full bg-yellow-400 hover:bg-yellow-300 active:bg-yellow-500 transition-all text-black font-semibold text-base py-4.5 rounded-2xl flex items-center justify-center gap-3 shadow-lg shadow-yellow-400/30">
                        <span>Sign in to dashboard</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="my-8 flex items-center gap-4">
                    <div class="flex-1 h-px bg-white/10"></div>
                    <span class="text-xs uppercase tracking-widest text-zinc-500 font-medium">or</span>
                    <div class="flex-1 h-px bg-white/10"></div>
                </div>

                <!-- Social / Alternative -->
                <div class="grid grid-cols-2 gap-4">
                    <button onclick="alert('Google login coming soon')" 
                            class="glass hover:bg-white/10 transition-colors text-white border border-white/10 rounded-2xl py-4 flex items-center justify-center gap-3 text-sm font-medium">
                        <i class="fa-brands fa-google"></i>
                        <span>Google</span>
                    </button>
                    <button onclick="alert('Apple login coming soon')" 
                            class="glass hover:bg-white/10 transition-colors text-white border border-white/10 rounded-2xl py-4 flex items-center justify-center gap-3 text-sm font-medium">
                        <i class="fa-brands fa-apple"></i>
                        <span>Apple</span>
                    </button>
                </div>

                <!-- Create account link -->
                <div class="text-center mt-10">
                    <p class="text-zinc-400 text-sm">
                        Don't have a store yet? 
                        <a href="register.php" class="text-yellow-400 hover:text-yellow-300 font-semibold transition-colors">Create your PreOrda store →</a>
                    </p>
                </div>

            </div>

            <!-- Footer note -->
            <div class="text-center mt-8 text-xs text-zinc-500">
                Secure login • Powered by PreOrda • Ghana
            </div>
        </div>
    </div>

    <script>
        // Tailwind script already loaded via CDN

        function togglePassword() {
            const passwordInput = document.getElementById('loginPassword');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Form handling (keeps your existing auth.js logic if you want to attach it)
        const loginForm = document.getElementById('loginForm');
        const successMessage = document.getElementById('successMessage');

        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const emailError = document.getElementById('loginEmailError');
            const passError = document.getElementById('loginPasswordError');

            emailError.classList.add('hidden');
            passError.classList.add('hidden');

            if (email && password) {
                const btn = loginForm.querySelector('button[type="submit"]');
                const prevText = btn.innerHTML;
                btn.innerHTML = '<span>Signing in...</span><i class="fa-solid fa-spinner fa-spin"></i>';
                btn.disabled = true;

                try {
                    const response = await fetch('../actions/login_vendor.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ login: email, password: password })
                    });
                    const result = await response.json();

                    btn.disabled = false;
                    btn.innerHTML = prevText;

                    if (result.status === 'success') {
                        successMessage.classList.remove('hidden');
                        successMessage.classList.add('flex');
                        
                        setTimeout(() => {
                            if (result.role === 'admin') {
                                window.location.href = '../admin/dashboard.php';
                            } else {
                                window.location.href = '../vendor/dashboard.php';
                            }
                        }, 1000);
                    } else if (result.status === 'invalid_password') {
                        passError.textContent = 'Incorrect password';
                        passError.classList.remove('hidden');
                    } else if (result.status === 'user_not_found') {
                        emailError.textContent = 'Account not found';
                        emailError.classList.remove('hidden');
                    } else {
                        emailError.textContent = result.message || 'Login failed. Please try again.';
                        emailError.classList.remove('hidden');
                    }
                } catch (error) {
                    btn.disabled = false;
                    btn.innerHTML = prevText;
                    emailError.textContent = 'Network error. Please try again later.';
                    emailError.classList.remove('hidden');
                }
            } else {
                if (!email) emailError.classList.remove('hidden');
                if (!password) passError.classList.remove('hidden');
            }
        });

        // Keyboard support
        document.addEventListener('keydown', function(e) {
            if (e.key === "Enter" && document.activeElement.tagName !== "TEXTAREA") {
                loginForm.dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>