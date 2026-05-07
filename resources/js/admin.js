/**
 * SPA admin entry — host-mount.
 *
 * createAdminApp() из @dskripchenko/laravel-admin собирает Pinia, Router,
 * AdminClient + регистрирует built-in fields/widgets/infolist.
 *
 * WYSIWYG: с v1.2.3 core использует @dskripchenko/wysiwyg как default
 * (zero-dep, ~7 KB gz). Если хотите Quill/Tinymce — раскомментируйте
 * блок registerField ниже.
 */
// (1) UI-кит: tokens + themes + reset + global + Uid*-компоненты.
import '@dskripchenko/ui/styles/all.css'
// (2) admin-каркасные стили
import '@dskripchenko/laravel-admin/style.css'
// (3) WYSIWYG (default) стили — сам JS подтягивается через core's WysiwygField.
import '@dskripchenko/wysiwyg/style.css'

import { createAdminApp } from '@dskripchenko/laravel-admin'

const { app } = createAdminApp(window.__ADMIN_BOOTSTRAP__, {
    onAppCreated: (vueApp) => {
        // Optional: переключить wysiwyg на Quill/Tinymce.
        // ─────────────────────────────────────────────────
        // import { defineAsyncComponent } from 'vue'
        // import { registerField } from '@dskripchenko/laravel-admin'
        // import '@vueup/vue-quill/dist/vue-quill.snow.css'
        // const QuillField = defineAsyncComponent(() =>
        //     import('@dskripchenko/laravel-admin/quill').then((m) => m.QuillField),
        // )
        // registerField('wysiwyg', QuillField)
        if (import.meta.env.DEV) {
            // eslint-disable-next-line no-console
            console.info('[laravel-admin-demo] admin app initialized')
        }
        void vueApp
    },
})

app.mount('#admin-app')
