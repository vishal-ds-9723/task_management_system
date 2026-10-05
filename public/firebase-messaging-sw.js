importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// PWA lifecycle (previously in a separate /sw.js, merged here since only one
// service worker can control the '/' scope — registering both caused FCM
// token generation to fail silently).
self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', () => {
    // Required for PWA install prompt
});

const firebaseConfig = {
    apiKey: "AIzaSyCM1JPND9WPN_8l-rWQl4U1ppeV8r0MZEw",
    authDomain: "tms-task-4e9b9.firebaseapp.com",
    projectId: "tms-task-4e9b9",
    storageBucket: "tms-task-4e9b9.firebasestorage.app",
    messagingSenderId: "169931045249",
    appId: "1:169931045249:web:61ba9c60a44618019bd080"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/images/logo.png'
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
