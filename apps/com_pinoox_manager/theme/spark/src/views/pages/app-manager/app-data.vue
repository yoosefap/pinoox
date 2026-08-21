<template>
  <div class="appData appDetails">
    <div v-if="isLoading && !usage" class="appManagerSectionLoading">
      <WidgetLoading/>
    </div>

    <template v-else>
      <section class="appData__storage">
        <div class="appData__storageHead">
          <span class="appData__storageIcon">
            <Icon :is="saxIcon.data" size="sm"/>
          </span>
          <div>
            <h3 class="appData__storageTitle">{{ translate('app_data_storage') }}</h3>
            <p class="appData__storageHint">{{ storageHint }}</p>
          </div>
        </div>
        <strong class="appData__storageValue" dir="ltr">{{ storageLabel }}</strong>
      </section>

      <section class="appData__stats">
        <article v-for="item in statItems" :key="item.label" class="appData__stat">
          <span class="appData__statLabel">{{ item.label }}</span>
          <strong class="appData__statValue" :dir="item.ltr ? 'ltr' : undefined">{{ item.value }}</strong>
        </article>
      </section>

      <section v-if="isSystemApp" class="appDetails__notice appDetails__notice--info">
        <Icon :is="saxIcon.notifyInfo" size="sm"/>
        <p>{{ translate('app_system_notice') }}</p>
      </section>

      <section v-else class="appDetails__dangerZone">
        <h3 class="appDetails__dangerZoneTitle">{{ translate('app_details_danger_zone') }}</h3>
        <div class="appDetails__dangerGrid">
          <article class="appDetails__warn">
            <div class="appDetails__warnHead">
              <span class="appDetails__warnIcon">
                <Icon :is="saxIcon.refresh" size="sm"/>
              </span>
              <h4>{{ translate('app_reset_title') }}</h4>
            </div>
            <p>{{ translate('app_reset_intro') }}</p>
            <Button
                :label="translate('app_reset_button')"
                variant="warning"
                outline
                @click="openResetModal"
            />
          </article>

          <article class="appDetails__danger">
            <div class="appDetails__dangerHead">
              <span class="appDetails__dangerIcon">
                <Icon :is="saxIcon.remove" size="sm"/>
              </span>
              <h4>{{ translate('app_uninstall_title') }}</h4>
            </div>
            <p>{{ translate('app_uninstall_intro') }}</p>
            <Button
                :label="translate('app_uninstall_button')"
                variant="danger"
                outline
                @click="openUninstallModal"
            />
          </article>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import {computed, onMounted, ref, watch} from 'vue';
import {openModal} from '@kolirt/vue-modal';
import {saxIcon} from '@/const/icons.js';
import Icon from '@/views/components/widgets/Icon.vue';
import Button from '@/views/components/widgets/Button.vue';
import WidgetLoading from '@/views/components/desktop-widgets/WidgetLoading.vue';
import {appAPI} from '@api/app.js';
import {unwrapResponse} from '@utils/helpers/apiHelper.js';
import {translate} from '@utils/helpers/managerLang.js';
import {toastSuccess} from '@utils/helpers/toastHelper.js';
import {useControlPanelNavigation} from '@/views/composables/useControlPanelNavigation.js';
import {normalizeAppRoutes} from '@utils/appRoutes.js';
import ModalUninstallApp from '@/views/pages/app-manager/modal-uninstall-app.vue';
import ModalResetApp from '@/views/pages/app-manager/modal-reset-app.vue';

const props = defineProps({
  app: {
    type: Object,
    required: true,
  },
  packageName: {
    type: String,
    required: true,
  },
});

const {pushControlPath} = useControlPanelNavigation();
const usage = ref(null);
const isLoading = ref(true);

const isSystemApp = computed(() => !!(props.app?.sys_app ?? props.app?.['sys-app']));
const routeCount = computed(() => {
  if (usage.value?.routes != null) {
    return Number(usage.value.routes) || 0;
  }

  return normalizeAppRoutes(props.app?.routes).length;
});

const storageLabel = computed(() => usage.value?.storage_label || '0 B');
const storageHint = computed(() => {
  const files = Number(usage.value?.files) || 0;
  const packageLabel = usage.value?.package_label;

  if (files > 0 && packageLabel) {
    return translate('app_data_storage_hint')
        .replace('{files}', String(files))
        .replace('{package}', packageLabel);
  }

  if (files > 0) {
    return translate('app_data_storage_hint_files').replace('{files}', String(files));
  }

  if (packageLabel) {
    return translate('app_data_storage_hint_package').replace('{package}', packageLabel);
  }

  return translate('app_data_storage_empty');
});

const statItems = computed(() => [
  {
    label: translate('app_data_stat_files'),
    value: String(usage.value?.files ?? 0),
    ltr: true,
  },
  {
    label: translate('app_data_stat_users'),
    value: String(usage.value?.users ?? 0),
    ltr: true,
  },
  {
    label: translate('app_data_stat_routes'),
    value: String(routeCount.value),
    ltr: true,
  },
  {
    label: translate('app_data_stat_package'),
    value: usage.value?.package_label || '—',
    ltr: true,
  },
  {
    label: translate('app_data_stat_migrations'),
    value: String(usage.value?.migrations ?? 0),
    ltr: true,
  },
  {
    label: translate('app_data_stat_patches'),
    value: String(usage.value?.patches ?? 0),
    ltr: true,
  },
]);

async function loadUsage() {
  if (!props.packageName) {
    usage.value = null;
    isLoading.value = false;
    return;
  }

  isLoading.value = true;

  try {
    usage.value = unwrapResponse(await appAPI.usage(props.packageName)) ?? null;
  } catch {
    usage.value = null;
  } finally {
    isLoading.value = false;
  }
}

function openResetModal() {
  openModal(ModalResetApp, {
    props: {
      app: props.app,
      packageName: props.packageName,
    },
  }).then((result) => {
    if (result?.reset) {
      toastSuccess(translate('reset_successfully'));
      loadUsage();
    }
  }).catch(() => {});
}

function openUninstallModal() {
  openModal(ModalUninstallApp, {
    props: {
      app: props.app,
      packageName: props.packageName,
    },
  }).then((result) => {
    if (result?.uninstalled) {
      toastSuccess(translate('delete_successfully'));
      pushControlPath('/control/apps');
    }
  }).catch(() => {});
}

onMounted(loadUsage);

watch(() => props.packageName, loadUsage);
</script>
