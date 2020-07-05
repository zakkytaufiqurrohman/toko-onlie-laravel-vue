import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import store from '../store'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/about',
    name: 'About',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "about" */ '../views/About.vue')
  },
  {
    path: '/categories',
    name: 'Categories',
    component: () => import('../views/Categories.vue')
  },
  {
    path: '/books',
    name: 'Books',
    component: () => import('../views/Books.vue')
  },
  {
    path: '/detail/:slug',
    name: "detail",
    component: () => import('../views/Category.vue'),
  },
  {
    path: '/details/:slug',
    name: 'book',
    component: () => import('../views/Book.vue')
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: () => import('../views/Checkout.vue'),
    meta: { auth: true } //harus login dulu
  },
  {
    path: '/payment',
    name: 'payment',
    component: () => import('../views/Payment.vue'),
    meta: {auth:true}
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('../views/Profile.vue'),
    meta: {auth:true}
  },
  {
    path: '/myOrder',
    name: 'myOrder',
    component: () => import('../views/MyOrder'),
    meta: {auth:true}
  }
]

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
})
// tambahkan ini untuk melakukan pengecekan pada setiap routing
router.beforeEach((to, from, next) => {
  // jika routing ada meta auth-nya maka
  if (to.matched.some(record => record.meta.auth)) {
    // jika user adalah guest
    if (store.getters['auth/guest']) {
      // tampilkan pesan bahwa harus login dulu
      store.dispatch('alert/actionSet', {
        status: true,
        text: 'Login first',
        color: 'error',
      })
      store.dispatch('setPrevUrl', to.path)
      // tampilkan form login
      store.dispatch('dialog/setComponent', 'login')
    }
    else {
      next()
    }
  }
  else {
    next()
  }
})
export default router
