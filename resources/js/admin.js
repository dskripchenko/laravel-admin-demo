/**
 * SPA admin entry — host-mount.
 *
 * Все building blocks (Pinia, Router, AdminClient, built-in fields/widgets)
 * собирает createAdminApp() из @dskripchenko/laravel-admin. Дальше это
 * обычный Vue-app, который можно расширить плагинами через onAppCreated.
 */
// (1) UI-кит: tokens + themes + reset + global + Uid*-компоненты.
// Импортируем ДО admin'а, чтобы --uid-* CSS-токены были доступны.
import '@dskripchenko/ui/styles/all.css'
// (2) admin-каркасные стили (impersonation, density, topbar, etc.)
import '@dskripchenko/laravel-admin/style.css'

import { defineAsyncComponent } from 'vue'
import { createAdminApp, registerField } from '@dskripchenko/laravel-admin'

// Темы Quill подключаем eagerly (CSS, ~30 KB) чтобы не было flash
// неcтилизованного поля при первой загрузке wysiwyg.
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import '@vueup/vue-quill/dist/vue-quill.bubble.css'

// Quill сам компонент (~200 KB JS) — lazy: подтягивается только при
// первом рендере wysiwyg-поля. Для index/login/dashboard это даёт
// ощутимое уменьшение initial bundle'а.
const QuillFieldLazy = defineAsyncComponent(() =>
    import('@dskripchenko/laravel-admin/quill').then((m) => m.QuillField),
)

const { app } = createAdminApp(window.__ADMIN_BOOTSTRAP__, {
    onAppCreated: (vueApp) => {
        // ВАЖНО: регистрируем после createAdminApp() — иначе
        // registerBuiltinComponents() внутри фабрики перезатрёт wysiwyg
        // обратно на TextAreaField fallback.
        registerField('wysiwyg', QuillFieldLazy)
        if (import.meta.env.DEV) {
            // eslint-disable-next-line no-console
            console.info('[laravel-admin-demo] admin app initialized (Quill enabled)')
        }
        void vueApp
    },
})

app.mount('#admin-app')
