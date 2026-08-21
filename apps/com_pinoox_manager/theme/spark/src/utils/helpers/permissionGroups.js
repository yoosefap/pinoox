function asKey(item) {
    if (typeof item === 'string') {
        return item.trim();
    }

    return String(item?.key || item?.permission_key || item?.name || '').trim();
}

export function permissionGroupPrefix(key) {
    const value = String(key || '').trim();

    if (!value || value === '*') {
        return '';
    }

    const parts = value.split('.').filter(Boolean);

    if (parts.length < 2) {
        return '';
    }

    return parts.slice(0, -1).join('.');
}

export function permissionLeaf(key) {
    const value = String(key || '').trim();

    if (!value) {
        return '';
    }

    if (value === '*') {
        return '*';
    }

    const parts = value.split('.').filter(Boolean);

    return parts[parts.length - 1] || value;
}

export function normalizePermission(item) {
    const key = asKey(item);

    if (typeof item === 'string') {
        return {
            key,
            name: key,
            id: key,
            raw: item,
        };
    }

    return {
        key,
        name: String(item?.name || key),
        id: item?.permission_id ?? item?.id ?? key,
        raw: item,
    };
}

export function permissionLabel(item) {
    const normalized = normalizePermission(item);

    if (normalized.name && normalized.name !== normalized.key) {
        return normalized.name;
    }

    return permissionLeaf(normalized.key);
}

export function groupPermissions(items = []) {
    const map = new Map();

    for (const item of items) {
        const normalized = normalizePermission(item);
        const prefix = permissionGroupPrefix(normalized.key);

        if (!map.has(prefix)) {
            map.set(prefix, []);
        }

        map.get(prefix).push({
            ...normalized,
            label: permissionLabel(item),
            leaf: permissionLeaf(normalized.key),
        });
    }

    const prefixes = [...map.keys()].sort((left, right) => {
        if (left === '') {
            return 1;
        }

        if (right === '') {
            return -1;
        }

        return left.localeCompare(right);
    });

    return prefixes.map((prefix) => ({
        prefix,
        items: map.get(prefix).sort((left, right) => {
            return left.leaf.localeCompare(right.leaf) || left.key.localeCompare(right.key);
        }),
    }));
}
