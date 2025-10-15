<?php

return [
    // Inquiry Management
    'inquiries' => [
        'title' => 'Управление Запросами',
        'email' => 'Электронная почта',
        'ip_address' => 'IP-адрес',
        'user_agent' => 'User Agent',
        'created_at' => 'Создано',
        'delete' => 'Удалить',
        'export' => 'Экспорт CSV',
        'export_button' => 'Экспортировать в CSV',
        'search' => 'Поиск по email...',
        'search_placeholder' => 'Поиск запросов...',
        'no_results' => 'Запросы не найдены',
        'total' => 'Всего Запросов',
        'today' => 'Сегодня',
        'this_week' => 'На Этой Неделе',
        'this_month' => 'В Этом Месяце',
        'delete_confirm' => 'Вы уверены, что хотите удалить этот запрос?',
        'delete_success' => 'Запрос успешно удален',
        'delete_error' => 'Ошибка при удалении запроса',
    ],

    // User Management
    'users' => [
        'title' => 'Управление Пользователями',
        'name' => 'Имя',
        'email' => 'Электронная почта',
        'role' => 'Роль',
        'status' => 'Статус',
        'last_login' => 'Последний Вход',
        'stats' => 'Статистика',
        'actions' => 'Действия',
        'search' => 'Поиск пользователей...',
        'search_placeholder' => 'Поиск по имени или email...',
        'no_results' => 'Пользователи не найдены',
        'total_users' => 'Всего Пользователей',
        'active_users' => 'Активные',
        'suspended_users' => 'Заблокированные',
        'new_this_month' => 'Новых в Этом Месяце',

        // Actions
        'send_password_reset' => 'Отправить Ссылку на Сброс Пароля',
        'suspend' => 'Заблокировать Пользователя',
        'unsuspend' => 'Разблокировать Пользователя',
        'impersonate' => 'Войти как Пользователь',
        'view_details' => 'Просмотреть Детали',

        // Status
        'active' => 'Активен',
        'suspended' => 'Заблокирован',
        'suspended_by' => 'Заблокировал',
        'suspended_on' => 'Заблокирован',
        'suspension_reason' => 'Причина',

        // Stats
        'companies_count' => 'Компании',
        'instagram_accounts' => 'Аккаунты Instagram',
        'posts_count' => 'Публикации',
        'account_age' => 'Возраст Аккаунта',
        'never_logged_in' => 'Никогда не входил',

        // Modals
        'suspend_modal_title' => 'Блокировка Пользователя',
        'suspend_modal_message' => 'Пожалуйста, укажите причину блокировки:',
        'confirm_suspend' => 'Заблокировать',
        'cancel' => 'Отмена',
        'confirm_impersonate_title' => 'Вход как Пользователь',
        'confirm_impersonate_message' => 'Вы собираетесь просматривать приложение от имени этого пользователя. Продолжить?',
        'confirm' => 'Подтвердить',

        // Messages
        'password_reset_sent' => 'Ссылка на сброс пароля отправлена успешно',
        'suspend_success' => 'Пользователь заблокирован успешно',
        'unsuspend_success' => 'Доступ пользователя восстановлен успешно',
        'cannot_suspend_self' => 'Вы не можете заблокировать самого себя',
        'cannot_suspend_admin' => 'Вы не можете заблокировать администратора',
    ],

    // Impersonation
    'impersonating' => 'Вы вошли как',
    'impersonating_as' => 'Вход от имени',
    'stop_impersonation' => 'Остановить Вход',
    'return_to_admin' => 'Вернуться к Аккаунту Администратора',
    'impersonation_started' => 'Теперь вы вошли как пользователь',
    'impersonation_ended' => 'Вход от имени пользователя завершен',

    // Common
    'filter' => 'Фильтр',
    'sort' => 'Сортировка',
    'sort_by' => 'Сортировать по',
    'direction' => 'Направление',
    'ascending' => 'По возрастанию',
    'descending' => 'По убыванию',
    'per_page' => 'На Странице',
    'showing' => 'Показано',
    'to' => 'до',
    'of' => 'из',
    'results' => 'результатов',
    'loading' => 'Загрузка...',
    'no_data' => 'Нет данных',
];
