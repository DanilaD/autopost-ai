import '../css/app.css'
import './bootstrap'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import { createI18n } from 'vue-i18n'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

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
