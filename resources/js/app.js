import jquery from 'jquery';

window.$ = window.jQuery = jquery;

import './bootstrap';
import './adventiste-ui';
import './montant-fcfa';
import { initOfflineSync } from './offline-sync';

initOfflineSync();
