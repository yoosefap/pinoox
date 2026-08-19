import {useRouter} from 'vue-router';
import {MARKET_ID, useMarketWindowStore} from '@/stores/modules/marketWindow.js';
import {useAppViewMode} from '@/views/composables/useAppViewMode.js';
import {isControlRoute, useControlPanel} from '@/views/composables/useControlPanel.js';
import {pushManagerBrowserRoute} from '@/views/composables/useManagerWindowRouteSync.js';

export {MARKET_ID};

export function isMarketRoute(route) {
    return String(route?.path ?? '').startsWith('/market');
}

export function useMarket() {
    const router = useRouter();
    const {isAdvanced} = useAppViewMode();
    const marketWindow = useMarketWindowStore();
    const {openControlPanel, controlPanelWindow} = useControlPanel();

    function captureReturnTo(fromControl) {
        const openedFromControl = fromControl === true
            || isControlRoute(router.currentRoute.value);

        if (!marketWindow.isOpen) {
            marketWindow.setReturnTo(openedFromControl ? 'control' : null);
            return;
        }

        if (openedFromControl) {
            marketWindow.setReturnTo('control');
        }
    }

    async function openMarket(path = '/market', options = {}) {
        captureReturnTo(options.fromControl === true);
        marketWindow.setLastPath(path);

        if (isAdvanced.value) {
            if (marketWindow.isMinimized) {
                marketWindow.restoreSession();
                await pushManagerBrowserRoute(router, marketWindow, path);
                return;
            }

            if (!marketWindow.isActive) {
                marketWindow.openFullscreen();
            }

            await pushManagerBrowserRoute(router, marketWindow, path);
            return;
        }

        await router.push(path);
    }

    async function openMarketFromControl(path = '/market') {
        await openMarket(path, {fromControl: true});
    }

    async function closeMarket() {
        const shouldReturnToControl = marketWindow.returnTo === 'control';
        const controlPath = controlPanelWindow.lastPath || '/control/apps';

        if (isAdvanced.value && marketWindow.isOpen) {
            marketWindow.close();
        }

        marketWindow.clearReturnTo();

        if (shouldReturnToControl) {
            await openControlPanel(controlPath);
            return;
        }

        if (isMarketRoute(router.currentRoute.value)) {
            await router.push({name: 'desktop'});
        }
    }

    return {
        openMarket,
        openMarketFromControl,
        closeMarket,
        isMarketRoute,
        marketWindow,
    };
}
