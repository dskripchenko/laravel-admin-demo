/**
 * SPA admin entry — host-mount.
 *
 * Все building blocks (Pinia, Router, AdminClient, built-in fields/widgets)
 * собирает createAdminApp() из @dskripchenko/laravel-admin. Дальше это
 * обычный Vue-app, который можно расширить плагинами через onAppCreated.
 */
import { createAdminApp } from '@dskripchenko/laravel-admin'
import '@dskripchenko/laravel-admin/style.css'

const { app } = createAdminApp(window.__ADMIN_BOOTSTRAP__, {
    onAppCreated: (vueApp) => {
        // Здесь можно подключить кастомные плагины:
        //   vueApp.use(MyPlugin)
        //   registerField('my-field', MyField)
        // Демо ничего своего не подключает — стандартный JSON-driven рендер.
        if (import.meta.env.DEV) {
            // eslint-disable-next-line no-console
            console.info('[laravel-admin-demo] admin app initialized')
        }
        void vueApp
    },
})

app.mount('#admin-app')
