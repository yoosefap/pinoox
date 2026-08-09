<template>
  <Page hide-header class="pageAppManager">
    <div v-if="isLoading" class="appManagerLoading">
      <WidgetLoading/>
    </div>

    <template v-else-if="app">
      <header class="appManagerHero">
        <button
            type="button"
            class="appManagerHero__back"
            :title="translate('app_back')"
            :aria-label="translate('app_back')"
            @click="pushControlPath('/control/apps')"
        >
          <Icon :is="saxIcon.back" size="sm"/>
        </button>

        <AppIcon v-bind="appIconProps(app)" size="md"/>

        <div class="appManagerHero__text">
          <h1 class="appManagerHero__title">{{ app.name }}</h1>
          <span class="appManagerHero__package" dir="ltr">{{ packageName }}</span>
        </div>

        <div class="appManagerHero__aside">
          <div v-if="versionLabel || statusBadges.length" class="appManagerHero__badges">
            <button
                v-if="versionLabel"
                type="button"
                class="appManagerHero__badge appManagerHero__badge--version"
                :class="{ 'is-open': versionCodeOpen }"
                :title="versionCodeTitle"
                @click="toggleVersionCode"
            >
              v{{ versionLabel }}
              <span v-if="versionCode" class="appManagerHero__versionCode">#{{ versionCode }}</span>
            </button>
            <span
                v-for="badge in statusBadges"
                :key="badge"
                class="appManagerHero__badge"
            >
              {{ badge }}
            </span>
          </div>

          <button
              v-if="canSecretView"
              type="button"
              class="appManagerHero__preview"
              @click="openSecretView"
          >
            <Icon :is="saxIcon.eye" size="xs"/>
            <span>{{ translate('app_secret_view') }}</span>
          </button>
        </div>
      </header>

      <nav class="appManagerNav" aria-label="بخش‌های مدیریت اپ">
        <router-link
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="appManagerNav__link"
        >
          <Icon :is="item.icon" size="xs"/>
          <span>{{ item.label }}</span>
        </router-link>
      </nav>

      <RouterView v-slot="{ Component }">
        <component :is="Component" :package-name="packageName" :app="app"/>
      </RouterView>
    </template>

    <template v-else>
      <header class="appManagerHero appManagerHero--empty">
        <button
            type="button"
            class="appManagerHero__back"
            :title="translate('app_back')"
            :aria-label="translate('app_back')"
            @click="pushControlPath('/control/apps')"
        >
          <Icon :is="saxIcon.back" size="sm"/>
        </button>
        <div class="appManagerHero__text">
          <h1 class="appManagerHero__title">{{ translate('app_not_found_title') }}</h1>
        </div>
      </header>
      <PageEmpty title="اپلیکیشن یافت نشد" description="این اپ نصب نشده یا حذف شده است."/>
    </template>
  </Page>
</template>

<script setup>
import {computed, onMounted, ref} from 'vue';
import {useRoute, useRouter} from 'vue-router';
import {saxIcon} from '@/const/icons.js';
import Icon from '@/views/components/widgets/Icon.vue';
import WidgetLoading from '@/views/components/desktop-widgets/WidgetLoading.vue';
import {useAppStore} from '@/stores/modules/app.js';
import {appIconProps} from '@utils/helpers/appIconProps.js';
import {useControlPanelNavigation} from '@/views/composables/useControlPanelNavigation.js';
import {useAppViewMode} from '@/views/composables/useAppViewMode.js';
import {useAppViewWindowStore} from '@/stores/modules/appViewWindow.js';
import {translate} from '@utils/helpers/managerLang.js';

const route = useRoute();
const router = useRouter();
const appStore = useAppStore();
const {pushControlPath, appManagerPath} = useControlPanelNavigation();
const {isAdvanced} = useAppViewMode();
const appViewWindow = useAppViewWindowStore();
const isLoading = ref(true);
const versionCodeOpen = ref(false);
const packageName = computed(() => route.params.package_name);
const app = computed(() => appStore.fetchAppByPackage(packageName.value));

const isSystemApp = computed(() => !!(app.value?.sys_app ?? app.value?.['sys-app']));
const versionLabel = computed(() => app.value?.version || null);
const versionCode = computed(() => {
  const code = app.value?.version_code;

  if (code == null || code === '') {
    return null;
  }

  return String(code);
});
const versionCodeTitle = computed(() => {
  if (!versionCode.value) {
    return undefined;
  }

  return `${translate('app_stat_version_code')}: ${versionCode.value}`;
});
const canSecretView = computed(() => {
  if (!packageName.value) {
    return false;
  }

  if (app.value?.open === 'app-view') {
    return true;
  }

  return !isSystemApp.value;
});

const statusBadges = computed(() => {
  const list = [];

  if (isSystemApp.value) {
    list.push(translate('app_badge_system'));
  }

  return list;
});

const navItems = computed(() => [
  {to: appManagerPath(packageName.value, 'details'), label: 'جزئیات', icon: saxIcon.guide},
  {to: appManagerPath(packageName.value, 'config'), label: 'تنظیمات', icon: saxIcon.setting},
  {to: appManagerPath(packageName.value, 'users'), label: 'کاربران', icon: saxIcon.user},
  {to: appManagerPath(packageName.value, 'templates'), label: 'قالب‌ها', icon: saxIcon.appearance},
  {to: appManagerPath(packageName.value, 'data'), label: 'داده‌ها', icon: saxIcon.data},
]);

function toggleVersionCode() {
  if (!versionCode.value) {
    return;
  }

  versionCodeOpen.value = !versionCodeOpen.value;
}

function openSecretView() {
  if (!canSecretView.value) {
    return;
  }

  if (isAdvanced.value) {
    appViewWindow.openFullscreen(packageName.value);
  }

  router.push({name: 'app-view', params: {package_name: packageName.value}});
}

onMounted(async () => {
  if (!appStore.isLoaded) {
    await appStore.getApps();
  }

  isLoading.value = false;
});
</script>
