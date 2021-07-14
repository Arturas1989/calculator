/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

const { Input } = require('postcss');

require('./bootstrap');

window.Vue = require('vue').default;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});




// let timeout = null

// DOM = document.querySelector('.code');
//     console.log(DOM);


    
//     DOM.addEventListener('input',function (e) 
//     {
//         clearTimeout(timeout);

//         timeout = setTimeout(function () 
//         {
//             axios.get(getOrder)
//             .then(response =>
//             {
//                 const row = response.data.find(function(data) 
//                 {
//                     if(data.code === "G20BE0R00800")
//                     {
//                         return true;
//                     }  
//                 });
//                 console.log(e.target.name);
//             })
//         }, 1500);

        
//     });




