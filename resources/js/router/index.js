{
    path: '/tecnospeed',
    component: () => import('@/layouts/default/Default.vue'),
    children: [
        {
            path: 'config',
            name: 'TecnospeedConfig',
            component: () => import('@/views/tecnospeed/Config.vue'),
            meta: {
                title: 'Configuração Tecnospeed',
                requiresAuth: true
            }
        },
        {
            path: 'nfes',
            name: 'TecnospeedNFes',
            component: () => import('@/views/tecnospeed/NFes.vue'),
            meta: {
                title: 'NFes',
                requiresAuth: true
            }
        }
    ]
}, 