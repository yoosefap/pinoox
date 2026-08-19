import { resolveLucideComponent } from '@/utils/lucideIcon.js';
import { h } from 'vue';

function makeLucideIcon(name) {
    const comp = resolveLucideComponent(name);
    if (!comp) return null;
    return {
        element: { render: () => h(comp, { size: 18, strokeWidth: 2.15, color: 'currentColor' }) },
        class: '',
    };
}

export const controlMenuItems = [
    { href: '/control/widgets', title: 'ویجت‌ها',     lucide: 'layout-grid' },
    { href: '/control/apps',    title: 'اپلیکیشن‌ها', lucide: 'boxes' },
    { href: '/control/routes',  title: 'مسیریابی',    lucide: 'route' },
    {
        title: 'تنظیمات', lucide: 'settings',
        children: [
            { href: '/control/settings/appearance',  title: 'ظاهر و زمینه' },
            { href: '/control/settings/application', title: 'تنظیمات اپلیکیشن' },
        ],
    },
    { href: '/control/profile', title: 'حساب کاربری', lucide: 'user' },
    { href: '/control/pincore', title: 'پینوکس',       lucide: 'code' },
    { href: '/market',          title: 'مارکت',        lucide: 'store' },
];

export function toSidebarMenuItems() {
    return controlMenuItems.map((item) => {
        const base = {
            title: item.title,
            icon: makeLucideIcon(item.lucide),
            attributes: { 'aria-label': item.title },
        };

        if (item.children) {
            return { ...base, child: item.children.map((child) => ({ ...child })) };
        }

        return { ...base, href: item.href };
    });
}

export function isControlMenuItemActive(routePath, item) {
    if (item.href) {
        return routePath === item.href || routePath.startsWith(`${item.href}/`);
    }

    if (item.children) {
        return item.children.some((child) =>
            routePath === child.href || routePath.startsWith(`${child.href}/`)
        );
    }

    return false;
}
