// Configuration de l'API
const API_CONFIG = {
    // URL de base de l'API selon l'environnement
    BASE_URL: window.location.hostname === 'localhost' 
        ? 'http://localhost:8000' 
        : 'https://ecoecoride-bfc4b6ed3554.herokuapp.com',
    
    // Endpoints
    ENDPOINTS: {
        AUTH: {
            LOGIN: '/api/auth/login',
            REGISTER: '/api/user',
            ME: '/api/auth/me'
        },
        CAR: {
            LIST: '/api/car/list',
            ADD: '/api/car/add',
            DELETE: '/api/car/delete'
        },
        TRIP: {
            ALL: '/api/trip/all',
            ADD: '/api/trip/add',
            SEARCH: '/api/trip/search',
            DELETE: '/api/trip/delete',
            DETAILS: '/api/trip'
        },
        RESERVATION: {
            LIST: '/api/trip/reservation/list',
            RESERVE: '/api/trip/reservation',
            CANCEL: '/api/trip/reservation/cancel'
        },
        REVIEW: {
            ADD: '/api/trip',
            LIST: '/api/trip'
        }
    }
};

// Fonction utilitaire pour construire les URLs complètes
function getApiUrl(endpoint) {
    return API_CONFIG.BASE_URL + endpoint;
}

// Export pour utilisation dans les autres fichiers
window.API_CONFIG = API_CONFIG;
window.getApiUrl = getApiUrl;
