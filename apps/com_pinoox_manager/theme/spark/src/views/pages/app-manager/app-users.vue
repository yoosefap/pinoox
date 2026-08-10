<template>
  <PageSection :title="translate('app_users_title')">
    <template #actions>
      <button type="button" class="appUsers__add" @click="openCreate">
        <Icon :is="saxIcon.add" size="xs"/>
        <span>{{ translate('app_users_add') }}</span>
      </button>
    </template>

    <div v-if="isLoading && !users.length" class="appManagerSectionLoading">
      <WidgetLoading/>
    </div>

    <div
        v-else
        class="appManagerSection"
        :class="{ 'is-refreshing': isRefreshing }"
    >
      <div v-if="isRefreshing" class="appManagerSection__refresh" aria-hidden="true">
        <WidgetLoading/>
      </div>

      <div class="appUsers">
      <p class="appUsers__intro">{{ translate('app_users_intro') }}</p>

      <div class="appUsers__toolbar">
        <label class="appUsers__search">
          <Icon :is="saxIcon.search" size="xs"/>
          <input
              v-model.trim="query"
              type="search"
              :placeholder="translate('app_users_search_placeholder')"
          >
        </label>

        <DarkSelect
            v-model="statusFilter"
            :label="translate('app_users_filter_status')"
            :options="statusFilterOptions"
            direction="rtl"
        />
        <DarkSelect
            v-if="meta.has_roles"
            v-model="roleFilter"
            :label="translate('app_users_filter_level')"
            :options="roleFilterOptions"
            direction="rtl"
        />
      </div>

      <section class="appUsersBlock">
        <div v-if="users.length" class="appUsersTableWrap">
          <table class="appUsersTable">
            <thead>
            <tr>
              <th
                  v-for="column in userColumns"
                  :key="column.key"
                  :class="{
                    'is-sortable': column.sort,
                    'is-active': sort === column.key,
                    'is-ltr': column.ltr,
                    'is-actions': column.key === 'actions',
                  }"
              >
                <button
                    v-if="column.sort"
                    type="button"
                    class="appUsersTable__sort"
                    @click="toggleSort(column.key)"
                >
                  <span>{{ column.label }}</span>
                  <span class="appUsersTable__sortMark" aria-hidden="true">{{ sortMark(column.key) }}</span>
                </button>
                <span v-else>{{ column.label }}</span>
              </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in users" :key="user.user_id">
              <td
                  v-for="column in userColumns"
                  :key="column.key"
                  :class="{
                    'is-ltr': column.ltr,
                    'is-id': column.key === 'user_id',
                    'appUsersTable__actions': column.key === 'actions',
                  }"
                  :data-label="column.label"
              >
                <template v-if="column.key === 'actions'">
                  <button type="button" class="appUsersTable__action" :title="translate('app_users_edit')" @click="openEdit(user)">
                    <Icon :is="saxIcon.edit" size="xs"/>
                  </button>
                  <button
                      type="button"
                      class="appUsersTable__action is-danger"
                      :title="user.is_self ? translate('app_users_delete_self') : translate('app_users_delete')"
                      :disabled="user.is_self"
                      @click="openDelete(user)"
                  >
                    <Icon :is="saxIcon.remove" size="xs"/>
                  </button>
                </template>
                <span v-else-if="column.key === 'status'" class="appUsersStatus" :class="`is-${user.status}`">
                  {{ statusLabel(user.status) }}
                </span>
                <span v-else-if="cellValue(user, column)" :dir="column.ltr ? 'ltr' : undefined">
                  {{ cellValue(user, column) }}
                </span>
                <span v-else class="appUsersEmpty">{{ emptyLabel(column) }}</span>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <PageEmpty
            v-else
            :title="hasActiveFilters ? translate('app_users_empty_filtered') : translate('app_users_empty')"
        />

        <p class="appUsersBlock__caption">
          {{ usersCaption }}
        </p>

        <div v-if="lastPage > 1 || total > perPage" class="appUsersPager">
          <button type="button" :disabled="page <= 1" @click="goPage(page - 1)">{{ translate('app_users_prev') }}</button>
          <span>{{ translate('app_users_page') }} {{ page }} / {{ lastPage }}</span>
          <button type="button" :disabled="page >= lastPage" @click="goPage(page + 1)">{{ translate('app_users_next') }}</button>
          <DarkSelect
              v-model="perPageValue"
              :label="translate('app_users_per_page')"
              :options="perPageOptions"
              direction="rtl"
          />
        </div>
      </section>
      </div>
    </div>
  </PageSection>
</template>

<script setup>
import {computed, onMounted, ref, watch} from 'vue';
import {openModal} from '@kolirt/vue-modal';
import {saxIcon} from '@/const/icons.js';
import Icon from '@/views/components/widgets/Icon.vue';
import DarkSelect from '@/views/components/form/DarkSelect.vue';
import WidgetLoading from '@/views/components/desktop-widgets/WidgetLoading.vue';
import {userAPI} from '@api/user.js';
import {unwrapResponse} from '@utils/helpers/apiHelper.js';
import {translate, translateLevel} from '@utils/helpers/managerLang.js';
import ModalAppUser from './modal-app-user.vue';
import ModalDeleteAppUser from './modal-delete-app-user.vue';

const props = defineProps({packageName: String});

const packageName = computed(() => props.packageName);
const users = ref([]);
const meta = ref({
  has_roles: false,
  roles: [],
  statuses: ['active', 'inactive', 'suspend', 'pending'],
});
const total = ref(0);
const page = ref(1);
const perPage = ref(10);
const lastPage = ref(1);
const query = ref('');
const statusFilter = ref('');
const roleFilter = ref('');
const sort = ref('user_id');
const dir = ref('desc');
const isLoading = ref(true);
const isRefreshing = ref(false);
let searchTimer = null;

const perPageValue = computed({
  get: () => String(perPage.value),
  set: (value) => {
    perPage.value = Number(value) || 10;
    page.value = 1;
    loadUsers();
  },
});

const perPageOptions = [
  {value: '10', label: '10'},
  {value: '20', label: '20'},
  {value: '50', label: '50'},
];

const hasActiveFilters = computed(() => Boolean(query.value || statusFilter.value || roleFilter.value));

const statusFilterOptions = computed(() => [
  {value: '', label: translate('app_users_filter_all')},
  ...(meta.value.statuses || ['active', 'inactive', 'suspend', 'pending']).map((value) => ({
    value,
    label: translate(`app_users_status_${value}`),
  })),
]);

const roleFilterOptions = computed(() => [
  {value: '', label: translate('app_users_filter_all')},
  ...(meta.value.roles || []).map((role) => ({
    value: String(role.role_id),
    label: translateLevel(role.key, role.label || role.name),
  })),
]);

const userColumns = computed(() => {
  const columns = [
    {key: 'user_id', label: translate('app_users_col_id'), sort: true, ltr: true},
    {key: 'full_name', label: translate('app_users_col_name'), sort: true},
    {key: 'username', label: translate('app_users_col_username'), sort: true, ltr: true},
    {key: 'mobile', label: translate('app_users_col_mobile'), sort: true, ltr: true},
    {key: 'email', label: translate('app_users_col_email'), sort: true, ltr: true},
    {key: 'status', label: translate('app_users_col_status'), sort: true},
  ];

  if (meta.value.has_roles) {
    columns.push({key: 'roles', label: translate('app_users_col_level'), sort: false});
  } else {
    columns.push({key: 'group_key', label: translate('app_users_col_group'), sort: true, ltr: true});
  }

  columns.push(
      {key: 'created_at', label: translate('app_users_col_date'), sort: true, ltr: true},
      {key: 'actions', label: translate('app_users_col_actions'), sort: false},
  );

  return columns;
});

const usersCaption = computed(() => {
  const count = String(total.value);
  return `${translate('app_users_table_caption')} · ${translate('app_users_count').replace('{count}', count)}`;
});

function statusLabel(status) {
  return translate(`app_users_status_${status}`);
}

function userRoleLabel(user) {
  const role = user?.roles?.[0];

  if (!role) {
    return '';
  }

  return translateLevel(role.key, role.name);
}

function cellValue(user, column) {
  switch (column.key) {
    case 'user_id':
      return user?.user_id != null ? String(user.user_id) : '';
    case 'full_name':
      return String(user?.full_name || '').trim();
    case 'username':
      return String(user?.username || '').trim();
    case 'mobile':
      return String(user?.mobile || '').trim();
    case 'email':
      return String(user?.email || '').trim();
    case 'group_key':
      return String(user?.group_key || '').trim();
    case 'roles':
      return userRoleLabel(user);
    case 'created_at':
      return String(user?.register_date_fa || user?.register_date || '').trim();
    default:
      return '';
  }
}

function emptyLabel(column) {
  if (column.key === 'roles') {
    return translate('app_users_no_roles_assigned');
  }

  if (column.key === 'group_key') {
    return translate('app_users_form_group_none');
  }

  return translate('app_users_empty_value');
}

function sortMark(column) {
  if (sort.value !== column) {
    return '↕';
  }

  return dir.value === 'asc' ? '↑' : '↓';
}

function toggleSort(column) {
  if (sort.value === column) {
    dir.value = dir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sort.value = column;
    dir.value = column === 'user_id' ? 'desc' : 'asc';
  }

  page.value = 1;
  loadUsers();
}

function goPage(next) {
  page.value = Math.min(lastPage.value, Math.max(1, next));
  loadUsers();
}

async function loadUsers() {
  if (!packageName.value) {
    users.value = [];
    isLoading.value = false;
    isRefreshing.value = false;
    return;
  }

  if (users.value.length || !isLoading.value) {
    isRefreshing.value = users.value.length > 0;
    if (!users.value.length) {
      isLoading.value = true;
    }
  } else {
    isLoading.value = true;
  }

  try {
    const response = await userAPI.getUsers(packageName.value, {
      q: query.value || undefined,
      status: statusFilter.value || undefined,
      role: roleFilter.value || undefined,
      sort: sort.value,
      dir: dir.value,
      page: page.value,
      per_page: perPage.value,
    });
    const data = unwrapResponse(response) ?? {};
    users.value = Array.isArray(data.items) ? data.items : [];
    total.value = Number(data.total || 0);
    page.value = Number(data.page || 1);
    perPage.value = Number(data.per_page || 10);
    lastPage.value = Number(data.last_page || 1);

    if (data.meta && typeof data.meta === 'object') {
      meta.value = {
        has_roles: Boolean(data.meta.has_roles),
        roles: data.meta.roles || [],
        statuses: data.meta.statuses || ['active', 'inactive', 'suspend', 'pending'],
      };
    }
  } catch {
    users.value = [];
    total.value = 0;
  } finally {
    isLoading.value = false;
    isRefreshing.value = false;
  }
}

function openCreate() {
  openModal(ModalAppUser, {
    props: {packageName: packageName.value, meta: meta.value, user: null},
  }).then(() => loadUsers()).catch(() => {});
}

function openEdit(user) {
  openModal(ModalAppUser, {
    props: {packageName: packageName.value, meta: meta.value, user},
  }).then(() => loadUsers()).catch(() => {});
}

function openDelete(user) {
  if (user.is_self) {
    return;
  }

  openModal(ModalDeleteAppUser, {
    props: {packageName: packageName.value, user},
  }).then(() => loadUsers()).catch(() => {});
}

onMounted(loadUsers);

watch(packageName, () => {
  query.value = '';
  statusFilter.value = '';
  roleFilter.value = '';
  page.value = 1;
  loadUsers();
});

watch(query, () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    page.value = 1;
    loadUsers();
  }, 280);
});

watch([statusFilter, roleFilter], () => {
  page.value = 1;
  loadUsers();
});
</script>
