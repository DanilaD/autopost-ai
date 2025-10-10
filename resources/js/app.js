import '../css/app.css'
import './bootstrap'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import { createI18n } from 'vue-i18n'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Sync LocalStorage locale preference to cookie (so server can read it)
// This is better than using cookies for storage because:
// 1. LocalStorage doesn't send data with every request (better performance)
// 2. We only use the cookie to pass the value to the server
// 3. Primary storage is still LocalStorage (browser-side)
if (typeof window !== 'undefined' && window.localStorage) {
    const preferredLocale = window.localStorage.getItem('preferred_locale')
    if (preferredLocale) {
        // Set cookie with 1 year expiry
        document.cookie = `preferred_locale=${preferredLocale}; path=/; max-age=31536000; SameSite=Lax`
    }
}

// I18n messages
const messages = {
    en: {
        auth: {
            email: 'Email Address',
            enter_email: 'Enter your email',
            continue: 'Continue',
            welcome_back: 'Welcome back!',
            new_here: "New here? We'll create your account.",
            login: 'Login',
            register: 'Register',
            password: 'Password',
            remember_me: 'Remember me',
            forgot_password: 'Forgot your password?',
            secure_auth: 'Secure authentication powered by Autopost AI',
        },
        dashboard: {
            title: 'Dashboard',
            greeting_morning: 'Good morning',
            greeting_afternoon: 'Good afternoon',
            greeting_evening: 'Good evening',
            welcome_message:
                "Welcome to Autopost AI. Let's make something amazing today.",
            instagram_accounts: 'Instagram Accounts',
            scheduled_posts: 'Scheduled Posts',
            wallet_balance: 'Wallet Balance',
            connect_instagram: 'Connect Instagram',
            connect_instagram_desc:
                'Link your Instagram account to start automating your content.',
            connect_now: 'Connect Now',
            create_post: 'Create Post',
            create_post_desc:
                'Schedule and publish content across your Instagram accounts.',
            coming_soon: 'Coming Soon',
        },
        menu: {
            dashboard: 'Dashboard',
            profile: 'Profile',
            logout: 'Log Out',
            instagram: 'Instagram',
            instagram_accounts: 'Instagram Accounts',
        },
        instagram: {
            title: 'Instagram Accounts',
            no_accounts: 'No Instagram accounts connected yet',
            connect_description:
                'Connect your Instagram account to start automating your content publishing.',
            connect_button: 'Connect Instagram Account',
            not_configured:
                'Instagram integration is not configured yet. Please contact your administrator to set up Instagram API credentials.',
            no_active_company:
                'You need to have an active company to connect Instagram accounts. Please create or select a company first.',
        },
    },
    ru: {
        auth: {
            email: 'Электронная почта',
            enter_email: 'Введите вашу электронную почту',
            continue: 'Продолжить',
            welcome_back: 'С возвращением!',
            new_here: 'Новый пользователь? Мы создадим ваш аккаунт.',
            login: 'Войти',
            register: 'Регистрация',
            password: 'Пароль',
            remember_me: 'Запомнить меня',
            forgot_password: 'Забыли пароль?',
            secure_auth: 'Безопасная аутентификация от Autopost AI',
        },
        dashboard: {
            title: 'Панель управления',
            greeting_morning: 'Доброе утро',
            greeting_afternoon: 'Добрый день',
            greeting_evening: 'Добрый вечер',
            welcome_message:
                'Добро пожаловать в Autopost AI. Давайте создадим что-то удивительное сегодня.',
            instagram_accounts: 'Аккаунты Instagram',
            scheduled_posts: 'Запланированные публикации',
            wallet_balance: 'Баланс кошелька',
            connect_instagram: 'Подключить Instagram',
            connect_instagram_desc:
                'Свяжите свой аккаунт Instagram, чтобы начать автоматизацию контента.',
            connect_now: 'Подключить сейчас',
            create_post: 'Создать публикацию',
            create_post_desc:
                'Планируйте и публикуйте контент в ваших аккаунтах Instagram.',
            coming_soon: 'Скоро',
        },
        menu: {
            dashboard: 'Панель управления',
            profile: 'Профиль',
            logout: 'Выйти',
            instagram: 'Instagram',
            instagram_accounts: 'Аккаунты Instagram',
        },
        instagram: {
            title: 'Аккаунты Instagram',
            no_accounts: 'Аккаунты Instagram еще не подключены',
            connect_description:
                'Подключите свой аккаунт Instagram, чтобы начать автоматизацию публикации контента.',
            connect_button: 'Подключить аккаунт Instagram',
            not_configured:
                'Интеграция с Instagram еще не настроена. Пожалуйста, свяжитесь с администратором для настройки учетных данных Instagram API.',
            no_active_company:
                'Для подключения аккаунтов Instagram необходима активная компания. Пожалуйста, создайте или выберите компанию.',
        },
    },
    es: {
        auth: {
            email: 'Correo electrónico',
            enter_email: 'Ingrese su correo electrónico',
            continue: 'Continuar',
            welcome_back: '¡Bienvenido de nuevo!',
            new_here: '¿Nuevo aquí? Crearemos tu cuenta.',
            login: 'Iniciar sesión',
            register: 'Registrarse',
            password: 'Contraseña',
            remember_me: 'Recuérdame',
            forgot_password: '¿Olvidaste tu contraseña?',
            secure_auth: 'Autenticación segura con Autopost AI',
        },
        dashboard: {
            title: 'Panel de control',
            greeting_morning: 'Buenos días',
            greeting_afternoon: 'Buenas tardes',
            greeting_evening: 'Buenas noches',
            welcome_message:
                'Bienvenido a Autopost AI. Hagamos algo increíble hoy.',
            instagram_accounts: 'Cuentas de Instagram',
            scheduled_posts: 'Publicaciones programadas',
            wallet_balance: 'Saldo de la cartera',
            connect_instagram: 'Conectar Instagram',
            connect_instagram_desc:
                'Vincula tu cuenta de Instagram para comenzar a automatizar tu contenido.',
            connect_now: 'Conectar ahora',
            create_post: 'Crear publicación',
            create_post_desc:
                'Programa y publica contenido en tus cuentas de Instagram.',
            coming_soon: 'Próximamente',
        },
        menu: {
            dashboard: 'Panel de control',
            profile: 'Perfil',
            logout: 'Cerrar sesión',
            instagram: 'Instagram',
            instagram_accounts: 'Cuentas de Instagram',
        },
        instagram: {
            title: 'Cuentas de Instagram',
            no_accounts: 'Aún no hay cuentas de Instagram conectadas',
            connect_description:
                'Conecta tu cuenta de Instagram para comenzar a automatizar la publicación de contenido.',
            connect_button: 'Conectar cuenta de Instagram',
            not_configured:
                'La integración con Instagram aún no está configurada. Por favor, contacta con tu administrador para configurar las credenciales de la API de Instagram.',
            no_active_company:
                'Necesitas tener una empresa activa para conectar cuentas de Instagram. Por favor, crea o selecciona una empresa primero.',
        },
    },
}

// Get locale (will be updated from Inertia page props)
const getInitialLocale = () => {
    // Try URL first
    const path = window.location.pathname
    const urlLocale = path.split('/')[1]
    if (['en', 'ru', 'es'].includes(urlLocale)) {
        return urlLocale
    }
    // Default to English
    return 'en'
}

const i18n = createI18n({
    legacy: false,
    locale: getInitialLocale(),
    fallbackLocale: 'en',
    messages,
})

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n)

        // Sync i18n locale with Inertia page props
        if (props.initialPage?.props?.locale) {
            i18n.global.locale.value = props.initialPage.props.locale
        }

        return app.mount(el)
    },
    progress: {
        color: '#4B5563',
    },
})
