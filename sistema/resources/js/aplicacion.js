import './inicio';
import Alpine from 'alpinejs';
import { metricsPage } from './pages/dashboard';
import { historyPage } from './pages/historial';
import { usersPage } from './pages/usuarios';
import { ecgUpload } from './pages/subir-ecg';
import { loginForm } from './pages/login';
import { patientsPage } from './pages/patients';

Alpine.data('metricsPage', metricsPage);
Alpine.data('historyPage', historyPage);
Alpine.data('usersPage', usersPage);
Alpine.data('ecgUpload', ecgUpload);
Alpine.data('loginForm', loginForm);
Alpine.data('patientsPage', patientsPage);

window.Alpine = Alpine;
Alpine.start();
