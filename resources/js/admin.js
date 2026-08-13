/**
 * The SPA admin entry — the host mount.
 *
 * createAdminApp() from @dskripchenko/laravel-admin assembles Pinia, the router
 * and the AdminClient, and registers the built-in fields, widgets and infolist
 * entries.
 *
 * WYSIWYG: since v1.2.3 the core uses @dskripchenko/wysiwyg as the default
 * (dependency-free, about 7 KB gzipped). For Quill or Tinymce, uncomment the
 * registerField block below.
 */
// (1) The UI kit: the tokens, the themes, the reset, the globals and the Uid*
// components.
import '@dskripchenko/ui/styles/all.css'
// (2) The admin shell styles
import '@dskripchenko/laravel-admin/style.css'
// (3) The WYSIWYG (default) styles — the JS itself is pulled in through the core's WysiwygField.
import '@dskripchenko/wysiwyg/style.css'

import { createAdminApp } from '@dskripchenko/laravel-admin'

const { app } = createAdminApp(window.__ADMIN_BOOTSTRAP__, {
    onAppCreated: (vueApp) => {
        // Optional: switch the wysiwyg over to Quill or Tinymce.
        // -------------------------------------------------
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
