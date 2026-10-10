<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

$error = '';
$success = '';
$active_form = ($_GET['mode'] ?? '') === 'signup' ? 'signup' : 'login';
$form_data = [];

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout_user();
    header('Location: ../index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = $_POST;
    $form_action = $_POST['form_action'] ?? (basename($_SERVER['SCRIPT_NAME']) === 'register.php' ? 'signup' : 'login');

    if ($form_action === 'signup') {
        $active_form = 'signup';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? '';
        $terms = isset($_POST['terms']);

        if ($name === '' || $email === '' || $password === '' || $confirm_password === '' || $role === '') {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (!$terms) {
            $error = 'You must agree to the terms and policy.';
        } elseif (!in_array($role, [ROLE_STUDENT, ROLE_STAFF, ROLE_ADMIN], true)) {
            $error = 'Select a valid account type.';
        } else {
            switch ($role) {
                case ROLE_STUDENT:
                    $file = STUDENTS_FILE;
                    break;
                case ROLE_STAFF:
                    $file = STAFF_FILE;
                    break;
                case ROLE_ADMIN:
                    $file = ADMINS_FILE;
                    break;
            }

            if (json_find($file, 'email', $email)) {
                $error = 'Email already registered for this role.';
            } else {
                $user = [
                    'id' => generate_id($role . '_'),
                    'name' => htmlspecialchars($name),
                    'email' => htmlspecialchars($email),
                    'password' => hash_password($password),
                    'role' => $role,
                    'phone' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($role === ROLE_STUDENT) {
                    $user['residence'] = '';
                    $user['room'] = '';
                }

                if (json_add($file, $user)) {
                    $success = 'Account created. You can now log in.';
                    log_activity($user['id'], $role, 'register', ucfirst($role) . ' registered: ' . $user['name']);
                    header('Refresh: 2; url=login.php');
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    } else {
        $active_form = 'login';
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if ($email === '' || $password === '' || $role === '') {
            $error = 'Enter your email address, password, and select your role.';
        } else {
            $user = authenticate_user($email, $password, $role);

            if ($user) {
                login_user($user['id'], $user['email'], $user['name'], $user['role']);
                log_activity($user['id'], $role, 'login', ucfirst($role) . ' logged in: ' . $user['name']);
                
                // Redirect based on role
                switch ($role) {
                    case ROLE_STUDENT:
                        header('Location: index.php');
                        break;
                    case ROLE_STAFF:
                        header('Location: ../maintenance/index.php');
                        break;
                    case ROLE_ADMIN:
                        header('Location: ../admin/index.php');
                        break;
                }
                exit;
            }

            $error = 'Invalid email or password for the selected role.';
        }
    }
}

$login_email = htmlspecialchars($form_data['email'] ?? '', ENT_QUOTES, 'UTF-8');
$signup_name = htmlspecialchars($form_data['name'] ?? '', ENT_QUOTES, 'UTF-8');
$signup_email = htmlspecialchars($form_data['email'] ?? '', ENT_QUOTES, 'UTF-8');
$signup_role = $form_data['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Log in or create a VUT MainRes Maintenance account.">
    <meta name="keywords" content="VUT, Login, Sign Up, Maintenance">
    <title>Log In or Sign Up - VUT MainRes</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/styles.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="auth-page-body">
    <!-- Navigation -->
    <nav class="navbar" id="main-nav">
        <input type="checkbox" id="nav-toggle" class="nav-toggle-input" aria-label="Toggle navigation menu">
        <label for="nav-toggle" style="display: none;"></label>
        <div class="logo" id="brand-logo">
            <img src="../assets/images/logo.png" alt="VUT Logo" class="nav-logo">
            VUT MainRes<span>Maintenance</span>
        </div>
        <!-- Toggle Button -->
        <label for="nav-toggle" class="menu-toggle-btn" id="mobile-menu-toggle">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </label>
        <ul class="nav-links" id="nav-links">
            <li><a href="../index.php" id="nav-home">Home</a></li>
            <li><a href="../index.php?page=about" class="nav-btn-text" id="nav-about">About</a></li>
            <li><a href="login.php" class="nav-btn-text" id="nav-login">Log In</a></li>
            <li><a href="login.php?mode=signup" class="nav-btn-primary" id="nav-signup" data-auth-switch="signup">Sign Up</a></li>
        </ul>
    </nav>

    <main class="auth-shell">
        <div class="auth-panel" id="auth-panel" data-mode="<?php echo htmlspecialchars($active_form, ENT_QUOTES, 'UTF-8'); ?>">
            <section class="auth-form-side" aria-label="Account access">
                <a href="../index.php" class="auth-brand">
                    <img src="../assets/images/logo.png" alt="">
                    <span>VUT MainRes<small>MAINTENANCE SERVICES</small></span>
                </a>

                <div class="auth-form-window">
                    <div class="auth-form-track">
                        <form class="auth-form" id="login-form" data-auth-form="login" method="POST" action="login.php" <?php echo $active_form === 'signup' ? 'inert aria-hidden="true"' : 'aria-hidden="false"'; ?>>
                            <input type="hidden" name="form_action" value="login">
                            <div class="auth-form-heading">
                                <span class="auth-eyebrow">YOUR RESIDENCE, WELL CARED FOR</span>
                                <h1>Welcome back</h1>
                                <p>Log in to report and manage your maintenance requests.</p>
                            </div>

                            <?php if ($error && $active_form === 'login'): ?>
                                <div class="auth-message auth-message-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>

                            <div class="auth-field auth-field--wide">
                                <label>I am a...</label>
                                <input type="hidden" id="login-role" name="role" required>
                                <div class="role-select-wrap">
                                    <div class="role-select-display" id="loginRoleDisplay" data-empty="true">
                                        <span class="role-select-icon role-select-icon--placeholder"><i class="fa-solid fa-user-tag"></i></span>
                                        <span class="role-select-label">Select your role</span>
                                        <i class="fa-solid fa-chevron-down role-select-caret"></i>
                                    </div>
                                    <div class="role-select-dropdown" id="loginRoleDropdown" role="listbox" aria-hidden="true">
                                        <button type="button" class="role-select-option" data-role="student" role="option">
                                            <span class="role-option-icon role-option-icon--student"><i class="fa-solid fa-user"></i></span>
                                            <span class="role-option-title">Student</span>
                                        </button>
                                        <button type="button" class="role-select-option" data-role="staff" role="option">
                                            <span class="role-option-icon role-option-icon--staff"><i class="fa-solid fa-user"></i></span>
                                            <span class="role-option-title">Maintenance Staff</span>
                                        </button>
                                        <button type="button" class="role-select-option" data-role="admin" role="option">
                                            <span class="role-option-icon role-option-icon--admin"><i class="fa-solid fa-user"></i></span>
                                            <span class="role-option-title">Administrator</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="login-email">Email address</label>
                                <div class="auth-input-wrap">
                                    <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                                    <input id="login-email" type="email" name="email" value="<?php echo $login_email; ?>" placeholder="you@example.com" autocomplete="email" required>
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="login-password">Password</label>
                                <div class="auth-input-wrap">
                                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                    <input id="login-password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                                    <button class="auth-password-toggle" type="button" data-password-toggle aria-controls="login-password" aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
                                </div>
                            </div>

                            <button class="auth-submit-btn" type="submit">Log In <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                            <p class="auth-switch-text">New to MainRes? <a href="login.php?mode=signup" data-auth-switch="signup">Create Account</a></p>
                        </form>

                        <form class="auth-form auth-form--signup" id="signup-form" data-auth-form="signup" method="POST" action="login.php" <?php echo $active_form === 'signup' ? 'aria-hidden="false"' : 'inert aria-hidden="true"'; ?>>
                            <input type="hidden" name="form_action" value="signup">
                            <div class="auth-form-heading auth-field--wide">
                                <span class="auth-eyebrow">JOIN YOUR RESIDENCE COMMUNITY</span>
                                <h1>Create your account</h1>
                                <p>Set up your account to get maintenance support.</p>
                            </div>

                            <?php if ($error && $active_form === 'signup'): ?>
                                <div class="auth-message auth-message-error auth-field--wide" role="alert"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <?php if ($success): ?>
                                <div class="auth-message auth-message-success auth-field--wide" role="status"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <div class="auth-field auth-field--wide">
                                <label>Account type</label>
                                <input type="hidden" id="signup-role" name="role" required value="<?php echo htmlspecialchars($signup_role ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="role-select-wrap">
                                    <div class="role-select-display" id="signupRoleDisplay" data-empty="<?php echo empty($signup_role) ? 'true' : 'false'; ?>">
                                        <span class="role-select-icon <?php echo empty($signup_role) ? 'role-select-icon--placeholder' : ('role-select-icon--' . htmlspecialchars($signup_role)); ?>">
                                            <i class="fa-solid <?php echo empty($signup_role) ? 'fa-user-tag' : 'fa-user'; ?>"></i>
                                        </span>
                                        <span class="role-select-label"><?php echo empty($signup_role) ? 'Select your role' : htmlspecialchars($signup_role === 'staff' ? 'Maintenance Staff' : ($signup_role === 'admin' ? 'Administrator' : 'Student')); ?></span>
                                        <i class="fa-solid fa-chevron-down role-select-caret"></i>
                                    </div>
                                    <div class="role-select-dropdown" id="signupRoleDropdown" role="listbox" aria-hidden="true">
                                        <button type="button" class="role-select-option" data-role="student" role="option">
                                            <span class="role-option-icon role-option-icon--student"><i class="fa-solid fa-user"></i></span>
                                            <span class="role-option-title">Student</span>
                                        </button>
                                        <button type="button" class="role-select-option" data-role="staff" role="option">
                                            <span class="role-option-icon role-option-icon--staff"><i class="fa-solid fa-user"></i></span>
                                            <span class="role-option-title">Maintenance Staff</span>
                                        </button>
                                        <button type="button" class="role-select-option" data-role="admin" role="option">
                                            <span class="role-option-icon role-option-icon--admin"><i class="fa-solid fa-user"></i></span>
                                            <span class="role-option-title">Administrator</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="signup-name">Full name</label>
                                <div class="auth-input-wrap">
                                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                                    <input id="signup-name" type="text" name="name" value="<?php echo $signup_name; ?>" placeholder="Your full name" autocomplete="name" required>
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="signup-email">Email address</label>
                                <div class="auth-input-wrap">
                                    <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                                    <input id="signup-email" type="email" name="email" value="<?php echo $signup_email; ?>" placeholder="you@example.com" autocomplete="email" required>
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="signup-password">Password</label>
                                <div class="auth-input-wrap">
                                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                    <input id="signup-password" type="password" name="password" placeholder="Create a password" autocomplete="new-password" minlength="8" required>
                                    <button class="auth-password-toggle" type="button" data-password-toggle aria-controls="signup-password" aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="signup-confirm-password">Confirm password</label>
                                <div class="auth-input-wrap">
                                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                    <input id="signup-confirm-password" type="password" name="confirm_password" placeholder="Re-enter password" autocomplete="new-password" minlength="8" required>
                                    <button class="auth-password-toggle" type="button" data-password-toggle aria-controls="signup-confirm-password" aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
                                </div>
                            </div>

                            <label class="auth-terms auth-field--wide" for="signup-terms">
                                <input id="signup-terms" type="checkbox" name="terms" required>
                                <span>I agree to the terms and policy.</span>
                            </label>

                            <button class="auth-submit-btn auth-field--wide" type="submit">Create Account <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                            <p class="auth-switch-text auth-field--wide">Already have an account? <a href="login.php" data-auth-switch="login">Login</a></p>
                        </form>
                    </div>
                </div>
            </section>

            <aside class="auth-visual" aria-label="VUT MainRes maintenance support">
                <img src="../assets/images/Loginfix.png" alt="Maintenance support at VUT Main Residence">
                <div class="auth-visual-content">
                    <span class="auth-visual-kicker">VAAL UNIVERSITY OF TECHNOLOGY</span>
                    <h2>Make room for<br><span>better living.</span></h2>
                    <p>Report residence issues and connect with the right maintenance support.</p>
                    <span class="auth-visual-rule"></span>
                    <span class="auth-visual-caption">VUT MAIN RESIDENCE · MAINTENANCE SERVICES</span>
                </div>
            </aside>
        </div>
    </main>
    <footer class="auth-legal"><span>© 2026 VUT MainRes Maintenance</span><a href="../index.php">Back to home</a></footer>
    <script>
        const authPanel = document.getElementById('auth-panel');
        const authForms = [...document.querySelectorAll('[data-auth-form]')];
        const authFormWindow = document.querySelector('.auth-form-window');

        function updateAuthFormHeight() {
            const activeForm = authForms.find(form => form.dataset.authForm === authPanel.dataset.mode);
            if (activeForm) authFormWindow.style.setProperty('--auth-form-height', `${activeForm.scrollHeight}px`);
        }

        function setAuthMode(mode, focusForm = false) {
            const activeForm = authForms.find(form => form.dataset.authForm === mode);
            if (!activeForm) return;

            authPanel.dataset.mode = mode;
            activeForm.inert = false;
            activeForm.setAttribute('aria-hidden', 'false');
            updateAuthFormHeight();

            if (focusForm) {
                activeForm.querySelector('[data-autofocus]')?.focus({ preventScroll: true });
            }

            authForms.forEach(form => {
                if (form === activeForm) return;
                form.inert = true;
                form.setAttribute('aria-hidden', 'true');
            });

            const menuToggle = document.getElementById('nav-toggle');
            if (menuToggle) menuToggle.checked = false;
        }

        setAuthMode(authPanel.dataset.mode);
        window.addEventListener('resize', updateAuthFormHeight);

        document.addEventListener('click', event => {
            const switchLink = event.target.closest('[data-auth-switch]');
            if (!switchLink) return;
            event.preventDefault();
            setAuthMode(switchLink.dataset.authSwitch, true);
        });

        document.querySelectorAll('[data-password-toggle]').forEach(button => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.getAttribute('aria-controls'));
                const showPassword = input.type === 'password';
                input.type = showPassword ? 'text' : 'password';
                button.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
                button.innerHTML = `<i class="fa-regular ${showPassword ? 'fa-eye-slash' : 'fa-eye'}" aria-hidden="true"></i>`;
            });
        });

        const signupForm = document.getElementById('signup-form');
        const signupPassword = document.getElementById('signup-password');
        const signupConfirmation = document.getElementById('signup-confirm-password');
        const validatePasswordMatch = () => {
            signupConfirmation.setCustomValidity(
                signupConfirmation.value && signupConfirmation.value !== signupPassword.value
                    ? 'Passwords do not match.'
                    : ''
            );
        };

        signupPassword.addEventListener('input', validatePasswordMatch);
        signupConfirmation.addEventListener('input', validatePasswordMatch);
        signupForm.addEventListener('submit', event => {
            validatePasswordMatch();
            if (!signupForm.reportValidity()) event.preventDefault();
        });

        function setRoleSelection(pickerName, role, silentUpdate = false) {
            const display = document.getElementById(pickerName === 'login' ? 'loginRoleDisplay' : 'signupRoleDisplay');
            const dropdown = document.getElementById(pickerName === 'login' ? 'loginRoleDropdown' : 'signupRoleDropdown');
            const hidden = document.getElementById(pickerName === 'login' ? 'login-role' : 'signup-role');
            if (!display || !dropdown || !hidden) return;

            const meta = {
                student: { label: 'Student' },
                staff:   { label: 'Maintenance Staff' },
                admin:   { label: 'Administrator' }
            };

            hidden.value = role || '';

            const iconEl = display.querySelector('.role-select-icon');
            const labelEl = display.querySelector('.role-select-label');

            iconEl.classList.remove(
                'role-select-icon--placeholder',
                'role-select-icon--student',
                'role-select-icon--staff',
                'role-select-icon--admin'
            );
            const iconInner = iconEl.querySelector('i');
            if (iconInner) iconInner.remove();

            if (role && meta[role]) {
                display.dataset.empty = 'false';
                iconEl.classList.add('role-select-icon--' + role);
                iconEl.innerHTML = `<i class="fa-solid fa-user"></i>`;
                labelEl.textContent = meta[role].label;
                dropdown.querySelectorAll('.role-select-option').forEach(opt => {
                    const selected = opt.dataset.role === role;
                    opt.classList.toggle('role-option--selected', selected);
                    opt.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
            } else {
                display.dataset.empty = 'true';
                iconEl.classList.add('role-select-icon--placeholder');
                iconEl.innerHTML = `<i class="fa-solid fa-user-tag"></i>`;
                labelEl.textContent = 'Select your role';
            }

            if (!silentUpdate) updateAuthFormHeight();
        }

        function closeAllRoleDropdowns(except) {
            document.querySelectorAll('.role-select-dropdown').forEach(dd => {
                if (except && dd === except) return;
                dd.classList.remove('role-select-dropdown--open');
                dd.setAttribute('aria-hidden', 'true');
                const display = document.getElementById(dd.id.replace('Dropdown', 'Display'));
                if (display) display.classList.remove('role-select-display--open');
            });
        }

        document.querySelectorAll('.role-select-display').forEach(display => {
            display.addEventListener('click', event => {
                event.stopPropagation();
                const name = display.id.includes('login') ? 'login' : 'signup';
                const dropdown = document.getElementById(name === 'login' ? 'loginRoleDropdown' : 'signupRoleDropdown');
                const isOpen = dropdown.classList.toggle('role-select-dropdown--open');
                dropdown.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                display.classList.toggle('role-select-display--open', isOpen);
                closeAllRoleDropdowns(isOpen ? dropdown : null);
                updateAuthFormHeight();
            });
        });

        document.querySelectorAll('.role-select-option').forEach(opt => {
            opt.addEventListener('click', event => {
                event.stopPropagation();
                const dropdown = opt.closest('.role-select-dropdown');
                const name = dropdown.id.includes('login') ? 'login' : 'signup';
                setRoleSelection(name, opt.dataset.role);
                closeAllRoleDropdowns(null);
            });
        });

        document.addEventListener('click', () => closeAllRoleDropdowns(null));

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeAllRoleDropdowns(null);
        });

        (function initRolePickers() {
            const loginRole = document.getElementById('login-role');
            if (loginRole && loginRole.value) setRoleSelection('login', loginRole.value, true);
            const signupRole = document.getElementById('signup-role');
            if (signupRole && signupRole.value) setRoleSelection('signup', signupRole.value, true);
        })();

        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', event => {
                const hidden = document.getElementById('login-role');
                if (!hidden || !hidden.value) {
                    event.preventDefault();
                    alert('Please select your role to continue.');
                }
            });
        }
        if (signupForm) {
            signupForm.addEventListener('submit', event => {
                const hidden = document.getElementById('signup-role');
                if (!hidden || !hidden.value) {
                    event.preventDefault();
                    alert('Please select an account type to continue.');
                }
            });
        }
    </script>
</body>
</html>
