export default function buildRoute(name, params = null) {
    const routes = {
        laundry: () => `/user/laundry`,
        ironing: () => `/user/iron`,
        complete_transaction: (slug) => `/user/complete-transaction/${slug}`,
        transaction: (slug) => `/user/transaction/${slug}`,
        cancel_order: () => `/user/cancel-order`,
        item_types_delete: (id) => `/admin/item-types/${id}`, 
        item_types_update: () => `/admin/item-types`, 
        feedback_admin_delete: (id) => `/admin/feedback/${id}`,
        admin_transaction_delete: (id) => `/admin/transaction/${id}`,
        admin_user_delete: (id) => `/admin/users/${id}`,
        manage_users_update: () => `/admin/users`,
        ironing_delete: (id) => `/admin/ironing/${id}`,
        ironing_admin_update: () => `/admin/ironing`,
        laundry_delete: (id) => `/admin/laundry/${id}`,
        laundry_admin_update: () => `/admin/laundry`,
        admin_print: ([type, service] = params) => `/admin/print?type=${type}&service=${service}`,
    };

    return routes[name] ? routes[name](params) : null;
}

