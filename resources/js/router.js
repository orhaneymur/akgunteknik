import { createRouter, createWebHistory } from 'vue-router';
import AppLayout from './layouts/AppLayout.vue';
import Login from './pages/auth/Login.vue';
import ProductList from './pages/inventory/ProductList.vue';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login
    },
    {
        path: '/',
        component: AppLayout,
        children: [
            {
                path: 'dashboard',
                name: 'Dashboard',
                component: () => import('./pages/Dashboard.vue')
            },
            {
                path: 'products',
                name: 'ProductList',
                component: ProductList
            },
            {
                path: 'sales/create',
                name: 'SalesCreate',
                component: () => import('./pages/sales/SalesCreate.vue')
            },
            {
                path: 'sales',
                name: 'SalesList',
                component: () => import('./pages/sales/SalesList.vue')
            },
            {
                path: 'customers',
                name: 'CustomerList',
                component: () => import('./pages/customer/CustomerList.vue')
            },
            {
                path: 'quotes',
                name: 'QuoteList',
                component: () => import('./pages/sales/QuoteList.vue')
            },
            {
                path: 'quotes/create',
                name: 'QuoteCreate',
                component: () => import('./pages/sales/QuoteForm.vue')
            },
            {
                path: 'finance/expenses',
                name: 'FinanceExpenses',
                component: () => import('./pages/finance/ExpenseList.vue')
            },
            {
                path: 'finance/safes',
                name: 'FinanceSafes',
                component: () => import('./pages/finance/SafeList.vue')
            },
            {
                path: 'finance/categories',
                name: 'FinanceCategories',
                component: () => import('./pages/finance/CategoryList.vue')
            },
            {
                path: 'transfers',
                name: 'TransferList',
                component: () => import('./pages/inventory/TransferList.vue')
            },
            {
                path: 'transfers/create',
                name: 'TransferCreate',
                component: () => import('./pages/inventory/TransferForm.vue')
            },
            {
                path: 'reports',
                name: 'ReportsDashboard',
                component: () => import('./pages/reports/ReportsDashboard.vue')
            },
            {
                path: 'reports/sales',
                name: 'SalesReport',
                component: () => import('./pages/reports/SalesReport.vue')
            },
            {
                path: 'reports/stock',
                name: 'StockReport',
                component: () => import('./pages/reports/StockReport.vue')
            },
            {
                path: 'customers/create',
                name: 'CustomerCreate',
                component: () => import('./pages/customer/CustomerForm.vue')
            },
            {
                path: 'customers/:id/edit',
                name: 'CustomerEdit',
                component: () => import('./pages/customer/CustomerForm.vue'),
                props: true
            },
            {
                path: 'customers/:id/statement',
                name: 'CustomerStatement',
                component: () => import('./pages/customer/CustomerStatement.vue'),
                props: true
            },
            {
                path: 'products/create',
                name: 'ProductCreate',
                component: () => import('./pages/inventory/ProductForm.vue')
            },
            {
                path: 'products/movements',
                name: 'InventoryMovements',
                component: () => import('./pages/inventory/InventoryMovements.vue')
            },
            {
                path: 'products/:id/edit',
                name: 'ProductEdit',
                component: () => import('./pages/inventory/ProductForm.vue')
            },
            {
                path: 'returns',
                name: 'ReturnList',
                component: () => import('./pages/returns/ReturnList.vue')
            },
            {
                path: 'returns/create',
                name: 'ReturnCreate',
                component: () => import('./pages/returns/ReturnForm.vue')
            },
            {
                path: 'suppliers',
                name: 'SupplierList',
                component: () => import('./pages/inventory/SupplierList.vue')
            },
            {
                path: 'suppliers/:id/statement',
                name: 'SupplierStatement',
                component: () => import('./pages/inventory/SupplierStatement.vue'),
                props: true
            },
            {
                path: 'purchase-orders',
                name: 'PurchaseOrderList',
                component: () => import('./pages/inventory/PurchaseOrderList.vue')
            },
            {
                path: 'purchase-orders/create',
                name: 'PurchaseOrderCreate',
                component: () => import('./pages/inventory/PurchaseOrderCreate.vue')
            },
            {
                path: 'invoices',
                name: 'InvoiceList',
                component: () => import('./pages/finance/InvoiceList.vue')
            },
            {
                path: 'invoices/:id',
                name: 'InvoiceView',
                component: () => import('./pages/finance/InvoiceView.vue')
            },
            {
                path: 'users',
                name: 'UserList',
                component: () => import('./pages/core/UserList.vue')
            },
            {
                path: 'users/create',
                name: 'UserCreate',
                component: () => import('./pages/core/UserForm.vue')
            },
            {
                path: 'users/:id/edit',
                name: 'UserEdit',
                component: () => import('./pages/core/UserForm.vue')
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token');
    if (to.name !== 'Login' && !token) {
        next({ name: 'Login' });
    } else {
        next();
    }
});

export default router;
