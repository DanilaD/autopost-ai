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
            forgot_password_title: 'Forgot Password',
            forgot_password_description:
                "Forgot your password? No problem.\nEnter your email below, and we'll send you a link to reset it securely.",
            email_password_reset_link: 'Email Password Reset Link',
        },
        dashboard: {
            title: 'Dashboard',
            greeting_morning: 'Good morning',
            greeting_afternoon: 'Good afternoon',
            greeting_evening: 'Good evening',
            welcome_messages: [
                "Welcome to Autopost AI. Let's make something amazing today.",
                "Ready to automate your social media? Let's get started!",
                "Your content deserves to shine. Let's make it happen.",
                'Time to turn your ideas into engaging posts.',
                'Great content starts here. What will you create today?',
                "Let's grow your Instagram presence together.",
                'Welcome back! Your audience is waiting for your next post.',
            ],
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
            empty_state: {
                no_posts: 'No posts yet',
                get_started: 'Get started by creating your first post',
            },
        },
        menu: {
            dashboard: 'Dashboard',
            profile: 'Profile',
            logout: 'Log Out',
            instagram: 'Instagram',
            instagram_accounts: 'Instagram Accounts',
            admin: 'Administration',
            inquiries: 'Inquiries',
            users: 'Users',
        },
        theme: {
            switchToDark: 'Switch to dark mode',
            switchToLight: 'Switch to light mode',
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
            connected: 'Connected',
            sync: 'Sync',
            disconnect: 'Disconnect',
            disconnect_confirm:
                'Are you sure you want to disconnect @{username}? You can reconnect it anytime.',
            disconnect_confirm_title: 'Disconnect Instagram Account?',
            disconnect_confirm_message:
                'Are you sure you want to disconnect {username}? You can reconnect it anytime.',
            disconnect_button: 'Yes, Disconnect',
            disconnect_cancel: 'Cancel',
            status_active: 'Active',
            status_expired: 'Expired',
            status_expiring_soon: 'Expiring Soon',
            token_warning:
                'Your access token will expire soon. Please reconnect this account to continue posting.',
        },
        profile: {
            title: 'Profile',
            information: {
                title: 'Profile Information',
                description:
                    "Update your account's profile information and email address.",
                name: 'Name',
                email: 'Email',
                timezone: 'Timezone',
                timezone_description:
                    'All dates and times will be displayed in your selected timezone.',
                search_timezone: 'Search timezones...',
                common_timezones: '🌟 Quick Select (USA, Canada & Key Cities)',
                all_timezones: '🌍 All Timezones',
                unverified_email: 'Your email address is unverified.',
                resend_verification:
                    'Click here to re-send the verification email.',
                verification_sent:
                    'A new verification link has been sent to your email address.',
                save: 'Save',
                saved: 'Saved.',
            },
            password: {
                title: 'Update Password',
                description:
                    'Ensure your account is using a long, random password to stay secure.',
                current_password: 'Current Password',
                new_password: 'New Password',
                confirm_password: 'Confirm Password',
                save: 'Save',
                saved: 'Saved.',
            },
            company: {
                title: 'Company Information',
                description: 'Your current company and team details.',
                no_company:
                    'You are not currently associated with any company.',
                member_since: 'Member since',
                role_admin: 'Administrator',
                role_user: 'User',
                role_network: 'Network Manager',
                member_singular: 'Team Member',
                member_plural: 'Team Members',
                instagram_account_singular: 'Instagram Account',
                instagram_account_plural: 'Instagram Accounts',
                manage_accounts: 'Manage Instagram Accounts',
            },
            delete: {
                title: 'Delete Account',
                description:
                    'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.',
                button: 'Delete Account',
                confirm_title: 'Are you sure you want to delete your account?',
                confirm_description:
                    'Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
                password: 'Password',
                password_placeholder: 'Password',
                cancel: 'Cancel',
                confirm_button: 'Delete Account',
            },
        },
        timezone: {
            your_timezone: 'Your Timezone',
            current_time: 'Current Time',
            click_to_change: 'Click to change timezone',
            timezone: 'Timezone',
            local_time: 'Local Time',
        },
        admin: {
            inquiries: {
                title: 'Inquiry Management',
                email: 'Email',
                ip_address: 'IP Address',
                user_agent: 'User Agent',
                created_at: 'Created At',
                delete: 'Delete',
                export: 'Export CSV',
                export_button: 'Export to CSV',
                search: 'Search by email...',
                search_placeholder: 'Search inquiries...',
                no_results: 'No inquiries found',
                total: 'Total Inquiries',
                today: 'Today',
                this_week: 'This Week',
                this_month: 'This Month',
                delete_confirm_title: 'Delete Inquiry?',
                delete_confirm_message:
                    'This action cannot be undone. The inquiry will be permanently deleted.',
                confirm_delete: 'Yes, delete it',
                cancel: 'Cancel',
                deleted_success: 'Deleted!',
                deleted_message: 'Inquiry has been deleted successfully.',
                delete_error: 'Error!',
                delete_error_message:
                    'Failed to delete inquiry. Please try again.',
                tooltip_total: 'Total number of inquiries received',
                tooltip_today: 'Inquiries received today',
                tooltip_this_week: 'Inquiries received this week',
                tooltip_this_month: 'Inquiries received this month',
            },
            users: {
                title: 'User Management',
                name: 'Name',
                email: 'Email',
                role: 'Role',
                status: 'Status',
                last_login: 'Last Login',
                stats: 'Statistics',
                actions: 'Actions',
                search: 'Search users...',
                search_placeholder: 'Search by name or email...',
                no_results: 'No users found',
                total_users: 'Total Users',
                active_users: 'Active',
                suspended_users: 'Suspended',
                new_this_month: 'New This Month',
                send_password_reset: 'Send Password Reset',
                suspend: 'Suspend User',
                unsuspend: 'Unsuspend User',
                impersonate: 'Impersonate',
                active: 'Active',
                suspended: 'Suspended',
                suspension_reason: 'Reason',
                never_logged_in: 'Never logged in',
                companies_count: 'Companies',
                instagram_accounts: 'Instagram Accounts',
                posts_count: 'Posts',
                suspend_modal_title: 'Suspend User',
                suspend_modal_message:
                    'Please provide a reason for suspension:',
                confirm_suspend: 'Suspend',
                cancel: 'Cancel',
                confirm_impersonate_title: 'Impersonate User',
                confirm_impersonate_message:
                    'You will be logged in as this user. You can stop impersonation at any time.',
                confirm_password_reset_title: 'Send Password Reset?',
                confirm_password_reset_message:
                    'A password reset link will be sent to',
                confirm_unsuspend_title: 'Unsuspend User?',
                confirm_unsuspend_message:
                    'This user will be able to log in again.',
                confirm: 'Confirm',
                success: 'Success!',
                error: 'Error!',
                password_reset_sent:
                    'Password reset link has been sent successfully',
                user_suspended: 'User has been suspended',
                user_unsuspended: 'User has been unsuspended',
                impersonation_started: 'Now impersonating user',
                action_failed: 'Failed to complete action. Please try again.',
                tooltip_total_users: 'Total number of registered users',
                tooltip_active_users: 'Users who are active and not suspended',
                tooltip_suspended_users:
                    'Users who have been suspended by admins',
                tooltip_new_this_month: 'New users registered this month',
            },
            impersonating: 'You are impersonating',
            stop_impersonation: 'Stop Impersonation',
            loading: 'Loading...',
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
            forgot_password_title: 'Забыли пароль',
            forgot_password_description:
                'Забыли пароль? Не проблема.\nВведите ваш email ниже, и мы отправим вам ссылку для безопасного сброса пароля.',
            email_password_reset_link: 'Отправить ссылку для сброса пароля',
        },
        dashboard: {
            title: 'Панель управления',
            greeting_morning: 'Доброе утро',
            greeting_afternoon: 'Добрый день',
            greeting_evening: 'Добрый вечер',
            welcome_messages: [
                'Добро пожаловать в Autopost AI. Давайте создадим что-то удивительное сегодня.',
                'Готовы автоматизировать социальные сети? Начнём!',
                'Ваш контент заслуживает внимания. Давайте воплотим это в жизнь.',
                'Время превратить ваши идеи в увлекательные публикации.',
                'Отличный контент начинается здесь. Что вы создадите сегодня?',
                'Давайте вместе развивать ваше присутствие в Instagram.',
                'С возвращением! Ваша аудитория ждёт вашу следующую публикацию.',
            ],
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
            empty_state: {
                no_posts: 'Пока нет публикаций',
                get_started: 'Начните с создания вашей первой публикации',
            },
        },
        menu: {
            dashboard: 'Панель управления',
            profile: 'Профиль',
            logout: 'Выйти',
            instagram: 'Instagram',
            instagram_accounts: 'Аккаунты Instagram',
            admin: 'Администрирование',
            inquiries: 'Запросы',
            users: 'Пользователи',
        },
        theme: {
            switchToDark: 'Переключить на темную тему',
            switchToLight: 'Переключить на светлую тему',
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
            connected: 'Подключен',
            sync: 'Синхронизировать',
            disconnect: 'Отключить',
            disconnect_confirm:
                'Вы уверены, что хотите отключить @{username}? Вы можете переподключить его в любое время.',
            disconnect_confirm_title: 'Отключить аккаунт Instagram?',
            disconnect_confirm_message:
                'Вы уверены, что хотите отключить {username}? Вы можете переподключить его в любое время.',
            disconnect_button: 'Да, отключить',
            disconnect_cancel: 'Отмена',
            status_active: 'Активен',
            status_expired: 'Истек',
            status_expiring_soon: 'Скоро истечет',
            token_warning:
                'Ваш токен доступа скоро истечет. Пожалуйста, переподключите этот аккаунт для продолжения публикаций.',
        },
        profile: {
            title: 'Профиль',
            information: {
                title: 'Информация профиля',
                description:
                    'Обновите информацию вашего профиля и адрес электронной почты.',
                name: 'Имя',
                email: 'Электронная почта',
                timezone: 'Часовой пояс',
                timezone_description:
                    'Все даты и время будут отображаться в выбранном часовом поясе.',
                search_timezone: 'Поиск часовых поясов...',
                common_timezones:
                    '🌟 Быстрый выбор (США, Канада и Ключевые города)',
                all_timezones: '🌍 Все часовые пояса',
                unverified_email: 'Ваш адрес электронной почты не подтвержден.',
                resend_verification:
                    'Нажмите здесь, чтобы повторно отправить письмо с подтверждением.',
                verification_sent:
                    'Новая ссылка для подтверждения отправлена на ваш адрес электронной почты.',
                save: 'Сохранить',
                saved: 'Сохранено.',
            },
            password: {
                title: 'Обновить пароль',
                description:
                    'Убедитесь, что ваша учетная запись использует длинный случайный пароль для обеспечения безопасности.',
                current_password: 'Текущий пароль',
                new_password: 'Новый пароль',
                confirm_password: 'Подтвердите пароль',
                save: 'Сохранить',
                saved: 'Сохранено.',
            },
            company: {
                title: 'Информация о компании',
                description: 'Информация о вашей текущей компании и команде.',
                no_company:
                    'Вы в настоящее время не связаны ни с одной компанией.',
                member_since: 'Участник с',
                role_admin: 'Администратор',
                role_user: 'Пользователь',
                role_network: 'Менеджер сети',
                member_singular: 'Участник команды',
                member_plural: 'Участники команды',
                instagram_account_singular: 'Аккаунт Instagram',
                instagram_account_plural: 'Аккаунты Instagram',
                manage_accounts: 'Управлять аккаунтами Instagram',
            },
            delete: {
                title: 'Удалить аккаунт',
                description:
                    'После удаления вашей учетной записи все ее ресурсы и данные будут удалены навсегда. Перед удалением учетной записи загрузите все данные или информацию, которую хотите сохранить.',
                button: 'Удалить аккаунт',
                confirm_title:
                    'Вы уверены, что хотите удалить свою учетную запись?',
                confirm_description:
                    'После удаления вашей учетной записи все ее ресурсы и данные будут удалены навсегда. Пожалуйста, введите свой пароль для подтверждения окончательного удаления учетной записи.',
                password: 'Пароль',
                password_placeholder: 'Пароль',
                cancel: 'Отмена',
                confirm_button: 'Удалить аккаунт',
            },
        },
        timezone: {
            your_timezone: 'Ваш часовой пояс',
            current_time: 'Текущее время',
            click_to_change: 'Нажмите, чтобы изменить часовой пояс',
            timezone: 'Часовой пояс',
            local_time: 'Местное время',
        },
        admin: {
            inquiries: {
                title: 'Управление Запросами',
                email: 'Электронная почта',
                ip_address: 'IP-адрес',
                user_agent: 'User Agent',
                created_at: 'Создано',
                delete: 'Удалить',
                export: 'Экспорт CSV',
                export_button: 'Экспортировать в CSV',
                search: 'Поиск по email...',
                search_placeholder: 'Поиск запросов...',
                no_results: 'Запросы не найдены',
                total: 'Всего Запросов',
                today: 'Сегодня',
                this_week: 'На Этой Неделе',
                this_month: 'В Этом Месяце',
                delete_confirm_title: 'Удалить Запрос?',
                delete_confirm_message:
                    'Это действие нельзя отменить. Запрос будет удалён навсегда.',
                confirm_delete: 'Да, удалить',
                cancel: 'Отмена',
                deleted_success: 'Удалено!',
                deleted_message: 'Запрос успешно удалён.',
                delete_error: 'Ошибка!',
                delete_error_message:
                    'Не удалось удалить запрос. Попробуйте снова.',
                tooltip_total: 'Общее количество полученных запросов',
                tooltip_today: 'Запросы, полученные сегодня',
                tooltip_this_week: 'Запросы, полученные на этой неделе',
                tooltip_this_month: 'Запросы, полученные в этом месяце',
            },
            users: {
                title: 'Управление Пользователями',
                name: 'Имя',
                email: 'Электронная почта',
                role: 'Роль',
                status: 'Статус',
                last_login: 'Последний Вход',
                stats: 'Статистика',
                actions: 'Действия',
                search: 'Поиск пользователей...',
                search_placeholder: 'Поиск по имени или email...',
                no_results: 'Пользователи не найдены',
                total_users: 'Всего Пользователей',
                active_users: 'Активные',
                suspended_users: 'Заблокированные',
                new_this_month: 'Новых в Этом Месяце',
                send_password_reset: 'Отправить Ссылку на Сброс Пароля',
                suspend: 'Заблокировать Пользователя',
                unsuspend: 'Разблокировать Пользователя',
                impersonate: 'Войти как Пользователь',
                active: 'Активен',
                suspended: 'Заблокирован',
                suspension_reason: 'Причина',
                never_logged_in: 'Никогда не входил',
                companies_count: 'Компании',
                instagram_accounts: 'Аккаунты Instagram',
                posts_count: 'Публикации',
                suspend_modal_title: 'Блокировка Пользователя',
                suspend_modal_message:
                    'Пожалуйста, укажите причину блокировки:',
                confirm_suspend: 'Заблокировать',
                cancel: 'Отмена',
                confirm_impersonate_title: 'Вход как Пользователь',
                confirm_impersonate_message:
                    'Вы войдёте как этот пользователь. Вы можете остановить вход в любое время.',
                confirm_password_reset_title: 'Отправить Сброс Пароля?',
                confirm_password_reset_message:
                    'Ссылка для сброса пароля будет отправлена на',
                confirm_unsuspend_title: 'Разблокировать Пользователя?',
                confirm_unsuspend_message:
                    'Этот пользователь снова сможет войти в систему.',
                confirm: 'Подтвердить',
                success: 'Успешно!',
                error: 'Ошибка!',
                password_reset_sent:
                    'Ссылка для сброса пароля успешно отправлена',
                user_suspended: 'Пользователь заблокирован',
                user_unsuspended: 'Пользователь разблокирован',
                impersonation_started: 'Теперь вы работаете как пользователь',
                action_failed:
                    'Не удалось выполнить действие. Попробуйте снова.',
                tooltip_total_users:
                    'Общее количество зарегистрированных пользователей',
                tooltip_active_users:
                    'Пользователи, которые активны и не заблокированы',
                tooltip_suspended_users:
                    'Пользователи, которые были заблокированы администраторами',
                tooltip_new_this_month:
                    'Новые пользователи, зарегистрированные в этом месяце',
            },
            impersonating: 'Вы вошли как',
            stop_impersonation: 'Остановить Вход',
            loading: 'Загрузка...',
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
            forgot_password_title: 'Olvidé mi contraseña',
            forgot_password_description:
                '¿Olvidaste tu contraseña? No hay problema.\nIngresa tu email a continuación, y te enviaremos un enlace para restablecerla de forma segura.',
            email_password_reset_link: 'Enviar enlace de restablecimiento',
        },
        dashboard: {
            title: 'Panel de control',
            greeting_morning: 'Buenos días',
            greeting_afternoon: 'Buenas tardes',
            greeting_evening: 'Buenas noches',
            welcome_messages: [
                'Bienvenido a Autopost AI. Hagamos algo increíble hoy.',
                '¿Listo para automatizar tus redes sociales? ¡Comencemos!',
                'Tu contenido merece brillar. Hagámoslo realidad.',
                'Es hora de convertir tus ideas en publicaciones atractivas.',
                'El gran contenido comienza aquí. ¿Qué crearás hoy?',
                'Crezcamos juntos tu presencia en Instagram.',
                '¡Bienvenido de nuevo! Tu audiencia espera tu próxima publicación.',
            ],
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
            empty_state: {
                no_posts: 'Aún no hay publicaciones',
                get_started: 'Comienza creando tu primera publicación',
            },
        },
        menu: {
            dashboard: 'Panel de control',
            profile: 'Perfil',
            logout: 'Cerrar sesión',
            instagram: 'Instagram',
            instagram_accounts: 'Cuentas de Instagram',
            admin: 'Administración',
            inquiries: 'Consultas',
            users: 'Usuarios',
        },
        theme: {
            switchToDark: 'Cambiar a modo oscuro',
            switchToLight: 'Cambiar a modo claro',
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
            connected: 'Conectado',
            sync: 'Sincronizar',
            disconnect: 'Desconectar',
            disconnect_confirm:
                '¿Estás seguro de que quieres desconectar @{username}? Puedes reconectarlo en cualquier momento.',
            disconnect_confirm_title: '¿Desconectar cuenta de Instagram?',
            disconnect_confirm_message:
                '¿Estás seguro de que quieres desconectar {username}? Puedes reconectarla en cualquier momento.',
            disconnect_button: 'Sí, desconectar',
            disconnect_cancel: 'Cancelar',
            status_active: 'Activo',
            status_expired: 'Expirado',
            status_expiring_soon: 'Por expirar',
            token_warning:
                'Tu token de acceso expirará pronto. Por favor, vuelve a conectar esta cuenta para continuar publicando.',
        },
        profile: {
            title: 'Perfil',
            information: {
                title: 'Información del Perfil',
                description:
                    'Actualiza la información de tu cuenta y dirección de correo electrónico.',
                name: 'Nombre',
                email: 'Correo Electrónico',
                timezone: 'Zona Horaria',
                timezone_description:
                    'Todas las fechas y horas se mostrarán en la zona horaria seleccionada.',
                search_timezone: 'Buscar zonas horarias...',
                common_timezones:
                    '🌟 Selección Rápida (EE.UU., Canadá y Ciudades Clave)',
                all_timezones: '🌍 Todas las Zonas Horarias',
                unverified_email:
                    'Tu dirección de correo electrónico no está verificada.',
                resend_verification:
                    'Haz clic aquí para reenviar el correo de verificación.',
                verification_sent:
                    'Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.',
                save: 'Guardar',
                saved: 'Guardado.',
            },
            password: {
                title: 'Actualizar Contraseña',
                description:
                    'Asegúrate de que tu cuenta utiliza una contraseña larga y aleatoria para mantener la seguridad.',
                current_password: 'Contraseña Actual',
                new_password: 'Nueva Contraseña',
                confirm_password: 'Confirmar Contraseña',
                save: 'Guardar',
                saved: 'Guardado.',
            },
            company: {
                title: 'Información de la empresa',
                description: 'Detalles de tu empresa actual y equipo.',
                no_company:
                    'Actualmente no estás asociado con ninguna empresa.',
                member_since: 'Miembro desde',
                role_admin: 'Administrador',
                role_user: 'Usuario',
                role_network: 'Gerente de red',
                member_singular: 'Miembro del equipo',
                member_plural: 'Miembros del equipo',
                instagram_account_singular: 'Cuenta de Instagram',
                instagram_account_plural: 'Cuentas de Instagram',
                manage_accounts: 'Gestionar cuentas de Instagram',
            },
            delete: {
                title: 'Eliminar Cuenta',
                description:
                    'Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Antes de eliminar tu cuenta, descarga cualquier dato o información que desees conservar.',
                button: 'Eliminar Cuenta',
                confirm_title:
                    '¿Estás seguro de que quieres eliminar tu cuenta?',
                confirm_description:
                    'Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Por favor, ingresa tu contraseña para confirmar que deseas eliminar permanentemente tu cuenta.',
                password: 'Contraseña',
                password_placeholder: 'Contraseña',
                cancel: 'Cancelar',
                confirm_button: 'Eliminar Cuenta',
            },
        },
        timezone: {
            your_timezone: 'Tu Zona Horaria',
            current_time: 'Hora Actual',
            click_to_change: 'Haz clic para cambiar la zona horaria',
            timezone: 'Zona Horaria',
            local_time: 'Hora Local',
        },
        admin: {
            inquiries: {
                title: 'Gestión de Consultas',
                email: 'Correo electrónico',
                ip_address: 'Dirección IP',
                user_agent: 'Agente de Usuario',
                created_at: 'Creado el',
                delete: 'Eliminar',
                export: 'Exportar CSV',
                export_button: 'Exportar a CSV',
                search: 'Buscar por correo...',
                search_placeholder: 'Buscar consultas...',
                no_results: 'No se encontraron consultas',
                total: 'Total de Consultas',
                today: 'Hoy',
                this_week: 'Esta Semana',
                this_month: 'Este Mes',
                delete_confirm_title: '¿Eliminar Consulta?',
                delete_confirm_message:
                    'Esta acción no se puede deshacer. La consulta se eliminará permanentemente.',
                confirm_delete: 'Sí, eliminarla',
                cancel: 'Cancelar',
                deleted_success: '¡Eliminado!',
                deleted_message: 'La consulta se ha eliminado correctamente.',
                delete_error: '¡Error!',
                delete_error_message:
                    'No se pudo eliminar la consulta. Por favor, inténtelo de nuevo.',
                tooltip_total: 'Número total de consultas recibidas',
                tooltip_today: 'Consultas recibidas hoy',
                tooltip_this_week: 'Consultas recibidas esta semana',
                tooltip_this_month: 'Consultas recibidas este mes',
            },
            users: {
                title: 'Gestión de Usuarios',
                name: 'Nombre',
                email: 'Correo electrónico',
                role: 'Rol',
                status: 'Estado',
                last_login: 'Último Acceso',
                stats: 'Estadísticas',
                actions: 'Acciones',
                search: 'Buscar usuarios...',
                search_placeholder: 'Buscar por nombre o correo...',
                no_results: 'No se encontraron usuarios',
                total_users: 'Total de Usuarios',
                active_users: 'Activos',
                suspended_users: 'Suspendidos',
                new_this_month: 'Nuevos Este Mes',
                send_password_reset: 'Enviar Restablecimiento de Contraseña',
                suspend: 'Suspender Usuario',
                unsuspend: 'Reactivar Usuario',
                impersonate: 'Suplantar',
                active: 'Activo',
                suspended: 'Suspendido',
                suspension_reason: 'Razón',
                never_logged_in: 'Nunca ha iniciado sesión',
                companies_count: 'Empresas',
                instagram_accounts: 'Cuentas de Instagram',
                posts_count: 'Publicaciones',
                suspend_modal_title: 'Suspender Usuario',
                suspend_modal_message:
                    'Por favor, proporcione una razón para la suspensión:',
                confirm_suspend: 'Suspender',
                cancel: 'Cancelar',
                confirm_impersonate_title: 'Suplantar Usuario',
                confirm_impersonate_message:
                    'Iniciará sesión como este usuario. Puede detener la suplantación en cualquier momento.',
                confirm_password_reset_title:
                    '¿Enviar Restablecimiento de Contraseña?',
                confirm_password_reset_message:
                    'Se enviará un enlace de restablecimiento de contraseña a',
                confirm_unsuspend_title: '¿Reactivar Usuario?',
                confirm_unsuspend_message:
                    'Este usuario podrá iniciar sesión nuevamente.',
                confirm: 'Confirmar',
                success: '¡Éxito!',
                error: '¡Error!',
                password_reset_sent:
                    'Enlace de restablecimiento de contraseña enviado correctamente',
                user_suspended: 'Usuario suspendido',
                user_unsuspended: 'Usuario reactivado',
                impersonation_started: 'Ahora está suplantando al usuario',
                action_failed:
                    'No se pudo completar la acción. Por favor, inténtelo de nuevo.',
                tooltip_total_users: 'Número total de usuarios registrados',
                tooltip_active_users:
                    'Usuarios que están activos y no suspendidos',
                tooltip_suspended_users:
                    'Usuarios que han sido suspendidos por administradores',
                tooltip_new_this_month: 'Nuevos usuarios registrados este mes',
            },
            impersonating: 'Está suplantando a',
            stop_impersonation: 'Detener Suplantación',
            loading: 'Cargando...',
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
